<?php
    
    // Zugriff auf die Produktivdatenbank f�r das Bew�sserungsprojekt Nanismus
    // MySQL Server auf der Synology Disk
    // Benuzterzugriff mit den Lese und Schreibrechten des Arduino Boards
    
    $db_link = mysqli_connect (
                               "localhost",             // MYSQL_HOST,
                               "arduino",               // MYSQL_BENUTZER,
                               "!Pflanzenprojekt2012",  // MYSQL_KENNWORT,
                               "plantdata"              // MYSQL_DATENBANK
                               );
    
    ?>