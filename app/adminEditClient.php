<?php
include 'defaultValue.php';
$response = json_decode(file_get_contents('php://input'), true);
$start_date = $response['start_date'];
$client = $response['client'];
$agenda = $response['agenda'];
$hour_salary = $response['hour_salary'];
$day_salary = $response['day_salary'];
include 'sqlConnect.php';
try {
  $rootPath = $_SERVER['DOCUMENT_ROOT'].$root_folder;
  $time = date('Y/m/d-H:i');
  $logDate = date('Ymd');
  $path = $rootPath."error_".$logDate.".txt";
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, adminEditClient, $client, $start_date, $agenda, $hour_salary, $day_salary\n");
  @fclose($log);
  $dbConnect = new mysqlConnect();
  $sql = "UPDATE client SET hour_salary = '$hour_salary', day_salary = '$day_salary' WHERE client = '$client' AND agenda = '$agenda' AND date = '$start_date'";
  $dbConnect->mysql->query($sql);
  $result = json_encode(array('status' => true));  
} catch(Exception $e) {
  $rootPath = $_SERVER['DOCUMENT_ROOT'].$root_folder;
  $time = date('Y/m/d-H:i');
  $logDate = date('Ymd');
  $path = $rootPath."error_".$logDate.".txt";
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, adminEditClient, $e\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => '登録にエラーが発生しました。'));
}
$dbConnect->dbClose();
echo($result);
?>