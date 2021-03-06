<?php
require_once 'php/data/rec/sql/_SqlRec.php';
require_once 'php/data/rec/sql/Client.php';
/**
 * Client Stub
 */
class ClientStub extends SqlRec implements ReadOnly {
  //
  public $clientId;
  public $uid;
  public $lastName;
  public $firstName;
  public $middleName;
  public $sex;
  //
  public function getSqlTable() {
    return 'clients';
  }
  public function toJsonObject() {
    $o = parent::toJsonObject();
    $o->name = Client::formatName($this);
    return $o;
  }
  //
  public static function formatName($client) {
    return Client::formatName($client);
  }
  /**
   * @param int $cid
   * @return ClientStub
   */
  public static function fetch($cid) {
    return SqlRec::fetch($cid, 'ClientStub');
  }
  /**
   * @param string $cid
   * @return ClientStub
   */
  public static function fetchByUid($uid) {
    $rec = new ClientStub();
    $rec->uid = $uid;
    return SqlRec::fetchOneBy($rec);
  }
}
