<?php
require_once "php/data/db/_util.php";

class UserGroup0 {
	
	public $id;
	public $name;
	public $usageLevel;
	public $estAdjust;
	public $sessionTimeout;

	// Children
  public $address;

  // Plans
  const USAGE_LEVEL_BASIC = 0;
  const USAGE_LEVEL_EMR = 1;
  const USAGE_LEVEL_EPRESCRIBE = 2;

	public function __construct($id, $name, $usageLevel, $estAdjust = 0, $sessionTimeout) {
		$this->id = $id;
		$this->name = $name;
		$this->usageLevel = $usageLevel;
		$this->estAdjust = $estAdjust;
		$this->sessionTimeout = $sessionTimeout;
	}
}
?>
