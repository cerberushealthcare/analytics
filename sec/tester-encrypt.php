<?php
require_once 'php/data/LoginSession.php';
require_once 'php/data/rec/cryptastic.php';
require_once 'php/c/patient-list/PatientList.php';
//
LoginSession::verify_forServer();
?>
<html>
  <head>
  </head>
  <body>
  <pre>
<?php 
switch ($_GET['t']) {
  case '1':
    $r1 = '1234567890123456';
    p_r($r1, 'r1');
    p_r(strlen($r1));
    $r1e = MyCrypt_Auto::encrypt($r1);
    p_r($r1e, 'r1e');
    p_r(strlen($r1e));
    $r1 = '2012-05-02 01:02:03';
    p_r($r1, 'r1');
    p_r(strlen($r1));
    $r1e = MyCrypt_Auto::encrypt($r1);
    p_r($r1e, 'r1e');
    p_r(strlen($r1e));
    exit;   
  case '2':
    $recs = PatientList::get();
    p_r($recs);
    exit;
  case '3':
    $recs = PatientList::search('Qwdej');
    //require_once 'php/cbat/csv-import/6ktest/InputFiles.php';
    //PatientCsv::create();
    exit;
  case '4':
    require_once 'php/cbat/csv-import/6ktest/CsvImport.php';
    CsvImport::exec();
    exit;
  case '5':
    $r1 = 'AB';
    p_r($r1, 'r1');
    $r1h = MyCrypt_Auto::hash($r1);
    p_r($r1h, 'r1h');
    $r1 = 'AB';
    p_r($r1, 'r1');
    $r1h = MyCrypt_Auto::hash($r1);
    p_r($r1h, 'r1h');
    $r1 = 'AC';
    p_r($r1, 'r1');
    $r1h = MyCrypt_Auto::hash($r1);
    p_r($r1h, 'r1h');
    exit;
  case '6':
    $date = '2013-04-25';
    p_r(strtotime($date));
    exit;
  case '7':
    $value = '1' . '1' . 'NaCl';
    p_r(sha1($value));
    exit;
  case '8':
    $name = 'Tatrrxb';
    p_r(nameToInt($name), $name);
    exit;
  case '9':
    $n1 = null;
    $n2 = "Hory";
    p_r(levenshtein($n1, $n2));
    exit;
  case '10':
    $name = 'Tatrrxb';
    $name = 'Frodo';
    $rec = current(Client_Search::fetchAll(3, $name));
    p_r($rec);
    $rec = jsonencode($rec);
    p_r($rec);
    exit;
  case '11':
    $last = 'Tatrrxb';
    $first = 'Tpzi';
    $recs = PatientList::search($last, $first);
    p_r($recs);
    exit;
  case '12':
    $last = 'bamps';
    $first = null;
    $recs = PatientList::search($last, $first);
    p_r($recs);
    exit;
}

function nameToInt($name) {
  $int = 0;
  if (! empty($name)) {
    $n1 = ord(strtoupper(substr($name, 0, 1)));
    $n2 = (strlen($name) > 1) ? ord(strtoupper(substr($name, 1, 1))) : 0;
    $int = $n1 * 128 + $n2;
  }
  return $int;
}
?>
</html>
