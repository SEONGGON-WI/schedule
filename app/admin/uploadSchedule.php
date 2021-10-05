<?php

$name = $_POST['name'];
$data = $_POST['data'];

require 'sqlConnect.php';

$dbConnect = new mysqlConnect();

if ( $data == '[]' ) {
    $result = $dbConnect->deleteSchedule($name);
} else {
    $result = $dbConnect->editSchedule($name, $data);
}
$dbConnect->dbClose();
echo($result);
?>