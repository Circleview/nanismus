/*
  SD card datalogger
 
 This example shows how to log data from three analog sensors
 to an SD card using the SD library.
   
 The circuit:
 * analog sensors on analog ins 0, 1, and 2
 * SD card attached to SPI bus as follows:
 ** MOSI - pin 11
 ** MISO - pin 12
 ** CLK - pin 13
 ** CS - pin 4
 
 created  24 Nov 2010
 modified 9 Apr 2012
 by Tom Igoe
 
 This example code is in the public domain.
     
 */

// On the Ethernet Shield, CS is pin 4. Note that even if it's not
// used as the CS pin, the hardware CS pin (10 on most Arduino boards,
// 53 on the Mega) must be left as an output or the SD library
// functions will not work.

// Note that if you use an Arduino Mega 2560 and a SDCard Shield you might need to change
// the Library Sd2Card.h 
// In der Zeile #define MEGA_SOFT_SPI 0 muss die Null durch eine 1 ersetzt werden: #define MEGA_SOFT_SPI 1
// http://nanismus.no-ip.org/MediaWiki/index.php/Datalogging_auf_einer_SD_Karte

/*
 apperance of the logging table
 
 Timestamp	Logtype	  Detail    result
 16.11.2013	Gateway	  WiFi	    Ist eingeschaltet

*/

void log(long timestamp, int logtype, char * detail, char * result)
{
  RedFly.disable();
  
  if (debug)
  {
    spo2(PSTR("Logging gestartet."), 1); 
    //freememorycheck();                // Print the available RAM memory out  
    
    spo2(PSTR("timestamp: "), 0); 
    Serial.println(timestamp);
    spo2(PSTR("logtype: "), 0);
    Serial.println(logtype); 
    spo2(PSTR("detail: "), 0); 
    spo2(detail, 1);
    spo2(PSTR("result: "), 0); 
    spo2(result, 1);
    // First we convert the time 
    // Format 16.11.2013 11:07:09
  }
    
  const char * dot = ".";
  const char * space = " ";
  const char * colon = ":";
  const char * separator = ";";
  
  // Convert the numbers into chars
  
  char * charday; 
  charday = (char*) calloc(3, sizeof(char));
  sprintf(charday, "%02i", day(timestamp));            // http://home.fhtw-berlin.de/~junghans/cref/FUNCTIONS/format.html 
                                                       // http://www.mikrocontroller.net/topic/297962
                                                       // http://www.kriwanek.de/arduino/sprachreferenz/173-sprintf-ausgabeformatierung.html
  // dot
  char * charmonth; 
  charmonth = (char*) calloc(3, sizeof(char));
  sprintf(charmonth, "%02i", month(timestamp));
  // dot
  char * charyear; 
  charyear = (char*) calloc(5, sizeof(char));
  itoa(year(timestamp), charyear, 10);  
  // space
  char * charhour; 
  charhour = (char*) calloc(3, sizeof(char));
  sprintf(charhour, "%02i", hour(timestamp));
  // colon
  char * charminute; 
  charminute = (char*) calloc(3, sizeof(char));
  sprintf(charminute, "%02i", minute(timestamp));    
  // colon
  char * charsecond; 
  charsecond = (char*) calloc(3, sizeof(char));
  sprintf(charsecond, "%02i", second(timestamp));   
  
  // assemble the chars to a full timestamp in chars
  char * chartimestamp;
  
  // allocate memory for the message
  chartimestamp = (char*) calloc(strlen(charday) + strlen(dot) + strlen(charmonth) + strlen(dot) + strlen(charyear) + strlen(space) 
  + strlen(charhour) + strlen(colon) + strlen(charminute) + strlen(colon) + strlen(charsecond) + 1, sizeof(char));
  
  // assemble the String
  strcat(chartimestamp, charday);
  strcat(chartimestamp, dot);        
  strcat(chartimestamp, charmonth);
  strcat(chartimestamp, dot);
  strcat(chartimestamp, charyear);                            
  strcat(chartimestamp, space);
  strcat(chartimestamp, charhour);        
  strcat(chartimestamp, colon);
  strcat(chartimestamp, charminute);
  strcat(chartimestamp, colon); 
  strcat(chartimestamp, charsecond); 
  
  // Only keep the full timestamp in memory an delete the rest
  
  free(charday);                       // free the allocated string memory
  free(charmonth);  
  free(charyear);
  free(charhour);
  free(charminute);
  free(charsecond);

  // Now convert the logtype to a char
  // The logtypes are not written in words, but in numbers, 
  // which are matched in an evaluation database
  
  // the following logtypes are defined
  // logtype  | definition  
  // 0        | Event 
  // 1        | gateway or if statement
  // 3        | Messwert
  
  char * charlogtype; 
  charlogtype = (char*) calloc(3, sizeof(char));
  itoa(logtype, charlogtype, 10); 

  // Then get the detail and the result and assemble a full string
  
  /*
  char *buf;
  buf=(char *)malloc(strlen_P(detail)+1);
  strcpy_P(buffer,detail);
  Serial.println(buffer);
  free(buffer);
  */
  
  char * logstring;
  // allocate memory for the message
  logstring = (char*) calloc(strlen(chartimestamp) + strlen(separator) + strlen(charlogtype) + strlen(separator) 
  + strlen_P(detail) + strlen(separator) + strlen_P(result) + 1, sizeof(char));

  strcat(logstring, chartimestamp);
  strcat(logstring, separator);        
  strcat(logstring, charlogtype);
  strcat(logstring, separator);
  strcat_P(logstring, detail);                            
  strcat(logstring, separator);
  strcat_P(logstring, result);

  free(chartimestamp);  
  free(charlogtype);
  //free(detail);
  //free(result);
  
  // write the string to the SD Card

  digitalWrite(LogLED, 1);
  //delay(400);
  //RedFly.disable();
  // open the file. note that only one file can be open at a time,
  // so you have to close this one before opening another.
  File dataFile = SD.open("datalog.txt", FILE_WRITE);

  // if the file is available, write to it:
  if (dataFile) 
  {
    dataFile.println(logstring);
    dataFile.close();
    // print to the serial port too:
    Serial.println(logstring);
  }  
  // if the file isn't open, pop up an error:
  else 
  {
    spo2(PSTR("error opening datalog.txt"), 1);
  }

  //RedFly.enable();
  free(logstring);                       // free the allocated string memory
  digitalWrite(LogLED, 0);

  //freememorycheck();                // Print the available RAM memory out  
  spo2(PSTR("Logging abgeschlossen."), 1);
  spo2(PSTR("################################"), 1);
  RedFly.enable();
  //delay(100);
    
}    

