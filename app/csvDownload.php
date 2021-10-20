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

    $csv = '"案件","名前","出勤時間","退勤時間","勤務時間",",時給","日給","勤務回数","総給与",,"時給","日給","経費","勤務回数","総給与","総経費"'. "\r\n";
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
      if ($value['staff_expense']) {
        $staff_total_expense = (float)$value['staff_expense'] * (int)$value['cnt'];
      } else {
        $staff_total_expense = '';
      }
      
      $csv .= '"'
          . mb_convert_encoding($value['agenda'], 'SJIS', 'UTF-8') . '","'
          . mb_convert_encoding($value['name'], 'SJIS', 'UTF-8') . '","'
          . $value['start_time'] . '","'
          . $value['end_time'] . '","'
          . $value['total_time'] . '","'
          . $value['admin_hour_salary'] . '","'
          . $value['admin_day_salary'] . '","'
          . $value['cnt'] . '","'
          . $admin_total_salary . '","'
          . '","'
          . $value['staff_hour_salary'] . '","'
          . $value['staff_day_salary'] . '","'
          . $value['staff_expense'] . '","'
          . $value['cnt'] . '","'
          . $staff_total_salary . '","'
          . $staff_total_expense . '"'
          . "\r\n";
    }
    echo $csv;
    return;
  }
} catch(Exception $e) {
  return;
}
?>