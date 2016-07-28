
// Setup Start

void setup()
{

  // Inialize the Serial Communication
  Serial.begin(9600);                   // initialize the serial communication:
                                        // set the data rate for the hardware serial port 

  for (int i = 0; i < numberofprobes; i++)
  {
    pumpstate[i] = false;              // state of the pump-action;
    
    // the pumping has to stop or should not start
    digitalWrite(PumpBasepin[i], LOW);// Switch the Pump OFF
    beginpumping[i] = false;   // Prevents from pumping again   
  }

  // Initialize the SC Card for logging
  RedFly.disable();                   // Switch off the RedFly Shield to communicate serially
  spo2(PSTR("Initializing SD card..."), 1);
  // make sure that the default chip select pin is set to
  // output, even if you don't use it:
 
  // see if the card is present and can be initialized:
  if (!SD.begin(chipSelect)) 
  {
    spo2(PSTR("Card failed, or not present"), 1);
    // don't do anything more:
    //return;
    log(now(), 1, PSTR("Suche SD Card"), PSTR("nicht gefunden"));
    blinkLED(LogLED, 6, 100);       // blink 6 times to show that the initialization failed
    IsSDCard = false;               // if false a LED will light up
  }
  else
  {
    spo2(PSTR("card initialized."), 1);
    log(now(), 1, PSTR("Suche SD Card"), PSTR("gefunden"));    
    blinkLED(LogLED, 2, 100);       // blink 2 times to show that the initialization happened
    IsSDCard = true;                // if false a LED will light up
  }
     
  RedFly.enable();             // Stop Using Serial monitor enable the RedFly WiFi Shield again

  log(now(), 0, PSTR("Funktion Setup"), PSTR("gestartet"));
  //logRAM();

  // Define Pins
  pinMode(PumpWarningLED, OUTPUT);  
  pinMode(CosmComLED, OUTPUT);
  pinMode(LogLED, OUTPUT);  
  pinMode(WiFiStatusPin, OUTPUT);   
  pinMode(tanksensorpin, OUTPUT);
  pinMode(watertankwarnLED, OUTPUT);
  pinMode(MoistTimeLED1, OUTPUT);
  pinMode(MoistTimeLED2, OUTPUT); 
  pinMode(MoistTimeLED3, OUTPUT);
  pinMode(NPTLED, OUTPUT);
  pinMode(chipSelect, OUTPUT);  

  // Blink all the LEDs to check if they are working properly
  int blinkcount = 2;
  int blinktime = 200;
  blinkLED(watertankwarnLED, blinkcount, blinktime);     // Indicate the start
  blinkLED(CosmComLED,       blinkcount, blinktime);  
  blinkLED(PumpWarningLED,   blinkcount, blinktime);
  blinkLED(MoistTimeLED1,    blinkcount, blinktime); 
  blinkLED(MoistTimeLED2,    blinkcount, blinktime); 
  blinkLED(MoistTimeLED3,    blinkcount, blinktime);
  blinkLED(WiFiStatusPin,    blinkcount, blinktime);        // Indicate the start
  blinkLED(NPTLED,           blinkcount, blinktime);
  blinkLED(LogLED,           blinkcount, blinktime);  
  
  // Start the WiFi Connection
  WiFlyConnection = false; 
  WiFlyConnect();                       // Try to connect the WiFi Shield   
  SDCardLEDStatus();                    // Indicate if the SD Card is accessable

  // Get the default measure values
  for (pc = 0; pc < numberofprobes; pc++) 
  {
    // initialize moisture value array for every plant     

    pinMode(sensorpowerpin(pc), OUTPUT); 
    pinMode(PumpBasepin[pc], OUTPUT);
    // Defines the Sensorpin as Output
    // Reset the array values
    lastWaterVal[pc]    = 0;  
    mn[pc]   = 0; 
    LastWateringDetection[pc] = -1*(MinPumpTimeLag()*60);
    LastPumpingTime[pc] = -1 * MoistMeasureInterval(1); 
    pumpwarningtime[pc] = 0;     
    lastMoistAvg[pc]    = 0;
    counter[pc]         = 2;                    
    // init static counter, needed to define if the moisture average should be calculated of a value higher than the counter 
    manuell[pc]         = false;
    beginpumping[pc]    = false; 
    PumpSeconds[numberofprobes]   = 0; 
    state[pc] = MOISTURE_OK;
    lastWaterVal[pc] = 1000; 
    // With that high value we can avoid that there will be Watering Messages on tiwtter after booting
  }

  RedFly.enable();                  // Switch on the RedFly Shield again 
  
  // Check if there is still water in the watertank
  watertankcheck();                 // Call the function to check if there is enough water in the watertank
  
  log(now(), 0, PSTR("Setup"), PSTR("beendet"));
  //logRAM();
  
} // void





