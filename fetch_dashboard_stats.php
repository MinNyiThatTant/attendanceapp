<?php
require_once 'database/database.php';
$db = new Database();

$today_day = date('l');

// attendance summary calculation
$stmt_present = $db->conn->prepare("SELECT COUNT(DISTINCT student_id) FROM attendance_details WHERE on_date = CURDATE() AND status = 'Present'");
$stmt_present->execute();
$total_present = $stmt_present->fetchColumn() ?: 0;

// attendance expected calculation
$stmt_total_reg = $db->conn->prepare("
    SELECT COUNT(DISTINCT cr.student_id) 
    FROM course_registration cr
    JOIN timetable t ON cr.course_id = t.course_id
    WHERE t.day_of_week = ?
");
$stmt_total_reg->execute([$today_day]);
$total_expected = $stmt_total_reg->fetchColumn() ?: 0;

// fetch latest attendance logs
$stmt_log = $db->conn->prepare("
    SELECT a.on_time, s.name, c.title as course_title, a.status 
    FROM attendance_details a
    JOIN student_details s ON a.student_id = s.id
    JOIN course_details c ON a.course_id = c.id
    WHERE a.on_date = CURDATE()
    ORDER BY a.id DESC LIMIT 10
");
$stmt_log->execute();
$logs = $stmt_log->fetchAll(PDO::FETCH_ASSOC);

$table_html = "";
foreach($logs as $log) {
    // determine status color
    $status = $log['status'];
    // absent = red, present = green
    $color = (strtolower($status) == 'absent') ? '#ef4444' : '#10b981';
    
    $table_html .= "<tr>
        <td>".date('h:i A', strtotime($log['on_time']))."</td>
        <td>".htmlspecialchars($log['name'])."</td>
        <td>".htmlspecialchars($log['course_title'])."</td>
        <td><span style='color: {$color}; font-weight:bold;'>● ".htmlspecialchars($status)."</span></td>
    </tr>";
}

if(empty($table_html)) {
    $table_html = "<tr><td colspan='4' style='text-align:center;'>No records for today yet.</td></tr>";
}

echo json_encode([
    'total_present' => (int)$total_present,
    'total_expected' => (int)$total_expected,
    'table_html' => $table_html
]);