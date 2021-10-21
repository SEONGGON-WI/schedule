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
    $query = "SELECT password, access_time FROM manager WHERE name = '$name'";
    $result = $this->mysql->query($query);
    if ($result->num_rows > 0) {
      $table = $result->fetch_assoc();
    }
    if (!isset($table)) {
      return;
    }
    return $table;
  }

  public function getSchedule($name) {
    $query = "SELECT name, date, agenda, start_time, end_time, total_time, staff_hour_salary, staff_day_salary, staff_expense FROM schedule WHERE name = '$name' ORDER BY date";
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

  public function getAdmin($start_date, $end_date) {
    $query = "SELECT * FROM schedule WHERE date >= '$start_date' AND date <= '$end_date' ORDER BY name*1, date";
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

  public function getStaff() {
    $query = "SELECT name, access_time FROM manager ORDER BY name*1";
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

  public function getCsv($start_date, $end_date) {
    $sql = "SELECT name, agenda, start_time, end_time, total_time ,admin_hour_salary, admin_day_salary, staff_hour_salary, staff_day_salary, COUNT(name) AS cnt, sum(staff_expense) AS staff_total_expense FROM schedule ";
    $sql = $sql."WHERE date >= '$start_date' AND date <= '$end_date' ";
    $sql = $sql."GROUP BY name, agenda, start_time, end_time, total_time ,admin_hour_salary, admin_day_salary, staff_hour_salary, staff_day_salary ";
    $sql = $sql."ORDER BY agenda, name*1";

    $result = $this->mysql->query($sql);
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
    $query = $query."access_time TIMESTAMP not null DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,";
    $query = $query."primary key(name));";
    $this->mysql->query($query);
  }

  public function createScheduleTable() {
    $query = "CREATE TABLE IF NOT EXISTS schedule (";
    $query = $query."name varchar(32) not null,";
    $query = $query."date DATE not null,";
    $query = $query."agenda TEXT not null,";
    $query = $query."start_time TEXT not null,";
    $query = $query."end_time TEXT not null,";
    $query = $query."total_time TEXT not null,";
    $query = $query."staff_hour_salary TEXT not null,";
    $query = $query."staff_day_salary TEXT not null,";
    $query = $query."staff_expense TEXT not null,";
    $query = $query."admin_hour_salary TEXT not null,";
    $query = $query."admin_day_salary TEXT not null,";
    $query = $query."primary key(name, date));";
    $this->mysql->query($query);
  }
}
?>
