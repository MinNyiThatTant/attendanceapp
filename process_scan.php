<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once 'database/database.php';
$db = new Database();
date_default_timezone_set('Asia/Yangon');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['rfid_uid'])) {
    $uid = isset($_POST['rfid_uid']) ? trim($_POST['rfid_uid']) : trim($_GET['rfid_uid']);
    $date = date('Y-m-d');
    $current_time = date('H:i:s');
    $day_of_week = date('l');

    $current_month = (int)date('m');
    $current_year = (int)date('Y');
    $current_academic_year = ($current_month < 6) ? ($current_year - 1) . "-" . $current_year : $current_year . "-" . ($current_year + 1);

    try {
        // take current_semester from student_details table
        $stmt = $db->conn->prepare("SELECT id, name, roll_no, major_id, current_semester, photo FROM student_details WHERE rfid_uid = ?");
        $stmt->execute([$uid]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            echo json_encode(['success' => false, 'message' => 'Unregistered RFID Card!']);
            exit;
        }

        $student_id = $student['id'];
        $student_sem = $student['current_semester']; 

        // Timetable Query
        $time_sql = "SELECT t.id, t.course_id, t.end_time, c.title as course_title, c.total_classes 
                     FROM timetable t
                     JOIN course_details c ON t.course_id = c.id
                     WHERE t.major_id = ? 
                     AND t.term = ? 
                     AND t.day_of_week = ? 
                     AND t.academic_year = ?
                     AND ? BETWEEN DATE_SUB(t.start_time, INTERVAL 15 MINUTE) AND t.end_time 
                     LIMIT 1";

        $time_stmt = $db->conn->prepare($time_sql);
        $time_stmt->execute([$student['major_id'], $student_sem, $day_of_week, $current_academic_year, $current_time]);
        $current_class = $time_stmt->fetch(PDO::FETCH_ASSOC);

        if ($current_class) {
            // Attendance Logic
            $course_id = $current_class['course_id'];
            $timetable_id = $current_class['id'];

            $check = $db->conn->prepare("SELECT id FROM attendance_details WHERE student_id = ? AND course_id = ? AND on_date = ? AND timetable_id = ?");
            $check->execute([$student_id, $course_id, $date, $timetable_id]);
            
            if (!$check->fetch()) {
                $ins = $db->conn->prepare("INSERT INTO attendance_details (student_id, course_id, on_date, on_time, status, academic_year, timetable_id) VALUES (?, ?, ?, ?, 'Present', ?, ?)");
                $ins->execute([$student_id, $course_id, $date, $current_time, $current_academic_year, $timetable_id]);
                $msg = 'Attendance Marked!';
            } else {
                $msg = 'Already Checked-in';
            }

            echo json_encode([
                'success' => true,
                'message' => $msg,
                'name' => $student['name'],
                'roll_no' => $student['roll_no'],
                'photo' => $student['photo'] ?: 'default.png',
                'type' => 'Attendance',
                'course' => $current_class['course_title']
            ]);
        } else {
            // Computer Lab Logic (check-in/check-out) 
            $check_lab = $db->conn->prepare("SELECT id FROM computer_usage_logs WHERE student_id = ? AND usage_date = ? AND check_out_time IS NULL LIMIT 1");
            $check_lab->execute([$student_id, $date]);
            $lab_record = $check_lab->fetch(PDO::FETCH_ASSOC);

            if ($lab_record) {
                // Check-out 
                $update_lab = $db->conn->prepare("UPDATE computer_usage_logs SET check_out_time = ? WHERE id = ?");
                $update_lab->execute([$current_time, $lab_record['id']]);
                $lab_msg = "Lab Check-out Success";
                $type = "Lab Out";
            } else {
                // Check-in (new record)
                $insert_lab = $db->conn->prepare("INSERT INTO computer_usage_logs (student_id, usage_date, check_in_time) VALUES (?, ?, ?)");
                $insert_lab->execute([$student_id, $date, $current_time]);
                $lab_msg = "Lab Check-in Success";
                $type = "Lab In";
            }

            echo json_encode([
                'success' => true, 
                'message' => $lab_msg, 
                'name' => $student['name'], 
                'roll_no' => $student['roll_no'],
                'photo' => $student['photo'] ?: 'default.png',
                'type' => $type,
                'course' => 'Computer Lab'
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
    }
}