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

$arduino_ip="nanismus.no-ip.org";
//$arduino_ip="192.168.178.30";
$arduino_port="24";

$debug = 0;             // throws additional information if true
$test = 0;               // grab different data from different databases due to the testcontext

global $delay;
       $delay = 0.3; // delay in seconds
                 // http://de1.php.net/manual/de/function.usleep.php
       $delay = $delay * 1000000; // convert into microseconds

	/*
	DUMMY

	$Werte = array(555, 666);
	echo "$Werte[0], ";
	echo "$Werte[1], ";

	// http://xhtmlforum.de/49701-php-die-ersten-3-zeichen-eines.html
	// $text = "einstring";
	// echo "$text, ";
	$text_ohne_ersten_3_zeichen = substr("$Werte[1]", 0, 1);
	echo "$text_ohne_ersten_3_zeichen, ";

	// http://phpforum.de/forum/showthread.php?t=145722
	$stringMitLeerInDerMitte = "text1;text2;text3";

	list($wort1,$wort2)=explode(";",$stringMitLeerInDerMitte);
	echo "$wort1, $wort2";
	*/

function arduino_send($ip,$port,$command) {
    $res = @fsockopen($ip,$port);    // @ schaltet fehlermeldungen aus
    if($res) {
        fwrite($res,$command);

		global $returnvalue;
        $returnvalue = fread($res,2);

        //echo "Returnvalue: $returnvalue <br />" ;
    }
}

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
    
    <!-- Stylesheets -->
    <!-- Fremdlink - Sollte noch auf den eigenen Server umgezogen werden
    das war aber auf die Schnelle nicht möglich, weil es Probleme mit der Darstellung des Webfonts gab -->
    <link rel="stylesheet" href="http://www.inserthtml.com/demos/layout/icons/ss-standard.css" />
    <link rel="stylesheet" href="css/style_umbau.css" />


	<!-- // Homebildschirm-Icon
    http://www.winfuture-forum.de/index.php?showtopic=191412 -->
	<!--link rel="apple-touch-icon" href="images/home_icon.ico" / -->             <!-- Mit Glossy-Effekt -->
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
    <meta name="generator" content="Webocton - Scriptly (www.scriptly.de)" />
    
    <!-- Für Testzwecke -->
    <!-- http://iphone4simulator.com/ -->
 
<!-- PHP für die Bewässerungsregeln und zum Laden der Seiteninhalte aus der MySQL Datenbank -->
<?php if ($test){include ("src/dbabfrage_test.php");}
      else {include ("src/dbabfrage.php");}?>     
<!-- PHP Um die Datenübertragung an das Arduino zu ermöglichen, um einen Gießbefehl zu übermitteln -->
		<?php

            if(isset($_POST['waessern']))
            {

                // Wenn eine manuelle Bewässerung angeordnet wurde, dann soll diese
                // in die Log Tabelle geschrieben werden

                // Diese Funktion wird zunächst nicht in Betrieb genommen, da das
                // Arduino die Gießmenge in die Datenbank schreibt. Die Gießmenge
                // Wird zusammen mit dem Timestamp in der Tabelle erfasst, sodass ein
                // zusätzlicher Logtype keinen Vorteil zu bringen scheint.

                //Datenbank-Verbindung herstellen
                //--------------------------------
                /*
                 include("db.php");
                 //include("db_test.php");

                        $table = "plant_log" ;
                        //$table = "plant_log_test" ;
                        // !! WICHTIG !!
                        // der logtype ist für ein Gießevent noch zu definieren, für das
                        // Gießen gibt es bisher keinen logtype

                        // http://stackoverflow.com/questions/1995562/now-function-in-php
                        $timestamp = date("Y-m-d H:i:s");
                        $sql = "
                          INSERT INTO $table
                          (
                          sensorname , logtype , value , timestamp
                          )
                          VALUES
                          (
                          '$name', '$type', $value, '$timestamp'
                          )
                        ";
                        $db_erg = mysqli_query($db_link, $sql)
                            or die("<p>Anfrage fehlgeschlagen</p>" . mysqli_error($db_link));

                            echo "Dateneingabe erfolgreich";
                        }
                        else
                        {
                            echo "Dateneingabe fehlerhaft";
                        }
                */

                // Sende den Gießbefehl an das Arduino Board
                arduino_send($arduino_ip,$arduino_port,"P".chr($menge));
                // Gießung anordnen
    			// Warte x * 1.000.000 Mikro-Sekunden
                usleep($delay); // to avoid connection refusal

            } /*else
            {
                //arduino_send($arduino_ip,$arduino_port,"M".chr(0));
                arduino_send($arduino_ip,$arduino_port,"M".chr(0)); // Gibt den aktuellen Feuchtigkeitswert als Status aus
				// Warte x * 1.000.000 Mikro-Sekunden
                usleep($delay); // to avoid connection refusal
            }

            if ($debug)
            {
                echo "Prozentfeuchte 0: $prozentfeuchte[0] %<br>";
                echo "Prozentfeuchte 1: $prozentfeuchte[1] %<br>";
                echo "Prozentfeuchte 1: $prozentfeuchte[2] %<br>";
                echo "Returnvalue 0: $returnvalue[0] <br>";
                echo "Returnvalue 1: $returnvalue[1] <br>";
                echo "Returnvalue 1: $returnvalue[2] <br>";
            }
            */
        ?>

<!-- PHP, um zu ermitteln, ob das Gießen über die Website überhaupt erlaubt ist -->
<?php
    include ("src/giessregel.php");
?>

<!-- Script zum absenden von Formulardaten mit einem Klick auf einen Link --> 
<script type="text/javascript">
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
						+"<p><?php $zeit = strtotime($msgtime); echo "<strong>Nani</strong> "; echo date("d.m.H:i", $zeit); echo "Uhr:";  ?>"
						+"</p>"
						+"<p><?php echo "$msg ";?>"
						+"</p>"
						+"<p>Eine Kommentarbox ist auch vorstellbar."
						+"</p>"
						//+"<div class='button'>Weiterlesen</div>"
					+"</div>",


        'images/stats.png' :    "<h1>Feuchtigkeit: <?php echo "$Feuchte %"; ?></h1>"
                                                 
						
                        <?php 
                        // Zu Testzwecken kann hier die ausgelesene Feuchtigkeit manipuliert werden.
                        //$Feuchte = 23;
                        echo "+"; echo '"<p>'; 
                        if ($Feuchte >= 75)
                        {
                            echo "Meine Erde ist, ehrlich gesagt, gerade zu feucht.";
                            echo '</p>"'; 
                            echo "+"; echo '"<p>Beim n&auml;chsten Mal brauche ich nicht ganz so viel Wasser."';
                            echo "+"; echo '"</p>"';
                        }
                        else if ($Feuchte >=40) 
                        {
                            echo "Meine Erde ist ausreichend feucht.";
                            echo '</p>"';
                            echo "+"; echo '"<p>Danke f&uuml;r die gute Pflege!"';
                            echo "+"; echo '"</p>"';                            
                        }
                        else if ($Feuchte >= 25)
                        {
                            echo "Meine Erde ist schon ziemlich trocken.";
                            echo '</p>"';
                            echo "+"; echo '"<p>F&uuml;r einen Schluck Wasser w&auml;re ich dankbar."';
                            echo "+"; echo '"</p>"';                            
                        }
                        else 
                        {
                            echo "Meine Erde ist sehr trocken!";
                            echo '</p>"';
                            echo "+"; echo '"<p>Ich f&uuml;hle mich schrecklich vernachl&auml;ssigt und gie&szlig;e mich demn&auml;chst selbst."';
                            echo "+"; echo '"</p>"';                            
                        }
                        echo "+"; echo '"<p>Letzter Messwert vom: '; $zeit = strtotime($letztes_Lebenszeichen); echo date("d.m.Y H:i:s", $zeit); echo '"'; 
                        echo "+"; echo '"</p>"';
                        ?>
						+"<p>Zusätzlich könnte man hier noch ein kleines Diagramm mit Feuchtigkeitswerten der Vergangenheit einblenden."
						+"</p>"                        
						//+"<div class='button'>Zeige &Uuml;berflutungen</div>"
					+"</div>",

	
		        <?php
		        
                // Wenn eine Überflutung (das Wasser lief bis in den Untertopf)
                // vorliegt, dann soll dieses Sybol angezeigt werden, sonst nicht.

                // Zu Testzwecken kann der Topfwert (Also der Wasserstand im Untertopf) manipuliert werden
                //echo "&Uuml;berlaufwert: $ueberlaufwert";
                //$Topfwert = 60;
                if ($Topfwert < $ueberlaufwert)
                {
                    // Sybol nicht anzeigen
                    // echo "Wasser im &Uuml;bertopf: NEIN";
                }
                else {
                    // Symbol anzeigen
		echo "'images/warning.png' :    "; echo '"<h1>&Uuml;berflutung!</h1>"';
						echo "+"; echo '"<p>Die Banane hat jetzt sehr nasse F&uuml;&szlig;e."';
						echo "+"; echo '"</p>"';
						echo "+"; echo '"<p>Die &Uuml;berflutung fand am ';
						$zeit = strtotime($ueberlaufzeit); echo date("d.m.Y H:i:s", $zeit);
                        echo ' statt."';
                        echo "+"; echo'"</p>"';
                        echo "+"; echo '"<p>Was k&ouml;nnten wir mit dieser Information machen? "';
                        echo "+"; echo'"</p>"';
						//+"<div class='button'>Weiterlesen</div>"
					echo "+";echo '"</div>"'; echo ",";
                
                }
                ?>


		        <?php

                // Wenn eine Wässerung möglich ist, dann soll das Symbol angezeigt werden, sonst nicht

                // Zu Testzwecken kann der Topfwert (Also der Wasserstand im Untertopf) manipuliert werden
                //echo "&Uuml;berlaufwert: $ueberlaufwert";
                //$giessenerlaubt = true;
                if (!$giessenerlaubt)
                {
                    // Sybol nicht anzeigen
                    //echo "W&auml;sserung erlaubt?: NEIN";
                }
                else {
                    // Symbol anzeigen
		echo "'images/rainy.png' :    "; echo '"<h1>Lass es regnen!</h1>"';
						echo "+"; echo '"<p>Pflanzen brauchen Zuneigung und Wasser."';
						echo "+"; echo '"</p>"';
						echo "+"; echo '"<p>Lass das kühle Nass um die Wurzeln der Banane flie&szlig;en."';
                        echo "+"; echo'"</p>"';
                        echo "+"; echo '"<form name=';
                        echo "'wasser' method='post' action=''";
                        echo '>"';
                        echo "+"; echo '"<input type=';
                        echo "'hidden' name='waessern' value='Wasser los!' class='button'";
                        echo '/>"';
                        echo "+"; echo '"</form>"';
                        echo "+"; echo '"<a class=';
                        echo "'button' href ='#' onClick='document.wasser.submit()'";
                        echo '>Wasser marsch!</a>"';
						//+"<div class='button'>Weiterlesen</div>"
					echo "+";echo '"</div>"'; echo ",";

                }
                ?>

		'images/thermometer.png' :    "<h1>Temperatur: <?php echo "$temperatur °C"; ?></h1>"
						+"<p>So warm ist es der Bananenpflanze.</p>"
						+"<p>Was wir mit dieser Information machen, k&ouml;nnen wir noch entscheiden.</p>"
						+"<p>Man k&ouml;nnte z.B. in einem Diagramm die Temperatur der letzten Tage darstellen.</p>"
						//+"<div class='button'>Zeige Messewerte</div>"
					+"</div>",

	});

});

</script>

    
</head>

<body id="bg">

<!-- Block Slider Einbindung -->
<div id="block-slide">



</div>

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
<?php if ($test){
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