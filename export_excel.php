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

// SQL Query Preparation
if ($f_type == 'daily') {
    echo "Roll No\tStudent Name\tMajor\tDate\tStatus\n";

    // Daily Query
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

    $params = [
        ':course_id' => $f_course,
        ':f_date' => $f_date,
        ':f_date_leave' => $f_date
    ];
} else {
    echo "Roll No\tStudent Name\tMajor\tPresent (RFID+Leave)\tAbsence Days\n";

    // Monthly Logic - Get total class days in the month
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

    $params = [
        ':course_id' => $f_course,
        ':f_month' => $f_month . '%'
    ];
}

if ($f_major) {
    $sql .= " AND s.major_id = :major_id";
    $params[':major_id'] = $f_major;
}

if ($f_type == 'monthly') {
    $sql .= " GROUP BY s.id";
}
$sql .= " ORDER BY s.roll_no ASC";

// Execute Query
$stmt = $conn->prepare($sql);
$stmt->execute($params); 
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Output Data 
foreach ($data as $row) {
    if ($f_type == 'daily') {
        echo $row['roll_no'] . "\t" . 
             $row['name'] . "\t" . 
             $row['major_name'] . "\t" . 
             $f_date . "\t" . 
             $row['final_status'] . "\n";
    } else {
        // Calculate leaves in the month
        $leave_stmt = $conn->prepare("SELECT from_date, to_date FROM student_leaves WHERE student_id = ? AND (from_date LIKE ? OR to_date LIKE ?)");
        $leave_stmt->execute([$row['id'], $f_month . '%', $f_month . '%']);
        $leaves = $leave_stmt->fetchAll();
        $l_days = 0;
        // (Simple leave count logic)
        foreach($leaves as $lv) { 
            $l_days++; // Count each leave record as 1 day for simplicity
        }
        
        $present_total = $row['rfid_present'] + $l_days;
        $absent_days = ($total_class_days > $present_total) ? ($total_class_days - $present_total) : 0;

        echo $row['roll_no'] . "\t" . 
             $row['name'] . "\t" . 
             $row['major_name'] . "\t" . 
             $present_total . "\t" . 
             $absent_days . "\n";
    }
}
exit;