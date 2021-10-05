<?php

$name = $_POST['name'];
$password = $_POST['password'];
$data = $_POST['data'];

require 'sqlConnect.php';

$dbConnect = new mysqlConnect();

$result = $dbConnect->addSchedule($name, $password, $data);
$dbConnect->dbClose();

echo($result);
?>