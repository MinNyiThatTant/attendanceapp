<?php
require_once 'database/database.php';
$db = new Database();

if (isset($_POST['upload_csv'])) {
    $filename = $_FILES['csv_file']['tmp_name'];

    if ($_FILES['csv_file']['size'] > 0) {
        $file = fopen($filename, "r");
        
        // over header
        fgetcsv($file);

        while (($column = fgetcsv($file, 1000, ",")) !== FALSE) {
            // CSV Format: Name, Roll No, Major ID, Current Semester
            $name = $column[0];
            $roll_no = $column[1];
            $major_id = $column[2];
            $semester = $column[3];

            $sql = "INSERT INTO student_details (name, roll_no, major_id, current_semester) VALUES (?, ?, ?, ?)";
            $db->conn->prepare($sql)->execute([$name, $roll_no, $major_id, $semester]);
        }
        fclose($file);
        echo "<script>alert('CSV Imported Successfully'); window.location='manage_students.php';</script>";
    }
}
?>

<form method="POST" enctype="multipart/form-data" class="card">
    <h3>Import Students via CSV</h3>
    <input type="file" name="csv_file" accept=".csv" required style="margin-bottom:10px;">
    <button type="submit" name="upload_csv" class="save-btn">Upload CSV</button>
    <p><small>Format: Name, Roll_No, Major_ID, Semester (e.g. 1st semester)</small></p>
</form>