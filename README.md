# Student Attendance Management System Using Raspberry Pi
## [SAMS]

---

### Login Page
![Login Page](screenshots/login.PNG)

### Dashboard
![Dashboard](screenshots/Dashboard.PNG)

### Manage Majors
![Manage Majors](screenshots/majors.PNG)

### Manage Courses
![Manage Courses](screenshots/courses.PNG)

### Manage Students
![Manage Students](screenshots/manage_student.PNG)

### Manage Course Registration
![Manage Course Registration](screenshots/course_registration.PNG)

### Manage Reports
![Manage Reports](screenshots/report.PNG)

### Manage Timetable
![Manage Timetable](screenshots/manage_timetable.PNG)

### Manage Holidays
![Manage Holidays](screenshots/holidays.PNG)

### Manage Leave
![Manage Leave](screenshots/manage_leaves.PNG)

### Computer_Lab_Usage_Record
![Computer_Lab_Usage_Record](screenshots/computer_usage.PNG)

### System Architecture
![System Architecture](screenshots/system_architecture.jpg)

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

