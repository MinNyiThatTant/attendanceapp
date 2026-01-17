<?php
require_once __DIR__ . '/../database/database.php';
date_default_timezone_set('Asia/Yangon');

$db = new Database();
$rfid = $_POST['rfid_uid'] ?? '';

if (empty($rfid)) {
    echo json_encode(["status" => "error", "message" => "Invalid RFID"]);
    exit;
}

$current_time = date('H:i');
$today = date('Y-m-d');
$day_name = date('l');

// --- အချိန်နှင့် နေ့ရက် ကန့်သတ်ချက်များ ---
// if ($day_name == "Saturday" || $day_name == "Sunday") {
//     echo json_encode(["status" => "error", "message" => "Lab is closed on weekends!"]);
//     exit;
// }

// if ($current_time < '09:00' || $current_time > '16:00') {
//     echo json_encode(["status" => "error", "message" => "Lab open: 9 AM - 4 PM only."]);
//     exit;
// }

if ($current_time >= '12:00' && $current_time <= '13:00') {
    echo json_encode(["status" => "error", "message" => "Lunch Break (12 PM - 1 PM)"]);
    exit;
}

try {
    // ၁. ကျောင်းသားရှိမရှိ အရင်စစ်မယ်
    $stmt = $db->conn->prepare("SELECT id, name FROM student_details WHERE rfid_uid = ?");
    $stmt->execute([$rfid]);
    $student = $stmt->fetch();

    if (!$student) {
        echo json_encode(["status" => "error", "message" => "Student not found!"]);
        exit;
    }

    $student_id = $student['id'];

    // ၂. ဒီနေ့အတွက် Check-in ဝင်ထားပြီး Check-out မလုပ်ရသေးတဲ့ record ရှိလားစစ်မယ်
    $check = $db->conn->prepare("SELECT id FROM computer_usage_logs WHERE student_id = ? AND usage_date = ? AND check_out_time IS NULL");
    $check->execute([$student_id, $today]);
    $record = $check->fetch();

    if ($record) {
        // ရှိနေရင် Check-out လုပ်ပေးမယ်
        $update = $db->conn->prepare("UPDATE computer_usage_logs SET check_out_time = NOW() WHERE id = ?");
        $update->execute([$record['id']]);
        echo json_encode(["status" => "success", "message" => "Goodbye, " . $student['name'] . "! (Check-out)"]);
    } else {
        // မရှိရင် Check-in သစ် ဆောက်မယ်
        $insert = $db->conn->prepare("INSERT INTO computer_usage_logs (student_id, usage_date, check_in_time) VALUES (?, ?, NOW())");
        $insert->execute([$student_id, $today]);
        echo json_encode(["status" => "success", "message" => "Welcome, " . $student['name'] . "! (Check-in)"]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}