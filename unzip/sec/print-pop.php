<?
require_once "php/data/LoginSession.php";
require_once "inc/uiFunctions.php";
//
LoginSession::verify_forUser();
//
$pop = $_GET['pop'];
$obj = $_GET['obj'];
$title = getv('title');
$titlel = getv('titlel');
$titler = getv('titler');
$arg = $_GET['arg'];
function getv($name) {
  return isset($_GET[$name]) ? $_GET[$name] : null;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Strict//EN">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <title id='page-title'><?=$title ?></title>
    <link rel="stylesheet" type="text/css" href="css/page.css?<?=Version::getUrlSuffix() ?>" />
    <link rel="stylesheet" type="text/css" href="css/data-tables.css?<?=Version::getUrlSuffix() ?>" />
    <link rel="stylesheet" type="text/css" href="css/facesheet.css?<?=Version::getUrlSuffix() ?>" />
    <link rel="stylesheet" type="text/css" href="css/template-pops.css?<?=Version::getUrlSuffix() ?>" />
    <link rel="stylesheet" type="text/css" href="css/TabBar.css?<?=Version::getUrlSuffix() ?>" />
    <link rel="stylesheet" type="text/css" href="css/TemplateUi.css?<?=Version::getUrlSuffix() ?>" />
    <link rel="stylesheet" type="text/css" href="css/pop.css?<?=Version::getUrlSuffix() ?>" />
    <script language="JavaScript1.2" src="js/pages/Pop.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language='JavaScript1.2' src='js/_lcd_core.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/_lcd_html.js?<?=Version::getUrlSuffix() ?>'></script>
    <script type='text/javascript' src='js/ui.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language="JavaScript1.2" src="js/old-ajax.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/json.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/yui/yahoo-min.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/yui/event-min.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/yui/connection-min.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/ui.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/pages/Ajax.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/pages/Lookup.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/pages/Page.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/components/TableLoader.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/pops/<?=$pop ?>.js?<?=Version::getUrlSuffix() ?>"></script>
    <style>
A {
  text-decoration:none;
  cursor:normal;
  color:black;
  white-space:nowrap;
}
A:hover {
  text-decoration:none;
  cursor:normal;
}
BODY {
  font-family:Arial;
}
TABLE.h {
margin-bottom:1em;
}
TABLE.h TH {
  vertical-align:top;
}
TABLE.h TD {
  vertical-align:top;
  text-align:right;
}
H2 {
  font-size:11pt;
  font-family:Arial;
  color:black;
}
    </style>
  </head>
  <body>
  <div id='bodyContainer'>
  <div id='curtain'></div>
    <div id='body'>
      <?php if ($title) { ?>
        <div class='cj mb10'><h4><?=$title?></h4></div>
      <?php } else { ?>
        <table class='h'>
          <tr>
            <th>
              <h2><?=$titlel ?></h2>
            </th>
            <td>
              <h2><?=$titler ?></h2>
            </td>
          </tr>
        </table>
      <?php } ?>
      <div id='out' class='fstab noscroll'></div>
    </div>
  </div>
  <?php include "js/pops/inc/$pop.php" ?>
  </body>
  <script>
var Facesheet = Object.Rec.extend({});
var me;
var cid = <?=$arg ?>;
Pop.Working.show();
<?=$obj ?>.print(cid);
function printout(tableId) {
  Pop.Working.close();
  $('out').innerHTML = $(tableId).outerHTML;
  Ajax.Audit.printPop(cid, tableId, "<?=$title?>");
  call(window.print);
}
  </script>
</html>