# Student Attendance Management System Using Raspberry Pi
## [SAMS]
HTML, CSS, JavaScript, PHP နှင့် MySQL တို့ကို အသုံးပြု၍ ရေးသားထားသော System တစ်ခု ဖြစ်သည်။

## ✨ Features
* ကျောင်းသားစာရင်းများ ထည့်သွင်းခြင်း/ပြင်ဆင်ခြင်း။
* Courses များ ထည့်သွင်းခြင်း/ပြင်ဆင်ခြင်း။
* နေ့စဉ် Attendance ခေါ်ယူခြင်း။
* Attendance Report များကို ပြန်လည်ကြည့်ရှုခြင်း။ (Daily, Monthly)
* User Authentication (Login/Logout)။

## 🛠 Techniques
* **Frontend:** HTML5, CSS3, JavaScript
* **Backend:** PHP (Native) with xampp
* **Database:** MySQL

## 🚀 Installation 

In your computer

### Download Project
Download Project from Github
Go => Code => download zip and extract folder


### Transfer Project Folder 
Download ရလာသော `attendanceapp` Folder ကို XAMPP ထည့်ထားသော လမ်းကြောင်းအောက်ရှိ `htdocs` ထဲသို့ ကူးထည့်ပါ။
(ဥပမာ - `C:\xampp\htdocs\attendanceapp`)

### Database 
1.  **XAMPP Control Panel** ကိုဖွင့်ပြီး **Apache** နှင့် **MySQL** ကို Start လုပ်ပါ။
2.  Browser တွင် `localhost/phpmyadmin` သို့သွားပါ။

3.  Database အသစ်တစ်ခု တည်ဆောက်ပါ (`attendance_db`)။


### Database ချိတ်ဆက်မှု စစ်ဆေးခြင်း
database connection ဖိုင်တွင် အောက်ပါအတိုင်း Host, User, Password နှင့် Database အမည်များ မှန်/မမှန် စစ်ဆေးပါ။

```php
<?php
$servername = "localhost"; // may be sometime you should put 172.0.0.1:3306 or 172.0.0.1:3307 (depand on port of xampp you used)
$username = "root";
$password = "";
$dbname = "attendance_db";
```

### Raspberry Pi Apache Server Configuration

hostname : name
username : name
password : name


---

### 1. System Configuration
```bash
sudo raspi-config
```

### System Update
```
sudo apt update
```

### Install Required Packages
```
sudo apt install xrdp -y
sudo apt install apache2 -y
sudo apt install mariadb-server -y
```
### check browser with IP for Apache Debian

### Database Security Setup
```
sudo mysql_secure_installation
```
#### OR
```
sudo mariadb-secure-installation
```

### PHP & phpMyAdmin Installation
```
sudo apt install php libapache2-mod-php php-mysql -y
sudo apt install phpmyadmin -y
```

### default login
username : root
pass     : admin

### Set Directory Permissions
```
sudo chown -R $USER:$USER /var/www/html/
sudo chmod -R 755 /var/www/html/
```

### Root file manager access
```
sudo pcmanfm
```

### Project Permissions
```
sudo chown -R www-data:www-data /var/www/html/projectname
sudo chmod -R 755 /var/www/html/projectname
sudo chmod 666 /dev/input/event4
```

### Python3 & pip3 Installation
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

### Myanmar Font & Input Libraries
#### Install pyautogui
```
sudo apt-get install python3-pyautogui
```
#### OR enable uinput module
```
sudo modprobe uinput
```

### Install Python input libraries
```
pip install pynput --break-system-packages
pip install pyautogui --break-system-packages
```

### Identify Card Reader on Raspberry pi
```
lsusb
cat /proc/bus/input/devices
ls /dev/input/by-id/
cat /dev/input/by-id/usb-13ba_Barcode_Reader-event-kbd
003:ID
13ba:0018 PCPlay Barcode PCP-BCG4209
```

### Config RFID udev rule
```
sudo nano /etc/udev/rules.d/99-rfid.rules
SUBSYSTEM=="input", ATTRS{idVendor}=="13ba", ATTRS{idProduct}=="0018", MODE="0666"
sudo udevadm control --reload-rules && sudo udevadm trigger
```

### Auto-start Browser (optional)
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
