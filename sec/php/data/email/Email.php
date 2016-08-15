<?php
require_once 'php/data/Html.php';
//
class Email {
  //
  public $to;
  public $subject;
  public $message;
  //
  /* Headers */
  public $from = 'info@clicktatemail.info';
  public $cc;
  public $bcc;
  public $replyTo = 'info@clicktatemail.info';
  public $returnPath = 'info@clicktatemail.info';
  public $mimeVersion = '1.0';
  public $contentType = 'text/html; charset=UTF-8';
  //
  private $_html;
  //
  public function html() {
    if ($this->_html == null)
      $this->_html = new Html();
    return $this->_html;
  }
  public function loadMessage($filename) {
    $this->message = file_get_contents($filename, true);
  }
  public function /*bool*/mail() {
    $success = null;
    if ($this->_html)
      $this->message = $this->_html->out();
    if (MyEnv::$SEND_EMAIL) 
      $success = mail($this->to, $this->subject, $this->message, $this->getHeader());
    if (MyEnv::$BATCH) 
      blog($this, 'MAIL TO SEND');
    else
      logit_r($this, 'MAIL TO SEND');
    return $success;
  }
  //
  protected function getHeader() {
    return implode("\r\n", $this->getHeaders());
  }
  protected function getHeaders() {
    $a = array();
    $a[] = 'From: ' . $this->from;
    $a[] = 'Reply-To: ' . $this->replyTo ?: $this->from;
    $a[] = 'Return-Path: ' . $this->returnPath ?: $this->from;
    if ($this->cc)
      $a[] = 'Cc: ' . $this->cc;
    if ($this->bcc)
      $a[] = 'Bcc: ' . $this->bcc;
    $a[] = 'MIME-Version: ' . $this->mimeVersion;
    $a[] = 'Content-Type: ' . $this->contentType;
    return $a;
  }
}
class Email_Admin extends Email {
  //
  public $to = 'wghornsby@clicktatemail.com, pstewart@clicktatemail.com, mmckinney@clicktatemail.com, cindy@clicktatemail.com';
  //
  static function send() {
    $e = new static();
    // $e->html()->p('Body of email');
    $e->mail();
  }
}
class Email_Alert extends Email_Admin {
  //
  protected function getHeaders() {
    $a = parent::getHeaders();
    $a[] = 'X-Priority: 1 (Highest)';
    $a[] = 'X-MSMail-Priority: High';
    return $a;
  }
}