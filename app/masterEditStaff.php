<?php
include 'defaultValue.php';
$response = json_decode(file_get_contents('php://input'), true);
$current_name = $response['current_name'];
$name = $response['name'];
$password = $response['password'];
include 'sqlConnect.php';
try {
  $rootPath = $_SERVER['DOCUMENT_ROOT'].$root_folder;
  $time = date('Y/m/d-H:i');
  $logDate = date('Ymd');
  $path = $rootPath.$logDate.".txt";
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, masterEditStaff, $current_name, $name\n");
  @fclose($log);
  $dbConnect = new mysqlConnect();
  if ($current_name != $name) {
    $sql = "UPDATE manager SET name = '$name' WHERE name = '$current_name'";
    $dbConnect->mysql->query($sql);

    $sql = "UPDATE schedule SET name = '$name' WHERE name = '$current_name'";
    $dbConnect->mysql->query($sql);
    $result = json_encode(array('status' => true));
  }
  if ($password != '') {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $sql = "UPDATE manager SET password = '$hashedPassword' WHERE name = '$name'";
    $dbConnect->mysql->query($sql);
    $result = json_encode(array('status' => true));  
  }
} catch(Exception $e) {
  $rootPath = $_SERVER['DOCUMENT_ROOT'].$root_folder;
  $time = date('Y/m/d-H:i');
  $logDate = date('Ymd');
  $path = $rootPath."error_".$logDate.".txt";
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, masterEditStaff, $e\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => '登録にエラーが発生しました。'));
}
$dbConnect->dbClose();
echo($result);
?>