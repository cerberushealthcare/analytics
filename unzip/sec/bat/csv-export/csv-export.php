<?php
set_include_path('../../');
ini_set('display_errors', '1');
ini_set('memory_limit', '1024M');
require_once "bat/batch.php";
require_once "php/cbat/csv-export/papyrus/CsvExport.php";
//
blog_start($argv);
CsvExport::exec();
blog_end();