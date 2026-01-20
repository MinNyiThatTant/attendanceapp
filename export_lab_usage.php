<?php
require_once 'database/database.php';
$db = new Database();

// Set headers to force download as Excel file
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Lab_Usage_Report_" . date('Y-m-d') . ".xls");

$sql = "SELECT cul.*, sd.name, sd.roll_no 
        FROM computer_usage_logs cul
        JOIN student_details sd ON cul.student_id = sd.id
        ORDER BY cul.usage_date DESC";

$stmt = $db->conn->prepare($sql);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

function formatDuration($start, $end) {
    if (!$end) return "Still in Lab";
    $s = new DateTime($start);
    $e = new DateTime($end);
    $diff = $s->diff($e);
    return $diff->format('%h hr %i min');
}
?>

<table border="1">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th>Date</th>
            <th>Roll No</th>
            <th>Student Name</th>
            <th>Check-In</th>
            <th>Check-Out</th>
            <th>Total Duration</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($logs as $log): ?>
        <tr>
            <td><?= $log['usage_date'] ?></td>
            <td><?= $log['roll_no'] ?></td>
            <td><?= $log['name'] ?></td>
            <td><?= date('h:i A', strtotime($log['check_in_time'])) ?></td>
            <td><?= $log['check_out_time'] ? date('h:i A', strtotime($log['check_out_time'])) : '-' ?></td>
            <td><?= formatDuration($log['check_in_time'], $log['check_out_time']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>