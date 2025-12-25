<?php
session_start();
require_once 'database/database.php';

if (empty($_SESSION["current_user"])) {
    header('Location: login.php');
    exit;
}

$db = new Database();

// --- ၁။ Academic Year list ---
$academic_years = ["2024-2025", "2025-2026", "2026-2027", "2027-2028", "2028-2029", "2029-2030"];

// --- ၂။ Register Subject Logic ---
if (isset($_POST['register'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $academic_year = $_POST['academic_year'];

    $stmt_sess = $db->conn->prepare("SELECT session_id FROM course_details WHERE id = ?");
    $stmt_sess->execute([$course_id]);
    $session_id = $stmt_sess->fetchColumn();

    $check_sql = "SELECT COUNT(*) FROM course_registration 
                  WHERE student_id = ? AND course_id = ? AND academic_year = ?";
    $stmt_check = $db->conn->prepare($check_sql);
    $stmt_check->execute([$student_id, $course_id, $academic_year]);

    if ($stmt_check->fetchColumn() > 0) {
        echo "<script>alert('ဤကျောင်းသားသည် အဆိုပါဘာသာရပ်ကို ဤပညာသင်နှစ်အတွက် စာရင်းသွင်းပြီးသားဖြစ်ပါသည်။'); window.location='manage_registration.php';</script>";
        exit();
    }

    $sql = "INSERT INTO course_registration (student_id, course_id, session_id, academic_year) VALUES (?, ?, ?, ?)";
    $db->conn->prepare($sql)->execute([$student_id, $course_id, $session_id, $academic_year]);

    header("Location: manage_registration.php");
    exit();
}

// --- ၃။ Unregister Logic ---
if (isset($_GET['delete'])) {
    $db->conn->prepare("DELETE FROM course_registration WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: manage_registration.php");
    exit();
}

// --- ၄။ reterive data
$students = $db->conn->query("
    SELECT s.*, m.title as major_name 
    FROM student_details s 
    LEFT JOIN major_details m ON s.major_id = m.id 
    ORDER BY s.name ASC
")->fetchAll(PDO::FETCH_ASSOC);

$courses = $db->conn->query("
    SELECT cd.*, sd.term, 
    (SELECT GROUP_CONCAT(major_id) FROM course_assignments WHERE course_id = cd.id) as assigned_majors 
    FROM course_details cd 
    JOIN session_details sd ON cd.session_id = sd.id
")->fetchAll(PDO::FETCH_ASSOC);

$registrations = $db->conn->query("
    SELECT cr.id, cr.academic_year, sd.name, sd.roll_no, cd.title as course_name, cd.code, sess.term 
    FROM course_registration cr 
    JOIN student_details sd ON cr.student_id = sd.id 
    JOIN course_details cd ON cr.course_id = cd.id
    JOIN session_details sess ON cr.session_id = sess.id
    ORDER BY cr.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Course Registration</title>
    <link rel="stylesheet" href="css/attendance.css">

</head>
<body>
    <div class="container">
        <header class="attendance-header">
            <h1>Course <span style="color:#4f46e5">Registration</span></h1>
            <a href="dashboard.php" class="logout-btn" style="text-decoration:none;">⬅ Back To Dashboard</a>
        </header>

        <div class="registration-card">
            <h3>📝 Register Student to Course</h3>
            <form method="POST" id="regForm">
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
                                        data-ay="<?= $s['academic_year'] ?>">
                                        <?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['roll_no']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Major Field</label>
                            <input type="text" id="display_major" class="readonly-box" readonly placeholder="Major auto-filled">
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
                                        data-semester="<?= $c['term'] ?>">
                                        <?= htmlspecialchars($c['code']) ?> - <?= htmlspecialchars($c['title']) ?> (<?= $c['term'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Academic Year</label>
                            <select name="academic_year" id="academic_year" required>
                                <option value="">-- Choose Year --</option>
                                <?php foreach ($academic_years as $year): ?>
                                    <option value="<?= $year ?>"><?= $year ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="btn-row">
                        <button type="submit" name="register" class="register-btn">Confirm Registration</button>
                    </div>

                </div>
            </form>
        </div>

        <div class="table-title" style="font-weight: bold; margin-bottom: 15px; color: #1f2937;">Recent Registrations</div>
        <div class="card" style="padding: 0; overflow: hidden; border: 1px solid #e5e7eb;">
            <table class="student-table" style="margin: 0;">
                <thead style="background: #f8fafc; border-bottom: 2px solid #e5e7eb;">
                    <tr>
                        <th style="padding: 15px;">Student</th>
                        <th>Course</th>
                        <th>Academic Year</th>
                        <th>Semester</th>
                        <th style="text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registrations as $r): ?>
                        <tr>
                            <td style="padding: 12px 15px;">
                                <strong style="color: #111827;"><?= htmlspecialchars($r['name']) ?></strong><br>
                                <small style="color: #6b7280;"><?= htmlspecialchars($r['roll_no']) ?></small>
                            </td>
                            <td>
                                <span style="color: #4f46e5; font-weight: 600;"><?= htmlspecialchars($r['code']) ?></span><br>
                                <small style="color: #374151;"><?= htmlspecialchars($r['course_name']) ?></small>
                            </td>
                            <td style="font-weight:700; color:#111827;"><?= $r['academic_year'] ?></td>
                            <td><span class="badge"><?= htmlspecialchars($r['term']) ?></span></td>
                            <td style="text-align: center;">
                                <a href="?delete=<?= $r['id'] ?>" 
                                   style="color:#ef4444; font-weight: 700; text-decoration: none;"
                                   onclick="return confirm('ဖျက်ရန် သေချာပါသလား?')">Remove</a>
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
        var aySelect = document.getElementById('academic_year');
        var majorDisplay = document.getElementById('display_major');

        if (studentSelect.value === "") {
            courseSelect.value = "";
            majorDisplay.value = "";
            return;
        }

        var selectedOption = studentSelect.options[studentSelect.selectedIndex];
        var selectedMajorId = String(selectedOption.getAttribute('data-major-id'));
        var selectedMajorName = selectedOption.getAttribute('data-major-name');
        var selectedSem = String(selectedOption.getAttribute('data-semester'));
        var selectedAY = String(selectedOption.getAttribute('data-ay'));

        majorDisplay.value = selectedMajorName;

        if(selectedAY) {
            aySelect.value = selectedAY;
        }

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
        courseSelect.value = ""; 
    }
    </script>
</body>
</html>