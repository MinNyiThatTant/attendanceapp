<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Computer Lab Access</title>
    <link rel="stylesheet" href="css/attendance.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container" style="text-align:center; margin-top:50px;">
        <div class="card" style="padding:50px; border: 3px solid #4f46e5;">
            <h1>🖥️ Computer Lab <span style="color:#4f46e5">Access Control</span></h1>
            <p>Please scan your RFID card to Check-in / Check-out</p>
            
            <div style="margin: 30px 0;">
                <input type="text" id="rfid_input" autofocus 
                       style="padding:15px; width:300px; font-size:1.2rem; border-radius:10px; border:2px solid #ddd;">
            </div>

            <div id="status_msg" style="font-size:1.5rem; font-weight:bold; margin-top:20px;"></div>
        </div>
        <br>
        <a href="dashboard.php" style="text-decoration:none; color:#666;">← Back to Main Dashboard</a>
    </div>

    <script>
        $('#rfid_input').on('keypress', function(e) {
            if (e.which == 13) { 
                let uid = $(this).val();
                $(this).val('');
                
                $.post('ajaxhandler/computerUsageAjax.php', { rfid_uid: uid }, function(res) {
                    let data = JSON.parse(res);
                    if (data.status == 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Access Denied',
                            text: data.message
                        });
                    }
                });
            }
        });

        // Keep focus on input
        $(document).click(function() { $('#rfid_input').focus(); });
    </script>
</body>
</html>