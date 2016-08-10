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
// Version		0.66.0
//
// Copyright	© Stefan Willuda, 2016
// Licence		Creative Commons - Attribution - ShareAlike 3.0
//
// See         ReadMe.txt for references
//


// PIN Declaration ############################################################

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


// Include Libraries ###########################################################

// Include Arduino library to allow autocomplete syntax in Xcode
#include <Arduino.h>

// Include libraries to allow WiFi Connection with the RedFly Wifi shield
// https://github.com/watterott/Arduino-Libs/tree/master/RedFly
#include <RedFly.h>
#include <RedFlyClient.h>
#include <RedFlyServer.h>


// Constants and variables ######################################################

// Calculate or store constants that are uses several times in the codebase

// When we apply voltage to the moisture sensor it takes a short time for the sensor to adjust
unsigned long SoilMoistureMeasurementWaitDuration = 1000; // milliseconds 1.000 milliseconds = 1 second


// Every how many milliseconds are we going to perform a moisture measurement?
// currently I use millis() because I don't need the exact time and millis() is easier to simulate than now()
// 30 minutes * 60 seconds * 1000 milliseconds
unsigned long MoistMeasureInterval = 1800000; // 30 * 60 * 1000; // milliseconds 1.000 milliseconds = 1 second

// store the most recent time when the moisture measurement took place
/* When we start the first iteration of the code loop than we use the current time minus one interval
 * which leads to an immediate measurement of the soil when the board is connected to the power supply
 */
long lastMoistMeasureTime = -1 * MoistMeasureInterval;


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

 * Used thresholds
 * "zero water" : 0% : 205 : Indicator 0 - urgently dry
 * "urgently dry" : 20% : 260 : Indicator 0 - urgently dry
 * "moist" : 40% : 340 : Indicator 1 - dry
 * "very moist" : 80% : 442 : Indicator 2 - moist
 * "wet" : 100% : 481 : Indicator 2 - moist
 */
int ThresholdsForAnalogInputValues[] = {205, 260, 340, 442, 481};

/* In the Array we store different tresholds
 * position 0 --> the indicator for "urgently dry" - triggers self watering event
 * position 1 --> the indicator for "dry" - triggers the red warning lamp that asks for manual watering
 * position 2 --> the indicator for "moist" - everything is ok
 */
int MoistureIndicators[] = {0, 1, 2};

/* Store the indicator of the soil moisture
 * When we run the loop for the first time we consider the soil to be "moist";
 */
int MoistureIndicator = MoistureIndicators[2];


// Functions #####################################################################

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


// Establish a WiFi Connection using the RedFly WiFi Shield ######################
void EstablishWifiConnectionWithRedFlyShield()
{
    
    // initialize the WiFi module on the shield
    
    // Serial log
    debugoutln("EstablishWiFiConnectionWithRedFlyShield()");
    
    uint8_t ret;
    
    //init the WiFi module on the shield
    // ret = RedFly.init(br, pwr) //br=9600|19200|38400|57600|115200|200000|230400, pwr=LOW_POWER|MED_POWER|HIGH_POWER
    // ret = RedFly.init(pwr) //9600 baud, pwr=LOW_POWER|MED_POWER|HIGH_POWER
    // ret = RedFly.init() //9600 baud, HIGH_POWER
    
    ret = RedFly.init();
    
    /* sometimes the connection is not established on the first try, thats why I need to try more than once
     * but not more than 20 times, because this would make the whole code in the loop stop
     */
    
    int counter, maxcounter;
    counter = 0;
    maxcounter = 100;
    
    while (ret && counter < maxcounter) {
        
        ret = RedFly.init();

        debugoutln("RedFly.init ERROR"); //there are problems with the communication between the Arduino and the RedFly

        counter++;
        
    }
    
    // ret = RedFly.init();
    
    if(ret){

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
         * but not more than 20 times, because this would make the whole code in the loop stop
         */

        counter = 0;
        
        while (ret && counter < maxcounter) {
            
            ret = RedFly.join(Network, NetworkPW, INFRASTRUCTURE);
            
            debugoutln("RedFly.join ERROR");
            
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
                
                debugoutln("RedFly.begin ERROR");
                
                counter++;
                
            }
            
            if(ret){
                
                // The connection was not established this time, so disconnect
                // RedFly.disconnect();
                
            }
            else {
                
                //RedFly.getlocalip(ip);       // receive shield IP in case of DHCP/Auto-IP
                
                // server.begin();
                debugoutln("WiFi Shield connected");
                
            }
        }
    }
}


// Send out the measured data to a website #######################################

/* Sends different values to a http webserver on which a PHP script waits for the data
 * to store them in a MySQL database

 * Based on Watterott sample
 * Web Client
 * This sketch connects to a website using a RedFly-Shield.

 * Inspired by
 * http://jleopold.de/wp-content/uploads/2011/03/ArduinoDatenLogger.txt
 */
void HttpDataTransmition(int value){
    
    debugoutln("HttpDataTransmition");
    
    // server on which the php script runs, that interprets the moisture value
    byte server[] = { 192, 168, 178, 24 }; //{  85, 13,145,242 }; //ip from www.watterott.net (server)
    
    RedFlyClient client(server, 80);
    
    if(client.connect(server, 80))
    {
        //make a HTTP request
        //http://www.watterott.net/forum/topic/282
        
        debugoutln("Http Send");
        
        // Host IP der Website
        #define HOSTNAME "192.168.178.24"
        
        // we fill in different datatable values
        
        // one is the name of the sensor (plant)
        const char * sensor_string;
        sensor_string = "Test";
        
        // one is the kind of value we are transmitting
        const char * type_string;
        type_string = "Prozentfeuchte";
        
        
        //String GetRequest;
        // http://miscsolutions.wordpress.com/2011/10/16/five-things-i-never-use-in-arduino-projects/
        
        char * GetRequest;
        const char * get1;
        
        get1 = "GET /valueget.php"; // Zugang zur Live-Datenbank
        
        const char * get2 = "?name=";
        const char * get3 = "&type=";
        const char * get4 = "&value=";
        const char * get5 = "&key=c3781633f1fb1ddca77c9038d4994345";
        const char * get6 = " HTTP/1.1\r\nHost: ";
        const char * get7 = "\r\n\r\n";
        
        char * value_char;
        value_char = (char*) calloc(5, sizeof(char));
        itoa(value, value_char, 10);
        
        // allocate memory for the message
        GetRequest = (char*) calloc(strlen(get1) + strlen(get2) + strlen(sensor_string)  + strlen(get3) + strlen(type_string) + strlen(get4)
                                    + strlen(value_char) + strlen(get5) + strlen(get6) + strlen(HOSTNAME) + strlen(get7) + 1, sizeof(char));
        
        // assemble the GetRequest
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
        
        client.print(GetRequest);
    
        // http://nanismus.no-ip.org/nanismus_test/valueget.php?name=Banane&type=status&value=6&key=123
        
        free(GetRequest);                       // free the allocated string memory
        free(value_char);
        
        
        /* To be sure that the data transmission was successful catch the server response and evaluate the result
         * do it like here 
         * /Users/stefanwilluda/github/nanismus/embedXcode-nanismus/nanismusPCBcode/nanismus/nanismus/Sketchbook/Libraries/RedFly/examples/WebClient/WebClient.ino
         */
        
        client.flush();
        client.stop();
        
        // Serial Log info
        debugoutln("Transmission success");
        
    }
    else {
        
        // Serial Log info
        debugoutln("PHP Server unavailable");
        
        // try to re-establish the wifi connection
        EstablishWifiConnectionWithRedFlyShield();
        
    }
    
}


// Setup Start ###################################################################

void setup() {
    
    // Inialize the Serial Communication and set the data rate for the hardware serial port
    Serial.begin(9600);
    
    // Statuslog
    debugoutln("void setup()");
    
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

// Setup End #####################################################################
/* Check if it is time to perform a new moisture measurement
 * We don't want to measure the moisture every loop of the processor
 */
boolean IsTimeForMoistureMeasurement() {
    
    // TRUE = Yes, we need to perform a moisture measurement
    // FALSE = No, currently no new moisture measurement needed, the last moisture measurement was performed not long ago
    
    /* If the time that has passed between the last moisture measurement and now is larger than
     * the defined moisture intervall than a new moisture measurement is needed
     */
    
    unsigned long currentMillis = millis();
    if(currentMillis - lastMoistMeasureTime >= MoistMeasureInterval) {
        return(true);
    }
    else {
        return(false);
    }
}

/* Interprete the return percentage value into an LED light indication if the soil is dry
 *
 */
// Interprete the analog input from the moisture sensor
void InterpreteMoistureMeasurementAnalogInput(int Input) {
    
    // Define wether a analog input value is considered dry or moist
    
    // Check if the analog input value from the moisture sensor is considered to indicate an "urgently dry" soil
    if(Input <= ThresholdsForAnalogInputValues[MoistureIndicators[1]]){
        
        // retun that the soil is considered "urently dry"
        MoistureIndicator = MoistureIndicators[0];
        
    }
    // Check if the analog input value from the moisture sensor is considered to indicate a "dry" soil
    else if(Input <= ThresholdsForAnalogInputValues[MoistureIndicators[2]]){
        
        // retun that the soil is considered "dry"
        MoistureIndicator = MoistureIndicators[1];
        
    }
    else {
        
        // return that the soil is considred "moist"
        MoistureIndicator = MoistureIndicators[2];
    }
}

/* Calculate the percentage value of current moisture based on the last measured moisture analog Input
 * This percentage value of the current moisture will be shown to the user on a website or in an app and so on... 
 */
long PercentMoistureValue(int AnalogInputValue)
{
    
    /*
     * Used thresholds
     * "zero water" : 0% : 205 : Indicator 0 - urgently dry
     * "urgently dry" : 20% : 260 : Indicator 0 - urgently dry
     * "moist" : 40% : 340 : Indicator 1 - dry
     * "very moist" : 80% : 442 : Indicator 2 - moist
     * "wet" : 90% : 481 : Indicator 2 - moist
     */
    // int ThresholdsForAnalogInputValues[] = {205, 260, 340, 442, 481};
    
    
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


// Start the actual moisture measurement by calling data from the moisture sensor
void PerformMoistureMeasurement(){
    
    // idicate with an LED that a measurement is currently performed
    digitalWrite(CurrentlyMoistureMeasurementIndicatorLED, HIGH);
    
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
    
    // switch off the indication LED
    digitalWrite(CurrentlyMoistureMeasurementIndicatorLED, LOW);
    
    // Interprete the analog input value from the sensor
    InterpreteMoistureMeasurementAnalogInput(MoistureMeasurementResultAnalogInput);
    
}


/* Measure the moisture of the soil
 * but only if it is already time to do the measurement
 */
void MoistureMeasurement(boolean IsTimeForMoistureMeasurement) {
    
    // is it already time to perform a new moisture check?
    if(IsTimeForMoistureMeasurement) {
        
        /* Store the current time to "remember" when the last moisture measurement took place
         * This information will be needed to decide in later loops of the code if it is time
         * to perform a new measurement
         */
        lastMoistMeasureTime = millis();
        
        // Start the measurement of the current soil moisture
        PerformMoistureMeasurement();
    }
}


// Decide if we need to switch the Dryness Warning LED on or off based on the interpretation of the moisture sensor analog input
void DecisionToSwitchSoilDryWaringLED(int Indicator){

    // remember the moisture indicator in this switch statement if "0" some day no longer means "urgently dry"
    // and "1" does no longer mean "dry"
    // MoistureIndicators[0];
    
    switch(Indicator){ // What kind of soil moisture indicator did we receive?
            
        case 0: // the soil is urgently dry
        case 1: // the soil is dry
            
            // switch on the red dryness indication LED
            digitalWrite(SoilDryWarningLED, HIGH);
            break;
            
        default: // in all the cases where the soil is not "dry" or "urgently dry" no warning LED is needed
            
            // switch of the red dryness indication LED
            digitalWrite(SoilDryWarningLED, LOW);
            break;
    }
}


// Switch on the water pump by switching the transistor
void StartTheWaterPump(){
    
    /* store when the pump action started
     * check how long the self watering action is currently performed
     * check if the soil is already "moist" again
     * stop the watering immediately if the time for the watering is up or the soil is "moist" again to avoid water overflow
     */
    
    unsigned long CurrentMillis = millis(); // recurring check of the current time
    unsigned long PumpBeginningMillis = CurrentMillis; // this value serves to compare start and end time of the watering action
    unsigned long PumpDurationMillis = 10000; // water for 10 seconds. This provides 300 ml of water
    
    while ((CurrentMillis - PumpBeginningMillis < PumpDurationMillis) && (MoistureIndicator != MoistureIndicators[2])) {
        
        // switch on the water pump
        digitalWrite(PumpVoltagePin, HIGH);
        
        // check if the soil is "moist" already;
        PerformMoistureMeasurement();
        
        CurrentMillis = millis();
    }
    
    // stop the water pump
    digitalWrite(PumpVoltagePin, LOW);
}


// Decide if we need to switch on the water pump to perform a self watering action based on the interpretation of the moisture sensor analog input
void DecisionToSwitchWaterPump(int Indicator){
    
    // remember the moisture indicator in this switch statement if "0" some day no longer means "urgently dry"
    // MoistureIndicators[0];
    
    switch (Indicator) {
        case 0: // the soil is "urgently dry"
            
            // start the self watering action
            StartTheWaterPump();
            
            break;
            
        default:
            // do nothing but ensure that there is no power supply to the transistor
            digitalWrite(PumpVoltagePin, LOW);
            
            break;
    }
}

// Transform the current moisture value into a percentage value and send it to a database using http://
void SendMoisturePercentageValueToDatabase(boolean IsTimeToSendData, int MoistAnalogValue){
    
    if (IsTimeToSendData){
        
        debugoutln("SendMoisturePercentageValueToDatabase");
        
        // Transform the current analogInput value for the moisture of the soil into a percentage value
        HttpDataTransmition(PercentMoistureValue(MoistAnalogValue));
        
    }
}


void loop() {
    
    /* Check if it is time to start the measurement of the soil moisture
     * The return of this check is a simple TRUE or FALSE statement
     * We use this statement to pass it on to following functions to decide e.g. if a moisture
     * needs to take place
     */
    boolean MeasureAndDataTransimitionTime = IsTimeForMoistureMeasurement();
    
    /* Start the moisture measurement
     * Cosider the TRUE or FALSE statement from the time check before
     * The return of this moisture measurement is an analog input value
     * This analog input value is then converted into a percentage value in three ranges which lead to an interpretation
     * of the current moisture status of the soil - let's start with green, yellow, red
     */
    MoistureMeasurement(MeasureAndDataTransimitionTime);
    
    /* Decide if the red dryness warning indication LED needs to be swiched on or off based on the moisture
     * interpretation
     */
    DecisionToSwitchSoilDryWaringLED(MoistureIndicator);
    
    /* Decide if the water pump to water the soil automatically should be switched on
     * This decision will be based on the last moisture measurement of the moisture sensor
     * If the soil is "urgently dry" the waterpump will immediately start watering the soil
     */
    DecisionToSwitchWaterPump(MoistureIndicator);
    
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