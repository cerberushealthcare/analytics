<?php
require_once "php/forms/Form.php";
require_once "php/forms/utils/CommonCombos.php";

class SessionForm extends Form {

	// Enterable fields
	public $userId;
	public $templateId;
	public $cid;
	public $cname;
	public $csex;
	public $cdata1;
	public $cdata2;
	public $cdata3;
	public $cdata4;
	public $cdata5;
	public $cdata6;
	public $cdata7;
	public $cdata8;
	public $cdata9;
	public $action;  // either "add" or "update"
	public $sessionIdEd;
	public $userIdEd;
	public $templateIdEd;
	public $cidEd;
	public $cnameEd;
	public $csexEd;
	public $cdata1Ed;
	public $cdata2Ed;
	public $cdata3Ed;
	public $cdata4Ed;
	public $cdata5Ed;
	public $cdata6Ed;
	public $cdata7Ed;
	public $cdata8Ed;
	public $cdata9Ed;

	// Lists
	public $sessions;
	
	// Combo lists
	public $sexes;

	public function __construct() {
		$this->sexes = CommonCombos::sexes();
	}
		
	public function validate() {
		$this->resetValidationException();
		if ($this->action == "update") {
			$this->addRequired("cidEd", "Patient ID", $this->cidEd);
			$this->addRequired("cnameEd", "Patient Name", $this->cnameEd);
			$this->addRequired("csexEd", "Sex of Patient", $this->csexEd);
			$ret = strtotime($this->cdata1Ed);
			if (trim($this->cdata1Ed) != "") {
				if (! $ret) {
					$this->validationException->add("cdata1Ed", "Birthdate invalid, please format as month/date/year.");
				} else {
					$this->cdata1Ed = date("m/d/Y", $ret);
				}
			}
		} else {
			$this->addRequired("cid", "Patient ID", $this->cid);
			$this->addRequired("cname", "Patient Name", $this->cname);
			$this->addRequired("csex", "Sex of Patient", $this->csex);
			if (trim($this->cdata1) != "") {
				$ret = strtotime($this->cdata1);
				if (! $ret) {
					$this->validationException->add("cdata1", "Birthdate invalid, please format as month/date/year.");
				} else {
					$this->cdata1 = date("m/d/Y", $ret);
				}	
			}
		}
		$this->throwValidationException();
	}
	
	public function setEditFromDatabase($session) {
		$this->sessionIdEd = $session->id;
		$this->templateIdEd = $session->templateId;
		$this->cidEd = $session->cid;
		$this->cnameEd = $session->cname;
		$this->csexEd = $session->csex;
		$this->cdata1Ed = $session->cdata1;
	}

	public function buildSession() {
		if ($this->action == "update") {
			return new Session($this->sessionIdEd, $this->userId, $this->templateIdEd, null, null, $this->cidEd, $this->cnameEd, $this->csexEd, $this->cdata1Ed, $this->cdata2Ed, $this->cdata3Ed, $this->cdata4Ed, $this->cdata5Ed, $this->cdata6Ed, $this->cdata7Ed, $this->cdata8Ed, $this->cdata9Ed, null, null);
		} else {
			return new Session(null, $this->userId, $this->templateId, null, null, $this->cid, $this->cname, $this->csex, $this->cdata1, $this->cdata2, $this->cdata3, $this->cdata4, $this->cdata5, $this->cdata6, $this->cdata7, $this->cdata8, $this->cdata9, null, null);
		}
	}

	public function setFromPost() {
		$this->action = $_POST["action"];
		$this->userId = $_POST["userId"];
		$this->templateId = $_POST["templateId"];
		$this->cid = $_POST["cid"];
		$this->cname = $_POST["cname"];
		$this->csex = $_POST["csex"];
		$this->cdata1 = $_POST["cdata1"];
		$this->sessionIdEd = isset($_POST["sessionIdEd"]) ? $_POST["sessionIdEd"] : null;
		$this->templateIdEd = isset($_POST["templateIdEd"]) ? $_POST["templateIdEd"] : null;
		$this->cidEd = isset($_POST["cidEd"]) ? $_POST["cidEd"] : null;
		$this->cnameEd = isset($_POST["cnameEd"]) ? $_POST["cnameEd"] : null;
		$this->csexEd = isset($_POST["csexEd"]) ? $_POST["csexEd"] : null;
		$this->cdata1Ed = isset($_POST["cdata1Ed"]) ? $_POST["cdata1Ed"] : null;

		// TODO4 Not gathering cdata's currently
		//$this->cdata2 = $_POST["cdata2"];
		//$this->cdata3 = $_POST["cdata3"];
		//$this->cdata4 = $_POST["cdata4"];
		//$this->cdata5 = $_POST["cdata5"];
		//$this->cdata6 = null;
		//$this->cdata7 = null;
		//$this->cdata8 = null;
		//$this->cdata9 = null;
	}
}
?>