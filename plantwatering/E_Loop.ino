// Loop
void loop()
{ 
  //WiFiConnect();                                      // Connect the WiFi Shield if not connected
  // this function is deactivated here, because we activate it when the moistdata should be transmitted
  // to the webserver with the php file an the MySQL database. That's why we don't need it here  
  WiFiLEDStatus();                                    // Switch the LED that indicates the WiFi status 
  NPTLEDStatus();                                     // Switch the LED that indicates if we have a valid NPT time in the arduino
  SDCardLEDStatus();                                  // Switch the LED that indicates if we can access the SD Card for logging
  webserver();                                        // Call the Webserver functionality
   
  for (pc = 0; pc < numberofprobes; pc++) 
  {
    if (manuell[pc] != true)
    {                                                 // If the pumping was initated automatically set the pumping duration to the default value
      PumpSeconds[pc] = PumpDuration();
    }
    startpumping(beginpumping[pc], LastPumpingTime[pc], pc); 
  }  

  // Es gibt einen Checklauf, dieser ermittelt den Feuchtigkeitswert der Erde und stellt diesen für
  // die weiteren Interpretationsschritte zur Verfügung
  moistmeasurement();
   
  pumpcheck();                                        // check if pumping is needed
  pumpwarningcheck();                                 // checks if the warning LED above the plant should be lighted due to lack of water

  watertankcheck();                                   // checks if there is still water in the watertank
  watertankwarningcheck();                            // checks if the warning LED for the watertank should be switched on of off
  moisttimeLEDcheck();                                // checks how many moisttimeLEDs should be lighted to indicate when the mext measurement takes place
}


