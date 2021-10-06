<?php
$response = json_decode(file_get_contents('php://input'), true);

$name = $response['name'];
$password = $response['password'];
$event = $response['event'];

include 'sqlConnect.php';

try {
  $dbConnect = new mysqlConnect();
  $managerPassword= $dbConnect->getPassword($name);

  if (empty($managerPassword)) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $query = "INSERT into manager values";
    $query = $query."('$name', '$hashedPassword')";
    $dbConnect->mysql->query($query);
    $managerPassword = $hashedPassword;
  }
  $hashedPassword = $managerPassword;
  if (password_verify($password, $hashedPassword)) {
    $del = "DELETE FROM schedule WHERE name ='$name' AND comment = ''";
    $dbConnect->mysql->query($del);

    $index = 0;
    $sql_array = [];
    foreach ($event as $values) {
      $sql_array[$index] = "( '{$name}', '{$values['date']}', '{$values['start_time']}', '{$values['end_time']}', '{$values['staff_price']}' )";
      $index++;
    }
    $sql = "INSERT INTO schedule ( name, date, start_time, end_time, staff_price ) VALUES";
    $sub_sql = "ON DUPLICATE KEY UPDATE start_time = VALUES(start_time), end_time = VALUES(end_time), staff_price = VALUES(staff_price)";
    $sub_sql_query = implode(', ', $sql_array);
    $sql = $sql.$sub_sql_query.$sub_sql;
    $dbConnect->mysql->query($sql);
    $result = json_encode(array('status' => 'success' , 'message' => '登録を完了しました。'));
  } else {
    $result = json_encode(array('status' => 'error' , 'message' => 'パスワードを確認してください。'));
  }
} catch(Exception $e) {
  $result = json_encode(array('error' => 'error' , 'message' => '登録が失敗しました。'));
}
$dbConnect->dbClose();
echo($result);
?>