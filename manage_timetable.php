<?php
session_start();
require_once 'database/database.php';
$db = new Database();


// FETCH COURSES BASED ON MAJOR 
if (isset($_GET['get_courses_by_major'])) {
    $m_id = $_GET['get_courses_by_major'];
    
    $stmt = $db->conn->prepare("SELECT c.id, c.title FROM course_details c 
                                JOIN course_assignments ca ON c.id = ca.course_id 
                                WHERE ca.major_id = ?");
    $stmt->execute([$m_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// DELETE LOGIC 
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $db->conn->prepare("DELETE FROM timetable WHERE id = ?")->execute([$id]);
    header("Location: manage_timetable.php?msg=deleted");
    exit();
}

// EDIT DATA FETCH 
$edit_timetable = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $db->conn->prepare("SELECT * FROM timetable WHERE id = ?");
    $stmt->execute([$id]);
    $edit_timetable = $stmt->fetch(PDO::FETCH_ASSOC);
}

// SAVE / UPDATE LOGIC 
if (isset($_POST['save_timetable'])) {
    $major_id = $_POST['major_id'];
    $course_id = $_POST['course_id'];
    $day = $_POST['day_of_week'];
    $period = $_POST['period'];
    $academic_year = $_POST['academic_year'];
    $timetable_id = $_POST['timetable_id'] ?? null; // update

    $times = [
        1 => ['19:00:00', '20:00:59'],
        2 => ['20:00:00', '21:00:59'],
        3 => ['21:00:00', '22:00:59'],
        4 => ['22:00:00', '23:00:59'],
        5 => ['14:00:00', '15:00:00'],
        6 => ['15:00:00', '16:00:59']
    ];

    $start = $times[$period][0];
    $end = $times[$period][1];

    if ($timetable_id) {
        $sql = "UPDATE timetable SET major_id=?, course_id=?, day_of_week=?, period=?, start_time=?, end_time=?, academic_year=? WHERE id=?";
        $db->conn->prepare($sql)->execute([$major_id, $course_id, $day, $period, $start, $end, $academic_year, $timetable_id]);
    } else {
        $sql = "INSERT INTO timetable (major_id, course_id, day_of_week, period, start_time, end_time, academic_year) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $db->conn->prepare($sql)->execute([$major_id, $course_id, $day, $period, $start, $end, $academic_year]);
    }
    header("Location: manage_timetable.php?success=1");
    exit();
}

$majors = $db->conn->query("SELECT * FROM major_details")->fetchAll();
$timetables = $db->conn->query("SELECT t.*, m.title as major_name, c.title as course_name 
                                FROM timetable t 
                                JOIN major_details m ON t.major_id = m.id 
                                JOIN course_details c ON t.course_id = c.id 
                                ORDER BY FIELD(day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), period")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Timetable</title>
    <link rel="stylesheet" href="css/attendance.css">
    <style>
        .form-container { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 20px; }
        .btn-save { background: #4f46e5; color: white; border: none; padding: 12px; border-radius: 5px; cursor: pointer; grid-column: span 2; font-weight: bold; font-size: 1rem; }
        .timetable-table { width: 100%; border-collapse: collapse; background: white; }
        .timetable-table th, .timetable-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .day-header { background: #f3f4f6; font-weight: bold; color: #4f46e5; }
    </style>
</head>
<body>
    <div class="container">
        <header class="attendance-header">
            <h1>📅 Manage <span style="color:#4f46e5">Timetable</span></h1>
            <a href="dashboard.php" class="class-btn" style="text-decoration:none; background:lightblue;">⬅ Back To Dashboard</a>
        </header>

        <form method="POST" class="form-container">
    <input type="hidden" name="timetable_id" value="<?= $edit_timetable['id'] ?? '' ?>">

    <div class="input-group">
        <label>Select Major</label>
        <select name="major_id" id="major_select" required onchange="fetchCourses(this.value)">
            <option value="">-- Choose Major --</option>
            <?php foreach ($majors as $m): ?>
                <option value="<?= $m['id'] ?>" <?= (isset($edit_timetable) && $edit_timetable['major_id'] == $m['id']) ? 'selected' : '' ?>>
                    <?= $m['title'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="input-group">
        <label>Select Course</label>
        <select name="course_id" id="course_select" required <?= !isset($edit_timetable) ? 'disabled' : '' ?>>
            <option value="">-- Select Course --</option>
            <?php if(isset($edit_timetable)): 
                // reterive course concerning major_id
                $stmt = $db->conn->prepare("SELECT c.id, c.title FROM course_details c JOIN course_assignments ca ON c.id = ca.course_id WHERE ca.major_id = ?");
                $stmt->execute([$edit_timetable['major_id']]);
                $ecourses = $stmt->fetchAll();
                foreach($ecourses as $ec): ?>
                    <option value="<?= $ec['id'] ?>" <?= ($edit_timetable['course_id'] == $ec['id']) ? 'selected' : '' ?>><?= $ec['title'] ?></option>
                <?php endforeach; 
            endif; ?>
        </select>
    </div>


    <div class="input-group">
    <label>Academic Year</label>
    <select name="academic_year" required>
        <?php 
        $ay_list = ["2024-2025", "2025-2026", "2026-2027"];
        foreach($ay_list as $ay): ?>
            <option value="<?= $ay ?>" <?= (isset($edit_timetable) && $edit_timetable['academic_year'] == $ay) ? 'selected' : '' ?>><?= $ay ?></option>
        <?php endforeach; ?>
    </select>
</div>

    <div class="input-group">
        <label>Day</label>
        <select name="day_of_week" required>
            <?php $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday ']; 
            foreach($days as $d): ?>
                <option value="<?= $d ?>" <?= (isset($edit_timetable) && $edit_timetable['day_of_week'] == $d) ? 'selected' : '' ?>><?= $d ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="input-group">
        <label>Period</label>
        <select name="period" required>
            <?php
            $periods = ["9-10[24h] AM", "10-11 AM", "11-12 AM", "1-2 PM", "2-3 PM", "3-4 PM"];
            foreach($periods as $i => $time): $p_val = $i+1; ?>
                <option value="<?= $p_val ?>" <?= (isset($edit_timetable) && $edit_timetable['period'] == $p_val) ? 'selected' : '' ?>>
                    Period <?= $p_val ?> (<?= $time ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <button type="submit" name="save_timetable" class="btn-save">
        <?= isset($edit_timetable) ? "💾 Update Timetable" : "➕ Add Class to Schedule" ?>
    </button>
    
    <?php if(isset($edit_timetable)): ?>
        <a href="manage_timetable.php" style="grid-column: span 2; text-align:center; color: #666; text-decoration:none;">Cancel Edit</a>
    <?php endif; ?>
</form>

        <table class="timetable-table">
    <thead>
        <tr class="day-header">
            <th>Day</th><th>Period</th><th>Major</th><th>Course</th><th>Time</th><th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($timetables as $t): ?>
            <tr>
                <td><?= $t['day_of_week'] ?></td>
                <td>P-<?= $t['period'] ?></td>
                <td><?= $t['major_name'] ?></td>
                <td><?= $t['course_name'] ?></td>
                <td><?= date("g:i A", strtotime($t['start_time'])) ?> - <?= date("g:i A", strtotime($t['end_time'])) ?></td>
                <td>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <a href="?edit=<?= $t['id'] ?>" class="btn-icon edit-btn" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </a>

                                    <span style="color: #ddd;">|</span>

                                    <a href="?delete=<?= $t['id'] ?>" class="btn-icon delete-btn" onclick="return confirm('Are you sure you want to delete this?')" title="Delete">
                                        <i class="fa-solid fa-trash-can"></i> Delete
                                    </a>
                                </div>
                            </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
    </div>

    <script>
    function fetchCourses(majorId) {
        const courseSelect = document.getElementById('course_select');
        courseSelect.innerHTML = '<option value="">Loading...</option>';
        courseSelect.disabled = true;

        if (!majorId) {
            courseSelect.innerHTML = '<option value="">-- First Select Major --</option>';
            return;
        }

        fetch(`manage_timetable.php?get_courses_by_major=${majorId}`)
            .then(response => response.json())
            .then(data => {
                courseSelect.innerHTML = '<option value="">-- Select Course --</option>';
                data.forEach(course => {
                    courseSelect.innerHTML += `<option value="${course.id}">${course.title}</option>`;
                });
                courseSelect.disabled = false;
            });
    }
    </script>

    <button onclick="topFunction()" id="scrollUpBtn" title="Go to top">↑</button>

<script>
// buttom
let mybutton = document.getElementById("scrollUpBtn");

// scroll page
window.onscroll = function() {scrollFunction()};

function scrollFunction() {
  if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
    mybutton.style.display = "block"; // show over 300px
  } else {
    mybutton.style.display = "none"; // hide under 300px
  }
}

// top function
function topFunction() {
  window.scrollTo({
    top: 0,
    behavior: 'smooth' 
  });
}
</script>
</body>
</html>