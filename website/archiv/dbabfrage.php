	<?php

    // Switch the Teststate
    // ------------------------
    $dbtest = false; // Decide if the database should store in testmode or in productivemode 
    
        //Datenbank-Verbindung herstellen
        //------------------------------
        if ($dbtest){
            include ("src/db_test.php");
            $tabelle = "plant_log_test";
            $msgtabelle = "plant_messages_test";            
        }
        else
        {
            include ("src/db.php");
            $tabelle = "plant_log";
            $msgtabelle = "plant_messages";            
        }

        // Ermittlung der Lufttemperatur
        $sql = "
        SELECT $tabelle.value
        FROM $tabelle
        WHERE ((($tabelle.sensorname)='Temperatur') AND (($tabelle.logtype)='Messwert'))
        ORDER BY $tabelle.ID DESC LIMIT 1
        ";

        $db_erg = mysqli_query( $db_link, $sql );
        if ( ! $db_erg )
        {
            die('Ungültige Abfrage: ' . mysqli_error($db_link));
        }

        while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
        {
            $temperatur = $row['value'];
            //echo "aktuelle Temperatur: ", $value,"<br /><br />";
        }

        // Ermittlung der Bodenfeuchte der Bananenpflanze
        $sql = "
        SELECT $tabelle.value
        FROM $tabelle
        WHERE ((($tabelle.sensorname)='Banane') AND (($tabelle.logtype)='Prozentfeuchte'))
        ORDER BY $tabelle.ID DESC LIMIT 1
        ";

        $db_erg = mysqli_query( $db_link, $sql );
        if ( ! $db_erg )
        {
            die('Ungültige Abfrage: ' . mysqli_error($db_link));
        }

        while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
        {
            $Feuchte = $row['value'];
            //echo "aktuelle Temperatur: ", $value,"<br /><br />";
        }

        // Ermittlung, ob Wasser im Übertopf ist
        $sql = "
        SELECT $tabelle.value
        FROM $tabelle
        WHERE ((($tabelle.sensorname)='Banane_Topf') AND (($tabelle.logtype)='Messwert'))
        ORDER BY $tabelle.ID DESC LIMIT 1
        ";

        $db_erg = mysqli_query( $db_link, $sql );
        if ( ! $db_erg )
        {
            die('Ungültige Abfrage: ' . mysqli_error($db_link));
        }

        while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
        {
            $Topfwert = $row['value'];
            //echo "aktuelle Temperatur: ", $value,"<br /><br />";
        }

        // Ermittlung des letzten Datenbankeintrags
        $sql = "
        SELECT $tabelle.timestamp
        FROM $tabelle
        ORDER BY $tabelle.ID DESC LIMIT 1
        ";

        $db_erg = mysqli_query( $db_link, $sql );
        if ( ! $db_erg )
        {
            die('Ungültige Abfrage: ' . mysqli_error($db_link));
        }

        while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
        {
            $letztes_Lebenszeichen = $row['timestamp'];
            //echo "aktuelle Temperatur: ", $value,"<br /><br />";
        }

        // Ermittlung wann das letzte mal eine Wässerung durch das Arduino in die
        // Datenbank eingetragen wurde

        $sql = "
        SELECT $tabelle.timestamp
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
            $letzte_Giessung = $row['timestamp'];
            //echo "aktuelle Temperatur: ", $value,"<br /><br />";
        }

        // Wie lautete die letzte Nachricht, die die Banane gesendet hat?
        // Um das zu ermitteln, müssen wir zuerst die Nachrichten-ID der letzten Nachricht ermitteln
        $sql = "
        SELECT $tabelle.value
        FROM $tabelle
        WHERE ((($tabelle.sensorname)='Banane') AND (($tabelle.logtype)='Nachricht'))
        ORDER BY $tabelle.ID DESC LIMIT 1
        ";

        $db_erg = mysqli_query( $db_link, $sql );
        if ( ! $db_erg )
        {
            die('Ungültige Abfrage: ' . mysqli_error($db_link));
        }

        while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
        {
                $msgid = $row['value'];
                //echo "aktuelle Temperatur: ", $value,"<br /><br />";
        }

        // Diese Nachrichten-ID nutzen wir, um die entsprechende Nachricht aus der Datenbank abzufragen
        $sql = "
        SELECT $msgtabelle.msg
        FROM $msgtabelle
        WHERE ((($msgtabelle.msgid)=$msgid))
        ";

        $db_erg = mysqli_query( $db_link, $sql );
        if ( ! $db_erg )
        {
            die('Ungültige Abfrage: ' . mysqli_error($db_link));
        }

        while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
        {
            $msg = $row['msg'];
            //echo "aktuelle Temperatur: ", $value,"<br /><br />";
        }

        // Wann hat die Banane das letzte Mal eine Nachricht verschickt?
        $sql = "
        SELECT $tabelle.timestamp
        FROM $tabelle
        WHERE ((($tabelle.sensorname)='Banane') AND (($tabelle.logtype)='Nachricht'))
        ORDER BY $tabelle.ID DESC LIMIT 1
        ";

        $db_erg = mysqli_query( $db_link, $sql );
        if ( ! $db_erg )
        {
            die('Ungültige Abfrage: ' . mysqli_error($db_link));
        }

        while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
        {
            $msgtime = $row['timestamp'];
            //echo "Zeit der letzten Nachricht ", $msgtime,"<br /> ";
        }
        
        // Wann ist das letzte mal das Wasser bin in den Übertopf glaufen?
        // Als Wasserüberlauf gilt alles, was einen höhren Feuchtigkeitsmesswert als 50 hat
        $ueberlaufwert = 50;
        $sql = "
        SELECT $tabelle.timestamp
        FROM $tabelle
        WHERE ((($tabelle.sensorname)='Banane_Topf') AND (($tabelle.logtype)='Messwert') AND (($tabelle.value)>$ueberlaufwert))
        ORDER BY $tabelle.ID DESC LIMIT 1
        ";

        $db_erg = mysqli_query( $db_link, $sql );
        if ( ! $db_erg )
        {
        die('Ungültige Abfrage: ' . mysqli_error($db_link));
        }

        while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
        {
            $ueberlaufzeit = $row['timestamp'];
            //echo "&Uuml;berlaufwert: ", $ueberlaufwert,"<br /> ";
            //echo "letzer &Uuml;berlauf fand statt am: ", $ueberlaufzeit,"<br />";
        }

        // Ermittle den letzten Wert des Wassertanksensors, um zu entscheiden, ob noch genug Wasser im Tank ist.
        $sql = "
        SELECT $tabelle.value
        FROM $tabelle
        WHERE ((($tabelle.sensorname)='Wassertank') AND (($tabelle.logtype)='Messwert'))
        ORDER BY $tabelle.ID DESC LIMIT 1
        ";

        $db_erg = mysqli_query( $db_link, $sql );
        if ( ! $db_erg )
        {
        die('Ungültige Abfrage: ' . mysqli_error($db_link));
        }

        while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
        {
            $tankmesswert = $row['value'];        
        }
        //$tankmesswert = 1000;
        //echo "Tankmesswert: ", $tankmesswert,"<br /> ";
        if ($tankmesswert < 50)
        {
            $tankleer = true;
        }
        else
        {
            $tankleer = false;
        }
        

     ?>
