<?php
require_once 'php/data/rec/sql/_SqlRec.php';
/**
 * Session for Tiani
 */
class SessionTiani extends SqlRec implements ReadOnly {
  //
  public $sessionId;
  public $clientId;
  public $html;
  public $title;
  //
  public function getSqlTable() {
    return 'sessions';
  }
  //
  public static function fetch($sid) {
    return SqlRec::fetch($sid, 'SessionTiani');
  }
}
