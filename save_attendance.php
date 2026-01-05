<?php
session_start();
require_once 'database/database.php';
$db = new Database();
$conn = $db->conn;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // accepting form data
    $course_id = $_POST['course_id'];
    $major_id = $_POST['major_id']; 
    $academic_year = $_POST['academic_year']; // New field for academic year
    $date = $_POST['attendance_date'];
    $on_time = date('H:i:s');
    $status_list = $_POST['status'] ?? []; // [student_id => status]

    try {
        $conn->beginTransaction();

        foreach ($status_list as $student_id => $status) {
            $status = ucfirst($status); // 'present' -> 'Present'

            // check if record exists
            $stmt_check = $conn->prepare("SELECT id FROM attendance_details WHERE student_id = ? AND course_id = ? AND on_date = ?");
            $stmt_check->execute([$student_id, $course_id, $date]);
            $existing_id = $stmt_check->fetchColumn();

            if ($existing_id) {
                // update existing record , aldo academic year
                $sql = "UPDATE attendance_details SET status = ?, on_time = ?, academic_year = ? WHERE id = ?";
                $conn->prepare($sql)->execute([$status, $on_time, $academic_year, $existing_id]);
            } else {
                // insert new record , also academic year
                $sql = "INSERT INTO attendance_details (student_id, course_id, on_date, on_time, status, academic_year) VALUES (?, ?, ?, ?, ?, ?)";
                $conn->prepare($sql)->execute([$student_id, $course_id, $date, $on_time, $status, $academic_year]);
            }
        }

        $conn->commit();
        header("Location: attendance.php?major_id=$major_id&course_id=$course_id&academic_year=" . urlencode($academic_year) . "&msg=success");
        exit();

    } catch (Exception $e) {
        $conn->rollBack();
        die("Database Error: " . $e->getMessage());
    }

} else {
    header("Location: dashboard.php");
    exit();
}