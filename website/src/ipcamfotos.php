<?php

// http://sevenx.de/blog/tutorial-einfach-mit-php-ordner-auslesen-und-dateien-und-bilder-anzeigen/

// Ordnername
$ordner = "images/nanifoto"; //auch komplette Pfade m�glich ($ordner = "download/files";)
 
// Ordner auslesen und Array in Variable speichern
$alledateien = scandir($ordner, 1); // Sortierung A-Z
// Sortierung Z-A mit scandir($ordner, 1)                               

$anzahldateien = 0;
$anzahldateienmax = 0; 
// Schleife um Array "$alledateien" aus scandir Funktion auszugeben
// Einzeldateien werden dabei in der Variabel $datei abgelegt

?>

<?php 
foreach ($alledateien as $datei) 
{
    // Zusammentragen der Dateiinfo
    $dateiinfo = pathinfo($ordner."/".$datei);
    //Folgende Variablen stehen nach pathinfo zur Verf�gung
    // $dateiinfo['filename'] =Dateiname ohne Dateiendung  *erst mit PHP 5.2
    // $dateiinfo['dirname'] = Verzeichnisname
    // $dateiinfo['extension'] = Dateityp -/endung
    // $dateiinfo['basename'] = voller Dateiname mit Dateiendung
 
     if($anzahldateien == $anzahldateienmax && $dateiinfo['extension'] == "jpg")
    {
        // in the first loop build a link to the last picture
        echo '+"<p>Jeden Tag ein neues <b><a href='.$ordner."/".$dateiinfo['basename']." target='_blank'>".'Foto.</b></a></p>"';
        echo '+"<div id='; echo "'fotorahmen'>"; echo '"';
        echo '+"<div class='; echo "'galleria'>"; echo '"';
    }
 
    // Gr��e ermitteln zur Ausgabe
    $size = ceil(filesize($ordner."/".$datei)/1024);
    //1024 = kb | 1048576 = MB | 1073741824 = GB
 
    // scandir liest alle Dateien im Ordner aus, zus�tzlich noch "." , ".." als Ordner
    // Nur echte Dateien anzeigen lassen und keine "Punkt" Ordner
    // _notes ist eine Erg�nzung f�r Dreamweaver Nutzer, denn DW legt zur besseren Synchronisation diese Datei in den Orndern ab
    if ($dateiinfo['extension'] == "jpg") 
    {
    
        $anzahldateien++; ?>
        +"<img src='<?php echo $ordner."/".$dateiinfo['basename'] ?>'/>"                                            
<?php
    };    
};
?>    
    +"</div>" 

+"</div>",

