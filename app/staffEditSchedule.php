<?php
$response = json_decode(file_get_contents('php://input'), true);
$name = $response['name'];
$password = $response['password'];
$date = $response['date'];
$start_time = $response['start_time'];
$end_time = $response['end_time'];
$total_time = $response['total_time'];
$admin_total_time = $response['admin_total_time'];
$staff_hour_salary = $response['staff_hour_salary'];
$staff_day_salary = $response['staff_day_salary'];
$staff_expense = $response['staff_expense'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $managerData = $dbConnect->getPassword($name);

  if (empty($managerData)) {
    $result = json_encode(array('status' => false , 'message' => '名前、パスワードを確認してください。'));
  } else {
    $hashedPassword = $managerData['password'];
    if (password_verify($password, $hashedPassword)) {
      $rootPath = $_SERVER['DOCUMENT_ROOT'].'/schedule/log/';
      $time = date('Y/m/d-H:i');
      $logDate = date('Ymd');
      $path = $rootPath.$logDate.".txt";
      $log = @fopen($path,"a+");
      @fwrite($log,"$time, staffEditSchedule, $name, $date\n");
      @fclose($log);

      $sql = "UPDATE schedule SET start_time = '$start_time', end_time = '$end_time', total_time = '$total_time', admin_total_time = '$admin_total_time', staff_hour_salary = '$staff_hour_salary', staff_day_salary = '$staff_day_salary', staff_expense = '$staff_expense' WHERE name = '$name' AND date = '$date'";
      $dbConnect->mysql->query($sql);
      $result = json_encode(array('status' => true , 'message' => '登録を完了しました。'));
    } else {
      $result = json_encode(array('status' => false , 'message' => 'パスワードを確認してください。'));
    }
  }
} catch(Exception $e) {
  $rootPath = $_SERVER['DOCUMENT_ROOT'].'/schedule/log/';
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