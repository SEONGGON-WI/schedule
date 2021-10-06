<?php
  // $data = json_decode($_POST['item']);
  $response = json_decode(file_get_contents('php://input'), true);

  

  echo(json_encode(['status' => 'true', 'data'=> $response['name']]));
?>