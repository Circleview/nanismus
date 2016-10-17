
<html>
    <head>
        
        <?php
            //some kind of header information will re-occur on may pages
            include ("page_metadata.php");
            
            
            // receive the values of the form which was sent earlier and interprete the value
            
            // try to prevent bots from inputing and watering the plant
            // http://stackoverflow.com/questions/8472/practical-non-image-based-captcha-approaches

            // what was in the input field?
            $botname = $_POST[name];
            
            /* 
             If AntiSpam = A Integer
                If AntiSpam >= 10
                    Comment = Approved
                Else
                    Comment = Spam
             Else
                Comment = Spam
             */
            
            if (is_numeric($name)){
                if ($botname >= 2){
                    $botname = "human";
                }
                else {
                    $botname = "bot";
                }
            }
            else {
                $botname = "notnumeric";
            }
            
            // receive and post test data
            $name = "Test";
            
            // Send to the database that the watering event was initiated manually
            include ("initiate_watering_manually_test.php");
            // $Feuchte = 100;
            
            ?>
        
        <!-- css styles -->
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
