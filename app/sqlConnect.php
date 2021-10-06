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

  public function getPassword($name) {
    $query = "SELECT password FROM manager WHERE name = '$name'";
    $result = $this->mysql->query($query);
    if ($result->num_rows > 0) {
      $table = $result->fetch_assoc();
    }

    if (!isset($table)) {
      return;
    }
    return $table['password'];
  }

  public function getSchedule($name) {
    $query = "SELECT * FROM schedule WHERE name = '$name'";
    $result = $this->mysql->query($query);
    if ($result->num_rows > 0) {
      $i = 0;
      while($row = $result->fetch_assoc()) {
        $table[$i] = $row;
        $i++;
      }
    }
    if (!isset($table)) {
      return;
    } 
    return $table;
  }

  public function createManagerTable() {
    $query = "CREATE TABLE IF NOT EXISTS manager (";
    $query = $query."name varchar(32) not null,";
    $query = $query."password varchar(64) not null,";
    $query = $query."primary key(name));";
    $this->mysql->query($query);
  }

  public function createScheduleTable() {
    $query = "CREATE TABLE IF NOT EXISTS schedule (";
    $query = $query."name varchar(32) not null,";
    $query = $query."date DATE not null,";
    $query = $query."comment TEXT not null,";
    $query = $query."start_time TEXT not null,";
    $query = $query."end_time TEXT not null,";
    $query = $query."staff_price TEXT not null,";
    $query = $query."admin_price TEXT not null,";
    $query = $query."primary key(name, date));";
    $this->mysql->query($query);
  }
}
?>
