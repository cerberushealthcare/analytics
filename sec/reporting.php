<?
require_once "php/data/LoginSession.php";
require_once 'inc/uiFunctions.php';
require_once 'php/data/rec/sql/LookupAreas.php';
require_once 'php/c/template-entry/TemplateEntry.php';
require_once 'php/c/health-maint/HealthMaint_Recs.php';
//
LoginSession::verify_forUser()->requires($login->Role->Report->any());
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Strict//EN'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <?php HEAD('Reporting', 'ReportingPage') ?>
    <?php HEAD_UI('Entry', 'Reporting') ?>
  </head>
  <body>
    <?php BODY() ?>
      <table class='h'>
        <tr>
          <th>
            <h1>Reporting</h1>
          </th>
          <td>
          <?php if (! $login->super) { ?>
            <a class='icon list' href='reporting-pqri.php'>NQF (Clinical Quality Measures)</a>
          <?php } ?>
          </td>
        </tr>
      </table>
      <?php renderBoxStart('wide min-pad', null, null, 'box') ?>
        <div id='tile'>
          <div class='spacer'>&nbsp;</div>
        </div>
      <?php renderBoxEnd() ?>
    <?php _BODY() ?>
  </body>
  <?php CONSTANTS('Reporting') ?>
  <?php START() ?>  
</html>
