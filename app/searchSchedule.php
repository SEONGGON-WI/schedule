<?php

$name = $_POST['name'];
$password = $_POST['password'];

require 'sqlConnect.php';

$dbConnect = new mysqlConnect();

$result = $dbConnect->getByNameScheduleTable($name, $password);
$dbConnect->dbClose();

echo($result);
?>