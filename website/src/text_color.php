<?php

    
    function textColor($moisturePercentValue){
        
        include ("color_threshold_configuration.php");
        
        $textColorString = "";
        
        if ($moisturePercentValue > $ColorThreshold1 ){
            $textColorString = "#ffffff"; /* green */
        }
        else if ($moisturePercentValue > $ColorThreshold2){
            $textColorString = "#000000"; /* "#F2EC38;"; /* yellow */
        }
        else {
            $textColorString = "#ffffff"; /* red */
            
        }

        return $textColorString;

    }
?>
