<?php
include 'defaultValue.php';
include 'sqlConnect.php';
$response = json_decode(file_get_contents('php://input'), true);
$start_date = $response['start_date'];
try {
  $dbConnect = new mysqlConnect();
  $data = $dbConnect->getClient($start_date);

  if (!empty($data)) {
    $result = json_encode(array('status' => true , 'data' => $data));
  } else {
    $result = json_encode(array('status' => true , 'data' => ''));
  }
} catch(Exception $e) {
  $rootPath = $_SERVER['DOCUMENT_ROOT'].$root_folder;
  $time = date('Y/m/d-H:i');
  $logDate = date('Ymd');
  $path = $rootPath."error_".$logDate.".txt";
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, adminGetClient, $e\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => 'クライアントリスト獲得にエラーが発生しました。'));
}
$dbConnect->dbClose();
echo($result);
?>