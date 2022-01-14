<?php
include 'defaultValue.php';
$response = json_decode(file_get_contents('php://input'), true);
$name = $response['name'];
include 'sqlConnect.php';
try {
  $rootPath = $_SERVER['DOCUMENT_ROOT'].$root_folder;
  $time = date('Y/m/d-H:i');
  $logDate = date('Ymd');
  $path = $rootPath.$logDate.".txt";
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, adminRemoveStaff, $name\n");
  @fclose($log);
  $dbConnect = new mysqlConnect();
  $del = "DELETE FROM manager WHERE name = '$name'";
  $dbConnect->mysql->query($del);
  $del = "DELETE FROM schedule WHERE name = '$name' AND agenda = ''";
  $dbConnect->mysql->query($del);
  $result = json_encode(array('status' => true));
} catch(Exception $e) {
  $rootPath = $_SERVER['DOCUMENT_ROOT'].$root_folder;
  $time = date('Y/m/d-H:i');
  $logDate = date('Ymd');
  $path = $rootPath."error_".$logDate.".txt";
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, adminRemoveStaff, $e\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => '削除にエラーが発生しました。'));
}
$dbConnect->dbClose();
echo($result);
?>