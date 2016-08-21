<?php
    
    //Datenbank-Verbindung herstellen
    //------------------------------
    include ("src/db.php");
    $tabelle = "initiate_watering_events";
    
    
    // Get the last status of the watering initiation table
    $sql = "
    SELECT $tabelle.watering_initiated
    FROM $tabelle
    WHERE ($tabelle.name ='Banane')
    ORDER BY $tabelle.ID DESC LIMIT 1
    ";
    
    $db_erg = mysqli_query( $db_link, $sql );
    if ( ! $db_erg )
    {
        die('invalid request: ' . mysqli_error($db_link));
    }
    
    while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
    {
        $watering_initiated = $row['watering_initiated'];
    }
    ?>