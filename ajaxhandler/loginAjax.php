<?php
// Error display 
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DOCUMENT_ROOT DIR
require_once __DIR__ . '/../database/database.php';
require_once __DIR__ . '/../database/facultyDetails.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? '';

if ($action === 'verifyUser') {
    $un = $_POST['user_name'] ?? '';
    $pw = $_POST['password'] ?? '';

    try {
        $dbo = new Database();
        $fdo = new faculty_details();
        $rv = $fdo->verifyUser($dbo, $un, $pw);

        if (isset($rv['status']) && $rv['status'] === 'success') {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start();
            $_SESSION['current_user'] = $rv['id'] ?? null;
            $_SESSION['user_name'] = $un;
        }
        echo json_encode($rv);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
echo json_encode(['status' => 'error', 'message' => 'invalid action']);
exit;