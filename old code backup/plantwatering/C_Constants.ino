
// Switch functions on and off
boolean debug = 0;                     // Set if Debug is true - this enables more Serial Printouts within the sketch

// Declaration of Network Parameters ******************************************

byte ip[]        = { 
  192, 168, 178, 30 };   // ip from WiFly shield (client/Webserver)
byte netmask[]   = { 
  255, 255, 255,  0 };   // local netmask
byte gateway[]   = { 
  192, 168, 178,  1 };   // ip from local gateway/router
byte dnsserver[] = { 
  192, 168, 178,  1 };   // ip from local dns server
byte mac[]       = { 
  0xDE, 0xAD, 0xBC, 0xAF, 0xFE, 0xED };
// MAC address
byte domainserver[]    = { 
  192, 168, 178, 24 }; //{  85, 13,145,242 }; //ip from www.watterott.net (server)

#define Network "WLAN-Kabel"         
#define NetworkPW "1604644462468036" 

// Host of nanismus website to send data to
#define HOSTNAME "192.168.178.24" //"nanismus.no-ip.org"  // //host

// state
#define WATER_OVERFLOW 5               // There is water on the ground of the pot 
#define NO_WATER_OVERFLOW 4            // There is no Water on the ground                                         
#define URGENT_SENT 3                  // Soil is critically dry
#define SOON_DRY_SENT 2                // Soil is not moist anymore
#define MOISTURE_OK 1                  // Soil is moist
#define TOOWET 0                       // Soil is too wet 

#define PumpingNeeded true             // flag that a water-pump-action is needed
#define PumpingNotNeeded false         // flag that a water-pump-action is not needed

#define WATERING_CRITERIA 20           // minimum change in value that indicates watering

#define MOIST 40                       // #define MOIST 320 // 480
// Percent Value - this value is a reference for the watering and moisture checks
// minimum level of satisfactory moisture, ab wann ist die Erde nicht mehr ausreichend "feucht"?
#define TOOMOIST 90                    // Pecent when the soil is too wet                                         
#define SOAKED 40                      // #define SOAKED 330 // 490
// Percent Value - this value is a reference for the watering and moisture checks 
// minimum desired level after watering
#define DRY 25                         // Percent when the soil is dry

// int wifichecktime = 30 * 60; // every 30 minutes there should be a WiFi Check
