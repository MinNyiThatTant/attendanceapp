<?php
session_start();
require_once 'database/database.php';

if (empty($_SESSION["current_user"])) {
    header('Location: login.php');
    exit;
}

$db = new Database();

// --- ၁။ Pagination & Settings ---
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$academic_years = ["2024-2025", "2025-2026", "2026-2027", "2027-2028", "2028-2029", "2029-2030"];
$edit_reg = null;

// --- ၂။ CSV Bulk Import (Course တခုတည်းသို့ ကျောင်းသားများစွာသွင်းရန်) ---
if (isset($_POST['import_csv'])) {
    $course_id = $_POST['import_course_id'];
    $academic_year = $_POST['import_ay'];
    $filename = $_FILES["reg_file"]["tmp_name"];

    // session_id ကို course_id ကနေယူမယ်
    $stmt_sess = $db->conn->prepare("SELECT session_id FROM course_details WHERE id = ?");
    $stmt_sess->execute([$course_id]);
    $session_id = $stmt_sess->fetchColumn();

    if ($_FILES["reg_file"]["size"] > 0) {
        $file = fopen($filename, "r");
        fgetcsv($file); // Header skip
        while (($column = fgetcsv($file, 1000, ",")) !== FALSE) {
            $student_roll = $column[0]; // CSV မှာ Roll No ပါရမယ်
            
            // Roll No ကနေ Student ID ရှာမယ်
            $st_stmt = $db->conn->prepare("SELECT id FROM student_details WHERE roll_no = ?");
            $st_stmt->execute([$student_roll]);
            $student_id = $st_stmt->fetchColumn();

            if ($student_id) {
                // Duplicate check
                $check = $db->conn->prepare("SELECT id FROM course_registration WHERE student_id=? AND course_id=? AND academic_year=?");
                $check->execute([$student_id, $course_id, $academic_year]);
                if (!$check->fetch()) {
                    $sql = "INSERT INTO course_registration (student_id, course_id, session_id, academic_year) VALUES (?, ?, ?, ?)";
                    $db->conn->prepare($sql)->execute([$student_id, $course_id, $session_id, $academic_year]);
                }
            }
        }
        fclose($file);
        header("Location: manage_registration.php?msg=imported");
        exit();
    }
}

// --- ၃။ Delete Logic ---
if (isset($_GET['delete'])) {
    $db->conn->prepare("DELETE FROM course_registration WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: manage_registration.php?msg=deleted");
    exit();
}

// --- ၄။ Edit Fetch ---
if (isset($_GET['edit'])) {
    $stmt = $db->conn->prepare("SELECT * FROM course_registration WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_reg = $stmt->fetch(PDO::FETCH_ASSOC);
}

// --- ၅။ Save / Update Logic ---
if (isset($_POST['save_registration'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $academic_year = $_POST['academic_year'];
    $reg_id = $_POST['reg_id'];

    $stmt_sess = $db->conn->prepare("SELECT session_id FROM course_details WHERE id = ?");
    $stmt_sess->execute([$course_id]);
    $session_id = $stmt_sess->fetchColumn();

    if (!empty($reg_id)) {
        $sql = "UPDATE course_registration SET student_id=?, course_id=?, session_id=?, academic_year=? WHERE id=?";
        $db->conn->prepare($sql)->execute([$student_id, $course_id, $session_id, $academic_year, $reg_id]);
    } else {
        $sql = "INSERT INTO course_registration (student_id, course_id, session_id, academic_year) VALUES (?, ?, ?, ?)";
        $db->conn->prepare($sql)->execute([$student_id, $course_id, $session_id, $academic_year]);
    }
    header("Location: manage_registration.php?msg=success");
    exit();
}

// --- ၆။ Search & Data Fetching ---
$search = $_GET['search'] ?? '';
$search_query = "";
if ($search) {
    $search_query = " WHERE sd.name LIKE :s OR sd.roll_no LIKE :s OR cd.title LIKE :s OR cd.code LIKE :s";
}

$count_sql = "SELECT COUNT(*) FROM course_registration cr JOIN student_details sd ON cr.student_id = sd.id JOIN course_details cd ON cr.course_id = cd.id $search_query";
$stmt_count = $db->conn->prepare($count_sql);
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

$stmt_reg = $db->conn->prepare($registrations_sql);
if ($search) $stmt_reg->bindValue(':s', "%$search%");
$stmt_reg->execute();
$registrations = $stmt_reg->fetchAll(PDO::FETCH_ASSOC);

// For dropdowns
$students = $db->conn->query("SELECT s.*, m.title as major_name FROM student_details s LEFT JOIN major_details m ON s.major_id = m.id ORDER BY s.name ASC")->fetchAll(PDO::FETCH_ASSOC);
$courses = $db->conn->query("SELECT cd.*, sd.term, (SELECT GROUP_CONCAT(major_id) FROM course_assignments WHERE course_id = cd.id) as assigned_majors FROM course_details cd JOIN session_details sd ON cd.session_id = sd.id")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Course Registration</title>
    <link rel="stylesheet" href="css/attendance.css">
    <style>
        .readonly-box { background: #f9fafb; border: 1px solid #d1d5db; color: #6b7280; cursor: not-allowed; }
        .search-container { display: flex; gap: 10px; margin-bottom: 20px; }
        .pagination { display: flex; justify-content: center; gap: 5px; margin-top: 20px; }
        .page-link { padding: 8px 12px; border: 1px solid #4f46e5; color: #4f46e5; text-decoration: none; border-radius: 4px; }
        .page-link.active { background: #4f46e5; color: white; }
        .import-section { background: #eff6ff; border: 1px dashed #3b82f6; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <header class="attendance-header">
            <h1>Course <span style="color:#4f46e5">Registration</span></h1>
            <div style="display:flex; gap:10px;">
                <form method="GET" class="search-group">
                    <input type="text" name="search" placeholder="Search student or course..." value="<?= htmlspecialchars($search) ?>" class="input-box" style="width:250px; margin:0;">
                    <button type="submit" class="class-btn">🔍</button>
                </form>
                <a href="dashboard.php" class="class-btn" style="text-decoration:none; background:lightblue;">⬅ Back To Dashboard</a>
            </div>
        </header>

        <div class="import-section">
            <h3 style="margin-top:0;">📤 Bulk Registration via CSV</h3>
            <form method="POST" enctype="multipart/form-data" style="display:flex; gap:15px; align-items:flex-end; flex-wrap:wrap;">
                <div>
                    <label style="display:block; font-size:12px;">1. Select Course</label>
                    <select name="import_course_id" required class="input-box" style="width:200px;">
                        <?php foreach($courses as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= $c['code'] ?> - <?= $c['title'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:12px;">2. Academic Year</label>
                    <select name="import_ay" required class="input-box" style="width:150px;">
                        <?php foreach($academic_years as $ay): ?>
                            <option value="<?= $ay ?>"><?= $ay ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:12px;">3. CSV File (RollNo only)</label>
                    <input type="file" name="reg_file" accept=".csv" required>
                </div>
                <button type="submit" name="import_csv" class="register-btn" style="padding:10px 20px;">Upload & Register</button>
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
                                        <?= (isset($edit_reg) && $edit_reg['course_id'] == $c['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['code']) ?> - <?= htmlspecialchars($c['title']) ?> (<?= $c['term'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Academic Year</label>
                            <select name="academic_year" required>
                                <?php foreach ($academic_years as $year): ?>
                                    <option value="<?= $year ?>" <?= (isset($edit_reg) && $edit_reg['academic_year'] == $year) ? 'selected' : '' ?>><?= $year ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="btn-row">
                        <?php if($edit_reg): ?><a href="manage_registration.php" class="class-btn" style="background:#6b7280; text-decoration:none;">Cancel</a><?php endif; ?>
                        <button type="submit" name="save_registration" class="register-btn"><?= $edit_reg ? 'Update' : 'Confirm' ?> Registration</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-title">Student Course List (Total: <?= $total_rows ?>)</div>
        <div class="card" style="padding: 0; overflow: hidden;">
            <table class="student-table" style="margin: 0;">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Semester</th>
                        <th style="text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registrations as $r): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($r['name']) ?></strong><br><small><?= htmlspecialchars($r['roll_no']) ?></small></td>
                            <td><span style="color: #4f46e5; font-weight: 600;"><?= htmlspecialchars($r['code']) ?></span><br><small><?= htmlspecialchars($r['course_name']) ?></small></td>
                            <td><?= $r['academic_year'] ?></td>
                            <td><span class="badge"><?= htmlspecialchars($r['term']) ?></span></td>
                            <td style="text-align: center;">
                                <a href="?edit=<?= $r['id'] ?>" style="color:#4f46e5; font-weight:700; text-decoration:none;">Edit</a> | 
                                <a href="?delete=<?= $r['id'] ?>" style="color:#ef4444; font-weight:700; text-decoration:none;" onclick="return confirm('ဖျက်ရန် သေချာပါသလား?')">Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if($total_pages > 1): ?>
        <div class="pagination">
            <?php for($i=1; $i<=$total_pages; $i++): ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="page-link <?= ($page==$i)?'active':'' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
    function filterCourses() {
        var studentSelect = document.getElementById('student_id');
        var courseSelect = document.getElementById('course_id');
        var majorDisplay = document.getElementById('display_major');

        if (studentSelect.value === "") {
            majorDisplay.value = "";
            return;
        }

        var selectedOption = studentSelect.options[studentSelect.selectedIndex];
        var selectedMajorId = String(selectedOption.getAttribute('data-major-id'));
        var selectedMajorName = selectedOption.getAttribute('data-major-name');
        var selectedSem = String(selectedOption.getAttribute('data-semester'));

        majorDisplay.value = selectedMajorName;

        for (var i = 0; i < courseSelect.options.length; i++) {
            var option = courseSelect.options[i];
            if (option.value === "") continue;

            var assignedMajorsStr = option.getAttribute('data-majors') || "";
            var courseSem = option.getAttribute('data-semester');
            var majorsArray = assignedMajorsStr.split(',');

            if (majorsArray.includes(selectedMajorId) && courseSem === selectedSem) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        }
    }
    // Run on page load for edit mode
    window.onload = filterCourses;
    </script>
</body>
</html>