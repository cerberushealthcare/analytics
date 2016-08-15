<?php
require_once "php/forms/Form.php";

class SurveyForm extends Form {

	// Enterable fields
	public $id;	
	public $name;
	public $desc;
	public $password;
	public $active = false;
	public $userId;
	
	// Lists
	public $items;

	public function validate() {
		$this->resetValidationException();
		$this->addRequired("name", "Name", $this->name);
		$this->addRequired("desc", "Description", $this->name);
		$this->throwValidationException();
	}

	public function buildSurvey() {
		return new Survey($this->id, $this->name, $this->desc, $this->password, $this->active, $this->userId);
	}

	public function setFromDatabase($dto) {
		$this->id = $dto->id;
		$this->name = $dto->name;
		$this->desc = $dto->desc;
		$this->password = $dto->password;
		$this->active = $dto->active;
		$this->userId = $dto->userId;
		$this->items = $dto->items;
	}

	public function setFromPost() {
		$this->id = $_POST["id"];
		$this->name = $_POST["name"];
		$this->desc = $_POST["desc"];
		$this->password = $_POST["password"];
		$this->active = $this->toBool(isset($_POST["active"]) ? $_POST["active"] : null);
		$this->userId = $_POST["userId"];
		$this->items = null;
	}
}
?>