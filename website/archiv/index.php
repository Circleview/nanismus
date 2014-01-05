<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">

<head>
    <title>Nanismus - Bananenbew&auml;sserung</title>

    <meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <meta name="keywords" content="" />
    <meta name="generator" content="Webocton - Scriptly (www.scriptly.de)" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<!-- // Homebildschirm-Icon
    http://www.winfuture-forum.de/index.php?showtopic=191412 -->
	<!--link rel="apple-touch-icon" href="images/home_icon.ico" />             <!-- Mit Glossy-Effekt -->
	<link rel="apple-touch-icon-precomposed" href="images/home_icon.ico" />	<!-- Ohne Glossy-Effekt -->
	
    <!--link rel="stylesheet" href="css/base.css" />
	<link rel="stylesheet" href="css/skeleton.css" />
	<link rel="stylesheet" href="css/layout.css" />
    <link rel="stylesheet" type="text/css" href="css/grafikstyles.css" />
    <link rel="stylesheet" type="text/css" href="css/comments.css" /-->

    <link href="images/favicon_gruen" type="image/x-icon" rel="shortcut icon" />
</head>

	</head>
	<body bgcolor="white">
	
		<p style="text-align: center;">
			<span style="font-family:lucida sans unicode,lucida grande,sans-serif;"><strong>Hallo liebe Gie&szlig;erin, </strong></span></p>
		<p style="text-align: center;">
			<span style="font-family:lucida sans unicode,lucida grande,sans-serif;"><strong>hier w&auml;chst bald wieder die Nani. </strong></span></p>
		<p style="text-align: center;">
			<span style="font-family:lucida sans unicode,lucida grande,sans-serif;"><img alt="Bild einer kleinen Bananenpflanze" src="http://www.maastrek-werbeartikel.de/img/artikel/big/624Banane.jpg" style="width: 200px; height: 200px; margin-top: 10px; margin-bottom: 10px;" /></span></p>
			
	<?php
        $db_link = mysqli_connect (
         "localhost",             // MYSQL_HOST,
         "arduino",               // MYSQL_BENUTZER,
         "!Pflanzenprojekt2012",  // MYSQL_KENNWORT,
         "plantdata_test"         // MYSQL_DATENBANK
        );

        // Ermittlung der Lufttemperatur
        $sql = "
        SELECT plant_log_test.value
        FROM plant_log_test
        WHERE (((plant_log_test.sensorname)='Temperatur') AND ((plant_log_test.logtype)='Messwert'))
        ORDER BY plant_log_test.ID DESC LIMIT 1
        ";

        $db_erg = mysqli_query( $db_link, $sql );
        if ( ! $db_erg )
        {
        die('Ungültige Abfrage: ' . mysqli_error());
        }

        while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
        {
        $temperatur = $row['value'];
        //echo "aktuelle Temperatur: ", $value,"<br /><br />";
        }

        // Ermittlung der Bodenfeuchte der Bananenpflanze
        $sql = "
        SELECT plant_log_test.value
        FROM plant_log_test
        WHERE (((plant_log_test.sensorname)='Banane') AND ((plant_log_test.logtype)='Prozentfeuchte'))
        ORDER BY plant_log_test.ID DESC LIMIT 1
        ";

        $db_erg = mysqli_query( $db_link, $sql );
        if ( ! $db_erg )
        {
        die('Ungültige Abfrage: ' . mysqli_error());
        }

        while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
        {
        $Feuchte = $row['value'];
        //echo "aktuelle Temperatur: ", $value,"<br /><br />";
        }

        // Ermittlung, ob Wasser im Übertopf ist
        $sql = "
        SELECT plant_log_test.value
        FROM plant_log_test
        WHERE (((plant_log_test.sensorname)='Banane_Topf') AND ((plant_log_test.logtype)='Messwert'))
        ORDER BY plant_log_test.ID DESC LIMIT 1
        ";

        $db_erg = mysqli_query( $db_link, $sql );
        if ( ! $db_erg )
        {
        die('Ungültige Abfrage: ' . mysqli_error());
        }

        while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
        {
        $Topfwert = $row['value'];
        //echo "aktuelle Temperatur: ", $value,"<br /><br />";
        }

        // Ermittlung des letzten Datenbankeintrags
        $sql = "
        SELECT plant_log_test.timestamp
        FROM plant_log_test
        ORDER BY plant_log_test.ID DESC LIMIT 1
        ";

        $db_erg = mysqli_query( $db_link, $sql );
        if ( ! $db_erg )
        {
        die('Ungültige Abfrage: ' . mysqli_error());
        }

        while($row = mysqli_fetch_array($db_erg, MYSQL_ASSOC))
        {
        $letzte_Zeit = $row['timestamp'];
        //echo "aktuelle Temperatur: ", $value,"<br /><br />";
        }        

        

                            
     ?>
		<p style="text-align: center;">
			<span style="font-size:15px; font-family:lucida sans unicode,lucida grande,sans-serif;">
            Lufttemperatur: <?php  echo $temperatur," °C"; ?></span></p>
		<p style="text-align: center;">
			<span style="font-size:15px; font-family:lucida sans unicode,lucida grande,sans-serif;">
            Bodenfeuchte: <?php  echo $Feuchte," %"; ?></span></p>
		<p style="text-align: center;">
			<span style="font-size:15px; font-family:lucida sans unicode,lucida grande,sans-serif;">
            Wasser&uuml;berlauf: 
            <?php
                if ($Topfwert >=50) {echo "ja";}
                else {echo "nein";} ?>
            </span></p>
		<p style="text-align: center;">
			<span style="font-size:9px; font-family:lucida sans unicode,lucida grande,sans-serif;">
            letztes Lebenszeichen: <?php  echo $letzte_Zeit; ?></span></p>            
			
	</body>

</html>