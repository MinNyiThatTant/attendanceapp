<?php
// check space
ob_start(); 
require_once 'database/database.php';
$db = new Database();
$conn = $db->conn;

$f_major = $_GET['major_id'] ?? '';
$f_course = $_GET['course_id'] ?? '';
$f_month = $_GET['month'] ?? ''; // Format: 2024-05

if (!$f_course) exit("No course selected");


// SQL Query course registration + student details + major details + left join attendance details
$sql = "SELECT s.roll_no, s.name, m.title as major, r.academic_year,
               IFNULL(a.status, 'Absent') as status,
               IFNULL(a.on_date, '-') as on_date
        FROM course_registration r
        JOIN student_details s ON r.student_id = s.id
        JOIN major_details m ON s.major_id = m.id
        LEFT JOIN attendance_details a ON s.id = a.student_id 
            AND a.course_id = r.course_id 
            AND a.on_date LIKE ?
        WHERE r.course_id = ? ";

$params = [$f_month . '%', $f_course];

if ($f_major) {
    $sql .= " AND s.major_id = ?";
    $params[] = $f_major;
}

$sql .= " ORDER BY s.roll_no ASC, a.on_date ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// clean output buffer
if (ob_get_length()) ob_clean();

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Attendance_Report_" . $f_month . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "\xEF\xBB\xBF"; // UTF-8 BOM - to open in Excel correctly (Myanmar characters  )
echo "<table border='1'>";
echo "<tr>
        <th style='background-color: #4f46e5; color: white;'>Roll No</th>
        <th style='background-color: #4f46e5; color: white;'>Student Name</th>
        <th style='background-color: #4f46e5; color: white;'>Major</th>
        <th style='background-color: #4f46e5; color: white;'>Date</th>
        <th style='background-color: #4f46e5; color: white;'>Status</th>
      </tr>";

if (count($data) > 0) {
    foreach ($data as $row) {
        $status_color = (strtolower($row['status']) == 'present') ? '#10b981' : '#ef4444';
        echo "<tr>
                <td>" . htmlspecialchars($row['roll_no']) . "</td>
                <td>" . htmlspecialchars($row['name']) . "</td>
                <td>" . htmlspecialchars($row['major']) . "</td>
                <td style='text-align:center;'>" . htmlspecialchars($row['on_date']) . "</td>
                <td style='text-align:center; color: {$status_color}; font-weight:bold;'>" . htmlspecialchars($row['status']) . "</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='5' style='text-align:center;'>No records found for this period.</td></tr>";
}
echo "</table>";
exit;