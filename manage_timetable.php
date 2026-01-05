<?php
session_start();
require_once 'database/database.php';
$db = new Database();

// FETCH COURSES BASED ON MAJOR (AJAX)
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

// SAVE / UPDATE LOGIC (Multiple Periods Support)
if (isset($_POST['save_timetable'])) {
    $major_id = $_POST['major_id'];
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
        1 => ['09:00:00', '09:59:59'], 2 => ['10:00:00', '10:59:59'],
        3 => ['11:00:00', '11:59:59'], 4 => ['13:00:00', '13:59:59'],
        5 => ['14:00:00', '14:59:59'], 6 => ['15:00:00', '16:00:59']
    ];

    try {
        $db->conn->beginTransaction();

        // Edit Mode - Delete existing record first
        if ($timetable_id) {
            $db->conn->prepare("DELETE FROM timetable WHERE id = ?")->execute([$timetable_id]);
        }

        foreach ($periods as $p) {
            // Duplicate Check: avoid same major, day, period, academic year
            $check = $db->conn->prepare("SELECT id FROM timetable WHERE major_id=? AND day_of_week=? AND period=? AND academic_year=?");
            $check->execute([$major_id, $day, $p, $academic_year]);
            
            if ($check->fetch()) {
                $db->conn->rollBack();
                header("Location: manage_timetable.php?msg=duplicate&day=$day&period=$p");
                exit();
            }

            $start = $times[$p][0];
            $end = $times[$p][1];
            
            $sql = "INSERT INTO timetable (major_id, course_id, day_of_week, period, start_time, end_time, academic_year) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $db->conn->prepare($sql)->execute([$major_id, $course_id, $day, $p, $start, $end, $academic_year]);
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
        .form-container { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 20px; }
        .input-group label { font-weight: bold; margin-bottom: 5px; display: block; }
        .period-box-group { display: flex; gap: 12px; flex-wrap: wrap; background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px dashed #cbd5e1; grid-column: span 2; }
        .period-item { display: flex; align-items: center; gap: 6px; cursor: pointer; padding: 5px 10px; background: white; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.9rem; }
        .period-item:hover { background: #f1f5f9; }
        .btn-save { background: #4f46e5; color: white; border: none; padding: 12px; border-radius: 5px; cursor: pointer; grid-column: span 2; font-weight: bold; }
        .timetable-table { width: 100%; border-collapse: collapse; background: white; margin-top: 10px; }
        .timetable-table th, .timetable-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .day-header { background: #f3f4f6; color: #4f46e5; }
        #scrollUpBtn { position: fixed; bottom: 20px; right: 30px; display: none; background-color: #4f46e5; color: white; border: none; padding: 10px 15px; border-radius: 50%; cursor: pointer; }
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
                        $stmt = $db->conn->prepare("SELECT c.id, c.title FROM course_details c JOIN course_assignments ca ON c.id = ca.course_id WHERE ca.major_id = ?");
                        $stmt->execute([$edit_timetable['major_id']]);
                        foreach($stmt->fetchAll() as $ec): ?>
                            <option value="<?= $ec['id'] ?>" <?= ($edit_timetable['course_id'] == $ec['id']) ? 'selected' : '' ?>><?= $ec['title'] ?></option>
                        <?php endforeach; 
                    endif; ?>
                </select>
            </div>

            <div class="input-group">
                <label>Academic Year</label>
                <select name="academic_year" required>
                    <?php 
                    $ay_list = ["2024-2025", "2025-2026", "2026-2027", "2027-2028", "2028-2029", "2029-2030"];
                    foreach($ay_list as $ay): ?>
                        <option value="<?= $ay ?>" <?= (isset($edit_timetable) && $edit_timetable['academic_year'] == $ay) ? 'selected' : '' ?>><?= $ay ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="input-group">
                <label>Day</label>
                <select name="day_of_week" required>
                    <?php $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']; 
                    foreach($days as $d): ?>
                        <option value="<?= $d ?>" <?= (isset($edit_timetable) && $edit_timetable['day_of_week'] == $d) ? 'selected' : '' ?>><?= $d ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="period-box-group">
                <label style="width: 100%; margin-bottom: 5px;">Select Period(s):</label>
                <?php
                $p_labels = ["9-10 AM", "10-11 AM", "11-12 AM", "1-2 PM", "2-3 PM", "3-4 PM"];
                foreach($p_labels as $i => $label): $p_val = $i+1; ?>
                    <label class="period-item">
                        <input type="checkbox" name="periods[]" value="<?= $p_val ?>" 
                            <?= (isset($edit_timetable) && $edit_timetable['period'] == $p_val) ? 'checked' : '' ?>>
                        P-<?= $p_val ?> (<?= $label ?>)
                    </label>
                <?php endforeach; ?>
            </div>

            <button type="submit" name="save_timetable" class="btn-save">
                <?= isset($edit_timetable) ? "💾 Update Schedule" : "➕ Add to Schedule" ?>
            </button>
            <?php if(isset($edit_timetable)): ?>
                <a href="manage_timetable.php" style="grid-column: span 2; text-align:center; color:#666; text-decoration:none;">Cancel Edit</a>
            <?php endif; ?>
        </form>

        <div class="card">
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
                            <td><strong>P-<?= $t['period'] ?></strong></td>
                            <td><?= $t['major_name'] ?></td>
                            <td><?= $t['course_name'] ?></td>
                            <td><small><?= date("g:i A", strtotime($t['start_time'])) ?> - <?= date("g:i A", strtotime($t['end_time'])) ?></small></td>
                            <td>
                                <a href="?edit=<?= $t['id'] ?>" class="btn-icon edit-btn" style="color:blue;"><i class="fa-solid fa-pen"></i></a> |
                                <a href="?delete=<?= $t['id'] ?>" class="btn-icon delete-btn" style="color:red;" onclick="return confirm('Delete this record?')"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <button onclick="topFunction()" id="scrollUpBtn" title="Go to top">↑</button>

    <script>
    function fetchCourses(majorId) {
        const courseSelect = document.getElementById('course_select');
        if (!majorId) { courseSelect.innerHTML = '<option value="">-- First Select Major --</option>'; return; }
        
        courseSelect.innerHTML = '<option value="">Loading...</option>';
        fetch(`manage_timetable.php?get_courses_by_major=${majorId}`)
            .then(r => r.json())
            .then(data => {
                courseSelect.innerHTML = '<option value="">-- Select Course --</option>';
                data.forEach(c => { courseSelect.innerHTML += `<option value="${c.id}">${c.title}</option>`; });
                courseSelect.disabled = false;
            });
    }

    // Scroll Button Logic
    let mybutton = document.getElementById("scrollUpBtn");
    window.onscroll = function() { if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) { mybutton.style.display = "block"; } else { mybutton.style.display = "none"; } };
    function topFunction() { window.scrollTo({top: 0, behavior: 'smooth'}); }

    // SweetAlert Messages
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    if (msg === 'duplicate') {
        Swal.fire({ icon: 'error', title: 'ထပ်နေပါသည်!', text: `${urlParams.get('day')} နေ့ Period (${urlParams.get('period')}) တွင် အတန်းရှိပြီးသားပါ။` });
    } else if (msg === 'success') {
        Swal.fire({ icon: 'success', title: 'အောင်မြင်သည်', text: 'အချိန်ဇယား သိမ်းဆည်းပြီးပါပြီ။', timer: 1500, showConfirmButton: false });
    } else if (msg === 'no_period') {
        Swal.fire({ icon: 'warning', title: 'သတိပေးချက်', text: 'အနည်းဆုံး Period တစ်ခု ရွေးချယ်ပေးပါ။' });
    } else if (msg === 'deleted') {
        Swal.fire({ icon: 'info', title: 'ဖျက်ပြီးပါပြီ', timer: 1500, showConfirmButton: false });
    } else if (msg === 'updated') {
        Swal.fire({ icon: 'success', title: 'ပြင်ဆင်ပြီးပါပြီ', timer: 1500, showConfirmButton: false });
    }
    </script>
</body>
</html>