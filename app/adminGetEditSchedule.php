<?php
$response = json_decode(file_get_contents('php://input'), true);
$date = $response['date'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $data = $dbConnect->getEdit($date);

  if (!empty($data)) {
    $result = json_encode(array('status' => true , 'data' => $data));
  } else {
    $result = json_encode(array('status' => true , 'data' => ''));
  }
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
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, adminGetEditSchedule, $e\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => 'データ更新に失敗しました。'));
}
$dbConnect->dbClose();
echo($result);
?>