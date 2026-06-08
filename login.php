<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMS - Student Attendance Management System | Login</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/loader.css">
    <style>
        /* Additional inline styles for SAMS branding */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .loginform {
            background: white;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        .loginform::before {
            content: "SAMS";
            display: block;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
        }
    </style>
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
        <div class="diverror topmarginlarge" id="diverror">
            <label for="" class="errormessage" id="errormessage">ERROR GOES HERE</label>

        </div>
    </div>

    <div class="lockscreen" id="lockscreen">
        <div class="spinner" id="spinner">
            <label for="" class="lblwait topmargin" id="lblwait">PLEASE WAIT...</label>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="js/login.js"></script>
</body>
</html>