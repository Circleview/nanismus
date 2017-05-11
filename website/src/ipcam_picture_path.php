<?php
    
    // Schleife um Array "$alledateien" aus scandir Funktion auszugeben
    // Einzeldateien werden dabei in der Variabel $datei abgelegt
    function LastIpCamPicturePath(){
        
        // http://sevenx.de/blog/tutorial-einfach-mit-php-ordner-auslesen-und-dateien-und-bilder-anzeigen/
        
        // Folder that stores the IP Cam pictures
        $ordner = "images/ipcampictures"; //auch komplette Pfade möglich ($ordner = "download/files";)
        
        // Ordner auslesen und Array in Variable speichern
        $alledateien = scandir($ordner, 1); // Sortierung A-Z
        // Sortierung Z-A mit scandir($ordner, 1)
        
        
        $anzahldateien = 0;
        $anzahldateienmax = 0;
        
        foreach ($alledateien as $datei)
        {
            // echo $datei."<br />";
            
            // Zusammentragen der Dateiinfo
            $dateiinfo = pathinfo($ordner."/".$datei);
            
            //Folgende Variablen stehen nach pathinfo zur Verfügung
            // $dateiinfo['filename'] =Dateiname ohne Dateiendung  *erst mit PHP 5.2
            // $dateiinfo['dirname'] = Verzeichnisname
            // $dateiinfo['extension'] = Dateityp -/endung
            // $dateiinfo['basename'] = voller Dateiname mit Dateiendung
            
            // Größe ermitteln zur Ausgabe
            //$size = ceil(filesize($ordner."/".$datei)/1024);
            //1024 = kb | 1048576 = MB | 1073741824 = GB
            
            
            if($anzahldateien == $anzahldateienmax && $dateiinfo['extension'] == "jpg"){
                
                // Da ich den Dateinamen des aktuellen Fotos noch einmal brauche speiche ich den mal weg
                $fotopfad = $ordner."/".$dateiinfo['basename'];
                
                // in the first loop build a link to the last picture
                // echo '+"<p>Jeden Tag ein neues <b><a href='. $fotopfad ." target='_blank'>".'Foto.</b></a></p>"';
                // echo '+"<div id='; echo "'fotorahmen'>"; echo '"';
                // echo '+"<div class='; echo "'galleria'>"; echo '"';
            }
            
            // scandir liest alle Dateien im Ordner aus, zusätzlich noch "." , ".." als Ordner
            
            // Nur echte Dateien anzeigen lassen und keine "Punkt" Ordner
            
            // _notes ist eine Ergänzung für Dreamweaver Nutzer, denn DW legt zur besseren Synchronisation diese Datei in den Orndern ab
            /*
             if ($dateiinfo['extension'] == "jpg"){
             $anzahldateien++;
             }
             */
            
            // $path = $ordner."/".$dateiinfo['basename'];
            
            return $fotopfad;
        }
    }
    ?>

