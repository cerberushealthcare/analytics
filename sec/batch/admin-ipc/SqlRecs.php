<?php
class Session_Aov extends SqlRec implements ReadOnly, NoAuthenticate {
  //
  public $userGroupId;
  public $sessionId;
  public $dateService;
  public $closed;
  public $closedBy;
  public $dateClosed;
  public $clientId;
  //
  public function getSqlTable() {
    return 'sessions';
  }
  //
  static function fetchAll($ugid) {
    $c = static::asCriteria($ugid);
    return static::fetchAllBy($c, null, 0);
  }
  static function asCriteria($ugid) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->closed = CriteriaValue::greaterThanNumeric('0');
    $c->dateClosed = CriteriaValue::greaterThanOrEquals('2012-01-01');
    return $c;
  }
}
class Proc_Aov extends SqlRec implements ReadOnly {
  //
  public $procId;
  public $userGroupId;
  public $clientId;
  public $date;  
  public $ipc;
  public $priority;
  public $location;
  public $providerId;
  public $addrFacility;
  public $recipient;
  public $scanIndexId;
  public $userId;
  public $comments;
  //
  const IPC_OFFICEVISIT = 600186;
  //
  public function getSqlTable() {
    return 'procedures';
  }
  //
  static function migrate($fm, $sessions) {
    $mes = static::fromSessions($sessions);
    static::appendSqlInserts($fm, $mes);
  }
  static function fromSessions($sessions) {
    $mes = array();
    foreach ($sessions as $session) 
      $mes[] = static::fromSession($session);
    return $mes;
  }
  static function fromSession($rec) {
    $me = new static();
    $me->userGroupId = $rec->userGroupId;
    $me->clientId = $rec->clientId;
    $me->date = $rec->dateService;
    $me->ipc = static::IPC_OFFICEVISIT;
    $me->userId = $rec->closedBy;
    $me->comments = "SID $rec->sessionId";
    return $me;
  }
  static function appendSqlInserts($fm, $recs) {
    foreach ($recs as $rec)  
      static::appendSqlInsert($fm, $rec);
  }
  static function appendSqlInsert($fm, $rec) {
    $fm->write($rec->getSqlInsert() . ";");
  }
}