<?php
session_start();
require_once 'database/database.php';
$db = new Database();

// Register Subject
if (isset($_POST['register'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];

    // reterive (session_id) from course_details
    $stmt_sess = $db->conn->prepare("SELECT session_id FROM course_details WHERE id = ?");
    $stmt_sess->execute([$course_id]);
    $session_id = $stmt_sess->fetchColumn();

    $sql = "INSERT INTO course_registration (student_id, course_id, session_id) VALUES (?, ?, ?)";
    $db->conn->prepare($sql)->execute([$student_id, $course_id, $session_id]);
    header("Location: manage_registration.php");
}

// Unregister
if (isset($_GET['delete'])) {
    $db->conn->prepare("DELETE FROM course_registration WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: manage_registration.php");
}

$students = $db->conn->query("SELECT * FROM student_details ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$courses = $db->conn->query("SELECT cd.*, sd.term FROM course_details cd JOIN session_details sd ON cd.session_id = sd.id")->fetchAll(PDO::FETCH_ASSOC);
$registrations = $db->conn->query("SELECT cr.id, sd.name, sd.roll_no, cd.title as course_name, cd.code, sess.term 
    FROM course_registration cr 
    JOIN student_details sd ON cr.student_id = sd.id 
    JOIN course_details cd ON cr.course_id = cd.id
    JOIN session_details sess ON cr.session_id = sess.id")->fetchAll(PDO::FETCH_ASSOC);
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
            <a href="dashboard.php" class="logout-btn" style="text-decoration:none;">⬅ Back</a>
        </header>

        // Registration Form ပိုင်း
        <div class="card" style="margin-bottom:20px;">
            <h3>Register Student to Course</h3>
            <form method="POST" id="regForm">
                <div style="display:grid; grid-template-columns: 1fr 1fr auto; gap:10px;">

                    <select name="student_id" id="student_id" required onchange="filterCourses()" style="padding:10px;">
                        <option value="">-- Select Student --</option>
                        <?php foreach ($students as $s): ?>
                            <option value="<?= $s['id'] ?>" data-major="<?= $s['major_id'] ?>">
                                <?= $s['name'] ?> (<?= $s['roll_no'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="course_id" id="course_id" required style="padding:10px;">
                        <option value="">-- Select Course --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>" data-major="<?= $c['major_id'] ?>">
                                <?= $c['code'] ?> - <?= $c['title'] ?> (<?= $c['term'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" name="register" class="save-btn" style="margin:0;">Register</button>
                </div>
            </form>
        </div>

        <script>
            function filterCourses() {
                var studentSelect = document.getElementById('student_id');
                var courseSelect = document.getElementById('course_id');

                // take major selected ID of student
                var selectedStudentMajor = studentSelect.options[studentSelect.selectedIndex].getAttribute('data-major');

                // loop Courses
                for (var i = 0; i < courseSelect.options.length; i++) {
                    var option = courseSelect.options[i];
                    var courseMajor = option.getAttribute('data-major');

                    if (option.value === "") continue; 

                    // check major
                    if (courseMajor === selectedStudentMajor) {
                        option.style.display = 'block';
                    } else {
                        option.style.display = 'none';
                    }
                }
                // reset course
                courseSelect.value = "";
            }
        </script>

        <div class="card">
            <table class="student-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Semester</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registrations as $r): ?>
                        <tr>
                            <td><?= $r['name'] ?> (<?= $r['roll_no'] ?>)</td>
                            <td><?= $r['code'] ?> - <?= $r['course_name'] ?></td>
                            <td><?= $r['term'] ?></td>
                            <td><a href="?delete=<?= $r['id'] ?>" style="color:red;" onclick="return confirm('ဖျက်ရန် သေချာပါသလား?')">Unregister</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>