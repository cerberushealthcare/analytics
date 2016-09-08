<?php
set_include_path($_SERVER['DOCUMENT_ROOT'] . '/analytics/api/;' . $_SERVER['DOCUMENT_ROOT'] . '/analytics/sec/');
require_once 'Api.php';
require_once 'ApiAddress.php';
require_once 'php/data/db/UserGroup.php';
/**
 * Practice
 */
class ApiPractice extends Api {
  // 
  public $practiceId; 
  public $name;
  // 
  public $_address;  // ApiAddress
  /**
   * Constructor
   * @param ['field'=>value,..] $data
   */
  public function __construct($data) {
    $required = array('practiceId','name');
    $this->load($data, $required);
    $this->_address = new ApiAddress($data);
  }
  /**
   * Build a USER_GROUP record
   * @param(opt) $ugid
   * @return UserGroup
   */
  public function toUserGroup($ugid = null) {
    return new UserGroup(
      $ugid, 
      $this->name, 
      UserGroup::USAGE_LEVEL_EPRESCRIBE);
  }
  /**
   * Address builder
   * @param int $ugid
   * @param(opt) int $id: if updating
   * @return Address
   */
  public function toAddress($ugid, $id = null) {
    return $this->_address->toAddress(Address::TABLE_USER_GROUPS, $ugid, Address::ADDRESS_TYPE_SHIP, $id);
  }
}
?>