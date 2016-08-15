<?php
require_once 'php/data/rec/sql/_SqlRec.php';
//
abstract class LabRec extends SqlRec implements AutoEncrypt {
  /*
  public $labId;
  public $uid;
  public $name;
  public $status;
  public $sendMethod;
  public $sftpFolder;
  public $id;
  public $pw;
  public $address;
  public $contact;
  */
  //
  const STATUS_CONFIGURING = 0;
  const STATUS_ACTIVE = 1;
  const STATUS_INACTIVE = 9;
  //
  const SEND_METHOD_WS = 1;  /* They call our webservice */
  const SEND_METHOD_SFTP = 2;  /* They push file into our SFTP server */
  const SEND_METHOD_SFTP_PULL = 3;  /* We pull results from their SFTP server */
  // 
  public function getSqlTable() {
    return 'labs';
  }
  public function getEncryptedFids() {
    return array('id','pw');
  }
}