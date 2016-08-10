<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml"  xml:lang="de" lang="de">

<head>
<title>Nanismus - Bananenbew&auml;sserung</title>


<!-- page metadata -->
<!-- define the rules to deal with mobile devices -->
<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no" />
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />

<!-- PHP load data from mySQL database to show on this page -->
<?php
    
    include ("src/dbabfrage.php");
    
?>

</head>

<body>

    <h1>Feuchtigkeit: <?php echo "$Feuchte %"; ?></h1>

</body>

</html>