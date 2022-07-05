<?php
include 'defaultValue.php';
$response = json_decode(file_get_contents('php://input'), true);
$start_date = $response['start_date'];
$client = $response['client'];
$agenda = $response['agenda'];
$hour_salary = $response['hour_salary'];
$day_salary = $response['day_salary'];
$staff_hour_salary = $response['staff_hour_salary'];
$staff_day_salary = $response['staff_day_salary'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $index = 0;
  $client = trim($client);
  $sql = "INSERT IGNORE INTO client ( date, client, agenda, hour_salary, day_salary, staff_hour_salary, staff_day_salary ) VALUES ";
  foreach ($agenda as $values) {
    $sql_value = "( '{$start_date}', '{$client}', '{$values}', '{$hour_salary}', '{$day_salary}', '{$staff_hour_salary}', '{$staff_day_salary}' )";
    $query = $sql.$sql_value;
    $dbConnect->mysql->query($query);
    $index++;
  }
  if ($index == 0) {
    $result = json_encode(array('status' => false , 'message' => '登録を失敗しました。'));
  } else {
    $result = json_encode(array('status' => true));
  }
} catch(Exception $e) {
  $rootPath = $_SERVER['DOCUMENT_ROOT'].$root_folder;
  $time = date('Y/m/d-H:i');
  $logDate = date('Ymd');
  $path = $rootPath."error_".$logDate.".txt";
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, masterUploadClient, $e\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => '登録にエラーが発生しました。'));
}
$dbConnect->dbClose();
echo($result);
?>