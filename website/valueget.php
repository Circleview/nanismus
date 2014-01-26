<?php
// Autor: Jens Leopold 
//
// Kontakt: info@jleopold.de
//
//
// P r o j e k t: Arduino-Aquarium-Temperatur-�berwachung
// -------------------------------------------------------
//
// PHP-Skript, welches von Arduino benutzt wird, um Werte in die mySQL-Datenbank einzutragen
//
// werden keine Daten per "?TEMP=......" �bergeben, wird der aktuellste Wert aus der Datenbank angezeigt
//

// Festlegen welche Datenbank verwendet werden soll
$test = false;  // bei true werden die Daten in die Testdatenbank geschrieben

// Zum Twittern der Nachrichten des Arduino, wird eine Funktion
// eingebunden, die sich mit Twitter via oAuth verbindet
include ("src/twittersend.php");

// f�r die Bew�sserungsregeln und zum Laden der Seiteninhalte aus der MySQL Datenbank -->
//include ("src/dbabfrage.php");

$tabelle = "plant_log";
$msgtabelle = "plant_messages";

//Datenbank-Verbindung herstellen
//--------------------------------
include("src/db.php"); 
            
        $key = "c3781633f1fb1ddca77c9038d4994345"; // Key zum Schreiben in die MY SQL Datenbank
        if ($test)
        {
            $twitteruser = '@NanismusKW';  // Im Debug Mode soll Frau K nicht bel�stigt werden                         
        }
        else
        {         
            $twitteruser = '@kklessmann';                                  
        }

        // Erhaltene Daten des Arduino Http Get in die MySQL Datenbank speichern
                
        // http://nanismus.no-ip.org/nanismus_test/valueget.php?name=Banane&type=status&value=99&key=c3781633f1fb1ddca77c9038d4994345
             
        if ((isset($_GET['name'])) and (isset($_GET['type'])) and (isset($_GET['value'])) and (($_GET['key']) == $key)) 
        {	// Wenn 'TEMP' �bergeben wurde und key stimmt...
        	$name = ($_GET['name']);
        	$type = ($_GET['type']);
            $value = ($_GET['value']);
        	//echo $value;
        	//$eintragen = mysql_query("INSERT INTO plantmeasures_test (value,timestamp)	VALUES ($value, NOW())");	// TEMP real �bergeben, DATE = automatischer SQL-Befehl (NOW)
        
        // http://stackoverflow.com/questions/1995562/now-function-in-php
        $timestamp = date("Y-m-d H:i:s");
        $sql = "
          INSERT INTO $tabelle
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
            
            echo "Dateneingabe erfolgreich <br>";
        }
        else
        {
            echo "Dateneingabe fehlerhaft <br>";
        }
        
        if ($type == "Nachricht")
        {
            echo "Nachricht wurde eingegeben<br><br>";
            // Der Nachrichtenwert vom Arduino ist nur ein Nachrichten-Ident
            // Also muss die Nachricht als n�chstes decodiert werden
            
            // Dazu nutzen wir die Nachrichten ID, die das Arduino sendet
            // Diese Nachrichten-ID nutzen wir, um die entsprechende Nachricht aus der Datenbank abzufragen
            $messageID = $value;
            
            $sql = "
            SELECT $msgtabelle.msg
            FROM $msgtabelle
            WHERE (($msgtabelle.msgid=$messageID))
            ";  

            $db_erg = mysqli_query( $db_link, $sql );
            if ( ! $db_erg )
            {
                die('Ung�ltige Abfrage: ' . mysqli_error($db_link));
            }

            while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
            {
                $message = $row['msg'];
            }            

            // nur bei bestimmten Nachrichten soll noch ein User angesprochen werden
            // und eine URL angef�gt werden
            // wenn ich das hier nicht filtere und mit den Nachrichten abspeichere,
            // dann steht das auch auf der Website und das ist unsch�n
            if ($messageID == 2 || $messageID == 3 || $messageID == 6 || $messageID == 13 || $messageID == 49)
            {
                $user = $twitteruser;
                $url = ''; // Zur Zeit blockiert twitter Nachrichten mit der nanismus website
                // mal gucken, ob ich die Adresse irgendwann wieder verwenden kann. 
                // bis dahin muss die URL leider drau�en bleiben.
                // Den Twitter Support habe ich am 26.01.2014 angeschrieben und um Entsperrung gebeten
                //$url = "http://nanismus.de";
            }
            else
            {
                $user = ''; // Zeige einfach keinen User
                $url = '';  // Zeige einfach keine URL
            }
            
            // Bei Nachrichten, die sich auf eine W�sserung beziehen sollen die W�sserungsmenge 
            // im Tweet enthalten 
            // Hier kann man die die Wassermenge manipulieren, um der Filterung von 
            // doppelten Tweets vorzubeugen
            //$menge = 350;

            // Wenn die MsgID eine Nachricht enth�lt, die sagt, dass gew�ssert wurde
            // dann soll die Gie�menge noch mit ausgegeben und der eigentlichen Nachricht angef�gt werden
            if ($messageID == 4  || $messageID == 6 ||
            $messageID == 47 || $messageID == 49 || $messageID == 68 || $messageID == 69)
            {
            
                // Wie lauteten die letzten Nachricht, die die Banane gesendet hat?
                // Um das zu ermitteln, m�ssen wir zuerst die Nachrichten-ID der letzten Nachricht ermitteln
                // Wann hat die Banane das letzte Mal eine Nachricht verschickt?
                $msgcount = 1; // Anzahl der Nachrichten aus der Vergangenheit anzeigen
                // 100 und 99 werden als Nachrichten ausgeklammert, da diese keine echten Nachrichten sind
                $sql = "
                SELECT $tabelle.value, $tabelle.timestamp
                FROM $tabelle
                WHERE (($tabelle.sensorname = 'Banane') AND ($tabelle.logtype = 'Nachricht') AND ($tabelle.value != 100) AND ($tabelle.value != 99))
                ORDER BY $tabelle.ID DESC LIMIT $msgcount
                ";
        
                $db_erg = mysqli_query( $db_link, $sql );
                if ( ! $db_erg )
                {
                    die('Ung�ltige Abfrage: ' . mysqli_error($db_link));
                }
        
                while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
                {
                    //$msgid = $row['value'];
                    $msgtime = $row['timestamp'];
                    //echo "msgtime: ". $msgtime."<br />";
                    //echo "msgid: ". $msgid."<br />";
                }
            
                $sql = "
                SELECT $tabelle.value
                FROM $tabelle
                WHERE ((($tabelle.sensorname)='Banane') AND (($tabelle.logtype)='Giessmenge') AND (($tabelle.timestamp) <= '$msgtime'))
                ORDER BY $tabelle.ID DESC LIMIT 1
                ";

                $db_erg = mysqli_query( $db_link, $sql );
                if ( ! $db_erg )
                {
                    die('Ung�ltige Abfrage: ' . mysqli_error($db_link));
                }

                while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
                {
                    $menge = $row['value'];
                    //echo "aktuelle Menge: ". $menge."<br /><br />";
                }
            }
            
            switch ($messageID) 
            {
                case 6:
                case 49: 
                    $message = $message." ".$menge." ml reichten nicht.";
                    break;
                case 68:
                case 69:
                    $message = $message." ".$menge." ml waren zu viel.";
                    break;
                case 4:
                case 47:
                    $message = $message." ".$menge." ml waren genau was ich brauchte.";
                    break;
                default: 
                    break;
            }                 

            echo "Nachricht: ", $message.' '.$user.' '.$url,"<br /><br />";
                      
            // Es wird nun eine Funktion aufgerufen, die oben eingebunden
            // wurde, und die eine Nachricht an Twitter sendet
            postSignupToTwitter($message, $user, $url, $test);
        }
?>