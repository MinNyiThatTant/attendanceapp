<?php
require_once 'database/database.php';
$db = new Database();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = $_POST['rfid_uid'];
    $date = date('Y-m-d');
    $current_time = date('H:i:s');
    $day_of_week = date('l'); // Monday, Tuesday, etc.

    // ၁။ search student by rfid_uid
    $stmt = $db->conn->prepare("SELECT id, name, roll_no, major_id FROM student_details WHERE rfid_uid = ?");
    $stmt->execute([$uid]);
    $student = $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        // ၂။ Find current class from timetable
        // match major_id, day_of_week, and current time
        $time_sql = "SELECT course_id, period FROM timetable 
                     WHERE major_id = ? 
                     AND day_of_week = ? 
                     AND ? BETWEEN start_time AND end_time 
                     LIMIT 1";
        $time_stmt = $db->conn->prepare($time_sql);
        $time_stmt->execute([$student['major_id'], $day_of_week, $current_time]);
        $current_class = $time_stmt->fetch(PDO::FETCH_ASSOC);

        if ($current_class) {
            $course_id = $current_class['course_id'];
            $period = $current_class['period'];

            // ၃။ Check duplicate attendance
            $check = $db->conn->prepare("SELECT id FROM attendance_details WHERE student_id = ? AND course_id = ? AND on_date = ?");
            $check->execute([$student['id'], $course_id, $date]);

            if (!$check->fetch()) {
                $ins = $db->conn->prepare("INSERT INTO attendance_details (student_id, course_id, on_date, status, period) VALUES (?, ?, ?, 'Present', ?)");
                $ins->execute([$student['id'], $course_id, $date, $period]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Attendance Marked!',
                    'name' => $student['name'],
                    'course' => 'Course ID: '.$course_id
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Already marked for this class!']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No active class for your major at this time!']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Student not found!']);
    }


    echo json_encode([
        'success' => true,
        'name' => $student['name'],
        'roll_no' => $student['roll_no'],
        'photo' => $student['photo'] // if you have photo column
    ]);
}