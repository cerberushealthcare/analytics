<?php
require_once 'php/data/LoginSession.php';
require_once 'php/data/rec/group-folder/GroupFolder_WsImport.php';
//
logit_r('Import!');
logit_r($_FILES, 'files');
logit_r($_POST, 'post');
$ugid = geta($_POST, 'ugid');
LoginSession::loginBatch($ugid, 'ws-import');
logit_r("Logged in as $ugid");
$file = GroupFolder_WsImport::open()->upload();
require_once "php/c/patient-import/ws-import/G$ugid/Importer.php";
Importer::import($file);