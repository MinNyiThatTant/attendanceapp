<?php
session_start();
require_once 'database/database.php';
$db = new Database();

// FETCH COURSES BASED ON MAJOR AND SEMESTER (AJAX)
if (isset($_GET['get_courses_ajax'])) {
    $m_id = $_GET['major_id'];
    $sem_num = $_GET['semester']; // 1, 2, 3...

    // change semester number to term string
    $suffixes = [1 => "1st", 2 => "2nd", 3 => "3rd", 4 => "4th", 5 => "5th", 6 => "6th", 7 => "7th", 8 => "8th", 9 => "9th", 10 => "10th"];
    $prefix = $suffixes[$sem_num] ?? $sem_num . "th";
    $term_string = $prefix . " semester"; // "1st semester" 

    $stmt = $db->conn->prepare("SELECT c.id, c.title FROM course_details c 
                                JOIN course_assignments ca ON c.id = ca.course_id 
                                JOIN session_details sd ON c.session_id = sd.id
                                WHERE ca.major_id = ? AND sd.term = ?");
    $stmt->execute([$m_id, $term_string]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result);
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
    $semester = $_POST['semester'];
    $course_id = $_POST['course_id'];
    $day = $_POST['day_of_week'];
    $academic_year = $_POST['academic_year'];
    $periods = $_POST['periods'] ?? [];
    $timetable_id = $_POST['timetable_id'] ?? null;

    if (empty($periods)) {
        header("Location: manage_timetable.php?msg=no_period");
        exit();
    }

    $times = [
        1 => ['09:00:00', '09:59:59'],
        2 => ['10:00:00', '10:59:59'],
        3 => ['11:00:00', '11:59:59'],
        4 => ['13:00:00', '13:59:59'],
        5 => ['14:00:00', '14:59:59'],
        6 => ['15:00:00', '16:00:59']
    ];

    try {
        $db->conn->beginTransaction();

        if ($timetable_id) {
            $db->conn->prepare("DELETE FROM timetable WHERE id = ?")->execute([$timetable_id]);
        }

        foreach ($periods as $p) {
            $check = $db->conn->prepare("SELECT id FROM timetable WHERE major_id=? AND semester=? AND day_of_week=? AND period=? AND academic_year=?");
            $check->execute([$major_id, $semester, $day, $p, $academic_year]);

            if ($check->fetch()) {
                $db->conn->rollBack();
                header("Location: manage_timetable.php?msg=duplicate&day=$day&period=$p");
                exit();
            }

            $start = $times[$p][0];
            $end = $times[$p][1];

            $sql = "INSERT INTO timetable (major_id, semester, course_id, day_of_week, period, start_time, end_time, academic_year) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $db->conn->prepare($sql)->execute([$major_id, $semester, $course_id, $day, $p, $start, $end, $academic_year]);
        }

        $db->conn->commit();
        header("Location: manage_timetable.php?msg=" . ($timetable_id ? "updated" : "success"));
    } catch (Exception $e) {
        $db->conn->rollBack();
        die("System Error: " . $e->getMessage());
    }
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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Timetable</title>
    <link rel="stylesheet" href="css/attendance.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .form-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }

        .input-group label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }

        .period-box-group {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            background: #f8fafc;
            padding: 15px;
            border-radius: 6px;
            border: 1px dashed #cbd5e1;
            grid-column: span 2;
        }

        .period-item {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            padding: 5px 10px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .btn-save {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 5px;
            cursor: pointer;
            grid-column: span 2;
            font-weight: bold;
        }

        .timetable-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-top: 10px;
        }

        .timetable-table th,
        .timetable-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        .day-header {
            background: #f3f4f6;
            color: #4f46e5;
        }

        #scrollUpBtn {
            position: fixed;
            bottom: 20px;
            right: 30px;
            display: none;
            background-color: #4f46e5;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 50%;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="container">
        <header class="attendance-header">
            <h1>📅 Manage <span style="color:#4f46e5">Timetable</span></h1>
            <a href="dashboard.php" class="class-btn" style="text-decoration:none; background:lightblue;"><i class="fa-solid fa-house"></i> Back To Dashboard</a>
        </header>

        <form method="POST" class="form-container">
            <input type="hidden" name="timetable_id" value="<?= $edit_timetable['id'] ?? '' ?>">

            <div class="input-group">
                <label>Select Major</label>
                <select name="major_id" id="major_select" required onchange="fetchCourses()">
                    <option value="">-- Choose Major --</option>
                    <?php foreach ($majors as $m): ?>
                        <option value="<?= $m['id'] ?>" <?= (isset($edit_timetable) && $edit_timetable['major_id'] == $m['id']) ? 'selected' : '' ?>>
                            <?= $m['title'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="input-group">
                <label>Select Semester</label>
                <select name="semester" id="semester_select" required onchange="fetchCourses()">
                    <option value="">-- Choose Semester --</option>
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                        <option value="<?= $i ?>" <?= (isset($edit_timetable) && $edit_timetable['semester'] == $i) ? 'selected' : '' ?>>
                            Semester <?= $i ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="input-group">
                <label>Select Course</label>
                <select name="course_id" id="course_select" required <?= !isset($edit_timetable) ? 'disabled' : '' ?>>
                    <option value="">-- Select Course --</option>
                    <?php if (isset($edit_timetable)):
                        // Edit Mode 
                        $s_num = $edit_timetable['semester'];
                        $suffixes = [1 => "1st", 2 => "2nd", 3 => "3rd", 4 => "4th", 5 => "5th", 6 => "6th", 7 => "7th", 8 => "8th", 9 => "9th", 10 => "10th"];
                        $t_str = ($suffixes[$s_num] ?? $s_num . "th") . " semester";

                        $stmt = $db->conn->prepare("SELECT c.id, c.title FROM course_details c 
                                        JOIN course_assignments ca ON c.id = ca.course_id 
                                        JOIN session_details sd ON c.session_id = sd.id 
                                        WHERE ca.major_id = ? AND sd.term = ?");
                        $stmt->execute([$edit_timetable['major_id'], $t_str]);
                        foreach ($stmt->fetchAll() as $ec): ?>
                            <option value="<?= $ec['id'] ?>" <?= ($edit_timetable['course_id'] == $ec['id']) ? 'selected' : '' ?>><?= $ec['title'] ?></option>
                    <?php endforeach;
                    endif; ?>
                </select>
            </div>

            <div class="input-group">
                <label>Academic Year</label>
                <select name="academic_year" required>
                    <?php $ay_list = ["2025-2026", "2026-2027", "2027-2028"];
                    foreach ($ay_list as $ay): ?>
                        <option value="<?= $ay ?>" <?= (isset($edit_timetable) && $edit_timetable['academic_year'] == $ay) ? 'selected' : '' ?>><?= $ay ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="input-group">
                <label>Day</label>
                <select name="day_of_week" required>
                    <?php $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                    foreach ($days as $d): ?>
                        <option value="<?= $d ?>" <?= (isset($edit_timetable) && $edit_timetable['day_of_week'] == $d) ? 'selected' : '' ?>><?= $d ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="period-box-group">
                <label style="width: 100%;">Select Period(s):</label>
                <?php $p_labels = ["9-10 AM", "10-11 AM", "11-12 AM", "1-2 PM", "2-3 PM", "3-4 PM"];
                foreach ($p_labels as $i => $label): $p_val = $i + 1; ?>
                    <label class="period-item">
                        <input type="checkbox" name="periods[]" value="<?= $p_val ?>" <?= (isset($edit_timetable) && $edit_timetable['period'] == $p_val) ? 'checked' : '' ?>>
                        P-<?= $p_val ?> (<?= $label ?>)
                    </label>
                <?php endforeach; ?>
            </div>

            <button type="submit" name="save_timetable" class="btn-save">
                <?= isset($edit_timetable) ? "💾 Update Schedule" : "Add to Schedule" ?>
            </button>
        </form>

        <table class="timetable-table">
            <thead>
                <tr class="day-header">
                    <th>Day</th>
                    <th>Period</th>
                    <th>Major</th>
                    <th>Sem</th>
                    <th>Course</th>
                    <th>Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($timetables as $t): ?>
                    <tr>
                        <td><?= $t['day_of_week'] ?></td>
                        <td><strong>P-<?= $t['period'] ?></strong></td>
                        <td><?= $t['major_name'] ?></td>
                        <td>Sem-<?= $t['semester'] ?></td>
                        <td><?= $t['course_name'] ?></td>
                        <td><small><?= date("g:i A", strtotime($t['start_time'])) ?> - <?= date("g:i A", strtotime($t['end_time'])) ?></small></td>
                        <td>
                            <a href="?edit=<?= $t['id'] ?>" style="color:blue;"><i class="fa-solid fa-pen"></i></a> |
                            <a href="?delete=<?= $t['id'] ?>" style="color:red;" onclick="return confirm('Delete?')"><i class="fa-solid fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function fetchCourses() {
            const majorId = document.getElementById('major_select').value;
            const semester = document.getElementById('semester_select').value;
            const courseSelect = document.getElementById('course_select');

            if (!majorId || !semester) {
                courseSelect.innerHTML = '<option value="">-- First Select Major & Sem --</option>';
                courseSelect.disabled = true;
                return;
            }

            courseSelect.innerHTML = '<option value="">Loading...</option>';
            fetch(`manage_timetable.php?get_courses_ajax=1&major_id=${majorId}&semester=${semester}`)
                .then(r => r.json())
                .then(data => {
                    courseSelect.innerHTML = '<option value="">-- Select Course --</option>';
                    data.forEach(c => {
                        courseSelect.innerHTML += `<option value="${c.id}">${c.title}</option>`;
                    });
                    courseSelect.disabled = false;
                });
        }

        // SweetAlert messages based on URL params (same as your previous logic)
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('msg')) {
            const msg = urlParams.get('msg');
            let icon = 'success',
                title = 'Success';
            if (msg === 'duplicate') {
                icon = 'error';
                title = 'Duplicate Entry!';
            }
            Swal.fire({
                icon: icon,
                title: title,
                timer: 1500,
                showConfirmButton: false
            });
        }
    </script>
</body>

</html>