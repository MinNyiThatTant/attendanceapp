<?php
session_start();
require_once 'database/database.php';
if (empty($_SESSION["current_user"])) { header('Location: login.php'); exit; }

$db = new Database();
$conn = $db->conn;

$major_id = $_GET['major_id'] ?? '';
$major_name = $_GET['major_name'] ?? 'Major';
$semester = $_GET['semester'] ?? '';
$course_id = $_GET['course_id'] ?? '';

// Semester များအားလုံးကို ဆွဲထုတ်ခြင်း
$sem_stmt = $conn->query("SELECT DISTINCT term FROM session_details ORDER BY id ASC");
$all_semesters = $sem_stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($semester) && !empty($all_semesters)) {
    $semester = $all_semesters[0]['term'];
}

// --- Dynamic Academic Year Logic ပြင်ဆင်ထားသောအပိုင်း ---
if (isset($_GET['academic_year']) && !empty($_GET['academic_year'])) {
    $academic_year = $_GET['academic_year'];
} else {
    $current_month = (int)date('m');
    $current_year = (int)date('Y');

    // ဇွန်လ (Month 6) မတိုင်ခင်ဆိုရင် ယခင်နှစ်-လက်ရှိနှစ် (ဥပမာ- 2025-2026)
    if ($current_month < 6) {
        $academic_year = ($current_year - 1) . "-" . $current_year;
    } else {
        $academic_year = $current_year . "-" . ($current_year + 1);
    }
}
// ---------------------------------------------------

// ရွေးချယ်ထားသော Major နှင့် Semester အတွက် ဘာသာရပ်များကို ရှာဖွေခြင်း
$stmt_sub = $conn->prepare("
    SELECT DISTINCT cd.* FROM course_details cd 
    INNER JOIN course_assignments ca ON cd.id = ca.course_id 
    INNER JOIN session_details sd ON cd.session_id = sd.id 
    WHERE sd.term = :sem AND ca.major_id = :mid
");
$stmt_sub->execute([':sem' => $semester, ':mid' => $major_id]);
$subjects = $stmt_sub->fetchAll(PDO::FETCH_ASSOC);

// ရွေးချယ်ထားသော ဘာသာရပ်အတွက် ကျောင်းသားများကို ဆွဲထုတ်ခြင်း
$students = [];
if ($course_id) {
    $stmt_std = $conn->prepare("
        SELECT sd.* FROM student_details sd 
        JOIN course_registration cr ON sd.id = cr.student_id 
        WHERE cr.course_id = :cid AND cr.academic_year = :ayear AND sd.major_id = :mid
        ORDER BY sd.roll_no ASC
    ");
    $stmt_std->execute([':cid' => $course_id, ':ayear' => $academic_year, ':mid' => $major_id]);
    $students = $stmt_std->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance - <?= htmlspecialchars($major_name) ?></title>
    <link rel="stylesheet" href="css/attendance.css">
</head>
<body>
    <div class="container">
        <header class="attendance-header">
            <div>
                <h1><?= htmlspecialchars($major_name) ?></h1>
                <p style="color: #4f46e5; font-weight: bold;">AY: <?= htmlspecialchars($academic_year) ?> | <?= htmlspecialchars($semester) ?></p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="dashboard.php" class="class-btn" style="background: #6b7280; color: white; text-decoration: none;">⬅ Back To Dashboard</a>
                <button class="logout-btn" id="btnlogout">Logout</button>
            </div>
        </header>

        <div class="filter-section" style="margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 5px;">
            <?php foreach ($all_semesters as $s_row): 
                $active = ($semester == $s_row['term']) ? "active" : "";
            ?>
                <a href="attendance.php?major_id=<?= $major_id ?>&major_name=<?= urlencode($major_name) ?>&semester=<?= urlencode($s_row['term']) ?>&academic_year=<?= urlencode($academic_year) ?>"
                   class="class-btn <?= $active ?>"><?= htmlspecialchars($s_row['term']) ?></a>
            <?php endforeach; ?>
        </div>

        <div class="card" style="margin-bottom:20px;">
            <label style="font-weight: bold;">Select Subject: </label>
            <select onchange="location = this.value;" style="padding: 10px; width: 100%; border-radius: 5px; margin-top: 5px;">
                <option value="">-- Choose Subject (<?= count($subjects) ?> found) --</option>
                <?php foreach ($subjects as $s): ?>
                    <option value="attendance.php?major_id=<?= $major_id ?>&major_name=<?= urlencode($major_name) ?>&semester=<?= urlencode($semester) ?>&academic_year=<?= urlencode($academic_year) ?>&course_id=<?= $s['id'] ?>"
                        <?= ($course_id == $s['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['code']) ?> - <?= htmlspecialchars($s['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <form action="save_attendance.php" method="POST">
            <input type="hidden" name="course_id" value="<?= $course_id ?>">
            <input type="hidden" name="major_id" value="<?= $major_id ?>">
            <input type="hidden" name="academic_year" value="<?= htmlspecialchars($academic_year) ?>">
            <input type="hidden" name="attendance_date" value="<?= date('Y-m-d') ?>">

            <div class="card">
                <?php if ($course_id && empty($students)): ?>
                    <p style="text-align: center; color: red;">No students found in <?= htmlspecialchars($academic_year) ?> for this course.</p>
                <?php elseif (!$course_id): ?>
                    <p style="text-align: center; color: gray;">Please select a subject first.</p>
                <?php else: ?>
                    <table class="student-table" style="width:100%">
                        <thead>
                            <tr>
                                <th style="text-align: left;">Roll No & Name</th>
                                <th>Present</th>
                                <th>Absent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $st): ?>
                                <tr>
                                    <td><b><?= htmlspecialchars($st['roll_no']) ?></b><br><?= htmlspecialchars($st['name']) ?></td>
                                    <td align="center"><input type="radio" name="status[<?= $st['id'] ?>]" value="present" checked></td>
                                    <td align="center"><input type="radio" name="status[<?= $st['id'] ?>]" value="absent"></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit" class="save-btn" style="width: 100%; margin-top: 20px;">✅ Save Attendance</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <script src="js/logout.js"></script>
</body>
</html>