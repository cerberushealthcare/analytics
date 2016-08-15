<?php
require_once 'php/pdf/PdfM.php';
require_once 'php/data/LoginSession.php';
//
LoginSession::verify_forServer();
ini_set('memory_limit', '1024M');
$htmlBody = stripslashes($_POST['body']);
$htmlBody = fixPdfAnchors($htmlBody);
$htmlHeader = stripslashes($_POST['head']);
$dos = $_POST['dos'];
if (! empty($dos) && substr($htmlHeader, -10) == '</P></DIV>') {
  $dos = formatConsoleDate($dos);
  $htmlHeader = substr($htmlHeader, 0, -11) . "DOS: $dos</P></DIV>";
}
$cssStyle = stripslashes($_POST['style']);
$filename = $_POST['filename'];
$p = PdfM_Factory::createMine();
$p->withPaging()
  ->setHeader($htmlHeader, $cssStyle)
  ->setBody($htmlBody)
  ->download($filename);
//
function fixPdfAnchors($body) {
  global $login;
  $from = '"session-image.php?';
  $to = '"' . MyEnv::$PDF_URL . 'session-image.php?sess=' . $login->sessionId . '&'; 
  $a = explode($from, $body);
  if (count($a))
    $body = implode($to, $a);
  logit_r($body, 'after');
  return $body;
}