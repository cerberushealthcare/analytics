<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('memory_limit', '1024M');
set_include_path('../../');
require_once 'batch/_batch.php';
require_once 'batch/FileManager.php';
require_once 'php/data/rec/sql/_SqlRec.php';
require_once "batch/admin-ipc/SqlRecs.php";
//
/**
 * Admin IPC creator
 */
$args = arguments($argv);
$db = $args[0];
$ugid = $args[1];
echo '<pre>';
echo "ADMIN IPC CREATOR SQL\n";
echo "DB: $db\n";
echo "UGID: $ugid\n";
echo "Building SQL files...";
$fm = new FileManager_Sql($ugid, "USE $db;");
$fm->open('OfficeVisit');
$sessions = Session_Aov::fetchAll($ugid);
Proc_Aov::migrate($fm, $sessions);
$fm->close();
echo "Script complete.";
//
