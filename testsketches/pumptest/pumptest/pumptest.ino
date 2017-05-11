  #define PumpVoltagePin 11

  unsigned long PumpDurationMS = 10000;

  int PumpIndicator = 1;
  

void setup() {
  // put your setup code here, to run once:

  pinMode(PumpVoltagePin, OUTPUT); 

  digitalWrite(PumpVoltagePin, LOW); 
  
}

void loop() {
  // put your main code here, to run repeatedly:

  unsigned long currentMillis = millis();
  unsigned long startMillis = currentMillis; 

  while ((currentMillis - startMillis < PumpDurationMS) && (PumpIndicator == 1)){

    digitalWrite(PumpVoltagePin, HIGH);

    currentMillis = millis(); 
  }

  digitalWrite(PumpVoltagePin, LOW); 

  PumpIndicator = 0; 

}
