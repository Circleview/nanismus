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
        

        echo "transmission success";
    
    }
    else
    {
        echo "transmission failure";
        //echo "0 \r\n";
    }
    
    ?>