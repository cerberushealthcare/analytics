<?php
require_once 'batch/mass-mailer/MassEmail.php';
//
class EmailCampaign extends MassEmail {
  //
  public $subject = 'Clicktate Trial';
  public $bcc = 'wghornsby@gmail.com';
  //
  static $BODY_FILE = 'batch/mass-mailer/trial-more-info/body.html';
  static $BODY_FIELDS = array('greet');
}