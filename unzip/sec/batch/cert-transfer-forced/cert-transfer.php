<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('memory_limit', '1024M');
set_include_path('../../');
require_once 'batch/_batch.php';
require_once 'batch/FileManager.php';
require_once 'php/data/rec/sql/_SqlRec.php';
require_once "batch/cert-transfer-forced/sql/SqlRecs_Migrate.php";
//
/**
 * Certification transfer
 */
$args = arguments($argv);
$dbFrom = $args[0];
$dbTo = $args[1];
$ugid = $args[2];
echo '<pre>';
echo "BUILD CERT TRANSFER SQL\n";
echo "Database From: $dbFrom\n";
echo "Database To: $dbTo\n";
echo "UGID: $ugid\n";
$myHost = 'prod';
Dao_Migrate::setDb($dbFrom);
Dao_Cert::setDb($dbTo);
$pkmap = PkMapper::fetch();
echo "Building SQL files...";
$fm = new FileManager_Sql($ugid, "USE $dbTo;");
$fm->open('users');
UserGroup_Migrate::migrate($fm, $ugid, $pkmap);
User_Migrate::migrate($fm, $ugid);
TemplatePreset_Migrate::migrate($fm, $ugid);
$fm->open('clients', 20000);
Client_Migrate::migrate($fm, $ugid, $pkmap);
$fm->open('hm-sched-msg');
IpcHm_Migrate::migrate($fm, $ugid, $pkmap);
Sched_Migrate::migrate($fm, $ugid, $pkmap);
MsgThread_Migrate::migrate($fm, $ugid, $pkmap);
$fm->open('sessions', 7000);
Session_Migrate::migrate($fm, $ugid, $pkmap);
$fm->open('facesheet', 70000);
DataAllergy_Migrate::migrate($fm, $ugid, $pkmap);
DataDiagnosis_Migrate::migrate($fm, $ugid, $pkmap);
DataHm_Migrate::migrate($fm, $ugid, $pkmap);
DataImmun_Migrate::migrate($fm, $ugid, $pkmap);
DataMed_Migrate::migrate($fm, $ugid, $pkmap);
DataSync_Migrate::migrate($fm, $ugid, $pkmap);
DataVital_Migrate::migrate($fm, $ugid, $pkmap);
TrackItem_Migrate::migrate($fm, $ugid, $pkmap);
$fm->close();
echo "Building map file...";
$filename = "out/UG" . $ugid. "-map.csv";
$fp = @fopen($filename, "w");
$pkmap->write($fp, $dbFrom, $dbTo);
fclose($fp);
echo "Script complete.";
