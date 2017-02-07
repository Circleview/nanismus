
<html>
    <head>
        
        <?php
            //some kind of header information will re-occur on may pages
            include ("page_metadata.php");
            
            // receive and post production data
            $name = "Banane";
            
            // Send to the database that the watering event was initiated manually
            include ("initiate_watering_manually.php");
            
            include ("color_threshold_configuration.php");
            
            $Feuchte = $ColorThreshold0; // 100% green
            
            ?>
        
        <!-- css styles -->
        <style>
            
            /* http://www.w3schools.com/colors/colors_picker.asp */
            
            body {
                
                /* change the background color based on the current moisture of the plant.
                 * use green, yellow and red */
                background:
                
                <?php
                
                include ("color_threshold_configuration.php");
                
                if ($Feuchte > $ColorThreshold1 ){
                    echo "#82c837;"; /* green */
                }
                else if ($Feuchte > $ColorThreshold2 ){
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
            
            include ("color_threshold_configuration.php");
            
            if ($Feuchte > $ColorThreshold1 ){
                echo "#ffffff;"; /* green */
            }
            else if ($Feuchte > $ColorThreshold2 ){
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
                    <?php
    
                        include ("text_constants.php");
    
                        echo "$wateringInitiationSuccessText";
    
                        ?>

                </td>
            </tr>
            <tr>
                <td>
                    <!-- check if we need to include the watering button -->
                    <?php
                        
                        // echo "name: $name";
                        
                        include ("back_to_index_button.php");
                        ?>
                </td>
            </tr>
        </table>
        
        
    </body>
    
</html>
