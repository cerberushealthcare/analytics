<?php
require_once "inc/tags.php";
require_once 'inc/require-login.php';
set_include_path('../');
require_once 'php/data/rec/sql/PortalScanning.php';
//
if (isset($_GET['id']))
  PortalScanning::output($_GET['id']);