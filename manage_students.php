<?php
session_start();
require_once 'database/database.php';
$db = new Database();

$academic_years = ["2024-2025", "2025-2026", "2026-2027", "2027-2028", "2028-2029", "2029-2030"];
$edit_student = null;

// PAGINATION SETTINGS 
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// CSV IMPORT LOGIC 
if (isset($_POST['import_csv'])) {
    $filename = $_FILES["student_file"]["tmp_name"];
    if ($_FILES["student_file"]["size"] > 0) {
        $file = fopen($filename, "r");
        fgetcsv($file); 
        $stmt = $db->conn->prepare("INSERT INTO student_details (roll_no, name, major_id, current_semester, academic_year, photo) VALUES (?, ?, ?, ?, ?, 'default.png')");
        while (($column = fgetcsv($file, 1000, ",")) !== FALSE) {
            if(count($column) >= 5) {
                $stmt->execute([$column[0], $column[1], $column[2], $column[3], $column[4]]);
            }
        }
        fclose($file);
        header("Location: manage_students.php?msg=imported");
        exit();
    }
}

// DELETE LOGIC 
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $db->conn->prepare("DELETE FROM student_details WHERE id = ?")->execute([$id]);
    header("Location: manage_students.php?msg=deleted");
    exit();
}

// FETCH FOR EDIT 
if (isset($_GET['edit'])) {
    $stmt = $db->conn->prepare("SELECT * FROM student_details WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_student = $stmt->fetch(PDO::FETCH_ASSOC);
}

// SAVE (ADD OR UPDATE) LOGIC 
if (isset($_POST['save_student'])) {
    $roll = $_POST['roll_no'];
    $name = $_POST['name'];
    $major_id = $_POST['major_id'];
    $current_semester = $_POST['current_semester'];
    $academic_year = $_POST['academic_year'];
    $rfid_uid = $_POST['rfid_uid'];
    $student_id = $_POST['student_id'] ?? null;
    $photo_name = $_POST['old_photo'] ?? "default.png"; 

    // Photo Upload Handling
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "assets/img/students/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

        $extension = pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION);
        $photo_name = "ST_" . time() . "." . $extension; 
        move_uploaded_file($_FILES["photo"]["tmp_name"], $target_dir . $photo_name);
    }

    if (!empty($student_id)) {
        // UPDATE 
        $sql = "UPDATE student_details SET roll_no=?, name=?, major_id=?, current_semester=?, academic_year=?, rfid_uid=?, photo=? WHERE id=?";
        $db->conn->prepare($sql)->execute([$roll, $name, $major_id, $current_semester, $academic_year, $rfid_uid, $photo_name, $student_id]);
        $msg = "updated";
    } else {
        // INSERT 
        $sql = "INSERT INTO student_details (roll_no, name, major_id, current_semester, academic_year, rfid_uid, photo) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $db->conn->prepare($sql)->execute([$roll, $name, $major_id, $current_semester, $academic_year, $rfid_uid, $photo_name]);
        $msg = "success";
    }
    header("Location: manage_students.php?msg=$msg");
    exit();
}

// SEARCH & DATA FETCHING
$search = $_GET['search'] ?? '';
$search_query = "";
if ($search) {
    $search_query = " WHERE sd.name LIKE :s OR sd.roll_no LIKE :s OR sd.rfid_uid LIKE :s";
}

$count_stmt = $db->conn->prepare("SELECT COUNT(*) FROM student_details sd $search_query");
if ($search) $count_stmt->bindValue(':s', "%$search%");
$count_stmt->execute();
$total_rows = $count_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

$query = "SELECT sd.*, md.title as major_name 
          FROM student_details sd 
          LEFT JOIN major_details md ON sd.major_id = md.id 
          $search_query 
          ORDER BY sd.id DESC 
          LIMIT $limit OFFSET $offset";

$stmt = $db->conn->prepare($query);
if ($search) $stmt->bindValue(':s', "%$search%");
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$majors = $db->conn->query("SELECT * FROM major_details")->fetchAll(PDO::FETCH_ASSOC);
$semesters = $db->conn->query("SELECT DISTINCT term FROM session_details")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students | Attendance System</title>
    <link rel="stylesheet" href="css/attendance.css">
    <style>
        .rfid-badge { background: #f3f4f6; color: #374151; padding: 4px 8px; border-radius: 4px; font-family: monospace; font-size: 0.85rem; border: 1px solid #d1d5db; }
        .input-box { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .pagination { display: flex; justify-content: center; gap: 5px; margin-top: 20px; }
        .page-item { padding: 8px 12px; border: 1px solid #4f46e5; text-decoration: none; color: #4f46e5; border-radius: 4px; }
        .page-item.active { background: #4f46e5; color: white; }
        .thumb-img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="container">
        <header class="attendance-header">
            <h1>Manage <span style="color:#4f46e5">Students</span></h1>
            <div class="search-group" style="display:flex; gap:10px; align-items:center;">
                <form method="GET" style="display:flex; gap:5px;">
                    <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>" class="input-box" style="margin:0; width:180px;">
                    <button type="submit" class="class-btn">🔍</button>
                </form>
                <a href="dashboard.php" class="class-btn" style="background:lightblue; text-decoration:none;">⬅ Back To Dashboard</a>
            </div>
        </header>

        <div class="card" style="margin-bottom:20px; border: 2px dashed #4f46e5; background: #f5f3ff;">
            <h3 style="margin-top:0;">📤 Quick Import (CSV)</h3>
            <form method="POST" enctype="multipart/form-data" style="display:flex; align-items:center; gap:15px;">
                <input type="file" name="student_file" accept=".csv" required>
                <button type="submit" name="import_csv" class="save-btn">Upload CSV</button>
            </form>
        </div>

        <div class="card">
            <h3><?= $edit_student ? '📝 Edit Student' : '➕ Add New Student' ?></h3>
            <form method="POST" enctype="multipart/form-data" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:15px;">
                <input type="hidden" name="student_id" value="<?= $edit_student['id'] ?? '' ?>">
                <input type="hidden" name="old_photo" value="<?= $edit_student['photo'] ?? 'default.png' ?>">
                
                <div>
                    <label>Student Photo:</label>
                    <input type="file" name="photo" class="input-box" accept="image/*">
                </div>
                <div>
                    <label>Roll No:</label>
                    <input type="text" name="roll_no" class="input-box" value="<?= $edit_student['roll_no'] ?? '' ?>" required>
                </div>
                <div>
                    <label>Full Name:</label>
                    <input type="text" name="name" class="input-box" value="<?= $edit_student['name'] ?? '' ?>" required>
                </div>
                <div>
                    <label>Major:</label>
                    <select name="major_id" class="input-box" required>
                        <option value="">-- Select Major --</option>
                        <?php foreach ($majors as $m): ?>
                            <option value="<?= $m['id'] ?>" <?= (isset($edit_student) && $edit_student['major_id'] == $m['id']) ? 'selected' : '' ?>><?= $m['title'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Semester:</label>
                    <select name="current_semester" class="input-box" required>
                        <?php foreach ($semesters as $sem): ?>
                            <option value="<?= $sem['term'] ?>" <?= (isset($edit_student) && $edit_student['current_semester'] == $sem['term']) ? 'selected' : '' ?>><?= $sem['term'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Academic Year:</label>
                    <select name="academic_year" class="input-box" required>
                        <?php foreach ($academic_years as $ay): ?>
                            <option value="<?= $ay ?>" <?= (isset($edit_student) && $edit_student['academic_year'] == $ay) ? 'selected' : '' ?>><?= $ay ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>RFID Card ID:</label>
                    <input type="text" name="rfid_uid" class="input-box" value="<?= $edit_student['rfid_uid'] ?? '' ?>">
                </div>

                <div style="grid-column: 1 / -1; display: flex; justify-content: flex-end; gap: 10px;">
                    <?php if ($edit_student): ?>
                        <a href="manage_students.php" class="class-btn" style="background:#eee; color:#333; text-decoration:none;">Cancel</a>
                    <?php endif; ?>
                    <button type="submit" name="save_student" class="save-btn">
                        <?= $edit_student ? 'Update Student' : 'Register Student' ?>
                    </button>
                </div>
            </form>
        </div>

        <div class="card" style="margin-top:20px;">
            <table class="student-table" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Roll No</th>
                        <th>Name</th>
                        <th>Major</th>
                        <th>Academic Year</th>
                        <th>RFID UID</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $s): ?>
                        <tr>
                            <td><img src="assets/img/students/<?= $s['photo'] ?>" class="thumb-img"></td>
                            <td><?= htmlspecialchars($s['roll_no']) ?></td>
                            <td><?= htmlspecialchars($s['name']) ?></td>
                            <td><?= htmlspecialchars($s['major_name']) ?></td>
                            <td><?= htmlspecialchars($s['academic_year']) ?></td>
                            <td><span class="rfid-badge"><?= $s['rfid_uid'] ?: 'Not Set' ?></span></td>
                            <td>
                                <a href="?edit=<?= $s['id'] ?>&page=<?= $page ?>" style="color:blue;">Edit</a> |
                                <a href="?delete=<?= $s['id'] ?>" style="color:red;" onclick="return confirm('Confirm delete?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="page-item <?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <button onclick="topFunction()" id="scrollUpBtn" title="Go to top">↑</button>

<script>
let mybutton = document.getElementById("scrollUpBtn");

window.onscroll = function() {scrollFunction()};

function scrollFunction() {
  if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
    mybutton.style.display = "block"; 
  } else {
    mybutton.style.display = "none"; 
  }
}

function topFunction() {
  window.scrollTo({
    top: 0,
    behavior: 'smooth' 
  });
}
</script>
</body>
</html>