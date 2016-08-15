<?php
require_once 'php/data/LoginSession.php';
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
    $r = CerberusBilling::login('demodoctor','demo');
    print_r($r);
    exit;
  case '2':
    $cq = CQ_Login::create('demodoctor','demo','123456');
    $response = $cq->submit();
    print_r("\n");
    print_r($response->cookie);
    print_r("\n");
    print_r($response->sessionId);
    exit;    
  case '3':
    $cookie = 'PCOOKIE=0F77662B3B865DCE0E51A08640AC3532'; 
    $sid = '7001158030578';
    $cid = '489';
    $site = '61';
    $case = null;
    $date = '01/05/2013';
    $loc = '12';
    $icds = array('493.90', '465.9');
    $cpts = array('99205', '11100', '12021');
    $cq = CQ_CloseNote::create($cid, $site, $case, $date, $loc, $icds, $cpts);
    $eid = $cq->submit($sid, $cookie);
    print_r("eid=" . $eid);
    exit;
  case '4':
    $eid = CerberusBilling::closeNote();
    print_r($eid);
    exit;
    /*
    $cookie = 'PCOOKIE=0F77662B3B865DCE0E51A08640AC3532'; 
    $sid = '7001158030578';
    $eid = '415032';
    $cid = '489';
    $cq = CQ_Superbill::create($cid, $eid);
    $html = $cq->submit($sid, $cookie);
    echo $html;
    exit;
    */
  case '5':
    $cq = CQ_Login::create('demodoctor','demo','123456');
    $cq->navigate();
    exit;
  case '6':
    /*
    $cq = CQ_Page::create_asSuperbill('demodoctor','demo','489','415032');
    $cq->navigate();
    exit;
    */
  case '7':
    $cq = CQ_Page::create_asListSuperbills('demodoctor','demo','489');
    $cq->navigate();
    exit;
  case '20':
    CerberusBilling::navSuperbill('489', '415032');
    exit;
  case '21':
    $cq = CQ_ListSuperbills::go('demodoctor','demo','489','415032');
    exit;
  case '39':
    $url = 'https://www.papyrus-pms.com/pls/apex/f?p=2307:CT_LOGIN::LOGIN::400:P400_USERNAME,P400_PASSWORD,P400_LOGIN,APP_EMR_SESSIONID:demodoctor,demo,Y,v6ftustjtmcp2pnh7tcb2djbj6';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_COOKIEFILE, '');
    curl_setopt($ch, CURLOPT_HEADER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    print_r($response);
    exit;
  case '41':
    $url = 'https://www.papyrus-pms.com/pls/apex/f?p=2307:CT_CLOSENOTE:16593108168771:NEW::401:P0_AGENCY_CLIENTID,P401_SITE_CODE,P401_CASE,P401_ENCOUNTER_DATE,P401_LOC_CODE,P401_DIAG1,P401_DIAG2,P401_PROC1,P401_PROC2,P401_PROC3:489,61,,01/05/2013,12,493.90,465.9,99205,11100,12021';
    $cookies = 'PCOOKIE=182F699FF543D99484EC6BC5E1DC188A';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, '');
    curl_setopt($ch, CURLOPT_COOKIE, $cookies);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    //curl_setopt($ch, CURLOPT_COOKIESESSION, true);
    //curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    //curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    //curl_setopt($ch, CURLOPT_POST, false);
    $response = curl_exec($ch);
    curl_close($ch);
    print_r($response);
    exit;
}
?>
</html>