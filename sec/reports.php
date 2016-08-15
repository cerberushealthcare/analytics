<?
ob_start('ob_gzhandler');
require_once "php/data/LoginSession.php";
require_once 'inc/uiFunctions.php';
//
LoginSession::verify_forUser()->requires($login->Role->Report->any());
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Strict//EN'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <? HEAD('Reporting') ?>
    <style>
TABLE.rp TH {
  padding-left:10px;
}
TABLE.rp TD {
  padding-left:10px;
}
    </style>
  </head>
  <body>
    <? BODY() ?>
      <h1>Reporting</h1>
      <? BOX() ?>
        <? RPANEL(1, 'Patient', 'General ad-hoc patient reporting.') ?>
        <? RPANEL(2, 'Meaningful Use', 'Reports to apply for meaningful use incentives.') ?>
        <? RPANEL(3, 'Clinical Decision Support', '') ?>
        <? RPANEL(4, 'Audit Logs', 'For reporting audit records of patient data modification.') ?>
      <? _BOX() ?>
    <? _BODY() ?>
  </body>
  <? CONSTANTS('Reporting') ?>
  <? START() ?>  
</html>
<? 
function RPANEL($i, $name, $desc) {
  echo <<<END
  <table class='rp'>
    <tr>
      <th>
        <div class='cmd-fixed'>
          <a class='cmd bigb breport hand'>$name</a>
        </div>
      </th>
      <td>
        $desc
      </td>
    </tr>
  </table>
END;
} 
