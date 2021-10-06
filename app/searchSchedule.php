<?php
$name = $_POST['name'];
$password = $_POST['password'];

require 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $managerPassword= $dbConnect->getManagerPassword($name);

  if (empty($managerPassword)) {
    $result = json_encode(array('status' => 'info' , 'message' => '登録された名前がありません。'));
  } else {
    $hashedPassword = $managerPassword;

    if (password_verify($password, $hashedPassword)) {
      $data = $dbConnect->getSchedule($name);
      if ($data == 0) {
        $result = json_encode(array('status' => 'info' , 'message' => '登録された日程がありません。'));
      } else {
        $result = json_encode(array('status' => 'success' , 'data' => $data));
      }
    } else {
      $result = json_encode(array('status' => 'warning' , 'message' => 'パスワードを確認してください。'));
    }
  }
} catch(Exception $e) {
  $result = json_encode(array('error' => 'error' , 'message' => '検索に失敗しました。'));
}

$dbConnect->dbClose();
echo($result);
?>