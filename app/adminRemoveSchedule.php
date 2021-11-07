<?php
$response = json_decode(file_get_contents('php://input'), true);
$current_date = $response['current_date'];
include 'sqlConnect.php';
try {
  $rootPath = $_SERVER['DOCUMENT_ROOT'].'/schedule/log/';
  $time = date('Y/m/d-H:i');
  $logDate = date('Ymd');
  $path = $rootPath.$logDate.".txt";
  if (!file_exists($path)) {
    $log = @fopen($path,"a+");
    @fwrite($log,"time, api, name, condition\n");
    @fclose($log);
  }
  $remoteAddr = $_SERVER['REMOTE_ADDR'];
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, adminRemove, admin, $current_date\n");
  @fclose($log);

  $dbConnect = new mysqlConnect();
  $del = "DELETE FROM schedule WHERE agenda = '' AND date < '$current_date'";
  $dbConnect->mysql->query($del);
  $result = json_encode(array('status' => true));
} catch(Exception $e) {
  $rootPath = $_SERVER['DOCUMENT_ROOT'].'/schedule/log/';
  $time = date('Y/m/d-H:i');
  $logDate = date('Ymd');
  $path = $rootPath."error_".$logDate.".txt";
  if (!file_exists($path)) {
    $log = @fopen($path,"a+");
    @fwrite($log,"time, api, error\n");
    @fclose($log);
  }
  $remoteAddr = $_SERVER['REMOTE_ADDR'];
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, adminRemoveSchedule, $e\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => '削除を失敗しました。'));
}
$dbConnect->dbClose();
echo($result);
?>