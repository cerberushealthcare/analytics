<?php
require_once 'php/data/rec/sql/_SqlRec.php';
/**
 * Session Stub
 */
class SessionStub extends SqlRec implements ReadOnly {
  //
  public $sessionId;
  public $dateService;
  public $closed;
  public $title;
  //
  public function getSqlTable() {
    return 'sessions';
  }
}
