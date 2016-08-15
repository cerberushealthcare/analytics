<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('memory_limit', '1024M');
set_include_path('../../');
require_once "bat/batch.php";
require_once "php/cbat/encryptor/hdata/HdCreator.php";
//
blog_start($argv);
$args = arguments($argv);
$ugid = $args[0];
if (strtoupper($ugid) == 'ALL')
  HdCreator::execAll();
else if ($ugid)
  HdCreator::exec($ugid);
blog_end();