<?php
session_start();
// User Login စစ်ဆေးခြင်း
if (empty($_SESSION["current_user"])) {
    header('Location: login.php');
    exit;
}

// URL ကနေပို့လိုက်တဲ့ Major ကိုယူခြင်း (ဥပမာ - attendance.php?major=CEIT)
$major = isset($_GET['major']) ? $_GET['major'] : 'Major Not Selected';
?>

<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $major; ?> - Attendance System</title>
    <link rel="stylesheet" href="css/attendance.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<div class="container">
    <header class="attendance-header">
        <div class="attendance-brand">
            <h1><?php echo $major; ?><span style="color:#1f2937"> Attendance</span></h1>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="dashboard.php" style="text-decoration: none; font-size: 0.9rem; color: var(--primary);">Back to Dashboard</a>
            <button class="logout-btn" id="btnlogout">Logout</button>
        </div>
    </header>

    <div class="filter-section">
        <div class="session-box">
            <select>
                <option>Session: 2023-2024</option>
                <option>Session: 2024-2025</option>
            </select>
        </div>
        <button class="class-btn active">1st Year</button>
        <button class="class-btn">2nd Year</button>
        <button class="class-btn">3rd Year</button>
        <button class="class-btn">4th Year</button>
        <button class="class-btn">5th Year</button>
        <button class="class-btn">6th Year</button>
    </div>

    <div class="main-grid">
        
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <span class="card-title">Student Attendance List</span>
                <span style="font-size:0.8rem; color:var(--text-muted)">Total: 3 Students</span>
            </div>
            
            <form action="save_attendance.php" method="POST">
                <table class="student-table">
                    <thead>
                        <tr>
                            <th>Student Information</th>
                            <th style="text-align:center">Present</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="std-info">
                                <span style="font-weight:600">Aung Aung</span>
                                <span class="std-id">ID: STD-001</span>
                            </td>
                            <td style="text-align:center">
                                <input type="checkbox" name="att[]" value="STD-001" style="transform:scale(1.3); cursor:pointer;">
                            </td>
                        </tr>
                        <tr>
                            <td class="std-info">
                                <span style="font-weight:600">Maung Maung</span>
                                <span class="std-id">ID: STD-002</span>
                            </td>
                            <td style="text-align:center">
                                <input type="checkbox" name="att[]" value="STD-002" style="transform:scale(1.3); cursor:pointer;">
                            </td>
                        </tr>
                        <tr>
                            <td class="std-info">
                                <span style="font-weight:600">Su Su</span>
                                <span class="std-id">ID: STD-003</span>
                            </td>
                            <td style="text-align:center">
                                <input type="checkbox" name="att[]" value="STD-003" style="transform:scale(1.3); cursor:pointer;">
                            </td>
                        </tr>
                    </tbody>
                </table>
                <button type="submit" class="save-btn">Save Attendance Records</button>
            </form>
        </div>

        <div class="card">
            <span class="card-title">Subjects Today</span>
            <div class="subjects-grid" style="grid-template-columns: 1fr;">
                <div class="subject-card">
                    <span class="subject-code">CS-101</span>
                    <span class="subject-name">Web Development</span>
                    <span class="subject-time">Mon, 09:00 AM</span>
                </div>
                <div class="subject-card">
                    <span class="subject-code">CS-102</span>
                    <span class="subject-name">Database System</span>
                    <span class="subject-time">Tue, 10:30 AM</span>
                </div>
                <div class="subject-card">
                    <span class="subject-code">CS-103</span>
                    <span class="subject-name">UI/UX Design</span>
                    <span class="subject-time">Wed, 01:00 PM</span>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="js/logout.js"></script>

</body>
</html>