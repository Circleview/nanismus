<?php

    // This litte php file decides if the button to initiate a watering event will be displayed on the index page
    
    // check the state of the watering initiation
    // if there was an unresolved manual watering initiation than don't show the button again
    include("load_watering_initiation_status.php");
    
    // for test we can manipulate the database result
    // $watering_initiated = reset;
    
    if ($Feuchte <= 40 && $watering_initiated == "reseted"){
        
        // yes, show the button
    
        if ($name == "Test"){
            
            // the test pages need to receive and post test data
            echo "<form action='src/after_watering_initated_test.php' method='post'>";
            
        }
        else {
            
            // this is the production data from the real plant
            echo "<form action='src/after_watering_initated.php' method='post'>";
        
        }
        
        // try to prevent bots from inputing and watering the plant
        // http://stackoverflow.com/questions/8472/practical-non-image-based-captcha-approaches
        echo "<input type='text' name='name' id='name' value='name' />";
        
        /*
        echo "<script>";
        
            echo "var antiSpam = function() {";
                echo "if (document.getElementById('name')) {";
                    echo "a = document.getElementById('name');";
                    echo "if (isNaN(a.value) == true) {";
                        echo "a.value = 0;";
                    echo "} else {";
                        echo "a.value = parseInt(a.value) + 1;";
                    echo "}";
                echo "}";
                echo "setTimeout('antiSpam()', 1000);";
            echo "}";
        
            echo "antiSpam();";
        
        echo "</script>";
         */
        
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
