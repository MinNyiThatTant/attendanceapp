<?php
session_start();
require_once 'database/database.php';
$db = new Database();
$conn = $db->conn;

if (isset($_POST['import_submit'])) {
    $academic_year = $_POST['academic_year'];
    $file = $_FILES['csv_file']['tmp_name'];
    $handle = fopen($file, "r");
    $total_added = 0;
    fgetcsv($handle); // Skip header

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if (empty($data[0])) continue;
        $roll_no = trim($data[0]);

        // search student by roll no
        $stmt_s = $conn->prepare("SELECT id, major_id, current_semester FROM student_details WHERE roll_no = ?");
        $stmt_s->execute([$roll_no]);
        $student = $stmt_s->fetch(PDO::FETCH_ASSOC);

        if ($student) {
            // search courses for the student's semester and major
            $stmt_c = $conn->prepare("
                SELECT cd.id, cd.session_id 
                FROM course_details cd
                JOIN session_details sd ON cd.session_id = sd.id
                JOIN course_assignments ca ON cd.id = ca.course_id
                WHERE sd.term COLLATE utf8mb4_general_ci LIKE :sem COLLATE utf8mb4_general_ci 
                AND ca.major_id = :major
            ");
            $stmt_c->execute([
                ':sem' => "%" . trim($student['current_semester']) . "%",
                ':major' => $student['major_id']
            ]);
            $courses = $stmt_c->fetchAll(PDO::FETCH_ASSOC);

            foreach ($courses as $c) {
                // check duplicate
                $check = $conn->prepare("SELECT id FROM course_registration WHERE student_id=? AND course_id=? AND academic_year=?");
                $check->execute([$student['id'], $c['id'], $academic_year]);
                
                if (!$check->fetch()) {
                    $conn->prepare("INSERT INTO course_registration (student_id, course_id, session_id, academic_year) VALUES (?, ?, ?, ?)")
                         ->execute([$student['id'], $c['id'], $c['session_id'], $academic_year]);
                    $total_added++;
                }
            }
        }
    }
    fclose($handle);
    header("Location: manage_registration.php?msg=success&total=$total_added");
    exit();
}