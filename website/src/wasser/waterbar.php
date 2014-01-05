<?php

// Daten f¸r die Kommunikation mit dem Arduino
$arduino_ip="nanismus.no-ip.org";
//$arduino_ip="192.168.178.30";
$arduino_port="24";
//global $delay;
$delay = 0.3; // delay in seconds
         // http://de1.php.net/manual/de/function.usleep.php
$delay = $delay * 1000000; // convert into microseconds

//$zufall = rand(1,3); // Zuf‰llige l‰nge der Aktion simulieren
$gesamtdauer = 10; // Wie lange in Sekunden soll der Ladebalken angezeigt werden?
$schrittdauer = 1; // Sekunden  // Dauer zwischen zwei Aktualisierungen
$wassermenge = 400;  // Wie viel Wasser wird gew‰ssert?
$maxwassermenge = 400; // Wie viel Wasser kann maximal gegossen werden?
 
$maxfortschritt = 100; // Progressbar Maximalwert = 100%
$maxhoehesaeule = 245; // Wenn so viel Wasser wie mˆglich gegossen wird, 
// dann ist die Wassers‰ule am hˆchsten. 

$hoehesaeule = $maxhoehesaeule * $wassermenge / $maxwassermenge; // Die Hˆhe der Wassers‰ule auf der Website

// um wie viele ml soll
// die Wassermengenanzeige erhˆht werden bei der Darstellung des W‰sserungsfortschritts?
$mljeprozentpunkt = $wassermenge/$maxfortschritt;

// Wie viele Schritte werden gemacht, bis die Wassers‰ule voll gef¸llt dargestellt wird? 
$schrittweite = ($maxfortschritt/($gesamtdauer/$schrittdauer));

// Um wie viele Pixel wird die S‰ule je Prozentpunkt angehoben
// wie hoch ist jeder Fortschritt-Schritt in Pixel?
$pixeljeprozent = $hoehesaeule/$maxfortschritt; 
$php_array['pixel'] = $pixeljeprozent;


If ($_GET['status'] == 0) // Beim ersten Aufrufen der Funktion 
// soll der Gieﬂbefehl an das Arduino gesendet werden)
{
    // Sende den Gieﬂbefehl an das Arduino Board
    arduino_send($arduino_ip,$arduino_port,"P".chr($menge));
    // Gieﬂung anordnen
	// Warte x * 1.000.000 Mikro-Sekunden
    usleep($delay); // to avoid connection refusal  
    
    // f¸r Testzwecke kann der $returnvalue hier manupuliert werden
	global $returnvalue;    
    //$returnvalue = 9; 
}

if ($returnvalue == 8) // Wenn die Erde jetzt doch noch feucht genug ist, dann
// wurde durch das Arduino die W‰sserung abgebrochen.
// Das soll dann auf der Website angezeigt werden;
{
    $php_array['status'] = $maxfortschritt;
    $php_array['message'] = '<h1>W&auml;sserung doch nicht n&ouml;tig </h1><p><a href="../../index.php">zur&uuml;ck zur Nani</a></p>';
}
else // Wenn die Erde trocken war, dann
// hat das Arduino begonnen zu w‰ssern. Das soll auf der Website
// mit der steigenden Wassers‰ule angezeigt werden
{
    sleep($schrittdauer);

    // Unser $php_array ist ein Array, welches nacher als JSON Objekt ausgeben wird
    // Enth‰lt unseren Prozessfortschritt, als Prozentwert
    // $php_array['status'] = $_GET['status']+($zufall*3);
    $php_array['status'] = $_GET['status']+($schrittweite);


    // Bei 100% ist Schluss ;)
    if($php_array['status']>$maxfortschritt) {
    	$php_array['status'] = $maxfortschritt;
    }

    // Eine von Nachricht an dem Benutzer aus PHP
    if($php_array['status'] != $maxfortschritt) {
    	$php_array['message'] = '<h1>bzzzzzz...</h1><p>'.$php_array['status']*$mljeprozentpunkt.' ml von '.$wassermenge.' ml</p>'; //, verbleibend: '.($wassermenge-$php_array['status']*$mlje100);
    } else {
    	$php_array['message'] = '<h1>W&auml;sserung abgeschlossen</h1><p><a href="../../index.php">zur&uuml;ck zur Nani</a></p>';
    }   
}
// Ausgabe des PHP Arrays als JSON Objekt
echo json_encode($php_array);
?>

<!-- PHP Um die Daten¸bertragung an das Arduino zu ermˆglichen, um einen Gieﬂbefehl zu ¸bermitteln -->
<?php 

function arduino_send($ip,$port,$command) {

    $res = @fsockopen($ip,$port);    // @ schaltet fehlermeldungen aus
    if($res) {
        fwrite($res,$command);
        $returnvalue = fread($res,2);

        //echo "Returnvalue: $returnvalue <br />" ;
    }
}
?>

<?php

	       /*

            if(isset($_POST['waessern']))
            {

                // Wenn eine manuelle Bew‰sserung angeordnet wurde, dann soll diese
                // in die Log Tabelle geschrieben werden

                // Diese Funktion wird zun‰chst nicht in Betrieb genommen, da das
                // Arduino die Gieﬂmenge in die Datenbank schreibt. Die Gieﬂmenge
                // Wird zusammen mit dem Timestamp in der Tabelle erfasst, sodass ein
                // zus‰tzlicher Logtype keinen Vorteil zu bringen scheint.

                //Datenbank-Verbindung herstellen
                //--------------------------------
                /*
                 include("db.php");
                 //include("db_test.php");

                        $table = "plant_log" ;
                        //$table = "plant_log_test" ;
                        // !! WICHTIG !!
                        // der logtype ist f¸r ein Gieﬂevent noch zu definieren, f¸r das
                        // Gieﬂen gibt es bisher keinen logtype

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

                // Sende den Gieﬂbefehl an das Arduino Board
                //arduino_send($arduino_ip,$arduino_port,"P".chr($menge));
                // Gieﬂung anordnen
    			// Warte x * 1.000.000 Mikro-Sekunden
                //usleep($delay); // to avoid connection refusal

            /*} /*else
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

<?php     

//header('Content-Type: text/html; charset=utf-8');

/* Problem mit utf-8 Konvertierung und Zeichenanzeige im
 * Zusammenhang mit einer MYSQL Datenbank, konnte durch den
 * HTML Tag weiter unten nicht abgefangen werden
 * http://www.winfuture-forum.de/index.php?showtopic=193063
 * brachte die Lˆsung mit dem PHP Header Tag.
 */

//session_start();

// Funktionen

/*$arduino_ip="nanismus.no-ip.org";
//$arduino_ip="192.168.178.30";
$arduino_port="24";

$debug = 0;             // throws additional information if true
$test = 1;               // grab different data from different databases due to the testcontext

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

/*function arduino_send($ip,$port,$command) {

    $res = @fsockopen($ip,$port);    // @ schaltet fehlermeldungen aus
    if($res) {
        fwrite($res,$command);

		global $returnvalue;
        $returnvalue = fread($res,2);

        //echo "Returnvalue: $returnvalue <br />" ;
    }
}

/*
 * Arduino States f¸r Feuchtigkeit und Wohlbefinden der Pflanze
 * // state
    #define WATER_OVERFLOW 5               // There is water on the ground of the pot
    #define NO_WATER_OVERFLOW 4            // There is no Water on the ground
    #define URGENT_SENT 3                  // Soil is critically dry
    #define SOON_DRY_SENT 2                // Soil is not moist anymore
    #define MOISTURE_OK 1                  // Soil is moist
    #define TOOWET 0                       // Soil is too wet
*/

?>
