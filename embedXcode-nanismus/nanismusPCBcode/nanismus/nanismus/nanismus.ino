//
// nanismusPCBcode
//
// Code to run on the Arduino PCB
// Developed with [embedXcode](http://embedXcode.weebly.com)
//
// Author 		Stefan Willuda
// 				Stefan Willuda
//
// Date			01.08.16 14:31
// Version		0.68.0
//
// Copyright	© Stefan Willuda, 2016
// Licence		Creative Commons - Attribution - ShareAlike 3.0
//
// See          ReadMe.txt for references

/* current result of arduino IDE compiling
Der Sketch verwendet 15.258 Bytes (47%) des Programmspeicherplatzes. Das Maximum sind 32.256 Bytes.
Globale Variablen verwenden 1.245 Bytes (60%) des dynamischen Speichers, 803 Bytes für lokale Variablen verbleiben. Das Maximum sind 2.048 Bytes.
 */

// PIN Declaration ################################################################################################################

// Declaration of the Pins for the RedFly WiFi Shield

// D0, D1, D2, D3 // https://github.com/watterott/Arduino-Libs/tree/master/RedFly


// Declarations for status indicators LEDs

// D5
#define SoilMeasureVoltagePin 5 // Pin to start the moisture measurement

// D7
#define SoilDryWarningLED 7 // LED that indicated dryness in the soil

// D11
#define PumpVoltagePin 11 // Pin to start the waterpump to water the soil

// D13
#define CurrentlyMoistureMeasurementIndicatorLED 13 // LED that indicates that right now a moisture measurement is performed

// A4
#define MoistureMeasurementAnalogInputPin 4


// Include Libraries ###############################################################################################################


// Include Arduino library to allow autocomplete syntax in Xcode
#include <Arduino.h>

// Include libraries to allow WiFi Connection with the RedFly Wifi shield
// https://github.com/watterott/Arduino-Libs/tree/master/RedFly
#include <RedFly.h>
#include <RedFlyClient.h>
#include <RedFlyServer.h>

// library to check the Currently free Memory from http://www.arduino.cc/playground/Code/AvailableMemory
// Download the Library from https://github.com/maniacbug/MemoryFree
#include <MemoryFree.h>


// Constants and variables ##########################################################################################################


// define if this is a test build or a production build
#define istest 1 // 0 == production ; 1 == test
/* the interpretation of this value will currently lead to a different http POST statement
 * which writes differently attributed data to the database
 */

// Calculate or store constants that are uses several times in the codebase

// When we apply voltage to the moisture sensor it takes a short time for the sensor to adjust
unsigned long SoilMoistureMeasurementWaitDuration = 1000; // milliseconds 1.000 milliseconds = 1 second


/* Every how many milliseconds are we going to perform a moisture measurement?
 currently I use millis() because I don't need the exact time and millis() is easier to simulate than now()
 30 minutes * 60 seconds * 1000 milliseconds
 ; // 30 * 60 * 1000; // milliseconds 1.000 milliseconds = 1 second
 */
unsigned long MoistMeasureInterval[2] = {1800000, 120000};

/* Every how many milliseconds are we going to perform a call to the web server to check the watering initiation status?
 currently I use millis() because I don't need the exact time and millis() is easier to simulate than now()
 15 * 60 * 1000; // milliseconds 1.000 milliseconds = 1 second
*/
unsigned long WateringInitiationCallInterval[2] = {900000, 120000}; // 0 == production 1 == test

/* if we initiate a manual watering action over the website we switch on the waterpump. After we watered the plant we try to reset the watering status in the web server database from "initiate" to "reset" to make watering over the website possible again and to avoid an immediate watering action after the first watering action took place. 
 * however what happens if we try to reset the status on the web server but are not able to because of a problem with the connection?
 * technically after one loop cycle the manual watering check would think that a new manual watering would be intended. To avoid that we use a small timer that prevents immediate watering actions
 */
unsigned long manualWateringInitiationInterval[2] = {3600000, 120000}; // milliseconds // 0 == production 1 == test
long lastManualWateringActionTime = -1 * manualWateringInitiationInterval[istest]; // the first manual watering should be possible immediately after program start


// store the most recent time when the moisture measurement took place
/* When we start the first iteration of the code loop than we use the current time minus one interval
 * which leads to an immediate measurement of the soil when the board is connected to the power supply
 */
long lastMoistMeasureTime = -1 * MoistMeasureInterval[istest];

// store the most recent time when call of the watering initiation status took place
/* When we start the first iteration of the code loop than we use the current time minus one interval
 * which leads to an immediate call when the board is connected to the power supply
 */
long lastWateringInitiationCallTime = -1 * WateringInitiationCallInterval[istest];


// define the variable that stores the data input that is sent by the soil moisture sensor for reuseage
int MoistureMeasurementResultAnalogInput;


// Define the thresholds of different analog input values to decide if they can be considered as dry, moist and so on...
/* This array shall be extended later if we want a more granular distinction between dry, moist, toomoist soil
 * How to calculate the actual voltage input is well described at https://www.arduino.cc/en/Tutorial/ReadAnalogVoltage
 */
/* to define the thresholds for the analogInput value of the moisture sensor
 * I've measured the voltage input in a glass of water, which I consider to be wet and it was 0.7 volts input
 * Considering the formular: voltage= sensorValue * (5.0 / 1023.0)
 * I've measured different states of moisture to collect example data
 * If the moisture sensor sticks to really dry soil = 10% wet = 1,0 V
 *      0% moist = 1,00 V = 205 analogInput
 * If the moisture sensor sticks in soil that is considered 40% wet = 1,66 V
 *		40% moist = 1,66 V = 340 analogInput
 * If the moisture sensor sticks in soil that has been watered right now = 2,16 V
 *		80% moist = 2,16 V = 442 analogInput
 * If the moisture sensor sticks in soil that is still water wet after a watering = 2,35 V
 *		100% moist = 2,35 V = 481 analogInput
 * If the moisture sensor sticks in a glass of water I can measure with a multimeter 2.6 Volts input
 *		water moist = 2.6 V = 532 analogInput

 * This definition was adjusted on the 15th August to
 
 * Used thresholds
 
 * "zero" : 0% : 310
 * "urgently dry" : 20% : 360
 * "moist" : 40% : 400
 * "very moist" : 80% : 442
 * "hundred" : 100% : 481
 */
/* line 121
 *     ThresholdsForAnalogInputValues[] = {zero, urgently dry, moist, very moist, hundred};
 *     ThresholdsForAnalogInputValues[] = {0%  , 20%         , 40%  , 80%       , 100%   };
 *     ThresholdsForAnalogInputValues[] = {0   , 1           , 2    , 3         , 4      };
 */
   int ThresholdsForAnalogInputValues[] = {310 , 360         , 400  , 442       , 481    };

// Store the indicator of the soil moisture
char * MoistureIndicator;

/* Store the initiation status of the webserver database
 * I do this because I cannot be 100% certain if the arduino and the database are in sync
 * when processing the initation status. 
 * To store this value shall prevent different initiation status 
 * status 1 = "initate" - this indicates a watering attempt over the website and will start the waterpump
 * status 2 = "reset" - this indicates that a watering action prevously took place - nothing will happen on the arduino when we have a "reset" state, but it allows a "watering button" to appear in the website if the soil of the plant is too dry
 */
const char * manualWateringInitiationStatus = "reset"; // the default value is reset so no watering takes place

// Debug Functions ####################################################################################################################


//debug output functions (9600 Baud, 8N2)
//Leonardo boards use USB for communication, so we dont need to disable the RedFly
void debugout(char *s)
{

    RedFly.disable();
    Serial.print(s);
    RedFly.enable();
    
}

/* By using the RedFly WiFi Shield we have communications conflicts with 
 * serial communication. That is why we have do shortly disable the RedFly Shield when 
 * doing a serial print
 */
void debugoutln(char *s)
{
#if defined(__AVR_ATmega32U4__)
    Serial.println(s);
#else
    RedFly.disable();
    Serial.println(s);
    RedFly.enable();
#endif
}

// debug output function for unsigned long values
void debugoutlnUnsignedLong(char *s, unsigned long value){
#if defined(__AVR_ATmega32U4__)
    Serial.print(s);
    Serial.print(": ");
    Serial.println(value);
#else
    RedFly.disable();
    Serial.print(s);
    Serial.print(": ");
    Serial.println(value);
    RedFly.enable();
#endif
}

// debug output function for char * values
void debugoutlnInt(char *s, int value){
#if defined(__AVR_ATmega32U4__)
    Serial.print(s);
    Serial.print(": ");
    Serial.println(value);
#else
    RedFly.disable();
    Serial.print(s);
    Serial.print(": ");
    Serial.println(value);
    RedFly.enable();
#endif
}

// debug output function for const char * values
void debugoutlnConstChar(char *s, const char * value){
#if defined(__AVR_ATmega32U4__)
    Serial.print(s);
    Serial.print(": ");
    Serial.println(value);
#else
    RedFly.disable();
    Serial.print(s);
    Serial.print(": ");
    Serial.println(value);
    RedFly.enable();
#endif
}

// debug output function for char * values
void debugoutlnChar(char *s, char * value){
#if defined(__AVR_ATmega32U4__)
    Serial.print(s);
    Serial.print(": ");
    Serial.println(value);
#else
    RedFly.disable();
    Serial.print(s);
    Serial.print(": ");
    Serial.println(value);
    RedFly.enable();
#endif
}


int currentMemoryFree()
{
    int mem = freeMemory();
    return (mem);

}

void debugoutlnMemory(){
    
    char *s = "Current Memory";
    int value = currentMemoryFree();
    
#if defined(__AVR_ATmega32U4__)
    Serial.print(s);
    Serial.print(": ");
    Serial.println(value);
#else
    RedFly.disable();
    Serial.print(s);
    Serial.print(": ");
    Serial.println(value);
    RedFly.enable();
#endif
    
}


// Establish a WiFi Connection using the RedFly WiFi Shield ##########################################################################


void EstablishWifiConnectionWithRedFlyShield()
{
    
    // initialize the WiFi module on the shield
    
    // Serial debug info
    // debugoutln("EstablishWiFiConnectionWithRedFlyShield()");
    
    uint8_t ret;
    
    //init the WiFi module on the shield
    // ret = RedFly.init(br, pwr) //br=9600|19200|38400|57600|115200|200000|230400, pwr=LOW_POWER|MED_POWER|HIGH_POWER
    // ret = RedFly.init(pwr) //9600 baud, pwr=LOW_POWER|MED_POWER|HIGH_POWER
    // ret = RedFly.init() //9600 baud, HIGH_POWER
    
    // ret = RedFly.init();
    
    /* sometimes the connection is not established on the first try, thats why I need to try more than once
     * but not more than maxcounter times, because this would make the whole code in the loop stop
     */
    
    int counter, maxcounter;
    counter = 0;
    maxcounter = 50;
    
    while (ret && counter < maxcounter) {
        
        /* In high power transmit mode an external power supply is recommended, because in some cases the USB port has not enough power
         * https://github.com/watterott/Arduino-Libs/tree/master/RedFly
         */
        // ret = RedFly.init(9600, LOW_POWER);
        ret = RedFly.init();

        // debugoutln("RedFly.init ERROR"); //there are problems with the communication between the Arduino and the RedFly

        counter++;
        
    }
    
    if(ret){
        
        // Serial debug info
        // debugoutln("RedFly.init ERROR"); //there are problems with the communication between the Arduino and the RedFly
        
    }
    else {
        
        // scan for wireless networks (must be run before join command)
        RedFly.scan();
        
        
        //join network
        // ret = join("wlan-ssid", "wlan-passw", INFRASTRUCTURE or IBSS_JOINER or IBSS_CREATOR, chn, authmode) //join infrastructure or ad-hoc network, or create ad-hoc network
        // ret = join("wlan-ssid", "wlan-passw", IBSS_CREATOR, chn) //create ad-hoc network with password, channel 1-14
        // ret = join("wlan-ssid", IBSS_CREATOR, chn) //create ad-hoc network, channel 1-14
        // ret = join("wlan-ssid", "wlan-passw", INFRASTRUCTURE or IBSS_JOINER) //join infrastructure or ad-hoc network with password
        // ret = join("wlan-ssid", INFRASTRUCTURE or IBSS_JOINER) //join infrastructure or ad-hoc network
        // ret = join("wlan-ssid", "wlan-passw") //join infrastructure network with password
        // ret = join("wlan-ssid") //join infrastructure network
        
        
        #define Network "WLAN-Kabel"
        #define NetworkPW "1604644462468036"
 
        ret = RedFly.join(Network, NetworkPW, INFRASTRUCTURE);
        
        /* sometimes the connection is not established on the first try, thats why I need to try more than once
         * but not more than maxcounter times, because this would make the whole code in the loop stop
         */
        
        // reset the counter
        counter = 0;
        
        while (ret && counter < maxcounter) {
            
            ret = RedFly.join(Network, NetworkPW, INFRASTRUCTURE);
            
            // debugoutln("RedFly.join ERROR");
            
            counter++;
            
        }
        
        if(ret){
            
            //debugoutln("RedFly.join ERROR");
            
        }
        else {
           
            /*
            byte ip[]        = { 192, 168, 178, 34 }; //ip from shield (client)
            byte netmask[]   = { 255, 255, 255,  0 }; //netmask
            byte gateway[]   = { 192, 168, 178,  1 }; //ip from gateway/router
            byte dnsserver[] = { 192, 168, 178,  1 }; //ip from dns server
            byte server[]    = {   0,  0,  0,  0 }; //{  85, 13,145,242 }; //ip from www.watterott.net (server)
            
             */
            
            //set ip config
            // ret = RedFly.begin(); //DHCP
            // ret = RedFly.begin(1 or 2); //1=DHCP or 2=Auto-IP
            // ret = RedFly.begin(ip);
            // ret = RedFly.begin(ip, dnsserver);
            // ret = RedFly.begin(ip, dnsserver, gateway);
            // ret = RedFly.begin(ip, dnsserver, gateway, netmask);
            
            ret = RedFly.begin();

            /* sometimes the connection is not established on the first try, thats why I need to try more than once
             * but not more than 20 times, because this would make the whole code in the loop stop
             */
            
            counter = 0;
            
            while (ret && counter < maxcounter) {
                
                ret = RedFly.begin();
                
                // debugoutln("RedFly.begin ERROR");
                
                counter++;
                
            }
            
            if(ret){
                
                // The connection was not established this time, so disconnect
                // RedFly.disconnect();
                
            }
            else {
                
                //RedFly.getlocalip(ip);       // receive shield IP in case of DHCP/Auto-IP
                
                // server.begin();
                // debugoutln("WiFi Shield connected");
                
            }
        }
    }
}


// Send out the measured data to a website ###########################################################################################


/* Sends different values to a http webserver on which a PHP script waits for the data
 * to store them in a MySQL database
 
 * Based on Watterott sample
 * Web Client
 * This sketch connects to a website using a RedFly-Shield.
 
 * Inspired by
 * http://jleopold.de/wp-content/uploads/2011/03/ArduinoDatenLogger.txt
 */


// Check if we receive a HTTP response for our POST request and interprete this response
// return true if the data transmission was successful, else return false
const char * resultOfHttpPostRequest(int websiteSelector, RedFlyClient client){
    
    // Serial debug info
    // debugoutln("SuccessOfHttpPostRequest - Start");
    
    // Serial debug info
    // debugoutlnMemory();
    
    // interprete the result of the http response to store the return value of this function
    const char * tempReturnValue;
    
    /* To be sure that the data transmission was successful catch the server response and evaluate the result
     * oriented on the RedFly Example WebClient.ino
     * I did not want to use the loop() to evaluate the server response
     * That is why I chose to use a while() with a timeout
     * If there is no response within the timeout, I have to asume that the server is not available
     */
    
    // Timekeeper to check if the Timeout is reached
    unsigned long currentMillis = millis();
    unsigned long startTime = currentMillis;
    
    // give it a TimeoutTime milliseconds to receive an answer from the webserver
    unsigned long TimeoutTime = 60000; // milliseconds
    
    /* The Webserver is going to answer with a simple HTTP response
     * We are going to fetch the answer in a buffer
     * and then we are going to check the server HTTP response if we get a "success" response or something else
     */
    
    // declarations to process the answer of the webserver
    char data[300];  //receive buffer, usually the answer is not larger than 250 chars
    unsigned int len=0; // receive buffer length
    
    /* We are going to seach in the HTTP response for a phase that indicates transmission success
     * Therefore we are looking for a pointer, which indicates the position in the array that holds
     * the match
     */
    // Size of the Pointer, is greater that 0 in every case we receive data from the webserver
    int PointerSize = 0;
    
    // indicator that the webserver is transmitting a response
    int c;
    
    // Serial log info
    // debugoutln("start waiting");

    
    client.print("Connection: close\r\n\r\n");
    
    /* Start the while()
     * As long as we don't get an response or the TimeoutTime is not exeeded, we keep waiting for a response
     * PointerSize is larger than 0 whenever we receive a HTTP response
     * Even if we receive a HTTP response that does not indicate transmission success, we stop the while()
     * because we only want to interprete the answer. It is not necessary to waste time
     */
    while ((currentMillis - startTime <= TimeoutTime) && (PointerSize == 0)) {
        
        
        /*if there are incoming bytes available
         * from the server then read them
         */
        if(client.available()) {
            do
            {
                c = client.read();
                if((c != -1) && (len < (sizeof(data)-1)))
                {
                    // collect the whole HTTP response in the data array
                    data[len++] = c;
                }
            }while(c != -1);
        }
        
        //if the server's disconnected, stop the client and evaluate the received data
        if(len && !client.connected()) {
            
            client.stop();
            RedFly.disconnect();
            
            data[len] = 0;
            // Serial log info
            debugout(data);
            
            /* Now that we received the HTTP response it is time to interprete the received data
             * The webserver we address is responding wether a "success", a "failure" or something else
             * We need to search in the HTTP response for that "success" or "failure" statement
             * It is not enough to receive a "200 OK" response, because this does only indicate, if we
             * have been able to connect to the webserver. Even if we receive a "failure" response
             * this will come with a "200 OK" response. That's why we have to dive a little bit deeper
             * and analyse the resonse message
             *
             * The idea is to find a specific substrting in a string with char datatype
             * this is described here http://forum.arduino.cc/index.php?topic=394718.0
             *
             * With that in mind we can search for any sub char array within the HTTP response array.
             */
            
            /* Answer, we need to receive in order to know that the submission was successful
             * This string is determinded in the "valueget.php" file on the webserver.
             */
            
            // how many different strings do we know?
            int numberOfDifferentStrings = 3;
            
            const char * expectedReturnStrings[numberOfDifferentStrings][8] = {{"success"}, {"initiat"}, {"reseted"}};
            
            /* run through a loop to find out if the webserver does respond one of the expected return strings
             * if so, fine. Store this string as the return value then. 
             * not? This might mean that there is a problem with the webser communication, store this as well. 
             */
            
            // Pointer that locates the position of the searched substring (char array) within a larger char array
            char * Pointer;
            
            int currentCounter = 0; // counter to know the number of the loop
            int maxCounter = numberOfDifferentStrings; // number of atempts in the loop
            
            do {
                
                
                // function to find the pointer - is empty if there is no match
                Pointer = strstr(data, expectedReturnStrings[currentCounter][0]);
                
                // To get out of the while() I have to check if we received a valid response from the webserver
                PointerSize = sizeof(Pointer);
                
                
                // We locate the position of the substring within the larger
                int PointerPosition;
                
                // subtract the starting pointer of Haystack from the pointer returned by strstr()
                PointerPosition = (&Pointer[0] - &data[0]);
                
                // Serial debug info
                // debugoutlnInt("Pointer Position", PointerPosition);
                
                // The PointerPosition in the array is always positive if we find the substring in the larger dara array
                if (PointerPosition >= 0) {
                    
                    // transmission success

                    // store the result to use it when the function is done
                    tempReturnValue = expectedReturnStrings[currentCounter][0];
                    
                    // Serial debug info
                    debugoutlnConstChar("tempReturnValue", tempReturnValue);
                    
                }
                else if (PointerPosition < 0) {
                    
                    // Serial debug info
                    debugoutln("");
                    debugoutln("failure");
                    
                    /* This means that we have been able to connect to the web server and POST our request
                     * but the response was not a success message.
                     * The reason for that might be, that the key to perform the database insert was wrong
                     * or that the script in valueget.php does not support the request URL
                     */
                    
                    // store the result to use it when the function is done
                    tempReturnValue = "failure";
                    
                }
                else {
                    
                    // Serial log info
                    // debugoutln("something is wrong here");
                    
                    /* Actually this else event should not occur, because we only get here if we don't receive a
                     * response. But I don't want to let special events to be unhandled.
                     */
                    
                    // store the result to use it when the function is done
                    tempReturnValue = "failure";
                    
                }
                
                // increase the counter by one
                currentCounter++;

            } while (tempReturnValue == "failure" && currentCounter < maxCounter);
            
            // const char * successString = successStringForAWebsiteSelector(websiteSelector);
            
            
            // Serial debug info
            // debugoutlnMemory();
            
            len = 0;
        }
        
        currentMillis = millis();
    }
    
    // serial log info
    // debugoutln("stopped waiting");
    
    // flush the client connection
    // https://www.arduino.cc/en/Reference/WiFiClientFlush
    client.flush();
    
    // Serial debug info
    // debugoutln("SuccessOfHttpPostRequest - End");
    
    // Serial debug info
    // debugoutlnMemory();
    
    return (tempReturnValue);

}


/* currently I am calling different URLs via http POST request
 * here I decide which website to call, based on an "websiteSelector" indicator
 */
const char * switchBetweenDifferentWebsiteURLs(int websiteSelector) {
    
    // Serial debug info
    // debugoutln("switchBetweenDifferentWebsiteURLs");
    
    const char * url; // what URL is related to which websiteSelector?
    
    // HTTP Methods: GET vs. POST http://www.w3schools.com/tags/ref_httpmethods.asp
    
    switch (websiteSelector) {
        case 1:
        
        // This is the web server script that logs the percentage value of the current moisture to display it on the index page
        // Change from GET to POST, I've read something about security issues with GET
        url = "GET /valueget.php";
        break;
        
        case 2:
        
        // This is the web server script that calls for the current watering initiation status (initate, reset)
        url = "GET /call_initiation.php";
        break;
        
        case 3:
        
        // This is the web server script that resets the manual watering initiation
        url = "GET /watering.php";
        break;
        
        
        default:
        break;
    }
    
    // Serial debug info
    // debugoutlnChar("url", url);
    return (url);
}


/* This function puts together all the different parts of the POST request
 * that is sent to the webserver
 */
char * assembleThePostRequest(long value, int websiteSelector) {
    
    // Serial debug info
    // debugoutln("assembleThePostRequest()");
    
    // Host IP of web server. We use the static IP and avoid DNS resolution because we know the static IP of the server
#define HOSTNAME "192.168.178.23" // "173.194.219.94" // google.de
 
    // one is the name of the sensor (plant)
    const char * sensor_string;
    
    // we fill in different datatable values based on wether we have a test build or a production build
    if (istest == 1){
        sensor_string = "Test";
    }
    else if (istest == 0){
        sensor_string = "Banane";
    }
    
    // one is the kind of value we are transmitting
    const char * type_string;
    type_string = "Prozentfeuchte";
    
    
    //String GetRequest;
    // http://miscsolutions.wordpress.com/2011/10/16/five-things-i-never-use-in-arduino-projects/
    
    // Define the different values of the POST request
    
    char * GetRequest;
    const char * get1;
    
    // Decide which website / php script you want to call
    get1 = switchBetweenDifferentWebsiteURLs(websiteSelector);
    
    
    const char * get2 = "?name=";
    const char * get3 = "&type=";
    const char * get4 = "&value=";
    const char * get5 = "&key=c3781633f1fb1ddca77c9038d4994345";//c3781633f1fb1ddca77c9038d4994345
    const char * get6 = " HTTP/1.1\r\nHost: ";
    const char * get7 = "\r\n";
    
    // transform the sensor value into a char to fit it in the POST request
    char * value_char;
    value_char = (char*) calloc(5, sizeof(char));
    itoa(value, value_char, 10);
    
    // allocate memory for the POST request
    GetRequest = (char*) calloc(strlen(get1) + strlen(get2) + strlen(sensor_string)  + strlen(get3) + strlen(type_string) + strlen(get4)
                                + strlen(value_char) + strlen(get5) + strlen(get6) + strlen(HOSTNAME) + strlen(get7) + 1, sizeof(char));
    
    // assemble the GET Request
    strcat(GetRequest, get1);
    strcat(GetRequest, get2);
    strcat(GetRequest, sensor_string);
    strcat(GetRequest, get3);
    strcat(GetRequest, type_string);
    strcat(GetRequest, get4);
    strcat(GetRequest, value_char);
    strcat(GetRequest, get5);
    strcat(GetRequest, get6);
    strcat(GetRequest, HOSTNAME);
    strcat(GetRequest, get7);
    
    // free the allocated string memory
    free(value_char);
    // free(GetRequest);
    
    return (GetRequest);
    
}

const char * successStringForAWebsiteSelector(int websiteSelector){
    
    // Serial debug info
    // debugoutln("successStringForAWebsiteSelector - Start");
    
    // Serial debug info
    // debugoutlnMemory();
    
    const char * successString; // what URL is related to which websiteSelector?
    
    switch (websiteSelector) {
        case 1:
        case 3:
            
            // This is the web server script that logs the percentage value of the current moisture to display it on the index page
            // /valueget.php
            successString = "transmission success";
            break;
            
        case 2:
            
            // This is the web server script that calls for the current watering initiation status (initate, reset)
            // /call_watering_initiation.php
            successString = "initate";
            break;
            
        default:
            break;
    }
    
    // Serial debug info
    // debugoutlnChar("successString", successString);
    
    // Serial debug info
    // debugoutlnInt("Size of successString", sizeof(successString));
    
    // Serial debug info
    // debugoutln("successStringForAWebsiteSelector - End");
    
    // Serial debug info
    // debugoutlnMemory();
    
    return (successString);
    
}


// Start the actual moisture measurement by calling data from the moisture sensor
int CurrentMoistureAnalogInputValue(){
    
    // idicate with an LED that a measurement is currently performed
    // currently swiched off because I fear it consumes too much voltage
    // digitalWrite(CurrentlyMoistureMeasurementIndicatorLED, HIGH);
    
    // Serial debug info
    // debugoutln("CurrentMoistureAnalogInputValue()");
    
    // apply voltage to soil moisture sensor
    digitalWrite(SoilMeasureVoltagePin, HIGH);
    
    /* wait for a short time that the soil moisture sensor can adjust
     * I know, that I could have used delay() but for some reasons I encounterd
     * timing problems with delays longer than 900 ms
     */
    // Therefore store the current time
    unsigned long currentMillis = millis();
    
    // store the beginning of this measurement
    unsigned long firstMeasureTime = currentMillis;
    
    // Go through a loop until the duration it takes to perform a moisture measurement is over
    while (currentMillis - firstMeasureTime < SoilMoistureMeasurementWaitDuration) {
        
        currentMillis = millis();
    }
    
    // collect the data input that is sent by the soil moisture sensor and store it for reuseage
    MoistureMeasurementResultAnalogInput = analogRead(MoistureMeasurementAnalogInputPin);
    
    // switch of the voltage of the moisture sensor
    digitalWrite(SoilMeasureVoltagePin, LOW);
    
    // Serial debug info
    // debugoutlnInt("MoistureMeasurementResultAnalogInput", MoistureMeasurementResultAnalogInput);
    
    // switch off the indication LED
    // digitalWrite(CurrentlyMoistureMeasurementIndicatorLED, LOW);
    
    return (MoistureMeasurementResultAnalogInput);
    
}


// Read the Analog Input Value from the moisture sensor and interprete this value to moisture categories
char * currentMoistureInterpretation() {
    
    /* returns the interpretation of the current soil moistue as a char
     * I do this because I got confused with the different numbers of moisture indication
     */
    
    // Serial debug info
    // debugoutln("currentMoistureInterpretation()");
    
    // Measure the current moisture, that returns an anlog Input Int value
    // Interprete the analog input from the moisture sensor
    int Input = CurrentMoistureAnalogInputValue();
    
    // Define a temporary moisture indicator to return to the function
    char * TempMoistureIndicator;
    
    // Define wether a analog input value is considered dry or moist
    
    /* look line 121
     *     ThresholdsForAnalogInputValues[] = {zero, urgently dry, moist, very moist, hundred};
     *     ThresholdsForAnalogInputValues[] = {0%  , 20%         , 40%  , 80%       , 100%   };
     *     ThresholdsForAnalogInputValues[] = {0   , 1           , 2    , 3         , 4      };
     */
    // int ThresholdsForAnalogInputValues[] = {310 , 360         , 400  , 442       , 481    };
    
    // Check if the analog input value from the moisture sensor is considered to indicate an "urgently dry" soil
    if(Input <= ThresholdsForAnalogInputValues[1]){
        
        // retun that the soil is considered "urgently dry"
        TempMoistureIndicator = "urgently dry";
        
    }
    // Check if the analog input value from the moisture sensor is considered to indicate a "dry" soil
    else if(Input <= ThresholdsForAnalogInputValues[2]){
        
        // retun that the soil is considered "dry"
        TempMoistureIndicator = "dry";
        
    }
    else if (Input <= ThresholdsForAnalogInputValues[3]){
        
        // return that the soil is considered "moist"
        TempMoistureIndicator = "moist";
    }
    else {
        
        // return that the soil is considred "very moist"
        TempMoistureIndicator = "very moist";
    }
    
    // Serial debug info
    // debugoutlnChar("TempMoistureIndicator", TempMoistureIndicator);
    
    // return the value to the function
    return (TempMoistureIndicator);
    
}


// Switch on the water pump by switching the transistor
void StartTheWaterPump(){
    
    // Serial debug info
    // debugoutln("StartTheWaterPump()");
    
    /* store when the pump action started
     * check how long the self watering action is currently performed
     * check if the soil is already "moist" again
     * stop the watering immediately if the time for the watering is up or the soil is "moist" again to avoid water overflow
     */
    
    unsigned long CurrentMillis = millis(); // recurring check of the current time
    unsigned long PumpBeginningMillis = CurrentMillis; // this value serves to compare start and end time of the watering action
    unsigned long PumpDurationMillis = 20000; // water for 20 seconds. This provides 300 ml of water
    
    // Serial debug info
    // debugoutlnUnsignedLong("PumpBeginningMillis", PumpBeginningMillis);
    
    while ((CurrentMillis - PumpBeginningMillis < PumpDurationMillis) && (currentMoistureInterpretation() != "very moist")) {
        
        // Serial debug info
        // debugoutlnUnsignedLong("CurrentMillis", CurrentMillis);
        // debugoutlnChar("MoistureIndicator", MoistureIndicator);
        
        // switch on the water pump
        digitalWrite(PumpVoltagePin, HIGH);
        
        CurrentMillis = millis();
    }
    
    // Serial debug info
    // debugoutln("stop the water pump");
    
    // stop the water pump
    digitalWrite(PumpVoltagePin, LOW);
    
    // reset the moisture indicator to avoid that the pump starts again immediately after it pumped to let the water spread in the plant pot
    MoistureIndicator = "moist"; // Indicator 2 == moist
    
}


// Here we do the  Http POST request and check if the transmission was successful
const char * ResultOfHttpPostRequest(long value, int websiteSelector, RedFlyClient client, byte server[]){
    
    // Serial debug info
    // debugoutln("SuccessResultOfHttpPostRequest - Start");
    
    // Serial debug info
    // debugoutlnMemory();
    
    char * PostRequest = assembleThePostRequest(value, websiteSelector);
    
    // Serial debug info
    // debugoutlnChar("PostRequest", PostRequest);
    
    // we want to know if the transmission was successful
    const  char * tempReturnValue;
    
    // const char * successString = successStringForAWebsiteSelector(websiteSelector);
    
    // connect the client to the web server and transmit the post request
    if(client.connect(server, 80))
    {
        // Serial debug info
        // debugoutln("client.connect(server, 80) == true");
        
        //make a HTTP request
        //http://www.watterott.net/forum/topic/282
        
        // call the web server
        // Example request http://nanismus.no-ip.org/nanismus_test/valueget.php?name=Banane&type=status&value=6&key=123
        client.print(PostRequest);

        // Receive the response from the web server
        tempReturnValue = resultOfHttpPostRequest(websiteSelector, client);
        
    }
    else {
        
        // Serial Log info
        // debugoutln("server unavailable");
        
        // try to re-establish the wifi connection
        EstablishWifiConnectionWithRedFlyShield();
        
        tempReturnValue = false;
        
    }
    
    // free allocated memory
    free(PostRequest);
    
    // Serial debug info
    // debugoutln("SuccessResultOfHttpPostRequest - End");
    
    // Serial debug info
    // debugoutlnMemory();
    
    return (tempReturnValue);
    
}


/*const char * webServerInitiationStatus(boolean webServerSuccessResult, int websiteSelector){
    
    
    /* If we receive a "initiate" status from the webserver, we know, that a watering action
     * was initiated manually over the website. If we have this information, we can "manually"
     * switch on the waterpump.
 
    if (tempRequestSuccessTrue && websiteSelector == 2){
        
        /* we know that we received an "initiate" status from the call_initiation.php so we want to start the waterpump "manually" - which means by force and not due to dryness and reset the status of the webserver, to allow a new "manual" watering event
 
        // manualWateringInitiationStatus = "initiate";
        return ("initiate");
        
    }
    else if (!tempRequestSuccessTrue && websiteSelector == 2){
        
        /* we know that the status of the website is not "initiate".
         * so its rather an error or the reset statement
         * in both cases we don't want the waterpump to perform a watering action
 
        // manualWateringInitiationStatus = "reset";
        return ("reset");
    }
}*/

// define the address of the server and trigger a GET request to a webserver
/* we try to send the value to the webserver
 * if this does not work out on the first try we try it again with the same value
 * if this again does not work out, we try to re-establish the whole Wifi connection
 * unfortunately we currently cannot do more than that because we don't have access to
 * the server */
void FullHttpPostTransmission(long value, int websiteSelector){
    
    // Serial debug info
    // debugoutln("FullHttpPostTransmission - Start");
    
    // Serial debug info
    // debugoutlnMemory();
    
    /* Server IP adress - we remain with a local IP because currently the web server is
     * in the same network as the RedFly WiFi shield
     */
    byte server[] = { 192, 168, 178, 23 }; // {173, 194, 219 ,94}; google.de //  //{  85, 13,145,242 }; //ip from www.watterott.net (server)
    
    // initialize the client
    RedFlyClient client(server, 80);
    
    int maxAttempts = 6; // how often do we try to send out the data at max?
    int numberAttempts = 1; // starting point to count
    
    // temp boolean to store the interpreted result of the http GET request
    const char * tempRequestSuccessTrue;
    
    // if we could not transmit successfully try it again
    do {
        
        // Serial log info
        // debugoutlnInt("new attempt", numberAttempts);
        
        // count the number of attempts up
        numberAttempts++;
        
        // The Post request is done in the statement below
        tempRequestSuccessTrue = ResultOfHttpPostRequest(value, websiteSelector, client, server);
 
        
        // figure out if a manual watering initiation happend on the website
        if (tempRequestSuccessTrue == "initiat"){
            
            // store the current manual watering initiation status
            manualWateringInitiationStatus = "initiate"; // webServerInitiationStatus(tempRequestSuccessTrue, websiteSelector);

        }
        else {
            
            manualWateringInitiationStatus = "reset"; // the default is to reset and that is ok, because nothing will happen then
        }
        
    }
    // Check if we receive a HTTP response for our POST request and interprete this response
    while ((tempRequestSuccessTrue == "failure") && (numberAttempts <= maxAttempts));
    
    // Serial debug info
    // debugoutln("FullHttpPostTransmission - End");
    
    // Serial debug info
    // debugoutlnMemory();
}

void resetTheWateringInitiationStatusOnWebserver(){
    
    /* Call the web server to receive the current initiation status (initiate or reset)
     * the php script that interpretes the incomming GET request is defined here
     * home/watering.php
     */
    FullHttpPostTransmission(1, 3); // The int value 1 == reset // The 3 is the value for the webSiteselector watering.php

}


/* Check if it is time to perform a new moisture measurement
 * We don't want to measure the moisture every loop of the processor
 */
boolean IsTimeForSomething(long starttime, unsigned long interval) {
    
    // TRUE = Yes, we need to perform a a action
    // FALSE = No, currently no new moisture measurement needed, the last moisture measurement was performed not long ago
    
    /* If the time that has passed between the last moisture measurement and now is larger than
     * the defined moisture intervall than a new moisture measurement is needed
     */
    unsigned long currentMillis = millis();
    
    if(currentMillis - starttime < interval) {
        
        // debugoutln("IsTimeForSomething = false");
        return(false);
    }
    else {
        
        // Serial debug info
        // debugoutln("IsTimeForSomething = true");
        
        return(true);
    }
}


// If we received an watering initiation state over the website we need to perform some actions accordingly
void checkForManualWateringInitiation(const char * startWatering){
    
    // input char * manualWateringInitiationStatus
    
    /* we know that we received an "initiate" status from the call_initiation.php so we want to start the waterpump "manually" - which means by force and not due to dryness and reset the status of the webserver, to allow a new "manual" watering event
     */
    
    if ((startWatering == "initiate") && (IsTimeForSomething(lastManualWateringActionTime, manualWateringInitiationInterval[istest]))){
        
        // reset the timer for the manual watering action
        lastManualWateringActionTime = millis();
        
        // so we want to start the waterpump "manually" - which means by force and not due to dryness
        StartTheWaterPump();
        
        // reset the watering initation status to avoid an immediate watering action and to allow a new watering initiation over the website
        manualWateringInitiationStatus = "reset";
        
        /* and reset the status of the webserver, to allow a new "manual" watering event
         * this is how a resetcall would look like
         * 192.168.178.24/watering/?name=Banane&value=0&key=c3781633f1fb1ddca77c9038d4994345
         */
        resetTheWateringInitiationStatusOnWebserver();

    }
    
}


// Setup Start #######################################################################################################################


void setup() {
    
    // Inialize the Serial Communication and set the data rate for the hardware serial port
    Serial.begin(9600);
    
    // Statuslog
    // debugoutln("void setup()");
    
    // Define pins and functions of these pins
    pinMode(SoilDryWarningLED, OUTPUT);  // to switch on or off the LED for dryness indication
    pinMode(SoilMeasureVoltagePin, OUTPUT); // to apply voltage to the moisture sensor
    pinMode(CurrentlyMoistureMeasurementIndicatorLED, OUTPUT); // to switch on or off the LED for measurement indication
    pinMode(PumpVoltagePin, OUTPUT);
    
    // Blink once to show that we have the new version of the code
    digitalWrite(SoilDryWarningLED, HIGH);
    delay(400);
    digitalWrite(SoilDryWarningLED, LOW);
    
    // initially connect to the WiFi network using the RedFly WiFi Shield
    EstablishWifiConnectionWithRedFlyShield();
    
}


// Setup End #########################################################################################################################


/* Calculate the percentage value of current moisture based on the last measured moisture analog Input
 * This percentage value of the current moisture will be shown to the user on a website or in an app and so on... 
 */
long PercentMoistureValue(int AnalogInputValue)
{
    
    // Serial debug info
    // debugoutln("PercentMoistureValue - Start");
    
    // Serial debug info
    // debugoutlnMemory();
    
    // see the threshold definition above
    int zero = ThresholdsForAnalogInputValues[0];
    int twenty = ThresholdsForAnalogInputValues[1];
    int fourty = ThresholdsForAnalogInputValues[2];
    int eighty = ThresholdsForAnalogInputValues[3];
    int hundred = ThresholdsForAnalogInputValues[4];
    
    long PercentageValue;
    
    /* If we ever receive an anlogInput value that is larger than 100% == 481 or smaller than 
     * 0% == 205 than we limit the range of the value we calculate with with the 0% and 100% values
     * which have been defined in the thresholds for the analog input values
     */
    AnalogInputValue = constrain(AnalogInputValue, zero, hundred);
    
    // percentage mapping between 0 and 20%
    // https://www.arduino.cc/en/Reference/Map
    if (AnalogInputValue <= twenty){
        PercentageValue = map(AnalogInputValue, zero, twenty, 0, 20);
    }
    // percentage mapping between 20 and 40%
    else if ((AnalogInputValue > twenty) && (AnalogInputValue <= fourty)){
        PercentageValue = map(AnalogInputValue, twenty + 1, fourty, 21, 40);
    }
    // percentage mapping between 40 and 80%
    else if ((AnalogInputValue > fourty) && (AnalogInputValue <= eighty)){
        PercentageValue = map(AnalogInputValue, fourty + 1 , eighty, 41, 80);
    }
    // percentage mapping between 80 and 100%
    else if (AnalogInputValue > eighty){
        PercentageValue = map(AnalogInputValue, eighty + 1, hundred, 81, 100);
    }
    
    return(PercentageValue);
}



/* Measure the moisture of the soil
 * but only if it is already time to do the measurement
 */
void DefineTheCurrentMoistureIndicator(boolean IsTimeForMoistureMeasurement) {
    
    // Serial debug info
    // debugoutln("MoistureMeasurement()");
    
    // is it already time to perform a new moisture check?
    if(IsTimeForMoistureMeasurement) {
        
        // Serial debug info
        // debugoutln("IsTimeForMoistureMeasurement == true");
        
        /* Store the current time to "remember" when the last moisture measurement took place
         * This information will be needed to decide in later loops of the code if it is time
         * to perform a new measurement
         */
        lastMoistMeasureTime = millis();
        
        /* Start the measurement of the current soil moisture
         * and interprete this value into an Moisture indicator
         */
        MoistureIndicator = currentMoistureInterpretation();

    }
    else{
        
        // Serial debug info
        // debugoutln("IsTimeForMoistureMeasurement == false");
    }
}


// Decide if we need to switch the Dryness Warning LED on or off based on the interpretation of the moisture sensor analog input
void DecisionToSwitchSoilDryWaringLED(char * Indicator){

    // Serial debug info
    // debugoutln("DecisionToSwitchSoilDryWaringLED()");
    
    // What kind of soil moisture indicator did we receive?
    // Serial debug info
    // debugoutlnChar("Received Indicator", Indicator);
    
    // Based on the received indicator you may decide if you need to switch on the LED
    if (Indicator == "urgently dry") {
        
        // Serial debug info
        // debugoutln("Switch ON LED");
        
        // switch on the red dryness indication LED
        // currently switched off, because it seems to consume too much current
        // digitalWrite(SoilDryWarningLED, HIGH);
        
    }
    else if (Indicator == "dry"){
        
        // Serial debug info
        // debugoutln("Switch ON LED");
        
        // switch on the red dryness indication LED
        // currently switched off, because it seems to consume too much current
        // digitalWrite(SoilDryWarningLED, HIGH);
        
    }else {
        
        // in all the cases where the soil is not "dry" or "urgently dry" no warning LED is needed
        
        // Serial debug info
        // debugoutln("Switch OFF LED");
        
        // switch off the red dryness indication LED
        digitalWrite(SoilDryWarningLED, LOW);
    }
            
}


// Decide if we need to switch on the water pump to perform a self watering action based on the interpretation of the moisture sensor analog input
void DecisionToSwitchWaterPump(char * Indicator){
    
    // Serial debug info
    // debugoutln("DecisionToSwitchWaterPump()");
    
    // Which Indicator did we receive?
    // Serial debug info
    // debugoutlnChar("Received Indicator", Indicator);
    
    // decide based on the indicator if we need to start the water pump
    if (Indicator == "urgently dry") {
        
        // Serial debug info
        // debugoutln("Switch ON pump");
        
        // start the self watering action
        StartTheWaterPump();
        
    } else {
        
        // Serial debug info
        // debugoutln("Switch OFF pump");
        
        // do nothing but ensure that there is no power supply to the transistor
        digitalWrite(PumpVoltagePin, LOW);
        
    }
}


// Transform the current moisture value into a percentage value and send it to a database using http://
void SendMoisturePercentageValueToDatabase(boolean IsTimeToSendData, int MoistAnalogValue){
    
    // Serial log info
    // debugoutln("SendMoisturePercentageValueToDatabase - Start");
    
    // Serial debug info
    // debugoutlnMemory();
    
    if (IsTimeToSendData){
        
        // Serial debug info
        // debugoutln("IsTimeToSendData");
        
        // Transform the current analogInput value for the moisture of the soil into a percentage value
        FullHttpPostTransmission(PercentMoistureValue(MoistAnalogValue), 1); // 1 is the websiteSelector for valueget.php
        
    }

    // Serial log info
    // debugoutln("SendMoisturePercentageValueToDatabase - End");
    
    // Serial debug info
    // debugoutlnMemory();
}

// Call a web server to see if there is a manual initiation for watering the plant
void CheckWateringInitiationStatus(boolean IsTimeToCallInitiationStatus){
   
    // Serial log info
    // debugoutln("CheckWateringInitiationStatus");
    
    if (IsTimeToCallInitiationStatus){
        
        // Serial debug info
        // debugoutln("IsTimeToCallData");
        
        // reset the timer to wait for the next Inition Call
        lastWateringInitiationCallTime = millis();
        
        // Call the web server to receive the current initiation status (initiate or reset)
        FullHttpPostTransmission(1, 2); // The int value is random, because we currently don't need it to just call the URL // The 2 is the value for the webSiteselector call_initiation.php
        
    }
}


// Loop Start #######################################################################################################################


void loop() {
    
    /* Check if it is time to start the measurement of the soil moisture
     * The return of this check is a simple TRUE or FALSE statement
     * We use this statement to pass it on to following functions to decide e.g. if a moisture
     * needs to take place
     */
    boolean MeasureAndDataTransimitionTime = IsTimeForSomething(lastMoistMeasureTime, MoistMeasureInterval[istest]);
    
    /* Check if it is time to start calling the web server for a watering initiation status 
     */
    boolean WateringInitiationStatusTime = IsTimeForSomething(lastWateringInitiationCallTime, WateringInitiationCallInterval[istest]);
    
    /* Start the moisture measurement
     * Cosider the TRUE or FALSE statement from the time check before
     * The return of this moisture measurement is an analog input value
     * This analog input value is then interpreted into an moisture indicator 
     * of the current moisture status of the soil
     */
    DefineTheCurrentMoistureIndicator(MeasureAndDataTransimitionTime);
    
    /* Decide if the red dryness warning indication LED needs to be swiched on or off based on the moisture
     * interpretation
     */
    DecisionToSwitchSoilDryWaringLED(MoistureIndicator);
    
    /* Decide if the water pump to water the soil automatically should be switched on
     * This decision will be based on the last moisture measurement of the moisture sensor
     * If the soil is "urgently dry" the waterpump will immediately start watering the soil
     */
    DecisionToSwitchWaterPump(MoistureIndicator);
    
    /* Check from time to time if a watering event was initiated manually over the website
     * Therefore we make a http:// request to our webserver and call the current status
     * of the watering initiation
     * If we receive that a manual watering event was initiated, then we start the waterpump
     * After that we reset the watering initiation status with a web server call
     */
    CheckWateringInitiationStatus(WateringInitiationStatusTime);
    
    /* Check if there was a manual watering action over the website 
     */
    checkForManualWateringInitiation(manualWateringInitiationStatus);
    
    /* Send the moisture data to a central database 
     * from there the moisture value can be displayed in an app or on a website
     * we only store the current percentage value for the moisture in that database
     */
    SendMoisturePercentageValueToDatabase(MeasureAndDataTransimitionTime, MoistureMeasurementResultAnalogInput);
    
    /* After one cycle of the loop has taken place reset the value of the MeasureAndDataTransimitionTime 
     * to avoid unnecessary measures or data transmitions
     */
    MeasureAndDataTransimitionTime = false;
    
}