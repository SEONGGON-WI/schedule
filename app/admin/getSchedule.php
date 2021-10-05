<?php
require 'sqlConnect.php';

$dbConnect = new mysqlConnect();

$result = $dbConnect->getSchedule();
$dbConnect->dbClose();

echo($result);
?>