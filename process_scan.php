<?php
require_once 'database/database.php';
$db = new Database();

date_default_timezone_set('Asia/Yangon');

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['rfid_uid'])) {
    $uid = isset($_POST['rfid_uid']) ? trim($_POST['rfid_uid']) : trim($_GET['rfid_uid']);
    $date = date('Y-m-d');
    $current_time = date('H:i:s');
    $day_of_week = date('l');

    // search student by rfid_uid
    $stmt = $db->conn->prepare("SELECT id, name, roll_no, major_id, photo FROM student_details WHERE rfid_uid = ?");
    $stmt->execute([$uid]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        echo json_encode(['success' => false, 'message' => 'Invalid RFID Card!']);
        exit;
    }

    // Get the current academic year for the student
    $reg_year_stmt = $db->conn->prepare("SELECT academic_year FROM course_registration WHERE student_id = ? ORDER BY id DESC LIMIT 1");
    $reg_year_stmt->execute([$student['id']]);
    $current_academic_year = $reg_year_stmt->fetchColumn();

    if (!$current_academic_year) {
        // Student not registered
        echo json_encode(['success' => false, 'message' => 'Student not registered!']);
        exit;
    }

    // check current class from timetable
    $time_sql = "SELECT t.course_id, t.period, c.title as course_title, c.total_classes 
                 FROM timetable t
                 JOIN course_details c ON t.course_id = c.id
                 WHERE t.major_id = ? 
                 AND t.day_of_week = ? 
                 AND t.academic_year = ?
                 AND ? BETWEEN DATE_SUB(t.start_time, INTERVAL 15 MINUTE) AND t.end_time 
                 LIMIT 1";

    $time_stmt = $db->conn->prepare($time_sql);
    $time_stmt->execute([$student['major_id'], $day_of_week, $current_academic_year, $current_time]);
    $current_class = $time_stmt->fetch(PDO::FETCH_ASSOC);

    if ($current_class) {
        $course_id = $current_class['course_id'];
        $period = $current_class['period'];

        // check registration for the course
        $reg_check_stmt = $db->conn->prepare("SELECT id FROM course_registration WHERE student_id = ? AND course_id = ? AND academic_year = ?");
        $reg_check_stmt->execute([$student['id'], $course_id, $current_academic_year]);

        if (!$reg_check_stmt->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => "Not Registered for " . $current_class['course_title'] . " ($current_academic_year)",
                'name' => $student['name'],
                'photo' => $student['photo'] ?: 'default.png'
            ]);
            exit;
        }

        // process_scan.php ရဲ့ အဓိက အစိတ်အပိုင်း
        $check = $db->conn->prepare("SELECT id, status FROM attendance_details WHERE student_id = ? AND course_id = ? AND on_date = ?");
        $check->execute([$student['id'], $course_id, $date]);
        $existing_attendance = $check->fetch(PDO::FETCH_ASSOC);

        if (!$existing_attendance) {
            // သစ်လွင်သော Record သွင်းခြင်း
            $ins = $db->conn->prepare("INSERT INTO attendance_details (student_id, course_id, on_date, on_time, status) VALUES (?, ?, ?, ?, 'Present')");
            $ins->execute([$student['id'], $course_id, $date, $current_time]);
            $msg = 'Attendance Marked!';
        } else {
            // အကယ်၍ Manual Save ကြောင့် Absent ဖြစ်နေရင် Present သို့ ပြောင်းပေးရမည်
            if ($existing_attendance['status'] !== 'Present') {
                $upd = $db->conn->prepare("UPDATE attendance_details SET status = 'Present', on_time = ? WHERE id = ?");
                $upd->execute([$current_time, $existing_attendance['id']]);
                $msg = 'Changed from Absent to Present!';
            } else {
                $msg = 'Already marked as Present';
            }
        }

        // calculate attendance percentage
        $total_expected = $current_class['total_classes'] ?: 45;
        $count_stmt = $db->conn->prepare("SELECT COUNT(*) FROM attendance_details WHERE student_id = ? AND course_id = ?");
        $count_stmt->execute([$student['id'], $course_id]);
        $attended_days = $count_stmt->fetchColumn();
        $percentage = round(($attended_days / $total_expected) * 100, 1);
        if ($percentage > 100) $percentage = 100;

        echo json_encode([
            'success' => true,
            'message' => $msg,
            'name' => $student['name'],
            'roll_no' => $student['roll_no'],
            'photo' => $student['photo'] ?: 'default.png',
            'course' => $current_class['course_title'],
            'percentage' => $percentage . '%'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => "No active class for " . $student['name'] . " at " . date('h:i A'),
            'photo' => $student['photo'] ?: 'default.png'
        ]);
    }
}
