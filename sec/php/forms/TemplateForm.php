<?php
require_once "php/forms/Form.php";

class TemplateForm extends Form {

	// Enterable fields
	public $id;	
	public $userId;
	public $uid;
	public $name;
	public $title;
	public $desc;
	public $public = false;

	// Lists
	public $sections;

	public function validate() {
		$this->resetValidationException();
		$this->addRequired("uid", "UID", $this->uid);
		$this->addRequired("name", "Name", $this->name);
		$this->addRequired("desc", "Description", $this->name);
		$this->throwValidationException();
	}

	public function buildTemplate() {
		return new Template($this->id, $this->userId, $this->uid, $this->name, $this->public, null, null, $this->desc, $this->title);
	}

	public function setFromDatabase($dto) {
		$this->id = $dto->id;
		$this->userId = $dto->userId;
		$this->uid = $dto->uid;
		$this->name = $dto->name;
		$this->public = $dto->public;
		$this->desc = $dto->desc;
		$this->title = $dto->title;
		$this->sections = $dto->sections;
	}

	public function setFromPost() {
		$this->id = $_POST["id"];
		$this->userId = $_POST["userId"];
		$this->uid = $_POST["uid"];
		$this->name = $_POST["name"];
		$this->public = $this->toBool(isset($_POST["public"]) ? $_POST["public"] : null);
		$this->desc = $_POST["desc"];
		$this->title = $_POST["title"];
		$this->sections = null;
	}
}
?>