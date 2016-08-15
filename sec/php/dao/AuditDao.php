<?php
require_once 'php/dao/_util.php';

class AuditDao {

  // Entities
  const ENTITY_CLIENT        = 'C';
  const ENTITY_SESSION       = 'S';
  const ENTITY_SCHED         = 'K';
  const ENTITY_ADDR_CLIENT   = 'AC';
  const ENTITY_ADDR_USER     = 'AU';
  const ENTITY_ADDR_GROUP    = 'AG';
  const ENTITY_USERGROUP     = 'UG';
  const ENTITY_FACESHEET     = 'F';
  const ENTITY_DATA_VITALS   = 'DV';
  const ENTITY_DATA_IMMUN    = 'DI';
  const ENTITY_DATA_MEDS     = 'DM';
  const ENTITY_DATA_ALLER    = 'DA';
  const ENTITY_DATA_DIAG     = 'DD';
  const ENTITY_DATA_HM       = 'DHM';
  const ENTITY_DATA_MED_HIST = 'DMH';
    
  // Actions
  const ACTION_CREATE = 'C';
  const ACTION_REVIEW = 'R';
  const ACTION_UPDATE = 'U';
  const ACTION_DELETE = 'D';
  const ACTION_CLOSED = "K";
  
  /*
   * Log an audit event
   */ 
  public static function log($clientId, $entity, $entityId, $action, $data = null, $title = null) {
    global $login;
    $sql = 'INSERT INTO audits VALUES(NULL';
    $sql .= ', ' . now();
    $sql .= ', ' . $login->userId;
    $sql .= ', ' . $login->userGroupId;
    $sql .= ', ' . q($clientId);
    $sql .= ', ' . q($entity);
    $sql .= ', ' . q($entityId);
    $sql .= ', ' . q($action);
    $sql .= ', ' . q($data);
    $sql .= ', ' . q($title);
    $sql .= ')';
    query($sql);
    if ($clientId) {
      $type = ClientUpdate::typeFromAudit($entity, $action);
      if ($type !== null) {
        AuditDao::saveClientUpdate(new ClientUpdate($clientId, $type, $entityId, $title));
      }
    }
  }
  
  /*
   * Log a review audit event
   */
  public static function logReview($clientId, $entity, $entityId = null, $data = null, $title = null) {
    AuditDao::log($clientId, $entity, $entityId, AuditDao::ACTION_REVIEW, $data, $title);
  }

  public static function saveClientUpdate($cu) {
    $sql = "INSERT INTO client_updates (client_id, date, type, id, descr) VALUES("
        . $cu->clientId . ","
        . now() . ","  // date
        . quote($cu->type) . ","
        . quote($cu->id) . ","
        . quote($cu->desc, true)
        . ") ON DUPLICATE KEY UPDATE date=VALUES(date), type=VALUES(type), id=VALUES(id), descr=VALUES(descr)"; 
    query($sql);    
  }

  /*
   * Get most recent client update timestamp
   */
  public static function getClientUpdateTimestamp($clientId) {
    //return fetchField("SELECT date FROM client_updates WHERE client_id=$clientId", 0, false);
  }
    
  /*
   * Get update audit events for a client since a certain time
   * $ts: in SQL format 'YYYY-MM-DD HH:MM:SS'
   * Returns [
   *    "ts"=>s         // new current timestamp
   *    "updates"=>[    // formatted audit rows (see formatRow)
   *      "time":s,
   *      "user":s,
   *      "entity":s,
   *      "entityId":s,
   *      "action":s,
   *      "data":s,
   *      "title":s
   *   ]
   */
  public static function getAuditsSince($clientId, $ts) {
    $sql = "SELECT a.time AS time, a.user_id AS userId, u.name AS user, a.entity AS entity, a.entity_id AS entityId, a.action AS action, a.data AS data, a.title AS title "
        . " FROM audits a INNER JOIN users u ON a.user_id=u.user_id WHERE client_id=$clientId AND time>" . q($ts);
    $rows = fetcharray($sql);
    foreach ($rows as &$row) {
      $row["time"] = AuditDao::formatInformatlTime($row["time"]);
      $row["entity"] = AuditDao::formatEntityName($row["entity"]);
      $row["action"] = AuditDao::formatAction($row["action"]);
    }
    return $rows;
  }
  
  private static function formatAction($action) {
    switch ($action) {
      case AuditDao::ACTION_CREATE:
        return 'Add';
      case AuditDao::ACTION_DELETE:
        return 'Delete';
      case AuditDao::ACTION_REVIEW:
        return 'Review';
      case AuditDao::ACTION_UPDATE:
        return 'Update';
    }
  }
  
  private static function formatEntityName($entity) {
    switch ($entity) {
      case AuditDao::ENTITY_ADDR_CLIENT:
        return 'Patient Address';
      case AuditDao::ENTITY_CLIENT:
        return 'Patient';
      case AuditDao::ENTITY_DATA_ALLER:
        return 'Allergy';
      case AuditDao::ENTITY_DATA_DIAG:
        return 'Diagnosis';
      case AuditDao::ENTITY_DATA_MED_HIST:
        return 'Medical History';
      case AuditDao::ENTITY_DATA_HM:
        return 'Health Maintenance';
      case AuditDao::ENTITY_DATA_MEDS:
        return 'Medication';
      case AuditDao::ENTITY_DATA_VITALS:
        return 'Vitals';
      case AuditDao::ENTITY_FACESHEET:
        return 'Facesheet';
      case AuditDao::ENTITY_SCHED:
        return 'Appointment';
      case AuditDao::ENTITY_SESSION:
        return 'Document';
    }
  }
}
