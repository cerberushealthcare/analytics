<?php
require_once "php/data/db/_util.php";

class TemplatePreset {

	public $id;
	public $userGroupId;
	public $name;
	public $templateId;
	public $dateCreated;
	public $dateUpdated;
	public $actions;

	public function __construct($id, $userGroupId, $name, $templateId, $dateCreated, $dateUpdated, $actions) {
		$this->id = $id;
		$this->userGroupId = $userGroupId;
		$this->name = $name;
		$this->templateId = $templateId;
		$this->dateCreated = $dateCreated;
		$this->dateUpdated = $dateUpdated;
		$this->actions = $actions; 
	}
}
?>
