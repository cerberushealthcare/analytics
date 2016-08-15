<?
ob_start('ob_gzhandler');
require_once "php/data/LoginSession.php";
require_once 'inc/uiFunctions.php';
require_once 'php/c/template-entry/TemplateEntry.php';
//
LoginSession::verify_forUser()->requires($login->Role->Artifact->noteCreate);
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Strict//EN'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <? HEAD('Quick Console', 'QuickConsolePage') ?>
    <? HEAD_UI('QuickConsole') ?>
    <style>
DIV.tuic {
  overflow-y:scroll;
}
DIV.tuis {
  font-size:10.5pt;
  padding-left:5px;
  padding-right:5px;
}
DIV.tuip {
  line-height:1.7em;
  padding-left:10px;
  padding-right:10px;
}
DIV.selector {
  width:200px;
  float:left;
  padding-right:10px;
}
DIV.menu {
  padding:2px 5px 7px 0;
  text-align:right;
}
A.template {
  background:url(img/big/blank2.png) no-repeat 0 2px;
  display:block;
  height:32px;
  line-height:32px;
  margin-bottom:2px;
  padding:2px 0 2px 31px;
  font-size:12pt;
  font-weight:bold;
}
A.section {
  padding:2px 5px 2px 20px;
  background:url(img/new/img3.png) no-repeat 0 1px !important;
  display:block;
  border:1px solid white;
  margin:0 0 3px 0;
}
A.green {
  color:green;
}
A.sbox {
  background:none !important;
  padding-left:0 !important;
  text-align:center;
  display:block;
  border:1px solid #e0e0e0;
  background-color:#F8F3DA !important;
  color:#000099 !important;
  text-decoration:none !important;
  text-transform:uppercase;
}
A.sectionl {
  background:url(img/new/img3.png) no-repeat left top;
  display:block;
  margin-bottom:2px;
}
A.paragraph {
  background:url(img/icons/paragraph.gif) no-repeat left top !important;
  font-family:Calibri,Tahoma,Arial !important;
  font-size:9pt !important;
  display:block;
  padding-left:16px !important;
  margin:8px 0 25px 14px;
}
A.pbox {
  background:none !important;
  padding-left:10px !important;
  border:1px solid white;
  border-bottom:1px dotted #c0c0c0;
  color:#909090 !important;
  text-decoration:none !important;
  margin:8px 0 4px 0;
}
A.parl {
  background:url(img/icons/paragraph.gif) no-repeat left top;
  display:block;
}
A.copy {
  background:url(img/icons/copy-word.gif) no-repeat left top;
}
A.clear {
  background:url(img/icons/page.png) no-repeat left top;
}
SPAN.tdiv {
  border-left:1px solid #c0c0c0;
  border-right:1px solid white;
  margin:0 5px;
}
DIV.docwindow {
  padding:5px;
  background-color:white;
  width:250px;
  height:100px;
  border:1px solid #e0e0e0;
}
A.qcsel {
  background-color:#DAECF8;
}
DIV.secwindow {
  padding:10px;
  background-color:white;
  width:250px;
  height:360px;
  border:1px solid #e0e0e0;
  overflow-y:scroll;
}
H3 {
  border-bottom:1px solid #c0c0c0;
}
DIV.tuip {
  margin:0.5em 0;
}
    </style>
  </head>
  <body>
    <? BODY() ?>
      <? TITLE('Quick Console') ?>
      <? _TITLE() ?>
      <? BOX() ?>
        <div id='tile'>
          <div class='spacer'>&nbsp;</div>
        </div>
      <? _BOX() ?>
    <? _BODY() ?>
  </body>
  <? CONSTANTS('QuickConsole') ?>
  <? START() ?>  
</html>
