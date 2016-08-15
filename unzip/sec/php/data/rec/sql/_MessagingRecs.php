<?php
require_once 'php/data/rec/sql/_SqlRec.php';
//
abstract class MsgThreadRec extends SqlRec implements AutoEncrypt {
  //
  /*
  public $threadId;
  public $userGroupId;
  public $clientId;
  public $creatorId;
  public $creator;
  public $dateCreated;
  public $dateToSend;
  public $dateClosed;
  public $type;
  public $status;
  public $priority;
  public $subject;
  public $stubType;
  public $stubId;
  */
  //
  const TYPE_GENERAL = 0;   // regular note
  const TYPE_ALERT = 1;
  const TYPE_PATIENT = 10;  // patient-portal note
  const TYPE_STUB_REVIEW = 20;
  static $TYPES = array(
    self::TYPE_GENERAL => 'Office',
    self::TYPE_ALERT => 'Alert',
    self::TYPE_PATIENT => 'Patient Portal');
  //
  const STATUS_OPEN = 1;
  const STATUS_CLOSED = 2;
  //
  const PRIORITY_NORMAL = 0;
  const PRIORITY_STAT = 9;
  static $PRIORITIES = array(
    self::PRIORITY_NORMAL => 'Normal',
    self::PRIORITY_STAT => 'STAT');
  //
  //  
  public function getSqlTable() {
    return 'msg_threads';
  }
  public function getEncryptedFids() {
    return array('subject');
  }
  public function isClosed() {
    return $this->status == self::STATUS_CLOSED;
  }
  public function isStat() {
    return $this->priority == self::PRIORITY_STAT;
  }
  //
  protected function authenticateClientId($cid) {
    if ($cid)  // allow nulls
      parent::authenticateClientId($cid);
  }
}
/**
 * Message Post 
 */
abstract class MsgPostRec extends SqlRec implements AutoEncrypt {
  //
  /*
  public $postId;
  public $threadId;
  public $action;
  public $dateCreated;
  public $authorType;
  public $authorId;
  public $author;
  public $body;
  public $sendTo;
  public $data;
  public $stubType;
  public $stubId;
  public $portalFile;
  */
  //
  const ACTION_CREATE = 0;
  const ACTION_REPLY = 1;
  const ACTION_CLOSE = 9;
  const ACTION_REVIEWED = 19;
  //
  const AUTHOR_TYPE_OFFICE = 0;
  const AUTHOR_TYPE_ALERT = 1;
  const AUTHOR_TYPE_PORTAL = 10;
  //
  public function getSqlTable() {
    return 'msg_posts';
  }
  public function getJsonFilters() {
    return array(
      'dateCreated' => JsonFilter::informalDateTime());
  }
  public function getEncryptedFids() {
    return array('body','data');
  }
}
/**
 * Message Inbox
 */
abstract class MsgInboxRec extends SqlRec {
  //
  /*
  public $inboxId;
  public $recipient;
  public $threadId;
  public $postId;  
  public $isRead;
  */
  //
  const IS_UNREAD = '0';
  const IS_READ = '1';
  const IS_SENT = '2';
  const IS_CLOSED = '9';
  const IS_UNREVIEWED = '10';
  const IS_REVIEWED = '19';
  //
  const ACCESS_READ = 0;
  const ACCESS_POST = 1;
  //
  public function getSqlTable() {
    return 'msg_inbox';
  }
  public function isUnread() {
    return $this->isRead == static::IS_UNREAD;
  }
}
