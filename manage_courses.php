<?php
session_start();
require_once 'database/database.php';
$db = new Database();

$edit_course = null;

// --- DELETE LOGIC ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM course_details WHERE id = ?";
    $db->conn->prepare($sql)->execute([$id]);
    header("Location: manage_courses.php?msg=deleted");
    exit();
}

// --- EDIT DATA FETCH ---
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $db->conn->prepare("SELECT * FROM course_details WHERE id = ?");
    $stmt->execute([$id]);
    $edit_course = $stmt->fetch(PDO::FETCH_ASSOC);
}

// --- ADD or UPDATE LOGIC ---
if (isset($_POST['save_course'])) {
    $code = $_POST['code'];
    $title = $_POST['title'];
    $credits = $_POST['credits'];
    $session_id = $_POST['session_id'];
    $major_id = $_POST['major_id']; // add new Major ID 
    $course_id = $_POST['course_id'];

    if ($course_id) {
        // UPDATE major_id
        $sql = "UPDATE course_details SET code=?, title=?, credits=?, session_id=?, major_id=? WHERE id=?";
        $db->conn->prepare($sql)->execute([$code, $title, $credits, $session_id, $major_id, $course_id]);
        header("Location: manage_courses.php?msg=updated");
    } else {
        // INSERT major_id
        $checkStmt = $db->conn->prepare("SELECT COUNT(*) FROM course_details WHERE code = ?");
        $checkStmt->execute([$code]);
        if ($checkStmt->fetchColumn() > 0) {
            echo "<script>alert('Error: Course Code ($code) ရှိပြီးသားဖြစ်နေပါသည်။'); window.location='manage_courses.php';</script>";
            exit();
        }
        $sql = "INSERT INTO course_details (code, title, credits, session_id, major_id) VALUES (?, ?, ?, ?, ?)";
        $db->conn->prepare($sql)->execute([$code, $title, $credits, $session_id, $major_id]);
        header("Location: manage_courses.php?msg=added");
    }
    exit();
}

// take data
$sessions = $db->conn->query("SELECT * FROM session_details")->fetchAll(PDO::FETCH_ASSOC);
$majors = $db->conn->query("SELECT * FROM major_details")->fetchAll(PDO::FETCH_ASSOC);

// join Major name
$courses = $db->conn->query("SELECT cd.*, sd.term, md.title as major_name 
                             FROM course_details cd 
                             LEFT JOIN session_details sd ON cd.session_id = sd.id
                             LEFT JOIN major_details md ON cd.major_id = md.id
                             ORDER BY cd.major_id, sd.id")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Courses</title>
    <link rel="stylesheet" href="css/attendance.css">
</head>
<body>
<div class="container">
    <header class="attendance-header">
        <h1>Manage <span style="color:#4f46e5">Courses</span></h1>
        <a href="dashboard.php" class="class-btn">Back</a>
    </header>

    <div class="card" style="margin-bottom: 20px;">
        <h3><?= $edit_course ? "Edit Course" : "Add New Course" ?></h3>
        <form method="POST">
            <input type="hidden" name="course_id" value="<?= $edit_course['id'] ?? '' ?>">
            
            <div style="display:grid; grid-template-columns: 1fr 2fr 1fr; gap:10px; margin-bottom:10px;">
                <input type="text" name="code" placeholder="Course Code" required value="<?= $edit_course['code'] ?? '' ?>" style="padding:8px;">
                <input type="text" name="title" placeholder="Course Title" required value="<?= $edit_course['title'] ?? '' ?>" style="padding:8px;">
                <input type="number" name="credits" placeholder="Credits" required value="<?= $edit_course['credits'] ?? '' ?>" style="padding:8px;">
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                <select name="major_id" required style="padding:8px;">
                    <option value="">Select Major</option>
                    <?php foreach($majors as $m): ?>
                        <option value="<?=$m['id']?>" <?= (isset($edit_course) && $edit_course['major_id'] == $m['id']) ? 'selected' : '' ?> >
                            <?=$m['title']?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="session_id" required style="padding:8px;">
                    <option value="">Select Semester</option>
                    <?php foreach($sessions as $s): ?>
                        <option value="<?=$s['id']?>" <?= (isset($edit_course) && $edit_course['session_id'] == $s['id']) ? 'selected' : '' ?> >
                            <?=$s['term']?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" name="save_course" class="save-btn" style="width: auto; margin-top: 15px; padding: 10px 25px;">
                <?= $edit_course ? "Update Course" : "Add Course" ?>
            </button>
            <?php if($edit_course): ?>
                <a href="manage_courses.php" style="margin-left: 10px; color: #666;">Cancel</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <table class="student-table">
            <thead>
                <tr>
                    <th>Major</th>
                    <th>Code</th>
                    <th>Title</th>
                    <th>Semester</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($courses as $c): ?>
                <tr>
                    <td><span style="color:#4f46e5; font-weight:bold;"><?=$c['major_name'] ?? 'N/A'?></span></td>
                    <td><?=$c['code']?></td>
                    <td><?=$c['title']?></td>
                    <td><?=$c['term'] ?? 'Not Assigned'?></td>
                    <td>
                        <a href="manage_courses.php?edit=<?=$c['id']?>">Edit</a> | 
                        <a href="manage_courses.php?delete=<?=$c['id']?>" style="color: red;" onclick="return confirm('ဖျက်ရန် သေချာပါသလား?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>