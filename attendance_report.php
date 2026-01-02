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
$attendance_percentage = 0;

if ($f_course) {
    if ($f_type == 'daily') {
    // --- Daily Query Fixed Version ---
$sql = "SELECT s.name, s.roll_no, m.title as major_name,
               CASE 
                 WHEN a.status = 'Present' THEN 'Present'
                 -- Date ကို string format သေချာပြောင်းပြီး စစ်မယ်
                 WHEN l.id IS NOT NULL THEN 'Leave'
                 ELSE 'Absent'
               END as status
        FROM course_registration r
        JOIN student_details s ON r.student_id = s.id
        JOIN major_details m ON s.major_id = m.id
        LEFT JOIN attendance_details a ON s.id = a.student_id 
            AND a.course_id = r.course_id 
            AND a.on_date = :f_date
        -- ဒီနေရာမှာ Date Range ကို သေချာပြန်ညှိထားပါတယ်
        LEFT JOIN student_leaves l ON s.id = l.student_id 
            AND DATE(:f_date2) >= DATE(l.from_date) 
            AND DATE(:f_date3) <= DATE(l.to_date)
        WHERE r.course_id = :course_id";

$params = [
    ':course_id' => $f_course, 
    ':f_date' => $f_date,
    ':f_date2' => $f_date,
    ':f_date3' => $f_date
];
} else {
        // --- Monthly Query (ခွင့်ရက်ကို Present ထဲ ပေါင်းတွက်မယ်) ---
        
        // အတန်းရှိခဲ့သော ရက်စုစုပေါင်း
        $stmt_days = $conn->prepare("SELECT COUNT(DISTINCT on_date) FROM attendance_details WHERE course_id = ? AND on_date LIKE ?");
        $stmt_days->execute([$f_course, $f_month . '%']);
        $total_class_days = $stmt_days->fetchColumn() ?: 0;

        $sql = "SELECT s.id, s.name, s.roll_no, m.title as major_name,
                       -- RFID နဲ့ တက်တဲ့ရက်
                       COUNT(DISTINCT a.on_date) as rfid_present,
                       -- ခွင့်ယူထားတဲ့ရက် (ဒီလအတွင်းကျရောက်သော ရက်များကိုသာ တွက်သည်)
                       (SELECT IFNULL(SUM(DATEDIFF(LEAST(l.to_date, LAST_DAY(STR_TO_DATE(:f_month2, '%Y-%m-%d'))), 
                                                 GREATEST(l.from_date, STR_TO_DATE(:f_month3, '%Y-%m-%d'))) + 1), 0)
                        FROM student_leaves l 
                        WHERE l.student_id = s.id 
                        AND l.from_date <= LAST_DAY(STR_TO_DATE(:f_month4, '%Y-%m-%d')) 
                        AND l.to_date >= STR_TO_DATE(:f_month5, '%Y-%m-%d')) as leave_days
                FROM course_registration r
                JOIN student_details s ON r.student_id = s.id
                JOIN major_details m ON s.major_id = m.id
                LEFT JOIN attendance_details a ON s.id = a.student_id 
                    AND a.course_id = r.course_id 
                    AND a.on_date LIKE :f_month
                WHERE r.course_id = :course_id";
        
        // ပိုတိကျအောင် Month string ကို date format ပြောင်းသုံးပါတယ်
        $month_start = $f_month . "-01";
        $params = [
            ':course_id' => $f_course, 
            ':f_month' => $f_month . '%',
            ':f_month2' => $month_start, ':f_month3' => $month_start,
            ':f_month4' => $month_start, ':f_month5' => $month_start
        ];
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
        $present_count = 0;
        foreach ($report_data as $row) {
            // Present ရော Leave ရောကို တက်ရောက်သူအဖြစ် သတ်မှတ်မယ်
            if ($row['status'] == 'Present' || $row['status'] == 'Leave') $present_count++;
        }
        $attendance_percentage = ($total_records > 0) ? round(($present_count / $total_records) * 100, 1) : 0;
    } else {
        $total_p_days = 0;
        $total_possible_days = $total_records * $total_class_days;
        foreach ($report_data as $row) { 
            $student_total = $row['rfid_present'] + $row['leave_days'];
            // ကျောင်းဖွင့်ရက်ထက် မကျော်အောင် Limit လုပ်မယ်
            $total_p_days += ($student_total > $total_class_days) ? $total_class_days : $student_total; 
        }
        $attendance_percentage = ($total_possible_days > 0) ? round(($total_p_days / $total_possible_days) * 100, 1) : 0;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Report</title>
    <link rel="stylesheet" href="css/attendance.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
        <h1>📊 Attendance <span style="color:#4f46e5">Report</span></h1>
        <div style="display: flex; gap: 10px;">
            <?php if ($f_course): ?>
                <a href="export_excel.php?major_id=<?= $f_major ?>&course_id=<?= $f_course ?>&report_type=<?= $f_type ?>&date=<?= $f_date ?>&month=<?= $f_month ?>" class="btn-excel">📊 Export Excel</a>
            <?php endif; ?>
            <a href="dashboard.php" class="class-btn" style="text-decoration:none; background:lightblue;"><i class="fa-solid fa-house"></i> Back To Dashboard</a>
        </div>
    </header>

    <div class="card" style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <form method="GET">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div class="input-group">
                    <label>Major</label>
                    <select name="major_id" id="major_filter" onchange="updateCourseFilter()" style="width:100%; padding:8px;">
                        <option value="">All Majors</option>
                        <?php foreach ($majors as $m): ?>
                            <option value="<?= $m['id'] ?>" <?= $f_major == $m['id'] ? 'selected' : '' ?>><?= $m['title'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label>Course</label>
                    <select name="course_id" id="course_filter" required style="width:100%; padding:8px;">
                        <option value="">-- Choose Course --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>" data-majors="<?= $c['assigned_majors'] ?>" <?= $f_course == $c['id'] ? 'selected' : '' ?>>
                                <?= $c['code'] ?> - <?= $c['title'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label>Report Type</label>
                    <select name="report_type" id="report_type" onchange="toggleDateInput()" style="width:100%; padding:8px;">
                        <option value="daily" <?= $f_type == 'daily' ? 'selected' : '' ?>>Daily Report</option>
                        <option value="monthly" <?= $f_type == 'monthly' ? 'selected' : '' ?>>Monthly Report</option>
                    </select>
                </div>
                <div class="input-group" id="date_input_group">
                    <label>Select Date</label>
                    <input type="date" name="date" value="<?= $f_date ?>" style="width:100%; padding:7px;">
                </div>
                <div class="input-group" id="month_input_group" style="display:none;">
                    <label>Select Month</label>
                    <input type="month" name="month" value="<?= $f_month ?>" style="width:100%; padding:7px;">
                </div>
                <div style="display: flex; align-items: flex-end;">
                    <button type="submit" class="save-btn" style="width: 100%; height: 40px; background:#4f46e5; color:white; border:none; border-radius:5px; cursor:pointer;">Generate</button>
                </div>
            </div>
        </form>
    </div>

    <?php if ($f_course): ?>
        <div class="stats-grid">
            <div class="stat-box" style="background: #4f46e5;"><small>Total Students</small><div style="font-size:1.5rem; font-weight:bold;"><?= $total_records ?></div></div>
            <div class="stat-box" style="background: #f59e0b;"><small>Overall Attendance Rate</small><div style="font-size:1.5rem; font-weight:bold;"><?= $attendance_percentage ?>%</div></div>
            <?php if ($f_type == 'monthly'): ?>
                <div class="stat-box" style="background: #10b981;"><small>Total Class Days</small><div style="font-size:1.5rem; font-weight:bold;"><?= $total_class_days ?></div></div>
            <?php endif; ?>
        </div>

        <div class="card" style="background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc; text-align: left; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 15px;">Roll No</th>
                        <th style="padding: 15px;">Student Name</th>
                        <?php if ($f_type == 'daily'): ?>
                            <th style="padding: 15px; text-align: center;">Status</th>
                        <?php else: ?>
                            <th style="padding: 15px; text-align: center;">Attendance (RFID + Leave)</th>
                            <th style="padding: 15px; text-align: center;">Percentage</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report_data as $row): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 15px;"><?= $row['roll_no'] ?></td>
                        <td style="padding: 15px; font-weight: 500;"><?= htmlspecialchars($row['name']) ?></td>
                        
                        <?php if ($f_type == 'daily'): ?>
                            <td style="padding: 15px; text-align: center;">
                                <?php 
                                    $bg = '#ef4444'; // Absent
                                    if ($row['status'] == 'Present') $bg = '#10b981';
                                    if ($row['status'] == 'Leave') $bg = '#f59e0b';
                                ?>
                                <span class="badge" style="background: <?= $bg ?>;">
                                    <?= $row['status'] ?>
                                </span>
                            </td>
                        <?php else: 
                            $combined_present = $row['rfid_present'] + $row['leave_days'];
                            if ($combined_present > $total_class_days) $combined_present = $total_class_days;
                            $percent = ($total_class_days > 0) ? round(($combined_present / $total_class_days) * 100, 1) : 0;
                        ?>
                            <td style="padding: 15px; text-align: center;">
                                <strong><?= $combined_present ?></strong> / <?= $total_class_days ?>
                                <div style="font-size: 0.75rem; color: #64748b;">
                                    (RFID: <?= $row['rfid_present'] ?> + Leave: <?= $row['leave_days'] ?>)
                                </div>
                            </td>
                            <td style="padding: 15px; text-align: center;">
                                <div style="width: 100px; background: #e2e8f0; height: 8px; border-radius: 4px; margin: 5px auto;">
                                    <div style="width: <?= $percent ?>%; background: <?= $percent < 75 ? '#ef4444' : '#10b981' ?>; height: 100%; border-radius: 4px;"></div>
                                </div>
                                <span style="font-weight: bold; color: <?= $percent < 75 ? '#ef4444' : '#10b981' ?>;">
                                    <?= $percent ?>%
                                </span>
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