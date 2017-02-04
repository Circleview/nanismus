<?php

    // This litte php file decides if the button to initiate a watering event will be enabled or disabled on the index page
    
    // check the state of the watering initiation
    // if there was an unresolved manual watering initiation than don't show the button again
    include("load_watering_initiation_status.php");
    
    // for test we can manipulate the database result
    // $watering_initiated = reseted; // initiat
    
    
    // The form action on the test website will be differently from production. That's why I check this here.
    if ($name == "Test"){
        
        // the test pages need to receive and post test data
        echo "<form action='src/after_watering_initated_test.php' method='post'>";
        
    }
    else {
        
        // this is the production data from the real plant
        echo "<form action='src/after_watering_initated.php' method='post'>";
        
    }
    
    // build the input button
    echo "<input type='submit' value='";
    
    
    // it depends on moisture thresholds if the watering button will be enabled or not
    include ("color_threshold_configuration.php");
    
    
    // If the current plant moisture is below a certain threshold and the last watering event has been reseted than a new watering event over the website is allowed. Then I want to enable the button
    if ($Feuchte <= $ColorThreshold1 && $watering_initiated == "reseted"){
        
        // yes, show the button
    
        echo "$submitButtonLabelTextWateringEnabled '"; // Button label
        
        echo " id='watering_button_enabled'/>"; // choose the css styling for an enabled button
        
    }
    else {
        
        // no, don't show the button but show a nice little plant icon
        
        echo "$submitButtonLabelTextWateringDisabled '"; // Button label
        
        echo " disabled"; // disable the button
        
        echo " id='watering_button_disabled'/>"; // choose the css styling for an disabled button
        
    }
    
    // build the rest of the form
    echo "</p>";
    echo "</form>";
?>
