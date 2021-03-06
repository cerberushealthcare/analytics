<?
require_once "php/data/LoginSession.php";
require_once 'inc/uiFunctions.php';
require_once 'php/data/rec/sql/PortalUsers.php';
//
LoginSession::verify_forUser()->requires($login->Role->Account->portal);
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Strict//EN'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <?php HEAD('Accounts', 'AccountsPage') ?>
    <?php HEAD_PortalUserEntry() ?>
    <?php HEAD_UserSelector() ?>
    <style>
DIV.CustomForm INPUT {
  font-size:9pt;
}
DIV.CustomForm SELECT {
  font-size:9pt;
}
DIV.CustomForm TR.another TD {
  padding:7px 6px;
}
    </style>
  </head>
  <body onload='start()'>
    <?php BODY() ?>
      <h1>Portal User Accounts</h1>
      <?php renderBoxStart('wide min-pad', null, null, 'box') ?>
        <div id='tile'>
          <div class='spacer'>&nbsp;</div>
        </div>
      <?php renderBoxEnd() ?>
    <?php _BODY() ?>
  </body>
<script type='text/javascript'>
C_Docs = <?=UserGroups::getDocsJsonList()?>;
C_Users = <?=UserGroups::getActiveUsersJsonList()?>;
C_PortalUser = <?=PortalUser::getStaticJson()?>;
function start() {
  var query = <?=jsonencode($_GET) ?>;
  AccountsPage.load(query);
}
</script>
</html>
