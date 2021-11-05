<?php
$response = json_decode(file_get_contents('php://input'), true);
$name = $response['name'];
$password = $response['password'];
$search_condition = $response['search_condition'];
$start_date = $response['start_date'];
$end_date = $response['end_date'];
$event = $response['event'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $managerData = $dbConnect->getPassword($name);

  if (empty($managerData)) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $query = "INSERT into manager ( name, password ) values";
    $query = $query."('$name', '$hashedPassword')";
    $dbConnect->mysql->query($query);
    $managerData['password'] = $hashedPassword;
  }
  $hashedPassword = $managerData['password'];
  if (password_verify($password, $hashedPassword)) {
    $rootPath = $_SERVER['DOCUMENT_ROOT'].'/schedule/log/';
    $time = date('Y/m/d-H:i');
    $logDate = date('Ym');
    $path = $rootPath.$logDate.".txt";

    if($search_condition == true) {
      $condition = "search";
    } else {
      $condition = "";
    }
    try {
      if (!file_exists($path)) {
        $log = @fopen($path,"a+");
        @fwrite($log,"time,api,date\n");
        @fclose($log);
      }
      $remoteAddr = $_SERVER['REMOTE_ADDR'];
      $log = @fopen($path,"a+");
      @fwrite($log,"$time,'staffUpload',$name_$condition\n");
      @fclose($log);
    } catch(Exception $e) {
      $logError = true;
    }

    if ($search_condition == true) {
      $dbConnect = new mysqlConnect();
      $del = "DELETE FROM schedule WHERE name = '$name' AND agenda = '' AND date >= '$start_date' AND date <= '$end_date'";
      $dbConnect->mysql->query($del);

      $index = 0;
      $sql_array = [];
      foreach ($event as $values) {
        $sql_array[$index] = "( '{$name}', '{$values['date']}', '{$values['start_time']}', '{$values['end_time']}', '{$values['total_time']}', '{$values['staff_hour_salary']}', '{$values['staff_day_salary']}', '{$values['staff_expense']}' )";
        $index++;
      }
      $sql = "INSERT INTO schedule ( name, date, start_time, end_time, total_time, staff_hour_salary, staff_day_salary, staff_expense ) VALUES";
      $sub_sql = "ON DUPLICATE KEY UPDATE start_time = VALUES(start_time), end_time = VALUES(end_time), total_time = VALUES(total_time), staff_hour_salary = VALUES(staff_hour_salary), staff_day_salary = VALUES(staff_day_salary), staff_expense = VALUES(staff_expense)";
      $sub_sql_query = implode(', ', $sql_array);
      $sql = $sql.$sub_sql_query.$sub_sql;
      $dbConnect->mysql->query($sql);
    }
    if ($search_condition == false) {
      $index = 0;
      $sql_array = [];
      foreach ($event as $values) {
        $sql_array[$index] = "( '{$name}', '{$values['date']}', '{$values['start_time']}', '{$values['end_time']}', '{$values['total_time']}', '{$values['staff_hour_salary']}', '{$values['staff_day_salary']}', '{$values['staff_expense']}' )";
        $index++;
      }
      $sql = "INSERT IGNORE INTO schedule ( name, date, start_time, end_time, total_time, staff_hour_salary, staff_day_salary, staff_expense ) VALUES";
      $sub_sql_query = implode(', ', $sql_array);
      $sql = $sql.$sub_sql_query;
      $dbConnect->mysql->query($sql);
    }

    $time = date('Y-m-d h:i:s', time());
    $time_sql = "UPDATE manager SET access_time = '$time' WHERE name = '$name'";
    $dbConnect->mysql->query($time_sql);

    $result = json_encode(array('status' => true , 'message' => '登録を完了しました。'));
  } else {
    $result = json_encode(array('status' => false , 'message' => 'パスワードを確認してください。'));
  }
} catch(Exception $e) {
  $result = json_encode(array('status' => false , 'message' => '登録を失敗しました。'));
}
$dbConnect->dbClose();
echo($result);
?>