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
    $logDate = date('Ymd');
    $path = $rootPath.$logDate.".txt";
    if($search_condition == true) {
      $condition = "search";
    } else {
      $condition = "";
    }
    if (!file_exists($path)) {
      $log = @fopen($path,"a+");
      @fwrite($log,"time, api, name, condition\n");
      @fclose($log);
    }
    $log = @fopen($path,"a+");
    @fwrite($log,"$time, staffUpload, $name, $condition\n");
    @fclose($log);
    if ($search_condition == true) {
      $dbConnect = new mysqlConnect();
      $data = $dbConnect->getEditSchedule($name, $start_date, $end_date);
      if (!empty($data)) { 
        $del = "DELETE FROM schedule WHERE name ='$name' AND";
        foreach ($data as $element) {
          $duplicate = true;
          foreach ($event as $values) {
            if ($element['date'] == $values['date']) {
              $duplicate = false;
            }
          }
          if ($duplicate) {
            $del = $del." ( date = '{$element['date']}' ) OR";
          }
        }
        if ($del != "DELETE FROM schedule WHERE name ='$name' AND") {
          $del = substr($del, 0, -3);
          $dbConnect->mysql->query($del);
        }
      }
      if ($event != []) {
        $index = 0;
        $sql_array = [];
        foreach ($event as $values) {
          // $sql_array[$index] = "( '{$name}', '{$values['date']}', '{$values['agenda']}', '{$values['start_time']}', '{$values['end_time']}', '{$values['total_time']}', '{$values['staff_hour_salary']}', '{$values['staff_day_salary']}', '{$values['staff_expense']}' )";
          $sql_array[$index] = "( '{$name}', '{$values['date']}', '', '', '', '', '', '', '' )";
          $index++;
        }
        // $sql = "INSERT INTO schedule ( name, date, agenda, start_time, end_time, total_time, staff_hour_salary, staff_day_salary, staff_expense ) VALUES";
        // $sub_sql = "ON DUPLICATE KEY UPDATE start_time = VALUES(start_time), end_time = VALUES(end_time), total_time = VALUES(total_time), staff_hour_salary = VALUES(staff_hour_salary), staff_day_salary = VALUES(staff_day_salary), staff_expense = VALUES(staff_expense)";
        $sql = "INSERT IGNORE INTO schedule ( name, date, agenda, start_time, end_time, total_time, staff_hour_salary, staff_day_salary, staff_expense ) VALUES";
        if ($sql_array != []) {
          $sub_sql_query = implode(', ', $sql_array);
          $sql = $sql.$sub_sql_query.$sub_sql;
          $dbConnect->mysql->query($sql);
          $result = json_encode(array('status' => true , 'message' => '登録を完了しました。'));
        } else {
          $result = json_encode(array('status' => false , 'message' => '登録を失敗しました。'));
        }
      } else {
        $result = json_encode(array('status' => true , 'message' => '登録を完了しました。'));
      }
    } else {
      $index = 0;
      $sql_array = [];
      foreach ($event as $values) {
        // $sql_array[$index] = "( '{$name}', '{$values['date']}', '{$values['agenda']}', '{$values['start_time']}', '{$values['end_time']}', '{$values['total_time']}', '{$values['staff_hour_salary']}', '{$values['staff_day_salary']}', '{$values['staff_expense']}' )";
        $sql_array[$index] = "( '{$name}', '{$values['date']}', '', '', '', '', '', '', '' )";
        $index++;
      }
      $sql = "INSERT IGNORE INTO schedule ( name, date, agenda, start_time, end_time, total_time, staff_hour_salary, staff_day_salary, staff_expense ) VALUES";
      if ($sql_array != []) {
        $sub_sql_query = implode(', ', $sql_array);
        $sql = $sql.$sub_sql_query;
        $dbConnect->mysql->query($sql);
        $result = json_encode(array('status' => true , 'message' => '登録を完了しました。'));
      } else {
        $result = json_encode(array('status' => false , 'message' => '登録を失敗しました。'));
      }
    }
    $time = date('Y-m-d H:i:s', time());
    $time_sql = "UPDATE manager SET access_time = '$time' WHERE name = '$name'";
    $dbConnect->mysql->query($time_sql);
  } else {
    $result = json_encode(array('status' => false , 'message' => 'パスワードを確認してください。'));
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
  @fwrite($log,"$time, staffUpload, $e\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => '登録を失敗しました。'));
}
$dbConnect->dbClose();
echo($result);
?>