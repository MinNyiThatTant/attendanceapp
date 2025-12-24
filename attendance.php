<?php
session_start();
require_once 'database/database.php';
if (empty($_SESSION["current_user"])) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$conn = $db->conn;

// access data from url
$major_id = $_GET['major_id'] ?? '';
$major_name = $_GET['major_name'] ?? 'Major';
$semester = $_GET['semester'] ?? '1st semester';
$course_id = $_GET['course_id'] ?? '';

// reterive subject which are selected Major and Semester 
// in course_details (major_id, session_id)
$stmt_sub = $conn->prepare("
    SELECT cd.* FROM course_details cd 
    INNER JOIN course_assignments ca ON cd.id = ca.course_id 
    INNER JOIN session_details sd ON cd.session_id = sd.id 
    WHERE sd.term = :sem 
    AND ca.major_id = :mid
");
$stmt_sub->execute([':sem' => $semester, ':mid' => $major_id]);
$subjects = $stmt_sub->fetchAll(PDO::FETCH_ASSOC);
// reterive Major Students
$students = [];
if ($course_id) {
    // take Major student from course_registration
    $stmt_std = $conn->prepare("
        SELECT sd.* FROM student_details sd 
        JOIN course_registration cr ON sd.id = cr.student_id 
        WHERE cr.course_id = :cid 
        AND sd.major_id = :mid
        ORDER BY sd.roll_no ASC
    ");
    $stmt_std->execute([':cid' => $course_id, ':mid' => $major_id]);
    $students = $stmt_std->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="my">

<head>
    <meta charset="UTF-8">
    <title>Attendance - <?= $major_name ?></title>
    <link rel="stylesheet" href="css/attendance.css">
</head>

<body>
    <div class="container">
        <header class="attendance-header">
            <h1><?= $major_name ?> <small style="font-size: 1.2rem; color: #666;">(<?= $semester ?>)</small></h1>
            <div style="display: flex; align-items: center; gap: 15px;">
                <a href="dashboard.php" class="class-btn" style="background: #6b7280; color: white; text-decoration: none;">⬅ Dashboard</a>
                <span>📅 <?= date('d-m-Y') ?></span>
                <button class="logout-btn" id="btnlogout">Logout</button>
            </div>
        </header>

        <div class="filter-section">
            <?php for ($i = 1; $i <= 10; $i++):
                $sem = ($i == 1) ? "1st semester" : (($i == 2) ? "2nd semester" : $i . "th semester");
                $active = ($semester == $sem) ? "active" : "";
            ?>
                <a href="attendance.php?major_id=<?= $major_id ?>&major_name=<?= $major_name ?>&semester=<?= $sem ?>" class="class-btn <?= $active ?>"><?= $sem ?></a>
            <?php endfor; ?>
        </div>

        <div class="card" style="margin-bottom:20px;">
            <label style="font-weight: bold;">Select Subject: </label>
            <select onchange="location = this.value;" style="padding: 8px; width: 100%;">
                <option value="">-- Choose Subject (Total: <?= count($subjects) ?>) --</option>
                <?php if (!empty($subjects)): ?>
                    <?php foreach ($subjects as $s): ?>
                        <option value="attendance.php?major_id=<?= $major_id ?>&major_name=<?= $major_name ?>&semester=<?= $semester ?>&course_id=<?= $s['id'] ?>" <?= ($course_id == $s['id']) ? 'selected' : '' ?>>
                            <?= $s['code'] ?> - <?= $s['title'] ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option disabled>No subjects found for this semester</option>
                <?php endif; ?>
            </select>
        </div>

        <form action="save_attendance.php" method="POST">
            <input type="hidden" name="course_id" value="<?= $course_id ?>">
            <input type="hidden" name="major_id" value="<?= $major_id ?>">

            <div class="card">
                <?php if ($course_id && empty($students)): ?>
                    <p style="text-align: center; color: #ef4444; padding: 20px;">ဤဘာသာရပ်တွင် စာရင်းသွင်းထားသော ကျောင်းသား မရှိသေးပါ။</p>
                <?php elseif (!$course_id): ?>
                    <p style="text-align: center; color: #6b7280; padding: 20px;">ကျောင်းသားစာရင်း မြင်ရရန် ဘာသာရပ်ကို အရင်ရွေးချယ်ပါ။</p>
                <?php else: ?>
                    <table class="student-table">
                        <thead>
                            <tr>
                                <th>Student Name & Roll No</th>
                                <th style="text-align: center;">Present</th>
                                <th style="text-align: center;">Absent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $st): ?>
                                <tr>
                                    <td>
                                        <strong><?= $st['name'] ?></strong><br>
                                        <small style="color: #666;"><?= $st['roll_no'] ?></small>
                                    </td>
                                    <td style="text-align: center;">
                                        <input type="radio" name="status[<?= $st['id'] ?>]" value="present" checked style="transform: scale(1.5);">
                                    </td>
                                    <td style="text-align: center;">
                                        <input type="radio" name="status[<?= $st['id'] ?>]" value="absent" style="transform: scale(1.5);">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit" class="save-btn" style="width: 100%; margin-top: 20px; font-size: 1.1rem;">Save Attendance</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <script src="js/jquery.js"></script>
    <script src="js/logout.js"></script>
</body>

</html>