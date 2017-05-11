<?php
    
    function weekdayToGermanWeekdayNameString($weekdayInt){
        
        // echo "timestamp: "; echo $timestamp; echo " ";
        // echo "weekdayInt: "; echo $weekdayInt; echo " ";
        
        $weekdayString = "";
        
        switch ($weekdayInt) {
                
            case 1:
                $weekdayString = "Montag";
                break;
            case 2:
                $weekdayString = "Dienstag";
                break;
            case 3:
                $weekdayString = "Mittwoch";
                break;
            case 4:
                $weekdayString = "Donnerstag";
                break;
            case 5:
                $weekdayString = "Freitag";
                break;
            case 6:
                $weekdayString = "Sonnabend";
                break;
            case 0:
                $weekdayString = "Sonntag";
                break;
        }
        
        return $weekdayString;
    }
    
    ?>
