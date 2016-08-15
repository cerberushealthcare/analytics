<?php
require_once "php/data/db/_util.php";

class Session {
	
	public $id;
  public $schedId;  
	public $userGroupId;
	public $clientId;
	public $templateId;
	public $dateCreated;
	public $dateUpdated;
	public $closed;
	public $billed;
	public $data;

	public function __construct($id, $userGroupId, $clientId, $templateId, $dateCreated, $dateUpdated, $closed, $billed, $schedId, $data) {
		$this->id = $id;
    $this->schedId = $schedId;
		$this->userGroupId = $userGroupId;
		$this->clientId = $clientId;
		$this->templateId = $templateId;
		$this->dateCreated = $dateCreated;
		$this->dateUpdated = $dateUpdated;
    $this->closed = toBool($closed);
    $this->billed = toBool($billed);
    $this->data = $data;
	}
}
?>
