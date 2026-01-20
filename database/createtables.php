<?php

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/attendanceapp/database/database.php';

function clearTable($dbo, $tabName)
{
    // Validate table name (allow only letters, numbers and underscore)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $tabName)) {
        echo "Error clearing table: invalid table name";
        return;
    }

    // exec() after validation
    $sql = "DELETE FROM `" . $tabName . "`";
    try {
        $rows = $dbo->conn->exec($sql);
        if ($rows === false) {
            echo "Error clearing table: failed to execute DELETE on $tabName";
        } else {
            echo "Cleared $rows rows from $tabName";
        }
    } catch (PDOException $e) {
        echo "Error clearing table: " . $e->getMessage();
    }
}

$dbo = new Database();

$c="create table if not exists student_details
(
    id int auto_increment primary key,
    roll_no varchar(50) unique not null,
    name varchar(100) not null
)";
$s = $dbo->conn->prepare($c);
try{
    $s->execute();
    echo "<br>Table created successfully";
}
catch (PDOException $e){
    echo "Table creation failed: " . $e->getMessage();
}

$c="create table if not exists course_details
(
    id int auto_increment primary key,
    code varchar(50) unique not null,
    title varchar(100) not null,
    credits int 
)";
$s = $dbo->conn->prepare($c);
try{
    $s->execute();
    echo "<br>Course detail created successfully";
}
catch (PDOException $e){
    echo "Course creation failed: " . $e->getMessage();
}

$c="create table if not exists faculty_details
(
    id int auto_increment primary key,
    user_name varchar(50) unique not null,
    name varchar(100),
    password varchar(255) 
)";
$s = $dbo->conn->prepare($c);
try{
    $s->execute();
    echo "<br>Faculty detail created successfully";
}
catch (PDOException $e){
    echo "Faculty creation failed: " . $e->getMessage();
}


$c="create table if not exists session_details
(
    id int auto_increment primary key,
    year int, 
    term varchar(100),
    unique (year, term)
)";
$s = $dbo->conn->prepare($c);
try{
    $s->execute();
    echo "<br>Session detail created successfully";
}
catch (PDOException $e){
    echo "<br>Session creation failed: " . $e->getMessage();
}

$c="create table if not exists course_registration
(
    student_id int,
    course_id int,
    session_id int,
    primary key (student_id, course_id, session_id)
    
)";
$s = $dbo->conn->prepare($c);
try{
    $s->execute();
    echo "<br>Course registration created.";
}
catch (PDOException $e){
    echo "<br>Course registration failed: " . $e->getMessage();
}

$c="create table if not exists course_allotment
(
    faculty_id int,
    course_id int,
    session_id int,
    primary key (faculty_id, course_id, session_id)
    
)";
$s = $dbo->conn->prepare($c);
try{
    $s->execute();
    echo "<br>Course allotment created successfully";
}
catch (PDOException $e){
    echo "Course allotment failed: " . $e->getMessage();
}

$c="create table if not exists attendance_details
(
    faculty_id int,
    student_id int,
    course_id int,
    session_id int,
    on_date date,
    status varchar(10),
    primary key (faculty_id, student_id, course_id, session_id, on_date)
)";
$s = $dbo->conn->prepare($c);
try{
    $s->execute();
    echo "<br>Attendance details created successfully";
}
catch (PDOException $e){
    echo "Attendance details creation failed: " . $e->getMessage();
}

$c="insert ignore into student_details (id,roll_no, name) values
(1, 'tu(25)/00001', 'မောင်အောင်မောင်း'),
(2, 'tu(25)/00002', 'မောအောင်‌‌အောင်'),
(3, 'tu(25)/00003', 'မပြည့်ပြည့်အောင်'),
(4, 'tu(25)/00004', 'မစည်စည်အောင်'),
(5, 'tu(25)/00005', 'မခိုင်ခိုင်အောင်'),
(6, 'tu(25)/00006', 'မသန်းသန်းအောင်'),
(7, 'tu(25)/00007', 'မဇော်ဇော်အောင်'),
(8, 'tu(25)/00008', 'မထွန်းထွန်းအောင်'),
(9, 'tu(25)/00009', 'မကျော်ကျော်အောင်'),
(10, 'tu(25)/00010', 'မဝင်းဝင်းအောင်'),
(11, 'tu(25)/00011', 'မစိုးစိုးအောင်'),
(12, 'tu(25)/00012', 'မနိုင်နိုင်အောင်'),
(13, 'tu(25)/00013', 'မထင်ထင်အောင်'),
(14, 'tu(25)/00014', 'မရွှေရွှေအောင်'),
(15, 'tu(25)/00015', 'မမြင့်မြင့်အောင်'),
(16, 'tu(25)/00016', 'မသိန်းသိန်းအောင်'),
(17, 'tu(25)/00017', 'မဇင်ဇင်အောင်'),
(18, 'tu(25)/00018', 'မခင်ခင်အောင်'),
(19, 'tu(25)/00019', 'မစိုးစိုးအောင်'),
(20, 'tu(25)/00020', 'မသုသုအောင်')";

$s = $dbo->conn->prepare($c);
try{
    $s->execute();
    echo "<br>Sample student data inserted successfully";
}
catch (PDOException $e){
    echo ("<br>duplicate student data insertion: " . $e->getMessage());
}


$c = "insert ignore into faculty_details (id,user_name, password, name) values
(1, 'admin', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'Admin User'),
(2, 'faculty1', '" . password_hash('faculty123', PASSWORD_DEFAULT) . "', 'Faculty One'),
(3, 'faculty2', '" . password_hash('faculty123', PASSWORD_DEFAULT) . "', 'Faculty Two'),
(4, 'faculty3', '" . password_hash('faculty123', PASSWORD_DEFAULT) . "', 'Faculty Three'),
(5, 'faculty4', '" . password_hash('faculty123', PASSWORD_DEFAULT) . "', 'Faculty Four'),
(6, 'faculty5', '" . password_hash('faculty123', PASSWORD_DEFAULT) . "', 'Faculty Five'),
(7, 'faculty6', '" . password_hash('faculty123', PASSWORD_DEFAULT) . "', 'Faculty Six'),
(8, 'faculty7', '" . password_hash('faculty123', PASSWORD_DEFAULT) . "', 'Faculty Seven'),
(9, 'faculty8', '" . password_hash('faculty123', PASSWORD_DEFAULT) . "', 'Faculty Eight'),
(10, 'faculty9', '" . password_hash('faculty123', PASSWORD_DEFAULT) . "', 'Faculty Nine')";

$s = $dbo->conn->prepare($c);
try{
    $s->execute();
    echo "<br>Sample faculty data inserted successfully";
}
catch (PDOException $e){
    echo ("<br>duplicate entry " . $e->getMessage());
}

$c="insert ignore into session_details (id,year, term) values
(1, 2024, '1st Semester'),
(2, 2025, '2nd Semester'),
(3, 2026, '3rd Semester'),
(4, 2027, '4th Semester'),
(5, 2028, '5th Semester'),
(6, 2029, '6th Semester'),
(7, 2030, '7th Semester'),
(8, 2031, '8th Semester'),
(9, 2032, '9th Semester'),
(10, 2033, '10th Semester'),
(11, 2026, '1st Semester')
";

$s = $dbo->conn->prepare($c);
try{
    $s->execute();
    echo "<br>Sample session data inserted successfully";
}
catch (PDOException $e){
    echo ("<br>duplicate session data insertion: " . $e->getMessage());
}


$c="insert ignore into course_details (id, title, code, credits) values
(1, 'Introduction to Computer Systems', 'CEIT11011', 3),
(2, 'C Programming', 'CEIT12002', 4),
(3, 'Digital Electronics', 'CEIT21011', 3),
(4, 'Basics Engineering Drawing', 'ME11011', 4),
(5, 'Engineering Physics', 'EPh12001', 3),
(6, 'Object Oriented Programming', 'CEIT21012', 3),
(7, 'Engineering Mathematics IV', 'EM22004', 3),
(8, 'Digital Communication', 'EC22001', 3),
(9, 'Structure', 'C220011', 4),
(10, 'Power Electronics', 'EP22001', 4)

";

$s = $dbo->conn->prepare($c);
try{
    $s->execute();
    echo "<br>Sample course data inserted successfully";
}
catch (PDOException $e){
    echo ("<br>duplicate course data insertion: " . $e->getMessage());
}

//if any record already there in the table delete them
clearTable($dbo, 'course_registration');
$c="insert ignore into course_registration
(student_id, course_id, session_id) values
(:sid, :cid, :sessid)";
$s=$dbo->conn->prepare($c);
//iterate over all the 20 students
// for each of them choose max 3 random courses, from 1 to 10
for($i=1;$i<=20;$i++)
{
    for($j=1;$j<=3;$j++)
        {
            $cid=rand(1,10);
            //insert the selected course into the course_registration table for 
            //session 1 and student_id $i
            try{
                $s->execute(array(':sid'=>$i, ':cid'=>$cid, ':sessid'=>1));
            }
            catch (PDOException $e){
                //ignore duplicate entries
            }


            //repeat for session 2
            $cid=rand(1,10);
            //insert the selected course into the course_registration table for 
            //session 1 and student_id $i
            try{
                $s->execute(array(':sid'=>$i, ':cid'=>$cid, ':sessid'=>2));
            }
            catch (PDOException $e){
                //ignore duplicate entries
            }
        }
}


//if any record already there in the table delete them
clearTable($dbo, 'course_allotment');
$c="insert ignore into course_allotment
(faculty_id, course_id, session_id) values
(:fid, :cid, :sessid)";
$s=$dbo->conn->prepare($c);
//iterate over all the 10 faculties
// for each of them choose max 2 random courses, from 1 to 10
for($i=1;$i<=10;$i++)
{
    for($j=1;$j<=2;$j++)
        {
            $cid=rand(1,10);
            //insert the selected course into the course_allotment table for 
            //session 1 and faculty_id $i
            try{
                $s->execute(array(':fid'=>$i, ':cid'=>$cid, ':sessid'=>1));
            }
            catch (PDOException $e){
                //ignore duplicate entries
            }


            //repeat for session 2
            $cid=rand(1,10);
            //insert the selected course into the course_registration table for 
            //session 1 and student_id $i
            try{
                $s->execute(array(':fid'=>$i, ':cid'=>$cid, ':sessid'=>2));
            }
            catch (PDOException $e){
                //ignore duplicate entries
            }
        }
}


?>