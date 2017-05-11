<?php
    
    /* We need to find out if we are currently requesting the test data or the production plant data
     * the name tag defines if we request test data or not */
    
    $key = "c3781633f1fb1ddca77c9038d4994345"; /* key to prevent random database queries - this is not a password*/

    if ((isset($_GET['name'])) and (($_GET['key']) == $key))
    {
        
        $name = ($_GET['name']);

    }
    
    include("src/load_watering_initiation_status.php");
    
    echo $watering_initiated;
    
    ?>
