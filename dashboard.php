<?php
session_start();
require_once 'database/database.php';
if (empty($_SESSION["current_user"])) { header('Location: login.php'); exit; }

$db = new Database();
$stmt = $db->conn->prepare("SELECT * FROM major_details");
$stmt->execute();
$majors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/attendance.css">
</head>
<body>
    <div class="card" style="margin-bottom:20px; display:flex; flex-wrap:wrap; gap:10px;">
    <a href="manage_majors.php" class="class-btn">⚙️ Majors</a>
    <a href="manage_courses.php" class="class-btn">📚 Courses</a>
    <a href="manage_students.php" class="class-btn">👨‍🎓 Students</a>
    <a href="manage_registration.php" class="class-btn" style="background:#4f46e5; color:white;">📝 Registration</a>
</div>
<div class="container">
    <header class="attendance-header">
        <h1>Select <span style="color:#4f46e5">Major</span></h1>
        <button class="logout-btn" id="btnlogout">Logout</button>
    </header>
    <div class="main-grid" style="grid-template-columns: repeat(3, 1fr); gap: 20px;">
        <?php foreach($majors as $m): ?>
            <a href="attendance.php?major_id=<?=$m['id']?>&major_name=<?=$m['title']?>" class="card" style="text-decoration:none; text-align:center; font-weight:bold;">
                <?=$m['title']?>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="js/logout.js"></script>
</body>
</html>