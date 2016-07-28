void tempcheck()
{
  
  log(now(), 0, PSTR("Function tempcheck"), PSTR("ausgefuehrt"));     // Log the event on the SD Card

  int temp;                 // value of the temperature sensor
  //If you're using a LM35 or similar, use line 'a' in the image above and the formula: Temp in °C = (Vout in mV) / 10
  // http://www.ladyada.net/learn/sensors/tmp36.html
  // http://learn.adafruit.com/tmp36-temperature-sensor

  temp = analogRead(temppin);                      // sensorvalue
  // converting that reading to voltage, for 3.3v arduino use 3.3
  temp = (temp * (5000/1024)/10);              // sensorvalue in millivolts in °C including a correction value due to some measure errors
  
  // Send the value to the MySQL database
  HttpSend(99, 1, temp);
  
  if(debug)
  {
    //printtime();
    RedFly.disable(); 
    Serial.print("Temperatur: "); 
    Serial.print(temp);
    Serial.println(" °C");
    RedFly.enable(); 
  }
}

//function for checking for watering events
void wateringCheck(int CurrentMoisture, int plant)   // CurrentMoisture was measured in the void moisture measurement
// This should shorten the code and make it easier to read
{
  log(now(), 0, PSTR("Funktion wateringCheck"), PSTR("gestartet"));     // Log the event on the SD Card  
  
  int samples = MOIST_SAMPLES();    // number of moisture samples to average

  if ((CurrentMoisture >= lastWaterVal[pc] + WATERING_CRITERIA) 
    || (mn[plant] == 100) 
    || (mn[plant] == 99)) 
  { 
    // if we've detected a watering event
    // Watering events can be detected by a difference in the WaterValue, that results from a measure 
    // or it results from a manual watering action over the website 
    // that is nessessary if the watering action did not provide enough water to change the soil moisture segnificantly

    log(now(), 1, PSTR("Waesserung"), PSTR("festgestellt"));     // Log the event on the SD Card
    
    LastWateringDetection[plant] = now(); 
    // Store the time when the last watering event was detected

    boolean automatisch; 
    if (mn[plant] == 99)
    {
      automatisch = true;
      log(now(), 1, PSTR("Waesserung"), PSTR("fand automatisch statt"));     // Log the event on the SD Card
    } 
    // the last status Message was a "Selfpumping" Message 
    // that will influece the "Thank you" messages, if the plant watered itself
    // if the pumping was evoked by a person, the reset messagenumer would be 100. 
    // That value is set by the void startpumping
    else
    {
      automatisch = false;
      log(now(), 1, PSTR("Waesserung"), PSTR("fand manuell statt"));     // Log the event on the SD Card
    }

    // to show the current moisture imediately on the website and to avoid
    // a slow increase of the moisture value we set up the current 
    // moisture as the new average moisture. 
    // Therefore we put the current moisture value to all field in the array

    // int CurrentMoisture = moistValues[plant * samples + 0]; 
    for(int i = 0; i < MOIST_SAMPLES(); i++) 
    { 
      moistValues[plant * samples + i] = CurrentMoisture; 
    } 
    counter[plant] = samples;        
    // This evokes later that the moisture average is calculated over the 
    // number of MOIST_SAMPLES 


    // Decide which Twitter Message should be send out
    int measureval = percent(CurrentMoisture, plant);
    // This is the value that will be evaluated in the next if statement
    int lastmeasureval = percent(lastWaterVal[plant], plant);
    // This is the value of the last  measurement in percent which is evaluated later        

    // the + 3 is a tollarance value
    if (measureval > TOOMOIST + 3 
      && lastmeasureval < MOIST)
    {
      // Watering event with too much water 
      if (automatisch == false)
      {
        mn[plant] = 68;   // too much water by watering event
      }
      else
      {
        mn[plant] = 69;   // Too much water by myself
      }

      //TwitterCounter = TwitterCounter + 1;  // Counts up. 
      //posttweet(mn[plant], plant, CurrentMoisture);   
      // announce to Twitter 
      state[plant] = TOOWET;         // To avoid a double twittering due to the change of the state in the moistcheck
      log(now(), 1, PSTR("Waesserung"), PSTR("zu feucht"));     // Log the event on the SD Card
    }                                   
    else if (measureval >= SOAKED  
      &&  lastmeasureval < MOIST)
    {

      if (automatisch == false)
      {
        mn[plant] = 4;   // THANK_YOU
      }
      else
      {
        mn[plant] = 47;   // Thank you to myself - enough water
      }

      //TwitterCounter = TwitterCounter + 1;  // Counts up. 
      //posttweet(mn[plant], plant, CurrentMoisture);   
      // announce to Twitter
      state[plant] = MOISTURE_OK;              // To avoid a double twittering due to the change of the state in the moistcheck 
      log(now(), 1, PSTR("Waesserung"), PSTR("ausreichend"));     // Log the event on the SD Card      

    } // if
    else if (measureval >= SOAKED           // the Soil is now soaked (not toomoist) but was not dry before 
    && measureval <= TOOMOIST       // 
    && lastmeasureval >= MOIST) 
    {

      if (automatisch == false)
      {
        mn[plant] = 5;    // Watering was not needed but thank you anyway
      }
      else
      {
        mn[plant] = 48;   // Thank you to myself although water was not needed
      }

      //TwitterCounter = TwitterCounter + 1;  // Counts up. 
      //posttweet(mn[plant], plant, CurrentMoisture);   
      // announce to Twitter   
      state[plant] = MOISTURE_OK;    // To avoid a double twittering due to the change of the state in the moistcheck                           
      log(now(), 1, PSTR("Waesserung"), PSTR("ausreichend - vorher nicht trocken"));     // Log the event on the SD Card

    } // else if
    else if (measureval < SOAKED  // It was not enough water to become soaked
    && lastmeasureval < MOIST)
    {

      if (automatisch == false)
      {
        mn[plant] = 6;    // UNDER_WATERED
      }
      else{
        mn[plant] = 49;   // Thank you to myself - not enough water
      }          

      //TwitterCounter = TwitterCounter + 1;  // Counts up. 
      //posttweet(mn[plant], plant, CurrentMoisture);   // announce to Twitter
      state[plant] = SOON_DRY_SENT;         // To avoid a double twittering due to the change of the state in the moistcheck                           
      log(now(), 1, PSTR("Waesserung"), PSTR("nicht ausreichend"));     // Log the event on the SD Card
      
    } // else if


    pumpstate[plant] = PumpingNotNeeded; 
    // Because a watering event was detected it is not necessary to pump water automaticaly
    spo2(PSTR("Waesserung nicht erforderlich"), 1);           // "Pumpe wird nicht aktiviert");  

    HttpSend(plant, 3, state[plant]);             // send the current moisture state to the MySQL Database
    HttpSend(plant, 4, mn[plant]);             // send the current message to the MySQL Database
    //HttpSend(plant, 5, pumpstate_int(pumpstate[plant]));      // send the current state if watering is needed to the MySQL Database

  } 

  lastWaterVal[plant] = CurrentMoisture; // record the watering reading for comparison next time this function is called

} // void


//function for checking soil moisture against threshold
void moistureCheck(int plant) 
{

  log(now(), 0, PSTR("Funktion moistureCheck "), PSTR("gestartet"));     // Log the event on the SD Card

  int moistAverage[numberofprobes];
  int moistTotal[numberofprobes]; 
  int moistflag = 0;        // Flag that indicates the handling event based on the average moisture
  int samples = MOIST_SAMPLES();

  for (int i = 0; i < numberofprobes; i++)
  {
    moistAverage[i]   = 0;                    // init soil moisture average
    moistTotal[i]     = 0;                    // create a little local int for an average of the moistValues array
  } // for


  for(int i = samples - 1; i > 0; i--) 
  {

    // move the first measurement to be the second one, and so forth until we reach the end of the array.
    moistValues[plant * samples + i] = moistValues[plant * samples + i-1];    

  } // for

  for(int i = 0; i < samples; i++) 
  {                                           // average the measurements (but not the nulls)
    moistTotal[plant] += moistValues[plant * samples + i];
    // in order to make the average we need to add them first    
  }

  if(counter[plant] < samples) 
  {
    moistAverage[plant] = moistTotal[plant] / counter[plant];
    counter[plant]++;               // this will add to the counter each time we've gone through the function
  }
  else 
  {
    moistAverage[plant] = moistTotal[plant] / samples;
    //here we are taking the total of the current light readings and finding the average by dividing by the array size
  } 

  HttpSend(plant, 2, percent(moistAverage[plant], plant));  // Send the measured percent value to a MySQL Database

  //if (cosmON)
  //{                             // Only send the data to cosm feed when enabled
  if (debug)
  {
    RedFly.disable();
    Serial.print(PSTR("Durchschnittsfeuchte, Sensor "));
    Serial.print(plant);
    Serial.print(PSTR(": "));
    Serial.println(moistAverage[plant]);
    RedFly.enable();
  } 
  //CosmFeed(plant, percent(moistAverage[plant], plant)); 
  // Send Average Soil-Moisture as a percent value      
  //}

  // If it is allowed to twitter out again than it should be checked what kind of message should be sent out
  if(debug)
  {
    RedFly.disable();
    Serial.print(PSTR("Sensor "));
    Serial.print(plant);
    Serial.print(PSTR(" state: "));
    Serial.println(state[plant]);
    RedFly.enable();
  }
  if (state[plant] != WATER_OVERFLOW)       // Only if there is no Water at the ground of the pot it is possible to get another status
    // Die Statusmeldung "Wasser im Topf" ist dominanter als die normalen Feuchtigkeitsstatus der Pflanze
    // Die Feuchtigkeitsstatus der Pflanze dürfen sich nur dann 
    // ändern können, wenn der "Wasser im Topf" Status nich aktiv ist

  {
    // Start the Event-Handler based on the Average Moisture

    int moistavg = percent(moistAverage[plant], plant);
    // We will need this imediately to check the wetness

    if (moistavg >= TOOMOIST) 
    {
      //if (debug){spo2(PSTR("Soil is too moist!"), 1);} 
      moistflag = TOOWET;
      log(now(), 1, PSTR("Feuchtemessung"), PSTR("zu feucht"));     // Log the event on the SD Card
    }                                     // Soil is too moist // TOOMOIST
    else if (moistavg < TOOMOIST 
      && moistavg >= MOIST) 
    {
      //if (debug){spo2(PSTR("Soil is OK moist"), 1);} 
      moistflag = MOISTURE_OK;
      log(now(), 1, PSTR("Feuchtemessung"), PSTR("Feuchte OK"));     // Log the event on the SD Card
    }                                     // Soil is moist // MOISTUR_OK
    else if (moistavg < MOIST 
      && moistavg > DRY 
      && (state[plant] <= SOON_DRY_SENT)) 
      // We need "<=Soon_dry_sent" to guarantee that messages are only sent out if the
      // moisture decreases and not increases
    {
      //if (debug){spo2(PSTR("Soil is soon dry"), 1);} 
      moistflag = SOON_DRY_SENT;
      log(now(), 1, PSTR("Feutemessung"), PSTR("bald trocken"));     // Log the event on the SD Card
    }                                     // Soil is soon dry  
    else if (moistavg <= DRY) 
    {
      //if (debug){spo2(PSTR("Soil is dry"), 1);} 
      moistflag = URGENT_SENT;
      log(now(), 1, PSTR("Feuchtemessung"), PSTR("trocken"));     // Log the event on the SD Card
    }         // Soil is dry
  } // if state
  else 
  {
    moistflag = WATER_OVERFLOW;          // there ist water at the ground of the plant pot
    log(now(), 1, PSTR("Feuchtemessung"), PSTR("Wasser im Übertopf"));     // Log the event on the SD Card
  } // else state 

  if(debug)
  {
    spo(PSTR("Sensor "), 0);
    spo(itoa(plant, buffer, 10), 1);
    spo2(PSTR(" moistflag: "), 0);
    spo(itoa(moistflag, buffer, 10), 1);
    RedFly.enable();
  }
  // check if the message type 1 was sent out before
  if (moistflag != state[plant]) 
  {
    // if the now statet messagetype differs from the last message type then figure out what message should be sent out now
    // if the current moistflag equals the last one do nothing more

    log(now(), 1, PSTR("Feuchtestatus"), PSTR("Aenderung festgestellt"));     // Log the event on the SD Card
    
    switch (moistflag) 
    {

    case WATER_OVERFLOW: 

      mn[plant] = 46;            // Das Wasser lief durch bin in den Topf

      pumpstate[plant] = PumpingNotNeeded;  // flag that the pumping is now needed 
      spo2(PSTR("Waesserung nicht erforderlich"), 1);       

      state[plant] = moistflag;           // remember this message

      HttpSend(plant, 3, state[plant]);             // send the current moisture state to the MySQL Database
      HttpSend(plant, 4, mn[plant]);             // send the current message to the MySQL Database
      //HttpSend(plant, 5, pumpstate_int(pumpstate[plant]));      // send the current state if watering is needed to the MySQL Database

      //TwitterCounter = TwitterCounter + 1;       // Counts up.  
      //posttweet(mn[plant], plant, moistAverage[plant]);   
      // announce to Twitter                       
      // Eine Twittermeldung für die eigentliche Pflanze wird mit dem Hinweis "Wasser im Topf" verschickt

      if(debug){
        spo2(PSTR("moistflag: WATER_OVERFLOW"),1);
      }

      break;                  

    case TOOWET:

      mn[plant] = 0;             // TOO_MUCH_WATER_LEFT

      pumpstate[plant] = PumpingNotNeeded;  // flag that the pumping is not needed    
      spo2(PSTR("Waesserung nicht erforderlich"), 1);       

      state[plant] = moistflag;           // remember this message   

      HttpSend(plant, 3, state[plant]);            // send the current moisture state to the MySQL Database
      HttpSend(plant, 4, mn[plant]);            // send the current message to the MySQL Database
      //HttpSend(plant, 5, pumpstate_int(pumpstate[plant]));     // send the current state if watering is needed to the MySQL Database

      //TwitterCounter = TwitterCounter + 1;       // Counts up.  
      //posttweet(mn[plant], plant, moistAverage[plant]);   
      // announce to Twitter                       
      if(debug){
        spo2(PSTR("moistflag: TOOWET"),1);
      }                                                                  
      break;

    case MOISTURE_OK:

      mn[plant] = 1;             // ENOUGH_WATER_LEFT   

      pumpstate[plant] = PumpingNotNeeded;  // flag that the pumping is not needed   
      spo2(PSTR("Waesserung nicht erforderlich"), 1);       

      state[plant] = moistflag;           // remember this message            

      HttpSend(plant, 3, state[plant]);            // send the current moisture state to the MySQL Database
      HttpSend(plant, 4, mn[plant]);            // send the current message to the MySQL Database
      //HttpSend(plant, 5, pumpstate_int(pumpstate[plant]));     // send the current state if watering is needed to the MySQL Database

      //TwitterCounter = TwitterCounter + 1;       // Counts up.  
      //posttweet(mn[plant], plant, moistAverage[plant]);   
      // announce to Twitter                       
      if(debug){
        spo(PSTR("moistflag: MOISTURE_OK"),1);
      }                                                                  

      break;

    case SOON_DRY_SENT:

      mn[plant] = 2;                                  // SOON_NO_WATER               

      pumpstate[plant] = PumpingNotNeeded;            // flag that the pumping is not needed    
      spo(PSTR("Waesserung nicht erforderlich"), 1);       

      state[plant] = moistflag;                     // remember this message            

      HttpSend(plant, 3, state[plant]);         // send the current moisture state to the MySQL Database
      HttpSend(plant, 4, mn[plant]);            // send the current message to the MySQL Database
      //HttpSend(plant, 5, pumpstate_int(pumpstate[plant]));     // send the current state if watering is needed to the MySQL Database

      //TwitterCounter = TwitterCounter + 1;       // Counts up.  
      //posttweet(mn[plant], plant, moistAverage[plant]);   
      // announce to Twitter                       
      if(debug){
        spo2(PSTR("moistflag: SOON_DRY_SENT"),1);
      }                                                                  
      break;

    case URGENT_SENT:

      if ((LastPumpingTime[plant] <= 0) || (now() > (LastPumpingTime[plant] + (MinPumpTimeLag() * 60))))
      {
        // If after the start of the Arduino there was no selfwatering-action so far, 
        // but the dryness of the soil is already urgent
        // The signal for urgent dryness will be sent out imediately
        // Otherwise the signal for urgent dryness will only be sent out if the last selfwatering-action 
        // took place some time in the past. 
        mn[plant] = 3;                      // NO_WATER_LEFT

        pumpstate[plant] = PumpingNeeded;   // flag that the pumping is now needed
        spo(PSTR("Waesserung IST erforderlich"), 1);         

        pumpwarningtime[plant] = now();     // save the time when the twitter dryness-warning was sent out.  

        state[plant] = moistflag;           // remember this message            

        HttpSend(plant, 3, state[plant]);         // send the current moisture state to the MySQL Database
        HttpSend(plant, 4, mn[plant]);            // send the current message to the MySQL Database
        //HttpSend(plant, 5, pumpstate_int(pumpstate[plant]));     // send the current state if watering is needed to the MySQL Database

        //TwitterCounter = TwitterCounter + 1;       // Counts up.  
        //posttweet(mn[plant], plant, moistAverage[plant]);   
        // announce to Twitter                         

      }
      if(debug){
        spo(PSTR("moistflag: URGENT_SENT"),1);
      } 
      break;
    } // switch 

  } // if moistflag

  if(debug)
  {
    char value2[2]; // To convert the int to char*
    spo2(PSTR("Pumpstate, plant "), 0);
    spo(itoa(plant, value2, 10), 0);
    spo2(PSTR(": "), 0);
    spo(itoa(pumpstate[plant], value2, 10), 1); 
  }

  lastMoistAvg[plant] = moistAverage[plant]; // record this moisture average for comparison the neplantcountert time this function is called

} // void

void freememorycheck()
{
  int mem = freeMemory();
  spo2(PSTR("freeMemory: "), 0);                  // freeMemory: 
  spo(itoa(mem, buffer, 10), 1);
  HttpSend(98, 1, mem);

} // void

void groundwaterCheck(int val, int plant)
{
  // val is the value of the watermeasurement

  log(now(), 0, PSTR("Funktion groundwaterCheck"), PSTR("gestartet"));     // Log the event on the SD Card
  
  if ((val > 100) && state[plant - 1] != WATER_OVERFLOW)
  {                                          // Wenn der Status vorher schon dieser war, dann passiert nichts.
    // Der Status der Pflanze wird auf "Wasser im Topf geändert"
    // Der Status der Pflanze wird gespeichert
    // There is water on the ground of the pot
    state[plant - 1] = WATER_OVERFLOW;// Switch the Plant Status of the plant not for the sensor on the ground of the pot    

    log(now(), 1, PSTR("groundwatercheck"), PSTR("Wasserueberlauf festgestellt"));     // Log the event on the SD Card
    
    // Der Status der Pflanze wird der Website zur Verfügung gestellt
    if(debug){
      spo2(PSTR(" : WATER_OVERFLOW"), 1);
    }
    pumpstate[plant-1] = PumpingNotNeeded; // That will switch of the WarningLED later

    spo2(PSTR("Waesserung nicht erforderlich"), 1); 
    //HttpSend((plant-1), 5, pumpstate_int(pumpstate[plant-1]));    

  } // if moistmeasurevalue
  else if ((val < 100) && (state[plant - 1] == WATER_OVERFLOW))
  {
    // Nur wenn der Pflanzenstatus vorher "Wasser im Topf" war, 
    //darf er geändert werden, da sonst die "normalen" Feuchtigkeitsstatus überschrieben würden 
    if (state[plant - 1] == WATER_OVERFLOW)
    { // Wenn der Status sich wieder ändert und der Topf unten trocken ist, 
      // dann wird der Status wieder geändert

      // There is no Water on the ground of the pot
      state[plant - 1] = NO_WATER_OVERFLOW;
      log(now(), 0, PSTR("groundwaterCheck"), PSTR("kein Wasserueberlauf"));     // Log the event on the SD Card
      // Switch the Plant Status of the plant not for the sensor on the ground of the pot                                                  
      //if(debug){spo2(PSTR(" : NO_WATER_OVERFLOW"), 1);}
    } // state
  } // else if moistmeasurevalue
  spo2(PSTR(""),1);
} // void


void pumpcheck()
{
  //Checks whether to pump water or not
  /*  
   if(debug)
   {
   spo2(PSTR("pumpcheck() gestartet"), 1);
   }
   */
  int Pumpwait = 120;                              // Duration in minutes in which the waterpump waits after warning due to dry soil

  for(pc = 0; pc < numberofprobes; pc++)
  {

    if (((now() >= ((Pumpwait * 60) + pumpwarningtime[pc])) && (pumpstate[pc] == PumpingNeeded)))
    {
      log(now(), 1, PSTR("Funktion pumpcheck"), PSTR("ergab Selbstwässerungsbedarf"));     // Log the event on the SD Card
      manuell[pc] = false;           // Puming was initiated automatically due to dry soil
      lastpumpcheck(pc);             // last check if the pumping is really nescessary

    } // if  
  } // for
}

void pumpwarningcheck()
{
  // This function gives current to the warning LED 

  /*                                                  
   pc = 0;                              // By now we only display the dryness of the first plant with a LED                                                  
   
   switch (pumpstate[pc]) 
   {
   
   case PumpingNeeded:
   digitalWrite(PumpWarningPin, HIGH);
   break;
   
   case PumpingNotNeeded:                    // or Not
   digitalWrite(PumpWarningPin, LOW);
   break;
   }
   */

  int countneeded = 0;                            // First count how many plants are urgendly dry
  for (int i=0; i < numberofprobes; i++)
  {
    if (i %2 == 0)                                // only check the dryness of real plants. The real plants have the even pc (e.g. 0, 2, 4)
    {
      if (pumpstate[i] == PumpingNeeded)
      {
        countneeded = countneeded + 1;
      }
    }
  }

  if (countneeded > 0)                              // If at least one plant is dry switch on the warning LED
  {
    digitalWrite(PumpWarningLED, HIGH);
  }
  else
  {
    digitalWrite(PumpWarningLED, LOW);
  }
}

void lastpumpcheck(int plant)
{  
  // this is the last security check to avoid overwatering
  // if the moisture right before the watering event ist higher then DRY the Selfwatering-Event stops

  // Boolean manuell true means the pumping was evoked manually by a person e.g. on the website
  // Boolean manuell false means the pumping starts due to the automatic watering event 

  log(now(), 0, PSTR("Funktion lastpumpcheck"), PSTR("gestartet"));     // Log the event on the SD Card
  
  digitalWrite(sensorpowerpin(plant), HIGH);
  delay(Messdauer());
  int CurrentMoisture = analogRead(moistmeasurepin(plant)); 
  // take a moisture measurement
  digitalWrite(sensorpowerpin(plant), LOW);
  
  // The following HttpSends take in total too much time, thats why they are deaktivated by now
  // HttpSend(plant, 1, CurrentMoisture);  // Send the CurrentMoisture to the Database
  // HttpSend(plant, 2, percent(CurrentMoisture, plant));  // Send the Moisture in percent to the Database   

  if (percent(CurrentMoisture, plant) <= MOIST + 5) // The + 3 is added here to handle the possible error bandwidth of the sensor +5%
  {
    if (debug)
    {
      spo2(PSTR("Pumpe muss angeworfen werden"), 1);   // Pumpe muss angeworfen werden;
    }

    log(now(), 1, PSTR("lastpumpcheck"), PSTR("Pumpe muss angeworfen werden"));     // Log the event on the SD Card

    if (manuell[plant] != true) 
    {
      // TwitterCounter = TwitterCounter + 1;    // Counts up.
      mn[plant] = 7;               // SELFPUMP // : DU wolltest ja nicht. Nun mache ICH Selbstbegiessung   
      //posttweet(mn[plant], plant, CurrentMoisture);   
      // announce to Twitter 
      HttpSend(plant, 4, mn[plant]);  // Send the Message to the Database
    }
    // start pumping water
    LastPumpingTime[plant] = now();           // store the last pumping time
    //lastMoistTime = 0;//(-1 * (MoistMeasureInterval(MoistMeasureIntervalNR()) / MOIST_SAMPLES()));
                                              // save the time of the measurement to check when the next measurement will be needed 
                                              // due to that lastMoistTime the next moisture measurement will start imediately   
    beginpumping[plant] = true;               // The function startpumping() will grab this state. If true the pump starts pumping 
  }
  else 
  {
    spo2(PSTR("Abbruch, Erde zu freucht"), 1);     // "Abbruch, zu feucht");

    log(now(), 1, PSTR("lastpumpcheck"), PSTR("Abbruch, Erde zu feucht"));     // Log the event on the SD Card
        
    // Send to the database, that the watering did not took place 
    // 2 = watering did not took place 
    // 1 = watering was evoked over the website
    // 0 = watering took place automatically
    HttpSend(plant, 7, 2);       // Store in the Database that the pumping took place    

    //TwitterCounter = TwitterCounter + 1;      // Counts up.
    mn[plant] = 8;                 // PUMPCANCEL // : Bewaesserungsabbruch, die Erde ist jetzt doch feucht genug.
    //posttweet(mn[plant], plant, CurrentMoisture);   
    // announce to Twitter 
    HttpSend(plant, 4, mn[plant]);  // Send the Message to the Database

    // Evoke a new moisture Measurement to show the moisture value on the website
    HttpSend(plant, 1, CurrentMoisture);    
    HttpSend(plant, 2, percent(CurrentMoisture, plant));

    beginpumping[plant] = false;
  }

  pumpstate[plant] = PumpingNotNeeded;      // Wether the pumping took place or not the pumpstate is reseted
  // Right now I don't know if I realy need this status. And it takes to long to send it, while the 
  // person, who send the pumping request waits for a response on the website. 
  // That's why the HttpSend ist deactivated right now.
  // HttpSend(plant, 5, pumpstate_int(pumpstate[plant]));  // Send the Pumpstate to the Database
  
  pumpwarningtime[plant] = now();           // Reset the timer     
}

void watertankcheck()
{
  
  long checkinterval = 2; // Intervall in hours
  checkinterval = checkinterval * 60 * 60; // calculate interval in seconds
  int std = hour(now());
  
  // The watertankcheck will be enabled every two hours between 6 and 10 AM an 6 and 12 PM or when the setup function called the function for the watertankcheck
  // if(((IsWatertankcheck + checkinterval) < now() && (hour(now()) == 8 || hour(now()) == 17 || hour(now()) == 20 || hour(now()) == 22 || hour(now()) == 23)))
  if(((IsWatertankcheck + checkinterval) < now() && ((std >= 6 && std <= 10) || (std >= 18 && std <= 24) )) || watertankstartcheck)
  {

    log(now(), 1, PSTR("Wassertank-Messung"), PSTR("durchgefuehrt"));  // Write the event to the SD Card
    
    
    // Once the watertankcheck was made it should be stored that it was made
    IsWatertankcheck = now();
    watertankstartcheck = false; // The setup took place, so the value can be changed to avoid duplicate watertankchecks
    
    // First measure the analog input for the moisture Sensor in the watertank
    int sensval = 0;
    digitalWrite(tanksensorpin, HIGH);
    delay(Messdauer());
                                     //take a measurement and put it in the first place                                             
    sensval = analogRead(watertankpin);
    digitalWrite(tanksensorpin, LOW);
    
    HttpSend(97, 1, sensval); // Send the Measure from Sensor 97 (Wassertank) to the MySQL Server

    /*if(debug)
    {
      RedFly.disable();
      Serial.println("watertankcheck() gestartet");
      Serial.print("Sensorvalue aus Wassertank: ");
      Serial.println(sensval);
      RedFly.enable();
    }*/
    
    // send the measurevalue to cosm
    /*if(cosmON)
    {                                // Only send the data to cosm feed when enabled
      CosmFeed(6, sensval); 
                                     // Send Watertank-Measure-Value to the Cosm DataFeed         
    }
    */
    
    // if the returnvalue is lower than 50 then a warning LED should be switched on 
    // and a twitter message should be send out
    // later the warning status for the watertank should be displayed on the website
    if(sensval < 500)
    {
      //TwitterCounter = TwitterCounter + 1;   // Counts up. 
      //posttweet(13, 6, sensval);             // 13 = Message, 6 = number of sensor, measurevalue
      IsWatertankwarnLED = true;             // This will switch on the LED later
      HttpSend(97, 4, 13);    // Send the message number 13 from Sensor Wassertank 
      log(now(), 1, PSTR("Wassertank Wasserstand"), PSTR("leer"));  // Write the event to the SD Card
    }
    // if the returnvalue is higher than 100 the warning LED should be switched off again
    else if(sensval >= 50)
    {
      IsWatertankwarnLED = false;            // This will switch off the LED later
      log(now(), 1, PSTR("Wassertank Wasserstand"), PSTR("voll"));  // Write the event to the SD Card
    } // if sensval
  } // if timer
} // void

void watertankwarningcheck() // If warning is needed the LED is switched on
{
  /*
  if(debug)
  {
    spo2(PSTR("watertankwarningcheck() gestartet"), 1);
  }  
  */
  if(IsWatertankwarnLED)
  {
    digitalWrite(watertankwarnLED, HIGH);
  }
  else if(!IsWatertankwarnLED)
  {
    digitalWrite(watertankwarnLED, LOW);
  }
}

void moisttimeLEDcheck()
{
  // First check how much time is left until the next moisture measurement takes place
  int numLED = 3; // How many LEDs indicate the time status?
  long timeleft = now() - lastMoistTime; 
  timeleft = timeleft / 60; // change timeformat from seconds to minutes
  
  // Secondly check if it is 30-20 min, 20-10 min or 10-0 min 
  if (timeleft <= MoistMeasureMinutes() / numLED * 1)
  {
    // less than 10 minutes after the last measurement. switch all LED on
    digitalWrite(MoistTimeLED1, 1);
    digitalWrite(MoistTimeLED2, 1);
    digitalWrite(MoistTimeLED3, 1);
  }  
  else if (timeleft <= MoistMeasureMinutes() / numLED * 2)
  {
    // less then 20 but more than 10 minutes after the last measurement. switch one LED off
    digitalWrite(MoistTimeLED1, 1);
    digitalWrite(MoistTimeLED2, 1);
    digitalWrite(MoistTimeLED3, 0);
  }  
  else if (timeleft <= MoistMeasureMinutes() / numLED * 3) 
  {
    // less than 30 but more than 20 minutes after the last measuremet? Switch two LEDs off
    digitalWrite(MoistTimeLED1, 1);
    digitalWrite(MoistTimeLED2, 0);
    digitalWrite(MoistTimeLED3, 0);
  }
  else 
  {
    // something must be wrong here. Switch all the LEDs off
    digitalWrite(MoistTimeLED1, 0);
    digitalWrite(MoistTimeLED2, 0);
    digitalWrite(MoistTimeLED3, 0);
  }
  // Switch the LEDs on or of  
}
