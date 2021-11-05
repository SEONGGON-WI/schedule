<?php
$response = json_decode(file_get_contents('php://input'), true);
$client = $response['client'];
$agenda = $response['agenda'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $index = 0;
  $sql_array = [];
  foreach ($agenda as $values) {
    $sql_array[$index] = "( '{$client}', '{$values}' )";
    $index++;
  }
  $sql = "INSERT IGNORE INTO client ( client, agenda ) VALUES";
  $sub_sql_query = implode(', ', $sql_array);
  $sql = $sql.$sub_sql_query;
  $dbConnect->mysql->query($sql);
  $result = json_encode(array('status' => 'success' , 'message' => '登録を完了しました。'));
} catch(Exception $e) {
  $result = json_encode(array('status' => 'error' , 'message' => '登録を失敗しました。'));
}
$dbConnect->dbClose();
echo($result);
?>