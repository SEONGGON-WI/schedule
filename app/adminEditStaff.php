<?php
include 'defaultValue.php';
$response = json_decode(file_get_contents('php://input'), true);
$name = $response['name'];
$password = $response['password'];
include 'sqlConnect.php';
try {
  if ($password != '') {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $dbConnect = new mysqlConnect();
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
  @fwrite($log,"$time, adminEditStaff, $e\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => '登録にエラーが発生しました。'));
}
$dbConnect->dbClose();
echo($result);
?>