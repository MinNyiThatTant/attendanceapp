<?php
require_once 'database/database.php';
$db = new Database();
if (isset($_GET['id'])) {
    $db->conn->prepare("DELETE FROM course_details WHERE id = ?")->execute([$_GET['id']]);
}
header("Location: manage_courses.php");