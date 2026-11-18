// PR-Prüfung: findet in einem Diff Stellen, über die ungeprüfter Code nachgeladen oder ausgeführt würde.
// Nur hinzugefügte Zeilen zählen. Fail-closed: lieber ein Befund zu viel, ein Mensch sieht ihn sich an.
// Lauf: gh pr diff -R <owner/repo> <nr> | node werkzeuge/pr-pruefen.mjs   (exit 1 = Befund)
// Der Sicherheits-Nachtlauf ruft pruefeDiff für jeden offenen PR aller eigenen Repos auf.
// ponytail: Muster je Zeile, kein Parser für JSON/YAML/Shell; über mehrere Zeilen verteilte Werte erkennt nur install-skript (Schlüssel allein genügt).
// Die Prüfung ersetzt kein Lesen, sie sagt, wo man lesen muss. Diese Datei, ihre Tests und die Doku treffen sich selbst (gewollt).
import { readFileSync, realpathSync } from 'node:fs';
import { basename } from 'node:path';
import { pathToFileURL } from 'node:url';

export const REGELN = {
  'fremdquelle': 'Paket oder Registry nicht aus registry.npmjs.org/PyPI (git, URL, Datei, fremder Index, Registry-Konfiguration)',
  'install-skript': 'Paket oder package.json führt beim Installieren ein Skript aus',
  'action-ohne-sha': 'GitHub-Action, Workflow oder Container-Image nicht auf Commit-SHA bzw. Digest festgeschrieben',
  'pull-request-target': 'Workflow mit pull_request_target (fremder PR-Code mit Repo-Rechten)',
  'pruef-datei': 'Die PR-Prüfung selbst wird angefasst: ihre Workflow-Datei oder dieses Skript geändert/gelöscht/umbenannt, pr-pruefung in einer anderen Workflow-Datei, jede Änderung an .gitattributes',
  'nachladen': 'Code wird aus dem Netz geladen oder dynamisch ausgeführt (curl/wget, Remote-Import, fremdes Skript, eval); auch Binärdarstellung einer Shell-Datei, eines Dockerfiles oder Workflows (Inhalt nicht lesbar)',
};

const ERLAUBTE_HOSTS = new Set(['registry.npmjs.org', 'registry.yarnpkg.com', 'pypi.org', 'files.pythonhosted.org']);
// package.json-Schlüssel, deren Wert eine URL oder „o/r“ sein darf. Gilt nur für schwache Formen (URL, o/r), nie für git:/github:/npm: usw.
const META = new Set(['url', 'homepage', 'repository', 'bugs', 'funding', 'main', 'module', 'types', 'typings', 'bin', 'exports', 'browser', '$schema', 'directory']);
const RE_STARK = /^(git\+|git:|github:|gitlab:|bitbucket:|gist:|file:|link:|npm:|portal:|patch:)/;
const RE_SCHWACH = /^(https?:|[\w.-]+\/[\w.-]+(#.*)?$)/;
const RE_LIFECYCLE = /"(preinstall|install|postinstall|preprepare|prepare|postprepare|prepublish)"\s*:/;
const RE_PIP = /git\+|@\s*(https?|file):|--(extra-)?index-url|--trusted-host|--find-links|--requirement|--constraint|^\s*-[ief]\s|^\s*-[rc]\s+\S*:\/\/|^\s*(https?|file):|^\s*\.{0,2}\//;
const RE_INSTALL = /\b(pip3?|pipx|uv\s+(pip|add|tool)|npm|npx|pnpm|yarn|bun|bunx)\b[^\n]*(https?:\/\/|git\+|github:|gitlab:|--index-url|--extra-index-url|--registry)/;
const RE_NACHLADEN = [
  /\b(curl|wget)\b[^\n]*\|\s*(sudo(\s+-\S+)*\s+)?(env\s+)?(\S*\/)?((ba|z|da|k)?sh|python3?|node|perl|ruby|php)\b/,
  /(\$\(|`|<\()\s*(curl|wget)\b/,
  /\bimport\s*\(?\s*["'`](https?:)?\/\//,
  /\bfrom\s+["'](https?:)?\/\//,
  /\bimportScripts\s*\(/,
  /\.src\s*=\s*["'`](https?:)?\/\//,
  /<script\b[^>]*\bsrc\s*=\s*["']?(https?:)?\/\//i,
  /\bsrc\s*=\s*["']?(https?:)?\/\/[^"'\s>]*\.m?js\b/i,
  /\beval\s*\(|\(\s*eval\s*\)|\bnew\s+Function\s*\(/,
];

const PRUEF_DATEIEN = new Set(['.github/workflows/pr-pruefung.yml', 'werkzeuge/pr-pruefen.mjs']);
const istShellArtig = (datei, name) => /\.(sh|bash|zsh|ps1)$/.test(name) || /^(Dockerfile|Containerfile)/.test(name) || /\.dockerfile$/i.test(name) || name === 'Makefile' || istWorkflow(datei);
const istWorkflow = (datei) => /^\.github\/workflows\/[^/]+\.ya?ml$/.test(datei) || /(^|\/)action\.ya?ml$/.test(datei);
const gepinnt = (ref) => /@[0-9a-f]{40}$/.test(ref) || /@sha256:[0-9a-f]{64}$/.test(ref);

function regelnFuer(datei, z) {
  const name = basename(datei);
  const r = new Set();
  if (istWorkflow(datei) && !PRUEF_DATEIEN.has(datei) && /pr-pruefung/.test(z)) r.add('pruef-datei');
  if (/^\s*#/.test(z) && !/\.(m?js|html?)$/.test(name)) return r; // Kommentarzeile (Shell, YAML, pip)
  if (['.npmrc', '.yarnrc', '.yarnrc.yml', 'pip.conf', 'pip.ini', '.pypirc', '.pnpmfile.cjs', 'bunfig.toml'].includes(name) && z.trim()) r.add('fremdquelle');
  if (name === 'package-lock.json' || name === 'npm-shrinkwrap.json') {
    const res = /"resolved":\s*"([^"]*)"/.exec(z);
    if (res && !res[1].startsWith('https://registry.npmjs.org/')) r.add('fremdquelle');
    if (/"hasInstallScript":\s*true/.test(z)) r.add('install-skript');
  }
  if (/^(yarn\.lock|pnpm-lock\.yaml|uv\.lock|poetry\.lock|Pipfile\.lock|bun\.lock)$/.test(name)) {
    for (const [, host] of z.matchAll(/\bhttps?:\/\/([^/\s"'}#,:]+)/g)) if (!ERLAUBTE_HOSTS.has(host)) r.add('fremdquelle');
    if (/\b(git\+|github:|file:)/.test(z)) r.add('fremdquelle');
  }
  if (name === 'package.json') {
    if (RE_LIFECYCLE.test(z)) r.add('install-skript');
    for (const [, k, v] of z.matchAll(/"([^"]+)"\s*:\s*"([^"]*)"/g)) {
      if (/^(preinstall|install|postinstall|preprepare|prepare|postprepare|prepublish)$/.test(k)) continue;
      if (RE_STARK.test(v) || (!META.has(k) && RE_SCHWACH.test(v))) r.add('fremdquelle');
    }
  }
  if (/(^|\/)(requirements|constraints)[^/]*\.(txt|in)$|(^|\/)(pyproject\.toml|setup\.cfg|setup\.py|Pipfile)$/.test(datei) && RE_PIP.test(z)) r.add('fremdquelle');
  if (!name.endsWith('.md') && RE_INSTALL.test(z)) r.add('fremdquelle');
  if (istWorkflow(datei) || /(^|\/)(docker-)?compose[^/]*\.ya?ml$/.test(datei)) {
    for (const [, ref] of z.matchAll(/["']?\buses["']?\s*:\s*["']?([^\s"'#,}]+)/g)) if (!ref.startsWith('./') && !gepinnt(ref)) r.add('action-ohne-sha');
    for (const [, ref] of z.matchAll(/["']?\b(?:image|container)["']?\s*:\s*["']?([^\s"'#,{}]+)/g)) if (!gepinnt(ref)) r.add('action-ohne-sha');
    if (/pull_request_target/.test(z)) r.add('pull-request-target');
  }
  if (/^(Dockerfile|Containerfile)/.test(name) || /\.dockerfile$/i.test(name)) {
    const from = /^\s*FROM\s+(?:--\S+\s+)*(\S+)/i.exec(z);
    if (from && from[1] !== 'scratch' && !gepinnt(from[1])) r.add('action-ohne-sha');
    if (/^\s*ADD\s+(--\S+\s+)*(https?:|git@)/i.test(z)) r.add('nachladen');
  }
  if (istShellArtig(datei, name) && /\b(curl|wget)\b/.test(z)) r.add('nachladen');
  if (!name.endsWith('.md') && RE_NACHLADEN.some((re) => re.test(z))) r.add('nachladen');
  return r;
}

// Pfad aus „+++ b/…“: Tab am Ende (Leerzeichen im Pfad) abschneiden, Anführungszeichen mit Oktal-Escapes (Umlaute) auflösen.
function pfad(roh) {
  let p = roh.replace(/\t.*$/, '');
  if (p.startsWith('"') && p.endsWith('"')) {
    const bytes = []; const inhalt = p.slice(1, -1);
    for (let i = 0; i < inhalt.length; i++) {
      if (inhalt[i] !== '\\') { bytes.push(...Buffer.from(inhalt[i], 'utf8')); continue; }
      const okt = /^[0-7]{3}/.exec(inhalt.slice(i + 1));
      if (okt) { bytes.push(parseInt(okt[0], 8)); i += 3; continue; }
      const z = inhalt[++i]; bytes.push(...Buffer.from({ n: '\n', t: '\t', r: '\r' }[z] ?? z, 'utf8'));
    }
    p = Buffer.from(bytes).toString('utf8');
  }
  return p.replace(/^b\//, '');
}

// Gibt [{ regel, datei }] zurück, je Regel und Datei höchstens einmal, in Fundreihenfolge.
// Dateiköpfe gelten nur außerhalb eines Hunks; im Hunk zählen die Zeilen laut „@@ -a,b +c,d @@“ (ein „+++“ im Inhalt ist eine neue Zeile).
export function pruefeDiff(diff) {
  const befunde = []; const gesehen = new Set();
  let datei = ''; let alt = 0; let neu = 0;
  const melde = (regel, d) => { const k = `${regel}\0${d}`; if (!gesehen.has(k)) { gesehen.add(k); befunde.push({ regel, datei: d }); } };
  const kopfPfad = (p) => { if (PRUEF_DATEIEN.has(p) || basename(p) === '.gitattributes') melde('pruef-datei', p); };
  const binaer = (p) => { if (p !== '/dev/null' && istShellArtig(p, basename(p))) melde('nachladen', p); };
  let gitB = '';
  for (const zeile of String(diff).split('\n')) {
    if (alt > 0 || neu > 0) {
      const c = zeile[0];
      if (c === '+') {
        neu--;
        for (const regel of regelnFuer(datei, zeile.slice(1))) {
          const k = `${regel}\0${datei}`;
          if (!gesehen.has(k)) { gesehen.add(k); befunde.push({ regel, datei }); }
        }
        continue;
      }
      if (c === '-') { alt--; continue; }
      if (c === ' ' || zeile === '') { alt--; neu--; continue; }
      if (c === '\\') continue;
      alt = 0; neu = 0; // Hunk kürzer als angegeben: Zeile als Kopf behandeln
    }
    if (zeile.startsWith('diff --git ')) {
      datei = ''; gitB = (/ b\/(.*)$/.exec(zeile) || [])[1] ?? '';
      if (/^diff --git a\/(.*\/)?\.gitattributes /.test(zeile) || /\/\.gitattributes$|^\.gitattributes$/.test(gitB)) kopfPfad(gitB);
      for (const p of PRUEF_DATEIEN) if (zeile.startsWith(`diff --git a/${p} `) || zeile.endsWith(` b/${p}`)) kopfPfad(p);
      continue;
    }
    const bin = /^Binary files (.+) and (.+) differ$/.exec(zeile);
    if (bin) { binaer(pfad(bin[1]).replace(/^a\//, '')); binaer(pfad(bin[2])); continue; }
    if (zeile === 'GIT binary patch') { binaer(gitB); continue; }
    if (zeile.startsWith('rename from ') || zeile.startsWith('rename to ')) { kopfPfad(pfad(zeile.replace(/^rename (from|to) /, '')).replace(/^a\//, '')); continue; }
    if (zeile.startsWith('--- ')) { kopfPfad(pfad(zeile.slice(4)).replace(/^a\//, '')); continue; }
    if (zeile.startsWith('+++ ')) { datei = pfad(zeile.slice(4)); kopfPfad(datei); continue; }
    const h = /^@@ -\d+(?:,(\d+))? \+\d+(?:,(\d+))? @@/.exec(zeile);
    if (h) { alt = h[1] === undefined ? 1 : Number(h[1]); neu = h[2] === undefined ? 1 : Number(h[2]) }
  }
  return befunde;
}

if (process.argv[1] && import.meta.url === pathToFileURL(realpathSync(process.argv[1])).href) {
  const befunde = pruefeDiff(readFileSync(0, 'utf8'));
  if (!befunde.length) { console.log('PR-Prüfung: keine Befunde'); process.exit(0); }
  for (const b of befunde) console.log(`${b.regel}  ${JSON.stringify(b.datei)}  – ${REGELN[b.regel]}`);
  process.exit(1);
}
