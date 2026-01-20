<?php
require_once 'database/database.php';
$db = new Database();
date_default_timezone_set('Asia/Yangon');

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['rfid_uid'])) {
    $uid = isset($_POST['rfid_uid']) ? trim($_POST['rfid_uid']) : trim($_GET['rfid_uid']);
    $date = date('Y-m-d');
    $current_time = date('H:i:s');
    $day_of_week = date('l');
    $current_academic_year = $db->getAcademicYear();

    // ၁။ Check Holidays
    $check_holiday = $db->conn->prepare("SELECT description FROM holidays WHERE holiday_date = ?");
    $check_holiday->execute([$date]);
    if ($holiday = $check_holiday->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Today is a Holiday: ' . $holiday['description']]);
        exit;
    }

    // ၂။ Search student by RFID UID including semester info
    $stmt = $db->conn->prepare("SELECT id, name, roll_no, major_id, semester, photo FROM student_details WHERE rfid_uid = ?");
    $stmt->execute([$uid]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        echo json_encode(['success' => false, 'message' => 'Unregistered RFID Card!']);
        exit;
    }

    $student_id = $student['id'];
    $student_sem = $student['semester']; // student's current semester

    // ၃။ Check active class in Timetable including semester filter
    $time_sql = "SELECT t.id as timetable_id, t.course_id, t.period, t.end_time, c.title as course_title, c.total_classes 
                 FROM timetable t
                 JOIN course_details c ON t.course_id = c.id
                 WHERE t.major_id = ? 
                 AND t.semester = ? 
                 AND t.day_of_week = ? 
                 AND t.academic_year = ?
                 AND ? BETWEEN DATE_SUB(t.start_time, INTERVAL 15 MINUTE) AND t.end_time 
                 LIMIT 1";

    $time_stmt = $db->conn->prepare($time_sql);
    $time_stmt->execute([$student['major_id'], $student_sem, $day_of_week, $current_academic_year, $current_time]);
    $current_class = $time_stmt->fetch(PDO::FETCH_ASSOC);

    if ($current_class) {
        // for Attendance marking
        $course_id = $current_class['course_id'];
        $timetable_id = $current_class['timetable_id'];

        $check = $db->conn->prepare("SELECT id FROM attendance_details WHERE student_id = ? AND course_id = ? AND on_date = ? AND timetable_id = ?");
        $check->execute([$student_id, $course_id, $date, $timetable_id]);
        $existing_attendance = $check->fetch(PDO::FETCH_ASSOC);

        if (!$existing_attendance) {
            $ins = $db->conn->prepare("INSERT INTO attendance_details (student_id, course_id, on_date, on_time, status, academic_year, timetable_id) VALUES (?, ?, ?, ?, 'Present', ?, ?)");
            $ins->execute([$student_id, $course_id, $date, $current_time, $current_academic_year, $timetable_id]);
            $msg = 'Attendance Marked!';
        } else {
            $msg = 'Already marked as Present';
        }

        // ၄။ Check for next consecutive class checking semester
        $sql_next = "SELECT id as next_timetable_id FROM timetable 
                     WHERE day_of_week = ? AND semester = ? AND course_id = ? AND academic_year = ? AND major_id = ?
                     AND start_time >= ? AND start_time <= ADDTIME(?, '00:15:00')";
        $stmt_next = $db->conn->prepare($sql_next);
        $stmt_next->execute([$day_of_week, $student_sem, $course_id, $current_academic_year, $student['major_id'], $current_class['end_time'], $current_class['end_time']]);
        
        if ($next_class = $stmt_next->fetch(PDO::FETCH_ASSOC)) {
            $next_tid = $next_class['next_timetable_id'];
            $check_next = $db->conn->prepare("SELECT id FROM attendance_details WHERE student_id = ? AND on_date = ? AND timetable_id = ?");
            $check_next->execute([$student_id, $date, $next_tid]);
            if (!$check_next->fetch()) {
                $db->conn->prepare("INSERT INTO attendance_details (student_id, course_id, on_date, on_time, status, academic_year, timetable_id) VALUES (?, ?, ?, ?, 'Present', ?, ?)")
                   ->execute([$student_id, $course_id, $date, $current_time, $current_academic_year, $next_tid]);
                $msg = "Checked in for consecutive classes!";
            }
        }

        // Percentage Calculation
        $total_expected = $current_class['total_classes'] ?: 45;
        $count_stmt = $db->conn->prepare("SELECT COUNT(*) FROM attendance_details WHERE student_id = ? AND course_id = ? AND status = 'Present'");
        $count_stmt->execute([$student_id, $course_id]);
        $attended_days = $count_stmt->fetchColumn();
        $percentage = round(($attended_days / $total_expected) * 100, 1);
        if ($percentage > 100) $percentage = 100;

        echo json_encode([
            'success' => true,
            'message' => $msg,
            'name' => $student['name'],
            'roll_no' => $student['roll_no'],
            'photo' => $student['photo'] ?: 'default.png',
            'type' => 'Attendance',
            'course' => $current_class['course_title'],
            'percentage' => $percentage . '%'
        ]);

    } else {
        // for Lab Check-in/out
        $check_lab = $db->conn->prepare("SELECT id FROM computer_usage_logs WHERE student_id = ? AND usage_date = ? AND check_out_time IS NULL LIMIT 1");
        $check_lab->execute([$student_id, $date]);
        $lab_record = $check_lab->fetch(PDO::FETCH_ASSOC);

        if ($lab_record) {
            $update_lab = $db->conn->prepare("UPDATE computer_usage_logs SET check_out_time = ? WHERE id = ?");
            $update_lab->execute([$current_time, $lab_record['id']]);
            $lab_msg = "Lab Check-out Successful";
            $type = "Lab Out";
        } else {
            $insert_lab = $db->conn->prepare("INSERT INTO computer_usage_logs (student_id, usage_date, check_in_time) VALUES (?, ?, ?)");
            $insert_lab->execute([$student_id, $date, $current_time]);
            $lab_msg = "Lab Check-in Successful";
            $type = "Lab In";
        }

        echo json_encode([
            'success' => true,
            'message' => $lab_msg,
            'name' => $student['name'],
            'roll_no' => $student['roll_no'],
            'photo' => $student['photo'] ?: 'default.png',
            'type' => $type,
            'course' => 'Computer Lab',
            'percentage' => 'N/A'
        ]);
    }
}
?>