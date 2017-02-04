
<?php

    if ($showIpCamPicture){
        
        echo "<tr>";
        echo "<td class = 'twitterTableData'>";
        
        echo "<a href='"; echo LastIpCamPicturePath(); echo "' target='_blank'>";
        echo "<img src='"; echo LastIpCamPicturePath(); echo "' style='width:285px' />";
        echo "</a>";
        
        echo "</td>";
        echo "</tr>";
    }
    
?>
