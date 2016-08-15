<?php
require_once 'php/data/json/_util.php';

/**
 * Messaging inbox for user (collection of thread stubs)
 */
class JMsgInbox {

  private $threads;  // [JInboxThread]
  
  /*
   * Construct from fetched $rows from MsgDao
   */
  public function __construct($rows) {
    $threads = array();
    foreach ($rows as &$row) {
      $threads[] = new JInboxThread($row);
    }
  }
  /*
   * Returns
   *   [JThreadStub->out(),..]
   */
  public function out() {
    return arr($this->threads);
  }
}

class JInboxThread {

  private $row;
  
  public function __construct($row) {
    $this->row = $row;
  }
  /*
   * Returns 
   *   {dsync:{"v":value,"l":label},..}  
   */
  public function out() {
    return aarro($this->fields);
  }
}
?>