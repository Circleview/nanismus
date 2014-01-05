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

//Datenbank-Verbindung herstellen
//--------------------------------
 include("src/db.php");

// GET mit Prüfung (durch Aufruf von "http://aquarium.jleopold.de/arduino_push_data.php?TEMP=21.90&key=XXXXXXXXX")
//-----------------

        /*
        $db_link = mysqli_connect (
         "localhost",             // MYSQL_HOST,
         "arduino",               // MYSQL_BENUTZER,
         "!Pflanzenprojekt2012",  // MYSQL_KENNWORT,
         "plantdata_test"              // MYSQL_DATENBANK
        );
        */

        $key = "c3781633f1fb1ddca77c9038d4994345";
        $table = "plant_log" ;
        // Erhaltene Daten des Arduino Http Get in die MySQL Datenbank speichern
        
        
        // http://nanismus.no-ip.org/nanismus_test/valueget.php?name=Banane&type=status&value=99&key=c3781633f1fb1ddca77c9038d4994345
        //http://nanismus.no-ip.org/nanismus_test/valueget_test.php?name=Banane&type=Messwert&value=447&key=c3781633f1fb1ddca77c9038d4994345
        
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
            
            echo "Dateneingabe erfolgreich";
        }
        else
        {
            echo "Dateneingabe fehlerhaft";
        }
?>