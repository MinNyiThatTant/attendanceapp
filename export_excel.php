<?php
ob_start(); 
require_once 'database/database.php';
$db = new Database();
$conn = $db->conn;

$f_major = $_GET['major_id'] ?? '';
$f_course = $_GET['course_id'] ?? '';
$f_type = $_GET['report_type'] ?? 'daily';
$f_date = $_GET['date'] ?? date('Y-m-d');
$f_month = $_GET['month'] ?? date('Y-m');

if (!$f_course) exit("No course selected");

if ($f_type == 'daily') {
    $sql = "SELECT s.roll_no, s.name, m.title as major, IFNULL(a.status, 'Absent') as status, :manual_date as on_date 
            FROM course_registration r
            JOIN student_details s ON r.student_id = s.id
            JOIN major_details m ON s.major_id = m.id
            LEFT JOIN attendance_details a ON s.id = a.student_id AND a.course_id = r.course_id AND a.on_date = :manual_date
            WHERE r.course_id = :course_id";
    $params = [':manual_date' => $f_date, ':course_id' => $f_course];
    $filename = "Daily_Attendance_" . $f_date;
} else {
    // အတန်းရှိခဲ့သော ရက်ပေါင်းကို အရင်ရှာ
    $stmt_days = $conn->prepare("SELECT COUNT(DISTINCT on_date) FROM attendance_details WHERE course_id = ? AND on_date LIKE ?");
    $stmt_days->execute([$f_course, $f_month . '%']);
    $total_class_days = $stmt_days->fetchColumn() ?: 0;

    $sql = "SELECT s.roll_no, s.name, m.title as major, 
                   COUNT(CASE WHEN a.status = 'Present' THEN 1 END) as days_present,
                   $total_class_days as total_days
            FROM course_registration r
            JOIN student_details s ON r.student_id = s.id
            JOIN major_details m ON s.major_id = m.id
            LEFT JOIN attendance_details a ON s.id = a.student_id AND a.course_id = r.course_id AND a.on_date LIKE :f_month
            WHERE r.course_id = :course_id";
    $params = [':f_month' => $f_month . '%', ':course_id' => $f_course];
    $filename = "Monthly_Attendance_" . $f_month;
}

if ($f_major) {
    $sql .= " AND s.major_id = :major_id";
    $params[':major_id'] = $f_major;
}
if ($f_type == 'monthly') $sql .= " GROUP BY s.id";
$sql .= " ORDER BY s.roll_no ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (ob_get_length()) ob_clean();

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=$filename.xls");

echo "\xEF\xBB\xBF"; 
echo "<table border='1'><tr>";
if ($f_type == 'daily') {
    echo "<th>Roll No</th><th>Student Name</th><th>Major</th><th>Date</th><th>Status</th>";
} else {
    echo "<th>Roll No</th><th>Student Name</th><th>Major</th><th>Present Days</th><th>Total Days</th><th>Percentage</th>";
}
echo "</tr>";

foreach ($data as $row) {
    echo "<tr>";
    echo "<td>{$row['roll_no']}</td><td>{$row['name']}</td><td>{$row['major']}</td>";
    if ($f_type == 'daily') {
        echo "<td>{$row['on_date']}</td><td>{$row['status']}</td>";
    } else {
        $percent = ($row['total_days'] > 0) ? round(($row['days_present'] / $row['total_days']) * 100, 1) : 0;
        echo "<td>{$row['days_present']}</td><td>{$row['total_days']}</td><td>{$percent}%</td>";
    }
    echo "</tr>";
}
echo "</table>";
exit;