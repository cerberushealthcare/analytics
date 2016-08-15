<?php
require_once 'batch/mass-mailer/MassEmail.php';
//
class EmailCampaign extends MassEmail {
  //
  public $subject = 'Important Notice About Your Clicktate Account';
  public $bcc = 'wghornsby@gmail.com';
  //
  static $BODY_FILE = 'batch/mass-mailer/cert-migrate/body.html';
  static $BODY_FIELDS = array();
}