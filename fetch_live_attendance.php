<?php
require_once 'database/database.php';
$db = new Database();

$sql = "SELECT a.*, s.name, c.title as course_name 
        FROM attendance_details a 
        JOIN student_details s ON a.student_id = s.id 
        JOIN course_details c ON a.course_id = c.id 
        WHERE a.on_date = CURDATE() 
        ORDER BY a.id DESC LIMIT 5";

try {
    $logs = $db->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    if (!$logs) {
        echo "<tr><td colspan='4' style='text-align:center; padding: 20px;'>📡 No activity today yet.</td></tr>";
    } else {
        foreach ($logs as $row) {
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($row['name']) . "</strong></td>";
            echo "<td><span style='background:#eef2ff; color:#4f46e5; padding:3px 8px; border-radius:4px; font-size:0.8rem;'>" . htmlspecialchars($row['course_name']) . "</span></td>";
            echo "<td>Period " . $row['period'] . "</td>";
            $time = isset($row['created_at']) ? date('h:i A', strtotime($row['created_at'])) : date('h:i A');
            echo "<td>" . $time . "</td>";
            echo "</tr>";
        }
    }
} catch (PDOException $e) {
    echo "<tr><td colspan='4'>Error: " . $e->getMessage() . "</td></tr>";
}