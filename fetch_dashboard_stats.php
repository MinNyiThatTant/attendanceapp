<?php
require_once 'database/database.php';
$db = new Database();

$today_day = date('l');
$current_academic_year = "2025-2026"; 

// 1. Present Count
$stmt_present = $db->conn->prepare("SELECT COUNT(DISTINCT student_id) FROM attendance_details WHERE on_date = CURDATE() AND status = 'Present'");
$stmt_present->execute();
$total_present = $stmt_present->fetchColumn() ?: 0;

// 2. Expected Count
$stmt_total_reg = $db->conn->prepare("
    SELECT COUNT(DISTINCT cr.student_id) 
    FROM course_registration cr
    JOIN timetable t ON cr.course_id = t.course_id
    WHERE t.day_of_week = ? AND t.academic_year = ?
");
$stmt_total_reg->execute([$today_day, $current_academic_year]);
$total_expected = $stmt_total_reg->fetchColumn() ?: 0;

// 3. Latest Logs
$stmt_log = $db->conn->prepare("
    SELECT ad.*, s.name as student_name, c.title as course_name 
    FROM attendance_details ad
    JOIN student_details s ON ad.student_id = s.id
    JOIN course_details c ON ad.course_id = c.id
    WHERE ad.on_date = CURDATE() 
    AND (ad.academic_year = :ay OR ad.academic_year IS NULL)
    ORDER BY ad.on_time DESC
");
$stmt_log->execute([':ay' => $current_academic_year]); // Parameter ကို bind လုပ်လိုက်သည်
$logs = $stmt_log->fetchAll(PDO::FETCH_ASSOC);

$table_html = "";
foreach($logs as $log) {
    $status = $log['status'];
    $color = (strtolower($status) == 'absent') ? '#ef4444' : '#10b981';
    $table_html .= "<tr>
        <td>".date('h:i A', strtotime($log['on_time']))."</td>
        <td>".htmlspecialchars($log['student_name'])."</td>
        <td>".htmlspecialchars($log['course_name'])."</td>
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