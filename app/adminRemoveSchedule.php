<?php
$response = json_decode(file_get_contents('php://input'), true);
$current_date = $response['current_date'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $del = "DELETE FROM schedule WHERE agenda = '' AND date < '$current_date'";
  $dbConnect->mysql->query($del);
  $result = json_encode(array('error' => 'error' , 'message' => '削除を完了しました。'));
} catch(Exception $e) {
  $result = json_encode(array('error' => 'error' , 'message' => '削除を失敗しました。'));
}
$dbConnect->dbClose();
echo($result);
?>