#include "EmonLib.h"
#include <WiFi.h>
#include <WiFiClient.h>

// Configuración del WiFi
const char* ssid = "Bibliteca";
const char* password = "";

// Configuración del socket
const char* host = "10.1.76.209";  // IP de tu servidor
const uint16_t port = 32320;        // Puerto remoto al que nos queremos conectar

EnergyMonitor emon;

#define vCalibration 83.3
#define currCalibration 0.50

#define LED_PIN 2  // Definir el pin del LED
#define UMBRAL_CONSUMO 10.0  // Umbral de consumo en kWh para activar el LED

float kWh = 0;
unsigned long lastmillis = millis();

WiFiClient client;

void connectToWiFi() {
  Serial.print("Conectando a WiFi...");
  WiFi.begin(ssid, password);
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("Conectado al WiFi.");
}

void connectToServer() {
  if (!client.connect(host, port)) {
    Serial.println("Conexión fallida con el servidor.");
    return;
  }
  Serial.println("Conectado al servidor.");
}

void setup() {
  Serial.begin(115200);
  pinMode(LED_PIN, OUTPUT);
  digitalWrite(LED_PIN, LOW);

  emon.voltage(35, vCalibration, 1.7);
  emon.current(34, currCalibration);

  connectToWiFi();
  connectToServer();

  Serial.println("Inicializado el Medidor de Energía IoT");
}

void loop() {
  mostrarDatosEnergeticos();
  delay(5000);
}

void mostrarDatosEnergeticos() {
  if (!client.connected()) {
    connectToServer();
  }

  emon.calcVI(20, 2000);
  float incrementoKWh = emon.apparentPower * (millis() - lastmillis) / 3600000000.0;
  kWh += incrementoKWh;
  lastmillis = millis();

  Serial.print("kWh: ");
  Serial.print(kWh, 5);
  Serial.println("kWh");

  if (kWh > UMBRAL_CONSUMO) {
    digitalWrite(LED_PIN, HIGH);
  } else {
    digitalWrite(LED_PIN, LOW);
  }

  // Enviar datos al servidor
  String data = "Vrms: " + String(emon.Vrms, 2) + " V, Irms: " + String(emon.Irms, 4) + " A, Potencia: " + String(emon.apparentPower, 4) + " W, kWh: " + String(kWh, 5) + " kWh";
  client.println(data);
}

