<?php
$response = json_decode(file_get_contents('php://input'), true);
$start_date = $response['start_date'];
$end_date = $response['end_date'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $client = $dbConnect->getClient();
  if (empty($client)) {
    $client = [];
  }
  $sql = "UPDATE schedule SET client = '' WHERE agenda != '' AND date >= '$start_date' AND date <= '$end_date'";
  if ($client != []) {
    $dbConnect->mysql->query($sql);
    foreach ($client as $values) {
      $sql = "UPDATE schedule SET client = '{$values['client']}' WHERE agenda = '{$values['agenda']}' AND date >= '$start_date' AND date <= '$end_date'";
      $dbConnect->mysql->query($sql);
    }
    $result = json_encode(array('status' => true));
  } else {
    $result = json_encode(array('status' => false , 'message' => 'クライアントが存在しません。'));
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
  @fwrite($log,"$time, adminUploadSchedule, $e\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => '反映に失敗しました。'));
}
$dbConnect->dbClose();
echo($result);
?>