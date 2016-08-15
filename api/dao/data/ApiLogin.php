<?php
require_once 'Api.php';
/**
 * Login
 */
class ApiLogin extends Api {
  // 
  public $practiceId;
  public $userId;
  public $password;
  public $session;
  public $cookie;
  /**
   * Constructor
   * @param ['field'=>value,..] $data
   */
  public function __construct($data) {
    $required = array('practiceId','userId','password','session','cookie');
    $this->load($data, $required);
  }
  /**
   * Build USER uid from practice + user ID 
   * @return '5001_userId'
   */
  public function getUserUid() {
    return $this->practiceId . "_" . $this->userId;
  }
}
?>