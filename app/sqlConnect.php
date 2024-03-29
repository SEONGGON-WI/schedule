<?php

class mysqlConnect {
  protected $host = 'mysql5005.xserver.jp';
  protected $user = 'kusimusou_admin';
  protected $password = 'Nao0919722832';
  // protected $dbName = 'kusimusou_schedule';
  protected $dbName = 'kusimusou_management';

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

  public function getSchedule($name, $start_date, $end_date) {
    $query = "SELECT _id, name, date, agenda, start_time, end_time, total_time, admin_total_time, admin_hour_salary, admin_day_salary, staff_hour_salary, staff_day_salary, staff_expense FROM schedule WHERE name = '$name' AND date >= '$start_date' AND date <= '$end_date' ORDER BY date";
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

  public function getEditSchedule($name, $start_date, $end_date) {
    $query = "SELECT _id, name, date FROM schedule WHERE name = '$name' AND agenda = '' AND date >= '$start_date' AND date <= '$end_date' ORDER BY date";
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
    $query = "SELECT * FROM schedule WHERE date >= '$start_date' AND date <= '$end_date' ORDER BY date, name";
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

  public function getEdit($date) {
    $query = "SELECT * FROM schedule WHERE date = '$date' ORDER BY date, name";
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

  public function getClient($start_date) {
    $query = "SELECT client, agenda, hour_salary, day_salary, staff_hour_salary, staff_day_salary FROM client WHERE date = '$start_date' ORDER BY client, agenda";
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
    $query = "SELECT name, access_time FROM manager ORDER BY name";
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
    $sql = "SELECT status, tax_status, name, date, client, agenda, overlap, start_time, end_time, total_time, admin_total_time, admin_hour_salary, admin_day_salary, admin_expense, staff_hour_salary, staff_day_salary, total_time, staff_expense FROM schedule ";
    $sql = $sql."WHERE date >= '$start_date' AND date <= '$end_date' AND agenda != '' AND admin_day_salary != '' ";
    $sql = $sql."ORDER BY client, agenda, date, name";

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

  public function getCsv2($start_date, $end_date, $client, $agenda) {
    $sql = "SELECT tax_status, date, client, agenda, admin_day_salary, admin_hour_salary, admin_total_time, SUM(overlap) AS cnt FROM schedule ";
    if ($agenda == '') {
      $sql = $sql."WHERE client = '$client' AND date >= '$start_date' AND date <= '$end_date' AND agenda != '' AND admin_day_salary != '' ";
    } else if ($client == '') {
      $sql = $sql."WHERE agenda = '$agenda' AND date >= '$start_date' AND date <= '$end_date' AND agenda != '' AND admin_day_salary != '' ";
    } else {
      $sql = $sql."WHERE client = '$client' AND agenda = '$agenda' AND date >= '$start_date' AND date <= '$end_date' AND agenda != '' admin_day_salary != '' ";
    }
    $sql = $sql."GROUP BY tax_status, date, agenda, admin_day_salary, admin_hour_salary ";
    $sql = $sql."ORDER BY client, agenda, date";

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

  public function getCsv3($start_date, $end_date, $client, $agenda) {
    $sql = "SELECT client, agenda, admin_expense, SUM(overlap) AS cnt FROM schedule ";
    if ($agenda == '') {
      $sql = $sql."WHERE client = '$client' AND date >= '$start_date' AND date <= '$end_date' AND agenda != '' AND admin_expense != '' ";
    } else if ($client == '') {
      $sql = $sql."WHERE agenda = '$agenda' AND date >= '$start_date' AND date <= '$end_date' AND admin_expense != '' ";
    } else {
      $sql = $sql."WHERE client = '$client' AND agenda = '$agenda' AND date >= '$start_date' AND date <= '$end_date' AND admin_expense != '' ";
    }
    $sql = $sql."GROUP BY agenda, admin_expense ";
    $sql = $sql."ORDER BY date";

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

  public function getCsv4($start_date, $end_date, $client, $agenda) {
    $sql = "SELECT tax_status, date, client, agenda, admin_day_salary, admin_hour_salary, admin_total_time, SUM(overlap) AS cnt FROM schedule ";
    if ($agenda == '') {
      $sql = $sql."WHERE client = '$client' AND date >= '$start_date' AND date <= '$end_date' AND agenda != '' AND admin_day_salary != '' ";
    } else if ($client == '') {
      $sql = $sql."WHERE agenda = '$agenda' AND date >= '$start_date' AND date <= '$end_date' AND admin_day_salary != '' ";
    } else {
      $sql = $sql."WHERE client = '$client' AND agenda = '$agenda' AND date >= '$start_date' AND date <= '$end_date' AND admin_day_salary != '' ";
    }
    $sql = $sql."GROUP BY tax_status, agenda, admin_day_salary, admin_hour_salary ";
    $sql = $sql."ORDER BY client, agenda, date";

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
    $query = $query."_id INT auto_increment ,";
    $query = $query."status BOOLEAN not null,";
    $query = $query."tax_status BOOLEAN not null,";
    $query = $query."name varchar(32) not null,";
    $query = $query."date DATE not null,";
    $query = $query."client TEXT not null,";
    $query = $query."agenda varchar(64) not null,";
    $query = $query."overlap TINYINT not null,";
    $query = $query."start_time varchar(5) not null,";
    $query = $query."end_time varchar(5) not null,";
    $query = $query."total_time varchar(5) not null,";
    $query = $query."admin_total_time varchar(5) not null,";
    $query = $query."staff_hour_salary TEXT not null,";
    $query = $query."staff_day_salary TEXT not null,";
    $query = $query."staff_expense TEXT not null,";
    $query = $query."admin_hour_salary TEXT not null,";
    $query = $query."admin_day_salary TEXT not null,";
    $query = $query."admin_expense TEXT not null,";
    $query = $query."primary key(_id));";
    $this->mysql->query($query);
  }

  public function createClientTable() {
    $query = "CREATE TABLE IF NOT EXISTS client (";
    $query = $query."date DATE not null,";
    $query = $query."client varchar(32) not null,";
    $query = $query."agenda varchar(64) not null,";
    $query = $query."hour_salary TEXT not null,";
    $query = $query."day_salary TEXT not null,";
    $query = $query."staff_hour_salary TEXT not null,";
    $query = $query."staff_day_salary TEXT not null,";
    $query = $query."primary key(date, client, agenda));";
    $this->mysql->query($query);
  }
}
?>
