<?php
include 'defaultValue.php';
$response = json_decode(file_get_contents('php://input'), true);
$date = $response['date'];
$event = $response['event'];
$remove_event = $response['remove_event'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  if ($remove_event != []) {
    $rootPath = $_SERVER['DOCUMENT_ROOT'].$root_folder;
    $time = date('Y/m/d-H:i');
    $logDate = date('Ymd');
    $path = $rootPath.$logDate.".txt";
    $log = @fopen($path,"a+");
    @fwrite($log,"$time, adminEditDeleteSchedule, $date\n");
    @fclose($log);
    $deleteName = '';
    foreach ($remove_event as $values) {
      $del = "DELETE FROM schedule WHERE date = '$date' AND name = '{$values['name']}'";
      $dbConnect->mysql->query($del);
      $deleteName = $deleteName.$values['name'].',';
    }
    $log = @fopen($path,"a+");
    @fwrite($log,"$time, adminEditDeleteSchedule, $deleteName\n");
    @fclose($log);
  }
  $index = 0;
  if ($event != []) {
    $sql = "INSERT INTO schedule ( name, date, client, agenda, overlap, start_time, end_time, total_time, admin_total_time, staff_hour_salary, staff_day_salary, staff_expense, admin_hour_salary, admin_day_salary , admin_expense ) VALUES ";
    $sub_sql = " ON DUPLICATE KEY UPDATE agenda = VALUES(agenda), client = VALUES(client), start_time = VALUES(start_time), end_time = VALUES(end_time), total_time = VALUES(total_time), admin_total_time = VALUES(admin_total_time), 
              staff_hour_salary = VALUES(staff_hour_salary), staff_day_salary = VALUES(staff_day_salary),  staff_expense = VALUES(staff_expense),
              admin_hour_salary = VALUES(admin_hour_salary), admin_day_salary = VALUES(admin_day_salary), admin_expense = VALUES(admin_expense)";
    foreach ($event as $values) {
      $values['agenda'] = preg_replace('/\r\n|\r|\n/','', $values['agenda']);
      $values['agenda'] = trim($values['agenda']);
      $sql_values = "( '{$values['name']}', '{$values['date']}', '{$values['client']}', '{$values['agenda']}', '{$values['overlap']}', '{$values['start_time']}', '{$values['end_time']}', '{$values['total_time']}', '{$values['admin_total_time']}',
                            '{$values['staff_hour_salary']}', '{$values['staff_day_salary']}', '{$values['staff_expense']}', 
                            '{$values['admin_hour_salary']}', '{$values['admin_day_salary']}', '{$values['admin_expense']}' )";
      $query = $sql.$sql_values.$sub_sql;
      $dbConnect->mysql->query($query);
      $index++;
    }
    if ($index == 0) {
      $result = json_encode(array('status' => false , 'message' => '登録を失敗しました。')); 
    } else {
      $result = json_encode(array('status' => true));  
    }
  } else {
    $result = json_encode(array('status' => true));  
  }
} catch(Exception $e) {
  $rootPath = $_SERVER['DOCUMENT_ROOT'].$root_folder;
  $time = date('Y/m/d-H:i');
  $logDate = date('Ymd');
  $path = $rootPath."error_".$logDate.".txt";
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, adminEditSchedule, $e\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => '登録にエラーが発生しました。'));
}
$dbConnect->dbClose();
echo($result);
?>