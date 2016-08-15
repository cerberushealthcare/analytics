<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('memory_limit', '1024M');
set_include_path('../../../');
require_once 'batch/_batch.php';
require_once 'batch/FileManager.php';
require_once 'batch/SqlRec_Script.php';
require_once "batch/template-migrate/SqlRecs_Mig.php";
//
echo "Building SQL files...";
$fm = new FileManager_Sql('berk');
$fm->open('templates');
Par_Mig::migrate($fm, array('3224','3225','3226','3227'), '2013-01-28 23:00:00');
$fm->close();
echo "Script complete.";
//
