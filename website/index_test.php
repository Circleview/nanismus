<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml"  xml:lang="de" lang="de">

<head>

<!-- some kind of header information will re-occur on may pages -->
<?php
 include ("src/page_metadata.php");
          ?>

<!-- PHP load data from mySQL database to show on this page -->
<?php
    
    // on production we don't want to see the test data
    $name = "Test";
    
    include ("src/dbabfrage.php");
    
    /* for tests */
    // $Feuchte = 18;
    
?>

<!-- dynamic styles based on database value -->
<style>
    
    /* http://www.w3schools.com/colors/colors_picker.asp */
    
    body {
        
        /* change the background color based on the current moisture of the plant. 
         * use green, yellow and red */
        background:

        <?php

            if ($Feuchte >=41){
                echo "#82c837;"; /* green */
            }
            else if ($Feuchte >=21){
                echo "#FFFF33;"; /* "#F2EC38;"; /* yellow */
            }
            else {
                echo "#C8373A;"; /* red */
            }
    
        ?>
    }

    td {
        
        /* change the td font color based on the current moisture of the plant.
         * use green, yellow and red */
        color:

        <?php

        if ($Feuchte >=41){
            echo "#ffffff;"; /* green */
        }
        else if ($Feuchte >=21){
            echo "#000000;"; /* "#F2EC38;"; /* yellow */
        }
        else {
            echo "#ffffff;"; /* red */
    
        }

        ?>
    }

</style>

</head>

<body>

    <table>
        <tr>
            <td>
                Feuchtigkeit: <?php echo "$Feuchte %"; ?>
            </td>
        </tr>
        <tr>
            <td>
                <!-- check if we need to include the watering button -->
                <?php
                    include ("src/watering_button.php");
                    ?>

            </td>
        </tr>
    </table>


</body>

</html>