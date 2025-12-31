<?php
session_start();
require_once 'database/database.php';

if (empty($_SESSION["current_user"])) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$conn = $db->conn;

// ၁။ Holiday အသစ်ထည့်ခြင်း Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_holiday'])) {
    $h_date = $_POST['holiday_date'];
    $desc = $_POST['description'];
    $ay = $_POST['academic_year']; // လက်ရှိ Academic Year

    if (!empty($h_date) && !empty($desc)) {
        $stmt = $conn->prepare("INSERT INTO holidays (holiday_date, description, academic_year) VALUES (?, ?, ?)");
        $stmt->execute([$h_date, $desc, $ay]);
        $success_msg = "Holiday added successfully!";
    }
}

// ၂။ Holiday ပြန်ဖျက်ခြင်း Logic
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM holidays WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: holidays.php");
    exit;
}

// ၃။ Holiday စာရင်းကို ပြန်ခေါ်ခြင်း
$holidays = $conn->query("SELECT * FROM holidays ORDER BY holiday_date DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Holidays</title>
    <link rel="stylesheet" href="css/attendance.css"> <style>
        .h-container { max-width: 900px; margin: 30px auto; padding: 20px; }
        .card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .h-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .h-table th, .h-table td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        .h-table th { background: #4f46e5; color: white; }
        .btn-del { color: #ef4444; font-weight: bold; text-decoration: none; }
        .btn-add { background: #4f46e5; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>

<div class="h-container">
    <header class="attendance-header">
            <h1>🎉 Holiday <span style="color:#4f46e5">Management</span></h1>
            <a href="dashboard.php" class="class-btn" style="text-decoration:none; background:lightblue;">⬅ Back To Dashboard</a>
        </header>

    <div class="card">
        <h3>Add New Holiday</h3>
        <form method="POST" style="display: flex; gap: 10px; align-items: flex-end;">
            <div style="flex: 1;">
                <label>Date</label>
                <input type="date" name="holiday_date" required style="width: 100%; padding: 8px; margin-top: 5px;">
            </div>
            <div style="flex: 2;">
                <label>Occasion / Description</label>
                <input type="text" name="description" placeholder="e.g. Myanmar New Year" required style="width: 100%; padding: 8px; margin-top: 5px;">
            </div>
            <div style="flex: 1;">
                <label>Academic Year</label>
                <input type="text" name="academic_year" value="2025-2026" required style="width: 100%; padding: 8px; margin-top: 5px;">
            </div>
            <button type="submit" name="add_holiday" class="btn-add">Add</button>
        </form>
    </div>

    <div class="card">
        <h3>Existing Holidays</h3>
        <table class="h-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>AY</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($holidays)): ?>
                    <tr><td colspan="4" style="text-align:center;">No holidays found.</td></tr>
                <?php else: ?>
                    <?php foreach ($holidays as $h): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($h['holiday_date'])) ?></td>
                            <td><?= htmlspecialchars($h['description']) ?></td>
                            <td><?= $h['academic_year'] ?></td>
                            <td>
                                <a href="?delete=<?= $h['id'] ?>" class="btn-del" onclick="return confirm('Delete this holiday?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>