<?php

    // This litte php file decides if the button to initiate a watering event will be displayed on the index page
    
    // check the state of the watering initiation
    // if there was an unresolved manual watering initiation than don't show the button again
    include("load_watering_initiation_status.php");
    
    // for test we can manipulate the database result
    // $watering_initiated = reset;
    
    include ("color_threshold_configuration.php");
    
    if ($Feuchte <= $ColorThreshold1 && $watering_initiated == "reseted"){
        
        // yes, show the button
    
        if ($name == "Test"){
            
            // the test pages need to receive and post test data
            echo "<form action='src/after_watering_initated_test.php' method='post'>";
            
        }
        else {
            
            // this is the production data from the real plant
            echo "<form action='src/after_watering_initated.php' method='post'>";
        
        }
        
        // the rest of the normal web form
        echo "<input type='submit' value='";
        echo "$submitButtonLabelText";
        echo "' id='watering_button'/></p>";
        echo "</form>";
        
    }
    else {
        
        // no, don't show the button but show a nice little plant icon
        
        // show a nice little picture
        echo "<img id='centralPlantIcon' src ='../images/naniplant_pot.svg'>";
        
    }
    
?>
