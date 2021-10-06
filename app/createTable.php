<?php
require 'sqlConnect.php';
$dbConnect = new mysqlConnect();
$dbConnect->createManagerTable();
$dbConnect->createScheduleTable();
$dbConnect->dbClose();
?>