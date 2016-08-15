<?php
require_once 'php/data/LoginSession.php';
require_once 'php/c/immun-charting/ImmunCharting.php';
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
  case '0':
    $f = 'G14S0002127.jpg';
    $s = substr($f, strpos($f, 'S'), 5);
    if (strlen($s) == 5 && is_numeric(substr($s, 1)))
      p_r('yep');
    p_r($s);
    exit;
  case '1':
    $cid = 3798;
    $cds = ImmunCds::get($cid);
    p_r($cds);
    exit;
  case '2':
    $cid = 3798;
    $form = 'KY';
    $asScheduled = empty($form) ? true : false;
    $chart = ImmunChart::fetch($cid, $asScheduled);
    p_r($chart);
    exit;
  case '3':
    $pdf = PdfM_Immun_KY::create();
    $html = <<<eos
<div id=cname>HORNSBY, EMMA FRANCES</div>
<div id=dob>11 / 23 / 2004</div>
<div id=parent>HORNSBY, WARREN G</div>    
<div id=addr>509 MACON AVE, LOUISVILLE, KY 40207</div>
<div id=dtp1>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=dtp2>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=dtp3>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=dtp4>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=dtp5>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=hib1>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=hib2>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=hib3>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=hib4>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=pcv1>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=pcv2>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=pcv3>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=pcv4>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=polio1>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=polio2>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=polio3>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=polio4>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=hepb1>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=hepb2>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=hepb3>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=hepba1>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=hepba2>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=mmr1>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=mmr2>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=var1>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=var2>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=tdap>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=td>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>    
<div id=mcv>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>
<div id=until>06&nbsp;&nbsp;04&nbsp;&nbsp;13</div>
<div id=pname>FAMILY PRACTICE, LOUISVILLE, KY</div>    
eos;
    $pdf->setHeader()->setBody($html)->download();
  case '3':
    $cid = 3788; /*Emma*/
    $chart = ImmunChart::fetch($cid);
    $pdf = PdfM_Immun_KY::from($chart)->download();
  case '4':
    $cid = 3788;
    ImmunCharting::downloadPdf($cid);
    exit;
  case '5':
    $cid = 3740;
    ImmunCharting::downloadPdf($cid);
    exit;
  case '10':
    require_once 'php/c/immun-cds/ImmunCds.php';
    p_r('here');
    exit;
  case '11':
    require_once 'php/c/immun-cds/ImmunCds.php';
    $results = ImmunCds::test(15796/*Emma*/);
    p_r($results);
    exit;
  case '12':
    require_once 'php/c/immun-cds/ImmunCds.php';
    $results = ImmunCds::test(15816/*Meg*/);
    p_r($results);
    exit;
  case '13':
    require_once 'php/c/immun-cds/ImmunCds.php';
    $results = ImmunCds::get_withHtml(15819/*Baby, New*/);
    p_r($results);
    exit;
}
?>
</html>
