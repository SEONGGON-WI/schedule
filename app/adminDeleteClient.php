<?php
$response = json_decode(file_get_contents('php://input'), true);
$client = $response['client'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $del = "DELETE FROM client WHERE client = '$client'";
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
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, adminDeleteClient, $e\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => '削除を失敗しました。'));
}
$dbConnect->dbClose();
echo($result);
?>