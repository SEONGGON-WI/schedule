<?php
$response = json_decode(file_get_contents('php://input'), true);
$start_date = $response['start_date'];
$end_date = $response['end_date'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $data = $dbConnect->getCsv($start_date, $end_date);
  $dbConnect->dbClose();

  if (!empty($data)) {
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=チェックルールマスタ.csv");
    header("Content-Transfer-Encoding: binary");

    $csv = '"日付","案件名","名前","出勤","退勤","時間","時間(30分=0,5)","日數","時給","日給","残業代","月給","交通費",,"時間","日數","時給","日給","月給","交通費"'. "\r\n";
    $csv = mb_convert_encoding($csv, 'SJIS', 'UTF-8');

    foreach ($data as $value) {
      if ($value['admin_day_salary']) {
        $admin_total_salary = (float)$value['admin_day_salary'] * (int)$value['cnt'];
      } else {
        $admin_total_salary = '';
      }
      if ($value['staff_day_salary']) {
        $staff_total_salary = (float)$value['staff_day_salary'] * (int)$value['cnt'];
      } else {
        $staff_total_salary = '';
      }

      if ($value['staff_total_expense'] == 0) {
        $value['staff_total_expense'] = '';
      }

      if ($value['admin_total_expense'] == 0) {
        $value['admin_total_expense'] = '';
      }
      
      $csv .= '"'
          . $value['day'] . '","'
          . mb_convert_encoding($value['agenda'], 'SJIS', 'UTF-8') . '","'
          . mb_convert_encoding($value['name'], 'SJIS', 'UTF-8') . '","'
          . $value['start_time'] . '","'
          . $value['end_time'] . '","'
          . $value['total_time'] . '","'
          . $value['total_time'] . '","'
          . $value['cnt'] . '","'
          . $value['admin_hour_salary'] . '","'
          . $value['admin_day_salary'] . '","'
          . '","'
          . $admin_total_salary . '","'
          . $value['admin_total_expense'] . '","'
          . '","'
          . $value['total_time'] . '","'
          . $value['cnt'] . '","'
          . $value['staff_hour_salary'] . '","'
          . $value['staff_day_salary'] . '","'
          . $staff_total_salary . '","'
          . $value['staff_total_expense'] . '"'
          . "\r\n";
    }
    echo $csv;
    return;
  }
} catch(Exception $e) {
  return;
}
?>