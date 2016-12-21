<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml"  xml:lang="de" lang="de">

<head>

<!-- http://www.responsinator.com/?url=http://nanismus.no-ip.org&device=iphone-6&orientation=portrait --> 

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
    $showIpCamPicture = true; 
    
    ?>

<!-- PHP load data from mySQL database to show on this page -->
<?php
    
    // on production we don't want to see the test data
    $name = "Test";
    
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

</style>
<!-- if the feature toggle is swiched on include all the data that is needed to display the moisture diagram -->
<?php
    if ($showMoistureChart) {
        
        include ("src/moistchart_setup.php");
        
    }
    ?>

<?php
    
    // try to prevent bots from inputing and watering the plant
    // http://stackoverflow.com/questions/8472/practical-non-image-based-captcha-approaches
    // echo "<input type='text' name='name' value='name' />";
    
    
    echo "<script>";
    
    echo " var antiSpam = function() {";
    echo " if (document.getElementById('name')) {";
    echo " a = document.getElementById('name'); ";
    echo " if (isNaN(a.value) == true) {";
    echo " a.value = 0;";
    echo " } else {";
    echo " a.value = parseInt(a.value) + 1; ";
    echo " }";
    echo " }";
    echo " setTimeout('antiSpam()', 1000); ";
    echo " }";
    
    echo " antiSpam();";
    
    echo " </script>";
    
    ?>
</head>

<body>

    <table>
        <tr>
            <td>

                <! -- build up the content of this row and show an icon of a waterdrop and the current moisture value -->
                <?php
                    include ("src/moisture_value_display.php");
                    ?>
            </td>
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
                include ("src/ipcamfotos.php");
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
