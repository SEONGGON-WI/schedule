<?php
$response = json_decode(file_get_contents('php://input'), true);
$client = $response['client'];
$agenda = $response['agenda'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $index = 0;
  $client = trim($client);
  $sql = "INSERT IGNORE INTO client ( client, agenda ) VALUES ";
  foreach ($agenda as $values) {
    $sql_value = "( '{$client}', '{$values}' )";
    $query = $sql.$sql_value;
    $dbConnect->mysql->query($query);
    $index++;
  }
  $result = json_encode(array('status' => true));
  if ($index == 0) {
    $result = json_encode(array('status' => false , 'message' => '登録を失敗しました。'));
  } else {
    $result = json_encode(array('status' => true));
  }
} catch(Exception $e) {
  $rootPath = $_SERVER['DOCUMENT_ROOT'].'/schedule/log/';
  $time = date('Y/m/d-H:i');
  $logDate = date('Ymd');
  $path = $rootPath."error_".$logDate.".txt";
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, adminUploadClient, $e\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => '登録を失敗しました。'));
}
$dbConnect->dbClose();
echo($result);
?>