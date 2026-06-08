# Student Attendance Management System Using Raspberry Pi [SAMS]

IoT-Based Student Attendance Management System using Raspberry Pi, RFID Card Reading, Web-Based Management, and Real-Time Attendance Tracking.

---

### Login Page
![Login Page](screenshots/login.PNG)

### Dashboard
![Dashboard](screenshots/Dash-board.PNG)

### Manage Majors
![Manage Majors](screenshots/manage_majors.PNG)

### Manage Courses
![Manage Courses](screenshots/manage_courses.PNG)

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

## Overview

Student Attendance Management System (SAMS) သည် နည်းပညာတက္ကသိုလ်များအတွက် Attendance ခေါ်ယူခြင်းကို Digitalized ပြုလုပ်နိုင်ရန် IoT, Web Technologies နှင့်ပေါင်းစပ်တည်ဆောက်ထားသော စနစ်ဖြစ်ပါတယ်။

ကျောင်းသားများသည် RFID Card ကို Raspberry Pi နှင့်ချိတ်ဆက်ထားသော RFID Reader တွင် ကပ်ဖတ်ပြီး Attendance ကို အလိုအလျောက် စာရင်းသွင်းနိုင်ပါတယ်။
ပြီးလျှင် Web Dashboard မှတစ်ဆင့် ကျောင်းသားများ၏ Attendance ကို နေ့စဉ်၊ လစဉ် Report များအဖြစ် ပြန်လည်ကြည့်ရှုနိုင်ပါတယ်။

Admin ဘက်မှ ကျောင်းသားစာရင်းများ၊ ဘာသာရပ် (Course_registration) များ၊ Web Dashboard မှတစ်ဆင့် စီမံခန့်ခွဲနိုင်ပါတယ်။

ထို့အပြင် Raspberry Pi အခြေပြု RFID Card Reading System ကို အသုံးပြုထားပြီး ကျောင်းသားများ၏ Card ကိုဖတ်သည်နှင့် သက်ဆိုင်ရာ Course/Class အတွက် Attendance ကို အလိုအလျောက် မှတ်သွင်းပေးနိုင်ပါတယ်။

---

## System Workflow

### 1. Student Registration

* Admin က Student Details ကို Web Dashboard မှ ထည့်သွင်းနိုင်ပါတယ်။
* ကျောင်းသားတစ်ဦးချင်းစီအတွက် Roll No, Name, Major, Current Semester နှင့် RFID Card ID သတ်မှတ်ပေးရပါမယ်။ ဒီနေရာမှာ csv file အနေနှင့် ထည့်လို့လည်း ရပါတယ်။

### 2. Course Management

* Admin က Course များကို ထည့်သွင်းနိုင်ပါတယ်။
* Course ခေါင်းစဉ်၊ Session/Term နှင့် Major အတိုင်း သတ်မှတ်ပေးရပါမယ်။

### 3. Course Assignment

* Major တစ်ခုချင်းစီအတွက် မည်သည့် Course များ သင်မည်ကို သတ်မှတ်ပေးရပါမယ်။

### 4. Attendance Marking (RFID)

* ကျောင်းသားက သူ၏ RFID Card ကို RFID Reader ပေါ်ကပ်ပါမယ်။
* Raspberry Pi က Card ID ကိုဖတ်ပြီး Database ရှိ ကျောင်းသားနှင့် ကိုက်ညီမှုရှိမရှိ စစ်ဆေးပါမယ်။
* ကိုက်ညီပါက ယနေ့ရက်စွဲအတွက် Attendance ကို မှတ်သွင်းပေးပါမယ်။

### 5. Attendance Report

* Admin က Web Dashboard မှ Daily Report သို့မဟုတ် Monthly Report ကို ပြန်လည်ကြည့်ရှုနိုင်ပါတယ်။
* ကျောင်းသားတစ်ဦးချင်းစီ၏ Attendance ကို Filter လုပ်၍ ကြည့်ရှုနိုင်ပါတယ်။

---

## Admin Features

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

### Report 

* Daily Attendance Report
* Monthly Attendance Report
* Export to excel

### User Authentication

* Admin Login
* Teacher Login ( Coming...)
* Student Login ( Coming...)

---

## Student Features

* RFID Card Scanning
* Automatic Attendance Marking

---

## Hardware Features

* RFID Card Reader
* Real-Time Attendance Marking
* Sound noti for Attendance Status 

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
* XAMPP / Apache (web server)
* python (for UID)

### Hardware

* Raspberry Pi (3B+ / 4)
* RFID Module
* Laptop (admin can manage with wifi)

---

### License

This project is developed for educational and research purposes only.
All Rights Reserved.

The source code, documentation, design, and related materials များကို လေ့လာရန်အတွက် ကြည့်ရှုခွင့်ရှိသည်။
မည်သည့်အပိုင်းကိုမျှ မိတ္တူကူးခြင်း၊ ပြင်ဆင်ခြင်း၊ ပြန်လည်ဖြန့်ချိခြင်း၊ ထုတ်ဝေခြင်း သို့မဟုတ် စီးပွားဖြစ်အသုံးပြုခြင်း မပြုရ။
ခွင့်ပြုချက်မရှိဘဲ အသုံးပြုပါက တားမြစ်သည်။
