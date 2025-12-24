<?php
session_start();
require_once 'database/database.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new Database();
    $course_id = $_POST['course_id'];
    $faculty_id = $_SESSION['current_user'];
    $date = date('Y-m-d');

    // session_id
    $stmt_sess = $db->conn->prepare("SELECT session_id FROM course_allotment WHERE course_id = ? LIMIT 1");
    $stmt_sess->execute([$course_id]);
    $session_id = $stmt_sess->fetchColumn();

    foreach($_POST['status'] as $student_id => $status) {
        $sql = "INSERT INTO attendance_details (faculty_id, student_id, course_id, session_id, on_date, status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $db->conn->prepare($sql)->execute([$faculty_id, $student_id, $course_id, $session_id, $date, $status]);
    }
    header("Location: dashboard.php?msg=Success");
}