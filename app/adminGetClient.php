<?php
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $data = $dbConnect->getClient();

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
  @fwrite($log,"$time, adminGetClient, $e\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => 'クライアントリスト獲得にエラーが発生しました。'));
}
$dbConnect->dbClose();
echo($result);
?>