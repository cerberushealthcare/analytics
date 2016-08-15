<?php
error_reporting(E_ALL); ini_set('display_errors', '1');
require_once 'php/data/LoginSession.php';
require_once 'php/data/xml/_XmlRec.php';
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
    $xml = new DomDocument();
    $xml->load('xsltest\allscripts-b7-hernandez.xml');
    $xsl = new DomDocument();
    $xsl->load('xsltest\CDA.xsl');
    $proc = new XSLTProcessor();
    $proc->importStyleSheet($xsl);
    echo $proc->transformToXml($xml);
    exit;
  case '2':
    $xml = new DomDocument();
    $xml->load('xsltest\allscripts-e2-jones.xml');
    $xsl = new DomDocument();
    $xsl->load('xsltest\CDA2.xsl');
    $proc = new XSLTProcessor();
    $proc->importStyleSheet($xsl);
    echo $proc->transformToXml($xml);
    exit;
}
?>
</html>
