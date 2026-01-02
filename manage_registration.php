<?php
session_start();
require_once 'database/database.php';

if (empty($_SESSION["current_user"])) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$conn = $db->conn;

// Clear All Logic 
if (isset($_GET['clear_all'])) {
    $conn->query("DELETE FROM course_registration");
    header("Location: manage_registration.php?msg=all_cleared");
    exit();
}

// Delete Logic 
if (isset($_GET['delete'])) {
    $conn->prepare("DELETE FROM course_registration WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: manage_registration.php?msg=deleted");
    exit();
}

// Bulk Import CSV 
if (isset($_POST['import_csv'])) {
    $course_id = $_POST['import_course_id'];
    $filename = $_FILES["reg_file"]["tmp_name"];
    $current_academic_year = $db->getAcademicYear();

    // course details fetch (session and academic_year)
    $stmt = $conn->prepare("INSERT INTO course_registration (student_id, course_id, academic_year) VALUES (?, ?, ?)");
    $stmt->execute([$student_id, $course_id, $current_academic_year]);
    $stmt_c = $conn->prepare("SELECT session_id, academic_year FROM course_details WHERE id = ?");
    $stmt_c->execute([$course_id]);
    $course_info = $stmt_c->fetch(PDO::FETCH_ASSOC);

    if ($_FILES["reg_file"]["size"] > 0 && $course_info) {
        $file = fopen($filename, "r");
        fgetcsv($file); // Header skip
        while (($column = fgetcsv($file, 1000, ",")) !== FALSE) {
            $student_roll = $column[0];

            $st_stmt = $conn->prepare("SELECT id, academic_year FROM student_details WHERE roll_no = ?");
            $st_stmt->execute([$student_roll]);
            $student_data = $st_stmt->fetch(PDO::FETCH_ASSOC);

            if ($student_data) {
                // Check if Academic Year matches before importing
                if ($student_data['academic_year'] === $course_info['academic_year']) {
                    $student_id = $student_data['id'];
                    $academic_year = $student_data['academic_year'];

                    $check = $conn->prepare("SELECT id FROM course_registration WHERE student_id=? AND course_id=? AND academic_year=?");
                    $check->execute([$student_id, $course_id, $academic_year]);
                    if (!$check->fetch()) {
                        $sql = "INSERT INTO course_registration (student_id, course_id, session_id, academic_year) VALUES (?, ?, ?, ?)";
                        $conn->prepare($sql)->execute([$student_id, $course_id, $course_info['session_id'], $academic_year]);
                    }
                }
            }
        }
        fclose($file);
        header("Location: manage_registration.php?msg=imported");
        exit();
    }
}

// Save / Update Logic 
if (isset($_POST['save_registration'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $student_academic_year = $_POST['academic_year'];
    $reg_id = $_POST['reg_id'];

    // Fetch Course details to verify Academic Year matching
    $stmt_course = $conn->prepare("SELECT session_id, academic_year FROM course_details WHERE id = ?");
    $stmt_course->execute([$course_id]);
    $course_data = $stmt_course->fetch(PDO::FETCH_ASSOC);

    if (!$course_data) {
        echo "<script>alert('Error: Course မတွေ့ရှိပါ။'); window.location='manage_registration.php';</script>";
        exit();
    }

    // *** BLOCK MISMATCHING ACADEMIC YEARS ***
    if ($student_academic_year !== $course_data['academic_year']) {
        echo "<script>alert('Error: ကျောင်းသား၏ Academic Year ($student_academic_year) နှင့် Course ၏ Year ({$course_data['academic_year']}) မကိုက်ညီပါ။'); window.location='manage_registration.php';</script>";
        exit();
    }

    $session_id = $course_data['session_id'];

    if (empty($reg_id)) {
        $check = $conn->prepare("SELECT id FROM course_registration WHERE student_id=? AND course_id=? AND academic_year=?");
        $check->execute([$student_id, $course_id, $student_academic_year]);

        if ($check->fetch()) {
            echo "<script>alert('Error: ဤကျောင်းသားသည် Register လုပ်ပြီးသားဖြစ်နေပါသည်။'); window.location='manage_registration.php';</script>";
            exit();
        }

        $sql = "INSERT INTO course_registration (student_id, course_id, session_id, academic_year) VALUES (?, ?, ?, ?)";
        $conn->prepare($sql)->execute([$student_id, $course_id, $session_id, $student_academic_year]);
    } else {
        $sql = "UPDATE course_registration SET student_id=?, course_id=?, session_id=?, academic_year=? WHERE id=?";
        $conn->prepare($sql)->execute([$student_id, $course_id, $session_id, $student_academic_year, $reg_id]);
    }
    header("Location: manage_registration.php?msg=success");
    exit();
}

// Fetch Data for list and dropdowns
$search = $_GET['search'] ?? '';
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search_query = "";
if ($search) {
    $search_query = " WHERE sd.name LIKE :s OR sd.roll_no LIKE :s OR cd.title LIKE :s OR cd.code LIKE :s";
}

$count_sql = "SELECT COUNT(*) FROM course_registration cr JOIN student_details sd ON cr.student_id = sd.id JOIN course_details cd ON cr.course_id = cd.id $search_query";
$stmt_count = $conn->prepare($count_sql);
if ($search) $stmt_count->bindValue(':s', "%$search%");
$stmt_count->execute();
$total_rows = $stmt_count->fetchColumn();
$total_pages = ceil($total_rows / $limit);

$registrations_sql = "
    SELECT cr.id, cr.academic_year, sd.name, sd.roll_no, cd.title as course_name, cd.code, sess.term 
    FROM course_registration cr 
    JOIN student_details sd ON cr.student_id = sd.id 
    JOIN course_details cd ON cr.course_id = cd.id
    JOIN session_details sess ON cr.session_id = sess.id
    $search_query
    ORDER BY cr.id DESC LIMIT $limit OFFSET $offset";

$stmt_reg = $conn->prepare($registrations_sql);
if ($search) $stmt_reg->bindValue(':s', "%$search%");
$stmt_reg->execute();
$registrations = $stmt_reg->fetchAll(PDO::FETCH_ASSOC);

$students = $conn->query("SELECT s.*, m.title as major_name FROM student_details s LEFT JOIN major_details m ON s.major_id = m.id ORDER BY s.name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch courses with Academic Year included
$courses = $conn->query("SELECT cd.*, sd.term, cd.academic_year as course_ay, (SELECT GROUP_CONCAT(major_id) FROM course_assignments WHERE course_id = cd.id) as assigned_majors FROM course_details cd JOIN session_details sd ON cd.session_id = sd.id")->fetchAll(PDO::FETCH_ASSOC);

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
    <style>
        .readonly-box {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            color: #374151;
            font-weight: bold;
            cursor: not-allowed;
        }

        .import-section {
            background: #eff6ff;
            border: 1px dashed #3b82f6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
        }

        .page-link {
            padding: 8px 12px;
            border: 1px solid #4f46e5;
            color: #4f46e5;
            text-decoration: none;
            border-radius: 4px;
        }

        .page-link.active {
            background: #4f46e5;
            color: white;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>

<body>
    <div class="container">
        <header class="attendance-header">
            <h1>📝 Course <span style="color:#4f46e5">Registration</span></h1>
            <div style="display:flex; gap:10px;">
                <form method="GET" style="display:flex; gap:5px;">
                    <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>" class="input-box" style="width:200px; margin:0;">
                    <button type="submit" class="class-btn">🔍</button>
                </form>
                <a href="dashboard.php" class="class-btn" style="text-decoration:none; background:lightblue;"><i class="fa-solid fa-house"></i> Back To Dashboard</a>
            </div>
        </header>

        <div class="import-section">
            <h3>📤 Bulk Registration via CSV</h3>
            <form method="POST" enctype="multipart/form-data" style="display:flex; gap:15px; align-items:flex-end;">
                <div>
                    <label style="display:block; font-size:12px;">Course</label>
                    <select name="import_course_id" required class="input-box" style="width:200px;">
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= $c['code'] ?> - <?= $c['title'] ?> (<?= $c['academic_year'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <input type="file" name="reg_file" accept=".csv" required>
                </div>
                <button type="submit" name="import_csv" class="register-btn">Upload</button>
            </form>
        </div>

        <div class="registration-card">
            <h3><?= $edit_reg ? '📝 Edit Registration' : '➕ Register Student' ?></h3>
            <form method="POST">
                <input type="hidden" name="reg_id" value="<?= $edit_reg['id'] ?? '' ?>">
                <div class="form-container">
                    <div class="form-row">
                        <div class="input-group">
                            <label>Student Name</label>
                            <select name="student_id" id="student_id" required onchange="filterCourses()">
                                <option value="">-- Choose Student --</option>
                                <?php foreach ($students as $s): ?>
                                    <option value="<?= $s['id'] ?>"
                                        data-major-id="<?= $s['major_id'] ?>"
                                        data-major-name="<?= $s['major_name'] ?>"
                                        data-semester="<?= $s['current_semester'] ?>"
                                        data-ay="<?= $s['academic_year'] ?>"
                                        <?= (isset($edit_reg) && $edit_reg['student_id'] == $s['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['roll_no']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Major Field</label>
                            <input type="text" id="display_major" class="readonly-box" readonly placeholder="Auto-filled">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="input-group">
                            <label>Select Course</label>
                            <select name="course_id" id="course_id" required>
                                <option value="">-- Choose Course --</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?= $c['id'] ?>"
                                        data-majors="<?= $c['assigned_majors'] ?>"
                                        data-semester="<?= $c['term'] ?>"
                                        data-ay="<?= $c['course_ay'] ?>"
                                        <?= (isset($edit_reg) && $edit_reg['course_id'] == $c['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['code']) ?> - <?= htmlspecialchars($c['title'] ?? '') ?> (<?= $c['course_ay'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Academic Year</label>
                            <input type="text" name="academic_year" id="display_ay" class="readonly-box" readonly
                                value="<?= $edit_reg['academic_year'] ?? '' ?>" placeholder="Auto-filled">
                        </div>
                    </div>

                    <div class="btn-row">
                        <button type="submit" name="save_registration" class="register-btn"><?= $edit_reg ? 'Update' : 'Confirm' ?></button>
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
                        <th>Academic Year</th>
                        <th>Semester</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registrations as $r): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($r['name']) ?></strong><br><small><?= htmlspecialchars($r['roll_no']) ?></small></td>
                            <td><span style="color: #4f46e5;"><?= htmlspecialchars($r['code']) ?></span></td>
                            <td><?= $r['academic_year'] ?></td>
                            <td><span class="badge"><?= htmlspecialchars($r['term']) ?></span></td>
                            <td>
                                <a href="?edit=<?= $r['id'] ?>" class="btn-icon edit-btn"><i class="fa-solid fa-pen"></i></a>
                                <a href="?delete=<?= $r['id'] ?>" class="btn-icon delete-btn" onclick="return confirm('Confirm delete?')"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function filterCourses() {
    var studentSelect = document.getElementById('student_id');
    var courseSelect = document.getElementById('course_id');
    var majorDisplay = document.getElementById('display_major');
    var ayDisplay = document.getElementById('display_ay');

    if (studentSelect.value === "") {
        majorDisplay.value = "";
        ayDisplay.value = "";
        return;
    }

    var selectedOption = studentSelect.options[studentSelect.selectedIndex];
    var selectedMajorId = String(selectedOption.getAttribute('data-major-id'));
    var selectedMajorName = selectedOption.getAttribute('data-major-name');
    var selectedSem = String(selectedOption.getAttribute('data-semester'));
    var selectedAY = selectedOption.getAttribute('data-ay');

    majorDisplay.value = selectedMajorName;
    ayDisplay.value = selectedAY;

    let hasVisibleCourse = false;

    for (var i = 0; i < courseSelect.options.length; i++) {
        var option = courseSelect.options[i];
        if (option.value === "") continue;

        var assignedMajorsStr = option.getAttribute('data-majors') || "";
        var courseSem = String(option.getAttribute('data-semester'));
        var courseAY = option.getAttribute('data-ay');
        var majorsArray = assignedMajorsStr.split(',');

        // စစ်ဆေးချက် - Major ကိုက်ညီရမည် + Semester ကိုက်ညီရမည် + Academic Year ကိုက်ညီရမည်
        if (majorsArray.includes(selectedMajorId) && courseSem === selectedSem && courseAY === selectedAY) {
            option.style.display = 'block';
            hasVisibleCourse = true;
        } else {
            option.style.display = 'none';
            // အကယ်၍ လက်ရှိ select လုပ်ထားတာက hide လုပ်မယ့်ဟာဖြစ်နေရင် select ကို reset ချမယ်
            if (courseSelect.value === option.value) {
                courseSelect.value = "";
            }
        }
    }
}
        window.onload = filterCourses;
    </script>
</body>

</html>