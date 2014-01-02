// Include Libraries ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''

  #include <Time.h>         // http://www.arduino.cc/playground/Code/Time
                            // Enable time measurement and the now() function
                            /* now(); // returns the current time as seconds since Jan 1 1970
                               time_t t = now(); // store the current time in time variable t
                               hour(t);          // returns the hour for the given time t
                               minute(t);        // returns the minute for the given time t
                               second(t);        // returns the second for the given time t
                               day(t);           // the day for the given time t
                               weekday(t);       // day of the week for the given time t  
                               month(t);         // the month for the given time t
                               year(t);          // the year for the given time t
                            */
  #include <MemoryFree.h>   // library to check the Currently free Memory from http://www.arduino.cc/playground/Code/AvailableMemory
			    // Download the Library from https://github.com/maniacbug/MemoryFree
  #include <avr/pgmspace.h> // library to store strings in Flash memory to avoid SRAM overflow
			    // http://www.arduino.cc/en/Reference/PROGMEM
  #include <RedFly.h>       // Use the RedFly WiFi Shield
  #include <RedFlyServer.h>
  #include <RedFlyClient.h>  
  
  
  #include <SD.h>          // needed for Datalogging on an SD Card
// ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''

