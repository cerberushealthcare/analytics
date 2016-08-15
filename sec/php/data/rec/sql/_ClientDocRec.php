<?php
require_once 'php/data/rec/sql/_SqlRec.php';
//
abstract class ClientDocRec extends SqlRec implements AutoEncrypt {
  /*
  public $clientDocId;
  public $clientId;
  public $type;
  public $dateCreated;
  public $createdBy;
  public $html;
  */
  //
  const TYPE_REFERRAL_CARD = 1;
  static $TYPES = array(
    self::TYPE_REFERRAL_CARD => 'Referral Card');
  //
  public function getSqlTable() {
    return 'client_docs';
  }
  public function getEncryptedFids() {
    return array('html');
  }
  public function getLabel() {
    return static::$TYPES[$this->type]; 
  }
}

