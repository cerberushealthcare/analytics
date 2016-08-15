<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
set_include_path('../../');
require_once 'batch/_batch.php';
//
/**
 * mass-mailer.php [folder]  e.g. mass-mailer.php 2012-11-08
 */
$args = arguments($argv);
if (count($args) == 0)
  die('Must supply folder arg');
$folder = $args[0];
blog('Start mass-mailer.php, folder=' . $folder);
MassMailer::start($folder);
blog('Finish mass-mailer.php');
//
class MassMailer {
  //
  static function start($folder) {
    require_once "batch/mass-mailer/$folder/EmailCampaign.php";
    require_once "batch/mass-mailer/$folder/ToCsv.php";
    $file = ToCsv::load();
    EmailCampaign::send($file);
  }
}