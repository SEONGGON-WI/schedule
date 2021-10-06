<?php
$name = $_POST['name'];
$password = $_POST['password'];
$date = $_POST['date'];

$date = explode(',', $date);

require 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $managerPassword= $dbConnect->getManagerPassword($name);

  if (empty($managerPassword)) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $query = "INSERT into manager values";
    $query = $query."('$name', '$hashedPassword')";
    $dbConnect->mysql->query($query);
    $managerPassword = $hashedPassword;
  }
  $hashedPassword = $managerPassword;

  if (password_verify($password, $hashedPassword)) {
    $data = $dbConnect->getSchedule($name);

    $index = 0;
    $sql_array = [];
    if(empty($data)) {
      foreach ($date as $value) {
        $sql_array[$index] = "( '{$name}', '{$value}', '{$comment[$index]}' )";
        $index++;
      }
    } else {
      $exist = false;
      foreach ($date as $value) {
        foreach ($data as $e) {
          if ($e['comment'] != '') {
            $sql_array[$index] = "( '{$name}', '{$value}', '{$e['comment']}' )";
            $exist = true;
            $index++;
            continue;
          }
        }
        if ($exist) {
          $exist = false;
          continue;
        }
        $sql_array[$index] = "( '{$name}', '{$value}', '{$comment[$index]}' )";
        $index++;
      }
      $del = "DELETE FROM work_table WHERE name ='$name'";
      $dbConnect->mysql->query($del);
    }

    $sql = "INSERT INTO work_table ( name, date, comment ) VALUES";
    $sub_sql = implode(', ', $sql_array);
    $sql .= $sub_sql;
    $dbConnect->mysql->query($sql);
    $result = json_encode(array('status' => 'success' , 'message' => '登録が完了しました。'));
  } else {
    $result = json_encode(array('status' => 'error' , 'message' => 'パスワードを確認してください。'));
  }
} catch(Exception $e) {
  $result = json_encode(array('error' => 'error' , 'message' => '登録が失敗しました。'));
}

$dbConnect->dbClose();
echo($result);
?>