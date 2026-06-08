<?php
session_start();
require_once 'database/database.php';
$db = new Database();

// save leave logic
if (isset($_POST['save_leave'])) {
    $student_id = $_POST['student_id'];
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    $leave_type = $_POST['leave_type'];
    $reason = $_POST['reason'];
    $leave_id = $_POST['leave_id'];
    $current_academic_year = $db->getAcademicYear();

    if (!empty($leave_id)) {
        // Update existing leave
        $stmt = $db->conn->prepare("UPDATE student_leaves SET student_id=?, from_date=?, to_date=?, leave_type=?, reason=?, academic_year=? WHERE id=?");
        $stmt->execute([$student_id, $from_date, $to_date, $leave_type, $reason, $current_academic_year]);
        $msg = "updated=1";
    } else {
        // Insert new leave
        $stmt = $db->conn->prepare("INSERT INTO student_leaves (student_id, from_date, to_date, leave_type, reason, academic_year) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$student_id, $from_date, $to_date, $leave_type, $reason, $current_academic_year]);
        $msg = "success=1";
    }
    header("Location: manage_leaves.php?$msg");
    exit;
}

// ၂။ Delete Logic
if (isset($_GET['delete'])) {
    $stmt = $db->conn->prepare("DELETE FROM student_leaves WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: manage_leaves.php?msg=deleted");
    exit;
}

// ၃။ Edit Logic
$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $db->conn->prepare("SELECT * FROM student_leaves WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch all students and leaves for listing
$students = $db->conn->query("SELECT id, name, roll_no FROM student_details ORDER BY name ASC")->fetchAll();
$leaves = $db->conn->query("SELECT l.*, s.name, s.roll_no FROM student_leaves l JOIN student_details s ON l.student_id = s.id ORDER BY l.from_date DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Leave Management</title>
    <link rel="stylesheet" href="css/attendance.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        .leave-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 0 15px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .select2-container--default .select2-selection--single {
            height: 40px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding-top: 5px;
        }

        .reason-box {
            grid-column: span 4;
        }

        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
            resize: none;
        }

        .save-btn {
            grid-column: span 4;
            background: #4f46e5;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        .save-btn:hover {
            background: #4338ca;
        }

        .btn-icon {
            text-decoration: none;
            font-size: 0.9rem;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .edit-btn {
            color: #4f46e5;
        }

        .delete-btn {
            color: #ef4444;
        }

        .alert {
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid;
        }
    </style>
</head>

<body>

    <div class="leave-container">
        <header class="attendance-header">
            <h2>Manage <span style="color:#4f46e5">Leaves</span></h2>
            <a href="dashboard.php" class="class-btn" style="text-decoration:none; background:lightblue;"><i class="fa-solid fa-house"></i> Back To Dashboard</a>
        </header>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert" style="background:#d1fae5; color:#065f46; border-color:#10b981;">✅ Leave recorded successfully!</div>
        <?php endif; ?>
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert" style="background:#e0f2fe; color:#075985; border-color:#0ea5e9;">ℹ️ Leave updated successfully!</div>
        <?php endif; ?>
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert" style="background:#fee2e2; color:#991b1b; border-color:#f87171;">🗑️ Record deleted!</div>
        <?php endif; ?>

        <div class="card" style="padding: 25px; border-radius: 15px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); background: white;">
            <h3 style="margin-top:0; margin-bottom:20px;"><?= $edit_data ? "✏️ Edit Leave Record" : "  Record New Leave" ?></h3>
            <form method="POST">
                <input type="hidden" name="leave_id" value="<?= $edit_data['id'] ?? '' ?>">
                <div class="form-grid">
                    <div>
                        <label style="font-size: 0.85rem; color: #666;">Student Name / Roll No</label><br>
                        <select name="student_id" id="student_select" style="width: 100%;" required>
                            <option value="">Search Student...</option>
                            <?php foreach ($students as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= (isset($edit_data) && $edit_data['student_id'] == $s['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['name']) ?> (<?= $s['roll_no'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="font-size: 0.85rem; color: #666;">From Date</label>
                        <input type="date" name="from_date" value="<?= $edit_data['from_date'] ?? '' ?>" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:8px;">
                    </div>
                    <div>
                        <label style="font-size: 0.85rem; color: #666;">To Date</label>
                        <input type="date" name="to_date" value="<?= $edit_data['to_date'] ?? '' ?>" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:8px;">
                    </div>
                    <div>
                        <label style="font-size: 0.85rem; color: #666;">Leave Type</label>
                        <select name="leave_type" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:8px;">
                            <option value="Medical" <?= (isset($edit_data) && $edit_data['leave_type'] == 'Medical') ? 'selected' : '' ?>>Medical</option>
                            <option value="Family" <?= (isset($edit_data) && $edit_data['leave_type'] == 'Family') ? 'selected' : '' ?>>Family</option>
                            <option value="Other" <?= (isset($edit_data) && $edit_data['leave_type'] == 'Other') ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    <div class="reason-box">
                        <label style="font-size: 0.85rem; color: #666;">Reason</label>
                        <textarea name="reason" rows="2" placeholder="Brief explanation..."><?= $edit_data['reason'] ?? '' ?></textarea>
                    </div>
                    <button type="submit" name="save_leave" class="save-btn">
                        <?= $edit_data ? "Update Record" : "Save Record" ?>
                    </button>
                    <?php if ($edit_data): ?>
                        <a href="manage_leaves.php" style="grid-column: span 4; text-align:center; color:#666; font-size:0.9rem; text-decoration:none;">Cancel Edit</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="card" style="margin-top: 30px; padding: 20px; border-radius: 15px; background: white;">
            <h3>Leave History</h3>
            <table style="width:100%; border-collapse: collapse;">
                <thead style="background: #f9fafb;">
                    <tr>
                        <th style="padding:12px; border-bottom:2px solid #eee; text-align:left;">Student</th>
                        <th style="padding:12px; border-bottom:2px solid #eee; text-align:left;">From</th>
                        <th style="padding:12px; border-bottom:2px solid #eee; text-align:left;">To</th>
                        <th style="padding:12px; border-bottom:2px solid #eee; text-align:left;">Type</th>
                        <th style="padding:12px; border-bottom:2px solid #eee; text-align:left;">Reason</th>
                        <th style="padding:12px; border-bottom:2px solid #eee; text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($leaves)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding:20px; color:#999;">No leave records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($leaves as $l): ?>
                            <tr>
                                <td style="padding:12px; border-bottom:1px solid #eee;">
                                    <strong><?= htmlspecialchars($l['name']) ?></strong><br>
                                    <small style="color:#666;"><?= $l['roll_no'] ?></small>
                                </td>
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?= date('d M Y', strtotime($l['from_date'])) ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee;"><?= date('d M Y', strtotime($l['to_date'])) ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee;">
                                    <span style="padding:4px 8px; border-radius:12px; font-size:0.8rem; background:<?= $l['leave_type'] == 'Medical' ? '#fee2e2; color:#b91c1c;' : '#fef3c7; color:#92400e;' ?>">
                                        <?= $l['leave_type'] ?>
                                    </span>
                                </td>
                                <td style="padding:12px; border-bottom:1px solid #eee; font-size:0.9rem; color:#444;"><?= htmlspecialchars($l['reason']) ?></td>
                                <td style="padding:12px; border-bottom:1px solid #eee; text-align:center;">
                                    <a href="?edit=<?= $l['id'] ?>" class="btn-icon edit-btn" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <span style="color: #ddd;">|</span>
                                    <a href="?delete=<?= $l['id'] ?>" class="btn-icon delete-btn" onclick="return confirm('Are you sure you want to delete this?')" title="Delete">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#student_select').select2({
                placeholder: "Search by Name or Roll No...",
                allowClear: true
            });
        });
    </script>
</body>

</html>