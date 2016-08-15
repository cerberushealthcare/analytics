<?php
require_once "php/data/json/_util.php";
require_once "php/data/json/JSession.php";

class JSessionStub {

	public $id;
	public $templateId;
	public $cid;
	public $clientName;
	public $clientSex;
	public $dateCreated;
	public $dateUpdated;
	public $dateService;
	public $date;  // informal dateService
  public $closed;
	public $closedBy;
  public $dateClosed;
	public $createdBy;
  public $updatedBy;
  public $sendTo;
  public $title;
  public $standard;
  
  // History navigation (assigned by JClientHistory)
  public $prev;  // sid  
  public $next;  // sid
  
  // Helpers
  public $label;  // e.g. "Standard Medical Note (Signed)" 
  
	// Children
	public $sched;  // May be null if session not associated with a sched

	public function __construct($id, $templateId, $templateName, $cid, $dateCreated, $dateUpdated, $dateService, $closed, $closedBy, $dateClosed, $createdBy, $updatedBy, $lastName, $firstName, $sex, $sendTo, $title, $standard) {
		$this->id = $id;
    $this->templateId = $templateId;
    //$this->templateName = $templateName;
    $this->cid = $cid;
    $this->clientSex = $sex;
		$this->dateCreated = formatTimestamp($dateCreated);
		$this->dateUpdated = formatTimestamp($dateUpdated);
		$this->dateService = formatDate($dateService);
		$this->date = formatInformalDate($dateService);
		$this->closed = $closed;
    $this->closedBy = $closedBy;
		$this->dateClosed = formatTimestamp($dateClosed);
    $this->createdBy = $createdBy;
    $this->updatedBy = $updatedBy;
    $this->clientName = $lastName . ", " . $firstName;
    $this->sendTo = $sendTo;
    $this->title = $title;
    $this->standard = $standard;
    $this->label = JSession::buildLabel($this->title, null, $this->closed, $this->standard);
	}

	public function out() {
		return cb(qq("id", $this->id) 
        . C . qq("tid", $this->templateId) 
        . C . qq("cid", $this->cid) 
        . C . qq("cname", $this->clientName) 
        . C . qq("csex", $this->clientSex) 
        . C . qq("created", $this->dateCreated) 
        . C . qq("updated", $this->dateUpdated) 
        . C . qq("dos", $this->dateService)
        . C . qq("date", $this->date)
        . C . qqo("closed", $this->closed) 
        . C . qq("closedBy", $this->closedBy)
        . C . qq("dateClosed", $this->dateClosed)
        . C . qq("createdBy", $this->createdBy) 
        . C . qq("updatedBy", $this->updatedBy) 
        . C . qq("sendTo", $this->sendTo) 
        . C . qq("title", $this->title) 
        . C . qqo("standard", $this->standard) 
        . C . qq("label", $this->label)
        //. C . qqo("prev", $this->prev)
        //. C . qqo("next", $this->next)
        );
	}
}
?>