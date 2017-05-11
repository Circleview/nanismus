<?php
    
    // http://www.colorpicker.com/37c8c5
    
    
    function backgroundColor($moisturePercentValue){
        
        include ("color_threshold_configuration.php");
        
        $bgcolor = "";
        
        if ($moisturePercentValue > $ColorThreshold1){
            $bgcolor = "#82c837"; /* green */
        }
        else if ($moisturePercentValue > $ColorThreshold2){
            $bgcolor = "#FFFF33"; /* "#F2EC38;"; /* yellow */
        }
        else {
            $bgcolor = "#C8373A"; /* red */
        }
        
        return $bgcolor; 
        
    }

    ?>
