<?php
require_once 'batch/mass-mailer/MassEmail.php';
//
class EmailCampaign extends MassEmail {
  //
  public $from = 'careers@clicktatemail.com';
  public $replyTo = 'careers@clicktatemail.com';
  public $returnPath = 'careers@clicktatemail.com';
  public $subject = 'EMR Sales Position - LCD Solutions';
  public $bcc = 'wghornsby@clicktatemail.com';
  //
  static $BODY_FILE = 'batch/mass-mailer/sales-job/body.html';
  static $BODY_FIELDS = array('greet');
}