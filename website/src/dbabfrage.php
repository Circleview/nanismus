<?php
    
    //Datenbank-Verbindung herstellen
    //------------------------------
    include ("src/db.php");
    $tabelle = "plant_log";
    
    
    // Ermittlung der Bodenfeuchte der Bananenpflanze
    $sql = "
    SELECT $tabelle.value
    FROM $tabelle
    WHERE ((($tabelle.sensorname)='$name') AND (($tabelle.logtype)='Prozentfeuchte'))
    ORDER BY $tabelle.ID DESC LIMIT 1
    ";
    
    $db_erg = mysqli_query( $db_link, $sql );
    if ( ! $db_erg )
    {
        die('UngŸltige Abfrage: ' . mysqli_error($db_link));
    }
    
    while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
    {
        $Feuchte = $row['value'];
     }
    
    // echo "Feuchte: $Feuchte";
?>
