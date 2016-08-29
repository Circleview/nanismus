<?php
    
    // Idee von
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
    // werden keine Daten per "?TEMP=......" ¸bergeben, wird der aktuellste Wert aus der Datenbank angezeigt
    //
    
    // Festlegen welche Datenbank verwendet werden soll
    $test = false;  // bei true werden die Daten in die Testdatenbank geschrieben
    
    $tabelle = "initiate_watering_events";
    
    
    //Datenbank-Verbindung herstellen
    //--------------------------------
    include("db.php");
    
    
    // pump initiation by use
    $valueString = "initate";
    
    // we receive the name from the calling page
    // $name = "Banane";
    
    
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
    
    ?>