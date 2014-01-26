// Sends different values to a http webserver on which a PHP script waits for the data
// to store them in a MySQL database

// Based on Watterott sample
/*
  Web Client
 
 This sketch connects to a website using a RedFly-Shield. 
 */

// Inspired by 
// http://jleopold.de/wp-content/uploads/2011/03/ArduinoDatenLogger.txt


// Needs 
// #include <RedFly.h>
// #include <RedFlyClient.h>
// which are included already

// Declaration for the HTTP Webserver - Sending ********************************

//#define HOSTNAME "nanismus.no-ip.org"  //host
//char url[] = "/nanismus_test/valueget.php";  // path to the PHP-file which writes the data in the MySQL database
//char key[] = "123";	                    // the transmission of the data is protected by 
// a key on which the PHP-file waits. If the key is not transmitted nothing will happen to that request
//initialize the client library with the ip and port of the server 
//that you want to connect to (port 80 is default for HTTP)

//char data[1024];  //receive buffer
unsigned int len=0; //receive buffer length

RedFlyClient phpclient(domainserver, 80);

void HttpSend(int sensor_int, int type_int, int value)
{
  // in the time, when the pumping started, no messages should be transmitted. 
  // That's because you never know if the transmission works perfectly
  // If it doesn't work right, in the worst case a overwatering could take place
  
  if (beginpumping[sensor_int] == false)
  {

    digitalWrite(CosmComLED, HIGH);    // Show the work in Progress
    
    if (debug) {
      spo2(PSTR("HttpSend started"), 1);
    }
    
    log(now(), 1, PSTR("HttpSend"), PSTR("gestartet"));     // Log the event on the SD Card
  
    /* Needs to define
     byte ip[]        = { 192, 168, 178, 30 };   // ip from WiFly shield (client/Webserver)
     byte netmask[]   = { 255, 255, 255,  0 };   // local netmask
     byte gateway[]   = { 192, 168, 178,  1 };   // ip from local gateway/router
     byte dnsserver[] = { 192, 168, 178,  1 };   // ip from local dns server
     byte server[]    = { 0, 0, 0, 0 }; //{  85, 13,145,242 }; //ip from www.watterott.net (server)
     which are definded as constants already
     */
  
    WiFiConnect(); // Check if the WiFi Shield is connected. If not, try to connect
    
    // If we've tried to connect 20 times in a row without any success we should stop here
    // With a successful internetconnection the internetconnectionerror should return 0
    // Without success it returns an error message different than 0
    if (internetconnectionerror != 0)
    {
        log(now(), 1, PSTR("HttpSend"), PSTR("ohne Verbindung abgebrochen"));     // Log the event on the SD Card      
        return; 
    }
  
    // freememorycheck();
   
    // I dont want to access the server via DNS, thats why I manipulate the returnvalue to zero
    
    //if(RedFly.getip(HOSTNAME, domainserver) == 0) //get ip
    {
      
      log(now(), 1, PSTR("HttpSend"), PSTR("IP erhalten - OK"));     // Log the event on the SD Card
      
      if(phpclient.connect(domainserver, 80))
      {
        //make a HTTP request
        spo2(PSTR("Verbunden...sende Daten..."), 1);
        log(now(), 1, PSTR("HttpSend"), PSTR("Verbunden mit PHP Server"));     // Log the event on the SD Card
  
        //http://www.watterott.net/forum/topic/282
        //make a HTTP request
         
         const char * sensor_string;
         switch (sensor_int)
         {                                                      // Different naming dependig on Sensor ID
         case 0:
           sensor_string = "Banane";         
           break;
         case 1:
           sensor_string = "Banane_Topf";        
           break;
         case 97: 
           sensor_string = "Wassertank";
           break;
         case 98:
           sensor_string = "RAM";
           log(now(), 1, PSTR("HttpSend"), PSTR("RAM nicht senden - Abbruch"));     // Log the event on the SD Card
           phpclient.flush();
           phpclient.stop();              
           return;                                             // You don't need to send the RAM to the webserver. That's why we can exit here
           break;
         case 99:
           sensor_string = "Temperatur";         
           break;
         } // Switch 
              
         const char * type_string;
         switch (type_int)
         {                                                      // Different type_string dependig on type ID
         case 1:
           type_string = "Messwert";     
           break;
         case 2:
           type_string = "Prozentfeuchte";    
           break;
         case 3:
           type_string = "Feuchtestatus";    
           break;   
         case 4:
           type_string = "Nachricht";     
           break;
         case 5: 
           type_string = "Wasser_erforderlich";   // Wird wasser benötigt?
           break; 
         case 6: 
           type_string = "Giessmenge";   // Wird wasser benötigt?
           break; 
         case 7:
           type_string = "Giessung"; // Fand eine Wässerung statt? 
           break;      
         } // Switch 
  
        if (debug)
        {
          RedFly.disable(); 
          Serial.print("senor_int: ");
          Serial.println(sensor_int);
          Serial.print("type_int: ");
          Serial.println(type_int);
          Serial.print("sensor_string: "); 
          Serial.println(sensor_string);
          Serial.print("type_string: "); 
          Serial.println(type_string); 
          Serial.print("value: "); 
          Serial.println(value);
          RedFly.enable();
        }
        
        //String GetRequest;
        // http://miscsolutions.wordpress.com/2011/10/16/five-things-i-never-use-in-arduino-projects/
        
        char * GetRequest;
        const char * get1;
        
        if (!debug)
        {
          get1 = "GET /valueget.php"; // Zugang zur Live-Datenbank
        }
        else
        {
          get1 = "GET /nanismus_test/valueget.php"; // Zugang zum Testsystem
        }
        const char * get2 = "?name=";
        const char * get3 = "&type=";
        const char * get4 = "&value=";
        const char * get5 = "&key=c3781633f1fb1ddca77c9038d4994345";
        const char * get6 = " HTTP/1.1\r\nHost: ";
        const char * get7 = "\r\n\r\n";
        
        char * value_char; 
        value_char = (char*) calloc(5, sizeof(char));
        itoa(value, value_char, 10);
        
        // allocate memory for the message
        GetRequest = (char*) calloc(strlen(get1) + strlen(get2) + strlen(sensor_string)  + strlen(get3) + strlen(type_string) + strlen(get4) 
        + strlen(value_char) + strlen(get5) + strlen(get6) + strlen(HOSTNAME) + strlen(get7) + 1, sizeof(char));
        
        // assemble the GetRequest
        strcat(GetRequest, get1);
        strcat(GetRequest, get2);
        strcat(GetRequest, sensor_string);
        strcat(GetRequest, get3);
        strcat(GetRequest, type_string);
        strcat(GetRequest, get4);
        strcat(GetRequest, value_char);
        strcat(GetRequest, get5);
        strcat(GetRequest, get6);
        strcat(GetRequest, HOSTNAME);
        strcat(GetRequest, get7);                                              
        
        phpclient.print(GetRequest);      
        //phpclient.print_P(PSTR("GET /nanismus_test/valueget.php?name=Banane&type=Status&value=value&key=123 HTTP/1.1\r\nHost: "HOSTNAME"\r\n\r\n"));
  
        // http://nanismus.no-ip.org/nanismus_test/valueget.php?name=Banane&type=status&value=6&key=123
  
        //GET /exampleApp/Create?deviceid=2&temperature=23 HTTP/1.1\r\n
        //Host: www.example.com\r\n
        //\r\n
  
        free(GetRequest);                       // free the allocated string memory
        free(value_char);  
        spo2(PSTR("fertig!"), 1);
  
        log(now(), 1, PSTR("HttpSend"), PSTR("Daten uergeben"));     // Log the event on the SD Card
        phpclient.flush();
        phpclient.stop();         
      }
      else
      {
        spo2(PSTR("CLIENT ERR"), 1);
        log(now(), 1, PSTR("HttpSend"), PSTR("PHP Server - nicht verbunden "));     // Log the event on the SD Card
      }
    }
    
    int c;
  
    //if there are incoming bytes available 
    //from the server then read them 
    if(phpclient.available())
    {
      do
      {
        c = phpclient.read();
        if((c != -1) && (len < (sizeof(buffer)-1)))
        {
          buffer[len++] = c;
        }
      }while(c != -1);
    }
  
    //if the server's disconnected, stop the client and print the received data
    if(len && !phpclient.connected())
    {
      phpclient.stop();
      //RedFly.disconnect();
  
      spo(buffer, 0);
      buffer[len] = 0;
      spo(buffer, 0);
      
      len = 0;
      free(buffer);
    }
  
    phpclient.flush();
    phpclient.stop(); 
  
    digitalWrite(CosmComLED, LOW);    // Show that the work is done
    log(now(), 1, PSTR("HttpSend"), PSTR("beendet"));     // Log the event on the SD Card
    
  } // If beginpumping == false  
  
}













