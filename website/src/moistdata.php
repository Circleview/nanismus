	<?php

        //Datenbank-Verbindung herstellen
        //------------------------------
        include ("db.php");
        $tabelle = "plant_log";
        
        //http://www.ayom.com/topic-7692.html
        //http://de.php.net/strtotime
        $vergleichsdatum = "-24 days";
        $now = strtotime($vergleichsdatum);

        $datum = date("Y-m-d H:i:s", $now);
        
        // Ermittlung der Prozentfeuchtedaten
        // http://technet.microsoft.com/de-de/library/ee634550.aspx
        
        $sql = "
        SELECT Day(timestamp) AS tag, Avg($tabelle.value) AS avgmoisture
        FROM $tabelle
        WHERE ((($tabelle.sensorname)='Banane') AND (($tabelle.logtype)='Prozentfeuchte') AND (($tabelle.timestamp) > '$datum'))
        GROUP BY Day(timestamp)
        ORDER BY ($tabelle.ID)
        ";

        $db_erg = mysqli_query( $db_link, $sql );
        if ( ! $db_erg )
        {
        die('Ungültige Abfrage: ' . mysqli_error($db_link));
        }
        
        // Im Folgenden wird das Array aufgebaut, das die Daten für das kleine Diagramm 
        // der Prozentfeuchtewerte hält

        // Wie fülle ich mit PHP führende Nullen auf? 
        // http://www.strassenprogrammierer.de/php-nullen-auffuellen_tipp_505.html
        
        // Einbinden der Bibliotheken von GoogleChart
        echo "var data = google.visualization.arrayToDataTable([";
        echo "['Tag', '% Feuchte'],"; // Tabellenkopf
        
        while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
        {
        $tag = $row['tag'];
        $tag = str_pad($tag, 2,'0', STR_PAD_LEFT); // der Tag des Monats soll mit einer führenden Null geschrieben werden
        
        //$moisture = $row['avgmoisture'];                

        // Wenn der heutige Tag angezeigt wird, dann soll das Wort "Jetzt" angezeigt werden
        // Und es soll der letzte gemessene Wert dargestellt werden
        if (date(d, time()) == ($tag))
        {
            $tag = "Jetzt";

            $sql = "
            SELECT $tabelle.ID, $tabelle.value
            FROM $tabelle
            WHERE ((($tabelle.sensorname)='Banane') AND (($tabelle.logtype)='Prozentfeuchte'))
            ORDER BY $tabelle.ID DESC LIMIT 1
            ";

            $db_erg = mysqli_query( $db_link, $sql );
            if ( ! $db_erg )
            {
            die('Ungültige Abfrage: ' . mysqli_error($db_link));
            }

            while($rowjetzt = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
            {
                 $moisture = $rowjetzt['value']/100;
            }

        }
        else
        {
            $moisture = (round($row['avgmoisture'], 1))/100; // http://php.net/manual/de/function.round.php
            //$avgtemp = number_format($row['Mittelwertvonvalue'],1,".",","); // http://php.net/manual/de/function.number-format.php
        }

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
                
        echo "['", $tag, "', " ,$moisture,"],";   // Aufbau des Arrays für die GoogleChart Daten
        
        }
        
        echo "]);";
     ?>
