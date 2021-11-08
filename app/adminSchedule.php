<?php
$response = json_decode(file_get_contents('php://input'), true);
$type = $response['type'];
$event = $response['event'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $index = 0;
  $sql_array = [];
  if ($event != []) {
    foreach ($event as $values) {
      $sql_array[$index] = "( '{$values['name']}', '{$values['date']}', '{$values['agenda']}', '{$values['start_time']}', '{$values['end_time']}', '{$values['total_time']}', 
                            '{$values['staff_hour_salary']}', '{$values['staff_day_salary']}', '{$values['staff_expense']}', 
                            '{$values['admin_hour_salary']}', '{$values['admin_day_salary']}', '{$values['admin_expense']}' )";
      $index++;
    }

    if ($type == 'upload') {
      $sql = "INSERT INTO schedule ( name, date, agenda, start_time, end_time, total_time, staff_hour_salary, staff_day_salary, staff_expense, admin_hour_salary, admin_day_salary , admin_expense ) VALUES";
      $sub_sql = "ON DUPLICATE KEY UPDATE agenda = VALUES(agenda), start_time = VALUES(start_time), end_time = VALUES(end_time), total_time = VALUES(total_time), 
                staff_hour_salary = VALUES(staff_hour_salary), staff_day_salary = VALUES(staff_day_salary),  staff_expense = VALUES(staff_expense),
                admin_hour_salary = VALUES(admin_hour_salary), admin_day_salary = VALUES(admin_day_salary), admin_expense = VALUES(admin_expense)";
      $sub_sql_query = implode(', ', $sql_array);
      $sql = $sql.$sub_sql_query.$sub_sql;
    } else {
      $sql = "INSERT IGNORE INTO schedule ( name, date, agenda, start_time, end_time, total_time, staff_hour_salary, staff_day_salary, staff_expense, admin_hour_salary, admin_day_salary , admin_expense ) VALUES";
      $sub_sql_query = implode(', ', $sql_array);
      $sql = $sql.$sub_sql_query;
    }
    $dbConnect->mysql->query($sql);
    $result = json_encode(array('status' => true));
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
  @fwrite($log,"$time, adminSchedule, $e\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => '登録を失敗しました。'));
}
$dbConnect->dbClose();
echo($result);
?>