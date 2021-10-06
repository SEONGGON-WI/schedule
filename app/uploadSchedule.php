<?php
$response = json_decode(file_get_contents('php://input'), true);

$name = $response['name'];
$password = $response['password'];
$event = $response['event'];

require 'sqlConnect.php';
$dbConnect = new mysqlConnect();
$result = $dbConnect->uploadSchedule($name, $password, $event);
$dbConnect->dbClose();

echo(json_encode(['status' => 'true', 'data'=> $event]));
// echo($event[0]['date']);
?>