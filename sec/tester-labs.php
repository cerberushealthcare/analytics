<?php
require_once 'php/data/LoginSession.php';
require_once 'php/data/ftp/FtpFolder.php';
require_once 'php/data/rec/sql/HL7_Labs.php';
require_once 'php/data/hl7/msg/_HL7Message.php';
require_once 'php/data/hl7/msg/labs/LabMessage.php';
//
LoginSession::loginBatch(1, 'tester-labs');
//LoginSession::verify_forServer();
?>
<html>
  <head>
  </head>
  <body>
  <pre>
<?php 
switch ($_GET['t']) {
  case '1':
    $labs = Lab::fetchAll_forSftpPolling();
    p_r($labs);
    exit; 
  case '2':
    $labs = Lab::fetchAll_forSftpPolling();
    $lab = end($labs);
    $folder = FtpFolder::from($lab);
    $files = $folder->getIncoming();
    p_r($files);
    exit;    
  case '3':
    $labs = Lab::fetchAll_forSftpPolling();
    $lab = end($labs);
    $folder = FtpFolder::from($lab);
    $files = $folder->getIncoming();
    $file = current($files);
    $msgs = ORU_L0080001::fromFtpFile($file, $lab);
    p_r($msgs);
    exit;
  case '4':
    $labs = Lab::fetchAll_forSftpPolling();
    $lab = end($labs);
    $folder = FtpFolder::from($lab);
    $files = $folder->getIncoming();
    $file = current($files);
    HL7_Labs::import_fromFtpFile($file);    
    exit;
  case '10':
    $labs = Lab::fetchAll_forSftpPolling();
    $lab = end($labs);
    $folder = FtpFolder::from($lab);
    $files = $folder->getIncoming();
    $file = current($files);
    $msgs = LabMessage::fromFtpFile($file, $file->Lab);
    $msg = current($msgs);
    //p_r($msg);
    $pdf = $msg->getDecodedPdf();
    //p_r(strlen($pdf));
    ob_clean();
    header("Pragma: ");
    header("Cache-Control: ");
    header("Content-type: application/pdf");
    echo $pdf;
    exit;
  case '11':
    $labs = Lab::fetchAll_forSftpPolling();
    $lab = end($labs);
    $folder = FtpFolder::from($lab);
    $files = $folder->getIncoming();
    $file = current($files);
    $msgs = LabMessage::fromFtpFile($file, $file->Lab);
    $msg = current($msgs);
    $pdf = $msg->getDecodedPdf();
    p_r($pdf);
    exit;
  case '12':
    $labs = Lab::fetchAll_forSftpPolling();
    $lab = end($labs);
    $folder = FtpFolder::from($lab);
    $files = $folder->getIncoming();
    $file = current($files);
    HL7_Labs::import_fromFtpFile($file);
    exit;
}
function blog($s, $c = null) {
  return;
  p_r($s, 'BLOG: ' . $c);
}
?>
</html>
