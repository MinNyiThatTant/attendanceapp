<?php
session_start();
require_once 'database/database.php';
$db = new Database();

$edit_course = null;
$assigned_majors = [];
$auto_ay = $db->getAcademicYear();

// --- academic_year သတ်မှတ်တဲ့ PHP Logic (Form ရဲ့ အပေါ်မှာ ထည့်ထားပါ) ---
$current_month = (int)date('m');
$current_year = (int)date('Y');
if ($current_month < 6) {
    $auto_ay = ($current_year - 1) . "-" . $current_year;
} else {
    $auto_ay = $current_year . "-" . ($current_year + 1);
}

// --- 1. DELETE LOGIC ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $db->conn->prepare("DELETE FROM course_assignments WHERE course_id = ?")->execute([$id]);
    $db->conn->prepare("DELETE FROM course_details WHERE id = ?")->execute([$id]);
    header("Location: manage_courses.php?msg=deleted");
    exit();
}

// --- 2. EDIT DATA FETCH ---
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $db->conn->prepare("SELECT * FROM course_details WHERE id = ?");
    $stmt->execute([$id]);
    $edit_course = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt_m = $db->conn->prepare("SELECT major_id FROM course_assignments WHERE course_id = ?");
    $stmt_m->execute([$id]);
    $assigned_majors = $stmt_m->fetchAll(PDO::FETCH_COLUMN);
}

// --- 3. ADD OR UPDATE LOGIC ---
if (isset($_POST['save_course'])) {
    $code = $_POST['code'];
    $title = $_POST['title'];
    $credits = $_POST['credits'];
    $session_id = $_POST['session_id'];
    $major_ids = $_POST['major_ids'] ?? [];
    $course_id = $_POST['course_id'];
    $academic_year = $_POST['academic_year']; 
    $total_classes = $_POST['total_classes'] ?? 45;

    try {
        $db->conn->beginTransaction();

        if ($course_id) {
            $sql = "UPDATE course_details SET code=?, title=?, credits=?, session_id=?, academic_year=?, total_classes=? WHERE id=?";
            $db->conn->prepare($sql)->execute([$code, $title, $credits, $session_id, $academic_year, $total_classes, $course_id]);
            $current_course_id = $course_id;
            $db->conn->prepare("DELETE FROM course_assignments WHERE course_id = ?")->execute([$current_course_id]);
        } else {
            $sql = "INSERT INTO course_details (code, title, credits, session_id, academic_year, total_classes) VALUES (?, ?, ?, ?, ?, ?)";
            $db->conn->prepare($sql)->execute([$code, $title, $credits, $session_id, $academic_year, $total_classes]);
            $current_course_id = $db->conn->lastInsertId();
        }

        if (!empty($major_ids)) {
            $assign_stmt = $db->conn->prepare("INSERT INTO course_assignments (course_id, major_id) VALUES (?, ?)");
            foreach ($major_ids as $m_id) {
                $assign_stmt->execute([$current_course_id, $m_id]);
            }
        }

        $db->conn->commit();
        header("Location: manage_courses.php?msg=success");
        exit();
    } catch (Exception $e) {
        $db->conn->rollBack();
        echo "Error: " . $e->getMessage();
    }
}

// --- 4. SEARCH & PAGINATION LOGIC ---
$search = $_GET['search'] ?? '';
$search_query = "";
$params = [];

if (!empty($search)) {
    $search_query = " WHERE cd.code LIKE ? OR cd.title LIKE ? OR cd.academic_year LIKE ? ";
    $params = ["%$search%", "%$search%", "%$search%"];
}

// Pagination parameters
$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Total count for pagination
$count_sql = "SELECT COUNT(*) FROM course_details cd $search_query";
$count_stmt = $db->conn->prepare($count_sql);
$count_stmt->execute($params);
$total_rows = $count_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// Fetch paginated results
$sql_courses = "SELECT cd.*, sd.term, GROUP_CONCAT(md.title SEPARATOR ', ') as major_names 
                FROM course_details cd 
                LEFT JOIN session_details sd ON cd.session_id = sd.id
                LEFT JOIN course_assignments ca ON cd.id = ca.course_id
                LEFT JOIN major_details md ON ca.major_id = md.id
                $search_query
                GROUP BY cd.id
                ORDER BY cd.academic_year DESC, sd.id ASC, cd.code ASC
                LIMIT $limit OFFSET $offset";

$stmt_courses = $db->conn->prepare($sql_courses);
$stmt_courses->execute($params);
$courses = $stmt_courses->fetchAll(PDO::FETCH_ASSOC);

$sessions = $db->conn->query("SELECT * FROM session_details")->fetchAll(PDO::FETCH_ASSOC);
$majors = $db->conn->query("SELECT * FROM major_details")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Courses</title>
    <link rel="stylesheet" href="css/attendance.css?v=1.2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .major-checkbox-group { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; background: #f9fafb; padding: 15px; border-radius: 8px; border: 1px solid #e5e7eb; }
        .checkbox-item { display: flex; align-items: center; gap: 8px; font-size: 0.9rem; cursor: pointer; }
        .pagination { display: flex; justify-content: center; gap: 8px; margin: 20px 0; padding-bottom: 20px; }
        .pagination a { padding: 8px 16px; border: 1px solid #4f46e5; text-decoration: none; color: #4f46e5; border-radius: 4px; transition: 0.3s; }
        .pagination a.active { background: #4f46e5; color: white; }
        .pagination a:hover:not(.active) { background: #eff6ff; }
        .input-label { font-weight: bold; display: block; margin-bottom: 5px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <header class="attendance-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h1>📚 Manage <span style="color:#4f46e5">Courses</span></h1>
            <div style="display: flex; gap: 10px;">
                <form method="GET" style="display: flex; gap: 5px;">
                    <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>" style="padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                    <button type="submit" class="class-btn">🔍</button>
                </form>
                <a href="dashboard.php" class="class-btn" style="text-decoration:none; background:lightblue;"><i class="fa-solid fa-house"></i> Back To Dashboard</a>
            </div>
        </header>

        <div class="card" style="margin-bottom: 25px;">
            <h3><?= $edit_course ? "📝 Edit Course" : "➕ Add New Course" ?></h3>
            <form method="POST">
                <input type="hidden" name="course_id" value="<?= $edit_course['id'] ?? '' ?>">
                
                <div style="display:grid; grid-template-columns: 1fr 2fr 1fr; gap:15px; margin-bottom:15px;">
                    <div>
                        <label class="input-label">Course Code</label>
                        <input type="text" name="code" required value="<?= $edit_course['code'] ?? '' ?>" style="width:100%; padding:8px;">
                    </div>
                    <div>
                        <label class="input-label">Course Title</label>
                        <input type="text" name="title" required value="<?= $edit_course['title'] ?? '' ?>" style="width:100%; padding:8px;">
                    </div>
                    <div>
                        <label class="input-label">Credits</label>
                        <input type="number" name="credits" required value="<?= $edit_course['credits'] ?? '' ?>" style="width:100%; padding:8px;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:15px; margin-bottom:15px;">
                    <div>
                        <label class="input-label">Semester (Term)</label>
                        <select name="session_id" required style="width:100%; padding:8px;">
                            <option value="">-- Select --</option>
                            <?php foreach ($sessions as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= (isset($edit_course) && $edit_course['session_id'] == $s['id']) ? 'selected' : '' ?>><?= $s['term'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
    <label class="input-label">Academic Year</label>
<select name="academic_year" required style="width:100%; padding:8px;">
    <?php 
    $current_ay = $db->getAcademicYear();
    $years = ["2024-2025", "2025-2026", "2026-2027", "2027-2028", "2028-2029", "2029-2030"];
    foreach($years as $y): ?>
        <option value="<?= $y ?>" <?= (isset($edit_course) && $edit_course['academic_year'] == $y) ? 'selected' : ($y == $current_ay ? 'selected' : '') ?>>
            <?= $y ?>
        </option>
    <?php endforeach; ?>
</select>
</div>
                    <div>
                        <label class="input-label">Total Classes</label>
                        <input type="number" name="total_classes" required value="<?= $edit_course['total_classes'] ?? '45' ?>" style="width:100%; padding:8px;">
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <label class="input-label">Assign to Majors:</label>
                    <label style="font-size: 13px; color: #4f46e5; cursor: pointer;">
                        <input type="checkbox" id="selectAllMajors"> Select All Majors
                    </label>
                    <div class="major-checkbox-group" style="margin-top:10px;">
                        <?php foreach ($majors as $m): ?>
                            <label class="checkbox-item">
                                <input type="checkbox" name="major_ids[]" class="major-checkbox" value="<?= $m['id'] ?>"
                                    <?= in_array($m['id'], $assigned_majors) ? 'checked' : '' ?>>
                                <?= $m['title'] ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" name="save_course" class="save-btn" style="padding: 10px 30px;">
                    <?= $edit_course ? "Update Course" : "Save Course" ?>
                </button>
                <?php if($edit_course): ?>
                    <a href="manage_courses.php" style="margin-left:10px; color:#666; text-decoration:none;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <table class="student-table">
                <thead>
                    <tr>
                        <th width="25%">Majors</th>
                        <th>Code</th>
                        <th>Title</th>
                        <th>Year</th>
                        <th>Sem</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($courses)): ?>
                        <tr><td colspan="6" style="text-align:center;">No courses found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($courses as $c): ?>
                        <tr>
                            <td><small><?= $c['major_names'] ?: '<span style="color:red;">Not assigned</span>' ?></small></td>
                            <td><strong><?= htmlspecialchars($c['code']) ?></strong></td>
                            <td><?= htmlspecialchars($c['title']) ?></td>
                            <td><?= $c['academic_year'] ?></td>
                            <td><span class="badge"><?= htmlspecialchars($c['term']) ?></span></td>
                            <td>
                                <div style="display:flex; gap:10px;">
                                    <a href="?edit=<?= $c['id'] ?>" class="btn-icon edit-btn" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                    <a href="?delete=<?= $c['id'] ?>" class="btn-icon delete-btn" onclick="return confirm('Are you sure?')" title="Delete"><i class="fa-solid fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">« Prev</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="<?= ($page == $i) ? 'active' : '' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>

    <?php if ($page < $total_pages): ?>
        <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Next »</a>
    <?php endif; ?>
</div>
<?php endif; ?>
        </div>
    </div>

    <script>
        // Select All Checkboxes
        document.getElementById('selectAllMajors').addEventListener('change', function() {
            document.querySelectorAll('.major-checkbox').forEach(cb => cb.checked = this.checked);
        });
    </script>
</body>
</html>