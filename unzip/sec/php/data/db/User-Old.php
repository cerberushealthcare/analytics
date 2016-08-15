<?php
require_once "php/data/db/_util.php";

// TODO 1 Rework as registration
class UserOld {

	public $id;
	public $uid;
	public $pw;
	public $name;
	public $admin;
	public $active;
	public $date_created;
	public $email;
	public $phone_num;
	public $ph_ac;
  public $ph_pf;
  public $ph_nm;
  public $phone_ext;	
	public $practice_name;
	public $state;
	public $license;
	public $udata3;
	public $udata4;
	public $udata5;
	public $user_type;
	public $trial_exp;
	public $found;
	public $rpw;
	public $practice;
	public $practice_id;
	public $practice_pw;
	public $imgval;
	public $cbAgree;
	public $action;
	public $userGroupId;
	
	public function __construct($id, $uid, $pw, $name, $admin, $active, $date_created, $email, $phone_num, $ph_ac, $ph_pf, $ph_nm, $phone_ext, $practice_name, $state, $license, $udata3, $udata4, $udata5, $user_type, $trial_exp, $rpw, $practice, $practice_id, $practice_pw, $imgval, $cbAgree, $action, $found, $userGroupId) {
		$this->id = $id;
		$this->uid = $uid;
		$this->pw = $pw;
		$this->name = $name;
		$this->admin = $admin;
		$this->active = 1;
		$this->date_created = $date_created;
		$this->email = $email;
		$this->phone_num = $phone_num;
		$this->ph_ac = $ph_ac;
		$this->ph_pf = $ph_pf;
		$this->ph_nm = $ph_nm;
		$this->phone_ext = $phone_ext;
		$this->practice_name = $practice_name;
		$this->state = $state;
		$this->license = $license;
		$this->udata3 = $udata3;
		$this->udata4 = $udata4;
		$this->udata5 = $udata5;
		$this->user_type = $user_type;
		$this->trial_exp = $trial_exp;
		$this->rpw = $rpw;
		$this->practice = $practice;
		$this->practice_id = $practice_id;
		$this->practice_pw = $practice_pw;
		$this->imgval = $imgval;
		$this->cbAgree = $cbAgree;
		$this->action = $action;
		$this->found = $found;
		$this->userGroupid = $userGroupId;
	}
}
?>