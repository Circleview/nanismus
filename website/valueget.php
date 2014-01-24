<?php
// Autor: Jens Leopold 
//
// Kontakt: info@jleopold.de
//
//
// P r o j e k t: Arduino-Aquarium-Temperatur-Überwachung
// -------------------------------------------------------
//
// PHP-Skript, welches von Arduino benutzt wird, um Werte in die mySQL-Datenbank einzutragen
//
// werden keine Daten per "?TEMP=......" übergeben, wird der aktuellste Wert aus der Datenbank angezeigt
//

// Festlegen welche Datenbank verwendet werden soll
$test = false;  // bei true werden die Daten in die Testdatenbank geschrieben

// Zum Twittern der Nachrichten des Arduino, wird eine Funktion
// eingebunden, die sich mit Twitter via oAuth verbindet
include ("src/twittersend.php");

// für die Bewässerungsregeln und zum Laden der Seiteninhalte aus der MySQL Datenbank -->
include ("src/dbabfrage.php");

$table = "plant_log";
$msgtabelle = "plant_messages";

//Datenbank-Verbindung herstellen
//--------------------------------
include("src/db.php"); 
            
        $key = "c3781633f1fb1ddca77c9038d4994345"; // Key zum Schreiben in die MY SQL Datenbank
        if ($test)
        {
            $twitteruser = '@NanismusKW';  // Im Debug Mode soll Frau K nicht belästigt werden                         
        }
        else
        {         
            $twitteruser = '@kklessmann';                                  
        }

        // Erhaltene Daten des Arduino Http Get in die MySQL Datenbank speichern
                
        // http://nanismus.no-ip.org/nanismus_test/valueget.php?name=Banane&type=status&value=99&key=c3781633f1fb1ddca77c9038d4994345
             
        if ((isset($_GET['name'])) and (isset($_GET['type'])) and (isset($_GET['value'])) and (($_GET['key']) == $key)) 
        {	// Wenn 'TEMP' übergeben wurde und key stimmt...
        	$name = ($_GET['name']);
        	$type = ($_GET['type']);
            $value = ($_GET['value']);
        	//echo $value;
        	//$eintragen = mysql_query("INSERT INTO plantmeasures_test (value,timestamp)	VALUES ($value, NOW())");	// TEMP real übergeben, DATE = automatischer SQL-Befehl (NOW)
        
        // http://stackoverflow.com/questions/1995562/now-function-in-php
        $timestamp = date("Y-m-d H:i:s");
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
            // Also muss die Nachricht als nächstes decodiert werden
            
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
                die('Ungültige Abfrage: ' . mysqli_error($db_link));
            }

            while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
            {
                $message = $row['msg'];
            }            

            // nur bei bestimmten Nachrichten soll noch ein User angesprochen werden
            // und eine URL angefügt werden
            // wenn ich das hier nicht filtere und mit den Nachrichten abspeichere,
            // dann steht das auch auf der Website und das ist unschön
            if ($messageID == 2 || $messageID == 3 || $messageID == 6 || $messageID == 13 || $messageID == 49)
            {
                $user = $twitteruser;
                $url = 'http://nanismus.de';
            }
            else
            {
                $user = ''; // Zeige einfach keinen User
                $url = '';  // Zeige einfach keine URL
            }
            
            // Bei Nachrichten, die sich auf eine Wässerung beziehen sollen die Wässerungsmenge 
            // im Tweet enthalten 
            // Hier kann man die die Wassermenge manipulieren, um der Filterung von 
            // doppelten Tweets vorzubeugen
            //$menge = 350;
            
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