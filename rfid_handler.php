<?php
// rfid_handler.php
require_once 'database/database.php';
$db = new Database();

if (isset($_GET['uid'])) {
    $uid = $_GET['uid'];
    $day = date('l'); 
    $time = date('H:i:s');
    $today = date('Y-m-d');

    // ၁။ Student နှင့် သူ၏ အချက်အလက်များကို ရှာဖွေခြင်း
    $stmt = $db->conn->prepare("SELECT id, name, major_id, academic_year, current_semester FROM student_details WHERE rfid_uid = ?");
    $stmt->execute([$uid]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        // ၂။ Timetable တွင် ယခုအချိန် သင်ကြားနေသော Course ကို ရှာခြင်း
        $stmt = $db->conn->prepare("SELECT course_id, period FROM timetable 
                                    WHERE major_id = ? AND day_of_week = ?");
                                     // AND ? BETWEEN start_time AND end_time");
        // $stmt->execute([$student['major_id'], $day, $time]);
        $stmt->execute([$student['major_id'], $day]);
        $timetable = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($timetable) {
            // ၃။ Duplicate စစ်ဆေးခြင်း (တစ်ချိန်တည်း နှစ်ခါမဝင်စေရန်)
            $check = $db->conn->prepare("SELECT id FROM attendance_details 
                                         WHERE student_id = ? AND course_id = ? AND on_date = ? AND period = ?");
            $check->execute([$student['id'], $timetable['course_id'], $today, $timetable['period']]);
            
            if (!$check->fetch()) {
                // ၄။ Attendance သိမ်းဆည်းခြင်း (သင့် Table Column များအတိုင်း)
                // faculty_id နဲ့ session_id ကို လိုအပ်သလို default သို့မဟုတ် query ထဲမှ တွက်ယူပါ
                $sql = "INSERT INTO attendance_details (student_id, course_id, period, on_date, status, academic_year) 
                        VALUES (?, ?, ?, ?, 'present', ?)";
                $db->conn->prepare($sql)->execute([
                    $student['id'], 
                    $timetable['course_id'], 
                    $timetable['period'], 
                    $today, 
                    $student['academic_year']
                ]);
                
                echo json_encode(["status" => "success", "message" => "Welcome " . $student['name']]);
            } else {
                echo json_encode(["status" => "error", "message" => "Already marked!"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "No class scheduled now."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid Card ID!"]);
    }
}