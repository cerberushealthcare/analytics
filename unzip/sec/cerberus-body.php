<?
require_once "php/data/LoginSession.php";
require_once 'php/c/patient-billing/CerberusBilling.php';
//
try {
  LoginSession::verify_forServer();
  if (! $login->cerberus) {
    echo 'This account is not set up for billing.';
    exit;
  }
} catch (Exception $e) {
  echo $e->getMessage();
  exit;
}
if (isset($_GET['action'])) {
  switch ($_GET['action']) {
    case 'superbill':
      CerberusBilling::navSuperbill($_GET['id']);
      exit;
    case 'list':
      CerberusBilling::navListSuperbills($_GET['id']);
      exit;
    case 'unsigned':
      CerberusBilling::navUnsignedSuperbills();
      exit;
    case 'insurance':
      CerberusBilling::navInsurance($_GET['id']);
      exit;
  }
}
?>
<html>
<body>
<form name='info' method='get' action='cerberus-body.php'>
  <input type='hidden' name='action' value='<?=geta($_GET, 'action')?>' />
  <input type='hidden' name='id' value='<?=geta($_GET, 'id')?>' />
</form>
<div style='text-align:center;padding-top:200px;font-family:Arial;font-size:9pt;'>
Connecting... 
</div>
</body>
<script>
document.info.submit();
</script>
</html>
