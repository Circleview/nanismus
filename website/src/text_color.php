<?php

    
    function textColor($moisturePercentValue){
        
        $textColorString = "";
        
        if ($moisturePercentValue >=41){
            $textColorString = "#ffffff"; /* green */
        }
        else if ($moisturePercentValue >=21){
            $textColorString = "#000000"; /* "#F2EC38;"; /* yellow */
        }
        else {
            $textColorString = "#ffffff"; /* red */
            
        }

        return $textColorString;

    }
?>
