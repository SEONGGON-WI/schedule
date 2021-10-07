<?php
$response = json_decode(file_get_contents('php://input'), true);
$event = $response['event'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $del = "DELETE FROM schedule WHERE name ='$name' AND comment = ''";
  $dbConnect->mysql->query($del);
  $index = 0;
  $sql_array = [];
  foreach ($event as $values) {
    $sql_array[$index] = "( '{$name}', '{$values['date']}', '{$values['comment']}', '{$values['hour_salary']}', '{$values['day_salary']}' )";
    $index++;
  }
  $sql = "INSERT INTO schedule ( name, date, comment, hour_salary, day_salary ) VALUES";
  $sub_sql = "ON DUPLICATE KEY UPDATE comment = VALUES(comment), hour_salary = VALUES(hour_salary), day_salary = VALUES(day_salary)";
  $sub_sql_query = implode(', ', $sql_array);
  $sql = $sql.$sub_sql_query.$sub_sql;
  $dbConnect->mysql->query($sql);
  $result = json_encode(array('status' => 'success' , 'message' => '登録を完了しました。'));
} catch(Exception $e) {
  $result = json_encode(array('error' => 'error' , 'message' => '登録を失敗しました。'));
}
$dbConnect->dbClose();
echo($result);
?>