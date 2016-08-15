<?php
require_once "php/forms/Form.php";
require_once "php/forms/utils/CommonCombos.php";

class AcctDataForm extends Form {

	// Enterable fields
	public $uid;
	public $pw;
	public $name;
	public $email;
	public $company;
	public $state;
	public $license;
	public $dea;
	public $phone_num;
	public $ph_ac;
	public $ph_pf;
	public $ph_nm;
	public $phone_ext;
	public $npw1;
	public $npw2;
  public $action;

        // Other user form fields

  // Combo lists
	public $states;

	public function __construct() {
		$this->buildCombos();
	}

	// Functions
	public function validate() {
		$this->resetValidationException();
		switch ($_SESSION["cField"]) {
			case "name":
				$this->addRequired("name", "Name", $this->name);
				break;
			case "email":
				$this->addRequired("email", "E-Mail", $this->email);
					if ( (! isBlank($this->email)) && (! isValidEmail($this->email)) ) {
						$this->validationException->add("email", "Please enter a valid e-mail address.");
					}
				break;
			case "company":
				$this->addRequired("company", "Practice", $this->company);
				break;
			case "pw":
				$this->addRequired("pw", "Password", $this->pw);
				$this->addRequired("npw1", "New Password", $this->npw1);
				$this->addRequired("npw2", "New Password", $this->npw2);
				if ($this->pw != $_SESSION["pwd"]) {
					$this->validationException->add("pw", "Password entered does not match stored password.");
				}
				if ( (! isBlank($this->npw1)) && (! isValidPassword($this->npw1)) ) {
					$this->validationException->add("npw1", "Password must be 6 or more in length with at least 1 alpha and 1 numeric character.");
				}
				if ( (! isBlank($this->npw1)) && (! isBlank($this->npw2)) && ($this->npw1 != $this->npw2) ) {
					$this->validationException->add("npw1", "Entered passwords do not match.");
				}
				break;
			case "state":
				$this->addRequired("state", "State", $this->state);
				break;
			case "license":
				$this->addRequired("license", "Medical Lic. #", $this->license);
				break;
		}
		$this->throwValidationException();
	}

	public function setFromDatabase($dto) {
		$this->uid = $dto->uid;
		$this->pw = $dto->pw;
		$this->name = $dto->name;
		$this->email = $dto->email;
		$this->company = $dto->company;
		$this->state = $dto->state;
		$this->license = $dto->license;
		$this->dea = $dto->dea;
		$this->phone_num = $dto->phone_num;
		$this->phone_ext = $dto->phone_ext;
		$this->npw1 = $dto->pw;
		$this->npw2 = $dto->pw;
	}

	public function setFromPost() {
		switch ($_SESSION["cField"]) {
			case "name":
				$this->name = $_POST["name"];
				break;
			case "email":
				$this->email = $_POST["email"];
				break;
			case "company":
				$this->company = $_POST["company"];
				break;
			case "state":
				$this->state = $_POST["state"];
				break;
			case "license":
				$this->license = $_POST["license"];
				break;
			case "dea":
			  $this->dea = $_POST["dea"];
			  break;
			case "pw":
				$this->pw = $_POST["pw"];
				$this->npw1 = $_POST["npw1"];
				$this->npw2 = $_POST["npw2"];
				break;
			case "phone":
				$this->ph_ac = $_POST["ph_ac"];
				$this->ph_pf = $_POST["ph_pf"];
				$this->ph_nm = $_POST["ph_nm"];
				$this->phone_num = $this->ph_ac . "-" . $this->ph_pf . "-" . $this->ph_nm;
				$this->phone_ext = $_POST["phone_ext"];
				break;
		}
	}


	private function buildCombos() {
		$this->states = CommonCombos::states();
	}

}