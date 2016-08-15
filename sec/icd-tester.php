<?php
require_once 'inc/requireLogin.php';
require_once 'php/data/csv/ICD9/ICD9_Import.php';
require_once 'php/data/csv/papyrus-import/Papyrus_Import.php';
//
echo '<pre>';
switch ($_GET['t']) {
  case '1':
    ICD9_Import::import();
    exit;
  case '2':
    Papyrus_Import::import();
    exit;
}
