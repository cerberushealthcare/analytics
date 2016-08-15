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
text-align:right;
line-height:2em;
}
TD.st {
width:120px;
}
TD.st {
padding-right:10px;
}
DIV.st {
width:120px;
font-size:11pt;
}
A.action {
font-size:11pt;
margin-left:1em;
}
A.pencil {
background-position:-1px top;
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
                <td class='h2'><h2>Patients</h2></td>
                <td class='img'><img src='img/welcome/1patients.png' /></td>
                <td class='st'>
                  <a class='action list' href='.'>Patient list</a>
                </td>
                <td class='w'>
                  <a class='action add' href='.'>New patient</a>
                  <a class='action patient' href='.'>Search</a>
                  <input type='text'>
                </td>
              </tr>
            </table>
          </div>
          <div class='link off'>
            <table>
              <tr>
                <td class='h2'><h2>Documents</h2></td>
                <td class='img'><img src='img/welcome/2documents.png' /></td>
                <td class='w'>
                  <a class='action noimg' href='.' style='color:red'>25 items to review</a>
                </td>
              </tr>
            </table>
          </div>
          <div class='link'>
            <table>
              <tr>
                <td class='h2'><h2>Messages</h2></td>
                <td class='img'><img src='img/welcome/8messages.png' /></td>
                <td class='w'>
                  <a class='action noimg' href='.' style='color:red'>3 unread messages</a>
                  <a class='action msg' href='.'>Compose</a>
                </td>
              </tr>
            </table>
          </div>
          <div class='link off'>
            <table>
              <tr>
                <td class='h2'><h2>Scheduling</h2></td>
                <td class='img'><img src='img/welcome/3scheduling.png' /></td>
                <td class='w'>
                  <a class='action noimg' href='.'>For date...</a>
                  <a class='action patient' href='.'>Search</a>
                  <input type='text'>
                </td>
              </tr>
            </table>
          </div>
          <div class='link'>
            <table>
              <tr>
                <td class='h2'><h2>Order<br>Tracking</h2></td>
                <td class='img'><img src='img/welcome/4tracking.png' /></td>
                <td class='w'>
                  <a class='action noimg' href='.' style='color:red'>2 open orders (STAT)</a>
                </td>
              </tr>
            </table>
          </div>
          <div class='link off'>
            <table>
              <tr>
                <td class='h2'><h2>Reporting</h2></td>
                <td class='img'><img src='img/welcome/7reports.png' /></td>
                <td class='w'>
                  <a class='action add' href='.'>Ad hoc patient report</a>
                </td>
              </tr>
            </table>
          </div>
          <div class='link'>
            <table>
              <tr>
                <td class='h2'><h2>Scanning</h2></td>
                <td class='img'><img src='img/welcome/5scanning.png' /></td>
                <td class='w'>
                </td>
              </tr>
            </table>
          </div>
          <div class='link off'>
            <table>
              <tr>
                <td class='h2'><h2>Profile and<br>Settings</h2></td>
                <td class='img'><img src='img/welcome/6profile.png' /></td>
                <td class='w'>
                  <a class='action pencil' href='.'>Change my password</a>
                  <a class='action key2' href='.'>Support accounts</a>
                  <a class='action key2' href='.'>Portal accounts</a>
                </td>
              </tr>
            </table>
          </div>
        </div>
      <? // renderBoxEnd() ?>
    <? _BODY() ?>
  </body>
</html>
