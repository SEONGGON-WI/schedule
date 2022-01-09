<?php
include 'defaultValue.php';
$response = json_decode(file_get_contents('php://input'), true);
$start_date = $response['start_date'];
$end_date = $response['end_date'];
$client = $response['client'];
include 'sqlConnect.php';
try {
  $rootPath = $_SERVER['DOCUMENT_ROOT'].$root_folder;
  $time = date('Y/m/d-H:i');
  $logDate = date('Ymd');
  $path = $rootPath."error_".$logDate.".txt";
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, adminDeleteClient, $client, $start_date\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => '削除にエラーが発生しました。'));
  $dbConnect = new mysqlConnect();
  $sql = "UPDATE schedule SET admin_hour_salary = '', admin_day_salary = '', client = '' WHERE client = '$client' AND date >= '$start_date' AND date <= '$end_date'";
  $dbConnect->mysql->query($sql);
  $del = "DELETE FROM client WHERE client = '$client' AND date = '$start_date'";
  $dbConnect->mysql->query($del);
  $result = json_encode(array('status' => true));
} catch(Exception $e) {
  $rootPath = $_SERVER['DOCUMENT_ROOT'].$root_folder;
  $time = date('Y/m/d-H:i');
  $logDate = date('Ymd');
  $path = $rootPath."error_".$logDate.".txt";
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, adminDeleteClient, $e\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => '削除にエラーが発生しました。'));
}
$dbConnect->dbClose();
echo($result);
?>