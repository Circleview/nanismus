// in debug mode the measurement shoult place more often
int MoistMeasureInterval(int nr)
{
  // 1. Moist Measurement
  if(nr == 0)
  {
    return(2 * MOIST_SAMPLES() * 60); // seconds in debug mode
  }
  else if(nr == 1)
  {
    return(MoistMeasureMinutes() * MOIST_SAMPLES() * 60);// seconds in normal mode  
  }
  // seconds over which to average moisture samples
  // intervall = 30 minutes * 10 samples * 60 seconds  
}

int MoistMeasureMinutes() // How many minutes between the moist measurements?
{
  return(30);             // Time in minutes
}

int MOIST_SAMPLES()
{
  // 1. Moist Measurement
  return(10);               // number of moisture samples to average
}

int MoistMeasureIntervalNR()             // To Choose from the Array of Moist Samples in Debug Mode
{
  if(debug)
  {
    //spo2(PSTR("moistmeasurement() started"), 1);
    return(0);
  }
  else
  {
    return(1);
  }
}
// Declarations for the moisture measurement
int sensorpowerpin(int nr) 
{
  switch (nr)
  {
  case 0: 
    return(34);
    break;                // Plant 1      
  case 1:
    return(32);
    break;                // Ground Water Plant 1
  case 2:
    return(100);  
    break;                // Plant 2
  case 3: 
    return(100);
    break;                // Ground Water Plant 2
  case 4: 
    return(100);
    break;                // Plant 3
  case 5:
    return(100);
    break;                // Ground Water Plant 3
  }// switch
}                           // Digital Pin, feeds power to the moisture probes

int Messdauer()
{
  return(200);            // Delay in Milliseconds for having current to the sensor
}

// Declaration for Moinsture Measurement 
// Analog Input Pin
int moistmeasurepin(int nr) 
{
  switch (nr)
  {
  case 0: 
    return(10);
    break;                // Moist Sensor 1    
  case 1:
    return(12);
    break;                // Moist Sensor 2
  case 2:
    return(99);  
    break;                // Moist Sensor 3
  case 3: 
    return(99);
    break;                // Moist Sensor 4
  case 4: 
    return(99);
    break;                // Moist Sensor 5
  case 5:
    return(99);
    break;                // Moist Sensor 6
  }// switch
}                         // Analog Pin for Moisture Measurement


int DRYVAL(int sensor)                 // What DRY means depends on the values of the different selfmade sensors
{
  int val = 0;                         // For the interpretation of the different values from the sensors

  switch (sensor)
  {
  case 0:                              // plant 1

    val = 340; // 335;
    break;

  case 2:                              // plant 2

      val = 340; // 335;
    break;

  case 4:                              // plant 3

    val = 340; // 335;
    break;
  }  

  return(val);  
}

int MOISTVAL(int sensor)
{
  int val = 0;                         // For the interpretation of the different values from the sensors

  switch (sensor)
  {
  case 0:                              // plant 1

    val = 340;
    break;

  case 2:                              // plant 2

    val = 340; 
    break;

  case 4:                              // plant 3

    val = 340; 
    break;
  }  

  return(val);  
}

int TOOMOISTVAL(int sensor)              // What TOOMOIST means depends on the values of the different selfmade sensors
{
  int val = 0;                           // For the interpretation of the different values from the sensors

  switch (sensor)
  {
  case 0:                              // plant 1

    val = 400; // 360
    break;

  case 2:                              // plant 2

    val = 400; // 360;
    break;

  case 4:                              // plant 3

    val = 400; // 360;
    break;
  }  

  return(val);  
}

long MinPumpTimeLag()
{
  int interval; 
  interval = (MoistMeasureInterval(1) / 60);  
  // Minimum Time-Lag in Minutes that is between two Self-Watering-Actions 
  // Which String in the string table should be taken? Refers to the ProgMem Code above
  return(interval);
}  

int pumpstate_int(boolean pumping_needed)
{
  // if the pumping is needed you get a true and convert it to a 1 
  // if the pumping is not needet you get a false and convert it to a 0
  int temp_int; // temporary int for conversion
  if (pumping_needed)
  {
    temp_int = 1; 
  }
  else
  {
    temp_int = 0; 
  }
  return(temp_int);
}

int PumpDuration()                                    // Seconds of having current to the waterpump
{
  int sec;  // Depending on the month of the year the amount of water should be more or less
  int ml;
  int mon = month(now());

  if (!isNPT)                                         // If we don't know the right time due to some error with the NPT time Server
                                                      // then we don't know what month is right now. That could lead e.g. to an overwatering in winter
                                                      // that's why we set the wateringamount quite low, if we have no clue what time it is
  {
    ml = 400; 
  }
  else
  {
    switch (mon)
    {
    case 1:
    case 2:
    case 3:
      ml = 400;
      break; 
    case 4: 
    case 5: 
      ml = 500; 
      break;
    case 6: 
    case 7: 
    case 8: 
      ml = 700;
      break;
    case 9:    
    case 10:
    case 11: 
    case 12: 
      ml = 400; 
      break;
    }
  }

  sec = ml/mlPerSecond();                       // 540 ml with 6 ml per second equals 90 seconds of current                                                   
  return(sec);                                                        
}

int mlPerSecond()
{
  // How many milliliters water are pumped in one second
  // depends on the pump, the diameter of the tube and on the current, has to be measured first
  return(25);
}

int percent(int val, int counter)
{

  // Wenn die Zahl Kleiner ist als DRY, dann soll die Prozentzahl kleiner sein als 25% 
  // Wenn die Zahl kleiner ist als MOIST und größer als DRY, dann sollen die Prozentwerte zwischen 26% und 40% liegen
  // Wenn die Zahl größer ist als MOIST und kleiner als TOOMOIST, dann sollen die Prozentwerte zwischen 41% und 75% liegen
  // Wenn die Zahl größer ist als TOOMOIST, dann sollen die Werte ab 75% umgerechnet werden

  int dry = DRYVAL(counter); 
  int moist = MOISTVAL(counter);
  int toomoist = TOOMOISTVAL(counter); 
  int zero = dry - (dry * 0.02);  
  int hundred = toomoist + (toomoist * 0.02);

  if (debug)
  {
    RedFly.disable();
    Serial.println("Prozentumrechnung");
    Serial.print("counter: ");
    Serial.println(counter);    
    Serial.print("Input: "); 
    Serial.print(val);
    Serial.println(" AI");
    RedFly.enable();
  }

  val = constrain(val, zero, hundred);

  if (val <= dry)                             // Bereich zwischen 0 - 25%
  {
    val = map(val, zero, dry, 0, 25);    
  } 
  else if ((val > dry) && (val <= moist))     // Bereich zwischen 26 und 40%
  {
    val = map(val, dry + 1, moist, 26, 40);
  }
  else if ((val > moist) && (val <= toomoist)) // Bereich zwischen 41 und 75%
  {
    val = map(val, moist + 1 , toomoist, 41, 75); 
  }
  else if (val > toomoist)                     // Bereich von 75 - 100%
  { 
    val = map(val, toomoist + 1, hundred, 76, 100);  
  }

  if (debug)
  {
    RedFly.disable();
    Serial.print("dry: ");
    Serial.println(dry);
    Serial.print("toomoist: ");
    Serial.println(toomoist);
    Serial.print("zero: ");
    Serial.println(zero);
    Serial.print("hundred: ");
    Serial.println(hundred);
    Serial.print("Output: "); 
    Serial.print(val);
    Serial.println("%");
    RedFly.enable();
  }

  return(val);
}
