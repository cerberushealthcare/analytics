<?php 
require_once "inc/requireLogin.php";
require_once "inc/uiFunctions.php";
?>
<html>
  <!-- Copyright (c)2006 by LCD Solutions, Inc.  All rights reserved. -->
  <!-- http://www.clicktate.com -->
  <head>
    <title>clicktate : rx</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta http-equiv="Content-Script-Type" content="text/javascript" />
    <meta http-equiv="Content-Language" content="en-us" />
    <meta name="keywords" content="dictate, dictation, medical note, document generation, note generation" />
    <meta name="description" content="Automated document generation." />
    <style>
BODY {
  font-family:Arial;
  font-size:10pt;
  margin:0;
}
TD {
  font-size:10pt;
  margin:0;
}
DIV#body {
  margin-top:0in;
  margin-left:0.2in;
}
DIV.brk {
  page-break-after:always;
}
DIV#rx {
  width:350px;
  text-align:left;
  border:1px solid #a0a0a0;
  padding:10px 5px;
  margin-top:0in;
  margin-left:0in;
}
TD#td1, TD#td3 {
  padding-right:0.4in;
}
TD#td1, TD#td2 {
  padding-bottom:0.4in;
}
DIV#head {
  text-align:center;
}
DIV#doc {
  margin-bottom:0.2em;
  font-family:Georgia;
  font-size:10pt;
  font-weight:bold;
}
DIV#practice {
  margin-bottom:2em;
  font-size:8pt;
  line-height:1.4em;
}
UL {
  list-style:none;
  margin-left:20px;  
}
LI {
  line-height:1.4em;
}
LI SPAN {
  font-weight:bold;
}
LABEL {
  width:50px;
  font-size:9pt;
}
LABEL#date {
  width:40px;
}
LI#med-name {
  margin-bottom:1.4em;
}
LI#dns {
  margin:1.4em 0;
}
SPAN#name {
  width:240px;
}
HR {
  height:1px;
  border:1px dashed #707070;
}
SPAN#symbol {
  position:absolute;
  margin-top:-10px;
  font-size:60pt;
  color:#797979;
}
DIV#med DIV {
  margin-left:80px;
}
DIV#sig {
  margin:3em 0.5em 1em 0.5em;
}
DIV#sig LABEL {
  width:auto;
}
DIV#sig SPAN {
  width:255px;
  border-bottom:1px solid #707070;
}
    </style>
  </head>
  <body>
    <div id="body" class="brk">
      <table border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td id="td1">
            <div id="rx">
              <div id="head">
                <div id="doc">
                  <?=stripslashes($_GET["doc"]) ?>
                </div>
                <div id="practice">
                  <?=stripslashes($_GET["practice1"]) ?><br>
                  <?=stripslashes($_GET["practice2"]) ?><br>
                  <?=stripslashes($_GET["practice3"]) ?><br>
                  <?=stripslashes($_GET["practice4"]) ?>
                </div>
              </div>
              <div id="patient">
                <ul>
                  <li>
                    <label>Date:</label>
                    <span><?=stripslashes($_GET["date"]) ?></span>
                  </li>
                  <li style="padding-top:10px">
                    <label>Name:</label>
                    <span><?=stripslashes($_GET["patient"]) ?></span>
                  </li>
                  <li>
                    <label>DOB:</label>
                    <span><?=stripslashes($_GET["dob"]) ?></span>
                  </li>
                </ul>
              </div>
              <hr />
              <div id="med">
                <span id="symbol">&#8478;</span>
                <ul>
                  <li id="med-name">
                    <label>&nbsp;</label>
                    <span><?=stripslashes($_GET["med"]) ?></span>
                  </li>
                  <li>
                    <label>Sig:</label>
                    <span><?=stripslashes($_GET["sig"]) ?></span>
                  </li>
                  <li>
                    <label>Disp:</label>
                    <span><?=stripslashes($_GET["disp"]) ?></span>
                  </li>
                  <li>
                    <label>Refills:</label>
                    <span><?=stripslashes($_GET["refills"]) ?></span>
                  </li>
                  <li id="dns">
                    <label>&nbsp;</label>
                    <span><?=stripslashes($_GET["dns"]) ?></span>
                  </li>
                </ul>
              </div>
              <div id="sig">
                <label>Signature</label>
                <span></span>
              </div>
            </div>
          </td>
          <td id="td2">
            <div id="rx">
              <div id="head">
                <div id="doc">
                  <?=stripslashes($_GET["doc"]) ?>
                </div>
                <div id="practice">
                  <?=stripslashes($_GET["practice1"]) ?><br>
                  <?=stripslashes($_GET["practice2"]) ?><br>
                  <?=stripslashes($_GET["practice3"]) ?><br>
                  <?=stripslashes($_GET["practice4"]) ?>
                </div>
              </div>
              <div id="patient">
                <ul>
                  <li>
                    <label>Date:</label>
                    <span><?=stripslashes($_GET["date"]) ?></span>
                  </li>
                  <li style="padding-top:10px">
                    <label>Name:</label>
                    <span><?=stripslashes($_GET["patient"]) ?></span>
                  </li>
                  <li>
                    <label>DOB:</label>
                    <span><?=stripslashes($_GET["dob"]) ?></span>
                  </li>
                </ul>
              </div>
              <hr />
              <div id="med">
                <span id="symbol">&#8478;</span>
                <ul>
                  <li id="med-name">
                    <label>&nbsp;</label>
                    <span><?=stripslashes($_GET["med"]) ?></span>
                  </li>
                  <li>
                    <label>Sig:</label>
                    <span><?=stripslashes($_GET["sig"]) ?></span>
                  </li>
                  <li>
                    <label>Disp:</label>
                    <span><?=stripslashes($_GET["disp"]) ?></span>
                  </li>
                  <li>
                    <label>Refills:</label>
                    <span><?=stripslashes($_GET["refills"]) ?></span>
                  </li>
                  <li id="dns">
                    <label>&nbsp;</label>
                    <span><?=stripslashes($_GET["dns"]) ?></span>
                  </li>
                </ul>
              </div>
              <div id="sig">
                <label>Signature</label>
                <span></span>
              </div>
            </div>
          </td>
        </tr>
        <tr>
          <td id="td3">
            <div id="rx">
              <div id="head">
                <div id="doc">
                  <?=stripslashes($_GET["doc"]) ?>
                </div>
                <div id="practice">
                  <?=stripslashes($_GET["practice1"]) ?><br>
                  <?=stripslashes($_GET["practice2"]) ?><br>
                  <?=stripslashes($_GET["practice3"]) ?><br>
                  <?=stripslashes($_GET["practice4"]) ?>
                </div>
              </div>
              <div id="patient">
                <ul>
                  <li>
                    <label>Date:</label>
                    <span><?=stripslashes($_GET["date"]) ?></span>
                  </li>
                  <li style="padding-top:10px">
                    <label>Name:</label>
                    <span><?=stripslashes($_GET["patient"]) ?></span>
                  </li>
                  <li>
                    <label>DOB:</label>
                    <span><?=stripslashes($_GET["dob"]) ?></span>
                  </li>
                </ul>
              </div>
              <hr />
              <div id="med">
                <span id="symbol">&#8478;</span>
                <ul>
                  <li id="med-name">
                    <label>&nbsp;</label>
                    <span><?=stripslashes($_GET["med"]) ?></span>
                  </li>
                  <li>
                    <label>Sig:</label>
                    <span><?=stripslashes($_GET["sig"]) ?></span>
                  </li>
                  <li>
                    <label>Disp:</label>
                    <span><?=stripslashes($_GET["disp"]) ?></span>
                  </li>
                  <li>
                    <label>Refills:</label>
                    <span><?=stripslashes($_GET["refills"]) ?></span>
                  </li>
                  <li id="dns">
                    <label>&nbsp;</label>
                    <span><?=stripslashes($_GET["dns"]) ?></span>
                  </li>
                </ul>
              </div>
              <div id="sig">
                <label>Signature</label>
                <span></span>
              </div>
            </div>
          </td>
          <td id="td4">
            <div id="rx">
              <div id="head">
                <div id="doc">
                  <?=stripslashes($_GET["doc"]) ?>
                </div>
                <div id="practice">
                  <?=stripslashes($_GET["practice1"]) ?><br>
                  <?=stripslashes($_GET["practice2"]) ?><br>
                  <?=stripslashes($_GET["practice3"]) ?><br>
                  <?=stripslashes($_GET["practice4"]) ?>
                </div>
              </div>
              <div id="patient">
                <ul>
                  <li>
                    <label>Date:</label>
                    <span><?=stripslashes($_GET["date"]) ?></span>
                  </li>
                  <li style="padding-top:10px">
                    <label>Name:</label>
                    <span><?=stripslashes($_GET["patient"]) ?></span>
                  </li>
                  <li>
                    <label>DOB:</label>
                    <span><?=stripslashes($_GET["dob"]) ?></span>
                  </li>
                </ul>
              </div>
              <hr />
              <div id="med">
                <span id="symbol">&#8478;</span>
                <ul>
                  <li id="med-name">
                    <label>&nbsp;</label>
                    <span><?=stripslashes($_GET["med"]) ?></span>
                  </li>
                  <li>
                    <label>Sig:</label>
                    <span><?=stripslashes($_GET["sig"]) ?></span>
                  </li>
                  <li>
                    <label>Disp:</label>
                    <span><?=stripslashes($_GET["disp"]) ?></span>
                  </li>
                  <li>
                    <label>Refills:</label>
                    <span><?=stripslashes($_GET["refills"]) ?></span>
                  </li>
                  <li id="dns">
                    <label>&nbsp;</label>
                    <span><?=stripslashes($_GET["dns"]) ?></span>
                  </li>
                </ul>
              </div>
              <div id="sig">
                <label>Signature</label>
                <span></span>
              </div>
            </div>
          </div>
        </td>
      </tr>
    </table>
    </div>
    <div id="body" class="brk">
      <table border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td id="td1">
            <div id="rx">
              <div id="head">
                <div id="doc">
                  <?=stripslashes($_GET["doc"]) ?>
                </div>
                <div id="practice">
                  <?=stripslashes($_GET["practice1"]) ?><br>
                  <?=stripslashes($_GET["practice2"]) ?><br>
                  <?=stripslashes($_GET["practice3"]) ?><br>
                  <?=stripslashes($_GET["practice4"]) ?>
                </div>
              </div>
              <div id="patient">
                <ul>
                  <li>
                    <label>Date:</label>
                    <span><?=stripslashes($_GET["date"]) ?></span>
                  </li>
                  <li style="padding-top:10px">
                    <label>Name:</label>
                    <span><?=stripslashes($_GET["patient"]) ?></span>
                  </li>
                  <li>
                    <label>DOB:</label>
                    <span><?=stripslashes($_GET["dob"]) ?></span>
                  </li>
                </ul>
              </div>
              <hr />
              <div id="med">
                <span id="symbol">&#8478;</span>
                <ul>
                  <li id="med-name">
                    <label>&nbsp;</label>
                    <span><?=stripslashes($_GET["med"]) ?></span>
                  </li>
                  <li>
                    <label>Sig:</label>
                    <span><?=stripslashes($_GET["sig"]) ?></span>
                  </li>
                  <li>
                    <label>Disp:</label>
                    <span><?=stripslashes($_GET["disp"]) ?></span>
                  </li>
                  <li>
                    <label>Refills:</label>
                    <span><?=stripslashes($_GET["refills"]) ?></span>
                  </li>
                  <li id="dns">
                    <label>&nbsp;</label>
                    <span><?=stripslashes($_GET["dns"]) ?></span>
                  </li>
                </ul>
              </div>
              <div id="sig">
                <label>Signature</label>
                <span></span>
              </div>
            </div>
          </td>
          <td id="td2">
            <div id="rx">
              <div id="head">
                <div id="doc">
                  <?=stripslashes($_GET["doc"]) ?>
                </div>
                <div id="practice">
                  <?=stripslashes($_GET["practice1"]) ?><br>
                  <?=stripslashes($_GET["practice2"]) ?><br>
                  <?=stripslashes($_GET["practice3"]) ?><br>
                  <?=stripslashes($_GET["practice4"]) ?>
                </div>
              </div>
              <div id="patient">
                <ul>
                  <li>
                    <label>Date:</label>
                    <span><?=stripslashes($_GET["date"]) ?></span>
                  </li>
                  <li style="padding-top:10px">
                    <label>Name:</label>
                    <span><?=stripslashes($_GET["patient"]) ?></span>
                  </li>
                  <li>
                    <label>DOB:</label>
                    <span><?=stripslashes($_GET["dob"]) ?></span>
                  </li>
                </ul>
              </div>
              <hr />
              <div id="med">
                <span id="symbol">&#8478;</span>
                <ul>
                  <li id="med-name">
                    <label>&nbsp;</label>
                    <span><?=stripslashes($_GET["med"]) ?></span>
                  </li>
                  <li>
                    <label>Sig:</label>
                    <span><?=stripslashes($_GET["sig"]) ?></span>
                  </li>
                  <li>
                    <label>Disp:</label>
                    <span><?=stripslashes($_GET["disp"]) ?></span>
                  </li>
                  <li>
                    <label>Refills:</label>
                    <span><?=stripslashes($_GET["refills"]) ?></span>
                  </li>
                  <li id="dns">
                    <label>&nbsp;</label>
                    <span><?=stripslashes($_GET["dns"]) ?></span>
                  </li>
                </ul>
              </div>
              <div id="sig">
                <label>Signature</label>
                <span></span>
              </div>
            </div>
          </td>
        </tr>
        <tr>
          <td id="td3">
            <div id="rx">
              <div id="head">
                <div id="doc">
                  <?=stripslashes($_GET["doc"]) ?>
                </div>
                <div id="practice">
                  <?=stripslashes($_GET["practice1"]) ?><br>
                  <?=stripslashes($_GET["practice2"]) ?><br>
                  <?=stripslashes($_GET["practice3"]) ?><br>
                  <?=stripslashes($_GET["practice4"]) ?>
                </div>
              </div>
              <div id="patient">
                <ul>
                  <li>
                    <label>Date:</label>
                    <span><?=stripslashes($_GET["date"]) ?></span>
                  </li>
                  <li style="padding-top:10px">
                    <label>Name:</label>
                    <span><?=stripslashes($_GET["patient"]) ?></span>
                  </li>
                  <li>
                    <label>DOB:</label>
                    <span><?=stripslashes($_GET["dob"]) ?></span>
                  </li>
                </ul>
              </div>
              <hr />
              <div id="med">
                <span id="symbol">&#8478;</span>
                <ul>
                  <li id="med-name">
                    <label>&nbsp;</label>
                    <span><?=stripslashes($_GET["med"]) ?></span>
                  </li>
                  <li>
                    <label>Sig:</label>
                    <span><?=stripslashes($_GET["sig"]) ?></span>
                  </li>
                  <li>
                    <label>Disp:</label>
                    <span><?=stripslashes($_GET["disp"]) ?></span>
                  </li>
                  <li>
                    <label>Refills:</label>
                    <span><?=stripslashes($_GET["refills"]) ?></span>
                  </li>
                  <li id="dns">
                    <label>&nbsp;</label>
                    <span><?=stripslashes($_GET["dns"]) ?></span>
                  </li>
                </ul>
              </div>
              <div id="sig">
                <label>Signature</label>
                <span></span>
              </div>
            </div>
          </td>
          <td id="td4">
            <div id="rx">
              <div id="head">
                <div id="doc">
                  <?=stripslashes($_GET["doc"]) ?>
                </div>
                <div id="practice">
                  <?=stripslashes($_GET["practice1"]) ?><br>
                  <?=stripslashes($_GET["practice2"]) ?><br>
                  <?=stripslashes($_GET["practice3"]) ?><br>
                  <?=stripslashes($_GET["practice4"]) ?>
                </div>
              </div>
              <div id="patient">
                <ul>
                  <li>
                    <label>Date:</label>
                    <span><?=stripslashes($_GET["date"]) ?></span>
                  </li>
                  <li style="padding-top:10px">
                    <label>Name:</label>
                    <span><?=stripslashes($_GET["patient"]) ?></span>
                  </li>
                  <li>
                    <label>DOB:</label>
                    <span><?=stripslashes($_GET["dob"]) ?></span>
                  </li>
                </ul>
              </div>
              <hr />
              <div id="med">
                <span id="symbol">&#8478;</span>
                <ul>
                  <li id="med-name">
                    <label>&nbsp;</label>
                    <span><?=stripslashes($_GET["med"]) ?></span>
                  </li>
                  <li>
                    <label>Sig:</label>
                    <span><?=stripslashes($_GET["sig"]) ?></span>
                  </li>
                  <li>
                    <label>Disp:</label>
                    <span><?=stripslashes($_GET["disp"]) ?></span>
                  </li>
                  <li>
                    <label>Refills:</label>
                    <span><?=stripslashes($_GET["refills"]) ?></span>
                  </li>
                  <li id="dns">
                    <label>&nbsp;</label>
                    <span><?=stripslashes($_GET["dns"]) ?></span>
                  </li>
                </ul>
              </div>
              <div id="sig">
                <label>Signature</label>
                <span></span>
              </div>
            </div>
          </div>
        </td>
      </tr>
    </table>
    </div>
    <div id="body">
      <table border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td id="td1">
            <div id="rx">
              <div id="head">
                <div id="doc">
                  <?=stripslashes($_GET["doc"]) ?>
                </div>
                <div id="practice">
                  <?=stripslashes($_GET["practice1"]) ?><br>
                  <?=stripslashes($_GET["practice2"]) ?><br>
                  <?=stripslashes($_GET["practice3"]) ?><br>
                  <?=stripslashes($_GET["practice4"]) ?>
                </div>
              </div>
              <div id="patient">
                <ul>
                  <li>
                    <label>Date:</label>
                    <span><?=stripslashes($_GET["date"]) ?></span>
                  </li>
                  <li style="padding-top:10px">
                    <label>Name:</label>
                    <span><?=stripslashes($_GET["patient"]) ?></span>
                  </li>
                  <li>
                    <label>DOB:</label>
                    <span><?=stripslashes($_GET["dob"]) ?></span>
                  </li>
                </ul>
              </div>
              <hr />
              <div id="med">
                <span id="symbol">&#8478;</span>
                <ul>
                  <li id="med-name">
                    <label>&nbsp;</label>
                    <span><?=stripslashes($_GET["med"]) ?></span>
                  </li>
                  <li>
                    <label>Sig:</label>
                    <span><?=stripslashes($_GET["sig"]) ?></span>
                  </li>
                  <li>
                    <label>Disp:</label>
                    <span><?=stripslashes($_GET["disp"]) ?></span>
                  </li>
                  <li>
                    <label>Refills:</label>
                    <span><?=stripslashes($_GET["refills"]) ?></span>
                  </li>
                  <li id="dns">
                    <label>&nbsp;</label>
                    <span><?=stripslashes($_GET["dns"]) ?></span>
                  </li>
                </ul>
              </div>
              <div id="sig">
                <label>Signature</label>
                <span></span>
              </div>
            </div>
          </td>
          <td id="td2">
            <div id="rx">
              <div id="head">
                <div id="doc">
                  <?=stripslashes($_GET["doc"]) ?>
                </div>
                <div id="practice">
                  <?=stripslashes($_GET["practice1"]) ?><br>
                  <?=stripslashes($_GET["practice2"]) ?><br>
                  <?=stripslashes($_GET["practice3"]) ?><br>
                  <?=stripslashes($_GET["practice4"]) ?>
                </div>
              </div>
              <div id="patient">
                <ul>
                  <li>
                    <label>Date:</label>
                    <span><?=stripslashes($_GET["date"]) ?></span>
                  </li>
                  <li style="padding-top:10px">
                    <label>Name:</label>
                    <span><?=stripslashes($_GET["patient"]) ?></span>
                  </li>
                  <li>
                    <label>DOB:</label>
                    <span><?=stripslashes($_GET["dob"]) ?></span>
                  </li>
                </ul>
              </div>
              <hr />
              <div id="med">
                <span id="symbol">&#8478;</span>
                <ul>
                  <li id="med-name">
                    <label>&nbsp;</label>
                    <span><?=stripslashes($_GET["med"]) ?></span>
                  </li>
                  <li>
                    <label>Sig:</label>
                    <span><?=stripslashes($_GET["sig"]) ?></span>
                  </li>
                  <li>
                    <label>Disp:</label>
                    <span><?=stripslashes($_GET["disp"]) ?></span>
                  </li>
                  <li>
                    <label>Refills:</label>
                    <span><?=stripslashes($_GET["refills"]) ?></span>
                  </li>
                  <li id="dns">
                    <label>&nbsp;</label>
                    <span><?=stripslashes($_GET["dns"]) ?></span>
                  </li>
                </ul>
              </div>
              <div id="sig">
                <label>Signature</label>
                <span></span>
              </div>
            </div>
          </td>
        </tr>
        <tr>
          <td id="td3">
            <div id="rx">
              <div id="head">
                <div id="doc">
                  <?=stripslashes($_GET["doc"]) ?>
                </div>
                <div id="practice">
                  <?=stripslashes($_GET["practice1"]) ?><br>
                  <?=stripslashes($_GET["practice2"]) ?><br>
                  <?=stripslashes($_GET["practice3"]) ?><br>
                  <?=stripslashes($_GET["practice4"]) ?>
                </div>
              </div>
              <div id="patient">
                <ul>
                  <li>
                    <label>Date:</label>
                    <span><?=stripslashes($_GET["date"]) ?></span>
                  </li>
                  <li style="padding-top:10px">
                    <label>Name:</label>
                    <span><?=stripslashes($_GET["patient"]) ?></span>
                  </li>
                  <li>
                    <label>DOB:</label>
                    <span><?=stripslashes($_GET["dob"]) ?></span>
                  </li>
                </ul>
              </div>
              <hr />
              <div id="med">
                <span id="symbol">&#8478;</span>
                <ul>
                  <li id="med-name">
                    <label>&nbsp;</label>
                    <span><?=stripslashes($_GET["med"]) ?></span>
                  </li>
                  <li>
                    <label>Sig:</label>
                    <span><?=stripslashes($_GET["sig"]) ?></span>
                  </li>
                  <li>
                    <label>Disp:</label>
                    <span><?=stripslashes($_GET["disp"]) ?></span>
                  </li>
                  <li>
                    <label>Refills:</label>
                    <span><?=stripslashes($_GET["refills"]) ?></span>
                  </li>
                  <li id="dns">
                    <label>&nbsp;</label>
                    <span><?=stripslashes($_GET["dns"]) ?></span>
                  </li>
                </ul>
              </div>
              <div id="sig">
                <label>Signature</label>
                <span></span>
              </div>
            </div>
          </td>
          <td id="td4">
            <div id="rx">
              <div id="head">
                <div id="doc">
                  <?=stripslashes($_GET["doc"]) ?>
                </div>
                <div id="practice">
                  <?=stripslashes($_GET["practice1"]) ?><br>
                  <?=stripslashes($_GET["practice2"]) ?><br>
                  <?=stripslashes($_GET["practice3"]) ?><br>
                  <?=stripslashes($_GET["practice4"]) ?>
                </div>
              </div>
              <div id="patient">
                <ul>
                  <li>
                    <label>Date:</label>
                    <span><?=stripslashes($_GET["date"]) ?></span>
                  </li>
                  <li style="padding-top:10px">
                    <label>Name:</label>
                    <span><?=stripslashes($_GET["patient"]) ?></span>
                  </li>
                  <li>
                    <label>DOB:</label>
                    <span><?=stripslashes($_GET["dob"]) ?></span>
                  </li>
                </ul>
              </div>
              <hr />
              <div id="med">
                <span id="symbol">&#8478;</span>
                <ul>
                  <li id="med-name">
                    <label>&nbsp;</label>
                    <span><?=stripslashes($_GET["med"]) ?></span>
                  </li>
                  <li>
                    <label>Sig:</label>
                    <span><?=stripslashes($_GET["sig"]) ?></span>
                  </li>
                  <li>
                    <label>Disp:</label>
                    <span><?=stripslashes($_GET["disp"]) ?></span>
                  </li>
                  <li>
                    <label>Refills:</label>
                    <span><?=stripslashes($_GET["refills"]) ?></span>
                  </li>
                  <li id="dns">
                    <label>&nbsp;</label>
                    <span><?=stripslashes($_GET["dns"]) ?></span>
                  </li>
                </ul>
              </div>
              <div id="sig">
                <label>Signature</label>
                <span></span>
              </div>
            </div>
          </div>
        </td>
      </tr>
    </table>
  </body>
</html>
<script>
// open print dialog
</script>