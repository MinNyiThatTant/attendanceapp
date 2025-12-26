<!DOCTYPE html>
<html>
<head>
    <title>RFID Simulation</title>
    <link rel="stylesheet" href="css/attendance.css">
</head>
<body>
    <div class="container" style="max-width: 500px; margin-top: 50px;">
        <div class="card" style="padding: 30px; text-align: center;">
            <h2>📟 RFID Card Simulator</h2>
            <p>Enter Student RFID UID to simulate a card tap.</p>
            
            <form action="rfid_handler.php" method="GET">
                <input type="text" name="uid" placeholder="Enter Card UID (e.g. 12345678)" 
                       style="width: 100%; height: 50px; text-align: center; font-size: 1.5rem; margin-bottom: 20px; border-radius: 8px; border: 2px solid #4f46e5;">
                <button type="submit" class="register-btn" style="width: 100%;">Tap Card</button>
            </form>
            
            <div style="margin-top: 20px; text-align: left; font-size: 0.9rem; color: #666;">
                <strong>Test Steps:</strong>
                <ol>
                    <li>Student မှာ UID တစ်ခုခု သတ်မှတ်ပါ။</li>
                    <li>Timetable မှာ အခုအချိန်အတွက် Course တစ်ခု သတ်မှတ်ပါ။</li>
                    <li>ဒီနေရာမှာ UID ရိုက်ထည့်ပြီး စမ်းသပ်ပါ။</li>
                </ol>
            </div>
        </div>
    </div>
</body>
</html>