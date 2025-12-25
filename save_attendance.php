<?php
session_start();
require_once 'database/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['status'])) {
    $db = new Database();
    $conn = $db->conn;

    $course_id = $_POST['course_id'];
    $faculty_id = $_SESSION['current_user'];
    $academic_year = $_POST['academic_year'] ?? '2024-2025'; // academic_year from attendance form
    $date = date('Y-m-d');

    // ၁။ session_id (Semester)
    $stmt_sess = $conn->prepare("SELECT session_id FROM course_details WHERE id = ? LIMIT 1");
    $stmt_sess->execute([$course_id]);
    $session_id = $stmt_sess->fetchColumn();

    if (!$session_id) {
        
        header("Location: dashboard.php?msg=Error_Invalid_Session");
        exit();
    }

    try {
        $conn->beginTransaction();

        foreach ($_POST['status'] as $student_id => $status) {
            // ၂။ Duplicate Check
            $stmt_check = $conn->prepare("SELECT id FROM attendance_details 
                                         WHERE student_id = ? AND course_id = ? AND on_date = ?");
            $stmt_check->execute([$student_id, $course_id, $date]);
            $existing_id = $stmt_check->fetchColumn();

            if ($existing_id) {
                // update
                $sql = "UPDATE attendance_details SET status = ?, faculty_id = ?, academic_year = ? WHERE id = ?";
                $conn->prepare($sql)->execute([$status, $faculty_id, $academic_year, $existing_id]);
            } else {
                // insert - academic_year
                $sql = "INSERT INTO attendance_details (faculty_id, student_id, course_id, session_id, on_date, status, academic_year) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $conn->prepare($sql)->execute([$faculty_id, $student_id, $course_id, $session_id, $date, $status, $academic_year]);
            }
        }

        $conn->commit();
        header("Location: dashboard.php?msg=Success");
        exit();

    } catch (Exception $e) {
        $conn->rollBack();
        die("Error: " . $e->getMessage());
    }
} else {
    header("Location: dashboard.php");
    exit();
}