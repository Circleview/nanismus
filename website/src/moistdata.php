	<?php

        //Datenbank-Verbindung herstellen
        //------------------------------
        include ("db.php");

        $tabelle = "plant_log";            

        //http://www.ayom.com/topic-7692.html
        //http://de.php.net/strtotime
        $auswertzeitraum = 15; // Wie viele Tage wollen wir in die Vergangenheit gucken?
        $vergleichsdatum = "-".$auswertzeitraum." days";
        $now = strtotime($vergleichsdatum);
        $datum = date("Y-m-d H:i:s", $now);
        
        //echo "datum: ".$datum."<br />";
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
        
        // Wie fülle ich mit PHP führende Nullen auf? 
        // http://www.strassenprogrammierer.de/php-nullen-auffuellen_tipp_505.html

        while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
        {
            $t = $row['tag'];
            $t = str_pad($t, 2,'0', STR_PAD_LEFT); // der Tag des Monats soll mit einer führenden Null geschrieben werden
            // speichere den Tag in einem Array, der String der Tageszahl ist der Ident des Arrays
            // Zu diesem Identen werden dann die Prozentfeuchte und die Wassermenge gespeichert
            
            $anzeigetag[$row['tag']] = $t;
            //echo "t: ".$t."<br />";
            //$moisture = $row['avgmoisture'];                

            // Wenn der heutige Tag angezeigt wird, dann soll das Wort "Jetzt" angezeigt werden
            // Und es soll der letzte gemessene Wert dargestellt werden
            $heuteTag = str_pad(date(d, time()), 2,'0', STR_PAD_LEFT);          
            if ($heuteTag == $t)
            {
                $anzeigetag[$row['tag']] = "Jetzt";
    
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
                     $moisture[$row['tag']] = $rowjetzt['value']/100;
                }
            }
            else
            {
                $moisture[$row['tag']] = (round($row['avgmoisture'], 1))/100; // http://php.net/manual/de/function.round.php
                //$avgtemp = number_format($row['Mittelwertvonvalue'],1,".",","); // http://php.net/manual/de/function.number-format.php
            }
        }
        
/*
 * Dann holen wir uns die Summen der Wässerungsmengen der letzten Tage
*/        
    $sql = "
    SELECT Day(timestamp) AS tag, SUM($tabelle.value) AS ml
    FROM $tabelle
    WHERE ((($tabelle.sensorname)='Banane') AND (($tabelle.logtype)='Giessmenge') AND (($tabelle.timestamp)> '$datum'))    
    GROUP BY $tabelle.sensorname, $tabelle.logtype, Day(timestamp)
    ORDER BY $tabelle.ID
    ";        

        $db_erg = mysqli_query( $db_link, $sql );
        if ( ! $db_erg )
        {
        die('Ungültige Abfrage: ' . mysqli_error($db_link));
        }

        while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
        {
                $ml[$row['tag']] = $row['ml'];
                //echo "Tag: ".$row['tag2']."; ml: ".$row['ml']."<br />";
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
        // Im Folgenden wird das Array aufgebaut, das die Daten für das kleine Diagramm
        // der Prozentfeuchtewerte hält

        // Einbinden der Bibliotheken von GoogleChart
        echo "var data = google.visualization.arrayToDataTable([";
        echo "['Tag', '% Feuchte', 'ml']"; // Tabellenkopf
        for ($daycount = $auswertzeitraum-1; $daycount > -1; $daycount--)
        {
            $vergleichsdatum = "-".$daycount." days";
            $now = strtotime($vergleichsdatum);
            $datum = date("j", $now);

            //echo "datum: ".$datum."<br />";
            // Wenn kein Datensatz mehr kommt, dann beende die Schleife
            /*if(empty($tag[$datum])) {
                break;
            } */                  
            if (empty($ml[$datum]))
            {
                $ml[$datum] = 0;
            }
            echo ",['". $anzeigetag[$datum]. "', " ,$moisture[$datum]. ", ". $ml[$datum]. "]";   // Aufbau des Arrays für die GoogleChart Daten            
        }

        echo "]);";
     ?>
