<?php
session_start();
require_once 'database/database.php';
if (empty($_SESSION["current_user"])) {
    header('Location: login.php');
    exit;
}

$db = new Database();

// reterive majors
$stmt = $db->conn->prepare("SELECT * FROM major_details");
$stmt->execute();
$majors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// default semester
$sem_stmt = $db->conn->query("SELECT term FROM session_details ORDER BY id ASC LIMIT 1");
$default_sem = $sem_stmt->fetchColumn() ?: '1st semester';

// dashboard.php (PHP Block ထဲတွင် ထည့်ရန်)

// ၁။ ဒီနေ့အတွက် စုစုပေါင်း Attendance အရေအတွက် (Unique Students)
$today_date = date('Y-m-d');
$stmt_present = $db->conn->prepare("SELECT COUNT(DISTINCT student_id) FROM attendance_details WHERE on_date = ?");
$stmt_present->execute([$today_date]);
$total_present = $stmt_present->fetchColumn();

// ၂။ ဒီနေ့အတွက် သင်ကြားရမည့် စုစုပေါင်း ဘာသာရပ်အရေအတွက် (Timetable မှ)
$today_day = date('l');
$stmt_classes = $db->conn->prepare("SELECT COUNT(*) FROM timetable WHERE day_of_week = ?");
$stmt_classes->execute([$today_day]);
$total_classes = $stmt_classes->fetchColumn();

?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard - Attendance System</title>
    <link rel="stylesheet" href="css/attendance.css">
    <style>
        .card:hover {
            border-color: #4f46e5 !important;
            transform: translateY(-3px);
            cursor: pointer;
        }

        .main-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card" style="margin-bottom:20px; display:flex; justify-content: space-between; align-items: center;">
            <div style="display:flex; gap:10px;">
                <a href="manage_majors.php" class="class-btn" style="text-decoration:none; background:lightblue;">⚙️ Majors</a>
                <a href="manage_courses.php" class="class-btn" style="text-decoration:none; background:lightblue;">📚 Courses</a>
                <a href="manage_students.php" class="class-btn" style="text-decoration:none; background:lightblue;">👨‍🎓 Students</a>
                <a href="manage_registration.php" class="class-btn" style="text-decoration:none; background:lightblue;">📝 Registration</a>
                <a href="attendance_report.php" class="class-btn" style="text-decoration:none; background:lightblue;">📊 Attendance Report</a>
                <a href="manage_timetable.php" class="class-btn" style="text-decoration:none; background:lightblue;">📅 Manage Timetable</a>
            </div>
            <button class="logout-btn" id="btnlogout">Logout</button>
        </div>

        <header class="attendance-header">
            <h1>Select <span style="color:#4f46e5">Major</span> to Take Attendance</h1>
        </header>


        <div id="scan-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; justify-content:center; align-items:center;">
            <div class="card" style="width:400px; text-align:center; padding:30px; border-radius:20px; background:white;">
                <div id="scan-status" style="color:#10b981; font-weight:bold; margin-bottom:15px; font-size:1.2rem;">✅ Attendance Marked!</div>
                <img id="st-photo" src="assets/img/students/default.png" style="width:180px; height:180px; border-radius:50%; border:5px solid #4f46e5; object-fit:cover; margin-bottom:20px;">
                <h2 id="st-name" style="margin:0;">Student Name</h2>
                <p id="st-roll" style="color:#6b7280; font-size:1.2rem;">Roll No: 001</p>
            </div>
        </div>

        <div class="summary-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div class="card" style="border-left: 5px solid #10b981; background: #f0fdf4;">
                <div style="color: #065f46; font-size: 0.9rem; font-weight: bold; text-transform: uppercase;">Total Present (Today)</div>
                <div style="font-size: 2rem; font-weight: bold; color: #047857; margin-top: 5px;"><?= $total_present ?></div>
            </div>

            <div class="card" style="border-left: 5px solid #4f46e5; background: #f5f3ff;">
                <div style="color: #3730a3; font-size: 0.9rem; font-weight: bold; text-transform: uppercase;">Classes Today</div>
                <div style="font-size: 2rem; font-weight: bold; color: #4338ca; margin-top: 5px;"><?= $total_classes ?></div>
            </div>

            <div class="card" style="border-left: 5px solid #0891b2; background: #ecfeff;">
                <div style="color: #155e75; font-size: 0.9rem; font-weight: bold; text-transform: uppercase;">Server Status</div>
                <div style="font-size: 1.1rem; font-weight: bold; color: #0891b2; margin-top: 10px;">
                    <span style="display: inline-block; width: 10px; height: 10px; background: #10b981; border-radius: 50%; margin-right: 5px;"></span>
                    Live & Monitoring
                </div>
            </div>
        </div>

        <div class="main-grid">
            <?php foreach ($majors as $m): ?>
                <div onclick="goToAttendance(<?= $m['id'] ?>, '<?= addslashes($m['title']) ?>')" class="card" style="text-align:center; padding: 40px 20px; border: 2px solid #e5e7eb; border-radius: 10px;">
                    <div style="font-size: 1.3rem; color: #1f2937; font-weight:bold;"><?= htmlspecialchars($m['title']) ?></div>
                    <div style="font-size: 0.85rem; color: #6b7280; margin-top: 10px;">Click to select subjects</div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card" style="margin-top: 30px;">
            <h3>📡 Live Attendance Log (Today)</h3>
            <table class="student-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Period</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // ဒီနေ့အတွက် နောက်ဆုံး Tap လုပ်ထားတဲ့ ၅ ယောက်ကို ပြပါ
                    $logs = $db->conn->query("SELECT a.*, s.name, c.title as course_name 
                                     FROM attendance_details a 
                                     JOIN student_details s ON a.student_id = s.id 
                                     JOIN course_details c ON a.course_id = c.id 
                                     WHERE a.on_date = CURDATE() 
                                     ORDER BY a.id DESC LIMIT 5")->fetchAll();
                    foreach ($logs as $log):
                    ?>
                        <tr>
                            <td><?= date('h:i A') ?></td>
                            <td><strong><?= $log['name'] ?></strong></td>
                            <td><?= $log['course_name'] ?></td>
                            <td>Period <?= $log['period'] ?></td>
                            <td><span style="color: #10b981;">✅ Checked In</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card" style="margin-top: 20px;">
            <h3>📡 Live Attendance Log (Real-time)</h3>
            <div id="live-logs">
                <table class="student-table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Course</th>
                            <th>Period</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody id="attendance-body">
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card" style="margin-top: 20px; display: flex; align-items: center; justify-content: space-around;">
            <div style="width: 300px;">
                <canvas id="todayAttendanceChart"></canvas>
            </div>
            <div style="flex: 1; padding-left: 40px;">
                <h3>Today's Summary</h3>
                <p>အခုလက်ရှိ အတန်းတက်ရောက်မှု အခြေအနေကို အောက်ပါ Chart တွင် ကြည့်ရှုနိုင်ပါသည်။</p>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const ctxToday = document.getElementById('todayAttendanceChart').getContext('2d');
            new Chart(ctxToday, {
                type: 'doughnut',
                data: {
                    labels: ['Present', 'Remaining'],
                    datasets: [{
                        // total_present နဲ့ ကျန်တဲ့အရေအတွက်ကို တွက်ပြမယ်
                        data: [<?= $total_present ?>, <?= (100 - $total_present) ?>], // 100 နေရာမှာ Total Students variable ထည့်နိုင်ပါတယ်
                        backgroundColor: ['#10b981', '#e5e7eb'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    cutout: '70%'
                }
            });
        </script>

        <script>
            function fetchLogs() {
                fetch('fetch_live_attendance.php')
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('attendance-body').innerHTML = data;
                    });
            }

            // ၃ စက္ကန့်တိုင်း တစ်ခါ အလိုအလျောက် data သွားဆွဲမည်
            setInterval(fetchLogs, 3000);
            window.onload = fetchLogs;
        </script>
    </div>
    <script src="js/logout.js"></script>
    <script>
        function goToAttendance(id, name) {
            const defaultSem = "<?= urlencode($default_sem) ?>";
            window.location.href = `attendance.php?major_id=${id}&major_name=${encodeURIComponent(name)}&semester=${defaultSem}`;
        }
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="js/logout.js"></script>


    <script>
        let rfid_buffer = "";
        document.addEventListener('keypress', function(e) {
            // Enter ခေါက်လိုက်ရင် (RFID Reader က နောက်ဆုံးမှာ Enter ခေါက်လေ့ရှိပါတယ်)
            if (e.key === 'Enter') {
                if (rfid_buffer.length > 0) {
                    submitScan(rfid_buffer);
                    rfid_buffer = ""; // reset buffer
                }
            } else {
                rfid_buffer += e.key;
            }
        });

        function submitScan(uid) {
            let formData = new FormData();
            formData.append('rfid_uid', uid);

            fetch('process_scan.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Overlay မှာ အချက်အလက်တွေ ဖြည့်မယ်
                        document.getElementById('st-photo').src = 'assets/img/students/' + data.photo;
                        document.getElementById('st-name').innerText = data.name;
                        document.getElementById('st-roll').innerText = 'Roll No: ' + data.roll_no;

                        // Overlay ပြမယ်
                        document.getElementById('scan-overlay').style.display = 'flex';

                        // ၃ စက္ကန့်နေရင် ပြန်ဖျောက်မယ်
                        setTimeout(() => {
                            document.getElementById('scan-overlay').style.display = 'none';
                            fetchLogs(); // အနောက်က table list ကို refresh လုပ်မယ်
                        }, 3000);
                    } else {
                        alert(data.message);
                    }
                });
        }
    </script>
</body>

</html>