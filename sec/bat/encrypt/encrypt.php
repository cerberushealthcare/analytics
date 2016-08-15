<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('memory_limit', '1024M');
set_include_path('../../');
require_once "bat/batch.php";
//
blog_start($argv);
$args = arguments($argv);
$folder = $args[0];
require_once "php/cbat/encryptor/$folder/Encrypt.php";
Encrypt::exec();
blog_end();
