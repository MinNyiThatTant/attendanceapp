<?php
session_start();
require_once __DIR__ . '/database/database.php';
$db = new Database();

// --- DELETE LOGIC ---
$delete_msg = false;
if (isset($_POST['delete_before_date'])) {
    $target_date = $_POST['target_date'];
    if (!empty($target_date)) {
        $del_stmt = $db->conn->prepare("DELETE FROM computer_usage_logs WHERE usage_date < ?");
        $del_stmt->execute([$target_date]);
        $delete_msg = true;
    }
}

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

// Duration calculation
function getDuration($start, $end)
{
    if (!$end) return "<span style='color:orange;'>Still in Lab</span>";
    $start_time = new DateTime($start);
    $end_time = new DateTime($end);
    $interval = $start_time->diff($end_time);

    $hours = $interval->h;
    $mins = $interval->i;

    if ($hours == 0 && $mins == 0) {
        return "Under 1 min";
    }

    return ($hours > 0 ? $hours . " hr " : "") . $mins . " min";
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Computer Usage Report</title>
    <link rel="stylesheet" href="css/attendance.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .cleanup-box {
            background: #fff5f5;
            border: 1px solid #feb2b2;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
        .delete-btn {
            background: #e53e3e;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .delete-btn:hover { background: #c53030; }
    </style>
</head>

<body>
    <div class="container">
        <header class="attendance-header">
            <h1>Computer <span style="color:#4f46e5">Lab</span></h1>
            <div style="display:flex; gap:10px;">
                <form method="GET" style="display:flex; gap:5px;">
                    <input type="text" name="search" placeholder="Search Student..." value="<?= htmlspecialchars($search) ?>" style="padding:10px; border-radius:5px; border:1px solid #ddd;">
                    <button type="submit" class="class-btn">Search</button>
                </form>
                
                <a href="export_lab_usage.php" class="class-btn" style="text-decoration:none; background:#10b981; color:white;">
                    <i class="fa-solid fa-file-excel"></i> Export
                </a>
                <a href="dashboard.php" class="class-btn" style="text-decoration:none; background:lightblue;">
                    <i class="fa-solid fa-house"></i> Back to Dashboard
                </a>
            </div>
        </header>

        <div class="cleanup-box">
            <div>
                <h4 style="margin:0; color:#c53030;"><i class="fa-solid fa-broom"></i> Data Cleanup</h4>
                <p style="margin:0; font-size:0.85rem; color:#718096;">သတ်မှတ်ရက်စွဲ၏ ရှေ့ပိုင်းမှ Data များကို ဖျက်ထုတ်ရန်</p>
            </div>
            <form method="POST" id="deleteForm" style="display:flex; gap:10px; align-items:center;">
                <input type="date" name="target_date" required style="padding:8px; border-radius:5px; border:1px solid #cbd5e0;">
                <button type="button" onclick="confirmDelete()" class="delete-btn">
                    <i class="fa-solid fa-trash-can"></i> Delete Old Records
                </button>
                <input type="hidden" name="delete_before_date" value="1">
            </form>
        </div>

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
                        <tr>
                            <td colspan="6" style="text-align:center;">No records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Delete Confirmation
        function confirmDelete() {
            const dateInput = document.querySelector('input[name="target_date"]').value;
            if(!dateInput) {
                Swal.fire('သတိပေးချက်', 'ရက်စွဲရွေးချယ်ပေးပါ', 'warning');
                return;
            }

            Swal.fire({
                title: 'သေချာပါသလား?',
                text: dateInput + " ရှေ့ပိုင်းက Data တွေကို အပြီးတိုင်ဖျက်ပါတော့မယ်!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ဖျက်မည်',
                cancelButtonText: 'မဖျက်တော့ပါ'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteForm').submit();
                }
            })
        }

        // Success Message
        <?php if ($delete_msg): ?>
            Swal.fire({
                icon: 'success',
                title: 'ဖျက်ပြီးပါပြီ',
                text: 'ဟောင်းနေသော Data များကို ရှင်းလင်းပြီးပါပြီ။',
                timer: 2000,
                showConfirmButton: false
            });
        <?php endif; ?>
    </script>
</body>
</html>