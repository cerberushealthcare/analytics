<?php 
require_once "php/data/LoginSession.php";
//
if (isset($_GET['sess'])) {
  session_id($_GET['sess']);
}
if (isset($_GET['amp;sess'])) {  // comes from TCPDF this way when inside img tag src
  session_id($_GET['amp;sess']);
}
try {
  $login = LoginSession::verify(); 
} catch (SessionExpiredException $e) {
  header("Location: index.php?timeout=1");
  exit;
} catch (SessionInvalidException $e) {
  header("Location: index.php?invalid=1");
  exit;
}
if ($login->User->isPasswordExpired()) {
  header("Location: index.php?cp=1");
  exit;
}
header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
