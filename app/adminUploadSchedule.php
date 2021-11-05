<?php
$response = json_decode(file_get_contents('php://input'), true);
$client = $response['client'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  foreach ($client as $values) {
    $sql = "UPDATE schedule SET client = '{$values['client']}' WHERE agenda = '{$values['agenda']}'";
    $dbConnect->mysql->query($sql);
  }
  $result = json_encode(array('status' => 'success' , 'message' => '登録を完了しました。'));
} catch(Exception $e) {
  $result = json_encode(array('status' => 'error' , 'message' => '登録を失敗しました。'));
}
$dbConnect->dbClose();
echo($result);
?>