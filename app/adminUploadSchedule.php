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
  if ($client != []) {
    $sql = "UPDATE schedule SET client = '' WHERE date >= '$start_date' AND date <= '$end_date'";
    $dbConnect->mysql->query($sql);
    foreach ($client as $values) {
      $sql = "UPDATE schedule SET client = '{$values['client']}' WHERE agenda = '{$values['agenda']}' AND date >= '$start_date' AND date <= '$end_date'";
      $dbConnect->mysql->query($sql);
    }
    $result = json_encode(array('status' => true));
  } else {
    $sql = "UPDATE schedule SET client = '' WHERE date >= '$start_date' AND date <= '$end_date'";
    $dbConnect->mysql->query($sql);
    $result = json_encode(array('status' => false , 'message' => 'クライアントが存在しません。'));
  }
} catch(Exception $e) {
  $rootPath = $_SERVER['DOCUMENT_ROOT'].'/schedule/log/';
  $time = date('Y/m/d-H:i');
  $logDate = date('Ymd');
  $path = $rootPath."error_".$logDate.".txt";
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, adminUploadSchedule, $e\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => '反映にエラーが発生しました。'));
}
$dbConnect->dbClose();
echo($result);
?>