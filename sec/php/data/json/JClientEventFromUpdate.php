<?php
require_once "php/data/json/_util.php";
require_once "php/data/json/JSession.php";

// JClientEvent created from a CLIENT_UPDATE record
class JClientEventFromUpdate {

  public $date; 
  public $type;  // "S"=session, "K"=sched, "C"=client
	public $id;
	public $tid;  // for S only
	public $name;  // for session, template name; for sched, schedType
	public $comment;  // for sched, schedStatus
	public $closed;  // for S only
  public $clientId;  
	
	// Derived
	public $fts;  // formatted timestamp, "05-Nov-2008 8:50AM"
	public $fd;  // formatted date, "2008-11-03"
	public $futs;  // formatted update, "05-Nov-2008 8:50AM"
	
	// Edit event anchor props
  public $aHref;  
  public $aClass; 
	
	public function __construct($date, $type, $id, $desc) {
    $this->date = $date;
    $this->futs = formatInformalTime($date);
    $this->fd = dateToString($date);
    switch ($type) {
      case ClientUpdate::TYPE_FACESHEET_UPDATED:
        $this->type = JClientEvent::TYPE_CLIENT;
        $this->name = "Facesheet updated";
        break;
      case ClientUpdate::TYPE_CLIENT_CREATED:
        $this->type = JClientEvent::TYPE_CLIENT;
        $this->name = "Patient created";
        break;
        case ClientUpdate::TYPE_CLIENT_UPDATED:
        $this->type = JClientEvent::TYPE_CLIENT;
        $this->name = "Patient updated";
        break;
      case ClientUpdate::TYPE_APPT_CREATED:
        $this->type = JClientEvent::TYPE_SCHED;
        $this->name = $desc;
        $this->aHref = "schedule.php?pop=" . $id;
        $this->aClass = "icon edit-appt";
        break;
      case ClientUpdate::TYPE_APPT_UPDATED:
        $this->type = JClientEvent::TYPE_SCHED;
        $this->name = $desc;
        $this->aHref = "schedule.php?pop=" . $id;
        $this->aClass = "icon edit-appt";
        break;
      case ClientUpdate::TYPE_APPT_DELETED:
        $this->type = JClientEvent::TYPE_SCHED;
        $this->name = "Appointment deleted";
        break;
      case ClientUpdate::TYPE_DOC_UPDATED:
        $this->type = JClientEvent::TYPE_SESSION;
        $this->name = $desc;
        $this->closed = false;
        $this->aClass = "icon edit-note";
        $this->aHref = "javascript:go(" . $id . ")";
        break;
      case ClientUpdate::TYPE_DOC_CLOSED:
        $this->type = JClientEvent::TYPE_SESSION;
        $this->name = $desc . " (Signed)";
        $this->closed = true;
        $this->aClass = "icon no-edit-note";
        $this->aHref = "javascript:go(" . $id . ")";
        break;
      case ClientUpdate::TYPE_DOC_DELETED:
        $this->type = JClientEvent::TYPE_SESSION;
        $this->name = "Document deleted";
        $this->closed = false;
        break;
    }
	}
	public function out() {
		return cb(qq("date", $this->date)
        . C . qq("fts", $this->fts) 
        . C . qq("fd", $this->fd) 
        . C . qq("type", $this->type) 
        . C . qq("id", $this->id) 
        . C . qq("tid", $this->tid) 
        . C . qq("name", $this->name) 
        . C . qq("comment", $this->comment) 
        . C . qq("ahref", $this->aHref) 
        . C . qq("aclass", $this->aClass) 
        );
	}
}
?>