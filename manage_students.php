<?php
session_start();
require_once 'database/database.php';
$db = new Database();

$academic_years = ["2024-2025", "2025-2026", "2026-2027", "2027-2028"];
$edit_student = null;

// --- DELETE ---
if (isset($_GET['delete'])) {
    $db->conn->prepare("DELETE FROM student_details WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: manage_students.php");
    exit();
}

// --- FETCH FOR EDIT ---
if (isset($_GET['edit'])) {
    $stmt = $db->conn->prepare("SELECT * FROM student_details WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_student = $stmt->fetch(PDO::FETCH_ASSOC);
}

// --- SAVE (ADD OR UPDATE) ---
if (isset($_POST['save_student'])) {
    $roll = $_POST['roll_no'];
    $name = $_POST['name'];
    $major_id = $_POST['major_id'];
    $current_semester = $_POST['current_semester'];
    $academic_year = $_POST['academic_year'];
    $student_id = $_POST['student_id'];

    if ($student_id) {
        $sql = "UPDATE student_details SET roll_no=?, name=?, major_id=?, current_semester=?, academic_year=? WHERE id=?";
        $db->conn->prepare($sql)->execute([$roll, $name, $major_id, $current_semester, $academic_year, $student_id]);
    } else {
        $sql = "INSERT INTO student_details (roll_no, name, major_id, current_semester, academic_year) VALUES (?, ?, ?, ?, ?)";
        $db->conn->prepare($sql)->execute([$roll, $name, $major_id, $current_semester, $academic_year]);
    }
    header("Location: manage_students.php");
    exit();
}

$majors = $db->conn->query("SELECT * FROM major_details")->fetchAll(PDO::FETCH_ASSOC);
$semesters = $db->conn->query("SELECT DISTINCT term FROM session_details")->fetchAll(PDO::FETCH_ASSOC);
$students = $db->conn->query("SELECT sd.*, md.title as major_name FROM student_details sd LEFT JOIN major_details md ON sd.major_id = md.id ORDER BY sd.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Students</title>
    <link rel="stylesheet" href="css/attendance.css">
</head>
<body>
<div class="container">
    <header class="attendance-header">
        <h1>Manage <span style="color:#4f46e5">Students</span></h1>
        <a href="dashboard.php" class="class-btn">⬅ Back to Dashboard</a>
    </header>

    <div class="card">
        <h3><?= $edit_student ? 'Edit Student' : 'Add New Student' ?></h3>
        <form method="POST" style="display:grid; grid-template-columns: repeat(3, 1fr); gap:15px;">
            <input type="hidden" name="student_id" value="<?= $edit_student['id'] ?? '' ?>">
            
            <div>
                <label>Roll No:</label>
                <input type="text" name="roll_no" placeholder="Roll No" value="<?= $edit_student['roll_no'] ?? '' ?>" required style="width:100%; padding:10px; margin-top:5px;">
            </div>

            <div>
                <label>Full Name:</label>
                <input type="text" name="name" placeholder="Full Name" value="<?= $edit_student['name'] ?? '' ?>" required style="width:100%; padding:10px; margin-top:5px;">
            </div>

            <div>
                <label>Academic Year:</label>
                <select name="academic_year" required style="width:100%; padding:10px; margin-top:5px;">
                    <?php foreach($academic_years as $ay): ?>
                        <option value="<?=$ay?>" <?= (isset($edit_student) && $edit_student['academic_year'] == $ay) ? 'selected' : '' ?>><?=$ay?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label>Major:</label>
                <select name="major_id" required style="width:100%; padding:10px; margin-top:5px;">
                    <option value="">-- Select Major --</option>
                    <?php foreach($majors as $m): ?>
                        <option value="<?=$m['id']?>" <?= (isset($edit_student) && $edit_student['major_id'] == $m['id']) ? 'selected' : '' ?>><?=$m['title']?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label>Semester:</label>
                <select name="current_semester" required style="width:100%; padding:10px; margin-top:5px;">
                    <option value="">-- Select Semester --</option>
                    <?php foreach($semesters as $sem): ?>
                        <option value="<?=$sem['term']?>" <?= (isset($edit_student) && $edit_student['current_semester'] == $sem['term']) ? 'selected' : '' ?>><?=$sem['term']?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display:flex; align-items:flex-end;">
                <button type="submit" name="save_student" class="save-btn" style="width:100%; margin:0;"><?= $edit_student ? 'Update' : 'Register' ?></button>
                <?php if($edit_student): ?>
                    <a href="manage_students.php" style="margin-left:10px; text-decoration:none; color:gray;">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <div class="card" style="margin-top:20px;">
        <table class="student-table">
            <thead>
                <tr>
                    <th>Roll No</th>
                    <th>Name</th>
                    <th>Major</th>
                    <th>Academic Year</th>
                    <th>Semester</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($students as $s): ?>
                <tr>
                    <td><?=$s['roll_no']?></td>
                    <td><?=$s['name']?></td>
                    <td><?=$s['major_name']?></td>
                    <td><span style="color:#4f46e5; font-weight:bold;"><?=$s['academic_year']?></span></td>
                    <td><?=$s['current_semester']?></td>
                    <td>
                        <a href="manage_students.php?edit=<?=$s['id']?>">Edit</a> | 
                        <a href="manage_students.php?delete=<?=$s['id']?>" style="color:red;" onclick="return confirm('ဖျက်ရန် သေချာပါသလား?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>