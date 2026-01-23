<?php
session_start();
require_once 'database/database.php';

if (empty($_SESSION["current_user"])) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$conn = $db->conn;

// add holiday logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_holiday'])) {
    $h_date = $_POST['holiday_date'];
    $desc = $_POST['description'];
    $ay = $_POST['academic_year']; 

    if (!empty($h_date) && !empty($desc)) {
        // check duplicate before insert
        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM holidays WHERE holiday_date = ?");
        $check_stmt->execute([$h_date]);
        $already_exists = $check_stmt->fetchColumn();

        if ($already_exists > 0) {
            // send duplicate message
            header("Location: holidays.php?msg=duplicate&date=$h_date");
            exit;
        } else {
            // insert new holiday
            $stmt = $conn->prepare("INSERT INTO holidays (holiday_date, description, academic_year) VALUES (?, ?, ?)");
            $stmt->execute([$h_date, $desc, $ay]);
            header("Location: holidays.php?msg=success");
            exit;
        }
    }
}

// delete holiday logic
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM holidays WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: holidays.php?msg=deleted");
    exit;
}

$holidays = $conn->query("SELECT * FROM holidays ORDER BY holiday_date DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Holidays</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/attendance.css"> </head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<body>

<div class="container">
    <header class="attendance-header">
        <div class="attendance-brand">
            <h1>⛱️ Manage <span style="color:var(--primary)">Holidays</span></h1>
        </div>
        <div class="back-btn-box">
            <a href="dashboard.php" class="class-btn" style="text-decoration:none; background:lightblue;">
                <i class="fa-solid fa-house"></i> Back to Dashboard
            </a>
        </div>
    </header>

    <div class="card">
        <span class="card-title">
            <i class="fa-solid fa-calendar-plus" style="color:var(--primary)"></i> Add New Holiday
        </span>
        <form method="POST" class="holiday-form">
            <div class="input-group">
                <label>Date</label>
                <input type="date" name="holiday_date" required>
            </div>

            <div class="input-group">
                <label>Occasion / Description</label>
                <input type="text" name="description" placeholder="e.g. Thingyan Festival" required>
            </div>

            <div class="input-group">
                <label>Academic Year</label>
                <input type="text" name="academic_year" value="<?= method_exists($db, 'getAcademicYear') ? $db->getAcademicYear() : '2025-2026' ?>" class="readonly-box" readonly>
            </div>

            <button type="submit" name="add_holiday" class="btn-add-holiday">
                <i class="fa-solid fa-plus"></i> Add
            </button>
        </form>
    </div>

    <div class="card">
        <span class="card-title">
            <i class="fa-solid fa-list-ul" style="color:var(--primary)"></i> Holiday Records
        </span>
        <div class="table-container">
            <table class="h-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Academic Year</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($holidays)): ?>
                        <tr>
                            <td colspan="4" style="text-align:center; color:var(--text-muted); padding: 40px;">
                                <i class="fa-regular fa-calendar-xmark" style="font-size: 2rem; display: block; margin-bottom: 10px;"></i>
                                No holidays recorded yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($holidays as $h): ?>
                            <tr>
                                <td style="font-weight: 600;"><?= date('d M Y', strtotime($h['holiday_date'])) ?></td>
                                <td><?= htmlspecialchars($h['description']) ?></td>
                                <td><span class="badge-ay"><?= $h['academic_year'] ?></span></td>
                                <td style="text-align: right;">
                                    <a href="?delete=<?= $h['id'] ?>" class="delete-btn btn-icon" onclick="return confirm('ဖျက်ရန် သေချာပါသလား?')">
                                        <i class="fa-solid fa-trash-can"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>



<script>
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    const date = urlParams.get('date');

    if (msg === 'duplicate') {
        Swal.fire({
            icon: 'warning',
            title: 'ရက်စွဲထပ်နေပါသည်!',
            text: `${date} ရက်နေ့သည် Holiday စာရင်းထဲတွင် ရှိနှင့်ပြီးသား ဖြစ်ပါသည်။`,
            confirmButtonColor: '#4f46e5'
        });
    } else if (msg === 'success') {
        Swal.fire({
            icon: 'success',
            title: 'အောင်မြင်ပါသည်',
            text: 'Holiday အသစ်ကို ထည့်သွင်းပြီးပါပြီ။',
            timer: 2000,
            showConfirmButton: false
        });
    } else if (msg === 'deleted') {
        Swal.fire({
            icon: 'success',
            title: 'ဖျက်ပြီးပါပြီ',
            timer: 1500,
            showConfirmButton: false
        });
    }
</script>

</body>
</html>