<?php
require_once "php/forms/Form.php";
require_once "php/forms/utils/CommonCombos.php";
require_once "php/dao/RegistrationDao.php";

class ForgotPassForm extends Form {

	// Enterable fields
	public $uid;
	public $state;
	public $license;
	public $pw;
	public $email;
	
	// Combo lists
	public $states;
	
	public function __construct() {
		$this->states = CommonCombos::states();
	}
	
	// Functions
	public function validate() {
		$this->resetValidationException();
		$this->addRequired("uid", "User ID", $this->uid);
		$this->addRequired("state", "State", $this->state);
		//$this->addRequired("license", "License", $this->license);
                if ( (! isBlank($this->uid)) && (! isBlank($this->state)) ) {
                   if (! RegistrationDao::existsRegistration($this->state, $this->uid)) {
                         $this->validationException->add("uid", "Sorry, that information was not found.<br>If you continue to have problems, call our support line at 1-888-8CLICK8 (1-888-825-4258).");
                   }
                }
		$this->throwValidationException();
	}

	public function setFromPost() {
                $this->uid = $_POST["id"];
                $this->state = $_POST["state"];
                //$this->license = $_POST["license"];
	}

	public function sendMail() {

                $user = RegistrationDao::resetPassword($this->uid);

                //  send mail
                $to       = $user->email;
                $subject  = "clicktate.com User Information";
                $message = "\r\n" . "Your password has been reset to: " . $user->_ptpw . "\r\nWhen you sign on, you will be prompted to change this to a new password.";

		$headers  = 'From: info@clicktatemail.info' . "\r\n";
		$headers .= 'Reply-To: info@clicktatemail.info' . "\r\n";
		$headers .= 'Return-Path: info@clicktatemail.info' . "\r\n";
		$headers .= 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-Type: text/plain; charset=UTF-8' . "\r\n";

                mail($to, $subject, $message, $headers);
                return $message;
	}

}
?>