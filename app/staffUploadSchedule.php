<?php
$response = json_decode(file_get_contents('php://input'), true);
$name = $response['name'];
$password = $response['password'];
$access_time = $response['access_time'];
$current_date = $response['current_date'];
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
    if ($access_time == $managerData['access_time']) {
      $dbConnect = new mysqlConnect();
      $del = "DELETE FROM schedule WHERE name = '$name' AND agenda = '' AND date > '$current_date'";
      $dbConnect->mysql->query($del);
    }
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

    $time = date('Y-m-d h:i:s', time());
    $time_sql = "UPDATE manager SET access_time = '$time' WHERE name = '$name'";
    $dbConnect->mysql->query($time_sql);

     $result = json_encode(array('status' => 'success' , 'message' => '登録を完了しました。'));
  } else {
    $result = json_encode(array('status' => 'error' , 'message' => 'パスワードを確認してください。'));
  }
} catch(Exception $e) {
  $result = json_encode(array('error' => 'error' , 'message' => '登録を失敗しました。'));
}
$dbConnect->dbClose();
echo($result);
?>