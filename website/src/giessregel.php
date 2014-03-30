<?php

//  Ein paar wichige Regeln zum Ermitteln des Seiteninhalts
// --------------------------------------------------------

// Festlegung ob, gegossen werden darf:
/* Es darf gegossen werden, wenn:
 * Es in den letzten 40 Minuten vom Arduino ein Lebenszeichen gab und wir davon ausgehen k�nnen, dass die
 * Verbindung zum Arduino intakt ist
 * Die letzte in der Datenbank gespeicherte Prozentfeuchte unter 40% liegt
 * Das Arduino keinen Wasser�berlauf gemeldet hat
 * Nicht gerade erst (also vor z.B. 3 Stunden) gegossen wurde (manchmal dauert es etwas bis das
 * Wasser bis ganz durchgesickert ist)
 * Wenn der letzte Versuch zu W�ssern nicht abgebrochen wurde, weil die Erde doch schon zu feucht war
 */

 /* Wann darf gegossen werden? 
  * 1. Wenn die Verbindung zwischen Webserver und Arduino besteht ($VerbindungArduino)
  * 2. Wenn der Wassertank voll ist. ($tankleer)
  * 3. Wenn die letzte W�sserung l�nger als die Gie�pause zur�ck liegt, und
  *     die Pause nicht durch eine Nachricht vom Arduino aufgehoben wurde ($PauseLetzteGiessung)
  * 4. Wenn der Topf nicht �bergelaufen ist.  ($KeinUeberlauf)
  * 5. Wenn die Feuchtigkeit geringer als 40% betr�gt  ($IstTrocken)
  */
   

 // f�r die Bew�sserungsregeln und zum Laden der Seiteninhalte aus der MySQL Datenbank -->
include ("src/dbabfrage.php");

$table = "plant_log";
include("src/db.php");

// Hier speichere ich das Ergebnis der Testl�ufe
// Darf �ber die Website gegossen werden?
$giessenerlaubt = false;   // Grunds�tzlich ist das Gie�en nicht erlaubt. Es kann
// ausgehend von den untenstehenden Regel jedoch erlaubt werden.
$VerbindungArduino = false; // Ich gehe zun�chst davon aus, dass die Verbindung besteht
// Die Pr�fungen unten k�nnen das dann aber widerlegen
$PauseLetzteGiessung = false; // Ich gehe davon aus, das die letzte Gie�ung gerade erst stattgefunden hat, 
// diese Annahme kann widergelgt werden
$KeinUeberlauf = false; // Ich gehe davon aus, dass kein Wasser im �bertopf ist 
$IstTrocken = false; // Ich gehe davon aus, dass die Erde nicht trocken ist

// wie sp�t ist es eigentlich?
$now = date("Y-m-d H:i:s");

if ($debug){echo "now: $now <br />";}

  
// 1. Test: Besteht die Verbindung zum Arduino noch?
    // Wann gab es das letzte Lebenszeichen und liegt das weniger als 40 Minuten in der Vergangenheit?
    $lebenszeichenpause = "-40 minutes";
    $pausezeit = strtotime($lebenszeichenpause);
    $pausezeit = date("Y-m-d H:i:s", $pausezeit);
    
    if ($debug)
    {
        echo "lebenszeichenpause: $lebenszeichenpause <br />";
        echo "pausezeit: $pausezeit <br />";
        echo "letztes Lebenszeichen: $letztes_Lebenszeichen <br />";
    }
    
    if ($letztes_Lebenszeichen > $pausezeit)
    {
        if ($debug)
        {
            echo "Die Verbindung zum Arduino besteht noch.<br />";
        }
    
        $VerbindungArduino = true; // Ja, die Verbindung zum Arduino steht noch. Das werte ich sp�ter aus.
    }
    else
    {
        // F�r Logzwecke wird diese Nichterreichbarkeit in die MySQL Datenbank eingetragen
        // http://stackoverflow.com/questions/1995562/now-function-in-php
        $timestamp = date("Y-m-d H:i:s");
        $name = "Banane";
        $type = "Verbindung";
        $value = 0;
    
        $sql = "
          INSERT INTO $table
          (
          sensorname , logtype , value , timestamp
          )
          VALUES
          (
          '$name', '$type', $value, '$timestamp'
          )
        ";
        $db_erg = mysqli_query($db_link, $sql)
            or die("<p>INSERT fehlgeschlagen</p>" . mysqli_error($db_link));        
    }

// 2. Test: Ist der Wassertank noch voll?
    // Teststufe 0
    // Ist noch Wasser im Wassertank?
    // Zu Testzwecken kann der Wert manipuliert werden
    // $tankleer = false;
    // $tankleer wird aus der dbabfrage.php eingebunden 
    
    if($debug)
    {
        echo "Tankmesswert: ", $tankmesswert, "<br />";
        echo "Ist der Wassertank leer?: ";
        if ($tankleer)
        {
            echo "ja<br />";
        }
        else if (!$tankleer)
        {
            echo "nein<br />";
        }
        else
        {
            echo "tankleer kann nicht ermittelt werden <br />";
        }
    }

 /* 3. Wenn die letzte W�sserung l�nger als die Gie�pause zur�ck liegt, oder
  *     die Pause durch eine Nachricht vom Arduino aufgehoben wurde*/
    // Teststufe 3
    // Wurde in den letzten Stunden bereits gegossen?
    
    //http://www.ayom.com/topic-7692.html
    //http://de.php.net/strtotime
    $giesspause = "-1 hours";
    $pausezeit = strtotime($giesspause);
    $pausezeit = date("Y-m-d H:i:s", $pausezeit);
    
    if ($debug)
    {
        echo "giesspause: $giesspause <br />";
        echo "pausezeit: $pausezeit <br />";
        echo "letzte Gie&szlig;ung: $letzte_Giessung <br />";
        echo "letzte Nachrichten ID der Pflanze: $lastmsgid <br />";
    }
    
    // Oder war die die letzte Nachricht der Pflanze, dass die W�sserung nicht ausreichte?
    // Die Nachrichten sind in der MySQL Tabelle gespeichert.
    if ($letzte_Giessung < $pausezeit)
    {
        if ($debug)
        {
            echo "Die letzte Gie&szlig;ung liegt l&auml;nger als $giesspause zur&uuml;ck <br />";
        }
        
        $PauseLetzteGiessung = true; 
    }
    
    //$lastmsgid = 6;
    if ($lastmsgid == 6 || $lastmsgid == 49)
    {
        if ($debug)
        {
            echo "Die letzte Nachricht wollte mehr Wasser <br />";
        }   
        
        $PauseLetzteGiessung = true;             
    }


// 4. Wenn der Topf nicht �bergelaufen ist. 
    // Es muss voher noch gepr�ft werden, ob schon Wasser im �bertopf ist
    // Ein messwert gr��er als 50 wird als relevante Wassermenge interpretiert
    if ($Topfwert < $ueberlaufwert)
    {
        if ($debug)
        {
            echo "Es ist kein Wasser im &Uuml;bertopf. <br />";
        }
        
        $KeinUeberlauf = true; // Es ist kein Wasser im �bertopf, also kann weiter gepr�ft werden
    }
        
// 5. Wenn die Feuchtigkeit geringer als 40% betr�gt  
    // Wie feucht ist die Erde? Ist sie feuchter als 40%?
    //$Feuchte = 39;
    if ($Feuchte < 40)
    {
        if ($debug)
        {
            echo "Die Erde ist jetzt trocken. <br />";
        }
            
        $IstTrocken = true; 
    }
  
// Hier findet die eigentlich Pr�fung statt, ob wieder gegossen werden darf
if ($VerbindungArduino && !$tankleer && $PauseLetzteGiessung && $KeinUeberlauf && $IstTrocken)
{
    $giessenerlaubt = true; 
}

 // Hier schreibe im debug-Modus das Ergebnis der Teststufen rein:
if($debug)
{
    echo "ist das Gie&szlig;en wieder erlaubt? ";
    if ($giessenerlaubt == true)
    {
        echo "JA<br />";
    }
    else if($giessenerlaubt == false)
    {
        echo "NEIN <br />";
    }
    else 
    {
        echo "Es wurde kein sinnvoller Wert f&uuml;r die Gie&szlig;erlaubnis ermittelt.<br />
        der Wert lautet: $giessenerlaubt.";
    }
}
?>