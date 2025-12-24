<?php

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/attendanceapp/database/database.php';
require_once $path . '/attendanceapp/database/facultyDetails.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? '';
if ($action === 'verifyUser') {
    $un = $_POST['user_name'] ?? '';
    $pw = $_POST['password'] ?? '';

    $dbo = new Database();
    $fdo = new faculty_details();
    $rv = $fdo->verifyUser($dbo, $un, $pw);
    if (isset($rv['status']) && $rv['status'] === 'success') {
        // start session and set session variables
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $_SESSION['current_user'] = $rv['id'] ?? null;
        $_SESSION['user_name'] = $un;
    }
    echo json_encode($rv);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'invalid action']);
exit;

?>