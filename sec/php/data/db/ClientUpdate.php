<?php
// DEPRECATED

require_once "php/data/db/_util.php";
require_once "php/data/rec/cryptastic.php";

class ClientUpdate {
	
	public $clientId;
	public $type;
  public $id;
  public $desc;
	
	// Types
  const TYPE_CLIENT_CREATED = 0;
  const TYPE_CLIENT_UPDATED = 1;
  const TYPE_APPT_CREATED = 10;
  const TYPE_APPT_UPDATED = 11;
  const TYPE_APPT_DELETED = 12;
  const TYPE_DOC_UPDATED = 20;
  const TYPE_DOC_CLOSED = 21;
  const TYPE_DOC_DELETED = 22;
  const TYPE_FACESHEET_UPDATED = 30;
  
	public function __construct($clientId, $type, $id, $desc) {
    $this->clientId = $clientId;
    $this->type = $type;
    $this->id = $id;
    $this->desc = $desc;
	}
	
  static function fromAuditMru($mru) {
    logit_r($mru, 'fromauditmru');
    $cid = $mru->clientId;
    $id = intval($mru->recId);
    $desc = $mru->label;
    switch ($mru->recName) {
      case 'Client':
        $type = ($mru->action == 'A') ? 0 : 1;
      case 'Session':
        $type = ($mru->action == 'D') ? 22 : 20;
      default:
        $type = 30;
    }
    return new static($cid, $type, $id, $desc);
  }
	static function typeFromAudit($entity, $action) {
    switch ($entity) {
      case AuditDao::ENTITY_ADDR_CLIENT:
        return ClientUpdate::TYPE_CLIENT_UPDATED;
      case AuditDao::ENTITY_SCHED:
        return ($action == AuditDao::ACTION_CREATE) ? ClientUpdate::TYPE_APPT_CREATED :
            (($action == AuditDao::ACTION_DELETE) ? ClientUpdate::TYPE_APPT_DELETED : ClientUpdate::TYPE_APPT_UPDATED);
      case AuditDao::ENTITY_CLIENT:
        return ($action == AuditDao::ACTION_CREATE) ? ClientUpdate::TYPE_CLIENT_CREATED : ClientUpdate::TYPE_CLIENT_UPDATED;
      case AuditDao::ENTITY_DATA_ALLER:
      case AuditDao::ENTITY_DATA_DIAG:
      case AuditDao::ENTITY_DATA_MED_HIST:
      case AuditDao::ENTITY_DATA_HM:
      case AuditDao::ENTITY_DATA_MEDS:
      case AuditDao::ENTITY_DATA_VITALS:
        return ClientUpdate::TYPE_FACESHEET_UPDATED;
      case AuditDao::ENTITY_FACESHEET:
        return ($action == AuditDao::ACTION_REVIEW) ? null : ClientUpdate::TYPE_FACESHEET_UPDATED;
      case AuditDao::ENTITY_SESSION:
        return ($action == AuditDao::ACTION_CLOSED) ? ClientUpdate::TYPE_DOC_CLOSED :
            (($action == AuditDao::ACTION_DELETE) ? ClientUpdate::TYPE_DOC_DELETED : ClientUpdate::TYPE_DOC_UPDATED);
            
    }
  }
  static function save($cu) {
    logit_r($cu, 'save cu');
    $sql = "INSERT INTO client_updates (client_id, date, type, id, descr) VALUES("
        . $cu->clientId . ","
        . now() . ","  // date
        . quote($cu->type) . ","
        . quote($cu->id) . ","
        . quote(MyCrypt_Auto::encrypt($cu->desc), true)
        . ") ON DUPLICATE KEY UPDATE date=VALUES(date), type=VALUES(type), id=VALUES(id), descr=VALUES(descr)"; 
    query($sql);    
  }
  static function save_mru($mru) {
    $cu = static::fromAuditMru($mru);
    static::save($cu);
  }
}
?>
