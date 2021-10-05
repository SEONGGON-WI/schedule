<?php

class mysqlConnect {
  protected $host = 'mysql5005.xserver.jp';
  protected $user = 'kusimusou_admin';
  protected $password = 'Nao0919722832';
  protected $dbName = 'kusimusou_schedule';

  public function __construct() {
    $this->mysql = new mysqli($this->host, $this->user, $this->password, $this->dbName);
  }

  public function dbClose() {
    $this->mysql->close();
  }

  public function getScheduleTable() {
    $query = "SELECT name FROM schedule";
    $result = $this->mysql->query($query);
    $status = false;

    $i = 0;
    if ($result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
        $table[$i] = $row;
        $i++;
      }
    }
    if (isset($table)) {
      $status = true;
    }
    return $status;
  }

  public function getByNameScheduleTable($name, $password) {
    $query = "SELECT * FROM schedule WHERE name = '$name'";
    $result = $this->mysql->query($query);
    if ($result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
        $data = $row;
      }
    }

    if ( !isset($data) ) {
      return json_encode(array('status' => 'info' , 'message' => '検索されたデータがありません。'));
    }
    $hashedPassword = $data['password'];
    if (password_verify($password, $hashedPassword)) {
      return json_encode(array('status' => 'info' , 'data' => $data['data']));
    }
    return json_encode(array('status' => 'error' , 'message' => 'パスワードを確認してください。'));
  }

  public function addSchedule($name, $password, $data) {
    try {
      $result = $this->getScheduleTable();
      if (!$result) {
        $this->createScheduleTable();
      }
      $query = "SELECT password FROM schedule WHERE name = '$name'";
      $result = $this->mysql->query($query);
      if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
          $table = $row;
        }
      }

      if ( !isset($table) ) {
        $hashedPassword = password_hash( $password, PASSWORD_DEFAULT);
        $query = "INSERT into schedule values";
        $query = $query."('$name', '$hashedPassword', '$data')";
        $this->mysql->query($query);
      } else {
        $hashedPassword = $table['password'];
        if(password_verify($password, $hashedPassword)) {
          $query = "UPDATE schedule SET data = '$data' WHERE name = '$name'";
          $this->mysql->query($query);
        } else {
          return json_encode(array('status' => 'error' , 'message' => 'パスワードを確認してください。'));
        }
      }
      return json_encode(array('status' => 'success' , 'message' => '登録が完了しました。'));

    } catch(Exception $e) {
      return json_encode(array('error' => 'error' , 'message' => '登録が失敗しました。'));
    }
  }

  public function createScheduleTable() {
    $query = "CREATE TABLE IF NOT EXISTS schedule (";
    $query = $query."name varchar(64) not null,";
    $query = $query."password varchar(64) not null,";
    $query = $query."data LONGTEXT not null,";
    $query = $query."primary key(name));";
    $this->mysql->query($query);
  }
}
?>
