<?php
$response = json_decode(file_get_contents('php://input'), true);
$start_date = $response['start_date'];
$end_date = $response['end_date'];
include 'sqlConnect.php';
try {
  $dbConnect = new mysqlConnect();
  $data = $dbConnect->getCsv($start_date, $end_date);

  if (!empty($data)) {
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=チェックルールマスタ.csv");
    header("Content-Transfer-Encoding: binary");

    $csv = '"日付","クライアント","案件名","名前","出勤","退勤","時間","時間(30分=0,5)","日數","時給","日給","残業代","月給","交通費",,"時間","日數","時給","日給","月給","交通費",,,,"日払い"'. "\r\n";
    $csv = mb_convert_encoding($csv, 'SJIS', 'UTF-8');

    foreach ($data as $value) {
      $sql = "SELECT date FROM schedule ";
      $sql = $sql."WHERE date >= '$start_date' AND date <= '$end_date' AND name = '{$value['name']}' AND agenda = '{$value['agenda']}' AND start_time = '{$value['start_time']}' AND end_time = '{$value['end_time']}' AND total_time = '{$value['total_time']}' AND admin_total_time = '{$value['admin_total_time']}' ";
      $sql = $sql."AND admin_hour_salary = '{$value['admin_hour_salary']}' AND admin_day_salary = '{$value['admin_day_salary']}' AND admin_expense = '{$value['admin_expense']}' ";
      $sql = $sql."AND staff_hour_salary = '{$value['staff_hour_salary']}' AND staff_day_salary = '{$value['staff_day_salary']}' AND staff_expense = '{$value['staff_expense']}' ";
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
      $working_day = '';
      foreach ($table as $element) {
        $day = [];
        $day = explode("-", $element['date']);
        if ((int)$day[2] < 10) {
          $day[2] = substr($day[2], 1);
        }
        $working_day .= $day[2].",";
      }
      // $working_day = substr($working_day, 0, -1);

      if ($value['admin_total_time'] != '') {
        $explode_time = explode(".", $value['admin_total_time']);
        if (strlen($explode_time[1]) != 2) {
          $explode_time[1] = $explode_time[1]."0";
        }
        $clock_time = $explode_time[0].":".((int)$explode_time[1] * 0.6);
      } else {
        $clock_time = '';
      }

      if ($value['admin_day_salary']) {
        $admin_total_salary = (int)$value['admin_day_salary'] * (int)$value['cnt'];
      } else {
        $admin_total_salary = '';
      }
      if ($value['staff_day_salary']) {
        $staff_total_salary = (int)$value['staff_day_salary'] * (int)$value['cnt'];
      } else {
        $staff_total_salary = '';
      }

      if ($value['staff_total_expense'] == 0) {
        $value['staff_total_expense'] = '';
      }

      if ($value['admin_total_expense'] == 0) {
        $value['admin_total_expense'] = '';
      }

      if ($value['admin_hour_salary'] != '' ) {
        $value['admin_hour_salary'] = number_format((int)$value['admin_hour_salary']);
      }
      if ($value['admin_day_salary'] != '' ) {
        $value['admin_day_salary'] = number_format((int)$value['admin_day_salary']);
      }
      if ($admin_total_salary != '' ) {
        $admin_total_salary = number_format((int)$admin_total_salary);
      }
      if ($value['admin_total_expense'] != '' ) {
        $value['admin_total_expense'] = number_format((int)$value['admin_total_expense']);
      }

      if ($value['staff_hour_salary'] != '' ) {
        $value['staff_hour_salary'] = number_format((int)$value['staff_hour_salary']);
      }
      if ($staff_total_salary != '' ) {
        $staff_total_salary = number_format((int)$staff_total_salary);
      }
      if ($value['staff_day_salary'] != '' ) {
        $value['staff_day_salary'] = number_format((int)$value['staff_day_salary']);
      }
      if ($value['staff_total_expense'] != '' ) {
        $value['staff_total_expense'] = number_format((int)$value['staff_total_expense']);
      }   

      if ($value['status'] != 1) {
        $paid = '';
      } else {
        $paid = number_format((int)$value['paid'] * 1000);
      }

      $csv .= '"'
          . $working_day . '","'
          . mb_convert_encoding($value['client'], 'SJIS', 'UTF-8') . '","'
          . mb_convert_encoding($value['agenda'], 'SJIS', 'UTF-8') . '","'
          . mb_convert_encoding($value['name'], 'SJIS', 'UTF-8') . '","'
          . $value['start_time'] . '","'
          . $value['end_time'] . '","'
          . $clock_time . '","'
          . $value['admin_total_time'] . '","'
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
          . $value['staff_total_expense'] . '","'
          . '","'
          . '","'
          . '","'
          . $paid . '"'
          . "\r\n";
    }
    $dbConnect->dbClose();
    echo $csv;
    return;
  }
} catch(Exception $e) {
  $rootPath = $_SERVER['DOCUMENT_ROOT'].'/schedule/log/';
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