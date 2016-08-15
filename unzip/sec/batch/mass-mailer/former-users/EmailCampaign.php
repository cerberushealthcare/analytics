<?php
require_once 'batch/mass-mailer/MassEmail.php';
//
class EmailCampaign extends MassEmail {
  //
  public $subject = 'Clicktate';
  //
  static $BODY_FILE = 'batch/mass-mailer/2012-11-08/body.html';
  static $BODY_FIELDS = array('greet');
}