<?php
session_start();
require_once __DIR__ . '/database/database.php';
$db = new Database();

// Search logic
$search = $_GET['search'] ?? '';
$where_clause = "";
if ($search) {
    $where_clause = " WHERE sd.name LIKE :s OR sd.roll_no LIKE :s ";
}

$sql = "SELECT cul.*, sd.name, sd.roll_no 
        FROM computer_usage_logs cul
        JOIN student_details sd ON cul.student_id = sd.id
        $where_clause
        ORDER BY cul.usage_date DESC, cul.check_in_time DESC";

$stmt = $db->conn->prepare($sql);
if ($search) $stmt->bindValue(':s', "%$search%");
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// အသုံးပြုချိန် ကြာမြင့်မှုကို တွက်ချက်ပေးမည့် Function
function getDuration($start, $end) {
    if (!$end) return "<span style='color:orange;'>Still in Lab</span>";
    $start_time = new DateTime($start);
    $end_time = new DateTime($end);
    $interval = $start_time->diff($end_time);
    return $interval->format('%h hr %i min');
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Computer Usage Report</title>
    <link rel="stylesheet" href="css/attendance.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="container">
        <header class="attendance-header">
            <h1>🖥️ Computer Lab <span style="color:#4f46e5">Usage Report</span></h1>
            <div style="display:flex; gap:10px;">
                <form method="GET" style="display:flex; gap:5px;">
                    <input type="text" name="search" placeholder="Search Student..." value="<?= htmlspecialchars($search) ?>" style="padding:10px; border-radius:5px; border:1px solid #ddd;">
                    <button type="submit" class="class-btn">Search</button>
                </form>
                <a href="dashboard.php" class="class-btn" style="background:#666; text-decoration:none;">Back</a>
            </div>
        </header>

        <div class="card">
            <table class="student-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Roll No</th>
                        <th>Student Name</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Duration</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($logs) > 0): ?>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= date('d-M-Y', strtotime($log['usage_date'])) ?></td>
                            <td><?= htmlspecialchars($log['roll_no']) ?></td>
                            <td><?= htmlspecialchars($log['name']) ?></td>
                            <td><?= date('h:i A', strtotime($log['check_in_time'])) ?></td>
                            <td><?= $log['check_out_time'] ? date('h:i A', strtotime($log['check_out_time'])) : '-' ?></td>
                            <td style="font-weight:bold; color:#4f46e5;">
                                <?= getDuration($log['check_in_time'], $log['check_out_time']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center;">No records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>