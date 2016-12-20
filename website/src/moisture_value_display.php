<?php

function pathToWaterdropIcon ($inputMoistureValue) {
    
    // The picture that will be displayed, depends on the moisture of the plant
    
    if ($inputMoistureValue >=41){
        return "'../images/waterdrop_white.svg'"; /* green */
    }
    else if ($inputMoistureValue >=21){
        return "'../images/waterdrop_black.svg';"; /* "#F2EC38;"; /* yellow */
    }
    else {
        return "'../images/waterdrop_white.svg'"; /* red */
        
    }
    
}
    
    
    // HTML
    // echo "<tr>";
        // echo "<td>";
    
            // show a nice little picture
    if ($showMoistureChart) {
        echo "<a href='#chart_div' id='linkToMoistChart' rel='' style='color: "; echo textColor($Feuchte); echo "; text-decoration: none;'>";
    }
            echo "<img id='waterdropForMoisture' src =";
            echo pathToWaterdropIcon($Feuchte);
            echo ">";
    
            // Get the name of the plant from $name - defined in the index.php
            echo " $Feuchte %";
    if ($showMoistureChart) {
        echo "</a>";
    }

        // echo "</td>";
    // echo "</tr>";
    
?>
