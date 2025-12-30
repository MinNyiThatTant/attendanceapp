<?php
session_start();
require_once 'database/database.php';

if (empty($_SESSION["current_user"])) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$conn = $db->conn;

// take data for filters
$majors = $conn->query("SELECT * FROM major_details")->fetchAll(PDO::FETCH_ASSOC);
$courses_sql = "SELECT cd.id, cd.title, cd.code, (SELECT GROUP_CONCAT(major_id) FROM course_assignments WHERE course_id = cd.id) as assigned_majors FROM course_details cd";
$courses = $conn->query($courses_sql)->fetchAll(PDO::FETCH_ASSOC);

// Filter Parameters 
$f_major = $_GET['major_id'] ?? '';
$f_course = $_GET['course_id'] ?? '';
$f_date = $_GET['date'] ?? date('Y-m-d');
$f_month = date('Y-m', strtotime($f_date)); // month format for excel

$report_data = [];
$present_count = 0;
$total_records = 0;
$attendance_percentage = 0;

if ($f_course) {
    $sql = "SELECT 
                s.name, s.roll_no, m.title as major_name,
                IFNULL(a.status, 'Absent') as status,
                :f_date as attendance_date
            FROM course_registration r
            JOIN student_details s ON r.student_id = s.id
            JOIN major_details m ON s.major_id = m.id
            LEFT JOIN attendance_details a ON s.id = a.student_id 
                AND a.course_id = r.course_id 
                AND a.on_date = :f_date
            WHERE r.course_id = :course_id";

    $params = [':course_id' => $f_course, ':f_date' => $f_date];
    if ($f_major) {
        $sql .= " AND s.major_id = :major_id";
        $params[':major_id'] = $f_major;
    }
    $sql .= " ORDER BY s.roll_no ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $report_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_records = count($report_data);
    foreach ($report_data as $row) {
        if (strtolower($row['status']) == 'present' || strtolower($row['status']) == 'checked in') $present_count++;
    }
    $absent_count = $total_records - $present_count;
    $attendance_percentage = ($total_records > 0) ? round(($present_count / $total_records) * 100, 1) : 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Analysis - Report</title>
    <link rel="stylesheet" href="css/attendance.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-box { padding: 15px; border-radius: 8px; color: white; text-align: center; }
        .report-content { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; align-items: start; }
        @media (max-width: 850px) { .report-content { grid-template-columns: 1fr; } }
        .badge { padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; color: #fff; }
        
        /* Excel Button Style */
        .btn-excel {
            background-color: #16a34a;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: 0.3s;
        }
        .btn-excel:hover { background-color: #15803d; }
    </style>
</head>
<body>

<div class="container">
    <header class="attendance-header">
        <h1>Attendance <span style="color:#4f46e5">Analytics</span></h1>
        <div style="display: flex; gap: 10px;">
            <?php if ($f_course): ?>
                <a href="export_excel.php?major_id=<?= $f_major ?>&course_id=<?= $f_course ?>&month=<?= $f_month ?>" class="btn-excel">
                    📊 Export Excel
                </a>
            <?php endif; ?>
            <a href="dashboard.php" class="class-btn" style="text-decoration:none;">⬅ Dashboard</a>
        </div>
    </header>

    <div class="card">
        <form method="GET">
            <div class="form-container">
                <div class="form-row">
                    <div class="input-group">
                        <label>Filter Major</label>
                        <select name="major_id" id="major_filter" onchange="updateCourseFilter()">
                            <option value="">All Majors</option>
                            <?php foreach ($majors as $m): ?>
                                <option value="<?= $m['id'] ?>" <?= $f_major == $m['id'] ? 'selected' : '' ?>><?= $m['title'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Select Course</label>
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
                </div>

                <div class="form-row">
                    <div class="input-group">
                        <label>Select Date</label>
                        <input type="date" name="date" value="<?= $f_date ?>">
                    </div>

                    <div class="input-group" style="display: flex; align-items: flex-end;">
                        <button type="submit" class="save-btn" style="width: 100%; margin: 0; height: 42px;">Generate Analysis</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php if ($f_course): ?>
        <div class="stats-grid">
            <div class="stat-box" style="background: #4f46e5;"><small>Total Students</small><div><?= $total_records ?></div></div>
            <div class="stat-box" style="background: #10b981;"><small>Present</small><div><?= $present_count ?></div></div>
            <div class="stat-box" style="background: #ef4444;"><small>Absent</small><div><?= $absent_count ?></div></div>
            <div class="stat-box" style="background: #f59e0b;"><small>Rate (%)</small><div><?= $attendance_percentage ?>%</div></div>
        </div>

        <div class="report-content">
            <div class="card" style="padding: 0; overflow: hidden;">
                <div style="padding: 15px; border-bottom: 1px solid #eee; background: #fafafa; font-weight: bold;">Student Attendance List</div>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f4f4f4; text-align: left;">
                            <th style="padding: 12px;">Roll No</th>
                            <th style="padding: 12px;">Student Name</th>
                            <th style="padding: 12px; text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report_data as $row): $is_p = (strtolower($row['status']) == 'present' || strtolower($row['status']) == 'checked in'); ?>
                        <tr>
                            <td style="padding: 12px; font-weight: bold;"><?= $row['roll_no'] ?></td>
                            <td style="padding: 12px;"><?= $row['name'] ?></td>
                            <td style="padding: 12px; text-align: center;">
                                <span class="badge" style="background: <?= $is_p ? '#10b981' : '#ef4444' ?>;">
                                    <?= $is_p ? 'Present' : 'Absent' ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card" style="text-align: center;">
                <h4 style="margin-top: 0;">Attendance Ratio</h4>
                <div style="height: 250px;"><canvas id="attendanceChart"></canvas></div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function updateCourseFilter() {
        const majorId = document.getElementById('major_filter').value;
        const courseSelect = document.getElementById('course_filter');
        const options = courseSelect.options;
        for (let i = 1; i < options.length; i++) {
            const majors = options[i].getAttribute('data-majors') || "";
            options[i].style.display = (majorId === "" || majors.split(',').includes(majorId)) ? "block" : "none";
        }
    }

    <?php if ($f_course): ?>
    new Chart(document.getElementById('attendanceChart'), {
        type: 'doughnut',
        data: {
            labels: ['Present', 'Absent'],
            datasets: [{ data: [<?= $present_count ?>, <?= $absent_count ?>], backgroundColor: ['#10b981', '#ef4444'], borderWidth: 0 }]
        },
        options: { plugins: { legend: { position: 'bottom' } }, maintainAspectRatio: false }
    });
    <?php endif; ?>
    window.onload = updateCourseFilter;
</script>
</body>
</html>