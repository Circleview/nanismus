<?php
    
    
    function datalineColor($moisturePercentValue){
        
        
        $bgcolor = "";
        
        if ($moisturePercentValue >=41){
            $bgcolor = "#7D37C8"; /* green */
        }
        else if ($moisturePercentValue >=21){
            $bgcolor = "#3333FF"; /* "#F2EC38;"; /* yellow */
        }
        else {
            $bgcolor = "#37C8C5"; /* red */
        }
        
        return $bgcolor; 
        
    }

    ?>