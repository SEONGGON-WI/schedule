<?php
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $data = $dbConnect->getStaff($start_date, $end_date);

  if (!empty($data)) {
    $result = json_encode(array('status' => 'success' , 'data' => $data));
  }
} catch(Exception $e) {
  $result = json_encode(array('error' => 'error' , 'message' => '検索に失敗しました。'));
}
$dbConnect->dbClose();
echo($result);
?>