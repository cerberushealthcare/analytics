<?php
set_include_path('../../');
require_once "bat/batch.php";
require_once "php/cbat/csv-stewarthome/Import.php";
//
blog_start($argv);
Import::exec();
blog_end();