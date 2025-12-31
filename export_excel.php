<?php
ob_start(); 
require_once 'database/database.php';
$db = new Database();
$conn = $db->conn;

$f_major = $_GET['major_id'] ?? '';
$f_course = $_GET['course_id'] ?? '';
$f_month = $_GET['month'] ?? ''; // Format: 2024-05
$f_date = $_GET['date'] ?? date('Y-m-d'); // Date filter ပါ ထပ်ယူမည်

if (!$f_course) exit("No course selected");

// SQL Query ကို ပြင်လိုက်သည် - IFNULL နေရာမှာ Filter Date ကို သုံးမည်
$sql = "SELECT s.roll_no, s.name, m.title as major, r.academic_year,
               IFNULL(a.status, 'Absent') as status,
               -- ဒီနေရာမှာ Date မရှိရင် Filter date ကို ပြပါမယ် --
               IF(a.on_date IS NOT NULL, a.on_date, :manual_date) as on_date 
        FROM course_registration r
        JOIN student_details s ON r.student_id = s.id
        JOIN major_details m ON s.major_id = m.id
        LEFT JOIN attendance_details a ON s.id = a.student_id 
            AND a.course_id = r.course_id 
            AND a.on_date = :manual_date
        WHERE r.course_id = :course_id ";

$params = [
    ':manual_date' => $f_date, 
    ':course_id' => $f_course
];

if ($f_major) {
    $sql .= " AND s.major_id = :major_id";
    $params[':major_id'] = $f_major;
}

$sql .= " ORDER BY s.roll_no ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (ob_get_length()) ob_clean();

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Attendance_Report_" . $f_date . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "\xEF\xBB\xBF"; 
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
        $status_val = ucfirst(strtolower($row['status']));
        $status_color = ($status_val == 'Present' || $status_val == 'Checked in') ? '#10b981' : '#ef4444';
        
        echo "<tr>
                <td>" . htmlspecialchars($row['roll_no']) . "</td>
                <td>" . htmlspecialchars($row['name']) . "</td>
                <td>" . htmlspecialchars($row['major']) . "</td>
                <td style='text-align:center;'>" . htmlspecialchars($row['on_date']) . "</td>
                <td style='text-align:center; color: {$status_color}; font-weight:bold;'>" . $status_val . "</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='5' style='text-align:center;'>No records found.</td></tr>";
}
echo "</table>";
exit;