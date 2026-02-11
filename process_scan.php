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
    // Academic Year 
    $current_academic_year = ($current_month < 6) ? ($current_year - 1) . "-" . $current_year : $current_year . "-" . ($current_year + 1);

    try {
        // check student
        $stmt = $db->conn->prepare("SELECT id, name, roll_no, major_id, current_semester, photo FROM student_details WHERE rfid_uid = ?");
        $stmt->execute([$uid]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            echo json_encode(['success' => false, 'message' => 'Unregistered RFID Card!']);
            exit;
        }

        $student_id = $student['id'];
        $student_sem = $student['current_semester']; 

        // search attendance and reterive session_id
        $time_sql = "SELECT t.id, t.course_id, t.end_time, c.title as course_title, c.session_id 
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
            // attendance logic
            $course_id = $current_class['course_id'];
            $timetable_id = $current_class['id'];
            $session_id = $current_class['session_id'];

            // today check in
            $check = $db->conn->prepare("SELECT id FROM attendance_details WHERE student_id = ? AND course_id = ? AND on_date = ? AND timetable_id = ?");
            $check->execute([$student_id, $course_id, $date, $timetable_id]);
            
            if (!$check->fetch()) {
                // Attendance with session_id
                $ins = $db->conn->prepare("INSERT INTO attendance_details (student_id, course_id, on_date, on_time, status, academic_year, timetable_id, session_id) VALUES (?, ?, ?, ?, 'Present', ?, ?, ?)");
                $ins->execute([$student_id, $course_id, $date, $current_time, $current_academic_year, $timetable_id, $session_id]);
                
                $msg = 'Attendance Marked!';
                $success = true;
            } else {
                $msg = 'Already Checked-in for this class!';
                $success = true; 
            }

            echo json_encode([
                'success' => $success,
                'message' => $msg,
                'name' => $student['name'],
                'roll_no' => $student['roll_no'],
                'photo' => $student['photo'] ?: 'default.png',
                'type' => 'Attendance',
                'course' => $current_class['course_title']
            ]);
        } else {
            // if scan, when no class
            echo json_encode([
                'success' => false, 
                'message' => 'No scheduled class for this time.', 
                'name' => $student['name'], 
                'roll_no' => $student['roll_no'],
                'photo' => $student['photo'] ?: 'default.png',
                'type' => 'No Class',
                'course' => '-'
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
    }
}
?>