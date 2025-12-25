<?php
session_start();
require_once 'database/database.php';
if (empty($_SESSION["current_user"])) { header('Location: login.php'); exit; }

$db = new Database();

// reterive majors
$stmt = $db->conn->prepare("SELECT * FROM major_details");
$stmt->execute();
$majors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// default semester
$sem_stmt = $db->conn->query("SELECT term FROM session_details ORDER BY id ASC LIMIT 1");
$default_sem = $sem_stmt->fetchColumn() ?: '1st semester';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Attendance System</title>
    <link rel="stylesheet" href="css/attendance.css">
    <style>
        .card:hover { border-color: #4f46e5 !important; transform: translateY(-3px); cursor: pointer; }
        .main-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px; }
    </style>
</head>
<body>
<div class="container">
    <div class="card" style="margin-bottom:20px; display:flex; justify-content: space-between; align-items: center;">
        <div style="display:flex; gap:10px;">
            <a href="manage_majors.php" class="class-btn">⚙️ Majors</a>
            <a href="manage_courses.php" class="class-btn">📚 Courses</a>
            <a href="manage_students.php" class="class-btn">👨‍🎓 Students</a>
            <a href="manage_registration.php" class="class-btn" style="background:#4f46e5; color:white;">📝 Registration</a>
        </div>
        <button class="logout-btn" id="btnlogout">Logout</button>
    </div>

    <header class="attendance-header">
        <h1>Select <span style="color:#4f46e5">Major</span> to Take Attendance</h1>
    </header>

    <div class="main-grid">
        <?php foreach($majors as $m): ?>
            <div onclick="goToAttendance(<?= $m['id'] ?>, '<?= addslashes($m['title']) ?>')" class="card" style="text-align:center; padding: 40px 20px; border: 2px solid #e5e7eb; border-radius: 10px;">
                <div style="font-size: 1.3rem; color: #1f2937; font-weight:bold;"><?= htmlspecialchars($m['title']) ?></div>
                <div style="font-size: 0.85rem; color: #6b7280; margin-top: 10px;">Click to select subjects</div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<script src="js/logout.js"></script>
<script>
    function goToAttendance(id, name) {
        const defaultSem = "<?= urlencode($default_sem) ?>";
        window.location.href = `attendance.php?major_id=${id}&major_name=${encodeURIComponent(name)}&semester=${defaultSem}`;
    }
</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="js/logout.js"></script>
</body>
</html>