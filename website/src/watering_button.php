<?php

    // This litte php file decides if the button to initiate a watering event will be displayed on the index page
    
    // check the state of the watering initiation
    // if there was an unresolved manual watering initiation than don't show the button again
    include("load_watering_initiation_status.php");
    
    // for test we can manipulate the database result
    // $watering_initiated = reset;
    
    if ($Feuchte <= 40 && $watering_initiated == "reset"){
        
        // yes, show the button
    
        echo "<form action='src/after_watering_initated.php' method='post'>";
        echo "<input type='submit' value='Jetzt gie&szlig;en' id='watering_button'/></p>";
        echo "</form>"; 
        
    }
    else {
        
        // no, don't show the button
    }
    
?>