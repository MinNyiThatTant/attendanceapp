# Student Attendance Management System Using Raspberry Pi
## [SAMS]

HTML, CSS, JavaScript, PHP နှင့် MySQL တို့ကို အသုံးပြု၍ ရေးသားထားသော System တစ်ခု ဖြစ်သည်။

<<<<<<< HEAD
---

## System Screenshots

### Login Page
![Login Page](screenshots/login.PNG)

### Dashboard
![Dashboard](screenshots/dashboard.PNG)

### Student Management
![Student Management](screenshots/student_management.PNG)

### Add / Edit Student
![Add Edit Student](screenshots/add_student.PNG)

### Course Management
![Course Management](screenshots/course_management.PNG)

### Add / Edit Course
![Add Edit Course](screenshots/add_course.PNG)

### Take Attendance
![Take Attendance](screenshots/take_attendance.PNG)

### Daily Attendance Report
![Daily Report](screenshots/daily_report.PNG)

### Monthly Attendance Report
![Monthly Report](screenshots/monthly_report.PNG)

### RFID Card Reading
![RFID Reading](screenshots/rfid_reading.PNG)

### Circuit Diagram
![Circuit Diagram](screenshots/circuit_diagram.jpg)

### Hardware Setup
![Hardware Setup](screenshots/hardware_setup.PNG)

---

# Student Attendance Management System Using Raspberry Pi (SAMS)

> IoT-Based Student Attendance Management System using Raspberry Pi, RFID Card Reading, Web-Based Management, and Real-Time Attendance Tracking.

---

## Overview

Student Attendance Management System (SAMS) သည် ကျောင်းတစ်ကျောင်းအတွက် Attendance ခေါ်ယူခြင်းကို Digitalized ပြုလုပ်နိုင်ရန် IoT, Web Technologies နှင့်ပေါင်းစပ်တည်ဆောက်ထားသော စနစ်ဖြစ်ပါတယ်။

ကျောင်းသားများသည် RFID Card ကို Raspberry Pi နှင့်ချိတ်ဆက်ထားသော RFID Reader တွင် ကပ်ဖတ်ပြီး Attendance ကို အလိုအလျောက် မှတ်သွင်းနိုင်ပါတယ်။
ထို့နောက် Web Dashboard မှတစ်ဆင့် ကျောင်းသားများ၏ Attendance ကို နေ့စဉ်၊ လစဉ် Report များအဖြစ် ပြန်လည်ကြည့်ရှုနိုင်ပါတယ်။

Admin/Teacher ဘက်မှ ကျောင်းသားစာရင်းများ၊ ဘာသာရပ် (Course) များ၊ သင်တန်းအပ်နှံမှုများကို Web Dashboard မှတစ်ဆင့် စီမံခန့်ခွဲနိုင်ပါတယ်။

ထို့အပြင် Raspberry Pi အခြေပြု RFID Card Reading System ကို အသုံးပြုထားပြီး ကျောင်းသားများ၏ Card ကိုဖတ်သည်နှင့် သက်ဆိုင်ရာ Course အတွက် Attendance ကို အလိုအလျောက် မှတ်သွင်းပေးနိုင်ပါတယ်။

---

## System Workflow

### 1. Student Registration

* Admin/Teacher က Student Details ကို Web Dashboard မှ ထည့်သွင်းနိုင်ပါတယ်။
* ကျောင်းသားတစ်ဦးချင်းစီအတွက် Roll No, Name, Major, Current Semester နှင့် RFID Card ID သတ်မှတ်ပေးရပါမယ်။

### 2. Course Management

* Admin/Teacher က Course များကို ထည့်သွင်းနိုင်ပါတယ်။
* Course ခေါင်းစဉ်၊ Session/Term နှင့် Major အတိုင်း သတ်မှတ်ပေးရပါမယ်။

### 3. Course Assignment

* Major တစ်ခုချင်းစီအတွက် မည်သည့် Course များ သင်မည်ကို သတ်မှတ်ပေးရပါမယ်။

### 4. Attendance Marking (RFID)

* ကျောင်းသားက သူ၏ RFID Card ကို RFID Reader ပေါ်ကပ်ပါမယ်။
* Raspberry Pi က Card ID ကိုဖတ်ပြီး Database ရှိ ကျောင်းသားနှင့် ကိုက်ညီမှုရှိမရှိ စစ်ဆေးပါမယ်။
* ကိုက်ညီပါက ယနေ့ရက်စွဲအတွက် Attendance ကို မှတ်သွင်းပေးပါမယ်။

### 5. Attendance Report

* Admin/Teacher က Web Dashboard မှ Daily Report သို့မဟုတ် Monthly Report ကို ပြန်လည်ကြည့်ရှုနိုင်ပါတယ်။
* ကျောင်းသားတစ်ဦးချင်းစီ၏ Attendance ကို Filter လုပ်၍ ကြည့်ရှုနိုင်ပါတယ်။

---

## Admin / Teacher Features

### Student Management

* Add Students
* Update Students
* Delete Students
* Assign RFID Card ID
* View Student List

### Course Management

* Add Courses
* Update Courses
* Delete Courses
* Set Course Session/Term

### Course Assignment

* Assign Courses to Major
* Remove Course Assignment
* View Assigned Courses

### Attendance Management

* View Daily Attendance
* View Monthly Attendance
* Filter by Student
* Filter by Course
* Filter by Date Range

### Report Generation

* Daily Attendance Report
* Monthly Attendance Report
* Export to PDF (Optional)
* Print Report

### User Authentication

* Admin Login
* Teacher Login
* Secure Logout

---

## Student Features

* RFID Card Scanning
* Automatic Attendance Marking
* View Personal Attendance (if student access is enabled)

---

## Hardware Features

* RFID Card Reading (RC522)
* Real-Time Attendance Marking
* LCD Display for Status Messages
* Buzzer Notification for Successful/Failed Scan
* LED Indicators

---

## Technologies Used

### Software

* PHP (Native)
* MySQL
* HTML5
* CSS3
* JavaScript
* Bootstrap
* AJAX
* XAMPP / Apache

### Hardware

* Raspberry Pi (3B+ / 4)
* RFID Module (RC522)
* 16x2 LCD Display
* Buzzer
* LED (Red/Green)
* Jumper Wires
* Breadboard

---

## Database Structure

### Major Details Table (major_details)
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary Key |
| title | VARCHAR | Major Name (e.g., Computer Science) |

### Student Details Table (student_details)
| Column | Type | Description |
|--------|------|-------------|
| roll_no | VARCHAR | Primary Key |
| name | VARCHAR | Student Name |
| major_id | INT | Foreign Key (major_details.id) |
| current_semester | VARCHAR | Current Semester (e.g., 1, 2, 3) |
| rfid_uid | VARCHAR | RFID Card Unique ID |

### Session Details Table (session_details)
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary Key |
| title | VARCHAR | Session Name |
| term | VARCHAR | Term/Semester |

### Course Details Table (course_details)
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary Key |
| title | VARCHAR | Course Name |
| session_id | INT | Foreign Key (session_details.id) |

### Course Assignments Table (course_assignments)
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary Key |
| major_id | INT | Foreign Key (major_details.id) |
| course_id | INT | Foreign Key (course_details.id) |

### Attendance Table (attendance)
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary Key |
| roll_no | VARCHAR | Foreign Key (student_details.roll_no) |
| course_id | INT | Foreign Key (course_details.id) |
| date | DATE | Attendance Date |
| status | ENUM | Present / Absent |

---

## Installation Guide

### 1. Download Project

Download Project from GitHub
Go → Code → Download ZIP and extract folder

### 2. Transfer Project Folder

Download ရလာသော `attendanceapp` Folder ကို XAMPP ထည့်ထားသော လမ်းကြောင်းအောက်ရှိ `htdocs` ထဲသို့ ကူးထည့်ပါ။



### 3. Database Setup

1. **XAMPP Control Panel** ကိုဖွင့်ပြီး **Apache** နှင့် **MySQL** ကို Start လုပ်ပါ။
2. Browser တွင် `localhost/phpmyadmin` သို့သွားပါ။
3. Database အသစ်တစ်ခု တည်ဆောက်ပါ → `attendance_db`
4. Import tab သို့သွား၍ `database/attendance_db.sql` ဖိုင်ကို Import လုပ်ပါ။

### 4. Database Connection Configuration

`config/db_connect.php` ဖိုင်တွင် အောက်ပါအတိုင်း Host, User, Password နှင့် Database အမည်များ မှန်/မမှန် စစ်ဆေးပါ။

```php
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance_db";
?>
```

### License

This project is developed for educational and research purposes only.
All Rights Reserved.

=======
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
>>>>>>> a6f12bd2dc1a721daf2ab19aa32c6ebecb9f77a8
