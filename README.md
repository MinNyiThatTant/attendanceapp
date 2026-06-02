# Student Attendance Management System Using Raspberry Pi
## [SAMS]
HTML, CSS, JavaScript, PHP နှင့် MySQL တို့ကို အသုံးပြု၍ ရေးသားထားသော System တစ်ခု ဖြစ်သည်။

## Features
* ကျောင်းသားစာရင်းများ ထည့်သွင်းခြင်း/ပြင်ဆင်ခြင်း။
* Courses များ ထည့်သွင်းခြင်း/ပြင်ဆင်ခြင်း။
* နေ့စဉ် Attendance ခေါ်ယူခြင်း။
* Attendance Report များကို ပြန်လည်ကြည့်ရှုခြင်း။ (Daily, Monthly)
* User Authentication (Login/Logout)။

## Techniques
# Softwares

* **Frontend:** HTML5, CSS3, JavaScript
* **Backend:** PHP (Native) with xampp
* **Database:** MySQL

# Hardwares
* **Raspberrypi Model-B**
* **RFID(MFRC522)**

### (i) System Configuration
```bash
sudo raspi-config
```

### (ii) System Update
```
sudo apt update
```

### (iii) Install Required Packages
```
sudo apt install xrdp -y
sudo apt install apache2 -y
sudo apt install mariadb-server -y
```
### (iv) check browser with IP for Apache Debian

### (v) Database Security Setup
```
sudo mysql_secure_installation
```
#### OR
```
sudo mariadb-secure-installation
```

### (vi) PHP & phpMyAdmin Installation
```
sudo apt install php libapache2-mod-php php-mysql -y
sudo apt install phpmyadmin -y
```

### (vii) Set Directory Permissions
```
sudo chown -R $USER:$USER /var/www/html/
sudo chmod -R 755 /var/www/html/
```

### (viii) Root file manager access
```
sudo pcmanfm
```

### (xi) Project Permissions
```
sudo chown -R www-data:www-data /var/www/html/projectname
sudo chmod -R 755 /var/www/html/projectname
sudo chmod 666 /dev/input/event4
```

### (x) Python3 & pip3 Installation
```
sudo apt update
```
#### Method 1: Install python3-evdev
```
sudo apt install python3-evdev
```
#### OR
```
sudo apt-get install python3-evdev
```
#### Method 2: Install Python packages
```
sudo apt install python3-pip python3-tk python3-dev sdotool -y
sudo pip3 install requests evdev
```

### (xi) Myanmar Font & Input Libraries
#### Install pyautogui
```
sudo apt-get install python3-pyautogui
```
#### OR enable uinput module
```
sudo modprobe uinput
```

### (xii) Install Python input libraries
```
pip install pynput --break-system-packages
pip install pyautogui --break-system-packages
```

### (xiii) Identify Card Reader on Raspberry pi
```
lsusb
cat /proc/bus/input/devices
ls /dev/input/by-id/
cat /dev/input/by-id/usb-13ba_Barcode_Reader-event-kbd
003:ID
13ba:0018 PCPlay Barcode PCP-BCG4209
```

### (xiv) Config RFID udev rule
```
sudo nano /etc/udev/rules.d/99-rfid.rules
SUBSYSTEM=="input", ATTRS{idVendor}=="13ba", ATTRS{idProduct}=="0018", MODE="0666"
sudo udevadm control --reload-rules && sudo udevadm trigger
```

### (xv) Remote Connection
```
ssh raspberrypi@raspberrypi.local
```
##### (or)
```
ssh raspberrypi@0.0.0.0
```
#### in Run Box
```
mstsc
```

### (xvi) Auto-start Browser (optional)
```
mkdir -p /home/pi/.config/lxsession/LXDE-pi/
nano /home/pi/.config/lxsession/LXDE-pi/autostart
@chromium --kiosk http://localhost/projectname/login.php
@chromium http://localhost/projectname/login.php --start-fullscreen
```

### checking and diagnosis query
```sql
SELECT 
    s.roll_no, 
    s.name as student_name, 
    m.title as major_name, 
    s.current_semester,
    cd.title as course_name,
    sd.term as course_semester
FROM student_details s
JOIN major_details m ON s.major_id = m.id
JOIN course_assignments ca ON m.id = ca.major_id
JOIN course_details cd ON ca.course_id = cd.id
JOIN session_details sd ON cd.session_id = sd.id
WHERE s.roll_no LIKE '%MC%' 
  AND s.current_semester COLLATE utf8mb4_general_ci = sd.term COLLATE utf8mb4_general_ci 
LIMIT 0, 25;
```

## License

This project is developed for educational and research purposes.
