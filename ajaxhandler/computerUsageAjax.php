<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../database/database.php';
date_default_timezone_set('Asia/Yangon');

header('Content-Type: application/json; charset=utf-8');

$db = new Database();
$rfid = $_POST['rfid_uid'] ?? '';

if (empty($rfid)) {
    echo json_encode(["status" => "error", "message" => "Invalid RFID"]);
    exit;
}

$current_time = date('H:i');
$today = date('Y-m-d');

try {
    $stmt = $db->conn->prepare("SELECT id, name, photo FROM student_details WHERE rfid_uid = ?");
    $stmt->execute([$rfid]);
    $student = $stmt->fetch();

    if (!$student) {
        echo json_encode(["status" => "error", "message" => "Student not found!"]);
        exit;
    }

    $student_id = $student['id'];
    $check = $db->conn->prepare("SELECT id FROM computer_usage_logs WHERE student_id = ? AND usage_date = ? AND check_out_time IS NULL");
    $check->execute([$student_id, $today]);
    $record = $check->fetch();

    if ($record) {
        $update = $db->conn->prepare("UPDATE computer_usage_logs SET check_out_time = NOW() WHERE id = ?");
        $update->execute([$record['id']]);
        echo json_encode(["status" => "success", "message" => "Goodbye, " . $student['name'], "photo" => $student['photo']]);
    } else {
        $insert = $db->conn->prepare("INSERT INTO computer_usage_logs (student_id, usage_date, check_in_time) VALUES (?, ?, NOW())");
        $insert->execute([$student_id, $today]);
        echo json_encode(["status" => "success", "message" => "Welcome, " . $student['name'], "photo" => $student['photo']]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}