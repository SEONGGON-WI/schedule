<?php
include 'defaultValue.php';
$response = json_decode(file_get_contents('php://input'), true);
$start_date = $response['start_date'];
$end_date = $response['end_date'];
$client = $response['client'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $data = $dbConnect->getCsv2($start_date, $end_date, $client);

  if (!empty($data)) {
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=チェックルールマスタ.csv");
    header("Content-Transfer-Encoding: binary");

    $csv = '"摘要","種別","数量","単位","数量","単位","単価","金額","備考"'. "\r\n";
    $csv = mb_convert_encoding($csv, 'SJIS', 'UTF-8');

    foreach ($data as $value) {
      $day = [];
      $day = explode("-", $value['date']);
      $working_day = $day[1]."-".$day[2];
      if ($value['admin_day_salary']) {
        $admin_total_salary = (int)$value['admin_day_salary'] * (int)$value['cnt'];
      } else {
        $admin_total_salary = '';
      }
      if ($value['admin_day_salary'] != '' ) {
        $value['admin_day_salary'] = number_format((int)$value['admin_day_salary']);
      }
      if ($admin_total_salary != '' ) {
        $admin_total_salary = number_format((int)$admin_total_salary);
      }

      $csv .= '"'
          . mb_convert_encoding($value['agenda'], 'SJIS', 'UTF-8') . '","'
          . '","'
          . $value['cnt'] . '","'
          . mb_convert_encoding('名', 'SJIS', 'UTF-8') . '","'
          . '"1,"'
          . mb_convert_encoding('日', 'SJIS', 'UTF-8') . '","'
          . $value['admin_day_salary'] . '","'
          . $admin_total_salary . '","'
          . "'". $working_day . '"'
          . "\r\n";
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