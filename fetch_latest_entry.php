<?php
require_once 'database/database.php';
$db = new Database();

// Fetch the latest attendance entry for today
$sql = "SELECT a.id, a.course_id, s.name, s.roll_no, s.photo, c.title as course_title, c.total_classes
        FROM attendance_details a
        JOIN student_details s ON a.student_id = s.id
        JOIN course_details c ON a.course_id = c.id
        WHERE a.on_date = CURDATE()
        ORDER BY a.id DESC LIMIT 1";

$row = $db->conn->query($sql)->fetch(PDO::FETCH_ASSOC);

if ($row) {
    // Percentage calculation
    $total_expected = $row['total_classes'] ?: 45;
    $count_stmt = $db->conn->prepare("SELECT COUNT(*) FROM attendance_details WHERE student_id = (SELECT student_id FROM attendance_details WHERE id = ?) AND course_id = ?");
    $count_stmt->execute([$row['id'], $row['course_id']]);
    $attended_days = $count_stmt->fetchColumn();
    $percentage = round(($attended_days / $total_expected) * 100, 1);

    echo json_encode([
        'success' => true,
        'entry_id' => $row['id'],
        'name' => $row['name'],
        'roll_no' => $row['roll_no'],
        'photo' => $row['photo'],
        'course' => $row['course_title'],
        'percentage' => $percentage
    ]);
} else {
    echo json_encode(['success' => false]);
}