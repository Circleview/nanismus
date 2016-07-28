<!-- http://d4nza.de/blog/tutorials/jquery-ui-ajax-animierter-ladebalken -->
<html>
<head>
    <title>W&auml;sserung l&auml;uft</title>
    <!--link type="text/css" href="./css/ui-lightness/jquery-ui-1.8.13.custom.css" rel="stylesheet" /-->
    <!--link rel="stylesheet" href="../../css/style_umbau.css" /-->
    <!-- Favicon -->
    <link href="../../images/favicon_gruen" type="image/x-icon" rel="shortcut icon" />

    <!-- Wie wird mit mobilen Endgeräten umgegangen? -->
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no" />

    <meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
     
    <script type="text/javascript" src="./js/jquery-1.5.1.min.js"></script>
    <script type="text/javascript" src="./js/jquery-ui-1.8.13.custom.min.js"></script>
    <script type="text/javascript">
        $(function(){
            /* Progessbar definieren */
            $("#progressbar").progressbar({
                value: 0
            });
             
            // Funktion, die das Laden anstupst
            function load() {
                $mengeval = document.getElementById('txtmenge').value;
                $.ajax({
                    // Welche URL soll aufgerufen werden?
                    url: './waterbar.php?status='+$( "#progressbar" ).progressbar( "value" )+'&menge='+$mengeval,
                    // Wird ausgeführt, wenn die Datei erfolgreich requestet wurde
                    success: function(data) {
                        /**
                        * PHP liefert ein JSON Objekt zurück, welches wir im
                        * JavaScript Code ausführen müssen, um ein Objekt zu erhalten.
                        * Danach können wir mittels ajax.message und ajax.status auf unser
                        * zuvor erstelltes PHP Array zu greifen. Wenn ein neuer Index im PHP Array
                        * hinzugefügt wird, können wir mittels ajax.neuerIndex auch im JS darauf
                        * zu greifen.
                        **/
                         
                        ajax = eval('(' + data + ')');
                         
                        // Überprüfen, ob ein JS Objekt da ist.
                        if(ajax!=false) {
                            // Updaten unserer Progressbar auf den aktuellen Stand
                            $("#progressbar").progressbar({
                                value: ajax.status
                            });
                            
                            hoehe = ajax.status * ajax.pixel;
                            
                            // Die Wassersäule soll in der Höhe angepasst werden
                            $("#wasserstatus").height(hoehe);
                             
                            // Die von PHP generierte Meldung dem Benutzer darstellen
                            $("#message").html( ajax.message );
                             
                            // Solange wir nicht 100% haben müssen wir die Datei nochmal aufrufen...
                            if(ajax.status!=100) {
                                load();
                            }
                        }
                    }
                });
            }
             
            load(); // Das erste Starten unserer Funktion
        });
    </script>
	
	<style>
		/* Größe für die Progressbar */
		#progressbar {
			width:300px;
			height: 20px;				
		}
        .wasser {
        	/*position: absolute;
        	bottom: 110px;
        	right: 57px;
        	z-index: 3;*/
        	background-color:#39F; width: 82px;
        	border-style: solid; border-width: 0px; border-color: #000;
        	display: block;
        	/*margin-left:auto;
        	margin-right:auto; */
        
        
        	background: rgb(135,224,253); /* Old browsers */
        	background: -moz-linear-gradient(top,  rgba(135,224,253,1) 0%, rgba(83,178,237,1) 42%, rgba(0,119,239,1) 100%); /* FF3.6+ */
        	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(135,224,253,1)), color-stop(42%,rgba(83,178,237,1)), color-stop(100%,rgba(0,119,239,1))); /* Chrome,Safari4+ */
        	background: -webkit-linear-gradient(top,  rgba(135,224,253,1) 0%,rgba(83,178,237,1) 42%,rgba(0,119,239,1) 100%); /* Chrome10+,Safari5.1+ */
        	background: -o-linear-gradient(top,  rgba(135,224,253,1) 0%,rgba(83,178,237,1) 42%,rgba(0,119,239,1) 100%); /* Opera 11.10+ */
        	background: -ms-linear-gradient(top,  rgba(135,224,253,1) 0%,rgba(83,178,237,1) 42%,rgba(0,119,239,1) 100%); /* IE10+ */
        	background: linear-gradient(to bottom,  rgba(135,224,253,1) 0%,rgba(83,178,237,1) 42%,rgba(0,119,239,1) 100%); /* W3C */
        	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#87e0fd', endColorstr='#0077ef',GradientType=0 ); /* IE6-9 */
        }
        
        #txtmenge /*Input Feld, das die Gießmenge speichert*/
        {
           visibility: hidden;
        }

        #message
        {
            height: 75px; width: auto;
            position: relative; top: 0px;
            margin-left: auto; margin-right: auto;
            border-style: dotted;
            border-width: 0px;
            border-color: #000000;
        }
        /*
         * padding:25px 50px 75px 100px;
         * 
            top padding is 25px
            right padding is 50px
            bottom padding is 75px
            left padding is 100px
        */
                
        #message h1 
        {
        	font-size: 1.2em;
        	color: #4E585F;
        	padding: 5px 0px 0px 0px;
        	margin-top: 5px;
        	font-family: 'adelle-sans', sans-serif;
        }
        
        #message a 
        {
        	font-family: 'adelle-sans', sans-serif;
        	color: #ACB4BB;
        	padding: 0 20px;
        	font-size: 1.1em;
        	text-decoration: none;
        }
        
        #message p 
        {
        	font-family: 'adelle-sans', sans-serif;
        	color: #ACB4BB;
        	padding: 0 0px;
        	font-size: 1.0em;
        }                      
                    			
	</style>
		
</head>
<body style="background: #F4FAFF;">

<?php 
/* Eintragen in die Datenbank, dass eine Wässerung stattgefunden hat. 
 * Das verhindert unmittelbar nach dem neu Laden der Website, dass erneut
 * gegossen werden kann. 
 * Eigentlich nimmt das Arduino selbst diesen Eintrag auch vor, doch dauert
 * das unter Umständen zu lange, um eine doppelte Wässerung zu verhindern.
 */

// Festlegen welche Datenbank verwendet werden soll
$test = false;  // bei true werden die Daten in die Testdatenbank geschrieben
$tabelle = "plant_log";
$name = "Banane";
$type = "Giessung";
$value = "1";

//Datenbank-Verbindung herstellen
//--------------------------------
include("../db.php");

        // http://stackoverflow.com/questions/1995562/now-function-in-php
        $timestamp = date("Y-m-d H:i:s");
        $sql = "
          INSERT INTO $tabelle
          (
          sensorname , logtype , value , timestamp
          )
          VALUES
          (
          '$name', '$type', $value, '$timestamp'
          )
        ";
        //http://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol
        $db_erg = mysqli_query($db_link, $sql)
            or die("<p>Anfrage fehlgeschlagen</p>" . mysqli_error($db_link));
            //or die("0 \r\n" . mysqli_error($db_link));

 ?>
    <div id="wasserfortschritt" 
        style="position: relative; top: -5px; left: -8px; height: 185px; width: 97.5%; text-align: center;
        margin-left: -8px; margin-right: auto; border-style: solid; padding-left: 8px;
        border-width: 0px; 
        border-color: #000000;">
        <!-- Div Platzhalter für eigene von PHP generierte Nachrichten -->
        <div id="message"> 
            <!--  Zeige auf der Website an, dass eine Verbindung mit dem Arduino aufgebaut wird
            Der Besucher soll merken, dass sich etwas auf der Seite tut, selbst wenn
            das Arduino etwas verzögert antwortet.  -->
            <h1>Verbindung herstellen... </h1>
            <p>Das kann ein paar Sekunden dauern.</p>                     
        </div>
        <div style="position: relative; bottom: 0px; height: 105px; width: auto; 
            margin-left: auto; margin-right: auto;
            border-style: dotted; 
            border-width: 0px; 
            border-color: #000000;">
            <div id="wasserstatus" class="wasser"
                style="height: 0px; width: 100%; position: absolute; bottom: 0px;
                margin-left: auto; margin-right: auto;
                padding-left: auto; padding-right: auto;">
            </div>   
        </div>     
    </div> 
		<!--Die Progressbar-->
		<div id="progressbar" style="visibility: hidden"></div>
    <form>
        <input id="txtmenge" name="menge" value="<?php echo $_POST['menge']; ?>"/>
    </form>		
</body>
</html>	