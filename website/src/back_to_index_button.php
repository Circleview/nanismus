
<!-- display the button that links to the index page -->

<form action=

<?php
    if ($name == "Test"){
        
        // return to the test index page
        echo "../index_test.php";
        
    }
    else {
        echo "../index.php";
    }
    ?>

     method="post">
    <input type="submit" value="zur&uuml;ck" id="watering_button"/></p>
</form>