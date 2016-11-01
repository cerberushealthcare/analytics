<?
set_include_path('sec');
require_once 'config/MyEnv.php';
$tos = MyEnv::$TOS_VERSION;
set_include_path('../');
?>
//
<?php $title = 'Clicktate - Terms of Service' ?>
<?php include "inc/hheader.php" ?>
<div id="body" style="background:white">
  <div class="content center">
    <h1>Terms of Service</h1>
  </div>
  <div class="wm">      
    <?php include "sec/tos/$tos.html" ?>
  </div>
</div>
<?php include "inc/hfooter.php" ?>
