void webserver()
{
 
 /*
 Arduino TCP Control
 
 A simple server that waits for incoming connections to set or read 
 digital Output Pins
   
 Using an Arduino Wiznet Ethernet shield or an Arduino Ethernet. 

 originally created 2012-04-28
 by Mario Keller
 
 adapted for RedFly Shield
 
 Adaptation based on WebServer Sketch from RedFly library examples to work with the RedFly WiFi shield
 
 */
        
  //listen for incoming clients
  if(server.available())
  {
    while(server.available())
    {
      //spo2(PSTR("While Webserver.available"), 1);
      blinkLED(WiFiStatusPin,4,20);        // Indicate that there is a connection made from the webclient      
        
      char command = server.read();
      int menge = server.read();
    
      if (debug)
      {
        RedFly.disable(); 
        Serial.print("command: "); 
        Serial.println(command);
        Serial.print("menge: "); 
        Serial.println(menge);        
        RedFly.enable();        
      }
      log(now(), 1, PSTR("Webserver"), PSTR("Clientzugriff findet statt"));     // Log the event on the SD Card
      
      boolean pumpeAN = false;  // Later this will be evaluated to switch the waterpump on or off
      int plant; // define which plant should be watered
      //String returnvalue = "";
      const char * returnvalue; 
      //int returnvalueTEMP[numberofprobes] = {0, 0}; 
      //PumpSeconds[numberofprobes] = {0, 0};
      //byte pinvalue =client.read();     

      if ((command != -1) || (menge >0))
      {      
        spo2(PSTR("command und menge besitzen Werte "), 1); 
    
        //Kommando auswerten
        switch(command) 
        {
        case 'P':
          spo2(PSTR("P - Pumpe an"),1); // P Kommando empfangen, Pumpe an 
          pumpeAN = true; 
          
          log(now(), 1, PSTR("Webserver"), PSTR("P Commando - Pumpe an"));     // Log the event on the SD Card
          
          //plant = 0; // later combine with a client.read
          
          PumpSeconds[plant] = PumpDuration();
    
          break;
        
        case 'Q':
          spo2(PSTR("Q - Pumpe an"), 1); // Q Kommando empfangen, Pumpe an
          pumpeAN = true;
          log(now(), 1, PSTR("Webserver"), PSTR("Q Commando - Pumpe an"));     // Log the event on the SD Card
          
          //plant = 0; // later combine with a client.read
          
          PumpSeconds[plant] = menge/mlPerSecond();
          if (PumpSeconds[plant] > PumpDuration())     // avoid overwatering in winter or autumn
          {
            PumpSeconds[plant] = PumpDuration();
          }    
          break;
          
        case 'R':
          spo2(PSTR("R - Pumpe an"), 1); //R Kommando empfangen
          pumpeAN = true;
          log(now(), 1, PSTR("Webserver"), PSTR("R Commando - Pumpe an"));     // Log the event on the SD Card
          
          //plant = 0; // later combine with a client.read      
          
          PumpSeconds[plant] = (menge + 255)/mlPerSecond();
          if (PumpSeconds[plant] > PumpDuration())     // avoid overwatering in winter or autumn
          {
            PumpSeconds[plant] = PumpDuration();
          }
          
          break;
          
/*    
        case 'M':
        // Gibt den aktuellen Feuchtigkeitswert als Status aus 
        
       
            // Sollte die Manuelle Pumpung nicht erlaubt sein, kann dies auch unterschiedliche Gründe haben. 
            // Entweder die Erde ist bereits zu feucht, oder noch ausreichend feucht, oder die letzte Gießung 
            // brachte ausreichend Feuchtigkeit in die Erde
            // Solle die manuelle Pumpung nicht noch einmal erforderlich sein soll abhängig von der 
            // zurückliegenden Zeit seit der letzten Wässerung entweder ein Hinweistext oder 
            // der normale Status der Feuchtigkeit der Erde angezeigt werden. 
    
            for (pc = 0; pc < numberofprobes; pc++)
            {
              if (pc %2 == 0)
                                                                   // Right now probe 1, 3 and 5are no plants, thats why 
                                                                   // I don't want them to appear on the website 
              {
                
                if ((((LastWateringDetection[pc] + (MinPumpTimeLag()*60)) > now()) 
                   || ((LastPumpingTime[pc] + (MinPumpTimeLag()*60)) > now())) 
                   && state[pc] != WATER_OVERFLOW)
                {
                  // Die letzte Wässerung liegt weniger als 3 Stunden zurück, also darf nicht noch einmal gewässert werden
                  // Es sei denn die Pflanze hatte bei der letzten Wässerung nicht genug Wasser, dann soll noch einmal
                  // gewässert werden können. 
                  // Es sei denn das Wasser ist bei der letzten Wässerung bereits durch den Topf durchgelaufen
                                                    
                    if ((mn[pc] == 6) || (mn[pc] == 49))
                    {
                    // Wurde die Pflanze bereits gegossen, die Gießung war aber nicht ausreichend, so wird ein anderer
                    // Status ausgegeben
                      
                      returnvalueTEMP[pc] = 6;                   // Erneute manuelle Pumpung erlauben          
                    }
                    else if (mn[pc] == 99 || mn[pc] == 100)
                    {
                      // es wurde gerade erst gepumpt, daher ist die Wässerung noch nicht freigegeben
                      returnvalueTEMP[pc] = 7;
                    }
                    //else if (state[pc] == URGENT_SENT || state[pc] == SOON_DRY_SENT || state[pc] == MOISTURE_OK)
                    else if ((mn[pc] == 2) || (mn[pc] == 3) || (mn[pc] == 1)) 
                    { 
                    // Es wurde eine normale Trockenheits-Warnung, oder der normale "mir geht es gut" Status
                    // getwittert, diese soll auf der Website angezeigt werden
                    
                      returnvalueTEMP[pc] = state[pc]; // Warnmeldung anzeigen, Pumpung erlauben
                    }
                    else 
                    {
                      returnvalueTEMP[pc] = 7;                   // Erneute Pumpung ist noch nicht freigegeben - Hinweistext auf der Webseite.               
                    }
                } // if
                else 
                {
                  // Wenn die letzte Pumpung länger als x Stunden her ist soll der normale Feuchtigkeitsstatus angezeigt werden
                  returnvalueTEMP[pc] = state[pc];
                } // else
                
                // assemble the returnvalue to send it to the website
                if (pc == 0)
                {
                  returnvalue = (itoa(returnvalueTEMP[pc], buffer, 10));
                }
                else 
                {
                  returnvalue += ";";
                  returnvalue += (itoa(returnvalueTEMP[pc], buffer, 10));
                }
              } // if pc  
            } // for
            
          //if(debug){spo2(PSTR("M - Messwert liefern"), true);} // M Kommando empfangen, Messwert liefern
    
          break;
        
        case 'F': 
        
            for (pc = 0; pc < numberofprobes; pc++)
            {
              if (pc %2 == 0) 
                                                                   // Right now probe 1, 3 and 5 are no plants, thats why 
                                                                   // I don't want them to appear on the website 
              {              
                if (lastMoistAvg[pc] != 0)
                { // Falls noch kein Messwert vorliegt, so soll auf den Initialmesswert zurückgegriffen werden
                // um überhaupt einen Messwert auf der Website anzeigen zu können
                  returnvalueTEMP[pc] = percent(lastMoistAvg[pc], pc);
                }
                else
                {
                  returnvalueTEMP[pc] = percent(lastWaterVal[pc], pc);
                }    
                
                // assemble the returnvalue to send it to the website
                if (pc == 0)
                {
                  returnvalue = (itoa(returnvalueTEMP[pc], buffer, 10));
                }
                else 
                {
                  returnvalue += ";";
                  returnvalue += (itoa(returnvalueTEMP[pc], buffer, 10));
                }
              } // if pc  
            } // for
          
          //if(debug){spo2(PSTR("F - Prozentfeuchte"), true);} // F Kommando empfangen, Prozentfeuchte liefern
          
          break;
    
        case 'V': 
    
            for (pc = 0; pc < numberofprobes; pc++)
            {      
              if (pc %2 == 0)
                                                                   // Right now probe 1, 3 and 5 are no plants, thats why 
                                                                   // I don't want them to appear on the website 
              {              
                if (lastMoistAvg[pc]  != 0)
                {
                  returnvalueTEMP[pc] = lastMoistAvg[pc];
                }
                else{
                  returnvalueTEMP[pc] = lastWaterVal[pc];
                } 
                // assemble the returnvalue to send it to the website
                if (pc == 0)
                {
                  returnvalue = (itoa(returnvalueTEMP[pc], buffer, 10));
                }
                else 
                {
                  returnvalue += ";";
                  returnvalue += (itoa(returnvalueTEMP[pc], buffer, 10));
                }
              } // if pc
            } // for          
          
          //if(debug){spo2(PSTR("V - Feuchte als Wert"), true);} // V Kommando empfangen, Feuchte als Wert liefern
        
          break;      
*/    
    
        default:
          spo2(PSTR("Fehler, unbekanntes Kommando"), 1); // Fehler, unbekanntes Kommando
          log(now(), 1, PSTR("Webserver"), PSTR("Fehler, unbekanntes Kommando"));     // Log the event on the SD Card    
          break;
    
        } // Switch  
        
      } // if command != -1
  
      if (pumpeAN)
      {
        
        log(now(), 1, PSTR("Wässerung"), PSTR("über Website angeordnet"));     // Log the event on the SD Card
        
        plant = 0; // later combine with a client.read = 0; // can later be replaced with a client.read(); 
        // by now it only should refer to the plant 0, which is the Banana
        if (debug) {printtime();}               // Systemzeit ausgeben      
        manuell[plant] = true;
        lastpumpcheck(plant); // Start Pumping Water, but first check if the soil is not wet already
        
        if (beginpumping[plant] == true)
        {
          log(now(), 1, PSTR("Wässerung"), PSTR("darf nach lastpumpcheck durchgeführt werden"));     // Log the event on the SD Card
          returnvalue = "9";
          //returnvalueTEMP[0] = 9;                   // Erneute Pumpung ist noch nicht freigegeben - Hinweistext auf der Seite.               
          //returnvalueTEMP[1] = state[2];
          //returnvalueTEMP[2] = state[4];
          //returnvalue = (itoa(9, buffer, 10)); // Pump was started - show this on the website
        }
        else
        {
          log(now(), 1, PSTR("Wässerung"), PSTR("darf nach lastpumpcheck nicht durchgeführt werden"));     // Log the event on the SD Card
          returnvalue = "8";
          //returnvalueTEMP[0] = 8;                   // Erneute Pumpung ist noch nicht freigegeben - Hinweistext auf der Seite.               
          //returnvalueTEMP[1] = state[2];
          //returnvalueTEMP[2] = state[4];
          //returnvalue = (itoa(8, buffer, 10)); // Pumping was canceled - show this on the website
        } 
        
        /*
          returnvalue = (itoa(returnvalueTEMP[0], buffer, 10));
          returnvalue += ";";
          returnvalue += (itoa(returnvalueTEMP[1], buffer, 10));
          returnvalue += ";";
          returnvalue += (itoa(returnvalueTEMP[2], buffer, 10));
        */          
                  
      } // if(pumpeAN)
  
        /*if(debug)
        {
          spo2(PSTR(". Wert = "), false); // . Wert =
          RedFly.disable();
          Serial.println(returnvalue);
          freememorycheck();
          RedFly.enable(); 
        } */    
  
    //RedFlyDE();                            // For some reasons the connection only works
                                           // stable if, the RedFly is previously disabled and immediately enabled again
                                           // This function does that for you
    //returnvalue.toCharArray(buffer, 10); 

    server.write(returnvalue);
    
    //close connection       
    //RedFlyDE();
    server.stop();         
    
    } // while server  
  }
  else if(!server.connected()) //listening port still open?
  {
    
    //RedFlyDE();
    /*if(debug)
    {*/
      spo2(PSTR("!server.connected()"), 1);
      spo2(PSTR("else: Server ist nicht connected --> neu verbinden"), 1); 
      //spo2(PSTR("Server.stop starten"), true);
   // } */
    
    server.stop(); //stop and reset server
    //RedFlyDE();
    //spo2(PSTR("Server.begin starten"), true);     
    server.begin(); //start server
  } // if server
  
  //spo2(PSTR("Server.stop starten"), true);  
  //server.stop();   
}




