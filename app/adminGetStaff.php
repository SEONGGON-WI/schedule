<?php
include 'defaultValue.php';
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $data = $dbConnect->getStaff();

  if (!empty($data)) {
    $result = json_encode(array('status' => true , 'data' => $data));
  } else {
    $result = json_encode(array('status' => true , 'data' => ''));
  }
} catch(Exception $e) {
  $rootPath = $_SERVER['DOCUMENT_ROOT'].$root_folder;
  $time = date('Y/m/d-H:i');
  $logDate = date('Ymd');
  $path = $rootPath."error_".$logDate.".txt";
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, adminGetStaff, $e\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => 'スタッフ情報獲得にエラーが発生しました。'));
}
$dbConnect->dbClose();
echo($result);
?>