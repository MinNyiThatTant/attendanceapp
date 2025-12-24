<?php
require_once 'database/database.php';
$db = new Database();
if (isset($_GET['id'])) {
    $db->conn->prepare("DELETE FROM student_details WHERE id = ?")->execute([$_GET['id']]);
}
header("Location: manage_students.php");