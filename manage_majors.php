<?php
session_start();
require_once 'database/database.php';
$db = new Database();

// --- Major အသစ်ထည့်ခြင်း သို့မဟုတ် ပြင်ဆင်ခြင်း Logic ---
if (isset($_POST['save_major'])) {
    $title = $_POST['title'];
    $code = $_POST['code'];
    $id = $_POST['major_id'];

    if (!empty($id)) {
        // ID ပါရင် Update လုပ်မယ်
        $stmt = $db->conn->prepare("UPDATE major_details SET title = ?, code = ? WHERE id = ?");
        $stmt->execute([$title, $code, $id]);
    } else {
        // ID မပါရင် Insert (အသစ်ထည့်) လုပ်မယ်
        $stmt = $db->conn->prepare("INSERT INTO major_details (title, code) VALUES (?, ?)");
        $stmt->execute([$title, $code]);
    }
    header("Location: manage_majors.php");
    exit;
}

// --- Delete Logic ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $db->conn->prepare("DELETE FROM major_details WHERE id = ?")->execute([$id]);
    header("Location: manage_majors.php");
    exit;
}

// --- Edit လုပ်ရန် Data ကြိုဆွဲထုတ်ခြင်း ---
$edit_major = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $db->conn->prepare("SELECT * FROM major_details WHERE id = ?");
    $stmt->execute([$id]);
    $edit_major = $stmt->fetch(PDO::FETCH_ASSOC);
}

$majors = $db->conn->query("SELECT * FROM major_details ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Majors</title>
    <link rel="stylesheet" href="css/attendance.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .btn-edit { color: #4f46e5; margin-right: 15px; text-decoration: none; font-size: 1.1rem; }
        .btn-delete { color: #ef4444; text-decoration: none; font-size: 1.1rem; border: none; background: none; cursor: pointer; }
        .action-cell { display: flex; justify-content: center; align-items: center; gap: 10px; }
        .edit-mode { border: 2px solid #4f46e5 !important; background: #f5f3ff; }
    </style>
</head>
<body>
<div class="container">
    <header class="attendance-header">
        <h1>⚙️ Manage <span style="color:#4f46e5">Major</span></h1>
        <a href="dashboard.php" class="class-btn" style="text-decoration:none; background:lightblue;">
            <i class="fa-solid fa-house"></i> Back To Dashboard
        </a>
    </header>

    <div class="card <?= $edit_major ? 'edit-mode' : '' ?>" style="margin-bottom: 20px;">
        <h3><?= $edit_major ? '<i class="fa-solid fa-pen-to-square"></i> Edit Major' : '<i class="fa-solid fa-plus"></i> Add New Major' ?></h3>
        <form method="POST" style="display:flex; gap:10px;">
            <input type="hidden" name="major_id" value="<?= $edit_major['id'] ?? '' ?>">
            
            <input type="text" name="title" placeholder="Major Name (e.g. Mechanical)" 
                   value="<?= $edit_major['title'] ?? '' ?>" required 
                   style="flex:2; padding:12px; border-radius:8px; border:1px solid #ddd;">
            
            <input type="text" name="code" placeholder="Code (e.g. ME)" 
                   value="<?= $edit_major['code'] ?? '' ?>" required 
                   style="flex:1; padding:12px; border-radius:8px; border:1px solid #ddd;">
            
            <button type="submit" name="save_major" class="save-btn" style="margin:0; width:auto; padding:10px 25px; background: <?= $edit_major ? '#10b981' : '#4f46e5' ?>;">
                <?= $edit_major ? '<i class="fa-solid fa-check"></i> Update' : '<i class="fa-solid fa-floppy-disk"></i> Add Major' ?>
            </button>
            
            <?php if($edit_major): ?>
                <a href="manage_majors.php" class="class-btn" style="background:#6b7280; text-decoration:none; color:white;">Cancel</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <table class="student-table">
            <thead>
                <tr>
                    <th width="80px">ID</th>
                    <th>Major Name</th>
                    <th width="150px">Code</th>
                    <th style="text-align:center" width="150px">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($majors)): ?>
                    <tr><td colspan="4" style="text-align:center; padding: 20px; color: #9ca3af;">No majors found.</td></tr>
                <?php endif; ?>
                
                <?php foreach($majors as $m): ?>
                <tr>
                    <td><?=$m['id']?></td>
                    <td style="font-weight: 500;"><?=$m['title']?></td>
                    <td><span class="ay-badge" style="background:#e0e7ff; color:#4338ca; padding: 3px 10px;"><?= $m['code'] ?></span></td>
                    <td class="action-cell">
                        <a href="?edit=<?=$m['id']?>" class="btn-edit" title="Edit">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <a href="?delete=<?=$m['id']?>" class="btn-delete" title="Delete" 
                           onclick="return confirm('ဖျက်ရန် သေချာပါသလား?')">
                            <i class="fa-solid fa-trash-can"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<button onclick="topFunction()" id="scrollUpBtn" title="Go to top"><i class="fa-solid fa-arrow-up"></i></button>

<script>
    // Scroll Button Logic
    let mybutton = document.getElementById("scrollUpBtn");
    window.onscroll = function() {
        if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
            mybutton.style.display = "block"; 
        } else {
            mybutton.style.display = "none"; 
        }
    };
    function topFunction() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
</script>
</body>
</html>