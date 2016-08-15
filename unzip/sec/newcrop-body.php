<?
require_once "php/data/LoginSession.php";
require_once "php/newcrop/NewCrop.php";
try {
  LoginSession::verify_forServer();
  if (! $login->Role->erx) {
    echo 'This account is not set up to e-prescribe.';
    exit;
  }
} catch (Exception $e) {
  echo $e->getMessage();
  exit;
}
/**
 * New Crop Window (body)
 * Get arguments passed to NewCrop::buildClickThru
 * @get 'id' client ID
 * @get 'dest' NewCrop landing page
 * @get 'renewal' renewalRequestIdentifier (for pharm renewal)
 */
$newcrop = new NewCrop();
$id = geta($_GET, 'id'); 
$dest = geta($_GET, 'dest', RequestedPageType::COMPOSE);
$renewal = geta($_GET, 'rxguid');
$clickthru = $newcrop->buildClickThru($id, $dest, $renewal);
?>
<html>
<body>
<form name='info' method='post' action='<?=$clickthru['url'] ?>'>
  <input type='hidden' value='Go' />
  <textarea style='display:none' id='RxInput' name='RxInput'><?=$clickthru['xml'] ?></textarea>
</form>
<div style='text-align:center;padding-top:50px;font-family:Arial;font-size:10pt'>
<div style='width:250px;height:70px;background:url(img/icons/working5.gif) #dddddd no-repeat center center;border:10px solid #008E80;margin-bottom:0.5em;margin-left:auto;margin-right:auto'>
</div>
Connecting to ePrescribe network
</div>
</body>
<script>
document.info.submit();
</script>
</html>