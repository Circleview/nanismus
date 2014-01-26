void WiFiConnect()
{

  log(now(), 0, PSTR("Funktion WiFiConnect"), PSTR("gestartet"));     // Log the event on the SD Card
  // Dokumentation of the RedFly Shield and its functions
  // https://github.com/watterott/RedFly-Shield/blob/master/src/docu.md
 
  // due to different reasons it can happen, that the WiFi ist disconnected by the router.
  // With regulary checks it should be avoided, that the RedFly Shield stays disconnected without noticing it  
  
  // if (now() > lastwifichecktime + wifichecktime) // This if statement is not needed anymore, because the HTTP Send function 
  // triggers this internet connection check just in time when it is needed
  //{
    // I want to adress the server without DNS, thats why I manipulate the returnvalue to zero
    internetconnectionerror = 0; //RedFly.getip(HOSTNAME, domainserver);  // Check if we can reach the server to which we want
    // to upload the data
    // It returns 0 or an error message. 0 Means that the connection is established proberly
    
    if (internetconnectionerror != 0)
    {
      WiFlyConnection = false;  // If we cannot connect to the webserver, we mark the WiFi connection to be reestablished 
      // we have to quess that the reason for the error is a problem with the WiFi connection
      log(now(), 1, PSTR("Nanismus Server"), PSTR("nicht erreicht"));     // Log the event on the SD Card
    }
  //}

  // Nach 20 vergeblichen Versuchen soll der Code ersteinmal normal ausgeführt werden
  // Siehe https://github.com/Circleview/nanismus/issues/42

  // Wie breche ich aus einer do while Funktion aus? http://torrentula.to.funpic.de/dokumentation/2011/11/page/2/
  int count = 0;                       // conter to quit the void after 20 unsuccessful connections
  
  if(!WiFlyConnection)
  {
    do //(!WiFlyConnection)
    {
      blinkLED(WiFiStatusPin, 5, 100);  // Show that there is a try to connect       
      log(now(), 1, PSTR("Nanismus Server"), PSTR("Verbindungsversuch"));   // Log the event on the SD Card // void log(long timestamp, int logtype, char * detail, char * result)
      
      WiFlyConnect();       // Connect the WiFi Shield 
      count++; 
      
      int connectiontrymax = 5; 
      // check if we should quit the void 
      if (count >= connectiontrymax)
      {
        
        log(now(), 1, PSTR("20 Verbindungsversuche"), PSTR("erfolglos"));   // Log the event on the SD Card // void log(long timestamp, int logtype, char * detail, char * result)
                                                                            // the following logtypes are defined
                                                                            // logtype  | definition  
                                                                            // 0        | Event 
                                                                            // 1        | gateway or if statement
                                                                            // 3        | Messwert        
        return;                                                             // quit the void, it seems to be useless to try it endless
      }
    } 
    while (!WiFlyConnection);
  }// if
  else
  {
    log(now(), 1, PSTR("Nanismus Server"), PSTR("erreicht - OK"));     // Log the event on the SD Card
  }
}

void spo2(char *s, boolean line) // Abrv. "spo" ... Serialprintout with usage of flash memory
{
  boolean on = debug;       // to avoid serial printout if not nessessary deactivate it on demand

  if (on)
  {
    char * buf;
    buf=(char *)malloc(strlen_P(s)+1);
    strcpy_P(buf,s);
    //Serial.println(buffer);
  
    RedFly.disable();                          // To avoid trouble with the communication, disable the WiFly Shield bevore printing out serially
    if (line) 
    {
      Serial.println(buf); 
      //Serial.println();
    }
    // println or print?
    else 
    {
      Serial.print(buf);
    }
    RedFly.enable();                           // Switch the WiFlyShield on again
    free(buf);
  }
}

void spo(char *s, boolean line) // Abrv. "spo" ... Serialprintout
{
  boolean on = debug;       // to avoid serial printout if not nessessary deactivate it on demand

  if (on)
  {

    RedFly.disable();                          // To avoid trouble with the communication, disable the WiFly Shield bevore printing out serially
    if (line) 
    {
      Serial.println(s); 
      //Serial.println();
    }
    // println or print?
    else 
    {
      Serial.print(s);
    }
    RedFly.enable();                           // Switch the WiFlyShield on again
  }
}

// LED Indication if the WiFi Connection is established
void WiFiLEDStatus() 
{  
  if(debug)
  {
    //spo("WiFiLEDStatus() started", 1);
  }
  if (WiFlyConnection)
  {
    digitalWrite(WiFiStatusPin, HIGH);         // Indicate that the WiFi Connection is established
  }
  else
  {
    digitalWrite(WiFiStatusPin, LOW);          // Indicate that the WiFi Connection is not established
  }
}

void NPTLEDStatus()
{
  if(isNPT)
  {
    // switch the LED off
    digitalWrite(NPTLED, 0);
  }
  else
  {
    // switch the LED on, because we have no valid NPT time
    digitalWrite(NPTLED, 1);
  }
}

// Measure the moisture of the soil 
void moistmeasurement()
{

  // Measures the wetness of the soil and saves it so that following functions can work with it

  // Fist check if a measurement is nessessary
  if((now() - lastMoistTime) > (MoistMeasureInterval(MoistMeasureIntervalNR()) / MOIST_SAMPLES())) 
  {

    log(now(), 1, PSTR("Feuchtemessung"), PSTR("durchgefuehrt"));     // Log the event on the SD Card
    freememorycheck();
    
    lastMoistTime = now();             // save the time of the measurement to check when the next measurement will be needed
    tempcheck();                       // Measure the current temperature 
    // Sollten mehr als eine Temperatur gemessen werden, so sollte der tempcheck() in die For Schleife unten aufgenommen werden

    int samples = MOIST_SAMPLES();       

    // If there are more than one sensors in more than one plant to measure, save it in an array
    for (pc = 0; pc < numberofprobes; pc++) 
    { 

      if(debug)
      {
        RedFly.disable();
        Serial.print("MoistMeasurement - pc: ");
        Serial.println(pc);
        RedFly.enable();
      }

      // Measure the wetness and save it for reusal
      digitalWrite(sensorpowerpin(pc), HIGH);
      delay(Messdauer());
      //take a measurement and put it in the first place        
      moistValues[pc * samples + 0] = analogRead(moistmeasurepin(pc));
      digitalWrite(sensorpowerpin(pc), LOW);       

      int MoiVal = moistValues[pc * samples + 0];
      // reduce the size of the code by shortening the variable                                         
      if(debug)
      {
        RedFly.disable();
        Serial.print(("Sensor ")); 
        Serial.print((pc));
        Serial.print((" : "));
        Serial.println(MoiVal);
        RedFly.enable();
      }

      HttpSend(pc, 1, MoiVal);  // Send the measured value to a MySQL Database

      if (pc %2 == 0)
        // Sensors 1 and 3 are right now no real plants
        // Thats why I don't want the sensorvalues to be evaluated
      {
        // Give the value of the measurement to the next function
        wateringCheck(MoiVal, pc);        // check to see if a watering event has occurred to report it
        // Give the value of the measurement to the next function
        moistureCheck(pc);              // check to see if moisture levels require Twittering out
      }
      else if (pc %2 != 0)
      {                               // Sensors 1 and 3 measure the water at the ground of the plant
        groundwaterCheck(MoiVal, pc);           // Evaluate if there is water at the ground of the plant
      }
    } // for pc
  } // if measurement is necessary
} // void

void printtime()
{
  // serially print out the time 

    time_t t = now();                           // store the current time in time variable t

  RedFly.disable();
  Serial.print(("Zeit (sek.): ")); 
  Serial.println(t);
  Serial.print(("Zeit (Uhr): "));         // "Zeit (Uhr): ");
  Serial.print(hour(t));
  printDigits(minute(t));
  printDigits(second(t));
  Serial.print(" ");
  Serial.print(day(t));
  Serial.print(" ");
  Serial.print(month(t));
  Serial.print(" ");
  Serial.print(year(t));
  Serial.println(""); 
  RedFly.enable();
}  

void printDigits(int digits){
  // utility function for digital clock display: prints preceding colon and leading 0

  Serial.print(":");
  if(digits < 10)
    Serial.print('0');
  Serial.print(digits);
} 

void getNPTtime()
{

  log(now(), 0, PSTR("Funktion getNPTtime"), PSTR("gestartet"));
  
  // initialize the client for the NTP Time
  #define NTPHost "pool.ntp.org"              //host
  //#define NTPHost "2.de.pool.ntp.org"    // Host in Germany http://de.wikipedia.org/wiki/NTP-Pool
  byte NTPServer[]  = {  
    0,  0,  0,  0           };      //{ 188,138,107,156 }; //ip from pool.ntp.org (server)

  if(RedFly.getip(NTPHost, NTPServer) == 0)  // get ip
  {
    uint32_t time;

    time = RedFly.gettime(NTPServer); // get time 
    if(time != 0UL)
    {
      char tmp[64];
      sprintf_P(tmp, ("Time: %lu sec since 1970"), time);
      spo2(tmp, 1);

      if(time > 56797200) // 56797200 = 13.03.2013
        // if the time is smaller than that date, the time from
        // NTPServer must be wrong - to avoid
        // that the time is reseted again and again 
        // the timeset should only happen if the time is higher

      {
        setTime(time + (3600*1));       // set the time to be overall available
        // (plus 1 hour for time in Germany)
        isNPT = true;
        log(now(), 1, PSTR("NPT Time"), PSTR("erhalten")); 
      }
      else 
      {
        isNPT = false; // The time from the NPT Server is somehow wrong
        log(now(), 1, PSTR("NPT Time"), PSTR("falsch erhalten. Uhrzeit falsch"));
      }
      printtime();                  // Print the time in digits

    }
    else
    {
      spo2(PSTR("NTP Error"), 1);
      isNPT = false; // We have no valid NPT time
      log(now(), 1, PSTR("NPT Time"), PSTR("nicht erhalten - Server Timeout"));
    }
  }
}

// this function blinks the an LED light as many times as requested
void blinkLED(byte targetPin, int numBlinks, int blinkRate) 
{
  for (int i=0; i<numBlinks; i++) {
    digitalWrite(targetPin, HIGH);           // sets the LED on
    delay(blinkRate);                        // waits for a blinkRate milliseconds
    digitalWrite(targetPin, LOW);            // sets the LED off
    delay(blinkRate);
  }
}

void startpumping(boolean start, long starttime, int plant)
{
  if (start)
  {                                         // The pumping starts imediately 
    // Give Current to the Basepin of the Transistor
    
    digitalWrite(PumpWarningLED, LOW);
    digitalWrite(PumpBasepin[plant], HIGH);     // Switch the Pump ON

    log(now(), 1, PSTR("Pumpe"), PSTR("eingeschaltet"));     // Log the event on the SD Card

    // Send to the MySQL Database that the watering-event took place
    // Send whether ist was a event that was evoked over the Website or that happened automatically
    // 1 means manuelly 0 means automatically
    int man; 
    if (manuell[plant] == true)
    {
      man = 1;
    }
    else 
    {
      man = 0; 
    }

    // check if the evoked pumping of water should stop
    if ((LastPumpingTime[plant] + PumpSeconds[plant]) <= now()) 
    {                                       // the pumping has to stop 
      
      digitalWrite(PumpBasepin[plant], LOW);// Switch the Pump OFF       
      spo2(PSTR("Wasserpumpe ausschalten"), 1);       
      // Wasserpumpe ausschalten

      log(now(), 1, PSTR("Pumpe"), PSTR("abgeschaltet"));     // Log the event on the SD Card 

      beginpumping[plant] = false;   // Prevents from pumping again

      if (manuell[plant] == true){
        mn[plant] = 100;
      } 
      // Reset the message number, to avoid overwatering over the website
      else {
        mn[plant] = 99;
      } 
      // according to the kind of watering event (selfwatering == manuell != true or watering 
      // by a person == manuell == true) the reset value for the message number differs
      // that is that way because in the next watering check, the messagenumber is checked to decide which kind 
      // of watering message should be send out

      // the messagenumer is checked in tab "Webserver" 

      //sumwater = sumwater + (PumpSeconds[plant] * mlPerSecond());      
      /*if (cosmON)
       { 
       // On the Cosm website I want the sum of water displayed that was given to the soil 
       // since the board started to work
       
       CosmFeed(777, sumwater); 
       // Send amount of water in Milliliter to the Cosm DataFeed 
       }
       */

      //HttpSend(plant, 6, sumwater);  // Send the over all amout of water to a MySQL Database 
      HttpSend(plant, 6, (PumpSeconds[plant] * mlPerSecond())); // Send the current amount of water to the Database
      HttpSend(plant, 7, man);       // Store in the Database that the pumping took place    
    

      manuell[plant] = false;              // Reset the status  
      //state[plant] = MOISTURE_OK;          // Anticipate that the moisture is OK after the watering event. 
      // That is important to show the right message on the website

      // Check if there is still water in the watertank
      watertankcheck();                 // Call the function to check if there is enough water in the watertank
      
      //lastMoistTime = 0; // set the last pumping time to evoke a new moistmeasurement after the pumping event.
      
    } // if Pumpzeit abgelaufen
  } // if start 
  else 
  {
    // the pumping has to stop or should not start
      digitalWrite(PumpBasepin[plant], LOW);// Switch the Pump OFF
      beginpumping[plant] = false;   // Prevents from pumping again      
  }  
} // void

void logRAM()
{
 spo2(PSTR("logRAM gestartet"), 1); 
/*  
  buf=(char *)malloc(strlen_P()+1);
  strcpy_P(buffer,progmemstring);
  Serial.println(buffer);
  free(buffer);  
  strcat_P(buffer, itoa(freeMemory(), buffer, 10));
*/
  //char *buf;
  //char *RAM; 
  
  char RAM[5];
  int myNumber = freeMemory();
  
  sprintf((char*)RAM,"%d",myNumber);
  //LCD.whateverPrintFunctionItIs(temp);

  //itoa(freeMemory(), RAM, 10);
  //buf=(char *)malloc(strlen_P(RAM)+1);
  //strcpy_P(buf, RAM);
  //Serial.println(buf);

  //char * charminute; 
  //RAM = (char*) calloc(5, sizeof(char));
  //sprintf(RAM, "%04i", freeMemory());    
  //sprintf_P(RAM, "%i", freeMemory());
  spo2(PSTR("RAM: "), 0); 
  spo2(RAM, 1); 
  //itoa(freeMemory(), RAM, 10);
  log(now(), 3, PSTR("RAM"), RAM);  

  //free(buf);
  free(RAM);
    
}

void SDCardLEDStatus()
{
  if(IsSDCard)
  {
    // switch the LED off
    digitalWrite(LogLED, 0);
  }
  else
  {
    // switch the LED on, because we have no valid NPT time
    digitalWrite(LogLED, 1);
  }
}
