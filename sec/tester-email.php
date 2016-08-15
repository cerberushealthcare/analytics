<?php
require_once 'php/data/LoginSession.php';
require_once 'php/data/email/Email.php';
//
LoginSession::verify_forServer();
//
class MassEmail extends Email {
  //
  public $subject = 'Clicktate';
  //
  public $from = 'Clicktate <info@clicktatemail.info>';
  public $replyTo = 'info@clicktatemail.info';
  public $returnPath = 'info@clicktatemail.info';
  //
  /**
   * @param MassCsvFile $file
   */
  static function create() {
    $me = new static();
    $me->to = 'wghornsby@gmail.com';
    $me->message = 'Hello';
    return $me;
  }
}
//
$email = MassEmail::create();
p_r($email);
$success = $email->mail();
p_r($success === false, 'success is false?');
