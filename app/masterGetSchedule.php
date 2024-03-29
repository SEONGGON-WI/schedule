<?php
include 'defaultValue.php';
$response = json_decode(file_get_contents('php://input'), true);
$start_date = $response['start_date'];
$end_date = $response['end_date'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $data = $dbConnect->getAdmin($start_date, $end_date);
  if (!empty($data)) {
    $result = json_encode(array('status' => true , 'data' => $data));
  } else {
    $result = json_encode(array('status' => true , 'data' => ''));
  }

  $month = date('m-');
  $time = (int)date('H');
  if ($time < 5 && $time >= 0) {
    $fileName = $month."18_backup.json";
    $deleteName = $month."6_backup.json";
  } else if ($time < 18 && $time >= 6) {
    $fileName = $month."6_backup.json";
    $deleteName = $month."18_backup.json";
  } else {
    $fileName = $month."18_backup.json";
    $deleteName = $month."6_backup.json";
  }

  $rootPath = $_SERVER['DOCUMENT_ROOT'].$root_folder;
  $backup_path = $rootPath.$fileName;
  $delete_path = $rootPath.$deleteName;

  if (!file_exists($backup_path)) {
    $time1 = date('Y-m-',strtotime("-1 month"));
    $time2 = date('Y-m-');
    $current_start = $time1.'01';
    $current_end = $time2.'31';
    $backup_data = $dbConnect->getAdmin($current_start, $current_end);
    $json_data = json_encode($backup_data);
    $log = @fopen($backup_path,"w+");
    @fwrite($log,"$json_data");
    @fclose($log);

    if (file_exists($delete_path)) {
      unlink($delete_path);
    }
  }
} catch(Exception $e) {
  $rootPath = $_SERVER['DOCUMENT_ROOT'].$root_folder;
  $time = date('Y/m/d-H:i');
  $logDate = date('Ymd');
  $path = $rootPath."error_".$logDate.".txt";
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, masterGetSchedule, $e\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => 'データ更新にエラーが発生しました。'));
}
$dbConnect->dbClose();
echo($result);
?>