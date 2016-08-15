<?php
require_once "php/forms/Form.php";

class SectionForm extends Form {

	// Enterable fields
	public $id;	
	public $templateId;
	public $uid;
	public $name;
	public $title;
	public $desc;
	public $sortOrder;

	// Lists
	public $pars;

	// Combo lists
	public $sortOrders = array();

	public function validate() {
		$this->resetValidationException();
		$this->addRequired("uid", "UID", $this->uid);
		$this->addRequired("name", "Name", $this->name);
		$this->throwValidationException();
	}
	
	public function buildSection() {
		return new Section($this->id, $this->templateId, $this->uid, $this->name, $this->desc, $this->sortOrder, $this->title);
	}

	public function setFromDatabase($dto) {
		$this->id = $dto->id;
		$this->templateId = $dto->templateId;
		$this->uid = $dto->uid;
		$this->name = $dto->name;
		$this->title = $dto->title;
		$this->desc = $dto->desc;
		$this->sortOrder = $dto->sortOrder;
		$this->pars = $dto->pars;
	}

	public function setFromPost() {
		$this->id = $_POST["id"];
		$this->templateId = $_POST["templateId"];
		$this->uid = $_POST["uid"];
		$this->name = $_POST["name"];
		$this->title = $_POST["title"];
		$this->desc = $_POST["desc"];
		$this->sortOrder = $_POST["sortOrder"];
		$this->pars = null;
	}
}
?>