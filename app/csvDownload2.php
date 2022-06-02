<?php
include 'defaultValue.php';
$response = json_decode(file_get_contents('php://input'), true);
$start_date = $response['start_date'];
$end_date = $response['end_date'];
$client = $response['client'];
$agenda = $response['agenda'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $data = $dbConnect->getCsv2($start_date, $end_date, $client, $agenda);

  if (!empty($data)) {
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=チェックルールマスタ.csv");
    header("Content-Transfer-Encoding: binary");

    $csv = '"摘要","数量","単位","数量","単位","単価","非課税","金額","備考"'. "\r\n";
    $csv = mb_convert_encoding($csv, 'SJIS', 'UTF-8');

    $csv .= '"'
        . '","'
        . '","'
        . '","'
        . '","'
        . '","'
        . '","'
        . '","'
        . '","'
        . '"'
        . "\r\n";

    $total_salary = 0;
    foreach ($data as $value) {
      $day = [];
      $day = explode("-", $value['date']);
      if ((int)$day[1] < 10) {
        $day[1] = substr($day[1], 1);
      }
      if ((int)$day[2] < 10) {
        $day[2] = substr($day[2], 1);
      }
      $working_day = $day[1]."/".$day[2];

      $admin_salary = '';
      $admin_non_tax_salary = '';

      if ($value['tax_status'] != 1) {
        if ($value['admin_day_salary'] != '') {
          $admin_salary = number_format((int)$value['admin_day_salary']);
        }
      } else {
        if ($value['admin_day_salary'] != '') {
          $admin_non_tax_salary = number_format((int)$value['admin_day_salary']);
        }
      }

      if ($value['admin_day_salary'] != '') {
        $admin_total_salary = number_format((int)$value['admin_day_salary'] * (int)$value['cnt']);
        $total_salary += (int)$value['admin_day_salary'] * (int)$value['cnt'];
      } else {
        $admin_total_salary = '';
      }

      $csv .= '"'
          . mb_convert_encoding($value['agenda'], 'SJIS', 'UTF-8') . '","'
          . $value['cnt'] . '","'
          . mb_convert_encoding('名', 'SJIS', 'UTF-8') . '","'
          . '"1,"'
          . mb_convert_encoding('日', 'SJIS', 'UTF-8') . '","'
          . $admin_salary . '","'
          . $admin_non_tax_salary . '","'
          . $admin_total_salary . '","'
          . "'". $working_day . '"'
          . "\r\n";
    }

    $csv .= '"'
        . '","'
        . '","'
        . '","'
        . '","'
        . '","'
        . '","'
        . '","'
        . '","'
        . '"'
        . "\r\n";

    $csv .= '"'
        . '","'
        . '","'
        . '","'
        . '","'
        . '","'
        . '","'
        . '","'
        . number_format($total_salary) . '","'
        . '"'
        . "\r\n";
  
    $csv .= '"'
        . '","'
        . '","'
        . '","'
        . '","'
        . '","'
        . '","'
        . '","'
        . '","'
        . '"'
        . "\r\n";

    $data = $dbConnect->getCsv3($start_date, $end_date, $client);
    if (!empty($data)) {
      foreach ($data as $value) {
        $sql = "SELECT date FROM schedule ";
        $sql = $sql."WHERE client = '$client' AND date >= '$start_date' AND date <= '$end_date' AND agenda = '{$value['agenda']}' AND admin_expense = '{$value['admin_expense']}' ";
        $sql = $sql."ORDER BY date";
        $table = [];
        $result = $dbConnect->mysql->query($sql);
        if ($result->num_rows > 0) {
          $index = 0;
          while($row = $result->fetch_assoc()) {
            $table[$index] = $row;
            $index++;
          }
        }
        $month = explode("-", $start_date);
        if ((int)$month[1] < 10) {
          $month[1] = substr($month[1], 1);
        }
        $working_day = $month[1]."/";
        foreach ($table as $element) {
          $day = [];
          $day = explode("-", $element['date']);
          if ((int)$day[2] < 10) {
            $day[2] = substr($day[2], 1);
          }
          $working_day .= $day[2].",";
        }
        $working_day = substr($working_day, 0, -1);
        if ($value['admin_expense'] != '' ) {
          $admin_expense = number_format((int)$value['admin_expense'] * (int)$value['cnt']);
        } else {
          $admin_expense = '';
        }
        $csv .= '"'
            . '","'
            . $value['cnt'] . '","'
            . mb_convert_encoding('名', 'SJIS', 'UTF-8') . '","'
            . '","'
            . '","'
            . '","'
            . $admin_expense . '","'
            . "'". $working_day . '","'
            . '"'
            . "\r\n";
      }
    }

    $dbConnect->dbClose();
    echo $csv;
    return;
  }
} catch(Exception $e) {
  $rootPath = $_SERVER['DOCUMENT_ROOT'].$root_folder;
  $time = date('Y/m/d-H:i');
  $logDate = date('Ymd');
  $path = $rootPath."error_".$logDate.".txt";
  if($search_condition == true) {
    $condition = "search";
  } else {
    $condition = "";
  }
  $log = @fopen($path,"a+");
  @fwrite($log,"$time, CSVDownload, $e\n");
  @fclose($log);
  return;
}
?>