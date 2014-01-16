<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">

<?php

//header('Content-Type: text/html; charset=utf-8');

/* Problem mit utf-8 Konvertierung und Zeichenanzeige im
 * Zusammenhang mit einer MYSQL Datenbank, konnte durch den
 * HTML Tag weiter unten nicht abgefangen werden
 * http://www.winfuture-forum.de/index.php?showtopic=193063
 * brachte die Lösung mit dem PHP Header Tag.
 */
  
//session_start();

// Funktionen

//$arduino_ip="nanismus.no-ip.org";
//$arduino_ip="192.168.178.30";
//$arduino_port="24";

$debug = 0;             // throws additional information if true
$test = 0;               // grab different data from different databases due to the testcontext

global $delay;
       $delay = 0.3; // delay in seconds
                 // http://de1.php.net/manual/de/function.usleep.php
       $delay = $delay * 1000000; // convert into microseconds

/*
 * Arduino States für Feuchtigkeit und Wohlbefinden der Pflanze
 * // state
    #define WATER_OVERFLOW 5               // There is water on the ground of the pot 
    #define NO_WATER_OVERFLOW 4            // There is no Water on the ground                                         
    #define URGENT_SENT 3                  // Soil is critically dry
    #define SOON_DRY_SENT 2                // Soil is not moist anymore
    #define MOISTURE_OK 1                  // Soil is moist
    #define TOOWET 0                       // Soil is too wet 
*/

?>


<head>
    <title>Nanismus - Bananenbew&auml;sserung</title>

    <!-- Scripte für den Slider-Effekt -->
    <script type="text/javascript" src="//use.typekit.net/vue1oix.js"></script>
    <script type="text/javascript">try{Typekit.load();}catch(e){}</script>        
    <script src="src/jquery-1.10.2.min.js"></script>
    <script src="src/modernizr.js"></script>
    <script src="src/block_slider_javascript.js"></script>  

    <!-- Skript für das iPod Rad zum Bestimmen der Wassermenge -->
    <!--script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script-->
    <script src="src/jquery.knob.js"></script>
    <script src="src/jquery.ipodwheel.js"></script>    
    
    <!-- Stylesheets -->
    <!-- Fremdlink - Sollte noch auf den eigenen Server umgezogen werden
    das war aber auf die Schnelle nicht möglich, weil es Probleme mit der Darstellung des Webfonts gab -->
    <link rel="stylesheet" href="http://www.inserthtml.com/demos/layout/icons/ss-standard.css" />
    <link rel="stylesheet" href="css/style_umbau.css" />

	<!-- // Homebildschirm-Icon
    http://www.winfuture-forum.de/index.php?showtopic=191412 -->
	<!--link rel="apple-touch-icon" href="images/home_icon.ico" / -->       <!-- Mit Glossy-Effekt -->
	<link rel="apple-touch-icon-precomposed" href="images/home_icon.ico" />	<!-- Ohne Glossy-Effekt -->

    <!-- Favicon -->
    <link href="images/favicon_gruen" type="image/x-icon" rel="shortcut icon" />

    <!-- Ever Needed a transparent 1x1 png? get one here:
    http://www.1x1px.me/ -->
    
    <!-- Metadaten der Seite -->
    <!-- Wie wird mit mobilen Endgeräten umgegangen? -->
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no" />
    
    <meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <meta name="keywords" content="Bew&auml;sserung, Arduino, Gardening, Selfmade, Pflanzenbew&auml;sserung" />
    
    <!-- Für Testzwecke -->
    <!-- http://iphone4simulator.com/ -->
 
<!-- PHP für die Bewässerungsregeln und zum Laden der Seiteninhalte aus der MySQL Datenbank -->
<?php include ("src/dbabfrage.php");
?>

<!-- PHP, um zu ermitteln, ob das Gießen über die Website überhaupt erlaubt ist -->
<?php include ("src/giessregel.php");?>

<!-- PHP, um das Temperatur-Diagramm aufbauen zu können -->
<?php include("src/tempchart.php"); ?>
<!-- PHP, um das Feuchtigkeits-Diagramm aufbauen zu können -->
<?php include("src/moistchart.php"); ?>
<!-- PHP, um die Fotos der IP Cam zu laden -->
<?php include("src/ipcamfotos.php"); ?>

<!-- Script zum absenden von Formulardaten mit einem Klick auf einen Link --> 
<!--script type="text/javascript">
    window.onload = function(){
	document.testform.submit();
}
</script>

 
<!-- Script das den Slider-Effekt und den Text ermöglicht. -->    
<script>

$(document).ready(function() {

	$('#block-slide').blockSlide({

		'imgurl':	'images/transparent.png',
		'animation':'zoom'

	},

	{

		'images/bubbles.png' :    "<h1>Pflanzengespr&auml;ch</h1>"
		                <?php 
		                  
		                  // Bei Bedarf kann die Anzahl der anzuzeigenden Nachrichten
		                  // hier angepasst werden
		                  // $msgcount = 3;
		                
                          for ($i = 0; $i < $msgcount; $i++)
                          {
    						echo "+"; echo '"<p>'; 
                                $zeit = strtotime($msgtime[$i]); 
                                echo "<strong>Nani "; 
                                echo date("d.m. H:i", $zeit); echo " Uhr";
                                echo "</strong><br>";
                                //echo '"';
                                //echo "+"; echo '"</p>"';
                                
                                //echo "+"; echo '"<p>';
                                echo "<span style='position: relative; left: 5px;'>";     
        						echo "$msg[$i] ";
        						echo '</span>';
        						echo '"';
        						echo "+"; echo '"</p>"';
                          }
		                
		                ?>

						+"<p>"
						+"</p>"
						//+"<div class='button'>Weiterlesen</div>"
					+"</div>",


        'images/leaf.png' :    "<h1>Feuchtigkeit: <?php echo "$Feuchte %"; ?></h1>"
                                                 
						
                        <?php 
                        // Zu Testzwecken kann hier die ausgelesene Feuchtigkeit manipuliert werden.
                        //$Feuchte = 23;
                        echo "+"; echo '"<p>'; 
                        if ($Feuchte >= 90)
                        {
                            echo "Meine Erde ist, ehrlich gesagt, gerade zu feucht.";
                            echo '</p>"'; 
                            echo "+"; echo '"<p>So feucht war die Erde in den letzten '.$auswertzeitraum.' Tagen."';
                            echo "+"; echo '"</p>"';
                        }
                        else if ($Feuchte >=40) 
                        {
                            echo "Meine Erde ist ausreichend feucht.";
                            echo '</p>"';
                            echo "+"; echo '"<p>So feucht war die Erde in den letzten '.$auswertzeitraum.' Tagen."';
                            echo "+"; echo '"</p>"';                            
                        }
                        else if ($Feuchte >= 25)
                        {
                            echo "Meine Erde ist schon ziemlich trocken.";
                            echo '</p>"';
                            echo "+"; echo '"<p>So feucht war die Erde in den letzten '.$auswertzeitraum.' Tagen."';
                            echo "+"; echo '"</p>"';                            
                        }
                        else 
                        {
                            echo "Meine Erde ist sehr trocken!";
                            echo '</p>"';
                            echo "+"; echo '"<p>So feucht war die Erde in den letzten '.$auswertzeitraum.' Tagen."';
                            echo "+"; echo '"</p>"';                            
                        }?>
                        +"<div id='moistchart_div'></div>" // Einbindung des Diagramms der Feuchtigkeit"
                        <?php /*echo "+"; echo '"<p>Letzter Messwert vom: '; $zeit = strtotime($letztes_Lebenszeichen); echo date("d.m.Y H:i:s", $zeit); echo '"'; 
                        echo "+"; echo '"</p>"';
                        */?>                        
						//+"<div class='button'>Zeige &Uuml;berflutungen</div>"
					+"</div>",

	
		        <?php
		        
                // Wenn eine Überflutung (das Wasser lief bis in den Untertopf)
                // vorliegt, dann soll dieses Sybol angezeigt werden, sonst nicht.

                // Zu Testzwecken kann der Topfwert (Also der Wasserstand im Untertopf) manipuliert werden
                //echo "&Uuml;berlaufwert: $ueberlaufwert";
                //$Topfwert = 60;
                if (($Topfwert < $ueberlaufwert))
                {
                    // Sybol nicht anzeigen
                    // echo "Wasser im &Uuml;bertopf: NEIN";
                }
                else {
                    // Symbol anzeigen
		echo "'images/warning.png' :    "; echo '"<h1>&Uuml;berflutung!</h1>"';
						echo "+"; echo '"<p>Ich habe sehr nasse F&uuml;&szlig;e!"';
						echo "+"; echo '"</p>"';
						echo "+"; echo '"<p>Letzte Messung: ';
						$zeit = strtotime($ueberlaufzeit); echo date("d.m.Y H:i:s", $zeit);
                        echo 'Uhr"';
                        echo "+"; echo'"</p>"';
                        echo "+"; echo '"<p>Gie&szlig;e beim n&auml;chsten Mal bitte etwas weniger."';
                        echo "+"; echo'"</p>"';
						//+"<div class='button'>Weiterlesen</div>"
					echo "+";echo '"</div>"'; echo ",";
                
                }
                ?>

                <?php 

                // Wenn der Wassertank leer ist,
                // dann soll dieses Sybol angezeigt werden, sonst nicht.

                // Zu Testzwecken kann der Wassertankwert manipuliert werden
                //$tankleer = true;
                
                if ($tankleer == false)
                {
                    // Sybol nicht anzeigen
                    // echo "Wassertank ist noch voll";
                }
                else {
                    // Symbol anzeigen
		echo "'images/batteryalt30.png' :    "; echo '"<h1>Wassertank leer</h1>"';
						echo "+"; echo '"<p>Mein Wassertank ist leer!"';
						echo "+"; echo '"</p>"';
						echo "+"; echo '"<p>Letzte Messung: ';
						$zeit = strtotime($wassertankmesszeit); echo date("d.m.Y H:i:s", $zeit);
                        echo 'Uhr"';
                        echo "+"; echo'"</p>"';
                        echo "+"; echo '"<p>So kann nicht mehr gew&auml;ssert werden."';
                        echo "+"; echo'"</p>"';
						//+"<div class='button'>Weiterlesen</div>"
					echo "+";echo '"</div>"'; echo ",";

                }                
                ?>

		        <?php

                // Wenn eine Wässerung möglich ist, dann soll das Symbol angezeigt werden, sonst nicht

                // Zu Testzwecken kann der Topfwert (Also der Wasserstand im Untertopf) manipuliert werden
                //echo "&Uuml;berlaufwert: $ueberlaufwert";
                // $giessenerlaubt = true;
                if (!$giessenerlaubt)
                {
                    // Sybol nicht anzeigen
                    //echo "W&auml;sserung erlaubt?: NEIN";
                }
                else {
                    // Symbol anzeigen
		echo "'images/watertap.png' :    "; echo '"<h1>Lass es regnen!</h1>"';
						echo "+"; echo '"<p>Pflanzen brauchen Zuneigung und Wasser."';
						echo "+"; echo '"</p>"';
						echo "+"; echo '"<p>Lege die Wassermenge durch drehen am Rad fest."';
                        // Einbeinden des Scrollrads über iframe, ich bekomme das gerade nicht anders hin, 
                        // da die javascripte für den Slider und das iPodrad geegenseitig stören
                        echo "+"; echo '"<iframe src='; echo "'./src/wasser/ipod.html'name='iPod wheel' class='ipodiframe' scrolling='no' "; echo '>"';
                        echo "+"; echo '"<p>Ihr Browser kann leider keine eingebetteten Frames anzeigen: Sie k&ouml;nnen die eingebettete Seite &uuml;ber den folgenden Verweis aufrufen: <a href='; echo "'pod.html'"; echo '>Link</a></p>"';
                        echo "+"; echo '"</iframe>"';                      
					echo ",";

                }
                ?>

		'images/thermometer.png' :    "<h1>Temperatur: <?php echo "$temperatur °C"; ?></h1>"
						+"<p>So warm war es der Nani in den letzten 15 Tagen.</p>"
						+"<div id='tempchart_div' style='width: 100%; '></div>" // Einbindung des Diagramms der Temperatur
						//+"<div class='button'>Zeige Messewerte</div>"
					+"</div>",

		'images/camera.png' :    "<h1>Bitte l&auml;cheln!</h1>"
						+"<p>Jeden Tag ein neues Foto.</p>"
						+"<div style='margin-left: auto; margin-right: auto; width: 90%; position: relative; top: 10px;'><a href='<?php echo $dateiinfo['dirname']."/".$dateiinfo['basename'];?>'>"
                        +"<img src='<?php echo $ordner."/".$dateiinfo['basename'] ?>' width='300px'/></a></div>"
						//+"<div class='button'>Zeige Messewerte</div>"
					+"</div>",                                                                                                                                                                      
                
                <?php

                // Wenn keine Verbindung zum Arduino mehr zu bestehen scheint,
                // dann soll dieses Sybol angezeigt werden, sonst nicht.
                // Die Verbindung scheint immer dann nicht zu bestehen, wenn der
                // letzte Datenbankeintrag läger als 40 Minuten in der Vergangenheit liegt
                
                // Zu Testzwecken kann der Verbindungswert manipuliert werden
                //$VerbindungArduino = false;

                if ($VerbindungArduino)
                {
                    // Sybol nicht anzeigen
                    // echo "Die Verbindung zum Arduino besteht";
                }
                else {
                    // Symbol anzeigen
		echo "'images/power-cord.png' :    "; echo '"<h1>Verbindung unterbrochen!</h1>"';
						echo "+"; echo '"<p>Die Nani sendet keine Daten mehr!"';
						echo "+"; echo '"</p>"';
						echo "+"; echo '"<p>Letzte Datenmeldung: ';
						$zeit = strtotime($letztes_Lebenszeichen); echo date("d.m.Y H:i:s", $zeit);
                        echo ' Uhr"';
                        echo "+"; echo'"</p>"';
                        echo "+"; echo '"<p>Hat Robi etwas damit zu tun?"';
                        echo "+"; echo'"</p>"';
						//+"<div class='button'>Weiterlesen</div>"
					echo "+";echo '"</div>"'; echo ",";

                }
                ?>
	});
});

</script>

    
</head>

<body scroll="no" style="overflow:hidden;">

<div align="center" id="bg">
    <!-- "Hintergrundfoto hinter der Slider - Animation" -->
    <!-- http://www.free-solutions.de/js/dokument_bildformat_dynamisch_erzwingen.html -->
    
    <script language="javascript" type="text/javascript">      //Die Auflösung des Hintergrundbildes soll von der Anzeigebreite des Bildschirms abhänig sein.
    if(window.innerWidth <= 340) 
    {
      document.write("<img src='images/nanifoto_400.jpg' id='bgimage' />");
    }
    else if (window.innerWidth <= 800) 
    {
      document.write("<img src='images/nanifoto_800.jpg' id='bgimage' />");
    }
    else 
    {
      document.write("<img src='images/nanifoto_1500.jpg' id='bgimage' />");  
    }

    </script>
</div>
		
<!-- http://www.inserthtml.com/2013/08/modal-image-slider/ -->
<div id="block-slide">

</div>
			
<div style="visibility: 
<?php if ($debug){
    echo "visible;";
} 
else {
    echo "hidden";
}?>;">

		<p style="text-align: center;">
			<span style="font-family:lucida sans unicode,lucida grande,sans-serif;">
            <br /><br /><br /><br /><br /><img alt="Bild einer kleinen Bananenpflanze" src="http://www.maastrek-werbeartikel.de/img/artikel/big/624Banane.jpg" style="width: 200px; height: 200px; margin-top: 10px; margin-bottom: 10px;" /></span>
            </p>
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
            letztes Lebenszeichen: <?php // http://php.net/manual/de/function.date.php
            $zeit = strtotime($letztes_Lebenszeichen); echo date("d.m.Y H:i:s", $zeit); ?></span></p> 
		<p style="text-align: center;">
			<span style="font-size:9px; font-family:lucida sans unicode,lucida grande,sans-serif;">
            letzte W&auml;sserung: <?php  echo $letzte_Giessung; ?></span></p>            
		<p style="text-align: center;">
			<span style="font-size:9px; font-family:lucida sans unicode,lucida grande,sans-serif;">
            letzte NachrichtID: <?php  echo $msgid; ?></span></p>                       
		<p style="text-align: center;">
			<span style="font-size:9px; font-family:lucida sans unicode,lucida grande,sans-serif;">
            letzte Nachricht: <?php  echo $msg; ?></span></p>	
</div>
           		
	</body>

</html>