<?php
require_once 'php/data/LoginSession.php';
require_once 'php/data/rec/sql/Clients.php';
require_once 'php/c/patient-billing/CerberusBilling.php';
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
    $r = CerberusBilling::login(1, 'demodoctor','demo');
    print_r($r);
    exit;
  case '2':
    $eid = CerberusBilling::closeNote();
    print_r($eid);
    exit;
  case '3':
    CerberusBilling::navSuperbill('489', '415032');
    exit;
  case '4':
    CerberusBilling::navListSuperbills('1658');
    exit;
  case '5':
    CerberusBilling::navUnsignedSuperbills();
    exit;
    case '10':
    $client = Clients::get(1658);
    $rs = CR_Patient::from($client);
    $xml = $rs->toString();
    echo '<pre>' . htmlentities($xml) . '</pre>'; 
    exit;
  case '11':
    $client = Clients::get(1658);
    $xq = CXQ_Patient::create('37', 'demodoctor', 'demo', $client);
    $response = $xq->submit();
    print_r($response);
    exit;
  case '20':
    $client = CerberusBilling::updateClient(3746);
    p_r($client);
    exit;
  case '21':
    $queryBase = 'https://www.papyrus-dev.com/ords/f?p=';
    p_r($queryBase);
    $url = str_replace('f?p=', 'ws1/', $queryBase);
    p_r($url);
    exit;
    $eid = CerberusBilling::closeNote(300562);
    p_r($eid);
    exit;
  case '22':
    $cpts = Proc_Cb::fetchCpts(1658, '2013-02-25');
    p_r($cpts);
    exit;
  case '30':
    CerberusBilling::navInsurance(1658/*Bea Dixon*/);
    exit;
  case '31':
    $entries = CerberusBilling::fetchInsurance(1658/*Bea Dixon*/);
    p_r($entries);
    exit;
  case '32':
    $icards = CerberusBilling::refreshICards(1658/*Bea Dixon*/);
    p_r($icards);
    exit;
  case '40':
    $appts = CerberusBilling::fetchAppts();
    p_r($appts);
}
?>
</html>