<?php
include 'defaultValue.php';
$response = json_decode(file_get_contents('php://input'), true);
$date = $response['date'];
include 'sqlConnect.php';
try {
  $rootPath = $_SERVER['DOCUMENT_ROOT'].$root_folder;
  $time = date('Y/m/d-H:i');
  $logDate = date('Ymd');
  $path = $rootPath.$logDate.".txt";
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, masterDeleteData, $date\n");
  @fclose($log);
  $dbConnect = new mysqlConnect();
  $del_schedule = "DELETE FROM schedule WHERE date < '$date'";
  $dbConnect->mysql->query($del_schedule);
  $del_client = "DELETE FROM client WHERE date < '$date'";
  $dbConnect->mysql->query($del_client);
  $del_manager = "DELETE FROM staff WHERE access_time < '$date'";
  $dbConnect->mysql->query($del_manager);
  $result = json_encode(array('status' => true));
} catch(Exception $e) {
  $rootPath = $_SERVER['DOCUMENT_ROOT'].$root_folder;
  $time = date('Y/m/d-H:i');
  $logDate = date('Ymd');
  $path = $rootPath."error_".$logDate.".txt";
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, masterDeleteData, $e\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => '削除にエラーが発生しました。'));
}
$dbConnect->dbClose();
echo($result);
?>