<?php
include 'defaultValue.php';
$response = json_decode(file_get_contents('php://input'), true);
$start_date = $response['start_date'];
$end_date = $response['end_date'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $client = $dbConnect->getClient($start_date);
  if (empty($client)) {
    $client = [];
  }
  if ($client != []) {
    $sql = "UPDATE schedule SET client = '' WHERE date >= '$start_date' AND date <= '$end_date'";
    $dbConnect->mysql->query($sql);
    foreach ($client as $values) {
      $client_sql = "UPDATE schedule SET client = '{$values['client']}' WHERE agenda = '{$values['agenda']}' AND date >= '$start_date' AND date <= '$end_date'";
      $dbConnect->mysql->query($client_sql);
      if ($values['hour_salary'] != '') {
        $hour_sql = "UPDATE schedule SET admin_hour_salary = '{$values['hour_salary']}' WHERE agenda = '{$values['agenda']}' AND date >= '$start_date' AND admin_hour_salary == '' AND date <= '$end_date'";
        $dbConnect->mysql->query($hour_sql);
      }
      if ($values['day_salary'] != '') {
        $day_sql = "UPDATE schedule SET admin_day_salary = '{$values['day_salary']}' WHERE agenda = '{$values['agenda']}' AND date >= '$start_date' AND admin_day_salary == '' AND date <= '$end_date'";
        $dbConnect->mysql->query($day_sql);  
      }
      if ($values['staff_hour_salary'] != '') {
        $staff_hour_sql = "UPDATE schedule SET staff_hour_salary = '{$values['staff_hour_salary']}' WHERE agenda = '{$values['agenda']}' AND staff_hour_salary == '' AND date >= '$start_date' AND date <= '$end_date'";
        $dbConnect->mysql->query($staff_hour_sql);  
      }
      if ($values['staff_day_salary'] != '') {
        $staff_day_sql = "UPDATE schedule SET staff_day_salary = '{$values['staff_day_salary']}' WHERE agenda = '{$values['agenda']}' AND staff_day_salary == '' AND date >= '$start_date' AND date <= '$end_date'";
        $dbConnect->mysql->query($staff_day_sql);  
      }
    }
    $result = json_encode(array('status' => true));
  } else {
    $sql = "UPDATE schedule SET client = '' WHERE date >= '$start_date' AND date <= '$end_date'";
    $dbConnect->mysql->query($sql);
    $result = json_encode(array('status' => false , 'message' => 'クライアントが存在しません。'));
  }
} catch(Exception $e) {
  $rootPath = $_SERVER['DOCUMENT_ROOT'].$root_folder;
  $time = date('Y/m/d-H:i');
  $logDate = date('Ymd');
  $path = $rootPath."error_".$logDate.".txt";
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, adminUploadSchedule, $e\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => '反映にエラーが発生しました。'));
}
$dbConnect->dbClose();
echo($result);
?>