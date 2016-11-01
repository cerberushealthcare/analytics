<?
require_once "php/data/LoginSession.php";
require_once 'php/data/rec/sql/LookupAreas.php';
require_once 'php/data/rec/sql/ErxUsers.php';
require_once 'php/data/rec/sql/_BillingRecs.php';
require_once 'inc/uiFunctions.php';
//
LoginSession::verify_forUser();
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Strict//EN'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <?php HEAD('Profile & Settings', 'ProfilePage') ?>
    <?php HEAD_Profile() ?>
    <style>
H2 {
font-size:13pt;
color:#578F85;
line-height:16pt;
}
H3 {
font-size:10pt;
color:#578F85;
display:inline;
}
TABLE.t2c {
  margin-top:5px;
  margin-bottom:7px;
}
TABLE.t2c TH {
  width:100px;
  vertical-align:top;
  text-align:right;
  padding:5px;
}
TABLE.t2c TD {
  border:1px solid #e0e0e0;
  background-color:white;
  padding:5px 5px 5px 10px;
  vertical-align:middle;
}
SPAN.rof {
  border:1px solid #e0e0e0;
}
SPAN.ft {
  width:500px;
  height:40px;
}
    </style>
 </head>
  <body>
    <?php BODY() ?>
      <h1>Profile & Settings</h1>
      <?php renderBoxStart('wide min-pad', null, null, 'box') ?>
        <div id='tile'>
          <div class='spacer'>&nbsp;</div>
        </div>
      <?php renderBoxEnd() ?>
    <?php _BODY() ?>
  </body>
  <?php CONSTANTS('Profile') ?>
  <?php START() ?>
</html>
