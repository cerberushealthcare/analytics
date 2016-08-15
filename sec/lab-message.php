<?
require_once "php/data/LoginSession.php";
require_once 'inc/uiFunctions.php';
//
LoginSession::verify_forUser()->requires($login->Role->Artifact->labs);
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Strict//EN'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <? HEAD('Lab Message') ?>
    <? HEAD_UI_CSSJS('LabMessagePop') ?>
    <? HEAD_REC('Labs') ?>
  </head>
  <body>
    <div id='tile'>
      <div class='spacer'>&nbsp;</div>
    </div>
  </body>
  <? CONSTANTS('Proc', 'OrderEntry', 'Doctors', 'Client') ?>
</html>
<script>
show(<?=$_GET['id'] ?>);
function show(id) {
  Ajax.Labs.getInboxMessage(id, function(rec) {
    rec.asList().into(_$('tile').clean());
  })
}
</script>