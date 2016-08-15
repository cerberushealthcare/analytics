<?php
require_once 'php/data/rec/sql/_SqlRec.php';
/**
 * Login Requirement 
 */
class LoginReq extends SqlRec {
  //
  public $loginReqId;
  public $name;
  public $active;
  public $grace;
  public $notifyText;
  //
  public function getSqlTable() {
    return 'login_reqs';
  } 
  //
  /**
   * @param int $loginReqId
   * @return LoginReq
   */
  public static function fetch($loginReqId) {
    return SqlRec::fetch($loginReqId, 'LoginReq');
  }
  /**
   * @return array(loginReqId=>LoginReq,..)
   */
  public static function fetchMapActive() {
    $c = new LoginReq();
    $c->active = true;
    return SqlRec::fetchMapBy($c, 'loginReqId');
  }
}
