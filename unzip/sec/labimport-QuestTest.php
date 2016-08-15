<?php
require_once 'php/data/LoginSession.php';
require_once 'bat/batch.php';
require_once 'php/data/hl7-2.5.1/msg/labs/L0080001/ORU_L0080001.php';
require_once 'php/data/rec/sql/HL7_Labs.php';
//
$s = file_get_contents("php://input");
$username = '';
$password = '';
if (! empty($s)) {
  blog($s, 'HTTP_RAW_POST_DATA');
  $headers = $_SERVER['HTTP_AUTHORIZATION'];
  blog($headers, 'headers');
  if (isset($_SERVER['PHP_AUTH_USER'])) {
      $username = $_SERVER['PHP_AUTH_USER'];
      $password = $_SERVER['PHP_AUTH_PW'];

  // most other servers
  } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {

          if (strpos(strtolower($_SERVER['HTTP_AUTHORIZATION']),'basic')===0)
            list($username,$password) = explode(':',base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));

  }
}

if (geta($_GET, 't')) {
$s = <<<eos
MSH|^~\&|LAB|WDL||97502840|20150205144545||ORU^R01|80000000000000228911|D|2.3.1
PID|1||CB019977A||TEST^QDRS||19800101|F||||||||||1224252
NTE|1|TX|FASTING: YES
ORC|RE|1224252|CB019977A||CM
OBR|1|1224252|CB019977A|484^GLUCOSE, PLASMA^^484SB=^GLUCOSE, PLASMA|||20150205000000|||||||20150205132700|||||||CB^Quest Diagnostics-Wood Dale^1355 Mittel Blvd^Wood Dale^IL^60191-1024^Anthony V Thomas, M.D.|20150205134100|||F
OBX|1|NM|1558-6^Glucose p fast SerPl-mCnc^LN^25014500^GLUCOSE, PLASMA^QDIWDL||95|mg/dL|65-99|N|||F|||20150205134100|CB
NTE|1||
NTE|2||           Fasting reference interval
NTE|3||
OBR|2|1224252|CB019977A|ClinicalPDFReport1^Clinical PDF Report CB019977A-1^^ClinicalPDFReport1^Clinical PDF Report CB019977A-1|||20150205000000|||||||20150205132700||||||||20150205134100|||F
eos;
$username = 'L0090001';
$password = 'STAGING';
}

blog($username, 'username');
blog($password, 'password');
if ($username == 'L0090001' && $password == 'STAGING') {
  $lab = Lab::fetchByUid('L0080001');
  $msg = ORU_L0080001::fromHL7($s, $lab);
  $id = $msg->Header->msgControlId;
  echo <<<eos
MSH|^~\&|Clicktate|G0001||97502840|20150205144545||ACK^R01|$id|D|2.3.1
MSA|AA|$id
eos;
} else {
  echo 'FAIL|Authentication failure';
}
return;
if ($s) log2("HTTP_RAW_POST_DATA:" . $s);
$rest = new Rest();
$server = new Cerberus();
try {
  $response = $server->request($rest);
  log2($response);
  ob_clean();
  header("Cache-Control: no-cache, must-revalidate");
  header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
  header("Pragma: no-cache");
  session_cache_limiter("nocache");
  echo $response;
} catch (ApiException $e) {
  log2('ApiException: ' . $e->getResponse());
  echo $e->getResponse();
} catch (Exception $e) {
  log2('Exception: ' . $e->getMessage());
  echo $e->getMessage();
  //header('Request not recognized.', true, 405);
}
