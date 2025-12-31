<?php
session_start();
require_once 'database/database.php';

if (empty($_SESSION["current_user"])) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$conn = $db->conn;

// Filter data များရယူခြင်း
$majors = $conn->query("SELECT * FROM major_details")->fetchAll(PDO::FETCH_ASSOC);
$courses_sql = "SELECT cd.id, cd.title, cd.code, (SELECT GROUP_CONCAT(major_id) FROM course_assignments WHERE course_id = cd.id) as assigned_majors FROM course_details cd";
$courses = $conn->query($courses_sql)->fetchAll(PDO::FETCH_ASSOC);

// Filter Parameters
$f_major = $_GET['major_id'] ?? '';
$f_course = $_GET['course_id'] ?? '';
$f_type = $_GET['report_type'] ?? 'daily'; 
$f_date = $_GET['date'] ?? date('Y-m-d');
$f_month = $_GET['month'] ?? date('Y-m');

$report_data = [];
$present_count = 0;
$total_records = 0;
$attendance_percentage = 0;

if ($f_course) {
    if ($f_type == 'daily') {
        // --- Daily Query ---
        $sql = "SELECT s.name, s.roll_no, m.title as major_name,
                       IFNULL(a.status, 'Absent') as status
                FROM course_registration r
                JOIN student_details s ON r.student_id = s.id
                JOIN major_details m ON s.major_id = m.id
                LEFT JOIN attendance_details a ON s.id = a.student_id 
                    AND a.course_id = r.course_id 
                    AND a.on_date = :f_date
                WHERE r.course_id = :course_id";
        $params = [':course_id' => $f_course, ':f_date' => $f_date];
    } else {
        // --- Monthly Query ---
        // အတန်းရှိခဲ့သော ရက်စုစုပေါင်းကို အရင်ရှာပါ
        $stmt_days = $conn->prepare("SELECT COUNT(DISTINCT on_date) FROM attendance_details WHERE course_id = ? AND on_date LIKE ?");
        $stmt_days->execute([$f_course, $f_month . '%']);
        $total_class_days = $stmt_days->fetchColumn() ?: 0;

        $sql = "SELECT s.name, s.roll_no, m.title as major_name,
                       COUNT(CASE WHEN a.status = 'Present' THEN 1 END) as days_present,
                       $total_class_days as total_days
                FROM course_registration r
                JOIN student_details s ON r.student_id = s.id
                JOIN major_details m ON s.major_id = m.id
                LEFT JOIN attendance_details a ON s.id = a.student_id 
                    AND a.course_id = r.course_id 
                    AND a.on_date LIKE :f_month
                WHERE r.course_id = :course_id";
        $params = [':course_id' => $f_course, ':f_month' => $f_month . '%'];
    }

    if ($f_major) {
        $sql .= " AND s.major_id = :major_id";
        $params[':major_id'] = $f_major;
    }
    
    if ($f_type == 'monthly') { $sql .= " GROUP BY s.id"; }
    $sql .= " ORDER BY s.roll_no ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $report_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Stats Calculation
    $total_records = count($report_data);
    if ($f_type == 'daily') {
        foreach ($report_data as $row) {
            if (strtolower($row['status']) == 'present') $present_count++;
        }
        $absent_count = $total_records - $present_count;
        $attendance_percentage = ($total_records > 0) ? round(($present_count / $total_records) * 100, 1) : 0;
    } else {
        // Monthly stats: တစ်တန်းလုံးရဲ့ ပျမ်းမျှ ရာခိုင်နှုန်း
        $total_p_days = 0;
        $total_possible_days = $total_records * $total_class_days;
        foreach ($report_data as $row) { $total_p_days += $row['days_present']; }
        $attendance_percentage = ($total_possible_days > 0) ? round(($total_p_days / $total_possible_days) * 100, 1) : 0;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report</title>
    <link rel="stylesheet" href="css/attendance.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-box { padding: 15px; border-radius: 8px; color: white; text-align: center; }
        .badge { padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; color: #fff; }
        .btn-excel { background-color: #16a34a; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; }
    </style>
</head>
<body>

<div class="container">
    <header class="attendance-header" style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1>Attendance <span style="color:#4f46e5">Analytics</span></h1>
        <div style="display: flex; gap: 10px;">
            <?php if ($f_course): ?>
                <a href="export_excel.php?major_id=<?= $f_major ?>&course_id=<?= $f_course ?>&report_type=<?= $f_type ?>&date=<?= $f_date ?>&month=<?= $f_month ?>" class="btn-excel">📊 Export Excel</a>
            <?php endif; ?>
            <a href="dashboard.php" class="save-btn" style="text-decoration:none; background:#94a3b8;">⬅ Back To Dashboard</a>
        </div>
    </header>

    <div class="card">
        <form method="GET">
            <div class="form-container">
                <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div class="input-group" style="flex: 1;">
                        <label>Major</label>
                        <select name="major_id" id="major_filter" onchange="updateCourseFilter()">
                            <option value="">All Majors</option>
                            <?php foreach ($majors as $m): ?>
                                <option value="<?= $m['id'] ?>" <?= $f_major == $m['id'] ? 'selected' : '' ?>><?= $m['title'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-group" style="flex: 1;">
                        <label>Course</label>
                        <select name="course_id" id="course_filter" required>
                            <option value="">-- Choose Course --</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['id'] ?>" data-majors="<?= $c['assigned_majors'] ?>" <?= $f_course == $c['id'] ? 'selected' : '' ?>>
                                    <?= $c['code'] ?> - <?= $c['title'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row" style="display: flex; gap: 15px;">
                    <div class="input-group" style="flex: 1;">
                        <label>Report Type</label>
                        <select name="report_type" id="report_type" onchange="toggleDateInput()">
                            <option value="daily" <?= $f_type == 'daily' ? 'selected' : '' ?>>Daily Report</option>
                            <option value="monthly" <?= $f_type == 'monthly' ? 'selected' : '' ?>>Monthly Report</option>
                        </select>
                    </div>
                    <div class="input-group" id="date_input_group" style="flex: 1;">
                        <label>Select Date</label>
                        <input type="date" name="date" value="<?= $f_date ?>">
                    </div>
                    <div class="input-group" id="month_input_group" style="flex: 1; display:none;">
                        <label>Select Month</label>
                        <input type="month" name="month" value="<?= $f_month ?>">
                    </div>
                    <div class="input-group" style="flex: 0.5; display: flex; align-items: flex-end;">
                        <button type="submit" class="save-btn" style="width: 100%; height: 42px;">Generate</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php if ($f_course): ?>
        <div class="stats-grid">
            <div class="stat-box" style="background: #4f46e5;"><small>Total Students</small><div><?= $total_records ?></div></div>
            <div class="stat-box" style="background: #f59e0b;"><small>Overall Rate (%)</small><div><?= $attendance_percentage ?>%</div></div>
            <?php if ($f_type == 'monthly'): ?>
                <div class="stat-box" style="background: #10b981;"><small>Total Class Days</small><div><?= $total_class_days ?></div></div>
            <?php endif; ?>
        </div>

        <div class="card" style="padding: 0; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f4f4f4; text-align: left;">
                        <th style="padding: 12px;">Roll No</th>
                        <th style="padding: 12px;">Student Name</th>
                        <?php if ($f_type == 'daily'): ?>
                            <th style="padding: 12px; text-align: center;">Status</th>
                        <?php else: ?>
                            <th style="padding: 12px; text-align: center;">Present Days</th>
                            <th style="padding: 12px; text-align: center;">Percentage</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report_data as $row): ?>
                    <tr>
                        <td style="padding: 12px;"><?= $row['roll_no'] ?></td>
                        <td style="padding: 12px;"><?= $row['name'] ?></td>
                        <?php if ($f_type == 'daily'): $is_p = (strtolower($row['status']) == 'present'); ?>
                            <td style="padding: 12px; text-align: center;">
                                <span class="badge" style="background: <?= $is_p ? '#10b981' : '#ef4444' ?>;">
                                    <?= $is_p ? 'Present' : 'Absent' ?>
                                </span>
                            </td>
                        <?php else: 
                            $percent = ($row['total_days'] > 0) ? round(($row['days_present'] / $row['total_days']) * 100, 1) : 0;
                        ?>
                            <td style="padding: 12px; text-align: center;"><?= $row['days_present'] ?> / <?= $row['total_days'] ?></td>
                            <td style="padding: 12px; text-align: center; font-weight: bold; color: <?= $percent < 75 ? '#ef4444' : '#10b981' ?>;">
                                <?= $percent ?>%
                            </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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

    function toggleDateInput() {
        const type = document.getElementById('report_type').value;
        document.getElementById('date_input_group').style.display = (type === 'daily') ? 'block' : 'none';
        document.getElementById('month_input_group').style.display = (type === 'monthly') ? 'block' : 'none';
    }

    window.onload = function() {
        updateCourseFilter();
        toggleDateInput();
    };
</script>
</body>
</html>