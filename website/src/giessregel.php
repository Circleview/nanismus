<?php

//  Ein paar wichige Regeln zum Ermitteln des Seiteninhalts
// --------------------------------------------------------

// Festlegung ob, gegossen werden darf:
/* Es darf gegossen werden, wenn:
 * Es in den letzten 40 Minuten vom Arduino ein Lebenszeichen gab und wir davon ausgehen können, dass die
 * Verbindung zum Arduino intakt ist
 * Die letzte in der Datenbank gespeicherte Prozentfeuchte unter 40% liegt
 * Das Arduino keinen Wasserüberlauf gemeldet hat
 * Nicht gerade erst (also vor z.B. 3 Stunden) gegossen wurde (manchmal dauert es etwas bis das
 * Wasser bis ganz durchgesickert ist)
 * Wenn der letzte Versuch zu Wässern nicht abgebrochen wurde, weil die Erde doch schon zu feucht war
 */
 // für die Bewässerungsregeln und zum Laden der Seiteninhalte aus der MySQL Datenbank -->
include ("src/dbabfrage.php");

$table = "plant_log";
include("src/db.php");  

    // Hier speichere ich das Ergebnis der Testläufe
    // Darf über die Website gegossen werden?
    $giessenerlaubt = false;
    $VerbindungArduino = true; // Ich gehe zunächst davon aus, dass die Verbindung besteht
    // Die Prüfungen unten können das dann aber widerlegen

    // wie spät ist es eigentlich?
    $now = date("Y-m-d H:i:s");

    if ($debug){echo "now: $now <br />";}

    // Teststufe 0
    // Ist noch Wasser im Wassertank?
    // Zu Testzwecken kann der Wert manipuliert werden
    // $tankleer = false;

    if($debug){
        echo "Tankmesswert: ", $tankmesswert, "<br />";
        echo "tankleer: ", $tankleer,"<br />"; 
        echo "Ist der Wassertank leer?: ";
        if ($tankleer){
            echo "JA<br />";
        }
        else
        {
            echo "NEIN<br />";
        }
    }

    if ($tankleer) // Ohne Wasser keine Wässerung
    {
        $giessenerlaubt = false;
    }

    //if (!$tankleer)  // Wenn noch Wasser im Tank ist muss weiter geprüft werden
    //{
                       
        // Teststufe 1
   
        // Wann gab es das letzte Lebenszeichen und liegt das weniger als 40 Minuten in der Vergangenheit?
        $lebenszeichenpause = "-40 minutes";
        $now = strtotime($lebenszeichenpause);
        $stichzeit = date("Y-m-d H:i:s", $now);
    
        if ($debug)
        {
            echo "now: $now <br />";
            echo "lebenszeichen pause: $lebenszeichenpause <br />";
            echo "stichzeit: $stichzeit <br />";
            echo "letztes Lebenszeichen: $letztes_Lebenszeichen <br />";
        }

        if ($letztes_Lebenszeichen > $stichzeit)
        {
            if ($debug)
            {
                echo "liegt k&uuml;rzer als $lebenszeichenpause zur&uuml;ck <br />"; 
            }
    
            $VerbindungArduino = true; // Ja, die Verbindung zum Arduino steht noch. Das werte ich später aus.
                
            // Teststufe 2
            // Wie feucht ist die Erde? Ist sie feuchter als 40%?
            // Oder war die die letzte Nachricht der Pflanze, dass die Wässerung nicht ausreichte?
            // Die Nachrichten sind in der MySQL Tabelle gespeichert.
            if ($Feuchte < 40)
            {
                // Die Erde ist Trocken. Also könnte gegossen werden
                // Es muss voher noch geprüft werden, ob schon Wasser im Übertopf ist
                // Ein messwert größer als 50 wird als relevante Wassermenge interpretiert
                if ($Topfwert < $ueberlaufwert)
                {
                    // Es ist kein Wasser im Übertopf, also kann weiter geprüft werden
    
                    // Teststufe 3
                    // Wurde in den letzten Stunden bereits gegossen?
    
                    //http://www.ayom.com/topic-7692.html
                    //http://de.php.net/strtotime
                    $giesspause = "-1 hours";
                    $now2 = strtotime($giesspause);
    
                    $stichzeit = date("Y-m-d H:i:s", $now2);
    
                    if ($debug){
                        echo "now: $now <br />";
                        echo "giesspause: $giesspause <br />";
                        echo "stichzeit: $stichzeit <br />";
                        echo "letzte Gie&szlig;ung: $letzte_Giessung <br />";
                        echo "letzte Nachrichten ID der Pflanze: $lastmsgid <br />";
                    }
    
                    // Es darf nur gegossen werden, wenn oben der Wassertank 
                    // nicht leer ist und eine Gießung daher schon ausgeschlossen ist
                    // Oder war die die letzte Nachricht der Pflanze, dass die Wässerung nicht ausreichte?
                    // Die Nachrichten sind in der MySQL Tabelle gespeichert.                    
                    if ($letzte_Giessung < $stichzeit || $lastmsgid == 6 || $lastmsgid = 49)
                    {
                        if ($debug)
                        {
                            if ($letzte_Giessung < $stichzeit)
                            {
                                echo "liegt l&auml;nger als $giesspause zur&uuml;ck <br />";
                            }
                            else if ($lastmsgid == 6 || $lastmsgid = 49)
                            {
                                echo "Die letzte Nachricht wollte mehr Wasser <br />";
                            }
                        }
                        
                        // Wenn nicht bei der Wassertankprüfung bereits festgestellt wurde, 
                        // dass eine Gießung nicht erlaubt ist, dann wird die Gießung an 
                        // dieser Stelle erlaubt
                        // Zu Testzwecken können die Ergebnisse der Datenbankabfrage hier manipuliert werden
                        //$Feuchte = 25;
                    
                        if ($tankleer) // Ohne Wasser keine Wässerung
                        {
                            $giessenerlaubt = false;
                        } 
                        else
                        {
                            // Es darf schon wieder gegeossen werden
                            $giessenerlaubt = true;                            
                        }                           
                    }
                    else if ($letzte_Giessung >= $stichzeit) 
                    {
                        if ($debug)
                        {
                            echo "liegt noch nicht l&auml;nger als $giesspause zu&uuml;ck <br />";
                        }
                        // da gerade erst gegossen wurde, sollte nicht noch einmal gegossen werden
                        $giessenerlaubt = false;
                    }
                    else {
                        if ($debug)
                        {
                            echo "Die Berechnung kommt nicht zu einem sinnvollen Ergebnis. <br />";
                        }
                        // Wenn hier etwas mit den Datumswerten nicht stimmt, dann ist ein Fehler im Programm
                        // Das Gießen solle sicherheitshalber nicht angeboten werden.
                        $giessenerlaubt = false;
                    }
    
                }
                else 
                {
                    // Es scheint Wasser im Übertopf zu sein. Eine neue Wässerung ist so ausgeschlossen
                    $giessenerlaubt = false;
                    }
            }
        }
        else if ($letztes_Lebenszeichen <= $stichzeit) 
        {
            // Die Verbindung zum Arduino Board scheint nicht mehr aktiv zu sein.
            // Das Gießen über die Website ist so nicht möglich
            // Es wird auch keine Möglichkeit anboten zu Wässern
            if ($debug)
            {
                echo "liegt noch nicht k&uuml;rzer als $lebenszeichenpause zu&uuml;ck <br />";
            }
            $giessenerlaubt = false;
            $VerbindungArduino = false; // Die Verbindung zum Arduino ist gerissen, das werte ich in der Index.php aus.
            
            // Für Logzwecke wird diese Nichterreichbarkeit in die MySQL Datenbank eingetragen
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
                or die("<p>Anfrage fehlgeschlagen</p>" . mysqli_error($db_link));
                    
        }
        else 
        {
            echo "Die Berechnung kommt nicht zu einem sinnvollen Ergebnis. <br />";
            // Wenn hier etwas mit den Datumswerten nicht stimmt, dann ist ein Fehler im Programm
            // Das Gießen solle sicherheitshalber nicht angeboten werden.
            $giessenerlaubt = false;
        }
    //}

    // Hier schreibe im debug-Modus das Ergebnis der Teststufen rein:
    if($debug)
    {
        echo "ist das Gie&szlig;en wieder erlaubt? ";
        if ($giessenerlaubt == true){
            echo "JA<br />";
        }
        else if($giessenerlaubt == false){
            echo "NEIN <br />";
        }
        else {
            echo "Es wurde kein sinnvoller Wert f&uuml;r die Gie&szlig;erlaubnis ermittelt.<br />
            der Wert lautet: $giessenerlaubt.";
        }
    }

?>