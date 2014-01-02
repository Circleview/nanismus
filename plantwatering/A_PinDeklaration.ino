// ****************************************************************************

// Declare the number of plants, which are measured +++++++++++++++++++++++++++
  #define numberofprobes 2   // How many moisture-sensors                     +
                             // are connected to the board?                   +
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++


// PIN Declaration ############################################################

// PINs D4 - D13

// Declaration for the Waterpump
// 4
  int PumpBasepin[numberofprobes] = 
  { 
    26, 
    0
  };                        // which Digital Pin is connectet to the Basepin of the transistor that switches the waterpump?
// 5
  #define tanksensorpin 30   // Sensor to measure if there is still water in the watertank

// 6
  #define LogLED 44           // LED that lights up when data is logged on the SD Card
  #define PumpWarningLED 42  // Digital Output pin that lights Up an LED that indicates a upcoming selfpumping action
// 7 // 8 // 9 // 10 // 11 // 12

// 10
  const int chipSelect = 10; // SS (Slave Select) - the pin on each device that the master can use to enable and disable specific devices. 
                             // Needed for writing on the SD Card
  
                            // Declarations for the moisture measurement
// see int sensorpowerpin() 

// 11
// 12
// 13
  #define WiFiStatusPin 40  // Pin to show if the WiFi Connection is established or that a request to the webserver was sent
                            // LED Indications
  
// PINs D14 - D53 Arduino MEGA only ! 
// 14
  #define watertankwarnLED 46  // LED that indicates dryness in the watertank
// 15 
  #define CosmComLED 44        // LED that indicates Cosm Communication Status
// 16 
//  #define TwitComLED 32      // LED that indicates Twitter Communication Status
// 17
  #define MoistTimeLED1 52     // LED to indicate how long until measure
// 18
  #define MoistTimeLED2 50     // LED to indicate how long until measure
// 19
  #define MoistTimeLED3 48     // LED to indicate how long until measure
// 20
  #define NPTLED 38             // LED to indicate if we have a valid NPT time
  
// PINs A0 - A5
// 0
                            // Declarations for the temperature measurement
  #define temppin 8         // analog input pin to measuere the temperature
// 1 // 2 // 3 

// see moistmeasurepin();

// 4

  #define watertankpin  15   // Analog Input Pin Moist Sensor 7 for the watertank
// ############################################################################

