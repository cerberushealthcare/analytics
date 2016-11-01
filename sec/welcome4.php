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
line-height:2em;
padding-left:10px;
}
TD.h2 {
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
    <?php BODY() ?>
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
      <?php // renderBoxStart('wide', null, null, 'box') ?>
        <div class='bc2'>
          <div class='link'>
            <table>
              <tr>
                <td class='h2'><h2>Patients</h2></td>
                <td class='img'><img src='img/welcome/1patients.png' /></td>
                <td class='w'>
                  <div class='st'>
                    <a href='.' class='blue'>There are 2,452 patients.</a>
                  </div>
                  <div class='act'>
                    <a class='action add' href='.'>New patient</a>
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
                <td class='h2'><h2>Documents</h2></td>
                <td class='img'><img src='img/welcome/2documents.png' /></td>
                <td class='w'>
                  <div class='st'>
                    <a href='.' class='red'>You have 25 items to review.</a> 
                  </div>
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
                  <div class='st'>
                    <a href='.' class='red'>You have 3 unread messages.</a>
                  </div>
                  <div class='act'>
                    <a class='action msg' href='.'>Compose</a>
                  </div>
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
                  <div class='st'>
                    <a href='.' class='blue'>There are 6 appointments for today.</a> 
                  </div>
                  <div class='act'>
                    <a class='action calend' href='.'>For date...</a>
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
                <td class='h2'><h2>Order<br>Tracking</h2></td>
                <td class='img'><img src='img/welcome/4tracking.png' /></td>
                <td class='w'>
                  <div class='st'>
                    <a href='.' class='red'>There are 2 unscheduled orders.</a> 
                  </div>
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
                  <div class='st'>
                    <a href='.' class='blue'>Reporting</a> 
                  </div>
                  <div class='act'>
                    <a class='action add' href='.'>Ad hoc patient report</a>
                  </div>
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
                  <div class='st'>
                    <a href='.' class='blue'>There are no unindexed scans.</a> 
                  </div>
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
                  <div class='st'>
                    <a href='.' class='blue'>Profile and Settings</a>
                  </div>
                  <a class='action pencil' href='.'>Change my password</a>
                </td>
              </tr>
            </table>
          </div>
        </div>
      <?php // renderBoxEnd() ?>
    <?php _BODY() ?>
  </body>
</html>
