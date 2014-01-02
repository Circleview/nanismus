// documentation

// ******************************************************
// Projekt Pflanzenbewaesserung von Frau K. und Herr W. *
// begonnen in 2011                                     *
// ******************************************************

// ?????????????????????????????????????????????????????
// Implemented features:                               ?
// 1. Moisture Measurement for more than one plant     ?
// 2. Water Measurement at the bottom of the plant     ?
// 3. Temperature sensing for one sensor               ?
// 4. Connection to the local WLAN                     ?
// 5. Sending status messages to twitter               ?
// 6. Sending measure values to cosm                   ?
// 7. Selfwatering Action if soil is too dry           ?
// 8. Moisture Measurement in the watertank            ?
// 9. Get current time from NTP via WLAN               ?
// ?????????????????????????????????????????????????????

// DECLARATION START //////////////////////////////////
// Picture of Arduino Mega http://orxor.files.wordpress.com/2011/12/arduino-mega.png
/* ****************************************************
 Circuit:                                             *
 * Which pins does the RedFly use?                    *
 * Used pins: D0, D1, D2, D3                          *
 * Pin D4  -> Current to the waterpump                *
 * Pin D5  ->                                         *
 * Pin D32  -> LED for Dryness-Warning + Communication *
 * Pin D22  -> Current MoistureSensor 1 Banane         *
 * Pin D24  -> Current MoistureSensor 2 Banane Topf    *
 * Pin D26  -> Current MoistureSensor 3 Wassertank     *
 * Pin D10 -> SD Card Shield                          *
 * Pin D11 -> SD Card Shield                          *
 * Pin D12 -> SD Card Shield                          *
 * Pin D13 -> SD Card Shield                          *
 * Pin D28 -> LED for Watertank dryness warning       * ! Arduino MEGA only
 * Pin D30 -> LED for COSM Status Light               * ! Arduino MEGA only
 * Pin D32 -> LED for Twitter Status Light            * ! Arduino MEGA only
 * Pin D34 -> LED to indicate how long until measure  * ! Arduino MEGA only
 * Pin D36 -> LED to indicate how long until measure  * ! Arduino MEGA only
 * Pin D38 -> LED to indicate how long until measure  * ! Arduino MEGA only
 * Pin D40 -> LED for WiFi and Connection Status      * ! Arduino MEGA only
 * Pin D53 -> SD Card Shield                          * ! Arduino MEGA only on Uno it is Pin 10
 * Pin A0  -> Measure Values Temperature Sensor       *
 * Pin A1  -> Measure Values Sensor 1 Banane          *
 * Pin A2  -> Measure Values Sensor 2 Banane Topf     *
 * Pin A3  -> Measure Values Sensor 3 Wassertank      *
 * Pin A4  ->                                         *
 * Pin A5  ->                                         *
 *                                                    *
 *****************************************************/
// Due to a strange error code I tried to reduce the number of global variables
// like it was recommended here http://stackoverflow.com/questions/8188849/avr-linker-error-relocation-truncated-to-fit
// The amount of global variables seems to be restricted by storage space in some vectors


