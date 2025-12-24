<?php
session_start();
if (empty($_SESSION["current_user"])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Major Selection</title>
    <link rel="stylesheet" href="css/attendance.css">
    <style>
        .major-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px; margin-top: 40px; }
        .major-card { background: white; padding: 30px; border-radius: 12px; text-align: center; 
                      box-shadow: var(--shadow); cursor: pointer; text-decoration: none; color: var(--primary); 
                      font-weight: bold; border: 1px solid var(--border); transition: 0.3s; }
        .major-card:hover { transform: translateY(-5px); border-color: var(--primary); background: #f5f3ff; }
    </style>
</head>
<body>
    <div class="container">
        <header class="attendance-header">
            <h1>Select <span style="color:#1f2937">Major</span></h1>
            <button class="logout-btn" id="btnlogout">Logout</button>
        </header>

        <div class="major-grid">
            <a href="attendance.php?major=CEIT" class="major-card">CEIT</a>
            <a href="attendance.php?major=Civil" class="major-card">Civil</a>
            <a href="attendance.php?major=EC" class="major-card">EC</a>
            <a href="attendance.php?major=EP" class="major-card">EP</a>
            <a href="attendance.php?major=Mech" class="major-card">Mech</a>
            <a href="attendance.php?major=MC" class="major-card">MC</a>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="js/logout.js"></script>
</body>
</html>