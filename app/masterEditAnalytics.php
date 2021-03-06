<?php
include 'defaultValue.php';
$response = json_decode(file_get_contents('php://input'), true);
$event = $response['event'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $rootPath = $_SERVER['DOCUMENT_ROOT'].$root_folder;
  $time = date('Y/m/d-H:i');
  $logDate = date('Ymd');
  $path = $rootPath.$logDate.".txt";
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, masterEditAnalytics, $date\n");
  @fclose($log);

  $index = 0;
  if ($event != []) {
    foreach ($event as $values) {
      $sql = "UPDATE schedule SET status = '{$values['status']}' WHERE name = '{$values['name']}' AND date = '{$values['date']}' AND _id = '{$values['_id']}'";
      $dbConnect->mysql->query($sql);
      $tax_sql = "UPDATE schedule SET tax_status = '{$values['tax_status']}' WHERE name = '{$values['name']}' AND date = '{$values['date']}' AND _id = '{$values['_id']}'";
      $dbConnect->mysql->query($tax_sql);
      $index ++;
    }
    if ($index == 0) {
      $result = json_encode(array('status' => false , 'message' => '登録を失敗しました。')); 
    } else {
      $result = json_encode(array('status' => true));  
    }
  } else {
    $result = json_encode(array('status' => true));  
  }

  $log = @fopen($path,"a+");
  @fwrite($log,"$time, masterEditAnalytics, $index\n");
  @fclose($log);
} catch(Exception $e) {
  $rootPath = $_SERVER['DOCUMENT_ROOT'].$root_folder;
  $time = date('Y/m/d-H:i');
  $logDate = date('Ymd');
  $path = $rootPath."error_".$logDate.".txt";
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, masterEditAnalytics, $e\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => '登録を失敗しました。'));
}
$dbConnect->dbClose();
echo($result);
?>