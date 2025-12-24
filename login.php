<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <div class="loginform">
        <div class="inputgroup">
            <label for="txtusername" id="lblusername">USER NAME</label>
            <input type="text" id="txtusername" required>
        </div>
        <div class="inputgroup topmargin">
            <label for="txtpassword" id="lblpassword">PASSWORD</label>
            <input type="password" id="txtpassword" required>
        </div>
        <div class="divcallforaction topmargin">
            <button class="btnlogin inactivecolor" id="btnlogin">LOGIN</button>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="js/login.js"></script>
</body>
</html>