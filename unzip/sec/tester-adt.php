<?php
require_once 'php/data/LoginSession.php';
require_once 'php/data/hl7-2.5.1/msg/ADTMessage.php';
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
$jane = new stdClass();
$jane->cid = 3839; 
$jane->npi = '1231231234';
$session = <<<eos
{
  "sessionId":"1446",
  "date":"08/17/2012",
  "dateCreated":"08/17/2012 10:35:02",
  "diagnoses":[
    {"icd":"599.0","text":"Urinary Tract Infection"}],
  "dsyncs":{
    "ss.temp":{"text":"99.1","code":"11289-6"},
    "ss.chiefComplaint":{"text":"Chills, Fever, Burning during Urination and Smelly Urine"},
    "ss.identifierType":{"text":"Medical Record Number","code":"MR"},
    "ss.facilityVisitType":{"text":"Urgent Care","code":"261QU0200X"},
    "ss.patientClass":{"text":"Outpatient","code":"O"},
    "ss.onsetDate":{"text":"16-Aug-2012","code":"11368-8"},
    "murmurStatus":{"text":"are no carotid bruits"},
    "ss.diagnosisType":{"text":"Working","code":"W"},
    "ss.dischargeDisposition":{"text":"Discharged to home or self care (routine discharge)","code":"01"},
    "ss.observationResultStatus":{"text":"Preliminary results","code":"P"}}
}
eos;
$jane->session = json_decode($session);
//
switch ($_GET['t']) {
  case '0':
    p_r($jane);
    exit;
  case '1':
    $id = $jane->cid;
    $npi = $jane->npi;
    $fs = Facesheet_Hl7Syndrome::from($id, $npi, $jane->session);
    p_r($fs);
    exit;
  case '2':
    $id = $jane->cid;
    $npi = $jane->npi;
    $fs = Facesheet_Hl7Syndrome::from($id, $npi, $jane->session);
    $adt = ADTMessage::asRegister($fs);
    p_r($adt);
    exit;
  case '3':
    $id = $jane->cid;
    $npi = $jane->npi;
    $fs = Facesheet_Hl7Syndrome::from($id, $npi, $jane->session);
    $adt = ADTMessage::asRegister($fs);
    p_r($adt->toHL7());
    exit;
  case '4':
    $id = $jane->cid;
    $npi = $jane->npi;
    $fs = Facesheet_Hl7Syndrome::from($id, $npi, $jane->session);
    $adt = ADTMessage::asEndVisit($fs);
    p_r($adt->toHL7());
    exit;
}
?>
</html>


