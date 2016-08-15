<?php
require_once "php/forms/Form.php";
require_once "php/forms/utils/CommonCombos.php";
require_once "php/dao/RegistrationDao.php";
require_once "php/dao/OldBillingDao.php";

class BillInfoForm extends Form {

	public $user_id;
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
	public $bill_code;
	public $upfront_charge;
	public $register_text;
	public $exists;
 	public $cvv2;

 	// Combo lists
	public $states;
 	
	public function __construct() {
		$this->states = CommonCombos::states();
	}
	
	// Functions
	public function setFromDatabase($dto) {
		$this->name = $dto->name;
		$this->address1 = $dto->address1;
		$this->address2 = $dto->address2;
		$this->city = $dto->city;
		$this->state = $dto->state;
		$this->zip = $dto->zip;
		$this->country = $dto->country;
		$this->card_type = $dto->card_type;
		$this->card_number = $dto->card_number;
		$this->exp_month = $dto->exp_month;
		$this->exp_year = $dto->exp_year;
		$this->next_bill_date = $dto->next_bill_date;
		$this->balance = $dto->balance;
		$this->phone_num = $dto->phone_num;
		$this->ph_ac = $dto->ph_ac;
		$this->ph_pf = $dto->ph_pf;
		$this->ph_nm = $dto->ph_nm;
		$this->phone_ext = $dto->phone_ext;
		$this->bill_code = $dto->bill_code;
		$this->upfront_charge = $dto->upfront_charge;
		$this->register_text = $dto->register_text;
		$this->exists = $dto->exists;
		$this->cvv2 = "";
	}

	public function setFromPost() {
		$this->unot = $_POST["unot"];
		$this->name = stripslashes($_POST["name"]);
		$this->address1 = stripslashes($_POST["address1"]);
		$this->address2 = stripslashes($_POST["address2"]);
		$this->city = stripslashes($_POST["city"]);
		$this->state = stripslashes($_POST["state"]);
		$this->zip = $_POST["zip"];
		$this->country = $_POST["country"];
		$this->card_type = $_POST["card_type"];
		$this->card_number = $_POST["card_number"];
		$this->exp_month = $_POST["exp_month"];
		$this->exp_year = $_POST["exp_year"];
		$this->next_bill_date = $_POST["next_bill_date"];
		$bal = str_replace(",", "", $_POST["balance"]);
		$this->balance = floatval(substr($bal,2,strlen($bal)-2));
		$this->phone_num = $_POST["ph_ac"] . "-" . $_POST["ph_pf"] . "-" . $_POST["ph_nm"];
		$this->ph_ac = $_POST["ph_ac"];
		$this->ph_pf = $_POST["ph_pf"];
		$this->ph_nm = $_POST["ph_nm"];
		$this->phone_ext = $_POST["phone_ext"];
		$this->bill_code = $_POST["bill_code"];
		$this->upfront_charge = $_POST["upfront_charge"];
		$this->register_text = $_POST["register_text"];
		$this->exists = $_POST["exists"];
		$this->cvv2 = $_POST["cvv2"];
	}

	public function validate() {
		$this->resetValidationException();
		$this->addRequired("name", "Name", $this->name);
		$this->addRequired("address1", "Address Line 1", $this->address1);
		$this->addRequired("city", "City", $this->city);
		$this->addRequired("state", "State", $this->state);
		$this->addRequired("zip", "Zip code", $this->zip);
		$this->addRequired("card_number", "Card Number", $this->card_number);
		$this->addRequired("ph_ac", "Phone Area Code", $this->ph_ac);
		$this->addRequired("ph_pf", "Phone Prefix", $this->ph_pf);
		$this->addRequired("ph_nm", "Phone Number", $this->ph_nm);
		$now_year = date('Y', strtotime("now"));
		$now_mth = date('m', strtotime("now"));
		if ($this->exp_year < $now_year) {
			$this->validationException->add("exp_year", "Credit Card expiration year is invalid.");                
		} else {
			if ($this->exp_year == $now_year) {
				if ($this->exp_month < $now_mth) {
				  	$this->validationException->add("exp_month", "Credit Card expiration month is invalid.");                
				}
			} 
		}
		$this->throwValidationException();
	}

	public function sendMail() {

	  	//  send mail
		$user = RegistrationDao::getUserInfo($_SESSION["uid"]);
		$to = $user["email"];
		$subject = "clicktate.com Account Activation";

		$message = "\r\n" . $user["name"] . ",\r\n\r\n";
		$message .= "Thank you for ";
		if ($this->exists == "Y") {
			$message .= "updating your billing information.  Your account has been re-activated.";
		} else {
			if ($this->upfront_charge <= 0) {
				$message .= "activating your clicktate.com account.";
			} else {
				$message .= "activating your clicktate.com account.  Your credit card ending in ";
				$message .= substr($this->card_number, strlen($this->card_number)-4, 4);
				$message .= " has been charged $" . number_format($this->balance,2) . ".  This ";
				$message .= "amount will be applied to your first month's balance.";
			}
		}
		$message .= "\r\n\r\nWe are glad you enjoy our product and always appreciate ";
		$message .= "any feedback or suggestions. \r\n\r\nThank you,\r\n";
		$message .= "clicktate.com User Support";

		$headers = 'From: info@clicktate.com' . "\r\n";
		$headers .= 'Reply-To: info@clicktate.com' . "\r\n";
		$headers .= 'Return-Path: info@clicktate.com' . "\r\n";
		$headers .= 'Bcc: info@clicktate.com' . "\r\n";
		$headers .= 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-Type: text/plain; charset=UTF-8' . "\r\n";

		mail($to, $subject, $message, $headers);
	}
}
?>