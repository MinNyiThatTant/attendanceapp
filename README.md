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
4.  **Import** tab ကိုနှိပ်ပြီး Project ထဲတွင် ပါဝင်သော `.sql` ဖိုင် (ဥပမာ- `database/attendance_db.sql`) ကို ရွေးချယ်ကာ **Go** ကို နှိပ်ပါ။


### ၄။ Database ချိတ်ဆက်မှု စစ်ဆေးခြင်း
`config.php` သို့မဟုတ် database connection ဖိုင်တွင် အောက်ပါအတိုင်း Host, User, Password နှင့် Database အမည်များ မှန်/မမှန် စစ်ဆေးပါ။

```php
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance_db";

// Connection တည်ဆောက်ခြင်း
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Connection စစ်ဆေးခြင်း
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
```


### ၅။ Raspberry Pi and RFID Card ဖြင့် ချိတ်ဆက်မှုကို ဆက်လက်ဖော်ပြပါမည်။ (comming soon)
