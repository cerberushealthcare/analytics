<?php
require_once "php/data/db/_util.php";

class OldBillInfo {

	public $name;
	public $address1;
	public $address2;
	public $city;
	public $state;
	public $zip;
	public $country;
	public $card_type;
	public $card_number;
	public $exp_month;
	public $exp_year;
	public $next_bill_date;
	public $balance;
	public $phone_num;
	public $ph_ac;
	public $ph_pf;
	public $ph_nm;
	public $phone_ext;
	public $exists;
	public $start_bill_date;
	public $bill_code;
	public $upfront_charge;
	public $register_text;
	
	public function __construct($name, $address1, $address2, $city, $state, $zip, $country, $card_type, $card_number, $exp_month, $exp_year, $next_bill_date, $balance, $phone_num, $ph_ac, $ph_pf, $ph_nm, $phone_ext, $start_bill_date, $bill_code, $upfront_charge, $register_text, $exists) {
		$this->name = $name;
		$this->address1 = $address1;
		$this->address2 = $address2;
		$this->city = $city;
		$this->state = $state;
		$this->zip = $zip;
		$this->country = $country;
		$this->card_type = $card_type;
		$this->card_number = $card_number;
		$this->exp_month = $exp_month;
		$this->exp_year = $exp_year;
		$this->next_bill_date = $next_bill_date;
		$this->balance = $balance;
		$this->phone_num = $phone_num;
		$this->ph_ac = $ph_ac;
		$this->ph_pf = $ph_pf;
		$this->ph_nm = $ph_nm;
		$this->phone_ext = $phone_ext;
		$this->start_bill_date = $start_bill_date;
		$this->bill_code = $bill_code;
		$this->upfront_charge = $upfront_charge;
		$this->register_text = $register_text;
		$this->exists = $exists;
	}
}
?>