<?php
class Database
{
  
    private $servername = "127.0.0.1:3307";
    private $username = "root";
    private $password = "";
    private $dbname = "attendance_db";
    public $conn=null;

    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host=$this->servername;dbname=$this->dbname", $this->username, $this->password);
            // set the PDO error mode to exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // echo "Connected successfully";
        } catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }

    }


    // database/database.php 
public function getAcademicYear() {
    $month = (int)date('m');
    $year = (int)date('Y');
    // ဇွန်လ (Month 6) မတိုင်ခင်ဆိုရင် ယခင်နှစ်-လက်ရှိနှစ်
    if ($month < 6) {
        return ($year - 1) . "-" . $year;
    } else {
        return $year . "-" . ($year + 1);
    }
}
}