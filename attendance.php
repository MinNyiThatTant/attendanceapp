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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Dashboard</title>
</head>
<body>
    <h3>You are in Attendance.</h3>
    <button id="btnlogout">LOGOUT</button>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="js/logout.js"></script>
</body>
</html>