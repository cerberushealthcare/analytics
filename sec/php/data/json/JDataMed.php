<?php
require_once 'php/data/json/_util.php';
//
/**
 * Med 
 * Facesheet Data Record
 * 
 * Organization:
 *   ugid, cid,..
 *     sid>0, date=DOS, quid=quid, index=cix  // built from closed note
 *     sid=NULL, name                         // facesheet summary record
 *     sid=0, name, date_updated              // facesheet update history
 */
class JDataMed {
  //
  public $id;
  public $userGroupId;
  public $clientId;
  public $sessionId;
	public $date;  // dos
	public $quid;
	public $index;
	public $name;
	public $amt;
	public $freq;
	public $asNeeded;
	public $meals;
	public $route;
	public $length;
	public $disp;
	public $text;
	public $rx;
	public $active;  // saved for facesheet recs (session_id=0); for history records, synced with corresponding facesheet active value 
	public $expires;
	public $updated;
	public $source;
  //
	const SQL_FIELDS = 'data_med_id, user_group_id, client_id, session_id, date, quid, `index`, name, amt, freq, as_needed, meals, route, length, disp, text, rx, active, expires, date_updated, source';
  //
  const QUID_CURRENT       = 'meds.meds.@addMed';
  const QUID_ADD           = 'med mgr.medMgr.@addMed';
  const QUID_DISCONTINUE   = 'med mgr.medMgr.@dcMed';
  const QUID_REFILL        = 'med mgr.medMgr.@rfMed'; 
  const QUID_FS_ADD        = 'fs.add'; 
  const QUID_FS_CHANGE     = 'fs.change'; 
  const QUID_FS_DEACTIVATE = 'fs.deactivate'; 
  const QUID_FS_RX         = 'fs.rx';
  const QUID_MSG_REFILL    = 'response.callInRx.@rfMed';
  const QUID_NC_ADD        = 'nc.add';
  const QUID_NC_DC         = 'nc.dc';
  const QUID_NC_RX         = 'nc.rx';
  //
  const SOURCE_NEWCROP            = 1; 
  //
  const SID_NC_AUDIT = 0;
  const ADDL_SIG = "Add'l Sig";
  //
	public function __construct($id, $userGroupId, $clientId, $sessionId, $date, $quid, $index, $name, $amt, $freq, $asNeeded, $meals, $route, $length, $disp, $text, $rx, $active, $expires, $updated, $source) {
    $this->id = $id;
    $this->userGroupId = $userGroupId;
    $this->clientId = $clientId;
    $this->sessionId = $sessionId;
    $this->date = $date;
    $this->quid = $quid;
    $this->index = $index;
    $this->name = $name;
    $this->amt = $amt;
    $this->freq = $freq;
    $this->asNeeded = ($asNeeded == 1) ? 1 : 0; 
    $this->meals = $meals;
    $this->route = $route;
    $this->length = $length;
    $this->disp = $disp;
    $this->setText($text);
    $this->rx = $rx;
    $this->active = $active;
    $this->expires = $expires;
    $this->updated = $updated;
    $this->source = $source;
	}
	public function isLongTerm() {
	  if (trim($this->length) == '' || $this->length == 'long-term') {
	    return true;
	  } else {
	    return false;
	  }
	}
	public static function isDiscontinued($quid) {
	  return JDataMed::getQuidText($quid) == 'Discontinued';
	}
	public static function getQuidText($quid) {
    switch ($quid) {
      case JDataMed::QUID_ADD:
      case JDataMed::QUID_FS_ADD:
      case JDataMed::QUID_NC_ADD:
      case 'plan.plan.@addMed':
      case 'plan.meds.@addMed':
        return 'Added';
      case JDataMed::QUID_CURRENT:
        return 'Listed';
      case JDataMed::QUID_DISCONTINUE:
      case JDataMed::QUID_NC_DC:
      case 'plan.meds.@dcMed':
      case 'plan.plan.@dcMed':
        return 'Discontinued';
      case JDataMed::QUID_FS_CHANGE:
        return 'Changed';
      case JDataMed::QUID_FS_DEACTIVATE:
        return 'Deactivated';
      case JDataMed::QUID_REFILL:
      case JDataMed::QUID_MSG_REFILL:
      case 'plan.plan.@rfMed':
        return 'Refilled';
      case JDataMed::QUID_FS_RX:
        return 'Printed';
      case JDataMed::QUID_NC_RX:
        return 'Prescribed';
    }
	}
	public function setText($text) {
	  $a = explode(' (Disp:', $text);
	  $this->text = $a[0];
	}
	public function setExpires($date = null) {
	  if ($date == null) {
	    $date = $this->date;
	  }
	  if ($this->isLongTerm()) {
	    $this->expires = null;
	    $this->length = null;
	  } else {
	    if (strpos($this->length, 'day') > 0) {
        $days = intval($this->length); 
        $dt = strtotime($date);
	      $dt = mktime(0, 0, 0, date('n', $dt), date('j', $dt) + $days, date('Y', $dt));  // add days
	      $this->expires = date('Y-m-d', $dt);
	    }
	  }
	}
	public function isExpired() {
	  if ($this->expires == null) return false;
	  return isPast($this->expires);
	}
	public function formatExpireText() {
	  if ($this->expires == null) {
	    return null;
	  }
	  return 'EXPIRES: ' . formatShortDate($this->expires);
	}
	public function buildSig() {
    $t = '';
    if (! isBlank($this->amt)) {
      $t .= ' ' . $this->amt;
    }
    if (! isBlank($this->freq)) {
      $t .= ' ' . $this->freq;
    }
    if (! isBlank($this->route)) {
      $t .= ' ' . $this->route;
    }
    if ($this->asNeeded) {
      $t .= ' as needed';
    }
    if ($this->meals) {
      $t .= ' with meals';
    }
    if (! isBlank($this->length)) {
      $t .= ' for ' . $this->length;
    }
    return trim($t);
	}
	public function out() {
    $out = '';
    $out = nqq($out, 'id', $this->id);
    $out = nqq($out, 'clientId', $this->clientId);
    $out = nqq($out, 'sessionId', $this->sessionId);
    $out = nqq($out, 'date', formatDate($this->date));
    $out = nqq($out, 'quid', JDataMed::getQuidText($this->quid));
    $out = nqq($out, 'index', $this->index);
    $out = aqq($out, 'name', denull($this->name));
    $out = nqq($out, 'amt', $this->amt);
    $out = nqq($out, 'freq', $this->freq);
    $out = nqqo($out, 'asNeeded', $this->asNeeded);
    $out = nqq($out, 'meals', $this->meals);
    $out = nqq($out, 'route', $this->route);
    $out = nqq($out, 'length', $this->length);
    $out = nqq($out, 'disp', $this->disp);
    $out = nqq($out, 'text', $this->text);
    $out = nqq($out, 'rx', $this->rx);
    $out = nqqo($out, 'active', $this->active);
    $out = nqq($out, 'expires', $this->expires);
    $out = nqq($out, 'expireText', $this->formatExpireText());
    $out = nqqo($out, 'source', $this->source);
    return cb($out);	  
	}
	//
	/**
	 * Static builder (clone)
	 * @param JDataMed $dto 
	 * @return JDataMed
	 */
	public static function copy($dto) {
	  return new JDataMed(
	    null,
      $dto->userGroupId,
      $dto->clientId,
      $dto->sessionId,
      $dto->date,
      $dto->quid,
      $dto->index,
      $dto->name,
      $dto->amt,
      $dto->freq,
      $dto->asNeeded,
      $dto->meals,
      $dto->route,
      $dto->length,
      $dto->disp,
      $dto->text,
      $dto->rx,
      $dto->active,
      $dto->expires,
      $dto->updated,
      $dto->source);
	}
  public static function copyAsFace($dto, $id = null) {
    $face = JDataMed::copy($dto);
    $face->id = $id;
    $face->sessionId = null;
    $face->quid = null;
    $face->rx = null;
    $face->active = true;
    return $face;
  }
  public static function copyAsNcAudit($dto, $quid, $now = null) {
    $med = JDataMed::copy($dto);
    $med->sessionId = JDataMed::SID_NC_AUDIT;
    if ($now) 
      $med->date = $now;
    $med->quid = $quid;
    return $med;
  }
	/**
   * Static builders
   */
	public static function fromRows($res, $assocBy = null) {
    $meds = array();
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      $rec = JDataMed::fromRow($row);
      if ($assocBy != null) {
        $meds[$row[$assocBy]] = $rec;
      } else {
        $meds[] = $rec;
      }
    }
    return $meds;
  }
  public static function fromRow($row) {
    if (! $row) return null;
    return new JDataMed(
        $row["data_med_id"],
        $row["user_group_id"],
        $row["client_id"],
        $row["session_id"],
        $row["date"],
        $row["quid"],
        $row["index"],
        $row["name"],
        $row["amt"],
        $row["freq"],
        $row["as_needed"],
        $row["meals"],
        $row["route"],
        $row["length"],
        $row["disp"],
        $row["text"],
        $row["rx"],
        $row["active"],
        $row["expires"],
        $row["date_updated"],
        $row["source"]
        );
  }
	/**
	 * Static builders
	 * @param int $ugid
	 * @param int $cid 
	 * @param array $meds; see NewCrop->getCurrent()
	 * @return array('name'->JDataMed,..)
	 */
	public static function fromNewCropMeds($ugid, $cid, $meds) {
	  $dtos = array();
	  if ($meds)
  	  foreach ($meds as $med) {
  	    $dto = JDataMed::fromNewCropMed($ugid, $cid, $med);
  	    $old = geta($dtos, $dto->name);
  	    if ($old && compareDates($old->date, $dto->date) == 1) {
          // don't replace newer one already there  	      
  	    } else {  
    	    $dtos[$dto->name] = $dto;
  	    }
  	  }
	  return $dtos;
	}
  /**
   * Static builder
   * @param int $ugid
   * @param int $cid 
   * @param object $med; see NewCrop->getCurrent()
   * @return JDataMed
   */
  public static function fromNewCropMed($ugid, $cid, $med) {
    logit_r($med, 'fromNewCropMed');
    $name = $med->DrugName;
    $amt = $med->DosageNumberDescription;
    if ($med->DosageForm != JDataMed::ADDL_SIG)
      $amt = "$amt $med->DosageForm";
    $freq = $med->DosageFrequencyDescription;
    if ($med->PrescriptionNotes) {
      if ($amt != JDataMed::ADDL_SIG || $freq != JDataMed::ADDL_SIG) {
        if ($amt == JDataMed::ADDL_SIG) 
          $amt = $med->PrescriptionNotes;
        if ($freq == JDataMed::ADDL_SIG) 
          $freq = $med->PrescriptionNotes;
        $med->PrescriptionNotes = null;
      }
    }
    if (! isblank($med->Strength)) 
      $name .= " ($med->Strength $med->StrengthUOM)";
    $dto = new JDataMed(
      null,
      $ugid,
      $cid,
      JDataMed::SID_NC_AUDIT,
      datetimeToString($med->PrescriptionDate),
      null,
      null,
      $name,
      $amt,
      $freq,
      ($med->TakeAsNeeded == 'Y') ? 1 : 0,
      null,
      $med->Route,
      null,
      $med->Dispense,
      null,
      JDataMed::rxFromNewCrop($med),
      null,
      null,
      null,
      JDataMed::SOURCE_NEWCROP);
    if ($med->PrescriptionNotes) 
      $dto->text = $med->PrescriptionNotes;
    else
      $dto->text = $dto->buildSig();
    return $dto;
  }
  private static function rxFromNewCrop($med) {
    $rx = null;
    if ($med->OrderGuid != '00000000-0000-0000-0000-000000000000') {
      $date = $med->PrescriptionDate;
      $disp = $med->Dispense;
      $refill = $med->Refills; 
      $rx = "RX: $date Disp: $disp, Refills: $refill";
    } 
    return $rx;
  }
	//
	public static function cmp($a, $b) {
	  if ($a->active != $b->active) {
	    return ($a->active) ? -1 : 1;
	  }
	  if ($a->expires != $b->expires) {
	    if ($a->expires == null) return -1;
	    if ($b->expires == null) return 1;
	    return compareDates($a->expires, $b->expires);
	  }
	  return ($a->name < $b->name) ? -1 : 1;
	}
	//
	public static function save($dto, $escape = false, $audit = true) {
    if ($dto->id != null) {
      LoginDao::authenticateDataMed($dto->id);
      $sql = "UPDATE data_meds SET ";
      $sql .= "session_id=" . quote($dto->sessionId);
      $sql .= ", date=" . quote($dto->date);
      $sql .= ", quid=" . quote($dto->quid);
      $sql .= ", name=" . quote($dto->name, $escape);
      $sql .= ", amt=" . quote($dto->amt, $escape);
      $sql .= ", freq=" . quote($dto->freq, $escape);
      $sql .= ", as_needed=" . quote($dto->asNeeded);
      $sql .= ", meals=" . quote($dto->meals);
      $sql .= ", route=" . quote($dto->route, $escape);
      $sql .= ", length=" . quote($dto->length, $escape);
      $sql .= ", disp=" . quote($dto->disp, $escape);
      $sql .= ", text=" . quote($dto->text, $escape);
      $sql .= ", rx=" . quote($dto->rx, $escape);
      $sql .= ", active=" . toBoolInt($dto->active);
      $sql .= ", expires=" . quote($dto->expires);
      $sql .= ", date_updated=NULL";
      $sql .=" WHERE data_med_id=" . $dto->id;
      if ($audit) AuditDao::log($dto->clientId, AuditDao::ENTITY_DATA_MEDS, $dto->id, AuditDao::ACTION_UPDATE, null, $dto->name);
      query($sql);
    } else {
      LoginDao::authenticateClientId($dto->clientId);
      $sql = "INSERT INTO data_meds VALUE(NULL";
      $sql .= ", " . quote($dto->userGroupId);
      $sql .= ", " . quote($dto->clientId);
      $sql .= ", " . quote($dto->sessionId);
      $sql .= ", " . quote($dto->date);
      $sql .= ", " . quote($dto->quid);
      $sql .= ", " . quote($dto->index);
      $sql .= ", " . quote($dto->name, $escape);
      $sql .= ", " . quote($dto->amt, $escape);
      $sql .= ", " . quote($dto->freq, $escape);
      $sql .= ", " . quote($dto->asNeeded);
      $sql .= ", " . quote($dto->meals);
      $sql .= ", " . quote($dto->route, $escape);
      $sql .= ", " . quote($dto->length, $escape);
      $sql .= ", " . quote($dto->disp, $escape);
      $sql .= ", " . quote($dto->text, $escape);
      $sql .= ", " . quote($dto->rx, $escape);
      $sql .= ", " . toBoolInt($dto->active);
      $sql .= ", " . quote($dto->expires);
      $sql .= ", NULL";  // date_updated
      $sql .= ", " . quote($dto->source);
      $sql .= ")";
      $dto->id = insert($sql);
      if ($audit) AuditDao::log($dto->clientId, AuditDao::ENTITY_DATA_MEDS, $dto->id, AuditDao::ACTION_CREATE, null, $dto->name);
    }
    return $dto;
  }
  public static function getMed($id) {
    $sql = "SELECT " . JDataMed::SQL_FIELDS . " FROM data_meds WHERE data_med_id=" . $id;
    $med = JDataMed::fromRow(fetch($sql), true);
    if ($med != null) {
      LoginDao::authenticateDataMed($id);
    }
    return $med;
  }
  public static function getClientMedByName($clientId, $name) {
    $row = fetch("SELECT data_med_id FROM data_meds WHERE session_id IS NULL and client_id=" . $clientId . " AND name=" . quote($name, true) . " ORDER BY data_med_id DESC");
    if ($row) {
      return $row["data_med_id"];
    }
  }
  // Return collection of distinct (by name) meds from session history, associated by name
  public static function getSessionMeds($clientId) {  // added order by QUID to give "med mgr.medMgr.@dcMed" precedence. note that this only works thru a quirk of the naming convention for these! if not for this, a more elaborate query would be necessary.
    $sql = "SELECT " . JDataMed::SQL_FIELDS . " FROM (SELECT * FROM data_meds WHERE client_id=" . $clientId . " AND session_id > 0 ORDER BY date DESC, date_updated DESC, QUID) a GROUP BY name ORDER BY name";
    return JDataMed::fromRows(query($sql), "name");
  }
  // Return collection of non-session med data records, associated by name
  public static function getFacesheetMeds($clientId) {
    $sql = "SELECT " . JDataMed::SQL_FIELDS . " FROM data_meds WHERE client_id=" . $clientId . " AND session_id IS NULL";
    return JDataMed::fromRows(query($sql), "name");
  }
  // Return collection of active non-session med data records, associated by name
  public static function getActiveFacesheetMeds($clientId) {
    $sql = "SELECT " . JDataMed::SQL_FIELDS . " FROM data_meds WHERE client_id=" . $clientId . " AND session_id IS NULL AND active=1";
    return JDataMed::fromRows(query($sql), "name");
  }
  /**
   * Fetch active facesheet meds (New Crop source)
   * @param int $clientId
   * @return array('medName'=>JDataMed,..)
   */
  public static function getActiveNewCropMeds($clientId) {
    $sql = "SELECT " . JDataMed::SQL_FIELDS . " FROM data_meds WHERE client_id=$clientId AND session_id IS NULL AND source=1 AND active=1";
    return JDataMed::fromRows(query($sql), "name");
  }
  /**
   * Fetch NewCrop audits since supplied time
   * @param int $clientId
   * @param string $since 'yyyy-mm-dd hh:mm:ss'
   * @return array(JDataMed,..)
   */
  public static function getNewCropAuditsSince($clientId, $since) {
    $sql = "SELECT " . JDataMed::SQL_FIELDS . " FROM data_meds WHERE client_id=$clientId AND session_id=0 AND source=1 AND date_updated>'$since'";    
    return JDataMed::fromRows(query($sql));
  }
  // Return simple array of entire session med history;
  public static function getMedsHistory($clientId, $orderBy) {
    $sql = "SELECT " . JDataMed::SQL_FIELDS . " FROM data_meds WHERE client_id=" . $clientId . " AND quid<>'meds.meds.@addMed' AND session_id IS NOT NULL ORDER BY " . $orderBy;
    return JDataMed::fromRows(query($sql));
  }
  public static function compareMedNames($name1, $name2) {  // returns 0=different med, 1=same med/diff dosage, 2=identical
    if ($name1 == $name2) {
      return 2;
    }
    $a1 = explode("(", $name1);
    $a2 = explode("(", $name2);
    return ($a1[0] == $a2[0]) ? 1 : 0;
  }
  public static function createMedAuditCopy($m, $quid, $rx = null) {  // Create a "history-audit" (session 0) record
    $med = JDataMed::copy($m);
    $med->sessionId = 0;
    $med->active = false;
    $med->date = nowShortNoQuotes();
    $med->quid = $quid;
    $med->rx = $rx;
    JDataMed::save($med, true, FacesheetDao::NO_AUDIT);
    return $med;
  }
  public static function updateLegacySource($clientId) {
    query("UPDATE data_meds SET source=0 WHERE client_id=$clientId AND source IS NULL");
  }
  public static function deactivateLegacy($clientId) {
    query("UPDATE data_meds SET active=0 WHERE client_id=$clientId AND (source IS NULL or source=0)");
  }
}
?>