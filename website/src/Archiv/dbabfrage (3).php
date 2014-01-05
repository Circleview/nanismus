	<?php

    // Switch the Teststate
    // ------------------------
    $dbtest = $test; // debug comes from the index page
    //$dbtest = true; // Decide if the database should store in testmode or in productivemode 
    
        //Datenbank-Verbindung herstellen
        //------------------------------
        include ("src/db.php");
        $tabelle = "plant_log";
        $msgtabelle = "plant_messages";        

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

        // Wie lauteten die letzten Nachricht, die die Banane gesendet hat?
        // Um das zu ermitteln, müssen wir zuerst die Nachrichten-ID der letzten Nachricht ermitteln
        // Wann hat die Banane das letzte Mal eine Nachricht verschickt?
        $msgcount = 4; // Anzahl der Nachrichten aus der Vergangenheit anzeigen
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
            die('Ungültige Abfrage: ' . mysqli_error($db_link));
        }

        $i = 0;
        while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
        {
            $msgid = $row['value'];
            $msgtime[$i] = $row['timestamp'];
            //echo "aktuelle Temperatur: ", $value,"<br /><br />";
                
            // Diese Nachrichten-ID nutzen wir, um die entsprechende Nachricht aus der Datenbank abzufragen
            $sql2 = "
            SELECT $msgtabelle.msg
            FROM $msgtabelle
            WHERE (($msgtabelle.msgid=$msgid))
            ";
    
            $db_erg2 = mysqli_query( $db_link, $sql2 );
            if ( ! $db_erg2 )
            {
                die('Ungültige Abfrage: ' . mysqli_error($db_link));
            }

            // Wenn die MsgID eine Nachricht enthält, die sagt, dass gewässert wurde)
            // dann soll die Gießmenge noch mit ausgegeben und der eigentlichen Nachricht angefügt werden
            if ($msgid == 4 || $msgid == 5 || $msgid == 6 ||
                $msgid == 47 || $msgid == 48 || $msgid == 49 ||$msgid == 68 || $msgid == 69)
            {
                $sql3 = "
                SELECT $tabelle.value
                FROM $tabelle
                WHERE ((($tabelle.sensorname)='Banane') AND (($tabelle.logtype)='Giessmenge'))
                ORDER BY $tabelle.ID DESC LIMIT 1
                ";
                
                $db_erg3 = mysqli_query( $db_link, $sql3 );
                if ( ! $db_erg3 )
                {
                    die('Ungültige Abfrage: ' . mysqli_error($db_link));
                } 
                    
                while($row3 = mysqli_fetch_array($db_erg3, MYSQL_ASSOC))
                {
                    $menge = $row3['value'];
                    //echo "aktuelle Temperatur: ", $value,"<br /><br />";
                } 

                while($row2 = mysqli_fetch_array($db_erg2, MYSQL_ASSOC))
                {
                    $msg[$i] = $row2['msg']."<br> Es wurden $menge ml Wasser gegossen.";
                    //$msg[$i] = "Es wurden &uuml;brigens $menge ml Wasser gegossen.";
                    //echo "aktuelle Temperatur: ", $value,"<br /><br />";
                }
            }
            else 
            {
                while($row2 = mysqli_fetch_array($db_erg2, MYSQL_ASSOC))
                {
                    $msg[$i] = $row2['msg'];
                    //echo "aktuelle Temperatur: ", $value,"<br /><br />";
                }                
            }                                                              
            ++$i;
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
