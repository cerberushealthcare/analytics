<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once 'php/data/LoginSession.php';
require_once 'php/c/cqm-reports/CqmReports.php';
require_once 'php/c/facesheets/Facesheet_Recs.php';
require_once 'php/data/xml/ccd/qrda/ClinicalDocument_Qrda.php';
require_once 'php/data/rec/group-folder/GroupFolder_Cqm.php';
//
LoginSession::verify_forUser();
?>
<html>
  <head>
  </head>
  <body>
  <pre>
<?php
global $login;
$from = '2013-01-01';
$to = '2014-01-01';
$userId = $login->userId;
switch ($_GET['t']) {
  case '0':
    $from = '2014-01-01';
    $to = '2015-01-01';
    $report = CqmReports::CMS002($from, $to, $userId);
    $pop = $report->Pops[0];
    $clients = $pop->IppClients;
    p_r($clients);
    $supp = CqmSuppData::from($clients);
    p_r($supp);
    break;
  case '1':
    $cqm = CqmReports::CMS069($from, $to, $userId);
    $filename = CqmReports::zipCat1($cqm);
    /*
    $filenames = CqmReports::saveCat1s($cqm);
    p_r($filenames);
    $folder = GroupFolder_Cqm::open();
    $filename = $folder->zip($filenames, $cqm);
    */
    p_r($filename);
    break;
  case '2':
    $report = CqmReports::CMS002($from, $to, $userId);
    p_r($report);
    break;
  case '2a':
    $report = CqmReports::CMS002($from, $to, $userId);
    p_r($report->forQrdaCat1());
    break;
  case '50':
    $report = CqmReports::CMS050($from, $to, $userId);
    p_r($report);
    break;
  case '50a':
    $report = CqmReports::CMS050($from, $to, $userId);
    p_r($report->forQrdaCat1());
    break;
  case '68':
    $report = CqmReports::CMS068($from, $to, $userId);
    p_r($report);
    break;
  case '68a':
    $report = CqmReports::CMS068($from, $to, $userId);
    p_r($report->forQrdaCat1());
    break;
  case '69':
    $report = CqmReports::CMS069($from, $to, $userId);
    p_r($report);
    break;
  case '69a':
    $report = CqmReports::CMS069($from, $to, $userId);
    p_r($report->forQrdaCat1());
    break;
  case '90':
    $report = CqmReports::CMS090($from, $to, $userId);
    p_r($report->Pops);
    break;
  case '138':
    $report = CqmReports::CMS138($from, $to, $userId);
    p_r($report->Pops);
    break;
  case '138a':
    $report = CqmReports::CMS138($from, $to, $userId);
    p_r($report->forQrdaCat1());
    break;
  case '156':
    $report = CqmReports::CMS156($from, $to, $userId);
    p_r($report->Pops);
    break;
  case '156a':
    $report = CqmReports::CMS156($from, $to, $userId);
    p_r($report->forQrdaCat1());
    break;
  case '165':
    $report = CqmReports::CMS165($from, $to, $userId);
    p_r($report->Pops);
    break;
  case '165a':
    $report = CqmReports::CMS165($from, $to, $userId);
    p_r($report->forQrdaCat1());
    break;
  case '166':
    $report = CqmReports::CMS166($from, $to, $userId);
    p_r($report->Pops);
    break;
  case '166a':
    $report = CqmReports::CMS166($from, $to, $userId);
    p_r($report->forQrdaCat1());
    break;
    /* Cat 1 reporting */
  case '1000':
    $cqm = CqmReports::CMS002($from, $to, $userId);
    $pop = $cqm->Pops[0];
    $client = reset($pop->IppClients);
    p_r($client);
    p_r($client->getClassType());
    p_r($client->getClassSubtype());
    p_r($client->getReportNum());
    break;
  case '1002':
    $cqm = CqmReports::CMS002($from, $to, $userId);
    $clients = $cqm->forQrdaCat1();
    foreach($clients as $client) {
      $cd = ClinicalDocument_Qrda::asCategory1($cqm, $client);
      echo $cd->debug();
    }
    break;
  case '1050':
    $cqm = CqmReports::CMS050($from, $to, $userId);
    $clients = $cqm->forQrdaCat1();
    $client = $clients[0];
    $cd = ClinicalDocument_Qrda::asCategory1($cqm, $client);
    echo $cd->debug();
    break;
  case '1069':
    $cqm = CqmReports::CMS069($from, $to, $userId);
    $clients = $cqm->forQrdaCat1();
    $client = $clients[0];
    $cd = ClinicalDocument_Qrda::asCategory1($cqm, $client);
    echo $cd->debug();
    break;
  case '1090':
    $cqm = CqmReports::CMS090($from, $to, $userId);
    $clients = $cqm->forQrdaCat1();
    $client = $clients[0];
    $cd = ClinicalDocument_Qrda::asCategory1($cqm, $client);
    echo $cd->debug();
    break;
  case '1138':
    $cqm = CqmReports::CMS138($from, $to, $userId);
    $clients = $cqm->forQrdaCat1();
    $client = $clients[3];
    $cd = ClinicalDocument_Qrda::asCategory1($cqm, $client);
    echo $cd->debug();
    break;
  case '1156':
    $cqm = CqmReports::CMS156($from, $to, $userId);
    $clients = $cqm->forQrdaCat1();
    $client = $clients[0];
    $cd = ClinicalDocument_Qrda::asCategory1($cqm, $client);
    echo $cd->debug();
    break;
  case '1165':
    $cqm = CqmReports::CMS165($from, $to, $userId);
    $clients = $cqm->forQrdaCat1();
    $client = $clients[0];
    $cd = ClinicalDocument_Qrda::asCategory1($cqm, $client);
    echo $cd->debug();
    break;
  case '1166':
    $cqm = CqmReports::CMS166($from, $to, $userId);
    $clients = $cqm->forQrdaCat1();
    $client = $clients[0];
    $cd = ClinicalDocument_Qrda::asCategory1($cqm, $client);
    echo $cd->debug();
    break;

  /* Cat 3 reporting */
  case '3000':
    $cqmset = CqmReports::getSet($userId);
    p_r($cqmset);
    break;
  case '3001':
    $cqmset = CqmReports::getSet($userId, '2013-01-01', '2014-01-01');
    $cd = ClinicalDocument_Qrda::asCategory3($cqmset);
    echo $cd->debug();
}
?>
  </pre>
</html>