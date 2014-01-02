void WiFlyConnect()
{

  log(now(), 0, PSTR("Funktion WiFlyConnect"), PSTR("gestartet"));
  
  WiFiLEDStatus();            // indicate if the WiFi connection is established  
  
  uint8_t ret;

  //init the WiFi module on the shield
  // ret = RedFly.init(br, pwr) //br=9600|19200|38400|57600|115200|200000|230400, pwr=LOW_POWER|MED_POWER|HIGH_POWER
  ret = RedFly.init(HIGH_POWER); //9600 baud, pwr=LOW_POWER|MED_POWER|HIGH_POWER
  // ret = RedFly.init() //9600 baud, HIGH_POWER
  //ret = RedFly.init();
  if(ret)
  {  
    spo2(PSTR("INIT ERR"), 1); //there are problems with the communication between the Arduino and the RedFly
    WiFlyConnection = false;          // Save the result for later
    log(now(), 1, PSTR("WiFi Verbindung"), PSTR("Initialisierung fehlgeschlagen"));
  }
  else
  {
                                      //scan for wireless networks (must be run before join command)
    RedFly.scan();
                                      //join network
    ret = RedFly.join(Network, NetworkPW, INFRASTRUCTURE);
    if(ret)
    {
      spo2(PSTR("JOIN ERR"), 1);     
      WiFlyConnection = false;
      log(now(), 1, PSTR("WiFi Verbindung"), PSTR("Netzwerkeinwahl fehlgeschlagen"));    
    }
    else
    {
                                      // set ip config
      ret = RedFly.begin(ip, dnsserver, gateway, netmask);
      //ret = RedFly.begin(ip); 
      if(ret)
      {
        spo2(PSTR("BEGIN ERR"), 1);
        RedFly.disconnect();
        WiFlyConnection = false; 
        log(now(), 1, PSTR("WiFi Verbindung"), PSTR("Netzwerkverbindung fehlgeschlagen"));
      }
      else
      {
        RedFly.getlocalip(ip);       // receive shield IP in case of DHCP/Auto-IP
        server.begin();
        spo2(PSTR("WiFi Shield connected"), 1);
        log(now(), 1, PSTR("WiFi Verbindung"), PSTR("Verbindung hergestellt"));
        
        // Now that we are connected to the Router, we have to try to reach a webserver over the internet. 
        internetconnectionerror = RedFly.getip(HOSTNAME, domainserver);  // Check if we can reach the server to which we want     
        if (internetconnectionerror != 0)
        {
          WiFlyConnection = false;
          log(now(), 1, PSTR("Nanismus Server"), PSTR("nicht erreicht")); 
        }
        else 
        {
          WiFlyConnection = true;
          log(now(), 1, PSTR("Nanismus Server"), PSTR("erricht - OK"));
          getNPTtime();                // Get current time
          //lastwifichecktime = now();   // store that the wificheck took place. This indicator is needed to check the wifi status from time to time
        } // else
      } // else
    }// else
  }// else
  WiFiLEDStatus();                    // Switch the WiFi indicating LED
}//void




