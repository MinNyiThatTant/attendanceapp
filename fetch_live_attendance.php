<?php
require_once 'database/database.php';
$db = new Database();
date_default_timezone_set('Asia/Yangon');
$today_day = date('l'); 

$query = "SELECT a.*, s.name, c.title as course_name 
          FROM attendance_details a 
          JOIN student_details s ON a.student_id = s.id 
          JOIN course_details c ON a.course_id = c.id 
          -- join registration and timetable to filter only registered students for today
          JOIN course_registration cr ON a.student_id = cr.student_id AND a.course_id = cr.course_id
          JOIN timetable t ON a.course_id = t.course_id 
          WHERE a.on_date = CURDATE() 
          AND t.day_of_week = ? 
          ORDER BY a.id DESC";

$stmt = $db->conn->prepare($query);
$stmt->execute([$today_day]);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$logs) {
    echo "<tr><td colspan='4' style='text-align:center; color:#9ca3af; padding:20px;'>No registered students present for today.</td></tr>";
} else {
    foreach ($logs as $log) {
        echo "<tr>";
        echo "<td>" . date('h:i A') . "</td>";
        echo "<td><strong>" . htmlspecialchars($log['name']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($log['course_name']) . "</td>";
        echo "<td><span class='badge present-bg'>Present</span></td>";
        echo "</tr>";
    }
}
?>