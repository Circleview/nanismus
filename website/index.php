<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml"  xml:lang="de" lang="de">

<head>
<title>Nanismus - Bananenbew&auml;sserung</title>


<!-- page metadata -->
<!-- define the rules to deal with mobile devices -->
<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no" />
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />

<!-- // iOS Homescreen-Icon
    https://developer.apple.com/library/ios/documentation/AppleApplications/Reference/SafariWebContent/ConfiguringWebApplications/ConfiguringWebApplications.html -->
<!-- https://icomoon.io/app/ // find nice icons fast and free of charge -->
<!-- http://www.colorpicker.com/309c0c // tiny colorpicker -->
<!-- current leaf color 309c0c -->
<link rel="apple-touch-icon" sizes="120x120" href="/images/leaf.png">

<!-- Favicon -->
<link href="images/favicon" type="image/x-icon" rel="shortcut icon" />

<!-- Stylesheets -->

<link rel="stylesheet" href="css/style.css" />



<!-- PHP load data from mySQL database to show on this page -->
<?php

    include ("src/dbabfrage.php");

?>

<!-- dynamic styles based on database value -->
<style>
    
    /* http://www.w3schools.com/colors/colors_picker.asp */
    
    body {
        
        background:

        <?php

            /* for tests
            $Feuchte = 18;
             */

            if ($Feuchte >=40){
                echo "#82c837;"; /* green */
            }
            else if ($Feuchte >=20){
                echo "#FFFF33;"; /* "#F2EC38;"; /* yellow */
            }
            else {
                echo "#C8373A;"; /* red */
            }
    
        ?>
    }

    td {
        color:

        <?php

        /* for tests
         $Feuchte = 18;
         */

        if ($Feuchte >=40){
            echo "#ffffff;"; /* green */
        }
        else if ($Feuchte >=20){
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
    </table>

</body>

</html>