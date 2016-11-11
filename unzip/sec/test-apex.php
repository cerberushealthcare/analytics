<?
require_once "php/data/LoginSession.php";
require_once 'inc/uiFunctions.php';
//
LoginSession::verify_forUser();
$addr = Address::formatCsz($login->User->UserGroup->Address);
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Strict//EN'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <?php HEAD('Welcome') ?>
  </head>
  <body>
    <?php BODY() ?>
      <table class='h'>
        <tr>
          <th>
            <h1>
              Tester
            </h1>
          </th>
          <td>
          </td>
        </tr>
      </table>
      <div id='output' style='height:500px'>
        <input type='button' value='Request' onclick='request()' />
      </div>
    <?php _BODY() ?>
  </body>
</html>
<script>
function request() {
  //var url = 'http://localhost/clicktate/sec/test-apex-r.php';
  var url = 'https://www.papyrus-pms.com/pls/apex/f?p=2307:CT_LOGIN::LOGIN::400:P400_USERNAME,P400_PASSWORD,P400_LOGIN,APP_EMR_SESSIONID:demodoctor,demo,Y,v6ftustjtmcp2pnh7tcb2djbj6';
  YAHOO.util.Connect.asyncRequest("GET", url, {success:response});
}
function response(o) {
  alert(o.responseText);
}
</script>