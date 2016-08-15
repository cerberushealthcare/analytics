<?php
set_include_path('../../');
require_once "bat/batch.php";
//
blog_start($argv);
$args = arguments($argv);
$folder = $args[0];
require_once "php/cbat/csv-import/$folder/CsvImport.php";
CsvImport::exec();
blog_end();