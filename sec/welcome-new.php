<?
require_once "php/data/LoginSession.php";
require_once "php/data/rec/sql/Welcome.php";
require_once 'inc/uiFunctions.php';
//
LoginSession::verify_forUser();
$addr = Address::formatCsz($login->User->UserGroup->Address);
$welcome = Welcome::fetch();
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Strict//EN'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <? HEAD('Welcome') ?>
    <style>
DIV.bc {
background-color:white;
border:1px solid #E4E2DC;
padding:10px 20px;
}
DIV.bc2 {
padding:10px 0;
}
DIV.link {
padding:3px 0 0 10px;
background-color:#f8f8f8;
}
DIV.off {
background-color:#f8f8f8;
background-color:white;
}
H2 {
font-size:13pt;
color:#578F85;
}
A.h2 {
font-family:'Lucida Grande','Trebuchet MS';
display:block;
width:100px;
line-height:14pt;
text-align:right;
color:#578F85;
}
DIV.link H2 {
display:block;
width:100px;
line-height:14pt;
text-align:right;
}
TABLE.b {
width:100%;
}
TD.w {
width:100%;
line-height:2em;
padding-left:10px;
}
TD.st {
padding-right:10px;
}
DIV.st {
font-size:11pt;
padding-left:15px;
display:inline;
}
DIV.act {
padding-left:10px;
display:inline;
}
A.action {
font-size:11pt;
margin-left:1em;
}
A.pencil {
background-position:-1px top;
}
A.blue {
color:blue;
}
    </style>
  </head>
  <body>
    <? BODY() ?>
      <table class='h'>
        <tr>
          <th>
            <h1>Welcome, <?=$login->User->name ?>
              &bull; <?=$login->User->UserGroup->name ?>
            </h1>
          </th>
          <td>
          </td>
        </tr>
      </table class='b'>
      <? // renderBoxStart('wide', null, null, 'box') ?>
        <div class='bc2'>
          <div class='link'>
            <table>
              <tr>
                <td class='h2'><a href="patients.php" class="h2">Patients</a></td>
                <td class='img'><a href="patients.php"><img src='img/welcome/1patients.png' /></a></td>
                <td class='w'>
                  <div class='st'>
                    <a href='patients.php' class='blue'>Patients</a>
                  </div>
                  <div class='act'>
                    <a class='action add' href='patients.php?pn=1'>New patient</a>
                    <a class='action patient' href='.'>Search</a>
                    <input type='text'>
                  </div>
                </td>
              </tr>
            </table>
          </div>
          <div class='link off'>
            <table>
              <tr>
                <td class='h2'><a href="documents.php" class="h2">Documents</a></td>
                <td class='img'><a href="documents.php"><img src='img/welcome/2documents.png' /></a></td>
                <td class='w'>
                  <div class='st'>
                    <? if ($welcome->docUnreviewed) { ?>
                      <a href='review.php' class='red'><?=plural($welcome->docUnreviewed, 'item')?> to review</a><? if ($welcome->docUnsigned) { ?> and <? } ?>
                    <? } ?>
                    <? if ($welcome->docUnsigned) { ?>
                      <a href='documents.php?pf1=closed&pfv1=0&pfe1=2&u=-1' class='red'><?=plural($welcome->docUnsigned, 'unsigned document')?></a>
                    <? } ?> 
                    <? if (empty($welcome->docUnsigned) && empty($welcome->docUnreviewed)) { ?>
                      <a href='documents.php' class='blue'>Documents</a>
                    <? } ?>
                  </div>
                </td>
              </tr>
            </table>
          </div>
          <div class='link'>
            <table>
              <tr>
                <td class='h2'><a href="messages.php" class="h2">Messages</a></td>
                <td class='img'><a href="messages.php"><img src='img/welcome/8messages.png' /></a></td>
                <td class='w'>
                  <div class='st'>
                    <? if ($welcome->msgUnread) { ?>
                      <a href='messages.php' class='red'><?=plural($welcome->msgUnread, 'unread message')?></a>
                    <? } else { ?>
                      <a href='messages.php' class='blue'>Messages</a>
                    <? } ?>
                  </div>
                  <div class='act'>
                    <a class='action msg' href='message.php'>Compose</a>
                  </div>
                </td>
              </tr>
            </table>
          </div>
          <div class='link off'>
            <table>
              <tr>
                <td class='h2'><a href="schedule.php" class="h2">Scheduling</a></td>
                <td class='img'><a href="schedule.php"><img src='img/welcome/3scheduling.png' /></a></td>
                <td class='w'>
                  <div class='st'>
                    <a href='schedule.php' class='blue'><?=plural($welcome->apptToday, 'appointment')?> pending</a> 
                  </div>
                  <div class='act'>
                    <a class='action calend' href='schedule.php?pc=1'>Show date...</a>
                    <a class='action patient' href='.'>Search</a>
                    <input type='text'>
                  </div>
                </td>
              </tr>
            </table>
          </div>
          <div class='link'>
            <table>
              <tr>
                <td class='h2'><a href="tracking.php" class="h2">Order<br>Tracking</a></td>
                <td class='img'><a href="tracking.php"><img src='img/welcome/4tracking.png' /></a></td>
                <td class='w'>
                  <div class='st'>
                    <? if ($welcome->orderUnsched) { ?>
                      <a href='tracking.php?pix=1' class='red'><?=plural($welcome->orderUnsched, 'unscheduled order')?></a>
                    <? } else { ?>
                      <a href='tracking.php' class='blue'>Order Tracking</a>
                    <? } ?>
                  </div>
                </td>
              </tr>
            </table>
          </div>
          <div class='link off'>
            <table>
              <tr>
                <td class='h2'><a href="reporting.php" class="h2">Reporting</a></td>
                <td class='img'><a href="reporting.php"><img src='img/welcome/7reports.png' /></a></td>
                <td class='w'>
                  <div class='st'>
                    <a href='reporting.php' class='blue'>Reporting</a> 
                  </div>
                  <div class='act'>
                    <a class='action add' href='reporting.php?npr=1'>Ad-hoc report</a>
                  </div>
                </td>
              </tr>
            </table>
          </div>
          <div class='link'>
            <table>
              <tr>
                <td class='h2'><a href="scanning.php" class="h2">Scanning</a></td>
                <td class='img'><a href="scanning.php"><img src='img/welcome/5scanning.png' /></a></td>
                <td class='w'>
                  <div class='st'>
                    <a href='scanning.php' class='blue'>Scanning</a> 
                  </div>
                </td>
              </tr>
            </table>
          </div>
          <div class='link off'>
            <table>
              <tr>
                <td class='h2'><a href="profile.php" class="h2">Profile and<br>Settings</a></td>
                <td class='img'><a href="profile.php"><img src='img/welcome/6profile.png' /></a></td>
                <td class='w'>
                  <div class='st'>
                    <a href='profile.php' class='blue'>Profile and Settings</a>
                  </div>
                  <a class='action pencil' href='profile.php?cp=1'>Change my password</a>
                </td>
              </tr>
            </table>
          </div>
          <div style='text-align:center; padding-top:20px; font-size:11pt'>
            Note... this is not where this will be.... but <a href='income.php' class='red'>click here</a>
          </div>
        </div>
      <? // renderBoxEnd() ?>
    <? _BODY() ?>
  </body>
</html>
