<?php
session_start();
require_once 'database/database.php';
$db = new Database();

$edit_course = null;
$assigned_majors = [];

// DELETE LOGIC 
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $db->conn->prepare("DELETE FROM course_assignments WHERE course_id = ?")->execute([$id]);
    $db->conn->prepare("DELETE FROM course_details WHERE id = ?")->execute([$id]);
    header("Location: manage_courses.php?msg=deleted");
    exit();
}

// EDIT DATA FETCH 
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $db->conn->prepare("SELECT * FROM course_details WHERE id = ?");
    $stmt->execute([$id]);
    $edit_course = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt_m = $db->conn->prepare("SELECT major_id FROM course_assignments WHERE course_id = ?");
    $stmt_m->execute([$id]);
    $assigned_majors = $stmt_m->fetchAll(PDO::FETCH_COLUMN);
}

// ADD or UPDATE LOGIC 
if (isset($_POST['save_course'])) {
    $code = $_POST['code'];
    $title = $_POST['title'];
    $credits = $_POST['credits'];
    $session_id = $_POST['session_id'];
    $major_ids = $_POST['major_ids'] ?? [];
    $course_id = $_POST['course_id'];
    $academic_year = $_POST['academic_year'] ?? '2024-2025';
    $total_classes = $_POST['total_classes'] ?? 45; // total_classes 

    try {
        $db->conn->beginTransaction();

        if ($course_id) {
            // Update Course 
            $sql = "UPDATE course_details SET code=?, title=?, credits=?, session_id=?, academic_year=?, total_classes=? WHERE id=?";
            $db->conn->prepare($sql)->execute([$code, $title, $credits, $session_id, $academic_year, $total_classes, $course_id]);
            $current_course_id = $course_id;

            // Delete previous assignments
            $db->conn->prepare("DELETE FROM course_assignments WHERE course_id = ?")->execute([$current_course_id]);
        } else {
            // Insert New Course 
            $sql = "INSERT INTO course_details (code, title, credits, session_id, academic_year, total_classes) VALUES (?, ?, ?, ?, ?, ?)";
            $db->conn->prepare($sql)->execute([$code, $title, $credits, $session_id, $academic_year, $total_classes]);
            $current_course_id = $db->conn->lastInsertId();
        }

        // Insert Major Assignments
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

// SEARCH & DATA FETCH LOGIC 
$search = $_GET['search'] ?? '';
$search_query = "";
$params = [];

if (!empty($search)) {
    $search_query = " WHERE cd.code LIKE ? OR cd.title LIKE ? ";
    $params = ["%$search%", "%$search%"];
}

$sql_courses = "SELECT cd.*, sd.term, GROUP_CONCAT(md.title SEPARATOR ', ') as major_names 
                FROM course_details cd 
                LEFT JOIN session_details sd ON cd.session_id = sd.id
                LEFT JOIN course_assignments ca ON cd.id = ca.course_id
                LEFT JOIN major_details md ON ca.major_id = md.id
                $search_query
                GROUP BY cd.id
                ORDER BY sd.id, cd.code";

$stmt_courses = $db->conn->prepare($sql_courses);
$stmt_courses->execute($params);
$courses = $stmt_courses->fetchAll(PDO::FETCH_ASSOC);

$sessions = $db->conn->query("SELECT * FROM session_details")->fetchAll(PDO::FETCH_ASSOC);
$majors = $db->conn->query("SELECT * FROM major_details")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Courses</title>
    <link rel="stylesheet" href="css/attendance.css?v=1.2">
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

        .search-input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 220px;
            outline: none;
        }

    </style>
</head>

<body>
    <div class="container">
        <header class="attendance-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <h1>Manage <span style="color:#4f46e5">Courses</span></h1>
            <div style="display: flex; gap: 10px; align-items: center;">
                <form method="GET" style="display: flex; gap: 5px;">
                    <input type="text" name="search" class="search-input" placeholder="Search code..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" style="padding: 8px 15px; background: #4f46e5; color: white; border: none; border-radius: 5px; cursor: pointer;">🔍</button>
                    <?php if (!empty($search)): ?>
                        <a href="manage_courses.php" style="padding: 8px; color: #666; text-decoration: none;">Clear</a>
                    <?php endif; ?>
                </form>
                <a href="dashboard.php" class="class-btn" style="text-decoration:none; background:lightblue;">⬅ Back To Dashboard</a>
            </div>
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
                    <div style="display:flex; align-items: center; justify-content: space-between; margin-bottom:10px;">
                        <div>
                            <label style="font-weight:bold;">Assign to Majors:</label>
                            <label style="font-size: 0.9rem; cursor: pointer; color: #4f46e5; margin-left: 10px;">
                                <input type="checkbox" id="selectAllMajors"> Select All
                            </label>
                        </div>
                        <div>
                            <label style="font-weight:bold;">Total Periods/Classes:</label>
                            <input type="number" name="total_classes" required value="<?= $edit_course['total_classes'] ?? '45' ?>" style="padding:5px; width: 70px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
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
            </form>
        </div>

        <div class="card">
            <table class="student-table">
                <thead>
                    <tr>
                        <th>Majors</th>
                        <th>Code</th>
                        <th>Title</th>
                        <th>Total Classes</th>
                        <th>Semester</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $c): ?>
                        <tr>
                            <td><small><?= $c['major_names'] ?: 'Not assigned' ?></small></td>
                            <td><?= htmlspecialchars($c['code']) ?></td>
                            <td><?= htmlspecialchars($c['title']) ?></td>
                            <td style="text-align:center;"><?= $c['total_classes'] ?></td>
                            <td><?= htmlspecialchars($c['term']) ?></td>
                            <td>
                                <a href="?edit=<?= $c['id'] ?>">Edit</a> |
                                <a href="?delete=<?= $c['id'] ?>" style="color: red;" onclick="return confirm('Sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.getElementById('selectAllMajors').addEventListener('change', function() {
            document.querySelectorAll('.major-checkbox').forEach(cb => cb.checked = this.checked);
        });
    </script>

<button onclick="topFunction()" id="scrollUpBtn" title="Go to top">↑</button>

    <script>
        let mybutton = document.getElementById("scrollUpBtn");

        
        window.onscroll = function() {
            scrollFunction()
        };

        function scrollFunction() {
            if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
                mybutton.style.display = "block"; 
            } else {
                mybutton.style.display = "none"; 
            }
        }

        function topFunction() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth' 
            });
        }
    </script>
</body>

</html>