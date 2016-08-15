<?php
require_once 'php/data/email/Email.php';
//
class Email_Test extends Email {
  //
  public $subject = 'Patient Portal Notification';
  //
  static function create($email) {
    $me = new static();
    $me->to = $email;
    $me->par("This is a notification that you have received a message to your medical patient portal.");
    $me->par_()->out("Please login to ")->a('https://www.clicktate.com/cert/sec/portal')->out('to read this message.')->_par();
    $me->par("If you have any problems logging in please contact your doctor's office.");
    return $me;
  }
}
$email = Email_Test::create('wghornsby@clicktatemail.com');
print_r($email);
$email->mail();
print_r('done');