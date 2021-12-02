<?php
include 'defaultValue.php';
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
  $rootPath = $_SERVER['DOCUMENT_ROOT'].$root_folder;
  $time = date('Y/m/d-H:i');
  $logDate = date('Ymd');
  $path = $rootPath."error_".$logDate.".txt";
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, adminGetEditSchedule, $e\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => 'データ更新にエラーが発生しました。'));
}
$dbConnect->dbClose();
echo($result);
?>