<?php
$response = json_decode(file_get_contents('php://input'), true);
$client = $response['client'];
$start_date = $response['start_date'];
$end_date = $response['end_date'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $sql = "UPDATE schedule SET client = '' WHERE agenda != '' AND date >= '$start_date' AND date <= '$end_date'";
  $dbConnect->mysql->query($sql);
  foreach ($client as $values) {
    $sql = "UPDATE schedule SET client = '{$values['client']}' WHERE agenda = '{$values['agenda']}' AND date >= '$start_date' AND date <= '$end_date'";
    $dbConnect->mysql->query($sql);
  }
  $result = json_encode(array('status' => 'success' , 'message' => '登録を完了しました。'));
} catch(Exception $e) {
  $result = json_encode(array('status' => 'error' , 'message' => '登録を失敗しました。'));
}
$dbConnect->dbClose();
echo($result);
?>