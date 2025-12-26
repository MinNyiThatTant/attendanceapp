<?php
session_start();
require_once 'database/database.php';
$db = new Database();

// add Major
if (isset($_POST['add_major'])) {
    $title = $_POST['title'];
    $code = $_POST['code'];
    $stmt = $db->conn->prepare("INSERT INTO major_details (title, code) VALUES (?, ?)");
    $stmt->execute([$title, $code]);
    header("Location: manage_majors.php");
}

// delete Major
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $db->conn->prepare("DELETE FROM major_details WHERE id = ?")->execute([$id]);
    header("Location: manage_majors.php");
}

$majors = $db->conn->query("SELECT * FROM major_details")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="my">
<head>
    <title>Manage Majors</title>
    <link rel="stylesheet" href="css/attendance.css">
</head>
<body>
<div class="container">
    <header class="attendance-header">
        <h1>Major <span style="color:#4f46e5">Management</span></h1>
        <a href="dashboard.php" class="class-btn" style="text-decoration:none; background:lightblue;">⬅ Back To Dashboard</a>
    </header>

    <div class="card" style="margin-bottom: 20px;">
        <h3>Add New Major</h3>
        <form method="POST" style="display:flex; gap:10px;">
            <input type="text" name="title" placeholder="Major Name (e.g. CEIT)" required style="flex:2; padding:10px; border-radius:5px; border:1px solid #ddd;">
            <input type="text" name="code" placeholder="Code (e.g. IT)" required style="flex:1; padding:10px; border-radius:5px; border:1px solid #ddd;">
            <button type="submit" name="add_major" class="save-btn" style="margin:0; width:auto; padding:10px 20px;">Add Major</button>
        </form>
    </div>

    <div class="card">
        <table class="student-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Major Name</th>
                    <th>Code</th>
                    <th style="text-align:center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($majors as $m): ?>
                <tr>
                    <td><?=$m['id']?></td>
                    <td><?=$m['title']?></td>
                    <td><?=$m['code']?></td>
                    <td style="text-align:center">
                        <a href="?delete=<?=$m['id']?>" style="color:#ef4444; font-weight:bold; text-decoration:none;" onclick="return confirm('ဖျက်ရန် သေချာပါသလား?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>