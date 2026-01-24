<?php
session_start();
require_once 'database/database.php';

if (empty($_SESSION["current_user"])) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$conn = $db->conn;

// Fetch Majors and Courses for Filters
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
$total_class_days = 0;

if ($f_course) {
    if ($f_type == 'daily') {
        // --- Daily Query ---
        $sql = "SELECT s.id, s.name, s.roll_no, m.title as major_name,
                       CASE 
                         WHEN a.status = 'Present' THEN 'Present'
                         WHEN l.id IS NOT NULL THEN 'Leave'
                         ELSE 'Absent'
                       END as attendance_status
                FROM course_registration r
                JOIN student_details s ON r.student_id = s.id
                JOIN major_details m ON s.major_id = m.id
                LEFT JOIN attendance_details a ON s.id = a.student_id 
                    AND a.course_id = r.course_id 
                    AND a.on_date = :f_date
                LEFT JOIN student_leaves l ON s.id = l.student_id 
                    AND :f_date_leave BETWEEN l.from_date AND l.to_date
                WHERE r.course_id = :course_id";

        $params = [':course_id' => $f_course, ':f_date' => $f_date, ':f_date_leave' => $f_date];
    } else {
        // --- Monthly Query ---
        $stmt_days = $conn->prepare("SELECT COUNT(DISTINCT on_date) FROM attendance_details WHERE course_id = ? AND on_date LIKE ?");
        $stmt_days->execute([$f_course, $f_month . '%']);
        $total_class_days = $stmt_days->fetchColumn() ?: 0;

        $sql = "SELECT s.id, s.name, s.roll_no, m.title as major_name,
                       COUNT(DISTINCT a.on_date) as rfid_present
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

    // Monthly Calculation and 75% Logic
    if ($f_type == 'monthly') {
        foreach ($report_data as &$row) {
            $leave_stmt = $conn->prepare("SELECT from_date, to_date FROM student_leaves WHERE student_id = ? AND 
                                         (from_date LIKE ? OR to_date LIKE ? OR (from_date < ? AND to_date > ?))");
            $leave_stmt->execute([$row['id'], $f_month . '%', $f_month . '%', $f_month . '-01', $f_month . '-01']);
            $leaves = $leave_stmt->fetchAll();
            
            $l_days = 0;
            $m_start = new DateTime($f_month . "-01");
            $m_end = new DateTime($m_start->format('Y-m-t'));

            foreach ($leaves as $lv) {
                $lv_start = new DateTime(max($lv['from_date'], $m_start->format('Y-m-d')));
                $lv_end = new DateTime(min($lv['to_date'], $m_end->format('Y-m-d')));
                while ($lv_start <= $lv_end) {
                    if ($lv_start->format('N') < 6) $l_days++;
                    $lv_start->modify('+1 day');
                }
            }
            $row['leave_days'] = $l_days;
            $total_present = $row['rfid_present'] + $l_days;
            $row['absent_days'] = ($total_class_days > $total_present) ? ($total_class_days - $total_present) : 0;
            
            // Percentage Calculation
            $row['percentage'] = ($total_class_days > 0) ? round(($total_present / $total_class_days) * 100, 2) : 0;
            // show low under 75% attendance flag
            $row['is_low_attendance'] = ($row['percentage'] < 75);
        }
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
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; color: #fff; }
        .absent-text { color: #ef4444; font-weight: bold; }
        .present-text { color: #10b981; font-weight: bold; }
        .low-attendance { background-color: #fff1f2; } /* for students under 75% attendance */
        .percentage-badge { padding: 4px 8px; border-radius: 5px; font-size: 0.85rem; }
        .bg-danger { background: #ef4444; color: white; }
        .bg-success { background: #10b981; color: white; }
    </style>
</head>
<body>

<div class="container">
    <header class="attendance-header" style="display:flex; justify-content: space-between; align-items: center; margin: 20px 0;">
        <h1>📊 Attendance <span style="color:#4f46e5">Report</span></h1>
        <div style="display: flex; gap: 10px;">
            <a href="dashboard.php" class="class-btn" style="text-decoration:none; background:#94a3b8;"><i class="fa-solid fa-house"></i> Dashboard</a>
        </div>
    </header>

    <div class="card" style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <form method="GET">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px;">
                <div class="input-group">
                    <label>Major</label>
                    <select name="major_id" id="major_filter" onchange="updateCourseFilter()">
                        <option value="">All Majors</option>
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
                            <option value="<?= $c['id'] ?>" data-majors="<?= $c['assigned_majors'] ?>" <?= $f_course == $c['id'] ? 'selected' : '' ?>>
                                <?= $c['code'] ?> - <?= $c['title'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label>Type</label>
                    <select name="report_type" id="report_type" onchange="toggleDateInput()">
                        <option value="daily" <?= $f_type == 'daily' ? 'selected' : '' ?>>Daily</option>
                        <option value="monthly" <?= $f_type == 'monthly' ? 'selected' : '' ?>>Monthly</option>
                    </select>
                </div>
                <div class="input-group" id="date_input_group">
                    <label>Date</label>
                    <input type="date" name="date" value="<?= $f_date ?>">
                </div>
                <div class="input-group" id="month_input_group" style="display:none;">
                    <label>Month</label>
                    <input type="month" name="month" value="<?= $f_month ?>">
                </div>
                <div style="display: flex; align-items: flex-end;">
                    <button type="submit" class="save-btn" style="width: 100%; background:#4f46e5;">Generate</button>
                </div>
            </div>
        </form>
    </div>

    <?php if ($f_course): ?>
        <div style="display: flex; justify-content: flex-end; margin-bottom: 15px;">
        <a href="export_excel.php?<?= $_SERVER['QUERY_STRING'] ?>" 
           class="class-btn" 
           style="background:#16a34a; text-decoration:none; display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; color: white; border-radius: 5px;">
            <i class="fa-solid fa-file-excel"></i> 
            Download <?= ucfirst($f_type) ?> Excel Report
        </a>
    </div>

        <div class="card" style="padding:0; overflow: hidden; border: 1px solid #e2e8f0;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc; text-align: left; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 15px;">Roll No</th>
                        <th style="padding: 15px;">Student Name</th>
                        <?php if ($f_type == 'daily'): ?>
                            <th style="padding: 15px; text-align: center;">Status</th>
                        <?php else: ?>
                            <th style="padding: 15px; text-align: center;">Present (RFID + Leave)</th>
                            <th style="padding: 15px; text-align: center;">Attendance %</th>
                            <th style="padding: 15px; text-align: center;">Remark</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $is_weekend = (date('N', strtotime($f_date)) >= 6);
                    foreach ($report_data as $row): 
                        $row_class = ($f_type == 'monthly' && $row['is_low_attendance']) ? 'low-attendance' : '';
                    ?>
                    <tr class="<?= $row_class ?>" style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 15px;"><?= $row['roll_no'] ?></td>
                        <td style="padding: 15px; font-weight: 500;">
                            <?= htmlspecialchars($row['name']) ?>
                            <?php if($f_type == 'monthly' && $row['is_low_attendance']): ?>
                                <i class="fa-solid fa-circle-exclamation" style="color:#ef4444; margin-left:5px;"></i>
                            <?php endif; ?>
                        </td>
                        
                        <?php if ($f_type == 'daily'): ?>
                            <td style="padding: 15px; text-align: center;">
                                <?php 
                                    if ($is_weekend) { echo '<span class="badge" style="background: #94a3b8;">Weekend</span>'; } 
                                    else {
                                        $status = $row['attendance_status'];
                                        $bg = ($status == 'Present') ? '#10b981' : (($status == 'Leave') ? '#f59e0b' : '#ef4444');
                                        echo '<span class="badge" style="background: '.$bg.';">'.$status.'</span>';
                                    }
                                ?>
                            </td>
                        <?php else: ?>
                            <td style="padding: 15px; text-align: center;">
                                <span class="present-text"><?= $row['rfid_present'] + $row['leave_days'] ?></span> / <?= $total_class_days ?>
                            </td>
                            <td style="padding: 15px; text-align: center;">
                                <span class="percentage-badge <?= $row['is_low_attendance'] ? 'bg-danger' : 'bg-success' ?>">
                                    <?= $row['percentage'] ?>%
                                </span>
                            </td>
                            <td style="padding: 15px; text-align: center;">
                                <?php if($row['is_low_attendance']): ?>
                                    <span class="absent-text">Incomplete (Under 75%)</span>
                                <?php else: ?>
                                    <span style="color:#10b981;">Qualified</span>
                                <?php endif; ?>
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