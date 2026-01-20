import RPi.GPIO as GPIO
from mfrc522 import SimpleMFRC522
import urllib.request

reader = SimpleMFRC522()

try:
    print("Waiting for RFID chip...")
    while True:
        id, text = reader.read()
        uid = str(id)
        print("Card Detected: " + uid)
        
        # send to Web App API (Change IP to Pi's IP)
        url = "http://192.168.1.100/attendanceapp/rfid_handler.php?uid=" + uid
        response = urllib.request.urlopen(url).read()
        print(response.decode('utf-8'))
finally:
    GPIO.cleanup()