<?php
require_once "php/data/db/_util.php";

class AcctData {

	public $uid;
	public $pw;
	public $name;
	public $email;
	public $company;
	public $state;
	public $license;
	public $dea;
	public $phone_num;
	public $phone_ext;
	
	public function __construct($uid, $pw, $name, $email, $company, $state, $license, $dea, $phone_num, $phone_ext) {
		$this->uid = $uid;
		$this->pw = $pw;
		$this->name = $name;
		$this->email = $email;
		$this->company = $company;
		$this->state = $state;
		$this->license = $license;
		$this->dea = $dea;
		$this->phone_num = $phone_num;
		$this->phone_ext = $phone_ext;
	}
}
?>