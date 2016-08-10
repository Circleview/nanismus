<?php
    
    // Idee von
    // Autor: Jens Leopold
    //
    // Kontakt: info@jleopold.de
    //
    //
    // P r o j e k t: Arduino-Aquarium-Temperatur-†berwachung
    // -------------------------------------------------------
    //
    // PHP-Skript, welches von Arduino benutzt wird, um Werte in die mySQL-Datenbank einzutragen
    //
    // werden keine Daten per "?TEMP=......" übergeben, wird der aktuellste Wert aus der Datenbank angezeigt
    //
    
    // Festlegen welche Datenbank verwendet werden soll
    $test = false;  // bei true werden die Daten in die Testdatenbank geschrieben
    
    $tabelle = "plant_log";
    
    
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
    {	// Wenn 'TEMP' Ÿbergeben wurde und key stimmt...
        
        $name = ($_GET['name']);
        $type = ($_GET['type']);
        $value = ($_GET['value']);
        
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
        
        //http://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol
        $db_erg = mysqli_query($db_link, $sql)
        or die("<p>Anfrage fehlgeschlagen</p>" . mysqli_error($db_link));
        
        ?>
<!DOCTYPE html>
<html>
<body>

<?php echo "<p>Dateneingabe erfolgreich</p>"; ?>

</body>
</html>
<?php
    }
    else
    {
        echo "Dateneingabe fehlerhaft <br>";
        //echo "0 \r\n";
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
            $url = 'http://nanismus.de'; // war mal eine Zeit blockiert
            // mal gucken, ob ich die Adresse jetzt wieder verwenden kann.
            // bis dahin muss die URL leider draußen bleiben.
            // Den Twitter Support habe ich am 26.01.2014 angeschrieben und um Entsperrung gebeten
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
        // $menge = 350;
        
        // Wenn die MsgID eine Nachricht enthält, die sagt, dass gewässert wurde
        // dann soll die Gießmenge noch mit ausgegeben und der eigentlichen Nachricht angefügt werden
        if ($messageID == 4  || $messageID == 6 ||
            $messageID == 47 || $messageID == 49 || $messageID == 68 || $messageID == 69)
        {
            // Frage die letzte Gießmenge in der Datenbank ab
            $sql = "
            SELECT $tabelle.value
            FROM $tabelle
            WHERE ((($tabelle.sensorname)='Banane') AND (($tabelle.logtype)='Giessmenge'))
            ORDER BY $tabelle.ID DESC LIMIT 1
            ";
            
            $db_erg = mysqli_query( $db_link, $sql );
            if ( ! $db_erg )
            {
                die('Ungültige Abfrage: ' . mysqli_error($db_link));
            }
            
            while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
            {
                $menge = $row['value'];
                //echo "aktuelle Menge: ". $menge."<br /><br />";
            }
            
            // Für den unwahrscheinlichen Fall, dass gar kein Wert in der Datenbank steht
            // Nimm einfach die normale Nachricht ohne Zusatzinformationen
            if (!empty($menge))
            {
                // Entscheide anhand der MessageID welchen Nachrichtenzusatz die
                // Twitternachricht erhalten soll.
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
            }
        }
        
        echo "Nachricht: ", $message.' '.$user.' '.$url,"<br /><br />";
        
        // Es wird nun eine Funktion aufgerufen, die oben eingebunden
        // wurde, und die eine Nachricht an Twitter sendet
        postSignupToTwitter($message, $user, $url, $test);
    }
    ?>