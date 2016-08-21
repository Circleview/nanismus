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
    
    $tabelle = "initiate_watering_events";
    
    
    //Datenbank-Verbindung herstellen
    //--------------------------------
    include("src/db.php");
    
    $key = "c3781633f1fb1ddca77c9038d4994345"; // Key zum Schreiben in die MY SQL Datenbank
    
    // Erhaltene Daten des Arduino Http Get in die MySQL Datenbank speichern
    
    // http://nanismus.no-ip.org/nanismus_test/valueget.php?name=Banane&type=status&value=99&key=c3781633f1fb1ddca77c9038d4994345
    
    if ((isset($_GET['name'])) and (isset($_GET['value'])) and (($_GET['key']) == $key))
    {	// If values have been transmitted and the key is correct ...
        
        $name = ($_GET['name']);
        $valueInt = ($_GET['value']);
        $valueString;
        
        // the http:// call from the arduino webclient only sends int values, those have to be interpreted
        
        switch ($valueInt){
            case 1 :
                
                // Reset by Arduino web client
                $valueString = "reset";
                break;
                
            case 2 :
                
                // pump initiation by use
                $valueString = "initate";
                break;
                
            default :
                
                // e.g. for unintended calls
                $valueString = "none";
                break;
        }
        
        $sql = "
        INSERT INTO $tabelle
        (
         name , watering_initiated
         )
        VALUES
        (
         '$name', '$valueString'
         )
        ";
        
        //http://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol
        $db_erg = mysqli_query($db_link, $sql)
        or die("transmission failure" . mysqli_error($db_link));
        

        echo "transmission success";
    
    }
    else
    {
        echo "transmission failure";
        //echo "0 \r\n";
    }
    
    ?>