<?php
session_start();
require_once 'database/database.php';

if (empty($_SESSION["current_user"])) {
    exit("Unauthorized access");
}

$db = new Database();
$conn = $db->conn;

// Filter parameters
$f_major = $_GET['major_id'] ?? '';
$f_course = $_GET['course_id'] ?? '';
$f_type = $_GET['report_type'] ?? 'daily';
$f_date = $_GET['date'] ?? date('Y-m-d');
$f_month = $_GET['month'] ?? date('Y-m');

if (empty($f_course)) {
    exit("Course ID is required");
}

$filename = "Attendance_Report_" . ($f_type == 'daily' ? $f_date : $f_month) . ".xls";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// --- Header Row ---
if ($f_type == 'daily') {
    echo "Roll No\tStudent Name\tMajor\tDate\tStatus\n";
} else {
    // Monthly Report Header columns 
    echo "Roll No\tStudent Name\tMajor\tPresent (RFID+Leave)\tTotal Class Days\tPercentage (%)\tRemark\n";
}

// SQL Logic
if ($f_type == 'daily') {
    $sql = "SELECT s.roll_no, s.name, m.title as major_name,
                   CASE 
                     WHEN a.status = 'Present' THEN 'Present'
                     WHEN l.id IS NOT NULL THEN 'Leave'
                     ELSE 'Absent'
                   END as final_status
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
    // Get total class days in the month
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
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Output Data ---
foreach ($data as $row) {
    if ($f_type == 'daily') {
        echo $row['roll_no'] . "\t" . 
             $row['name'] . "\t" . 
             $row['major_name'] . "\t" . 
             $f_date . "\t" . 
             $row['final_status'] . "\n";
    } else {
        // Leave logic (accurate counting)
        $leave_stmt = $conn->prepare("SELECT from_date, to_date FROM student_leaves WHERE student_id = ? AND (from_date LIKE ? OR to_date LIKE ? OR (from_date < ? AND to_date > ?))");
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
        
        $present_total = $row['rfid_present'] + $l_days;
        
        // --- 75% Calculation ---
        $percentage = ($total_class_days > 0) ? round(($present_total / $total_class_days) * 100, 2) : 0;
        $remark = ($percentage < 75) ? "Incomplete (Under 75%)" : "Qualified";

        echo $row['roll_no'] . "\t" . 
             $row['name'] . "\t" . 
             $row['major_name'] . "\t" . 
             $present_total . "\t" . 
             $total_class_days . "\t" . 
             $percentage . "%\t" . 
             $remark . "\n";
    }
}
exit;