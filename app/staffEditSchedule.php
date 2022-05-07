<?php
include 'defaultValue.php';
$response = json_decode(file_get_contents('php://input'), true);
$name = $response['name'];
$password = $response['password'];
$date = $response['date'];
$event= $response['event'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $managerData = $dbConnect->getPassword($name);

  if (empty($managerData)) {
    $result = json_encode(array('status' => false , 'message' => '名前、パスワードを確認してください。'));
  } else {
    $hashedPassword = $managerData['password'];
    if (password_verify($password, $hashedPassword)) {
      $rootPath = $_SERVER['DOCUMENT_ROOT'].$root_folder;
      $time = date('Y/m/d-H:i');
      $logDate = date('Ymd');
      $path = $rootPath.$logDate.".txt";
      $log = @fopen($path,"a+");
      @fwrite($log,"$time, staffEditSchedule, $name, $date\n");
      @fclose($log);

      if ($event != []) {
        foreach ($event as $values) {
          $sql = "UPDATE schedule SET start_time = '{$values['start_time']}', end_time ='{$values['end_time']}', total_time = '{$values['total_time']}', admin_total_time = '{$values['admin_total_time']}', admin_day_salary = '{$values['admin_day_salary']}', staff_hour_salary = '{$values['staff_hour_salary']}', staff_day_salary = '{$values['staff_day_salary']}', staff_expense = '{$values['staff_expense']}' WHERE _id = '{$values['_id']}' AND name = '{$values['name']}' AND date = '{$values['date']}'";
          $dbConnect->mysql->query($sql);
        }
        $result = json_encode(array('status' => false , 'message' => '登録を完了しました。'));
      } else {
        $result = json_encode(array('status' => true, 'message' => '登録にエラーが発生しました。'));  
      }
    } else {
      $result = json_encode(array('status' => false , 'message' => 'パスワードを確認してください。'));
    }
  }
} catch(Exception $e) {
  $rootPath = $_SERVER['DOCUMENT_ROOT'].$root_folder;
  $time = date('Y/m/d-H:i');
  $logDate = date('Ymd');
  $path = $rootPath."error_".$logDate.".txt";
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, staffEditSchedule, $e\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => '登録にエラーが発生しました。'));
}
$dbConnect->dbClose();
echo($result);
?>