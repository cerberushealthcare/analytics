<?php
require_once "php/data/db/_util.php";

class Sched {
	
	public $id;
  public $userId;  
	public $userGroupId;
	public $clientId;
	public $date;
	public $timeStart;
	public $duration;
	public $closed;
	public $status;
	public $comment;
	public $type;
	public $schedEventId;

	// Derived
	public $dateService;
	
	// Children
	public $client;
  public $sessions;
  public $schedEvent;
  
	public function __construct($id, $userId, $userGroupId, $clientId, $date, $timeStart, $duration, $closed, $status, $comment, $type, $schedEventId) {
		$this->id = $id;
    $this->userId = $userId;
		$this->userGroupId = $userGroupId;
		$this->clientId = $clientId;
		$this->date = $date;
    $this->timeStart = $timeStart;
    $this->duration = $duration;
    $this->closed = toBool($closed);
    $this->status = $status;
    $this->comment = $comment;
    $this->type = $type;
    $this->schedEventId = $schedEventId;
    $this->dateService = formatDate($date);
	}
}
?>
