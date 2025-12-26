<?php
session_start();
require_once 'database/database.php';

if (empty($_SESSION["current_user"])) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$conn = $db->conn;

// ၁။ Filter data
$majors = $conn->query("SELECT * FROM major_details")->fetchAll(PDO::FETCH_ASSOC);
// $courses = $conn->query("SELECT id, title, code FROM course_details")->fetchAll(PDO::FETCH_ASSOC);

// အရင်က code အဟောင်းနေရာမှာ ဒါနဲ့ အစားထိုးပါ
$courses_sql = "
    SELECT cd.id, cd.title, cd.code, 
    (SELECT GROUP_CONCAT(major_id) FROM course_assignments WHERE course_id = cd.id) as assigned_majors 
    FROM course_details cd
";
$courses = $conn->query($courses_sql)->fetchAll(PDO::FETCH_ASSOC);


// ၂။ Filter parameters 
$f_major = $_GET['major_id'] ?? '';
$f_course = $_GET['course_id'] ?? '';
$f_month = $_GET['month'] ?? date('Y-m');

// ၃။ Report Data 
// --- Report Data FETCHING (Updated with Major Filter) ---
$report_data = [];
if ($f_course) {
    // Student ရဲ့ Major ကိုပါ စစ်ဖို့ AND s.major_id = ? ကို ထည့်လိုက်ပါတယ်
    $sql = "SELECT a.on_date as attendance_date, a.status, s.name, s.roll_no, m.title as major_name
            FROM attendance_details a
            JOIN student_details s ON a.student_id = s.id
            JOIN major_details m ON s.major_id = m.id
            WHERE a.course_id = ? 
            AND a.on_date LIKE ? ";

    $params = [$f_course, $f_month . '%'];

    // အကယ်၍ Major ကို ရွေးထားရင် Query မှာ ထပ်တိုးစစ်မယ်
    if ($f_major) {
        $sql .= " AND s.major_id = ? ";
        $params[] = $f_major;
    }

    $sql .= " ORDER BY a.on_date DESC, s.roll_no ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $report_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ၄။ Calculate Statistics
$present_count = 0;
$absent_count = 0;
foreach ($report_data as $r) {
    if (strtolower($r['status']) == 'present' || $r['status'] == 'Checked In') $present_count++;
    else $absent_count++;
}
$total_records = count($report_data);
$attendance_percentage = ($total_records > 0) ? round(($present_count / $total_records) * 100, 2) : 0;

// $all_courses_json = json_encode($courses_with_majors); // course_assignments table နဲ့ join ထားတဲ့ data လိုအပ်ပါတယ်
?>



<!DOCTYPE html>
<html>

<head>
    <title>Attendance Report & Analytics</title>
    <link rel="stylesheet" href="css/attendance.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .filter-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .report-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            align-items: flex-end;
        }

        .stat-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .stat-box {
            padding: 20px;
            border-radius: 10px;
            color: white;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .present-box {
            background: #10b981;
        }

        .absent-box {
            background: #ef4444;
        }

        .total-box {
            background: #4f46e5;
        }

        .percent-box {
            background: #f59e0b;
        }

        .chart-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .search-btn {
            background: #4f46e5;
            color: white;
            border: none;
            height: 45px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }

        .search-btn:hover {
            background: #4338ca;
        }
    </style>
</head>

<body>
    <div class="container">
        <header class="attendance-header">
            <h1>Attendance <span style="color:#4f46e5">Analytics</span></h1>
            <a href="dashboard.php" class="class-btn" style="text-decoration:none; background:lightblue;">⬅ Back to Dashboard</a>
        </header>

        <div class="filter-card">
            <form method="GET" class="report-grid">
                <div class="input-group">
                    <label>Major</label>
                    <select name="major_id" id="major_filter" onchange="updateCourseFilter()">
                        <option value="">-- All Majors --</option>
                        <?php foreach ($majors as $m): ?>
                            <option value="<?= $m['id'] ?>" <?= $f_major == $m['id'] ? 'selected' : '' ?>><?= $m['title'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label>Course</label>
                    <select name="course_id" id="course_filter" required>
                        <option value="">-- Choose Course --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>"
                                data-majors="<?= $c['assigned_majors'] ?>"
                                <?= $f_course == $c['id'] ? 'selected' : '' ?>>
                                <?= $c['code'] ?> - <?= $c['title'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label>Month</label>
                    <input type="month" name="month" value="<?= $f_month ?>">
                </div>
                <!-- <button type="submit" class="search-btn">Generate Analysis</button> -->
                <!-- <div style="display: flex; gap: 10px;"> -->
                <button type="submit" class="search-btn" style="flex: 1;">Generate Analysis</button>

                <?php if ($f_course): ?>
                    <a href="export_excel.php?course_id=<?= $f_course ?>&major_id=<?= $f_major ?>&month=<?= $f_month ?>"
                        class="search-btn" style="background: #059669; text-decoration: none; display: flex; align-items: center; justify-content: center; padding: 0 15px;">
                        Excel Export
                    </a>
                <?php endif; ?>
                <!-- </div> -->
            </form>
        </div>

        <?php if ($f_course): ?>
            <div class="stat-container">
                <div class="stat-cards">
                    <div class="stat-box total-box">
                        <h2 style="margin:0;"><?= $total_records ?></h2>
                        <p style="margin:0; font-size: 0.9rem;">Total Classes</p>
                    </div>
                    <div class="stat-box percent-box">
                        <h2 style="margin:0;"><?= $attendance_percentage ?>%</h2>
                        <p style="margin:0; font-size: 0.9rem;">Attendance Rate</p>
                    </div>
                    <div class="stat-box present-box">
                        <h2 style="margin:0;"><?= $present_count ?></h2>
                        <p style="margin:0; font-size: 0.9rem;">Presents</p>
                    </div>
                    <div class="stat-box absent-box">
                        <h2 style="margin:0;"><?= $absent_count ?></h2>
                        <p style="margin:0; font-size: 0.9rem;">Absents</p>
                    </div>
                </div>

                <div class="chart-card">
                    <canvas id="attendanceChart" style="max-height: 200px;"></canvas>
                </div>
            </div>

            <div class="card" style="padding:0; overflow:hidden; margin-top: 20px;">
                <table class="student-table">
                    <thead style="background:#f9fafb">
                        <tr>
                            <th>Date</th>
                            <th>Roll No</th>
                            <th>Student Name</th>
                            <th style="text-align:center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($report_data)): ?>
                            <tr>
                                <td colspan="4" style="text-align:center; padding:30px; color: #9ca3af;">No records found for this period.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($report_data as $row): ?>
                                <tr>
                                    <td><?= date('d-M-Y', strtotime($row['attendance_date'])) ?></td>
                                    <td><strong><?= $row['roll_no'] ?></strong></td>
                                    <td><?= $row['name'] ?></td>
                                    <td style="text-align:center">
                                        <?php if (strtolower($row['status']) == 'present' || $row['status'] == 'Checked In'): ?>
                                            <span style="color:#10b981; font-weight:bold;">● Present</span>
                                        <?php else: ?>
                                            <span style="color:#ef4444; font-weight:bold;">● Absent</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Chart.js Configuration
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Absent'],
                datasets: [{
                    data: [<?= $present_count ?>, <?= $absent_count ?>],
                    backgroundColor: ['#10b981', '#ef4444'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: true,
                        text: 'Monthly Attendance Overview'
                    }
                }
            }
        });
    </script>


    <script>
        function updateCourseFilter() {
            const majorId = document.getElementById('major_filter').value;
            const courseSelect = document.getElementById('course_filter');
            const options = courseSelect.options;

            for (let i = 1; i < options.length; i++) {
                const majors = options[i].getAttribute('data-majors') || "";
                if (majorId === "" || majors.split(',').includes(majorId)) {
                    options[i].style.display = "block";
                } else {
                    options[i].style.display = "none";
                }
            }
            // အကယ်၍ လက်ရှိရွေးထားတဲ့ course က ဖျောက်လိုက်တဲ့အထဲ ပါသွားရင် reset လုပ်မယ်
            if (options[courseSelect.selectedIndex].style.display === "none") {
                courseSelect.value = "";
            }
        }
        // စစချင်း load ဖြစ်ချိန်မှာလည်း တစ်ခါ run ပေးဖို့
        window.onload = updateCourseFilter;
    </script>
</body>

</html>