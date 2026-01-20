# pip install requests
import requests
import sys

# URL = "http://localhost/attendanceapp/ajaxhandler/computerUsageAjax.php"

def start_scanner():
    print("--- RFID Scanner အဆင်သင့်ဖြစ်ပါပြီ ---")
    print("ကတ်ပြားကို Reader ပေါ်တင်ပါ...")
    
    try:
        while True:
            # Simulate scanning by user input
            rfid_uid = input("Scan UID: ").strip()
            
            if rfid_uid:
                print(f"UID {rfid_uid} ကို ပို့နေပါသည်...")
                
                # send POST request to server
                data = {'rfid_uid': rfid_uid}
                response = requests.post(URL, data=data)
                
                if response.status_code == 200:
                    print("ပို့ပြီးပါပြီ: ", response.text)
                else:
                    print("Error: Server သို့ ချိတ်ဆက်၍မရပါ")
                    
    except KeyboardInterrupt:
        print("\nScanner ပိတ်လိုက်ပါပြီ။")

if __name__ == "__main__":
    start_scanner()