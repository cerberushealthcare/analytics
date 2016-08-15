<?php
require_once 'php/data/LoginSession.php';
require_once 'php/data/sftp/Sftp.php';
//
LoginSession::verify_forServer();
//
class Sftp_PAML extends Sftp {
  //
  static $HOST = 'ssh.paml.com';
}
?>
<html>
  <head>
  </head>
  <body>
  <pre>
<?php
switch ($_GET['t']) {
  case '1':
    set_include_path('php/phpseclib');
    require_once 'Net/SFTP.php';
    $sftp = new Net_SFTP('ssh.paml.com');
    $r = $sftp->login('JhnWRchrMd', '4GLD5RK7H8W');
    if ($r) { 
      print_r($sftp->nlist());
      print_r($sftp->rawlist());
      echo $sftp->get('JhnWRchrMd_123774.hl7');
    }
    exit;
  case '2':
    set_include_path('php/phpseclib');
    require_once 'Net/SFTP.php';
    $sftp = new Net_SFTP('ssh.paml.com');
    $r = $sftp->login('JhnWRchrMd', '4GLD5RK7H8W');
    if ($r) { 
      echo $sftp->get('JhnWRchrMd_123774.hl7');
    }
    //print_r($sftp->getSFTPErrors());
    //print_r($sftp->getErrors());
    //print_r($sftp->getLog());
    exit;
  case '3':
    $sftp = Sftp_PAML::login('JhnWRchrMd', '4GLD5RK7H8W');
    $stubs = $sftp->dir();
    print_r($stubs);
  case '4':
    $sftp = Sftp_PAML::login('JhnWRchrMd', '4GLD5RK7H8W');
    $files = $sftp->getFiles();
    $base = MyEnv::$SFTP_PATH . '\L0040001 TESTING\in';
    SfFile::saveAll($files, $base);
}
?>
</html>