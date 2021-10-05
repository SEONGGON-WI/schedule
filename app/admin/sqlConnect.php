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

  public function getByNameScheduleTable($name) {
    $query = "SELECT data FROM schedule WHERE name = '$name'";
    $result = $this->mysql->query($query);

    if ($result->num_rows > 0) {
      return true;
    }
    
    return false;
  }

  public function getSchedule() {
    $query = "SELECT name, data FROM schedule";
    $result = $this->mysql->query($query);

    $i = 0;
    if ($result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
        $data[$i] = $row;
        $i++;
      }
    }
    if ( !isset($data) ) {
      return false;
    }
    return json_encode(array('data' => $data));
  }

  public function editSchedule($name, $data) {
    $status = false;
    try {
      $result = $this->getByNameScheduleTable($name);

      if ($result) {
        $query = "UPDATE schedule SET data = '$data' WHERE name = '$name'";
        $this->mysql->query($query);
      }
      $status = true;
    } catch(Exception $e) {
      $status = false;
    }
    return $status;
  }

  public function deleteSchedule($name) {
    $result = $this->getByNameScheduleTable($name);
    if($result) {
      $query = "DELETE FROM schedule WHERE name = '$name'";
      $this->mysql->query($query);
    }
    return true;
  }
}
?>
