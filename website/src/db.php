<?php

// Zugriff auf die Produktivdatenbank für das Bewässerungsprojekt Nanismus
// MySQL Server auf der Synology Disk
// Benuzterzugriff mit den Lese und Schreibrechten des Arduino Boards

if (!$test)  // $test comes from the index page it tells where to get the data from
{
        $db_link = mysqli_connect (
         "localhost",             // MYSQL_HOST,
         "arduino",               // MYSQL_BENUTZER,
         "!Pflanzenprojekt2012",  // MYSQL_KENNWORT,
         "plantdata"              // MYSQL_DATENBANK
        );
}
else
{
        $db_link = mysqli_connect (
         "localhost",             // MYSQL_HOST,
         "arduino",               // MYSQL_BENUTZER,
         "!Pflanzenprojekt2012",  // MYSQL_KENNWORT,
         "plantdata_test"         // MYSQL_DATENBANK
        );    
}

?>