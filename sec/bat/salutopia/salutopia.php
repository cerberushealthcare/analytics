<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
set_include_path('../../');
require_once "bat/batch.php";
require_once "php/cbat/salutopia/Salutopia.php";
//
blog_start($argv);
Salutopia::exec();
blog_end();