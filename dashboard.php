<?php
session_start();
require_once 'database/database.php';

// Check user session
if (empty($_SESSION["current_user"])) {
    header('Location: login.php');
    exit;
}

$db = new Database();

// Attendance Shortage Logic (Percentage < 100%)
// Test query to find students with attendance percentage below 100%
$shortage_sql = "SELECT s.roll_no, s.name, c.title AS course_name,
                (COUNT(CASE WHEN ad.status = 'Present' THEN 1 END) * 100 / 
                 NULLIF(COUNT(ad.id), 0)) AS percentage
                FROM student_details s
                JOIN attendance_details ad ON s.id = ad.student_id
                JOIN course_details c ON ad.course_id = c.id
                GROUP BY s.id, ad.course_id
                HAVING percentage < 100";

$shortage_list = $db->conn->query($shortage_sql)->fetchAll(PDO::FETCH_ASSOC);

// Date & Academic Year Info
$today_day = date('l'); 
$today = date('Y-m-d');
$current_month = (int)date('m');
$current_year = (int)date('Y');

if ($current_month < 6) {
    $current_academic_year = ($current_year - 1) . "-" . $current_year;
} else {
    $current_academic_year = $current_year . "-" . ($current_year + 1);
}

// Status Checkers
// Holiday Check
$check_h = $db->conn->prepare("SELECT description FROM holidays WHERE holiday_date = ?");
$check_h->execute([$today]);
$holiday_info = $check_h->fetch();

// Students Present Today
$total_present_sql = "SELECT COUNT(DISTINCT student_id) FROM attendance_details 
                      WHERE on_date = CURDATE() AND status = 'Present'";
$total_present = $db->conn->query($total_present_sql)->fetchColumn() ?: 0;

// Today Leaves
$today_leaves_stmt = $db->conn->prepare("SELECT COUNT(*) FROM student_leaves WHERE ? BETWEEN from_date AND to_date");
$today_leaves_stmt->execute([$today]);
$total_leaves = $today_leaves_stmt->fetchColumn() ?: 0;

// Today Classes Count
$stmt_classes_count = $db->conn->prepare("SELECT COUNT(*) FROM timetable WHERE day_of_week = ? AND academic_year = ?");
$stmt_classes_count->execute([$today_day, $current_academic_year]);
$total_classes = $stmt_classes_count->fetchColumn() ?: 0;

// Majors List
$majors = $db->conn->query("SELECT * FROM major_details")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Attendance System</title>
    <link rel="stylesheet" href="css/attendance.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card:hover { border-color: #4f46e5 !important; transform: translateY(-3px); transition: 0.3s; cursor: pointer; }
        .main-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px; }
        #scan-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.85); z-index: 9999; justify-content: center; align-items: center; backdrop-filter: blur(5px); }
        .scan-card { width: 450px; text-align: center; padding: 30px; border-radius: 25px; background: white; }
        .progress-container { background: #e5e7eb; border-radius: 10px; height: 12px; width: 80%; margin: 15px auto; overflow: hidden; }
        .progress-fill { height: 100%; background: #4f46e5; width: 0%; transition: width 1s ease-out; }
        .nav-link-btn { text-decoration: none; background: #f3f4f6; padding: 10px 15px; border-radius: 8px; color: #374151; font-weight: 500; font-size: 0.9rem; transition: 0.2s; }
        .holiday-alert { background: #fee2e2; border-left: 6px solid #ef4444; color: #991b1b; padding: 20px; border-radius: 12px; margin-bottom: 25px; display: flex; align-items: center; gap: 15px; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); } 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); } }
        .ay-badge { background: #4f46e5; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; margin-left: 10px; }
        .container { max-width: 1200px; margin: 0 auto; padding: 15px; }
        .dashboard-header { display: flex; justify-content: space-between; align-items: center; padding: 15px 25px; flex-wrap: wrap; gap: 15px; }
        .summary-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .log-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 30px; }
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        @media (max-width: 768px) { .log-grid { grid-template-columns: 1fr; } .scan-card { width: 90%; } .dashboard-header { justify-content: center; text-align: center; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="card dashboard-header" style="margin-bottom:20px;">
            <div style="display:flex; gap:12px; flex-wrap: wrap; align-items: center;">
                <h2 style="margin:0; font-size: 1.2rem;">Dashboard</h2>
                <span class="ay-badge">AY: <?= $current_academic_year ?></span>
                <div style="display:flex; gap:8px; flex-wrap: wrap;">
                    <a href="manage_majors.php" class="nav-link-btn">🎓 Majors</a>
                    <a href="manage_courses.php" class="nav-link-btn">📚 Courses</a>
                    <a href="manage_students.php" class="nav-link-btn">👨‍🎓 Students</a>
                    <a href="manage_registration.php" class="nav-link-btn">📝 Registration</a>
                    <a href="attendance_report.php" class="nav-link-btn">📊 Reports</a>
                    <a href="manage_timetable.php" class="nav-link-btn">📅 Timetable</a>
                    <a href="holidays.php" class="nav-link-btn">⛱️ Holidays</a>
                    <a href="manage_leaves.php" class="nav-link-btn">🏥 Leave</a>
                    <a href="computer_usage_report.php" class="nav-link-btn">🖥️ Computer Lab</a>
                </div>
            </div>
            <a href="logout.php" style="background:#ef4444; color:white; text-decoration:none; padding:10px 20px; border-radius:8px; white-space: nowrap; margin-left: auto;">Logout</a>
        </div>

        <?php if ($holiday_info): ?>
            <div class="holiday-alert">
                <span style="font-size: 30px;">🎊</span>
                <div>
                    <h3 style="margin:0;">Today is a Holiday: <?= htmlspecialchars($holiday_info['description']) ?></h3>
                    <p style="margin:5px 0 0 0;">Attendance scanning is disabled for today.</p>
                </div>
            </div>
        <?php endif; ?>

        <div class="card" style="margin-bottom: 25px; border: 2px dashed #4f46e5; display: flex; align-items: center; justify-content: center; gap: 15px; padding: 20px;">
            <span style="font-weight: bold; color: #4f46e5;">📡 RFID Scanner:</span>
            <input type="text" id="manual_uid" autofocus placeholder="<?= $holiday_info ? 'Scanner Disabled' : 'Waiting for scan...' ?>" <?= $holiday_info ? 'disabled' : '' ?> style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; width: 250px;">
            <button onclick="submitScan(document.getElementById('manual_uid').value)" <?= $holiday_info ? 'disabled style="opacity:0.5"' : '' ?> style="padding: 12px 25px; background: #4f46e5; color: white; border: none; border-radius: 8px; cursor: pointer;">Scan</button>
        </div>

        <?php if (!empty($shortage_list)): ?>
            <div class="card" style="border-left: 5px solid #ef4444; background: #fef2f2; margin-bottom: 20px; padding: 20px;">
                <h3 style="color: #991b1b; margin-top: 0;">⚠️ Attendance Warning (Below 100% for testing)</h3>
                <div class="table-responsive">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 2px solid #fee2e2; color: #991b1b;">
                                <th style="padding: 10px;">Roll No</th>
                                <th>Name</th>
                                <th>Course</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($shortage_list as $row): ?>
                            <tr style="border-bottom: 1px solid #fee2e2;">
                                <td style="padding: 10px;"><?= htmlspecialchars($row['roll_no']) ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['course_name']) ?></td>
                                <td style="color: #ef4444; font-weight: bold;"><?= round($row['percentage'], 1) ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <div class="summary-container">
            <div class="card" style="border-left: 5px solid #10b981; background: #f0fdf4;">
                <div style="color: #065f46; font-size: 0.8rem; font-weight: bold;">Present Today</div>
                <div id="present-count-display" style="font-size: 2.2rem; font-weight: bold; color: #047857;"><?= $total_present ?></div>
            </div>
            <div class="card" style="border-left: 5px solid #4f46e5; background: #f5f3ff;">
                <div style="color: #3730a3; font-size: 0.8rem; font-weight: bold;">Classes Today</div>
                <div style="font-size: 2.2rem; font-weight: bold; color: #4338ca;"><?= $total_classes ?></div>
            </div>
            <div class="card" style="border-left: 5px solid #f59e0b; background: #fffbeb;">
                <div style="color: #92400e; font-size: 0.8rem; font-weight: bold;">On Leave</div>
                <div style="font-size: 2.2rem; font-weight: bold; color: #b45309;"><?= $total_leaves ?></div>
            </div>
        </div>

        <div class="main-grid">
            <?php foreach ($majors as $m): ?>
                <div onclick="location.href='attendance.php?major_id=<?= $m['id'] ?>&major_name=<?= urlencode($m['title']) ?>'" class="card" style="text-align:center; padding: 25px;">
                    <div style="font-weight:bold;"><?= htmlspecialchars($m['title']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="log-grid">
            <div class="card">
                <h3>📡 Live Attendance Log</h3>
                <div class="table-responsive">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="text-align:left; background:#f9fafb;">
                                <th style="padding:12px;">Time</th>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="attendance-body"></tbody>
                    </table>
                </div>
            </div>
            <div class="card" style="text-align: center;">
                <h3>Today's Ratio</h3>
                <div style="width: 200px; margin: 0 auto;"><canvas id="todayAttendanceChart"></canvas></div>
            </div>
        </div>
    </div>

    <div id="scan-overlay">
        <div class="scan-card">
            <div id="scan-status" style="font-weight:bold; margin-bottom:15px; font-size:1.4rem;">Checking...</div>
            <img id="st-photo" src="assets/img/students/default.png" style="width:180px; height:180px; border-radius:50%; border:6px solid #4f46e5; object-fit:cover; margin-bottom:20px;">
            <h2 id="st-name">Student Name</h2>
            <p id="st-roll">Roll No: -</p>
            <p id="st-course" style="font-weight:bold; color:#4f46e5;"></p>
            <div class="progress-container"><div id="st-progress-bar" class="progress-fill"></div></div>
            <div id="st-percentage" style="font-size: 1.2rem; font-weight: bold;">0%</div>
        </div>
    </div>

    <audio id="audio-success" src="assets/audio/check-in.mp3" preload="auto"></audio>
    <audio id="audio-out" src="assets/audio/bye.mp3" preload="auto"></audio>
    <audio id="audio-error" src="assets/audio/error.mp3" preload="auto"></audio>

    <script>
        let attendanceChart;

        function fetchLogs() {
            $.getJSON('fetch_dashboard_stats.php', function(data) {
                $('#present-count-display').text(data.total_present);
                $('#attendance-body').html(data.table_html);
                if (attendanceChart) {
                    attendanceChart.data.datasets[0].data = [
                        data.total_present,
                        Math.max(0, data.total_expected - data.total_present)
                    ];
                    attendanceChart.update();
                }
            });
        }

        function submitScan(uid) {
            if (!uid) return;
            $('#manual_uid').val('');
            $.ajax({
                url: 'process_scan.php',
                type: 'POST',
                data: { rfid_uid: uid },
                success: function(res) {
                    let data = (typeof res === 'object') ? res : JSON.parse(res);
                    if (data.success) {
                        try { (data.type === 'Lab Out') ? document.getElementById('audio-out').play() : document.getElementById('audio-success').play(); } catch(e){}
                        $('#scan-status').text('✅ ' + data.message).css('color', '#10b981');
                        $('#st-photo').attr('src', 'assets/img/students/' + (data.photo || 'default.png'));
                        $('#st-name').text(data.name);
                        $('#st-roll').text('Roll No: ' + data.roll_no);
                        $('#st-course').text(data.course);
                        $('#st-percentage').text(data.percentage || '100%');
                        $('#st-progress-bar').css({'width': data.percentage || '100%', 'background': '#10b981'});
                    } else {
                        try { document.getElementById('audio-error').play(); } catch(e){}
                        $('#scan-status').text('❌ ' + data.message).css('color', '#ef4444');
                        $('#st-photo').attr('src', 'assets/img/students/default.png');
                        $('#st-name').text('Unknown');
                        $('#st-percentage').text('0%');
                        $('#st-progress-bar').css('width', '0%');
                    }
                    $('#scan-overlay').css('display', 'flex').hide().fadeIn(400);
                    setTimeout(() => { $('#scan-overlay').fadeOut(400); }, 3000);
                    fetchLogs();
                }
            });
        }

        $(document).ready(function() {
            const ctx = document.getElementById('todayAttendanceChart').getContext('2d');
            attendanceChart = new Chart(ctx, {
                type: 'doughnut',
                data: { labels: ['Present', 'Absent'], datasets: [{ data: [0, 1], backgroundColor: ['#10b981', '#e5e7eb'], borderWidth: 0 }] },
                options: { cutout: '75%' }
            });
            setInterval(fetchLogs, 5000);
            fetchLogs();
            $('#manual_uid').on('keypress', function(e) { if (e.which == 13) submitScan($(this).val()); });
        });
    </script>
</body>
</html>