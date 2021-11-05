<?php
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $data = $dbConnect->getClient();

  if (!empty($data)) {
    $result = json_encode(array('status' => 'success' , 'data' => $data));
  } else {
    $result = json_encode(array('status' => 'error'));
  }
} catch(Exception $e) {
  $result = json_encode(array('status' => 'error' , 'message' => '検索に失敗しました。'));
}
$dbConnect->dbClose();
echo($result);
?>