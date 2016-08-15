<?php
require_once 'php/data/email/Email.php';
//
class MassEmail extends Email {
  //
  public $subject = 'Clicktate';
  //
  public $from = 'Clicktate <info@clicktatemail.info>';
  public $replyTo = 'info@clicktatemail.info';
  public $returnPath = 'info@clicktatemail.info';
  //
  static $BODY_FILE;     // containing embedded %field% 
  static $BODY_FIELDS;   // array('field',..)
  //
  public function setBody($rec) {  
    $this->loadMessage(static::$BODY_FILE);
    $this->replaceFields($rec);
  }
  protected function replaceFields($rec) {
    foreach (static::$BODY_FIELDS as $field)
      $this->replaceField($rec, $field);
  }
  protected function replaceField($rec, $field) {
    $from = "%$field%";
    $to = $rec->getValue($field);
    $this->message = str_replace($from, $to, $this->message);
  }
  // 
  /**
   * @param MassCsvFile $file
   */
  static function send($file) {
    foreach ($file->recs as $rec) {
      if ($rec->shouldSend()) { 
        $e = static::from($rec);
        echo "\nSending to " . $e->to . '...';
        $e->mail();
      }
    }
  }
  protected static function from($rec) {
    $e = new static();
    $e->to = $rec->email;
    $e->setBody($rec); 
    return $e; 
  }
}