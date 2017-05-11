	<?php

        
        
        // conversion of the date when watering is allowed again into a german name of a weekday
        include("weekdayToGermanWeekdayNameString.php");
        
        
        Ç◊Ç//Datenbank-Verbindung herstellen
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
        $auswertzeitraum = 29; // Wie viele Tage wollen wir in die Vergangenheit gucken?
        $vergleichsdatum = "-".$auswertzeitraum." days";
        $vergleichsdatum = strtotime($vergleichsdatum);
        $datum = date("Y-m-d", $vergleichsdatum);

        
        //echo "datum: " . $datum . "\n";
        // datum: 2017-02-24
        
        
        // Baue das Array auf, das die Diagrammdaten hält
        // http://www.php.net/manual/de/language.types.array.php
        // http://www.php.net/manual/de/function.array.php
        $diagdata = array
          (
            "day" => array(),
            "moisture" => array()
          );


        // Ermittlung der Prozentfeuchtedaten
        // http://technet.microsoft.com/de-de/library/ee634550.aspx
        
        $sql = "
        SELECT DATE_FORMAT($tabelle.timestamp, '%d.%m.%Y') AS 'timeStampDate', round(Avg($tabelle.value),1) AS avgmoisture
        FROM $tabelle
        WHERE ((($tabelle.sensorname)='Banane') AND (($tabelle.logtype)='Prozentfeuchte') AND (($tabelle.timestamp) > '$datum') AND (($tabelle.value) is not null))
        GROUP BY timeStampDate
        ORDER BY ($tabelle.ID)
        ";

        $db_erg = mysqli_query( $db_link, $sql );
        if ( ! $db_erg )
        {
        die('Ungültige Abfrage: ' . mysqli_error($db_link));
        }
        
        // Wie fülle ich mit PHP führende Nullen auf?
        // http://www.strassenprogrammierer.de/php-nullen-auffuellen_tipp_505.html

        
        // We need a counter for the selected rows to calculate the average drying rate of the soil
        $RowCounter = 1;
        
        
        $lastMoisture = 999;
        
        
        // We define a basis value for the cumulative amount of droped moisture
        $cumulativeMoistureDrop = 0;
        
        
        // Wenn der heutige Tag angezeigt wird, dann soll das Wort "Jetzt" angezeigt werden
        $heuteTag = (date('d.m.Y', time()));
        //echo "heuteTag: " . $heuteTag . "\n";
        // heuteTag: 25.03.2017
        
        
        while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
        {
            $dayOfDatabaseRowTimestamp = $row['timeStampDate'];
            // echo "dayOfDatabaseRowTimestamp: " . $dayOfDatabaseRowTimestamp . " ";
            
            $t = substr($dayOfDatabaseRowTimestamp,0,2); // der Tag des Monats soll mit einer führenden Null geschrieben werden
            // speichere den Tag in einem Array, der String der Tageszahl ist der Ident des Arrays

            // der im Diagramm angezeigte Tag soll einen Punkt haben 01 => 01.
            $anzeigetag = $t.".";

            // Als nächstes ermittle ich die Feuchtigkeitswerte im Auswertungszeitraum
            // Ermittle die Durchschnittsfeuchte
            // http://php.net/manual/de/function.round.php
            $moisture = ($row['avgmoisture']);
            
            
            // echo "heuteTag: " . $heuteTag . "\n";
            // echo "dayOfDatabaseRowTimestamp: " . $dayOfDatabaseRowTimestamp . "\n";
            // dayOfDatabaseRowTimestamp: 24.02.2017
            
            if ($heuteTag == $dayOfDatabaseRowTimestamp)
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
                     $moisture = $rowjetzt['value'];
                }
            }

            
            $diagdata["day"][$t] = $anzeigetag;
            $diagdata["moisture"][$t] = $moisture/100;
            
            
            
            // We calculate the speed of drying of the plant soil to anticipate when the next watering event eventually needs to take place
            
            /*
            echo "dayOfDatabaseRowTimestamp: " . $dayOfDatabaseRowTimestamp . " // ";
            echo "RowCounter: " . $RowCounter  . " // ";
            echo "moisture: " . $moisture . " // ";
            echo "lastMoisture: " . $lastMoisture;
            echo "\n";
            */
            
            
            // On the first time when the while loop is performed we just skip the calculation, since we have no values to compare
            if ($lastMoisture == 999) {
                
                // nothing needs to happen here
                
                // echo "skip the first row \n";
                
                // we just store the last moisture value
                
            }
            else {
                
                // We check if the avgerage moisture is going up or down
                
                if ($lastMoisture < $moisture) {
                    
                    // we asume that a watering event took place, because the current moisture is higher than the last.
                    
                    // we don't use that data to calculate a burn rate, since it would mean an inverse burn rate
                    
                    // echo "lastMoisture is below current moisture --> we skip this value \n";
                    
                }
                else {
                
                    // If the moisture goes down we check the speed of the moisture decline
                    
                    /*
                    echo "last moisture is higher than current moisture --> moisture drop";
                    echo " // ";
                    */
                     
                    $cumulativeMoistureDrop = $cumulativeMoistureDrop + ($lastMoisture - $moisture);
                    
                    // echo "cumulativeMoistureDrop: " . $cumulativeMoistureDrop . "\n";
                    
                    // Increase the row counter by 1
                    $RowCounter = $RowCounter + 1;
                    
                }
                
            }
            
            // We store the last moisture to compare it with the moisture of the while upcoming loop
            $lastMoisture = $moisture;
            
        }
        
        
        // We calculate the average moisture burn-rate per day and save this burn rate to calculate the upcoming watering event
        $avgMoistureDrop = $cumulativeMoistureDrop / $RowCounter;
        // echo "avgMoistureDrop: " . $avgMoistureDrop . "\n";
        
        
        // To avoid a devision by zero check if the average moisture drop is zero
        if ($avgMoistureDrop == 0) {$avgMoistureDrop = 1;}
        
        
        // We take the last moisture value that was selected from the database and calculate the amount of days that will be needed until the next watering event eventually takes place
        
        
        // We define the treshold when the next watering event technically will be possible again
        include ("color_threshold_configuration.php");
        $aimedMoistureToAllowWateringEvent = $ColorThreshold1;
        // echo "aimedMoistureToAllowWateringEvent: " . $aimedMoistureToAllowWateringEvent . "\n";
        
        
        // We calculate the real calendar date of the anticipated next watering event
        $daysUntilWateringIsAllowedAgain = ceil((($moisture - $aimedMoistureToAllowWateringEvent) / $avgMoistureDrop));
        // echo "daysUntilWateringIsAllowedAgain: " . $daysUntilWateringIsAllowedAgain . "\n";
        
        $anticipatedWateringDay = "+".$daysUntilWateringIsAllowedAgain." days";
        // echo "anticipatedWateringDay: " . $anticipatedWateringDay . "\n";
        
        
        // To make the anticipated watering date in the future more naturally readable we convert the date into words
        
        // If the anticipated watering event is near in the future we just calculate the weekday name
        if ($daysUntilWateringIsAllowedAgain >= 7){
            
            
            /*
            echo "daysUntilWateringIsAllowedAgain (" . $daysUntilWateringIsAllowedAgain . ") is larger than 7 --> we need to display the date not the name of the day \n";
            */
            
            
            // we need to display the date not the name of the day
            $anticipatedWateringDay = date('d.m.', strtotime($anticipatedWateringDay));
            $anticipatedWateringDay = "am " . $anticipatedWateringDay;
            // echo "anticipatedWateringDay: " . $anticipatedWateringDay . "\n";
            
        }
        else if ($daysUntilWateringIsAllowedAgain > 1) {
            
            
            // echo "daysUntilWateringIsAllowedAgain (" . $daysUntilWateringIsAllowedAgain . ") is larger than 1 --> we display the name of the upcoming day \n";
            
            // we display the name of the upcoming day
            $anticipatedWateringDay = "am " . weekdayToGermanWeekdayNameString(date('w', strtotime($anticipatedWateringDay)));
            // echo "anticipatedWateringDay: " . $anticipatedWateringDay . "\n";
            
        }
        else {
            
            // we display the name of the upcoming day
            $anticipatedWateringDay = "Morgen";
            
        }
        
        $anticipatedWateringDay = "Gie&szlig;en voraussichtlich " . $anticipatedWateringDay . " m&ouml;glich";
        
        

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

            if ($diagdata["day"][$datum] == "") {
                
                // skip this day to avoid a broken diagram
                
            }
            else {
                
                echo ",['". $diagdata["day"][$datum] . "', " . $diagdata["moisture"][$datum] . "] ";   // Aufbau des Arrays für die GoogleChart Daten
            }

        }

        echo "]);";

        
        // the google api moist chart is configured in src/moistchart_configuration.php
        include("moistchart_configuration.php");
        
    ?>
