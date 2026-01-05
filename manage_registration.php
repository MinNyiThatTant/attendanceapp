<?php
session_start();
require_once 'database/database.php';
$db = new Database();
$conn = $db->conn;

if (empty($_SESSION["current_user"])) {
    header('Location: login.php');
    exit;
}


// Delete Logic
if (isset($_GET['delete'])) {
    $conn->prepare("DELETE FROM course_registration WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: manage_registration.php?msg=deleted");
    exit();
}

// Save / Update Logic 
if (isset($_POST['save_registration'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $academic_year = $_POST['academic_year']; 
    $reg_id = $_POST['reg_id'];

    $stmt_c = $conn->prepare("SELECT session_id FROM course_details WHERE id = ?");
    $stmt_c->execute([$course_id]);
    $session_id = $stmt_c->fetchColumn();

    if (empty($reg_id)) {
        // check duplicate registration
        $check = $conn->prepare("SELECT id FROM course_registration WHERE student_id=? AND course_id=? AND academic_year=?");
        $check->execute([$student_id, $course_id, $academic_year]);
        if ($check->fetch()) {
            echo "<script>alert('ဤကျောင်းသားသည် Register လုပ်ပြီးသားဖြစ်နေပါသည်။'); window.location='manage_registration.php';</script>";
            exit();
        }
        $sql = "INSERT INTO course_registration (student_id, course_id, session_id, academic_year) VALUES (?, ?, ?, ?)";
        $conn->prepare($sql)->execute([$student_id, $course_id, $session_id, $academic_year]);
    } else {
        $sql = "UPDATE course_registration SET student_id=?, course_id=?, session_id=?, academic_year=? WHERE id=?";
        $conn->prepare($sql)->execute([$student_id, $course_id, $session_id, $academic_year, $reg_id]);
    }
    header("Location: manage_registration.php?msg=success");
    exit();
}

// --- Data Fetching ---
$search = $_GET['search'] ?? '';
$search_query = $search ? " WHERE sd.name LIKE :s OR sd.roll_no LIKE :s OR cd.title LIKE :s " : "";

// Pagination Logic
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$registrations = $conn->prepare("
    SELECT cr.*, sd.name, sd.roll_no, cd.title as course_name, cd.code, sess.term 
    FROM course_registration cr 
    JOIN student_details sd ON cr.student_id = sd.id 
    JOIN course_details cd ON cr.course_id = cd.id
    JOIN session_details sess ON cr.session_id = sess.id
    $search_query ORDER BY cr.id DESC LIMIT $limit OFFSET $offset");
if ($search) $registrations->bindValue(':s', "%$search%");
$registrations->execute();
$reg_list = $registrations->fetchAll(PDO::FETCH_ASSOC);

// query all students for the dropdown selection
$students = $conn->query("SELECT s.*, m.title as major_name FROM student_details s JOIN major_details m ON s.major_id = m.id ORDER BY s.name")->fetchAll(PDO::FETCH_ASSOC);

// query all courses for the dropdown selection
$courses = $conn->query("
    SELECT cd.*, sd.term, GROUP_CONCAT(ca.major_id) as assigned_majors 
    FROM course_details cd 
    JOIN session_details sd ON cd.session_id = sd.id
    LEFT JOIN course_assignments ca ON cd.id = ca.course_id
    GROUP BY cd.id")->fetchAll(PDO::FETCH_ASSOC);

$edit_reg = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM course_registration WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_reg = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Course Registration</title>
    <link rel="stylesheet" href="css/attendance.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .readonly-box { background: #f3f4f6; color: #4f46e5; font-weight: bold; cursor: not-allowed; }
        .pagination { display: flex; justify-content: center; gap: 5px; margin-top: 20px; }
        .pagination a { padding: 8px 12px; border: 1px solid #4f46e5; border-radius: 4px; text-decoration: none; color: #4f46e5; }
        .pagination a.active { background: #4f46e5; color: #fff; }
    </style>
</head>
<body>
    <div class="container">
        <header class="attendance-header">
            <h1>📝 Course <span style="color:#4f46e5">Registration</span></h1>
            <!-- <a href="dashboard.php" class="class-btn" style="background:lightblue; text-decoration:none;"><i class="fa-solid fa-house"></i> Home</a> -->
            <a href="dashboard.php" class="class-btn" style="background:lightblue; text-decoration:none;"><i class="fa-solid fa-house"></i> Back To Dashboard</a>
        </header>

        <div class="registration-card">
            <h3><?= $edit_reg ? '📝 Edit Registration' : '➕ New Registration' ?></h3>
            <form method="POST">
                <input type="hidden" name="reg_id" value="<?= $edit_reg['id'] ?? '' ?>">
                <div class="form-container">
                    <div class="form-row">
                        <div class="input-group">
                            <label>Student Name</label>
                            <select name="student_id" id="student_id" required onchange="filterCourses()" class="input-box">
                                <option value="">-- Choose Student --</option>
                                <?php foreach ($students as $s): ?>
                                    <option value="<?= $s['id'] ?>" 
                                            data-major-id="<?= $s['major_id'] ?>"
                                            data-major-name="<?= $s['major_name'] ?>"
                                            data-semester="<?= $s['current_semester'] ?>"
                                            data-ay="<?= $s['academic_year'] ?>"
                                            <?= (isset($edit_reg) && $edit_reg['student_id'] == $s['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s['name']) ?> (<?= $s['roll_no'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Major & Academic Year</label>
                            <div style="display:flex; gap:5px;">
                                <input type="text" id="display_major" class="readonly-box" readonly placeholder="Major" style="flex:2;">
                                <input type="text" name="academic_year" id="display_ay" class="readonly-box" readonly placeholder="AY" style="flex:1;">
                            </div>
                        </div>
                    </div>

                    <div class="form-row" style="margin-top:15px;">
                        <div class="input-group" style="width:100%;">
                            <label>Select Course</label>
                            <select name="course_id" id="course_id" required class="input-box">
                                <option value="">-- Choose Course --</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?= $c['id'] ?>"
                                            data-majors="<?= $c['assigned_majors'] ?>"
                                            data-semester="<?= $c['term'] ?>"
                                            <?= (isset($edit_reg) && $edit_reg['course_id'] == $c['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['code']) ?> - <?= htmlspecialchars($c['title']) ?> (Sem: <?= $c['term'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="btn-row" style="margin-top:20px;">
                        <button type="submit" name="save_registration" class="register-btn">Confirm Registration</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card" style="margin-top:20px;">
            <table class="student-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Course</th>
                        <th>AY</th>
                        <th>Sem</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reg_list as $r): ?>
                        <tr>
                            <td><strong><?= $r['name'] ?></strong><br><small><?= $r['roll_no'] ?></small></td>
                            <td><?= $r['code'] ?></td>
                            <td><?= $r['academic_year'] ?></td>
                            <td><span class="badge"><?= $r['term'] ?></span></td>
                            <td>
                                <a href="?edit=<?= $r['id'] ?>" class="btn-icon edit-btn"><i class="fa-solid fa-pen"></i></a>
                                <a href="?delete=<?= $r['id'] ?>" class="btn-icon delete-btn" onclick="return confirm('Delete?')"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function filterCourses() {
            const studentSelect = document.getElementById('student_id');
            const courseSelect = document.getElementById('course_id');
            const majorDisplay = document.getElementById('display_major');
            const ayDisplay = document.getElementById('display_ay');

            if (!studentSelect.value) {
                majorDisplay.value = ""; ayDisplay.value = ""; return;
            }

            const selected = studentSelect.options[studentSelect.selectedIndex];
            const majorId = selected.getAttribute('data-major-id');
            const semester = selected.getAttribute('data-semester');
            
            majorDisplay.value = selected.getAttribute('data-major-name');
            ayDisplay.value = selected.getAttribute('data-ay');

            // Course Filtering Logic
            for (let i = 0; i < courseSelect.options.length; i++) {
                const opt = courseSelect.options[i];
                if (opt.value === "") continue;

                const assignedMajors = (opt.getAttribute('data-majors') || "").split(',');
                const courseSem = opt.getAttribute('data-semester');

                // Show only if major matches and semester matches
                if (assignedMajors.includes(majorId) && courseSem === semester) {
                    opt.style.display = 'block';
                } else {
                    opt.style.display = 'none';
                    if (courseSelect.value === opt.value) courseSelect.value = "";
                }
            }
        }
        window.onload = filterCourses;
    </script>
</body>
</html>