<?php
$response = json_decode(file_get_contents('php://input'), true);
$event = $response['event'];
$start_date = $response['start_date'];
$end_date = $response['end_date'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $data = $dbConnect->getAdmin($start_date, $end_date);
  $del = "DELETE FROM schedule WHERE";
  $index = 0;
  $sql_array = [];
  foreach ($data as $element) {
    $duplicate = true;
    foreach ($event as $values) {
      if ($element['name'] == $values['name'] && $element['date'] == $values['date']) {
        $duplicate = false;
      }
      $sql_array[$index] = "( '{$values['name']}', '{$values['date']}', '{$values['comment']}', '{$values['hour_salary']}', '{$values['day_salary']}' )";
      $index++;
    }
    if ($duplicate) {
      $del = $del." (name = '{$element['name']}' AND date = '{$element['date']}') OR";
    }
  }
  if ($del != "DELETE FROM schedule WHERE") {
    $del = substr($del, 0, -3);
    $dbConnect->mysql->query($del);
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