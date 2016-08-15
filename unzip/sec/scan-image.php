<?php
require_once 'inc/requireLogin.php';
require_once 'php/data/rec/sql/Scanning.php';
//
if (isset($_GET['id']))
  Scanning::output($_GET['id'], geta($_GET, 'h', 0), geta($_GET, 'w', 0));