<?php
require_once 'database/database.php';
$db = new Database();
date_default_timezone_set('Asia/Yangon');

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['rfid_uid'])) {
    $uid = isset($_POST['rfid_uid']) ? trim($_POST['rfid_uid']) : trim($_GET['rfid_uid']);
    $date = date('Y-m-d');
    $current_time = date('H:i:s');
    $day_of_week = date('l');
    $current_academic_year = $db->getAcademicYear(); // Dynamic Year ရယူခြင်း

    // 1. Holiday စစ်ဆေးခြင်း
    $check_holiday = $db->conn->prepare("SELECT description FROM holidays WHERE holiday_date = ?");
    $check_holiday->execute([$date]);
    if ($holiday = $check_holiday->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Today is a Holiday: ' . $holiday['description']]);
        exit;
    }

    // 2. ကျောင်းသားရှာဖွေခြင်း
    $stmt = $db->conn->prepare("SELECT id, name, roll_no, major_id, photo FROM student_details WHERE rfid_uid = ?");
    $stmt->execute([$uid]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        echo json_encode(['success' => false, 'message' => 'Unregistered RFID Card!']);
        exit;
    }

    // 3. (အရေးကြီးဆုံးအပိုင်း) Timetable + Course Registration ကို တွဲစစ်ခြင်း
    // ကျောင်းသားက ဒီနှစ်မှာ ဒီဘာသာရပ်ကို Register လုပ်ထားမှသာ Timetable ပေါ်မှာ Attendance ပေးမယ်
    $stmt_class = $db->conn->prepare("
        SELECT t.id as tid, c.id as cid, c.title as course_title, c.total_classes 
        FROM timetable t
        JOIN course_details c ON t.course_id = c.id
        JOIN course_registration cr ON c.id = cr.course_id
        WHERE t.day_of_week = ? 
        AND ? BETWEEN t.start_time AND t.end_time
        AND cr.student_id = ? 
        AND cr.academic_year = ?
    ");
    $stmt_class->execute([$day_of_week, $current_time, $student['id'], $current_academic_year]);
    $current_class = $stmt_class->fetch(PDO::FETCH_ASSOC);

    if (!$current_class) {
        echo json_encode(['success' => false, 'message' => 'No active class found or not registered for this course this year!']);
        exit;
    }

    $course_id = $current_class['cid'];

    // 4. လက်ရှိအချိန်မှာ ရှိနေတဲ့ အတန်း (Class Slot) ကို ရှာဖွေခြင်း
    $time_sql = "SELECT t.id as timetable_id, t.course_id, t.period, t.end_time, c.title as course_title, c.total_classes 
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
        $timetable_id = $current_class['timetable_id'];

        $check = $db->conn->prepare("SELECT id, status FROM attendance_details WHERE student_id = ? AND course_id = ? AND on_date = ? AND timetable_id = ?");
        $check->execute([$student['id'], $course_id, $date, $timetable_id]);
        $existing_attendance = $check->fetch(PDO::FETCH_ASSOC);

        if (!$existing_attendance) {
            $ins = $db->conn->prepare("INSERT INTO attendance_details (student_id, course_id, on_date, on_time, status, academic_year, timetable_id) VALUES (?, ?, ?, ?, 'Present', ?, ?)");
            $ins->execute([$student['id'], $course_id, $date, $current_time, $current_academic_year, $timetable_id]);
            $msg = 'Attendance Marked!';
        } else {
            $msg = 'Already marked as Present';
        }

        // --- Option 1: နောက်ထပ် ကပ်လျက်အချိန်မှာ ဒီ Course ပဲ ရှိနေရင် Auto-Present ပေးရန် ---
        $sql_next = "SELECT id as next_timetable_id FROM timetable 
                     WHERE day_of_week = ? 
                     AND course_id = ? 
                     AND academic_year = ?
                     AND major_id = ?
                     AND start_time >= ? 
                     AND start_time <= ADDTIME(?, '00:15:00')";
        
        $stmt_next = $db->conn->prepare($sql_next);
        $stmt_next->execute([$day_of_week, $course_id, $current_academic_year, $student['major_id'], $current_class['end_time'], $current_class['end_time']]);
        $next_class = $stmt_next->fetch(PDO::FETCH_ASSOC);

        if ($next_class) {
            $next_tid = $next_class['next_timetable_id'];
            $check_next = $db->conn->prepare("SELECT id FROM attendance_details WHERE student_id = ? AND on_date = ? AND timetable_id = ?");
            $check_next->execute([$student['id'], $date, $next_tid]);
            if (!$check_next->fetch()) {
                $ins_next = $db->conn->prepare("INSERT INTO attendance_details (student_id, course_id, on_date, on_time, status, academic_year, timetable_id) VALUES (?, ?, ?, ?, 'Present', ?, ?)");
                $ins_next->execute([$student['id'], $course_id, $date, $current_time, $current_academic_year, $next_tid]);
                $msg = "Checked in for consecutive classes!";
            }
        }

        // Percentage Calculation
        $total_expected = $current_class['total_classes'] ?: 45;
        $count_stmt = $db->conn->prepare("SELECT COUNT(*) FROM attendance_details WHERE student_id = ? AND course_id = ? AND status = 'Present'");
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
            'message' => "No active class for " . $student['name'], 
            'photo' => $student['photo'] ?: 'default.png'
        ]);
    }
}
?>