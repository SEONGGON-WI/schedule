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
    $deleteName = '';
    foreach ($remove_event as $values) {
      $del = "DELETE FROM schedule WHERE _id = '{$values['_id']}' AND date = '{$values['date']}' AND name = '{$values['name']}'";
      $dbConnect->mysql->query($del);
      $deleteName = $deleteName.$values['name'].','.$values['agenda'].',';
    }
    $log = @fopen($path,"a+");
    @fwrite($log,"$time, adminEditDeleteSchedule, $deleteName\n");
    @fclose($log);
  }
  $index = 0;
  if ($event != []) {
    foreach ($event as $values) {
      if ($values['_id'] == '') {
        $sql = "INSERT INTO schedule ( name, date, client, agenda, overlap, start_time, end_time, total_time, admin_total_time, staff_hour_salary, staff_day_salary, staff_expense, admin_hour_salary, admin_day_salary , admin_expense ) VALUES ";
        $sql .= "( '{$values['name']}', '{$values['date']}', '{$values['client']}', '{$values['agenda']}', '{$values['overlap']}', '{$values['start_time']}', '{$values['end_time']}', '{$values['total_time']}', '{$values['admin_total_time']}',
                          '{$values['staff_hour_salary']}', '{$values['staff_day_salary']}', '{$values['staff_expense']}', 
                          '{$values['admin_hour_salary']}', '{$values['admin_day_salary']}', '{$values['admin_expense']}' )";
      } else {
        $sql = "UPDATE schedule SET client = '{$values['client']}', agenda = '{$values['agenda']}', overlap = '{$values['overlap']}', start_time = '{$values['start_time']}', end_time = '{$values['end_time']}', total_time = '{$values['total_time']}', admin_total_time = '{$values['admin_total_time']}',
                                                staff_hour_salary = '{$values['staff_hour_salary']}', staff_day_salary = '{$values['staff_day_salary']}', staff_expense = '{$values['staff_expense']}',
                                                admin_hour_salary = '{$values['admin_hour_salary']}', admin_day_salary = '{$values['admin_day_salary']}', admin_expense = '{$values['admin_expense']}' 
                WHERE _id = '{$values['_id']}' AND name = '{$values['name']}' AND date = '{$values['date']}'";
      }
      $dbConnect->mysql->query($sql);
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