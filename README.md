# Student Attendance System Using Raspberry Pi

PHP, HTML, CSS, JavaScript နှင့် MySQL တို့ကို အသုံးပြု၍ ရေးသားထားသော System တစ်ခု ဖြစ်သည်။

## ✨ Features
* ကျောင်းသားစာရင်းများ ထည့်သွင်းခြင်း/ပြင်ဆင်ခြင်း။
* နေ့စဉ် Attendance ခေါ်ယူခြင်း။
* Attendance Report များကို ပြန်လည်ကြည့်ရှုခြင်း။
* User Authentication (Login/Logout)။

## 🛠 Techniques
* **Frontend:** HTML5, CSS3, JavaScript
* **Backend:** PHP (Native) with xampp
* **Database:** MySQL

## 🚀 Installation 

In your computer

### 1. Download Project
Download Project from Github
Go => Code => download zip and extract folder


### ၂။ Transfer Project Folder 
Download ရလာသော `attendanceapp` Folder ကို XAMPP ထည့်ထားသော လမ်းကြောင်းအောက်ရှိ `htdocs` ထဲသို့ ကူးထည့်ပါ။
(ဥပမာ - `C:\xampp\htdocs\attendanceapp`)

### ၃။ Database 
1.  **XAMPP Control Panel** ကိုဖွင့်ပြီး **Apache** နှင့် **MySQL** ကို Start လုပ်ပါ။
2.  Browser တွင် `localhost/phpmyadmin` သို့သွားပါ။

3.  Database အသစ်တစ်ခု တည်ဆောက်ပါ (အမည်: `attendance_db`)။


### ၄။ Database ချိတ်ဆက်မှု စစ်ဆေးခြင်း
database connection ဖိုင်တွင် အောက်ပါအတိုင်း Host, User, Password နှင့် Database အမည်များ မှန်/မမှန် စစ်ဆေးပါ။

```php
<?php
$servername = "localhost"; // may be sometime you should put 172.0.0.1:3306 or 172.0.0.1:3307 (depand on port of xampp you used)
$username = "root";
$password = "";
$dbname = "attendance_db";
```

### ၅။ Raspberry Pi and RFID Card ဖြင့် ချိတ်ဆက်မှုကို ဆက်လက်ဖော်ပြပါမည်။ (comming soon)
