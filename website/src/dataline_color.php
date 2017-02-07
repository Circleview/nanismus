<?php
    
    
    function datalineColor($moisturePercentValue){
        
        include ("color_threshold_configuration.php");
        
        $bgcolor = "";
        
        if ($moisturePercentValue > $ColorThreshold1 ){
            $bgcolor = "#7D37C8"; /* green */
        }
        else if ($moisturePercentValue > $ColorThreshold2 ){
            $bgcolor = "#3333FF"; /* "#F2EC38;"; /* yellow */
        }
        else {
            $bgcolor = "#37C8C5"; /* red */
        }
        
        return $bgcolor; 
        
    }

    ?>
