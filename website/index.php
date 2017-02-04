<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml"  xml:lang="de" lang="de">

<head>

<!-- some kind of header information will re-occur on may pages -->
<?php
    include ("src/page_metadata.php");
    ?>

<!-- Define feature toogles -->
<?php
    
    // feature toggle to display the last manual watering initiation timestamp
    $showLastManualWateringInitiationTimestamp = true;
    
    // feature toggle to display the twitter timeline for nanismusKW
    $showTwitterTimeline = true;
    
    // feature toggle to display the moisture chart
    $showMoistureChart = true;
    
    // feature toogle to display the last picture of the ip cam
    $showIpCamPicture = false;
    
    ?>

<!-- PHP load data from mySQL database to show on this page -->
<?php
    
    // on production we don't want to see the test data
    $name = "Banane";
    
    include ("src/dbabfrage.php");
    
    /* for tests */
    // $Feuchte = 44;
    
    ?>

<!-- include aditional data -->
<!-- text constants which will be displayed as labels -->
<?php
    
    include ("src/text_constants.php");
    ?>

<!-- dynamic styles based on database value -->
<style>

/* http://www.w3schools.com/colors/colors_picker.asp */

body {
    
    /* change the background color based on the current moisture of the plant.
     * use green, yellow and red */
background:
    
    <?php
    include ("src/background_color.php");
    echo backgroundColor($Feuchte); echo ";"; /* red */
    ?>
}

td {
    
    /* change the td font color based on the current moisture of the plant.
     * use green, yellow and red */
color:
    
    <?php
    include ("src/text_color.php");
    echo textColor($Feuchte); echo ";"; /* red */
    ?>
    
}

/*Include a script that fetches the path to the latest picture of the plant taken by a ip cam */
<?php
    include ("src/ipcam_picture_path.php");
    ?>


/* define the style for the td that contains the moisture drop icon and the latest moisture value*/
#moisture_drop_icon_with_ip_cam_picutre_background {

height: 200px;
background-image: url(<?php echo "'" . LastIpCamPicturePath() . "'" ?>);
background-repeat: no-repeat; background-size: 280px;
border: 0px solid black;
background-position: 50% 50%;
text-shadow: 0px 0px 10px rgba(255, 255, 255, 1); // https://css3gen.com/text-shadow/

}


</style>


<!-- if the feature toggle is swiched on include all the data that is needed to display the moisture diagram -->
<?php
    if ($showMoistureChart) {
        
        include ("src/moistchart_setup.php");
        
    }
    ?>

</head>

<body>

<table>
<tr>

<! -- build up the content of this row and show an icon of a waterdrop and the current moisture value -->
<?php
    include ("src/moisture_value_display.php");
    ?>

</tr>
<tr>
<td class="buttonTableData">
<!-- check if we need to include the watering button -->
<?php
    include ("src/watering_button.php");
    ?>

</td>
</tr>

<! -- build up the table row that displays the last manual watering intiation timestamp - if the feature toggle has been set -->
<?php
    include ("src/last_manual_watering_timestamp.php");
    ?>

<! -- build up the table row that displays the moisture data as a graph - if the feature toggle has been set -->

<?php
    include ("src/ipcam_picture.php");
    ?>


<! -- build up the table row that displays the moisture data as a graph -
if the feature toggle has been set -->
<?php
    include ("src/moistchart.php");
    ?>

<! -- build up the table row that displays the twitter timeline for nanismusKW -
if the feature toggle has been set -->
<?php
    include ("src/twitter_timeline.php");
    ?>

</table>

</body>

</html>
