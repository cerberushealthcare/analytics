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
    <?php HEAD('Reporting') ?>
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
    <?php BODY() ?>
      <h1>Reporting</h1>
      <?php BOX() ?>
        <?php RPANEL(1, 'Patient', 'General ad-hoc patient reporting.') ?>
        <?php RPANEL(2, 'Meaningful Use', 'Reports to apply for meaningful use incentives.') ?>
        <?php RPANEL(3, 'Clinical Decision Support', '') ?>
        <?php RPANEL(4, 'Audit Logs', 'For reporting audit records of patient data modification.') ?>
      <?php _BOX() ?>
    <?php _BODY() ?>
  </body>
  <?php CONSTANTS('Reporting') ?>
  <?php START() ?>  
</html>
<?php 
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
