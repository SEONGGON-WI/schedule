<?php
$response = json_decode(file_get_contents('php://input'), true);
$client = $response['client'];
$agenda = $response['agenda'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $del = "DELETE FROM client WHERE client = '$client' AND agenda = '$agenda'";
  $dbConnect->mysql->query($del);
  $result = json_encode(array('status' => 'success' , 'message' => '削除を完了しました。'));
} catch(Exception $e) {
  $result = json_encode(array('status' => 'error' , 'message' => '削除を失敗しました。'));
}
$dbConnect->dbClose();
echo($result);
?>