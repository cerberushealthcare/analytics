<?php
require_once 'php/data/LoginSession.php';
require_once 'php/data/hl7-2.5.1/msg/VXUMessage.php';
require_once 'php/data/rec/sql/Facesheets.php';
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
    $id = 3814/*Snow, Madelynn Ainsley*/;
    $fs = Facesheet_Hl7Immun::from($id);
    p_r($fs);
    p_r($fs->Client->getRaces());
    exit;
  case '2':
    $id = 3814/*Snow, Madelynn Ainsley*/;
    $fs = Facesheet_Hl7Immun::from($id);
    $vxu = VXUMessage::from($fs);
    p_r($vxu);
    exit;
  case '3':
    $id = 3814/*Snow, Madelynn Ainsley*/;
    $fs = Facesheet_Hl7Immun::from($id);
    $vxu = VXUMessage::from($fs, 'x68', 'test');
    echo($vxu->toHL7());
    exit;
  case '4':
    $id = 3816/*Vally, Nitika*/;
    $fs = Facesheet_Hl7Immun::from($id);
    $vxu = VXUMessage::from($fs, 'x68', 'test');
    echo($vxu->toHL7());
    exit;
  case '5':
    $id = 3817/*Mercer, Jirra*/;
    $fs = Facesheet_Hl7Immun::from($id);
    $vxu = VXUMessage::from($fs, 'x68', 'test');
    echo($vxu->toHL7());
    exit;
  case '6':
    $id = 3817/*Mercer, Jirra*/;
    $fs = Facesheet_Hl7Immun::from($id);
    p_r($fs);
    exit;
}
?>
</html>