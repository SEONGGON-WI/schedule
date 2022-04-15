<?php
include 'defaultValue.php';
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

    $csv = '"日付","クライアント","案件名","名前","開始","終了","時間","時間(30分=0.5)","時給","人数","合計","非課税","経費","総合計",,"時間(30分=0.5)","時給","人数","合計","非課税","経費","日払い","支払い額","利益"'. "\r\n";
    $csv = mb_convert_encoding($csv, 'SJIS', 'UTF-8');

    foreach ($data as $value) {
      $day = explode("-", $value['date']);
      if ((int)$day[2] < 10) {
        $day[2] = substr($day[2], 1);
      }
      $working_day = $day[2].",";

      if ($value['admin_total_time'] != '') {
        $explode_time = explode(".", $value['admin_total_time']);
        if (strlen($explode_time[1]) != 2) {
          $explode_time[1] = $explode_time[1]."0";
        }
        $admin_clock_time = $explode_time[0].":".((int)$explode_time[1] * 0.6);
      } else {
        $admin_clock_time = '';
      }

      $admin_total_salary = '';
      $admin_non_tax_total_salary = '';
      if ($value['tax_status'] != 1) {
        if ($value['admin_day_salary']) {
          $admin_total_salary = (int)$value['admin_day_salary'] * (int)$value['overlap'];
        } else {
          $admin_total_salary = '';
        }
      } else {
        if ($value['admin_day_salary']) {
          $admin_non_tax_total_salary = (int)$value['admin_day_salary'] * (int)$value['overlap'];
        } else {
          $admin_non_tax_total_salary = '';
        }
      }

      $staff_total_salary = '';
      $staff_non_tax_total_salary = '';
      if ($value['tax_status'] != 1) {
        if ($value['staff_day_salary'] != '') {
          $staff_total_salary = (int)$value['staff_day_salary'] * (int)$value['overlap'];
        } else {
          $staff_total_salary = '';
        }
      } else {
        if ($value['staff_day_salary'] != '') {
          $staff_non_tax_total_salary = (int)$value['staff_day_salary'] * (int)$value['overlap'];
        } else {
          $staff_non_tax_total_salary = '';
        }
      }

      if ($value['admin_expense']) {
        $admin_total_expense = (int)$value['admin_expense'] * (int)$value['overlap'];
      } else {
        $admin_total_expense = '';
      }
      if ($value['staff_expense']) {
        $staff_total_expense = (int)$value['staff_expense'] * (int)$value['overlap'];
      } else {
        $staff_total_expense = '';
      }

      $admin_total = 0;
      $staff_total = 0;

      if ($value['admin_hour_salary'] != '' ) {
        $value['admin_hour_salary'] = number_format((int)$value['admin_hour_salary']);
      }
      if ($value['admin_day_salary'] != '' ) {
        $value['admin_day_salary'] = number_format((int)$value['admin_day_salary']);
      }

      if ($admin_total_salary != '') {
        $admin_total = $admin_total_salary;
        $admin_total_salary = number_format((int)$admin_total_salary);
      } else if ($admin_non_tax_total_salary != '') {
        $admin_total = $admin_non_tax_total_salary;
        $admin_non_tax_total_salary = number_format((int)$admin_non_tax_total_salary);
      }
      
      if ($admin_total_expense != '' ) {
        $admin_total = $admin_total + $admin_total_expense;
        $admin_total_expense = number_format((int)$admin_total_expense);
      }

      if ($value['staff_hour_salary'] != '' ) {
        $value['staff_hour_salary'] = number_format((int)$value['staff_hour_salary']);
      }
      if ($value['staff_day_salary'] != '' ) {
        $value['staff_day_salary'] = number_format((int)$value['staff_day_salary']);
      }

      if ($staff_total_salary != '') {
        $staff_total = $staff_total_salary;
        $staff_total_salary = number_format((int)$staff_total_salary);
      } else if ($admin_non_tax_total_salary != '') {
        $staff_total = $staff_non_tax_total_salary;
        $staff_non_tax_total_salary = number_format((int)$staff_non_tax_total_salary);
      }

      if ($staff_total_expense != '' ) {
        $staff_total = $staff_total + $staff_total_expense;
        $staff_total_expense = number_format((int)$staff_total_expense);
      }   

      if ($value['status'] != 1) {
        $paid = '';
      } else {
        $staff_total = $staff_total - (1000 * (int)$value['overlap']);
        $paid =  number_format(-1000);
      }
      $profit = number_format($admin_total - $staff_total);
      $admin_total = number_format((int)$admin_total);
      $staff_total = number_format((int)$staff_total);

      $csv .= '"'
          . $working_day . '","'
          . mb_convert_encoding($value['client'], 'SJIS', 'UTF-8') . '","'
          . mb_convert_encoding($value['agenda'], 'SJIS', 'UTF-8') . '","'
          . mb_convert_encoding($value['name'], 'SJIS', 'UTF-8') . '","'
          . $value['start_time'] . '","'
          . $value['end_time'] . '","'
          . $admin_clock_time . '","'
          . $value['admin_total_time'] . '","'
          . $value['admin_hour_salary'] . '","'
          . $value['overlap'] . '","'
          . $admin_total_salary . '","'
          . $admin_non_tax_total_salary . '","'
          . $admin_total_expense . '","'
          . $admin_total . '","'
          . '","'
          . $value['total_time'] . '","'
          . $value['staff_hour_salary'] . '","'
          . $value['overlap'] . '","'
          . $staff_total_salary . '","'
          . $staff_non_tax_total_salary . '","'
          . $staff_total_expense . '","'
          . $paid . '","'
          . $staff_total . '","'
          . $profit . '"'
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