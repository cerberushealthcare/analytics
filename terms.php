<?
set_include_path('sec');
require_once 'config/MyEnv.php';
$tos = MyEnv::$TOS_VERSION;
set_include_path('../');
?>
//
<? $title = 'Clicktate - Terms of Service' ?>
<? include "inc/hheader.php" ?>
<div id="body" style="background:white">
  <div class="content center">
    <h1>Terms of Service</h1>
  </div>
  <div class="wm">      
    <? include "sec/tos/$tos.html" ?>
  </div>
</div>
<? include "inc/hfooter.php" ?>
