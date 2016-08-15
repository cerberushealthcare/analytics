<?php
require_once "php/forms/Form.php";
require_once "php/dao/RegistrationDao.php";

class ForgotUserForm extends Form {

	// Enterable fields
	public $email;

        public $uid;

	// Functions
	public function validate() {
		$this->resetValidationException();
		$this->addRequired("email", "E-Mail", $this->email);
                if (! isBlank($this->email)) {
                   if ( (! isValidEmail($this->email)) ) {
                      $this->validationException->add("email", "Please enter a valid email address.");
                   } else {
                      $this->uid = RegistrationDao::existsEmail($this->email);
                      if ($this->uid == null) {
                         $this->validationException->add("email", "Email address is not registered.");
                      }
                   }
                }
		$this->throwValidationException();
	}

	public function setFromPost() {
                $this->email = $_POST["email"];
	}

	public function sendMail() {

               //  send mail
               $to       = $this->email;
               $subject  = "clicktate.com User Information";
               $message = "\r\n" . "Your user name is:   " . $this->uid;

		$headers  = 'From: info@clicktatemail.info' . "\r\n";
		$headers .= 'Reply-To: info@clicktatemail.info' . "\r\n";
		$headers .= 'Return-Path: info@clicktatemail.info' . "\r\n";
		$headers .= 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-Type: text/plain; charset=UTF-8' . "\r\n";


               mail($to, $subject, $message, $headers);
	}

}
?>