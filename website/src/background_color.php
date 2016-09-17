<?php
    
    
    function backgroundColor($moisturePercentValue){
        
        
        $bgcolor = "";
        
        if ($moisturePercentValue >=41){
            $bgcolor = "#82c837"; /* green */
        }
        else if ($moisturePercentValue >=21){
            $bgcolor = "#FFFF33"; /* "#F2EC38;"; /* yellow */
        }
        else {
            $bgcolor = "#C8373A"; /* red */
        }
        
        return $bgcolor; 
        
    }

    ?>