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
  if (isset($data)) {
    foreach ($data as $element) {
      $duplicate = true;
      foreach ($event as $values) {
        if ($element['name'] == $values['name'] && $element['date'] == $values['date']) {
          $duplicate = false;
        }
        $sql_array[$index] = "( '{$values['name']}', '{$values['date']}', '{$values['agenda']}', '{$values['start_time']}', '{$values['end_time']}', '{$values['total_time']}', 
                              '{$values['staff_hour_salary']}', '{$values['staff_day_salary']}', '{$values['staff_expense']}', 
                              '{$values['admin_hour_salary']}', '{$values['admin_day_salary']}' )";
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
    $sql = "INSERT INTO schedule ( name, date, agenda, start_time, end_time, total_time, staff_hour_salary, staff_day_salary, staff_expense, admin_hour_salary, admin_day_salary ) VALUES";
    $sub_sql = "ON DUPLICATE KEY UPDATE agenda = VALUES(agenda), start_time = VALUES(start_time), end_time = VALUES(end_time), total_time = VALUES(total_time), 
              staff_hour_salary = VALUES(staff_hour_salary), staff_day_salary = VALUES(staff_day_salary),  staff_expense = VALUES(staff_expense),
              admin_hour_salary = VALUES(admin_hour_salary), admin_day_salary = VALUES(admin_day_salary)";
    $sub_sql_query = implode(', ', $sql_array);
    $sql = $sql.$sub_sql_query.$sub_sql;
    $dbConnect->mysql->query($sql);

    // $time = date('Y-m-d h:i:s', time());
    // $time_sql = "UPDATE manager SET access_time = '$time'";

    $result = json_encode(array('status' => 'success' , 'message' => '登録を完了しました。'));
  }
} catch(Exception $e) {
  $result = json_encode(array('error' => 'error' , 'message' => '登録を失敗しました。'));
}
$dbConnect->dbClose();
echo($result);
?>