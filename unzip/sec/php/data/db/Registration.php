<?php
require_once "php/data/db/_util.php";

class Registration {
	
	public $regid;
	public $uid;
	public $pw;
	public $name;
	public $email;
	public $company;
	public $licenseState;
	public $license;
	public $phoneNum;
	public $phoneExt;
	public $howFound;
	public $dateCreated;
	public $referrer;
	
	public function __construct($regid, $uid, $pw, $name, $email, $company, $licenseState, 
	                            $license, $phoneNum, $phoneExt, $howFound, $dateCreated, $referrer) {
		$this->regid = $regid;
    	$this->uid = $uid;
    	$this->pw = $pw;
    	$this->name = $name;
    	$this->email = $email;
    	$this->company = $company;
    	$this->licenseState = $licenseState;
    	$this->license = $license;
    	$this->phoneNum = $phoneNum;
    	$this->phoneExt = $phoneExt;
    	$this->howFound = $howFound;
    	$this->dateCreated = $dateCreated;
    	$this->referrer = $referrer;
	}
}
?>