<?php
require_once 'database/database.php';
$db = new Database();
$conn = $db->conn;

$f_major = $_GET['major_id'] ?? '';
$f_course = $_GET['course_id'] ?? '';
$f_month = $_GET['month'] ?? '';

if (!$f_course) exit("No course selected");

// SQL logic
$sql = "SELECT a.on_date, s.roll_no, s.name, m.title as major, a.status 
        FROM attendance_details a
        JOIN student_details s ON a.student_id = s.id
        JOIN major_details m ON s.major_id = m.id
        WHERE a.course_id = ? AND a.on_date LIKE ? ";

$params = [$f_course, $f_month . '%'];
if ($f_major) { $sql .= " AND s.major_id = ? "; $params[] = $f_major; }

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Excel Header & Encoding Fix 
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Attendance_Report_" . $f_month . ".xls");

// UTF-8 BOM 
echo "\xEF\xBB\xBF"; 

// Table format 
echo "<table border='1'>";
echo "<tr>
        <th style='background-color: #4f46e5; color: white;'>Date</th>
        <th style='background-color: #4f46e5; color: white;'>Roll No</th>
        <th style='background-color: #4f46e5; color: white;'>Student Name</th>
        <th style='background-color: #4f46e5; color: white;'>Major</th>
        <th style='background-color: #4f46e5; color: white;'>Status</th>
      </tr>";

foreach ($data as $row) {
    echo "<tr>";
    echo "<td>" . $row['on_date'] . "</td>";
    echo "<td>" . $row['roll_no'] . "</td>";
    echo "<td>" . $row['name'] . "</td>"; 
    echo "<td>" . $row['major'] . "</td>"; 
    echo "<td>" . $row['status'] . "</td>";
    echo "</tr>";
}
echo "</table>";
exit;