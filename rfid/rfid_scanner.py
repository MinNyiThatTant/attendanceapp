import RPi.GPIO as GPIO
from mfrc522 import SimpleMFRC522
import requests
import time

reader = SimpleMFRC522()
# Server API URL, server IP address 
API_URL = "http://192.168.1.100/attendance/process_scan.php" 

print("System Ready! Waiting for RFID Card...")

try:
    while True:
        id, text = reader.read()
        print(f"Card Detected: {id}")
        
        # send POST request to server
        try:
            payload = {'rfid_uid': str(id)}
            response = requests.post(API_URL, data=payload)
            result = response.json()
            
            if result['success']:
                print(f"✅ Success: {result['name']} - {result['message']}")
            else:
                print(f"❌ Error: {result['message']}")
                
        except Exception as e:
            print(f"Network Error: {e}")
            
        time.sleep(2) 

finally:
    GPIO.cleanup()