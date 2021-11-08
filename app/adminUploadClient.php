<?php
$response = json_decode(file_get_contents('php://input'), true);
$client = $response['client'];
$agenda = $response['agenda'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $index = 0;
  $sql_array = [];
  foreach ($agenda as $values) {
    $sql_array[$index] = "( '{$client}', '{$values}' )";
    $index++;
  }
  $sql = "INSERT IGNORE INTO client ( client, agenda ) VALUES";
  $sub_sql_query = implode(', ', $sql_array);
  $sql = $sql.$sub_sql_query;
  $dbConnect->mysql->query($sql);
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
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, adminUploadClient, $e\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => '登録を失敗しました。'));
}
$dbConnect->dbClose();
echo($result);
?>