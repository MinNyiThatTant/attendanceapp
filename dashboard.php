<?php
session_start();
require_once 'database/database.php';

// Check user session
if (empty($_SESSION["current_user"])) {
    header('Location: login.php');
    exit;
}

$db = new Database();

// Get today's date info
$today_day = date('l'); // Monday, Tuesday, etc.
$today = date('Y-m-d');
$current_month = (int)date('m');
$current_year = (int)date('Y');


/**
 * Determine current academic year
 * eg: If current month is before June (1-5), academic year is previous year-current year (2024-2025)
 *    If current month is June or later (6-12), academic year is current year-next year (2025-2026)
 */
if ($current_month < 6) {
    $current_academic_year = ($current_year - 1) . "-" . $current_year;
} else {
    $current_academic_year = $current_year . "-" . ($current_year + 1);
}

// --- Holiday Check ---
$check_h = $db->conn->prepare("SELECT description FROM holidays WHERE holiday_date = ?");
$check_h->execute([$today]);
$holiday_info = $check_h->fetch();

// 1. Students Present Today
$total_present_sql = "SELECT COUNT(DISTINCT student_id) FROM attendance_details 
                      WHERE on_date = CURDATE() AND status = 'Present'";
$total_present = $db->conn->query($total_present_sql)->fetchColumn() ?: 0;

// Today Leaves
$today_leaves = $db->conn->prepare("SELECT COUNT(*) FROM student_leaves WHERE ? BETWEEN from_date AND to_date");
$today_leaves->execute([$today]);
$total_leaves = $today_leaves->fetchColumn() ?: 0;

// 2. Today Classes Count , also academic year
$stmt_classes_count = $db->conn->prepare("SELECT COUNT(*) FROM timetable WHERE day_of_week = ? AND academic_year = ?");
$stmt_classes_count->execute([$today_day, $current_academic_year]);
$total_classes = $stmt_classes_count->fetchColumn() ?: 0;

// 3. Majors List
$majors = $db->conn->query("SELECT * FROM major_details")->fetchAll(PDO::FETCH_ASSOC);

// 4. Today Classes Table Details
$sql_timetable = "SELECT t.*, c.title as course_name, c.code as course_code 
                  FROM timetable t
                  JOIN course_details c ON t.course_id = c.id
                  WHERE t.day_of_week = :today 
                  AND t.academic_year = :ayear";

$stmt_timetable = $db->conn->prepare($sql_timetable);
$stmt_timetable->execute([
    ':today' => $today_day,
    ':ayear' => $current_academic_year
]);
$today_classes = $stmt_timetable->fetchAll();

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
    </style>
</head>
<body>
    <div class="container">
        <div class="card" style="margin-bottom:20px; display:flex; justify-content: space-between; align-items: center; padding: 15px 25px;">
            <div style="display:flex; gap:12px; flex-wrap: wrap; align-items: center;">
                <h2 style="margin:0; font-size: 1.2rem;">Dashboard</h2>
                <span class="ay-badge">AY: <?= $current_academic_year ?></span>
                <div style="display:flex; gap:8px; margin-left:15px;">
                    <a href="manage_majors.php" class="nav-link-btn">⚙️ Majors</a>
                    <a href="manage_courses.php" class="nav-link-btn">📚 Courses</a>
                    <a href="manage_students.php" class="nav-link-btn">👨‍🎓 Students</a>
                    <a href="manage_registration.php" class="nav-link-btn">📝 Registration</a>
                    <a href="attendance_report.php" class="nav-link-btn">📊 Reports</a>
                    <a href="manage_timetable.php" class="nav-link-btn">📅 Timetable</a>
                    <a href="holidays.php" class="nav-link-btn">🎉 Holidays</a>
                    <a href="manage_leaves.php" class="nav-link-btn">🏥 Leave</a>
                </div>
            </div>
            <a href="logout.php" style="background:#ef4444; color:white; text-decoration:none; padding:10px 20px; border-radius:8px;">Logout</a>
        </div>

        <?php if ($holiday_info): ?>
            <div class="holiday-alert">
                <span style="font-size: 30px;">🎊</span>
                <div>
                    <h3 style="margin:0;">Today is a Holiday: <?= htmlspecialchars($holiday_info['description']) ?></h3>
                    <p style="margin:5px 0 0 0; font-size: 0.9rem;">Attendance scanning is temporarily disabled for today.</p>
                </div>
            </div>
        <?php endif; ?>

        <div class="card" style="margin-bottom: 25px; border: 2px dashed #4f46e5; display: flex; align-items: center; justify-content: center; gap: 15px; padding: 20px;">
            <span style="font-weight: bold; color: #4f46e5;">📡 RFID Scanner:</span>
            <input type="text" id="manual_uid" autofocus placeholder="<?= $holiday_info ? 'Scanner Disabled' : 'Waiting for scan...' ?>" <?= $holiday_info ? 'disabled' : '' ?> style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; width: 250px;">
            <button onclick="submitScan(document.getElementById('manual_uid').value)" <?= $holiday_info ? 'disabled style="opacity:0.5"' : '' ?> style="padding: 12px 25px; background: #4f46e5; color: white; border: none; border-radius: 8px; cursor: pointer;">Scan</button>
        </div>

        <div id="scan-overlay">
            <div class="scan-card">
                <div id="scan-status" style="font-weight:bold; margin-bottom:15px; font-size:1.4rem;">Checking...</div>
                <img id="st-photo" src="assets/img/students/default.png" style="width:180px; height:180px; border-radius:50%; border:6px solid #4f46e5; object-fit:cover; margin-bottom:20px;">
                <h2 id="st-name">Student Name</h2>
                <p id="st-roll">Roll No: -</p>
                <p id="st-course" style="font-weight:bold; color:#4f46e5;"></p>
                <div class="progress-container">
                    <div id="st-progress-bar" class="progress-fill"></div>
                </div>
                <div id="st-percentage" style="font-size: 1.2rem; font-weight: bold;">0%</div>
            </div>
        </div>

        <div class="summary-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <a href="attendance_report.php?view=today_present" style="text-decoration: none;">
                <div class="card card-hover" style="border-left: 5px solid #10b981; background: #f0fdf4;">
                    <div style="color: #065f46; font-size: 0.8rem; font-weight: bold;">Students Present Today</div>
                    <div id="present-count-display" style="font-size: 2.2rem; font-weight: bold; color: #047857;"><?= $total_present ?></div>
                </div>
            </a>

            <a href="manage_timetable.php" style="text-decoration: none;">
                <div class="card card-hover" style="border-left: 5px solid #4f46e5; background: #f5f3ff;">
                    <div style="color: #3730a3; font-size: 0.8rem; font-weight: bold;">Classes Today (<?= $current_academic_year ?>)</div>
                    <div style="font-size: 2.2rem; font-weight: bold; color: #4338ca;"><?= $total_classes ?></div>
                </div>
            </a>

            <a href="manage_leaves.php" style="text-decoration: none;">
                <div class="card card-hover" style="border-left: 5px solid #f59e0b; background: #fffbeb;">
                    <div style="color: #92400e; font-size: 0.8rem; font-weight: bold;">On Leave Today</div>
                    <div style="font-size: 2.2rem; font-weight: bold; color: #b45309;"><?= $total_leaves ?></div>
                </div>
            </a>
        </div>

        <div class="main-grid">
            <?php foreach ($majors as $m): ?>
                <div onclick="location.href='attendance.php?major_id=<?= $m['id'] ?>&major_name=<?= urlencode($m['title']) ?>'" class="card" style="text-align:center; padding: 25px;">
                    <div style="font-weight:bold;"><?= htmlspecialchars($m['title']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 30px;">
            <div class="card">
                <h3>📡 Live Attendance Log</h3>
                <table class="student-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="attendance-body"></tbody>
                </table>
            </div>
            <div class="card" style="text-align: center;">
                <h3>Today's Ratio</h3>
                <div style="width: 200px; margin: 0 auto;"><canvas id="todayAttendanceChart"></canvas></div>
            </div>
        </div>
    </div>

    <script>
        let last_attendance_id = 0;
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
            $.post('process_scan.php', { rfid_uid: uid }, function(res) {
                try {
                    const data = JSON.parse(res);
                    if (data.success) {
                        document.getElementById('audio-success').play();
                        $('#scan-status').text('✅ ' + data.message).css('color', '#10b981');
                        $('#st-photo').attr('src', 'assets/img/students/' + (data.photo || 'default.png'));
                        $('#st-name').text(data.name);
                        $('#st-roll').text('Roll No: ' + data.roll_no);
                        $('#st-course').text(data.course);
                        $('#st-percentage').text(data.percentage);
                        $('#st-progress-bar').css({ 'width': data.percentage, 'background': '#10b981' });
                    } else {
                        document.getElementById('audio-error').play();
                        $('#scan-status').text('❌ ' + data.message).css('color', '#ef4444');
                        $('#st-photo').attr('src', 'assets/img/students/default.png');
                        $('#st-name').text(data.name || 'Unknown');
                        $('#st-percentage').text('0%');
                        $('#st-progress-bar').css('width', '0%');
                    }
                    $('#scan-overlay').css('display', 'flex').hide().fadeIn(400);
                    setTimeout(() => { $('#scan-overlay').fadeOut(400); }, 3000);
                    fetchLogs();
                } catch (e) { console.error("Server Error:", res); }
            });
        }

        $(document).ready(function() {
            const ctx = document.getElementById('todayAttendanceChart').getContext('2d');
            attendanceChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Present', 'Absent'],
                    datasets: [{ data: [0, 1], backgroundColor: ['#10b981', '#e5e7eb'], borderWidth: 0 }]
                },
                options: { cutout: '75%' }
            });

            setInterval(fetchLogs, 5000);
            fetchLogs();

            $('#manual_uid').on('keypress', function(e) {
                if (e.which == 13) submitScan($(this).val());
            });

            $(document).one('click', function() {
                document.querySelectorAll('audio').forEach(a => {
                    a.play().then(() => { a.pause(); a.currentTime = 0; }).catch(()=>{});
                });
            });
        });
    </script>

    <audio id="audio-success" src="assets/audio/success.mp3" preload="auto"></audio>
    <audio id="audio-error" src="assets/audio/error.mp3" preload="auto"></audio>
</body>
</html>