<?php
$response = json_decode(file_get_contents('php://input'), true);
$name = $response['name'];
$password = $response['password'];
$start_date = $response['start_date'];
$end_date = $response['end_date'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $managerData = $dbConnect->getPassword($name);
  if (empty($managerData)) {
    $result = json_encode(array('status' => false , 'message' => '登録された名前がありません。'));
  } else {
    $hashedPassword = $managerData['password'];
    if (password_verify($password, $hashedPassword)) {
      $data = $dbConnect->getSchedule($name, $start_date, $end_date);
      if (empty($data)) {
        $result = json_encode(array('status' => true , 'data' => ''));
      } else {
        $result = json_encode(array('status' => true , 'data' => $data));
      }
    } else {
      $result = json_encode(array('status' => false , 'message' => 'パスワードを確認してください。'));
    }
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
  $remoteAddr = $_SERVER['REMOTE_ADDR'];
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, staffSearch, $e\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => '検索に失敗しました。'));
}
$dbConnect->dbClose();
echo($result);
?>