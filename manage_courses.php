<?php
session_start();
require_once 'database/database.php';
$db = new Database();

$edit_course = null;
$assigned_majors = []; 

// --- DELETE LOGIC ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $db->conn->prepare("DELETE FROM course_assignments WHERE course_id = ?")->execute([$id]);
    $db->conn->prepare("DELETE FROM course_details WHERE id = ?")->execute([$id]);
    header("Location: manage_courses.php?msg=deleted");
    exit();
}

// --- EDIT DATA FETCH ---
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $db->conn->prepare("SELECT * FROM course_details WHERE id = ?");
    $stmt->execute([$id]);
    $edit_course = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt_m = $db->conn->prepare("SELECT major_id FROM course_assignments WHERE course_id = ?");
    $stmt_m->execute([$id]);
    $assigned_majors = $stmt_m->fetchAll(PDO::FETCH_COLUMN); 
}

// --- ADD or UPDATE LOGIC ---
if (isset($_POST['save_course'])) {
    $code = $_POST['code'];
    $title = $_POST['title'];
    $credits = $_POST['credits'];
    $session_id = $_POST['session_id'];
    $major_ids = $_POST['major_ids'] ?? []; 
    $course_id = $_POST['course_id'];
    $academic_year = $_POST['academic_year'] ?? '2024-2025'; 

    try {
        $db->conn->beginTransaction();

        if ($course_id) {
            // Update Course
            $sql = "UPDATE course_details SET code=?, title=?, credits=?, session_id=?, academic_year=? WHERE id=?";
            $db->conn->prepare($sql)->execute([$code, $title, $credits, $session_id, $academic_year, $course_id]);
            $current_course_id = $course_id;

            // Edit, delete previous assignments
            $db->conn->prepare("DELETE FROM course_assignments WHERE course_id = ?")->execute([$current_course_id]);
        } else {
            // Insert New Course
            $sql = "INSERT INTO course_details (code, title, credits, session_id, academic_year) VALUES (?, ?, ?, ?, ?)";
            $db->conn->prepare($sql)->execute([$code, $title, $credits, $session_id, $academic_year]);
            $current_course_id = $db->conn->lastInsertId(); // get last inserted id
        }

        // insert new Assignment 
        if (!empty($major_ids)) {
            $assign_stmt = $db->conn->prepare("INSERT INTO course_assignments (course_id, major_id) VALUES (?, ?)");
            foreach ($major_ids as $m_id) {
                $assign_stmt->execute([$current_course_id, $m_id]);
            }
        }

        $db->conn->commit();
        header("Location: manage_courses.php?msg=success");
        exit();
    } catch (Exception $e) {
        $db->conn->rollBack();
        echo "Error: " . $e->getMessage();
    }
}

// data fetch
$sessions = $db->conn->query("SELECT * FROM session_details")->fetchAll(PDO::FETCH_ASSOC);
$majors = $db->conn->query("SELECT * FROM major_details")->fetchAll(PDO::FETCH_ASSOC);

// show major with Group
$courses = $db->conn->query("SELECT cd.*, sd.term, GROUP_CONCAT(md.title SEPARATOR ', ') as major_names 
                             FROM course_details cd 
                             LEFT JOIN session_details sd ON cd.session_id = sd.id
                             LEFT JOIN course_assignments ca ON cd.id = ca.course_id
                             LEFT JOIN major_details md ON ca.major_id = md.id
                             GROUP BY cd.id
                             ORDER BY sd.id, cd.code")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Courses</title>
    <link rel="stylesheet" href="css/attendance.css">
    <style>
        .major-checkbox-group {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="attendance-header">
            <h1>Manage <span style="color:#4f46e5">Courses</span></h1>
            <a href="dashboard.php" class="class-btn">⬅ Back To Dashboard</a>
        </header>

        <div class="card" style="margin-bottom: 20px;">
            <h3><?= $edit_course ? "Edit Course" : "Add New Course" ?></h3>
            <form method="POST">
                <input type="hidden" name="course_id" value="<?= $edit_course['id'] ?? '' ?>">

                <div style="display:grid; grid-template-columns: 1fr 2fr 1fr 1fr; gap:10px; margin-bottom:15px;">
                    <input type="text" name="code" placeholder="Code" required value="<?= $edit_course['code'] ?? '' ?>" style="padding:8px;">
                    <input type="text" name="title" placeholder="Course Title" required value="<?= $edit_course['title'] ?? '' ?>" style="padding:8px;">
                    <input type="number" name="credits" placeholder="Credits" required value="<?= $edit_course['credits'] ?? '' ?>" style="padding:8px;">
                    <select name="session_id" required style="padding:8px;">
                        <option value="">Semester</option>
                        <?php foreach ($sessions as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= (isset($edit_course) && $edit_course['session_id'] == $s['id']) ? 'selected' : '' ?>><?= $s['term'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="margin-bottom:15px;">
                    <div style="display:flex; align-items: center; gap: 15px; margin-bottom:8px;">
                        <label style="font-weight:bold;">Assign to Majors:</label>
                        <label style="font-size: 0.9rem; cursor: pointer; color: #4f46e5; display: flex; align-items: center; gap: 5px;">
                            <input type="checkbox" id="selectAllMajors"> Select All
                        </label>
                    </div>

                    <div class="major-checkbox-group">
                        <?php foreach ($majors as $m): ?>
                            <label class="checkbox-item">
                                <input type="checkbox" name="major_ids[]" class="major-checkbox" value="<?= $m['id'] ?>"
                                    <?= in_array($m['id'], $assigned_majors) ? 'checked' : '' ?>>
                                <?= $m['title'] ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" name="save_course" class="save-btn" style="width: auto; padding: 10px 25px;">
                    <?= $edit_course ? "Update Course" : "Add Course" ?>
                </button>
                <?php if ($edit_course): ?>
                    <a href="manage_courses.php" style="margin-left: 10px; color: #666;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <table class="student-table">
                <thead>
                    <tr>
                        <th style="width:25%">Majors</th>
                        <th>Code</th>
                        <th>Title</th>
                        <th>Semester</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($courses)): ?>
                        <tr><td colspan="5" style="text-align:center;">No courses found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($courses as $c): ?>
                            <tr>
                                <td>
                                    <?php if($c['major_names']): ?>
                                        <small style="color:#4f46e5; font-weight:bold;"><?= $c['major_names'] ?></small>
                                    <?php else: ?>
                                        <small style="color:red;">Not assigned to any major</small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($c['code']) ?></td>
                                <td><?= htmlspecialchars($c['title']) ?></td>
                                <td><?= htmlspecialchars($c['term']) ?></td>
                                <td>
                                    <a href="?edit=<?= $c['id'] ?>">Edit</a> |
                                    <a href="?delete=<?= $c['id'] ?>" style="color: red;" onclick="return confirm('Sure?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.getElementById('selectAllMajors').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.major-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        const majorCheckboxes = document.querySelectorAll('.major-checkbox');
        majorCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const selectAll = document.getElementById('selectAllMajors');
                const allChecked = Array.from(majorCheckboxes).every(cb => cb.checked);
                selectAll.checked = allChecked;
            });
        });
    </script>
</body>
</html>