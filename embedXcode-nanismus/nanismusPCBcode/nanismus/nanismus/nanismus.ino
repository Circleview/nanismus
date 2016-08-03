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
// Version		0.65.0
//
// Copyright	Stefan Willuda, 2016
// Licence		Creative Commons - Attribution - ShareAlike 3.0
//
// See         ReadMe.txt for references
//


// PIN Declaration ############################################################


// Declarations for status indicators LEDs

// D6
#define SoilMeasureVoltagePin 6 // Pin to start the moisture measurement

// D7
#define SoilDryWarningLED 7 // LED that indicated dryness in the soil

// D13
#define CurrentlyMoistureMeasurementIndicatorLED 13 // LED that indicates that right now a moisture measurement is performed

// A5
#define MoistureMeasurementAnalogInputPin 4


// Include Libraries ###########################################################


#include <Arduino.h>

// Calculate or store constants that are uses several times in the codebase


// When we apply voltage to the moisture sensor it takes a short time for the sensor to adjust
unsigned long SoilMoistureMeasurementWaitDuration = 2000; // milliseconds 1.000 milliseconds = 1 second


// Define used variables, constants and calculations


// Every how many milliseconds are we going to perform a moisture measurement?
// currently I use millis() because I don't need the exact time and millis() is easier to simulate than now()
// 30 minutes * 60 seconds * 1000 milliseconds
unsigned long MoistMeasureInterval = 1800000; // 30 * 60 * 1000; // milliseconds 1.000 milliseconds = 1 second


// store the most recent time when the moisture measurement took place
/* When we start the first iteration of the code loop than we use the current time minus one interval
 * which leads to an immediate measurement of the soil when the board is connected to the power supply
 */
long lastMoistMeasureTime = -1 * MoistMeasureInterval;


/* Store the indicator of the soil moisture
 * When we run the loop for the first time we consider the soil to be 1 = moist;
 */
int MoistureIndicator = 1;


// Setup Start


void setup() {
    
    // Define pins and functions of these pins
    pinMode(SoilDryWarningLED, OUTPUT);  // to switch on or off the LED for dryness indication
    pinMode(SoilMeasureVoltagePin, OUTPUT); // to apply voltage to the moisture sensor
    pinMode(CurrentlyMoistureMeasurementIndicatorLED, OUTPUT);
    
    // Blink once to show that we have the new version of the code
    digitalWrite(SoilDryWarningLED, HIGH);
    delay(200);
    digitalWrite(SoilDryWarningLED, LOW);
    
}


boolean IsTimeForMoistureMeasurement() {
    
    // Check if it is time to perform a new moisture measurement
    // We don't want to measure the moisture every loop of the processor
    
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


// Interprete the analog input from the moisture sensor
void InterpreteMoistureMeasurementAnalogInput(int Input) {
    
    // Define wether a analog input value is considered dry or moist
    // This array shall be extended later if we want a more granular distinction between dry, moist, toomoist soil
    
    // How to calculate the actual voltage input is well described at https://www.arduino.cc/en/Tutorial/ReadAnalogVoltage
    
    // Define the thresholds of different analog input values to decide if they can be considered as dry, moist and so on...
    /* In the Array we store different tresholds
     * position 0 --> threshold for dry ; 0 is also the indicator for "dry"
     * position 1 --> currently no threshold needed ; 1 is the indicator for "moist"
     */
    int MoistureIndicators[] = {0, 1};
    
    /* to define the thresholds for the analogInput value of the moisture sensor
     * I've measured the voltage input in a glass of water, which I consider to be wet and it was 0.7 volts input
     * Considering the formular: voltage= sensorValue * (5.0 / 1023.0)
     * I've measured different states of moisture to collect example data
     * If the moisture sensor sticks in a glass of water I can measure with a multimeter 2.6 Volts input
     *		100% moist = 2.6 V = 532 analogInput
     * If the moisture sensor sticks in soil that is considered 40% wet = 1,66 V
     *		40% moist = 1,66 V = 340 analogInput
     * If the moisture sensor sticks in soil that has been watered right now = 2,16 V
     *		80% moist = 2,16 V = 442 analogInput
     * If the moisture sensor sticks in soil that is still water wet after a watering = 2,35 V
     *		90% moist = 2,35 V = 481 analogInput
     * I can assume that 100 analogInput indicates wet soil
     */
    int ThresholdsForAnalogInputValues[] = {340};
    
    // Check if the analog input value from the moisture sensor is considered to indicate a dry soil
    if(Input <= ThresholdsForAnalogInputValues[MoistureIndicators[0]]){
        
        // retun that the soil is considered "dry"
        MoistureIndicator = MoistureIndicators[0];
        
    }
    else {
        
        // return that the soil is considred "moist"
        MoistureIndicator = MoistureIndicators[1];
    }
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
    while (currentMillis - firstMeasureTime <= SoilMoistureMeasurementWaitDuration) {
        
        currentMillis = millis();
    }
    
    // collect the data input that is sent by the soil moisture sensor and store it for reuseage
    int MoistureMeasurementResultAnalogInput = analogRead(MoistureMeasurementAnalogInputPin);
    
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
    
    switch(Indicator){ // What kind of soil moisture indicator did we receive?
            
        case 0: // the soil is dry
            
            // switch on the red dryness indication LED
            digitalWrite(SoilDryWarningLED, HIGH);
            break;
            
        case 1: // the soil is moist
            
            // switch of the red dryness indication LED
            digitalWrite(SoilDryWarningLED, LOW);
            break;
    }
}


void loop() {
    // put your main code here, to run repeatedly:
    
    /* Check if it is time to start the measurement of the soil moisture
     * The return of this check is a simple TRUE or FALSE statement
     * We use this statement to pass it on to following functions to decide e.g. if a moisture
     * needs to take place
     */
    
    /* Start the moisture measurement
     * Cosider the TRUE or FALSE statement from the time check before
     * The return of this moisture measurement is an analog input value
     * This analog input value is then converted into a percentage value in three ranges which lead to an interpretation
     * of the current moisture status of the soil - let's start with green, yellow, red
     */
    
    MoistureMeasurement(IsTimeForMoistureMeasurement());
    
    /* Interprete the return percentage value into an LED light indication if the soil is dry
     *
     */
    
    /* Decide if the red dryness warning indication LED needs to be swiched on or off based on the moisture
     * interpretation
     */
    DecisionToSwitchSoilDryWaringLED(MoistureIndicator);
    
}