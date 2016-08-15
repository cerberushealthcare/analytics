<?php
require_once 'batch/mass-mailer/MassEmail.php';
//
class EmailCampaign extends MassEmail {
  //
  public $subject = 'Receive Your Labs Electronically';
  public $bcc = 'wghornsby@gmail.com';
  //
  static $BODY_FILE = 'batch/mass-mailer/labs/body.html';
  static $BODY_FIELDS = array();
}