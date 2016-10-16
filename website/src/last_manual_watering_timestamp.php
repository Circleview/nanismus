<?php
    
    //connect to database
    //------------------------------
    // include ("db.php");
    
    function minutesToSeconds ($inputMinutes){
        
        return $inputMinutes * 60;
        
    }
    
    function hoursToSeconds ($inputHours){
        
        return $inputHours * 60 * 60;
        
    }
    
    function daysToSeconds ($inputDays){
        
        return $inputDays * 24 * 60 * 60;
        
    }
    
    function pathToPicture ($inputMoistureValue) {
        
        // The picture that will be displayed, depends on the moisture of the plant
        
        if ($inputMoistureValue >=41){
            echo "'../images/plantwatering_white.svg'"; /* green */
        }
        else if ($inputMoistureValue >=21){
            echo "'../images/plantwatering_black.svg';"; /* "#F2EC38;"; /* yellow */
        }
        else {
            echo "'../images/plantwatering_white.svg'"; /* red */
            
        }
        
    }
    
    function timestampWeekday($timestamp){
        
        // echo "timestamp: "; echo $timestamp; echo " ";
        
        $weekdayInt = date('w', $timestamp);
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
    
    function lastWateringInitiationStatus ($plantname){

        // Get the status of the watering intitiation to check if the watering event was just initiated or if the watering already took place

        //connect to database
        //------------------------------
        include ("db.php");
        
        $tabelle = "initiate_watering_events";
        
        $sql = "
        SELECT $tabelle.watering_initiated
        FROM $tabelle
        WHERE ($tabelle.name = '$plantname')
        ORDER BY $tabelle.ID DESC LIMIT 1
        ";
        
        $db_erg = mysqli_query( $db_link, $sql );
        if ( ! $db_erg )
        {
            die('invalid request: ' . mysqli_error($db_link));
        }

        while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
        {
            $status = $row['watering_initiated'];
        }
        
        return $status;
    }
    
    function lastManualWateringInitiationTimestamp ($plantname){
        
        // Get the timestamp of the last manual watering intitiation
        
        //connect to database
        //------------------------------
        include ("db.php");
        
        $tabelle = "initiate_watering_events";
        
        $sql = "
        SELECT $tabelle.timestamp
        FROM $tabelle
        WHERE ($tabelle.name = '$plantname') && ($tabelle.watering_initiated = 'reseted')
        ORDER BY $tabelle.ID DESC LIMIT 1
        ";
        
        $db_erg = mysqli_query( $db_link, $sql );
        if ( ! $db_erg )
        {
            die('invalid request: ' . mysqli_error($db_link));
        }
        
        while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
        {
            $timestamp = $row['timestamp'];
        }
        
        // convert database timestamp into different date and time formats http://www.schattenbaum.net/php/datum.php
        
        // convert into unix timestamp format
        $timestamp = strtotime($timestamp);
        
        // for test reasons - display the timestamp
        // echo "timestamp: $timestamp";
        
        // get the current time in unit timestamp format
        $currentTime = time();
        
        // for test reasons - display the currentTime
        // echo " currentTime: $currentTime";
        
        // display different timestamp strings, based on the amount of time that has passed since the last watering was manually initiated
        
        // if we substract unix timestamps we receive the amount of seconds that have been passed between these two timestamps
        $timePassed = $currentTime - $timestamp;
        
        // For Test reasons - change the timePassed
        // $timePassed = minutesToSeconds(100);
        
        // For Test reasons - display the timePassed
        // echo " timePassed: $timePassed";
        
        // every duration between 0 seconds and 45 minutes will be displayed in a certain way
        if ($timePassed > 0 && $timePassed <= minutesToSeconds(1)){
            
            $timestampString = " jetzt";
            
        }
        
        else if ($timePassed > minutesToSeconds(1) && $timePassed <= minutesToSeconds(5)){
            
            $timestampString = " gerade eben";
            
        }
        
        else if ($timePassed > minutesToSeconds(5) && $timePassed <= minutesToSeconds(45)){
            
            $timestampString = " vor wenigen Minuten";
            
        }
        
        // every duration between 45 minutes and 60 minutes will be displayed in a certain way
        else if ($timePassed > minutesToSeconds(45) && $timePassed <= minutesToSeconds(60)){
            
            $timestampString = " vor knapp einer Stunde";
            
        }

        // every duration between 1 hour and 3 hours will be displayed in a certain way
        else if ($timePassed > hoursToSeconds(1) && $timePassed <= hoursToSeconds(3)){
            
            $timestampString = "vor wenigen Stunden";
            
        }
        
        // every druation between 3 hours and 15 hours will be displayed in a certain way
        else if ($timePassed > hoursToSeconds(3) && $timePassed <= hoursToSeconds(15)){
            
            $timestampString = "vor einigen Stunden";
            
        }
        
        // every duration between 15 hours and 30 hours will be displayed in a certain way
        else if ($timePassed > hoursToSeconds(15) && $timePassed <= hoursToSeconds(30)){
            
            $timestampString = "vor knapp einem Tag";
            
        }
        
        // erery druation with more than 30 hours to 3 days will be displayed in a certain way
        else if ($timePassed > hoursToSeconds(30) && $timePassed <= daysToSeconds(2)){
            
            $timestampString = "vor wenigen Tagen";
            
        }
        
        // every duration with more than 2 days and less or egal 4 days will be displayed in a certain way
        else if ($timePassed > daysToSeconds(2) && $timePassed <= daysToSeconds(4)){
            
            $timestampString = "vor einigen Tagen";
            
        }
        
        // every duration with more than 4 days will be displayed in a certain way
        elseif ($timePassed > daysToSeconds(4) && $timePassed <= daysToSeconds(7)){
            
            $timestampString = "am letzten ";
            $timestampString .= timestampWeekday($timestamp);
            
        }
        
        // every duration with more that 7 days
        elseif ($timePassed > daysToSeconds(7)){
            
            $timestampString = "am ";
            $timestampString .= date('d.m.Y',$timestamp);
            
        }
        
        return $timestampString;
        
    }
    
    // Display the data on the website
    
    // Check if this feature is toogled true - The feature toogle is received from the index page
    
    if (showLastManualWateringInitiationTimestamp) {
        
        echo "<tr>";
            echo "<td class = 'mediumTableData'>";
        
                // show a nice little picture
                echo "<img id='wateringCanWithWateringTimestamp' src =";
                echo pathToPicture($Feuchte);
                echo ">";
        
            // echo "</td>";
            // echo "<td class = 'mediumTableData'>";
        
                // echo "$name";
                // echo "<br>";
        
                // If the last status of the manual watering initiation was "initiated", which means that the watering event just recently was initiated but did not acually took place in the pot of the plant then show that the watering was only initiated otherwise show the true timestamp of the watering event
        
        if (lastWateringInitiationStatus($name) == "initiat"){
            
            // Show that the watering was initiated but physically did not took place
            
            //fetch the string that says that the watering was initiated but currently did not took place
            include ("text_constants.php");
            
            echo " "; echo $wateringInitiationStatusInformation;
            
        }
        elseif (lastWateringInitiationStatus($name) == "reseted"){
        
            // show the timestamp of the real watering event that physically took place in the plant pot
            
            // Get the name of the plant from $name - defined in the index.php
            echo " "; echo lastManualWateringInitiationTimestamp($name);
        }
        
            echo "</td>";
        echo "</tr>";

    }
    
    ?>
