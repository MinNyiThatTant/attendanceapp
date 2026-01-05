import RPi.GPIO as GPIO
from mfrc522 import SimpleMFRC522
import requests
import time

reader = SimpleMFRC522()
# Change this to your server's IP address or domain
API_ENDPOINT = "http://192.168.1.100/attendance_system/process_scan.php"

print("Attendance System: Ready. Scan your ID card...")

try:
    while True:
        id, text = reader.read()
        print(f"Scanned UID: {id}")
        
        try:
            # send POST request to server
            data = {'rfid_uid': str(id)}
            response = requests.post(API_ENDPOINT, data=data)
            
            if response.status_code == 200:
                result = response.json()
                if result.get('success'):
                    print(f"✅ SUCCESS: {result['name']} ({result['message']})")
                else:
                    print(f"❌ FAILED: {result.get('message')}")
            else:
                print("❌ Server Error")
        except Exception as e:
            print(f"⚠️ Network Error: {e}")
            
        time.sleep(2) # delay before next scan

finally:
    GPIO.cleanup()