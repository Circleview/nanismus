<?php

// Daten f�r die Kommunikation mit dem Arduino
$arduino_ip="nanismus.no-ip.org";
//$arduino_ip="192.168.178.30";
$arduino_port="24";
//global $delay;
$delay = 0.3; // delay in seconds
         // http://de1.php.net/manual/de/function.usleep.php
$delay = $delay * 1000000; // convert into microseconds

//$zufall = rand(1,3); // Zuf�llige l�nge der Aktion simulieren
$gesamtdauer = 10; // Wie lange in Sekunden soll der Ladebalken angezeigt werden?
$schrittdauer = 1; // Sekunden  // Dauer zwischen zwei Aktualisierungen

$maxwassermenge = 500; // Wie viel Wasser kann maximal gegossen werden?
if ($_GET['menge'] == 0)
{
    $wassermenge = $maxwassermenge;    
}
else
{
    $wassermenge = $_GET['menge'];  // Wie viel Wasser wird gew�ssert?    
}
 
$maxfortschritt = 100; // Progressbar Maximalwert = 100%
$maxhoehesaeule = 105; // Wenn so viel Wasser wie m�glich gegossen wird, 
// dann ist die Wassers�ule am h�chsten. in pixel

$hoehesaeule = $maxhoehesaeule * $wassermenge / $maxwassermenge; // Die H�he der Wassers�ule auf der Website

// um wie viele ml soll
// die Wassermengenanzeige erh�ht werden bei der Darstellung des W�sserungsfortschritts?
$mljeprozentpunkt = $wassermenge/$maxfortschritt;

// Wie viele Schritte werden gemacht, bis die Wassers�ule voll gef�llt dargestellt wird? 
$schrittweite = ($maxfortschritt/($gesamtdauer/$schrittdauer));

// Um wie viele Pixel wird die S�ule je Prozentpunkt angehoben
// wie hoch ist jeder Fortschritt-Schritt in Pixel?
$pixeljeprozent = $hoehesaeule/$maxfortschritt; 
$php_array['pixel'] = $pixeljeprozent;


If ($_GET['status'] == 0) // Beim ersten Aufrufen der Funktion 
// soll der Gie�befehl an das Arduino gesendet werden)
{
    // Sende den Gie�befehl an das Arduino Board
    // arduino_send($arduino_ip,$arduino_port,"P".chr($menge));
	if ($wassermenge == 0) {
		arduino_send($arduino_ip,$arduino_port,"P".chr($wassermenge));
	}
	else if ($wassermenge <= 255) {
		arduino_send($arduino_ip,$arduino_port,"Q".chr($wassermenge));
	}
	else {
		$menge = $wassermenge - 255;
		arduino_send($arduino_ip,$arduino_port,"R".chr($wassermenge));
	}    
    // Gie�ung anordnen
	// Warte x * 1.000.000 Mikro-Sekunden
    usleep($delay); // to avoid connection refusal  
    
    // f�r Testzwecke kann der $returnvalue hier manupuliert werden    
    // $returnvalue = 9; 
}

if ($returnvalue == 8) // Wenn die Erde jetzt doch noch feucht genug ist, dann
// wurde durch das Arduino die W�sserung abgebrochen.
// Das soll dann auf der Website angezeigt werden;
{
    $php_array['status'] = $maxfortschritt;
    $php_array['message'] = '<h1>W&auml;sserung doch nicht n&ouml;tig</h1><p>Die Erde war noch feucht.<a href="../../index.php" target="_top">zur&uuml;ck</a></p>';
}
else // Wenn die Erde trocken war, dann
// hat das Arduino begonnen zu w�ssern. Das soll auf der Website
// mit der steigenden Wassers�ule angezeigt werden
{
    sleep($schrittdauer);

    // Unser $php_array ist ein Array, welches nacher als JSON Objekt ausgeben wird
    // Enth�lt unseren Prozessfortschritt, als Prozentwert
    // $php_array['status'] = $_GET['status']+($zufall*3);
    $php_array['status'] = $_GET['status']+($schrittweite);


    // Bei 100% ist Schluss ;)
    if($php_array['status']>$maxfortschritt) {
    	$php_array['status'] = $maxfortschritt;
    }

    // Eine von Nachricht an dem Benutzer aus PHP
    if($php_array['status'] != $maxfortschritt) {
    	$php_array['message'] = '<h1>Yaaay, gleich bin ich undurstig!</h1><p>'.$php_array['status']*$mljeprozentpunkt.' ml von '.$wassermenge.' ml</p>'; //, verbleibend: '.($wassermenge-$php_array['status']*$mlje100);
    } else {
    	$php_array['message'] = '<h1>Yippie! Danke f&uuml;r das Wasser.</h1><p><a href="../../index.php" target="_top">zur&uuml;ck zur Nani</a></p>';
    }   
}
// Ausgabe des PHP Arrays als JSON Objekt
echo json_encode($php_array);
?>

<!-- PHP Um die Daten�bertragung an das Arduino zu erm�glichen, um einen Gie�befehl zu �bermitteln -->
<?php 

function arduino_send($ip,$port,$command) {

    $res = @fsockopen($ip,$port);    // @ schaltet fehlermeldungen aus
    if($res) {
        fwrite($res,$command);
        global $returnvalue;
        $returnvalue = fread($res,2);

        //echo "Returnvalue: $returnvalue <br />" ;
    }
}
?>
