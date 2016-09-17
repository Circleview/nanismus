	<?php

        //Datenbank-Verbindung herstellen
        //------------------------------
        include ("db.php");

        $tabelle = "plant_log";

        /* Definiere zunächst den Zeitraum, der in dem Diagramm angezeigt werden soll
         * Lade dann die Durchschnitts-Prozentfeuchtedaten aus dem Zeitraum in ein Array
         * Lade dann die Durchschnitts-Temperaturen in ein Array
         * Danach Speichere ich Wässerungsmengen aus dem Zeitraum in ein weiteres Array
         */
        //http://www.ayom.com/topic-7692.html
        //http://de.php.net/strtotime
        $auswertzeitraum = 15; // Wie viele Tage wollen wir in die Vergangenheit gucken?
        $vergleichsdatum = "-".$auswertzeitraum." days";
        $vergleichsdatum = strtotime($vergleichsdatum);
        $datum = date("Y-m-d H:i:s", $vergleichsdatum);


        // Baue das Array auf, das die Diagrammdaten hält
        // http://www.php.net/manual/de/language.types.array.php
        // http://www.php.net/manual/de/function.array.php
        $diagdata = array
          (
            "day" => array(),
            "moisture" => array()
          );


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

            // der im Diagramm angezeigte Tag soll einen Punkt haben 01 => 01.
            $anzeigetag = $t.".";

            // Als nächstes ermittle ich die Feuchtigkeitswerte im Auswertungszeitraum
            // Ermittle die Durchschnittsfeuchte
            // http://php.net/manual/de/function.round.php
            $moisture = (round($row['avgmoisture'], 1))/100;

            // Wenn der heutige Tag angezeigt wird, dann soll das Wort "Jetzt" angezeigt werden

            $heuteTag = str_pad(date('d', time()), 2,'0', STR_PAD_LEFT);
            if ($heuteTag == $t)
            {
                $anzeigetag = "Jetzt";

                /* Zum heutigen Tag soll statt der Durchschnittsfeuchte
                 * die letztgemessene Feuchtigkeit angezeigt werden
                 */
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

            $diagdata["day"][$t] = $anzeigetag;
            $diagdata["moisture"][$t] = $moisture;

            // echo("Tag / Feuchte " . $diagdata["day"][$t] . "/" . $diagdata["moisture"][$t] . " ");

        }

        // Jetzt muss das Array für den Google Grafen aufgebaut werden
        // So erwartet GoogleCharts seine Daten
        /*
        var data = google.visualization.arrayToDataTable([
          ['Tag', 'Temperatur'],
          ['Montag',  19      ],
          ['Dienstag',  22    ],
          ['Mittwoch',  21    ],
          ['heute',  23       ]
        ]);
        */

        // Im Folgenden wird das Array aufgebaut, das die Daten für das kleine Diagramm
        // der Prozentfeuchtewerte hält

        // Einbinden der Bibliotheken von GoogleChart
        echo "var data = google.visualization.arrayToDataTable([";
        echo "['Tag', '% Feuchte']"; // Tabellenkopf
        for ($daycount = $auswertzeitraum-1; $daycount > -1; $daycount--)
        {
            $vergleichsdatum = "-".$daycount." days";
            $vergleichsdatum = strtotime($vergleichsdatum);
            $datum = date('j', $vergleichsdatum);
            $datum = str_pad($datum, 2,'0', STR_PAD_LEFT); // Führende Null beim Datum


            echo ",['". $diagdata["day"][$datum] . "', " . $diagdata["moisture"][$datum] . "] ";   // Aufbau des Arrays für die GoogleChart Daten
        }

        echo "]);";
        
        
        
        /*
        var data = google.visualization.arrayToDataTable([
                                                         ['Year', 'Sales', 'Expenses'],
                                                         ['2013',  1000,      400],
                                                         ['2014',  1170,      460],
                                                         ['2015',  660,       1120],
                                                         ['2016',  1030,      540]
                                                         ]);
        */

        
        // https://developers.google.com/chart/interactive/docs/gallery/areachart#configuration-options
        
        echo "var options = {";
        echo "title: 'Feuchte im Zeitverlauf',";
        
        echo "hAxis: {";
            echo "textStyle: {";
                echo "color: '"; echo textColor($Feuchte); echo "'";
            echo "},";
            echo "gridlines: {";
                echo "count: 5";
            echo "}";
        echo "},";
        
        echo "vAxis: {";
            echo "minValue: 0.35,";
            echo "textStyle: {";
                echo "color: '"; echo textColor($Feuchte);
            echo "'},";
            echo "format: 'percent',";
            echo "gridlines: {";
                echo "color: '"; echo backgroundColor($Feuchte); echo "',";
                echo "count: 2";
            echo "},";
            echo "textPosition: 'in'"; 
        echo "},";
        
        echo "titleTextStyle: {";
            echo "color: '"; echo textColor($Feuchte);  echo "',";
            echo "fontSize: 15";
        echo "},";
        
        echo "backgroundColor: '"; echo backgroundColor($Feuchte); echo "',";
        
        echo "chartArea:{left:0,top:20,width:'100%',height:'85%'},";
        
        // color script based on moisture
        include ("dataline_color.php");
        
        echo "series: {";
            echo "0: {";
                echo "visibleInLegend: false,";
                echo "lineWidth: 4,";
                echo "color: '"; echo datalineColor($Feuchte); echo "',";
                echo "areaOpacity: 0.3"; 
                echo "}";
            echo "}";
        
        echo "};";

     ?>