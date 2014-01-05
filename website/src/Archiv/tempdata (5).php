	<?php

        //Datenbank-Verbindung herstellen
        //------------------------------
        include ("src/db.php");
        $tabelle = "plant_log";
        
        //http://www.ayom.com/topic-7692.html
        //http://de.php.net/strtotime
        $vergleichsdatum = "-25 days";
        $now = strtotime($vergleichsdatum);

        $datum = date("Y-m-d H:i:s", $now);

        // Ermittlung der Temperaturdaten
        // http://technet.microsoft.com/de-de/library/ee634550.aspx   
        
        //echo "vergleichsdatum: $datum";
        $sql = "
        SELECT Avg($tabelle.value) AS Mittelwertvonvalue, Weekday(timestamp) AS WT, Day(timestamp) AS Tag
        FROM $tabelle
        WHERE ((($tabelle.sensorname)='Temperatur') AND (($tabelle.logtype)='Messwert') AND (($tabelle.timestamp) > '$datum'))
        GROUP BY Day(timestamp)
        ORDER BY ($tabelle.ID)
        ";

        $db_erg = mysqli_query( $db_link, $sql );
        if ( ! $db_erg )
        {
        die('Ungültige Abfrage: ' . mysqli_error($db_link));
        }
        
        // Im Folgenden wird das Array aufgebaut, das die Daten für das kleine Diagramm 
        // der Temperaturwerte hält
        /* Dazu wird der Wochentag des Datenbanktimestamps in das Deutsche konvertiert
         * Dann wird der Tag des Monats mit einer führenden Null gespeichert
         * Anschließend wird die Kombination aus Wochentag und Tag im Monat 
         * mit dem heutigen Datum verglichen. 
         * Sind die Zahlenkombinationen identisch, dann soll im Array statt 
         * des aktuellen Wochentags das Wort "Heute" gespeichert werden. 
         * Der Vergleich auf der Basis der Kombination von Wochentag und Tag im Monat
         * ist ausreichend zuverlässig, da in den Diagramm auf der Website zur die Daten 
         * der zurückliegenden Tage angezeigt werden sollen.
         */
        // Wie fülle ich mit PHP führende Nullen auf? 
        // http://www.strassenprogrammierer.de/php-nullen-auffuellen_tipp_505.html
        
        // Einbinden der Bibliotheken von GoogleChart
        echo "var data = google.visualization.arrayToDataTable([";
        echo "['Tag', '°C'],"; // Tabellenkopf
        
        while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
        {
        $tag = $row['Tag'];
        $tag = str_pad($tag, 2,'0', STR_PAD_LEFT); // der Tag des Monats soll mit einer führenden Null geschrieben werden

        //$moisture = $row['avgmoisture'];

        // Wenn der heutige Tag angezeigt wird, dann soll das Wort "heute" angezeigt werden
        if (date(d, time()) == ($tag))
        {
            $tag = "Heute";
        }        
        
        /*while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
        {
        $tag = $row['Tag'];
        $tag = str_pad($tag, 2,'0', STR_PAD_LEFT); // der Tag des Monats soll mit einer führenden Null geschrieben werden
        
        $Wochentag = $row['WT'];                

        if ((date(N, time())- 1 . date(d, time())) == ($Wochentag . $tag))
        {
            $Wochentag = "Heute";
        }
        else 
        {       
            switch ($Wochentag)
            {
                case 0: 
                    $Wochentag = "Mo";
                    break;
                case 1: 
                    $Wochentag = "Di";
                    break; 
                case 2: 
                    $Wochentag = "Mi";
                    break;
                case 3: 
                    $Wochentag = "Do";
                    break;
                case 4: 
                    $Wochentag = "Fr"; 
                    break;
                case 5: 
                    $Wochentag = "Sa";
                    break; 
                case 6: 
                    $Wochentag = "So";
                    break;
            }
        }
        */
        // So erwartet GoogleCharts seine Daten
        /* 
        var data = google.visualization.arrayToDataTable([
          ['Tag', 'Temperatur'],
          ['Montag',  19        ],
          ['Dienstag',  22        ],
          ['Mittwoch',  21         ],
          ['heute',  23        ]
        ]);
        */
        
        $avgtemp = round($row['Mittelwertvonvalue'], 1); // http://php.net/manual/de/function.round.php 
        //$avgtemp = number_format($row['Mittelwertvonvalue'],1,".",","); // http://php.net/manual/de/function.number-format.php
        
        echo "['", $tag, "', " ,$avgtemp,"],";   // Aufbau des Arrays für die GoogleChart Daten
        
        }
        
        echo "]);";
     ?>
