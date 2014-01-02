
// Definitions of variables +++++++++++++++++++++++++++++++++++++++++++++++++++

boolean WiFlyConnection = false; // stores if the Connection via WiFi is established

uint8_t internetconnectionerror; // Stores the state of the internet connection. it equals 0 if the connection is ok

long lastMoistTime = (-1 * (MoistMeasureInterval(1) / MOIST_SAMPLES())); 
// stores the most recent moisture reading

int pc = 0;    // Abrv. "pc" ... plantcounter 
// Counts the number of plants in for loops 

int moistValues[10 * numberofprobes];  // stores the moisture values
// the array is ab big as the number of moisture sensors
// multiplied with the number of samples for the average

int mn[numberofprobes];   // Abrv. "mn" ... messagenumber

int state[numberofprobes];// stores the last state of Twitter message
// tracks the state to avoid erroneously repeated tweets  

boolean pumpstate[numberofprobes]; // If pumping ist needed

boolean isNPT = false; // Stores if we set the time of the arduino with a real time from a NPT time server
// We need this to indicate the NPT status with a LED

int lastWaterVal[numberofprobes]; 
// storage for watering detection value

long LastWateringDetection[numberofprobes]; 
// When we last time recognized, that there was a watering event
// Store a negative value to avoid a wrong status on the website within the first 3 hours after starting the arduino

static int counter[numberofprobes];
// init static counter, needed to define if the moisture average should be calculated of a value higher than the counter

long LastPumpingTime[numberofprobes];
// stores the time of the last "Self-Watering-Action" to avoid 
// to many Self-Watering-Actions in a too short period of time 
// there should be enough time left for the water to sink into the soil

long pumpwarningtime[numberofprobes]; 
// saves the now from the time when there was a warning due to dry soil

int lastMoistAvg[numberofprobes];
// storage for moisture value

//initialize the server library with the port 
//you want to use (port 80 is default for HTTP)
RedFlyServer server(24);  

// store the time on which the last check of the WiFi connection took place
// long lastwifichecktime = now() + wifichecktime;

boolean manuell[numberofprobes];
// states if the pumping was initated manuelly over the website or automaticaly by arduino

boolean beginpumping[numberofprobes]; 
// if true the pumping will start imediately

int PumpSeconds[numberofprobes];
// Duration in Seconds to have current to the pump                              

static char buffer[100];            // make sure this is large enough for the largest string it must hold
// Recall the Strings via: 
// strcpy_P(buffer, (char*)pgm_read_word(&(messagetext[i]))); // Necessary casts and dereferencing, just copy. 

long sumwater = 0;        // stores the sum of water given to all the plants in milliliter to send it to cosm                    

long IsWatertankcheck = 0;// stores the time when the watertank watercheck took place
boolean IsWatertankwarnLED = false;
// stores if the watertankwarnLED should be switched on or off    
boolean watertankstartcheck = true; 

boolean IsSDCard  = false;   // stores if the SD Card is available


