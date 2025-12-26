<?php
session_start();
require_once 'database/database.php';
$db = new Database();

$courses = $db->conn->query("SELECT id, title, code FROM course_details")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Real-time Attendance | Scan Card</title>
    <link rel="stylesheet" href="css/attendance.css">
    <style>
        .scan-container { text-align: center; padding: 50px 20px; }
        .rfid-input { opacity: 0; position: absolute; } /* Input ကို ဖျောက်ထားမယ် */
        .scan-visual { 
            width: 300px; height: 300px; border: 5px dashed #4f46e5; 
            border-radius: 50%; margin: 0 auto 30px;
            display: flex; align-items: center; justify-content: center;
            flex-direction: column; background: #f5f3ff;
            transition: 0.3s;
        }
        .scan-visual.active { border-color: #10b981; background: #ecfdf5; transform: scale(1.05); }
        .student-info-card { 
            max-width: 400px; margin: 20px auto; padding: 20px; 
            border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            display: none; background: white; border-top: 5px solid #10b981;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="attendance-header">
            <h1>Take <span style="color:#4f46e5">Attendance</span></h1>
            <a href="dashboard.php" class="class-btn" style="text-decoration:none;">⬅ Back</a>
        </header>

        <div class="card" style="margin-bottom: 20px;">
            <label>Select Current Subject/Class:</label>
            <select id="current_course" class="input-box" style="font-size: 1.2rem; padding: 15px;">
                <?php foreach($courses as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= $c['code'] ?> - <?= $c['title'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="scan-container">
            <input type="text" id="rfid_field" class="rfid-input" autofocus>
            
            <div class="scan-visual" id="visual_box">
                <span style="font-size: 5rem;">🪪</span>
                <p id="status_text" style="font-weight: bold; color: #4f46e5;">Ready to Scan...</p>
            </div>

            <div id="student_card" class="student-info-card">
                <h2 id="st_name" style="margin:0; color: #111827;">Student Name</h2>
                <p id="st_roll" style="color: #6b7280; font-size: 1.1rem; margin: 5px 0;">Roll No</p>
                <div style="background: #ecfdf5; color: #059669; padding: 10px; border-radius: 8px; font-weight: bold; margin-top: 15px;">
                    ✅ Attendance Marked Successfully!
                </div>
            </div>
        </div>
    </div>

    <script>
        const rfidField = document.getElementById('rfid_field');
        const visualBox = document.getElementById('visual_box');
        const statusText = document.getElementById('status_text');

        // အမြဲတမ်း focus ဖြစ်နေအောင် (Card ဖတ်ရင် input ထဲ တန်းရောက်ဖို့)
        document.addEventListener('click', () => rfidField.focus());

        rfidField.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') { // RFID scanner တွေက ဖတ်ပြီးရင် Enter ခေါက်လေ့ရှိတယ်
                const uid = this.value;
                const courseId = document.getElementById('current_course').value;
                processAttendance(uid, courseId);
                this.value = ''; // ချက်ချင်းပြန်ရှင်းမယ်
            }
        });

        async function processAttendance(uid, courseId) {
            visualBox.classList.add('active');
            statusText.innerText = "Processing...";

            try {
                // AJAX နဲ့ server ဆီ ပို့မယ် (ဒီဖိုင်ကို အောက်မှာ ရေးပေးပါမယ်)
                const response = await fetch('process_scan.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `rfid_uid=${uid}&course_id=${courseId}`
                });

                const data = await response.json();

                if (data.success) {
                    document.getElementById('st_name').innerText = data.name;
                    document.getElementById('st_roll').innerText = data.roll_no;
                    document.getElementById('student_card').style.display = 'block';
                    statusText.innerText = "Success!";
                    
                    // ၃ စက္ကန့်နေရင် ပျောက်သွားမယ်
                    setTimeout(() => {
                        document.getElementById('student_card').style.display = 'none';
                        visualBox.classList.remove('active');
                        statusText.innerText = "Ready to Scan...";
                    }, 3000);
                } else {
                    alert(data.message || "Card not recognized!");
                    visualBox.classList.remove('active');
                    statusText.innerText = "Ready to Scan...";
                }
            } catch (error) {
                console.error("Error:", error);
            }
        }
    </script>
</body>
</html>