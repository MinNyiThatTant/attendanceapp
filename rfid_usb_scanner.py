# pip install requests
import requests
import sys

# Dashboard ရဲ့ URL (သင့် Pi ရဲ့ IP သို့မဟုတ် localhost)
# Attendance အတွက်ဆိုရင် process_scan.php
# Computer Lab အတွက်ဆိုရင် computerUsageAjax.php ကို သုံးပါ
# URL = "http://localhost/attendanceapp/ajaxhandler/computerUsageAjax.php"

def start_scanner():
    print("--- RFID Scanner အဆင်သင့်ဖြစ်ပါပြီ ---")
    print("ကတ်ပြားကို Reader ပေါ်တင်ပါ...")
    
    try:
        while True:
            # USB Reader က ID ရိုက်ပြီး Enter ခေါက်လိုက်တာကို စောင့်ဖမ်းတာပါ
            rfid_uid = input("Scan UID: ").strip()
            
            if rfid_uid:
                print(f"UID {rfid_uid} ကို ပို့နေပါသည်...")
                
                # PHP ဆီကို Data ပို့ခြင်း
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