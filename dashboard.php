<?php
session_start();
require_once 'database/database.php';
if (empty($_SESSION["current_user"])) {
    header('Location: login.php');
    exit;
}
$db = new Database();
$today_day = date('l');

// Initial counts for first load
$total_present = $db->conn->query("SELECT COUNT(DISTINCT student_id) FROM attendance_details WHERE on_date = CURDATE() AND status = 'Present'")->fetchColumn() ?: 0;
$total_classes = $db->conn->prepare("SELECT COUNT(*) FROM timetable WHERE day_of_week = ?");
$total_classes->execute([$today_day]);
$total_classes = $total_classes->fetchColumn() ?: 0;

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
        .card:hover {
            border-color: #4f46e5 !important;
            transform: translateY(-3px);
            transition: 0.3s;
            cursor: pointer;
        }

        .main-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        #scan-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(5px);
        }

        .scan-card {
            width: 450px;
            text-align: center;
            padding: 30px;
            border-radius: 25px;
            background: white;
        }

        .progress-container {
            background: #e5e7eb;
            border-radius: 10px;
            height: 12px;
            width: 80%;
            margin: 15px auto;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: #4f46e5;
            width: 0%;
            transition: width 1s ease-out;
        }

        .nav-link-btn {
            text-decoration: none;
            background: #f3f4f6;
            padding: 10px 15px;
            border-radius: 8px;
            color: #374151;
            font-weight: 500;
            font-size: 0.9rem;
            transition: 0.2s;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.4;
            }

            100% {
                opacity: 1;
            }
        }


        #scan-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 9999;


            display: none;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(8px);
        }


        .scan-card {
            width: 90%;
            max-width: 450px;
            text-align: center;
            padding: 40px 30px;
            border-radius: 30px;
            background: white;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
            animation: scaleIn 0.3s ease-out;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0.8);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card" style="margin-bottom:20px; display:flex; justify-content: space-between; align-items: center; padding: 15px 25px;">
            <div style="display:flex; gap:12px; flex-wrap: wrap;">
                <a href="manage_majors.php" class="nav-link-btn">⚙️ Majors</a>
                <a href="manage_courses.php" class="nav-link-btn">📚 Courses</a>
                <a href="manage_students.php" class="nav-link-btn">👨‍🎓 Students</a>
                <a href="manage_registration.php" class="nav-link-btn">📝 Registration</a>
                <a href="attendance_report.php" class="nav-link-btn">📊 Reports</a>
                <a href="manage_timetable.php" class="nav-link-btn">📅 Timetable</a>
            </div>
            <a href="logout.php" style="background:#ef4444; color:white; text-decoration:none; padding:10px 20px; border-radius:8px;">Logout</a>
        </div>

        <div class="card" style="margin-bottom: 25px; border: 2px dashed #4f46e5; display: flex; align-items: center; justify-content: center; gap: 15px; padding: 20px;">
            <span style="font-weight: bold; color: #4f46e5;">📡 RFID Scanner:</span>
            <input type="text" id="manual_uid" autofocus placeholder="Waiting for scan..." style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; width: 250px;">
            <button onclick="submitScan(document.getElementById('manual_uid').value)" style="padding: 12px 25px; background: #4f46e5; color: white; border: none; border-radius: 8px; cursor: pointer;">Scan</button>
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
            <div class="card" style="border-left: 5px solid #10b981; background: #f0fdf4;">
                <div style="color: #065f46; font-size: 0.8rem; font-weight: bold;">Students Present Today</div>
                <div id="present-count-display" style="font-size: 2.2rem; font-weight: bold; color: #047857;"><?= $total_present ?></div>
            </div>
            <div class="card" style="border-left: 5px solid #4f46e5; background: #f5f3ff;">
                <div style="color: #3730a3; font-size: 0.8rem; font-weight: bold;">Classes Today</div>
                <div style="font-size: 2.2rem; font-weight: bold; color: #4338ca;"><?= $total_classes ?></div>
            </div>
            <!-- <div class="card" style="border-left: 5px solid #0891b2; background: #ecfeff;">
                <div style="color: #155e75; font-size: 0.8rem; font-weight: bold;">System Status</div>
                <div style="font-size: 1.1rem; color: #0891b2; margin-top: 10px;">
                    <span style="display: inline-block; width: 10px; height: 10px; background: #10b981; border-radius: 50%; animation: pulse 1.5s infinite;"></span> Monitoring Live
                </div>
            </div> -->
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
        let attendanceChart;

        let audioUnlocked = false;

        function unlockAudio() {
            if (audioUnlocked) return;

            // unlock all audio elements
            const sounds = ['audio-success', 'audio-error', 'audio-warning'];
            sounds.forEach(id => {
                const audio = document.getElementById(id);
                audio.play().then(() => {
                    audio.pause();
                    audio.currentTime = 0;
                }).catch(e => {
                    console.log("Audio unlock waiting for first interaction");
                });
            });
            audioUnlocked = true;
        }


        function submitScan(uid) {
            if (!uid) return;
            $('#manual_uid').val('');

            $.post('process_scan.php', {
                rfid_uid: uid
            }, function(res) {
                const data = JSON.parse(res);
                if (data.success) {
                    document.getElementById('audio-success').play();
                    $('#scan-status').text('✅ ' + data.message).css('color', '#10b981');
                    $('#st-photo').attr('src', 'assets/img/students/' + (data.photo || 'default.png'));
                    $('#st-name').text(data.name);
                    $('#st-roll').text('Roll No: ' + data.roll_no);
                    $('#st-course').text(data.course);
                    $('#st-percentage').text(data.percentage + '%');
                    $('#st-progress-bar').css({
                        'width': data.percentage + '%',
                        'background': '#10b981'
                    });
                } else {
                    document.getElementById('audio-error').play();
                    $('#scan-status').text('❌ ' + data.message).css('color', '#ef4444');
                    // default
                    $('#st-photo').attr('src', 'assets/img/students/default.png');
                    $('#st-name').text('Unknown Student');
                    $('#st-roll').text('Roll No: -');
                    $('#st-course').text('');
                    $('#st-percentage').text('0%');
                    $('#st-progress-bar').css('width', '0%');
                }

                // flex
                $('#scan-overlay').css('display', 'flex').hide().fadeIn(400);

                // 3 seconds intervel
                setTimeout(function() {
                    $('#scan-overlay').fadeOut(400);
                }, 3000);

                fetchLogs();
            });
        }

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

        $(document).ready(function() {
            // Chart Initializing
            const ctx = document.getElementById('todayAttendanceChart').getContext('2d');
            attendanceChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Present', 'Absent'],
                    datasets: [{
                        data: [0, 1],
                        backgroundColor: ['#10b981', '#e5e7eb'],
                        borderWidth: 0
                    }]
                },
                options: {
                    cutout: '75%'
                }
            });

            // 5 seconds time intervel
            setInterval(fetchLogs, 5000);
            fetchLogs();

            // Enter
            $('#manual_uid').on('keypress', function(e) {
                if (e.which == 13) submitScan($(this).val());
            });
        });
    </script>

    <audio id="audio-success" src="assets/audio/success.mp3" preload="auto"></audio>
    <audio id="audio-error" src="assets/audio/error.mp3" preload="auto"></audio>
    <audio id="audio-warning" src="assets/audio/warning.mp3" preload="auto"></audio>
</body>

</html>