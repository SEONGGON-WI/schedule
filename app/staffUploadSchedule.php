<?php
include 'defaultValue.php';
$response = json_decode(file_get_contents('php://input'), true);
$name = $response['name'];
$password = $response['password'];
$search_condition = $response['search_condition'];
$start_date = $response['start_date'];
$end_date = $response['end_date'];
$event = $response['event'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $managerData = $dbConnect->getPassword($name);

  if (empty($managerData)) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $query = "INSERT into manager ( name, password ) values";
    $query = $query."('$name', '$hashedPassword')";
    $dbConnect->mysql->query($query);
    $managerData['password'] = $hashedPassword;
  }
  $hashedPassword = $managerData['password'];
  if (password_verify($password, $hashedPassword)) {
    $rootPath = $_SERVER['DOCUMENT_ROOT'].$root_folder;
    $time = date('Y/m/d-H:i');
    $logDate = date('Ymd');
    $path = $rootPath.$logDate.".txt";
    if($search_condition == true) {
      $condition = "search";
    } else {
      $condition = "";
    }
    $log = @fopen($path,"a+");
    @fwrite($log,"$time, staffUpload, $name, $condition\n");
    @fclose($log);
    if ($search_condition == true) {
      $dbConnect = new mysqlConnect();
      $data = $dbConnect->getEditSchedule($name, $start_date, $end_date);
      if (!empty($data)) { 
        foreach ($data as $element) {
          $duplicate = false;
          foreach ($event as $values) {
            if ($element['date'] == $values['date']) {
              $duplicate = true;
            }
          }
          if (!$duplicate) {
            $del = "DELETE FROM schedule WHERE name ='$name' AND date = '{$element['date']}'";
            $dbConnect->mysql->query($del);
          }
        }
      }
    }
    if ($event != []) {
      $index = 0;
      foreach ($event as $values) {
        if ($values['_id'] == '' || $values['_id'] == null) {
          $sql = "INSERT INTO schedule ( name , date, overlap ) VALUES ( '{$name}', '{$values['date']}', '1' )";
          $dbConnect->mysql->query($sql);
          $index++;
        }
      }
      if ($index == 0) {
        $index = "empty";
        $result = json_encode(array('status' => false , 'message' => '登録を失敗しました。'));
      } else {
        $result = json_encode(array('status' => true , 'message' => '登録を完了しました。'));  
      }
    } else {
      $del = "DELETE FROM schedule WHERE name = '$name' AND agenda = '' AND date >= '$start_date' AND date <= '$end_date'";
      $dbConnect->mysql->query($del);
      $result = json_encode(array('status' => true , 'message' => '登録を完了しました。'));
    }
    $log = @fopen($path,"a+");
    @fwrite($log,"$time, staffUpload, $name, $index\n");
    @fclose($log);

    $time = date('Y-m-d H:i:s', time());
    $time_sql = "UPDATE manager SET access_time = '$time' WHERE name = '$name'";
    $dbConnect->mysql->query($time_sql);
  } else {
    $result = json_encode(array('status' => false , 'message' => 'パスワードを確認してください。'));
  }
} catch(Exception $e) {
  $rootPath = $_SERVER['DOCUMENT_ROOT'].$root_folder;
  $time = date('Y/m/d-H:i');
  $logDate = date('Ymd');
  $path = $rootPath."error_".$logDate.".txt";
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, staffUpload, $e\n");
  @fclose($log);
  $result = json_encode(array('status' => false , 'message' => '登録にエラーが発生しました。'));
}
$dbConnect->dbClose();
echo($result);
?>