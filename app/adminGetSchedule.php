<?php
$response = json_decode(file_get_contents('php://input'), true);
$start_date = $response['start_date'];
$end_date = $response['end_date'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $data = $dbConnect->getAdmin($start_date, $end_date);

  if (empty($data)) {
    $result = json_encode(array('status' => 'warning' , 'message' => '登録された日程がありません。'));
  } else {
    $result = json_encode(array('status' => 'success' , 'data' => $data));
  }
} catch(Exception $e) {
  $result = json_encode(array('error' => 'error' , 'message' => '検索に失敗しました。'));
}
$dbConnect->dbClose();
echo($result);
?>