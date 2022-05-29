<?php
include 'defaultValue.php';
$response = json_decode(file_get_contents('php://input'), true);
$start_date = $response['start_date'];
$end_date = $response['end_date'];
$client = $response['client'];
$select_mode = $response['select_mode'];
include 'sqlConnect.php';
try {
  if ($select_mode === false) {
    $dbConnect = new mysqlConnect();
    $client_list = $dbConnect->getClient($start_date);
    if (empty($client_list)) {
      $client_list = [];
    }
    if ($client_list != []) {
      foreach ($client_list as $values) {
        $client_sql = "UPDATE schedule SET client = '{$values['client']}' WHERE agenda = '{$values['agenda']}' AND date >= '$start_date' AND date <= '$end_date'";
        $dbConnect->mysql->query($client_sql);
        if ($values['hour_salary'] != '') {
          $hour_sql = "UPDATE schedule SET admin_hour_salary = '{$values['hour_salary']}' WHERE agenda = '{$values['agenda']}' AND admin_hour_salary = '' AND date >= '$start_date' AND date <= '$end_date'";
          $dbConnect->mysql->query($hour_sql);
        }
        if ($values['day_salary'] != '') {
          $day_sql = "UPDATE schedule SET admin_day_salary = '{$values['day_salary']}' WHERE agenda = '{$values['agenda']}' AND admin_day_salary = '' AND date >= '$start_date' AND date <= '$end_date'";
          $dbConnect->mysql->query($day_sql);  
        } 
        if ($values['staff_hour_salary'] != '') {
          $staff_hour_sql = "UPDATE schedule SET staff_hour_salary = '{$values['staff_hour_salary']}' WHERE agenda = '{$values['agenda']}' AND staff_hour_salary = '' AND date >= '$start_date' AND date <= '$end_date'";
          $dbConnect->mysql->query($staff_hour_sql);  
        }
        if ($values['staff_day_salary'] != '') {
          $staff_day_sql = "UPDATE schedule SET staff_day_salary = '{$values['staff_day_salary']}' WHERE agenda = '{$values['agenda']}' AND staff_day_salary = '' AND date >= '$start_date' AND date <= '$end_date'";
          $dbConnect->mysql->query($staff_day_sql);  
        }
      }
      $result = json_encode(array('status' => true));
    } else {
      $sql = "UPDATE schedule SET client = '' WHERE date >= '$start_date' AND date <= '$end_date'";
      $dbConnect->mysql->query($sql);
      $result = json_encode(array('status' => false , 'message' => 'クライアントが存在しません。'));
    }
  } else {
    $dbConnect = new mysqlConnect();
    $client_sql = "UPDATE schedule SET client = '{$client['client']}' WHERE agenda = '{$client['agenda']}' AND date >= '$start_date' AND date <= '$end_date'";
    $dbConnect->mysql->query($client_sql);
    if ($client['hour_salary'] != '') {
      $hour_sql = "UPDATE schedule SET admin_hour_salary = '{$client['hour_salary']}' WHERE agenda = '{$client['agenda']}' AND admin_hour_salary = '' AND date >= '$start_date' AND date <= '$end_date'";
      $dbConnect->mysql->query($hour_sql);
    }
    if ($client['day_salary'] != '') {
      $day_sql = "UPDATE schedule SET admin_day_salary = '{$client['day_salary']}' WHERE agenda = '{$client['agenda']}' AND admin_day_salary = '' AND date >= '$start_date' AND date <= '$end_date'";
      $dbConnect->mysql->query($day_sql);  
    }
    if ($client['staff_hour_salary'] != '') {
      $staff_hour_sql = "UPDATE schedule SET staff_hour_salary = '{$client['staff_hour_salary']}' WHERE agenda = '{$client['agenda']}' AND staff_hour_salary = '' AND date >= '$start_date' AND date <= '$end_date'";
      $dbConnect->mysql->query($staff_hour_sql);  
    }
    if ($client['staff_day_salary'] != '') {
      $staff_day_sql = "UPDATE schedule SET staff_day_salary = '{$client['staff_day_salary']}' WHERE agenda = '{$client['agenda']}' AND staff_day_salary = '' AND date >= '$start_date' AND date <= '$end_date'";
      $dbConnect->mysql->query($staff_day_sql);  
    }
    $result = json_encode(array('status' => true));
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