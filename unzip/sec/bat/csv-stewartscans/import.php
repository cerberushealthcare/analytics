<?php
set_include_path('../../');
ini_set('memory_limit', '1024M');
require_once "bat/batch.php";
require_once "php/cbat/csv-stewartscans/Import.php";
//
blog_start($argv);
Import::exec();
blog_end();