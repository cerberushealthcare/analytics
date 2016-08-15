<?
require_once "php/data/LoginSession.php";
require_once "php/data/bat/Bat.php";
require_once 'inc/uiFunctions.php';
//
LoginSession::verify_forUser()->requires($login->admin);
//
class Bat_Deploy extends Bat {
  //
  static $BAT_FILE = 'run-deploy-local.bat';
}
//
if (isset($_GET['confirm'])) {
  echo '<pre>';
  print_r(Bat_Deploy::run());
  echo "<br><a href='serverAdm.php'>Back to admin</a>";
} else {
  echo "<a href='adminTestDeploy.php?confirm=1'>Confirm deploy</a>";
}  
