<?php
require_once "php/data/json/_util.php";

/**
 * Diagnosis 
 * Facesheet Data Record
 * 
 * Organization:
 *   ugid, cid,..
 *     sid>0, date=DOS, par_uidx=puid  // built from closed note
 *     sid=NULL, text                  // facesheet summary record
 */
class JDataDiagnosis {

  public $id;
  public $userGroupId;
  public $clientId;
  public $sessionId;
  public $date;  // dos
  public $parUid;
  public $text;  // 'Hyperlipidemia'
  public $parDesc;
  public $icd;  // '272.2'
  public $active;
  public $updated;

  const SQL_FIELDS = "data_diagnoses_id, user_group_id, client_id, session_id, date, par_uid, text, par_desc, icd, active, date_updated";
  
  public function __construct($id, $userGroupId, $clientId, $sessionId, $date, $parUid, $text, $parDesc, $icd, $active, $updated) {
    $this->id = $id;
    $this->userGroupId = $userGroupId;
    $this->clientId = $clientId;
    $this->sessionId = $sessionId;
    $this->date = $date;
    $this->parUid = $parUid;
    $this->text = $text;
    $this->parDesc = $parDesc;
    $this->icd = $icd;
    $this->active = $active;
    $this->updated = $updated;
	}
	public function out() {
    $out = "";
    $out = nqq($out, "id", $this->id);
    $out = nqq($out, "clientId", $this->clientId);
    $out = nqq($out, "sessionId", $this->sessionId);
    $out = nqq($out, "date", formatDate($this->date));
    $out = nqq($out, "parUid", $this->parUid);
    $out = nqq($out, "text", $this->text);
    $out = nqq($out, "parDesc", $this->parDesc);
    $out = nqq($out, "icd", $this->icd);
    $out = nqqo($out, "active", $this->active);
    return cb($out);
	}
	
	// Static functions
	public static function copy($dto) {
	  return new JDataDiagnosis(
	      null,
        $dto->userGroupId,
        $dto->clientId,
        $dto->sessionId,
        $dto->date,
        $dto->parUid,
        $dto->text,
        $dto->parDesc,
        $dto->icd,
        $dto->active,
        $dto->updated);
	}
  public static function fromRows($res, $assocBy = null) {
    $diagnoses = array();
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      $rec = JDataDiagnosis::fromRow($row);
      if ($assocBy != null) {
        $diagnoses[$row[$assocBy]] = $rec;
      } else {
        $diagnoses[] = $rec;
      }
    }
    return $diagnoses;
  }
  public static function fromRow($row) {
    if (! $row) return null;
    return new JDataDiagnosis(
        $row["data_diagnoses_id"],
        $row["user_group_id"],
        $row["client_id"],
        $row["session_id"],
        $row["date"],
        $row["par_uid"],
        $row["text"],
        $row["par_desc"],
        $row["icd"],
        $row["active"],
        $row["date_updated"]
        );
  }
	public static function save($dto, $escape = false, $audit = true) {
    if ($dto->id != null) {
      LoginDao::authenticateDataDiagnosis($dto->id);
      $sql = "UPDATE data_diagnoses SET ";
      $sql .= " date=" . quote($dto->date);
      $sql .= ", text=" . quote($dto->text, $escape);
      $sql .= ", icd=" . quote($dto->icd);
      $sql .= ", active=" . toBoolInt($dto->active);
      $sql .= ", date_updated=NULL";
      $sql .=" WHERE data_diagnoses_id=" . $dto->id;
      query($sql);
      if ($audit) AuditDao::log($dto->clientId, AuditDao::ENTITY_DATA_DIAG, $dto->id, AuditDao::ACTION_UPDATE, null, $dto->text);
    } else {
      LoginDao::authenticateClientId($dto->clientId);
      $sql = "INSERT INTO data_diagnoses VALUE(NULL";
      $sql .= ", " . quote($dto->userGroupId);
      $sql .= ", " . quote($dto->clientId);
      $sql .= ", " . quote($dto->sessionId);
      $sql .= ", " . quote($dto->date);
      $sql .= ", " . quote($dto->parUid);
      $sql .= ", " . quote($dto->text, $escape);
      $sql .= ", " . quote($dto->parDesc, $escape);
      $sql .= ", " . quote($dto->icd);
      $sql .= ", " . toBoolInt($dto->active);
      $sql .= ", NULL";  // date_updated
      $sql .= ")";
      $dto->id = insert($sql);
      if ($audit) AuditDao::log($dto->clientId, AuditDao::ENTITY_DATA_DIAG, $dto->id, AuditDao::ACTION_CREATE, null, $dto->text);
    }
    return $dto;
  }
  
  // Return array of unread (active=null) diagnoses from session history
  public static function getUnreadSessionDiagnoses($clientId) {
    $sql = "SELECT " . JDataDiagnosis::SQL_FIELDS . " FROM data_diagnoses WHERE active IS NULL AND session_id IS NOT NULL AND client_id=" . $clientId;
    return JDataDiagnosis::fromRows(query($sql));
  }

  // Return array of non-session diagnoses data records
  public static function getActiveFacesheetDiagnoses($clientId) {
    $sql = "SELECT " . JDataDiagnosis::SQL_FIELDS . " FROM data_diagnoses WHERE client_id=" . $clientId . " AND session_id IS NULL AND active=1 ORDER BY text";
    return JDataDiagnosis::fromRows(query($sql), "text");
  }
  
  // Return array of active diagnoses ICD 
  public static function getActiveDiagnosesIcd($clientId) {
    $sql = "SELECT icd FROM data_diagnoses WHERE client_id=$clientId AND icd IS NOT NULL AND session_id IS NULL AND active=1";
    return fetchSimpleArray($sql, 'icd');
  }

  // Return simple array of entire session diagnoses history;
  public static function getDiagnosesHistory($clientId) {
    $sql = "SELECT " . JDataDiagnosis::SQL_FIELDS . " FROM data_diagnoses WHERE client_id=" . $clientId . " AND session_id IS NOT NULL ORDER BY date DESC, session_id, text";
    return JDataDiagnosis::fromRows(query($sql));
  }

  public static function getDiagnosis($id) {
    $sql = "SELECT " . JDataDiagnosis::SQL_FIELDS . " FROM data_diagnoses WHERE data_diagnoses_id=" . $id;
    $d = JDataDiagnosis::fromRow(fetch($sql));
    if ($d != null) {
      LoginDao::authenticateDataDiagnosis($id);
    }
    return $d;
  }
}
?>