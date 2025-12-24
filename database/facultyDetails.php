<?php 

$path=$_SERVER['DOCUMENT_ROOT'];
require_once $path . '/attendanceapp/database/database.php';
class faculty_details
{
    public function verifyUser($dbo,$un,$pw)
    {
        $rv=["id"=>-1,"status"=>"error"];
        // select by user_name (column name is user_name in table) and fetch hashed password
        $c = "SELECT id, password FROM faculty_details WHERE user_name = :un";
        $s = $dbo->conn->prepare($c);
        try {
            $s->execute([':un' => $un]);
            if ($s->rowCount() > 0) {
                $result = $s->fetch(PDO::FETCH_ASSOC);
                // verify hashed password
                if (password_verify($pw, $result['password'])) {
                    $rv = ["id" => $result['id'], "status" => "success"];
                    return $rv;
                } else {
                    $rv = ["id" => -1, "status" => "error", "message" => "Wrong password"];
                }
            } else {
                $rv = ["id" => -1, "status" => "error", "message" => "user name does not exist"];
            }
        } catch (PDOException $e) {
            $rv = ["id" => -1, "status" => "error", "message" => "Query failed: " . $e->getMessage()];
        }
        // json_encode($rv);
        return $rv;
    }
}



?>