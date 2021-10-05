<?php
  // $data = json_decode($_POST['item']);
  $data = json_decode(file_get_contents('php://input'), true);

  echo(json_encode(['status' => 'true', 'data'=> $data]));
?>