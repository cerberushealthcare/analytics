<?php
require_once 'php/data/json/_util.php';

/*
 * Health Maintenance
 * Facesheet Data Record
 *  
 * Organization:  
 *   ugid, cid, type=1,..
 *     sid>0, proc             // built from closed note (active=NULL not yet processed by Facesheet DAO, active=0 processed)
 *     sid=0, proc, date_sort  // facesheet proc results history
 *     sid=NULL, proc          // facesheet summary record (last result and next due info)     
 */
class JDataHm {

  public $id;
  public $userGroupId;
  public $clientId;
  public $sessionId;
  public $type;          // currently only '1' 
  public $procId;        // lookup instance if selected from lookup table, else null for freetext
  public $proc;          // supplied from proc list if selected, else freetexted
  public $results;       // ['adenomatous polyps','dysplasitic polyps'] or ['free text']
  public $dateText;      // 'in December of 2008'
  public $dateSort;      // 1/1/2008 (approximation of dateText)
  public $nextText;      // 'in December of 2008'
  public $nextTimestamp; // for fs record, UNIX timestamp when proc should be done next
  public $updated;
  public $active;
  public $interval;      // for facesheet rec, to override proc int/every
  public $every;         // for facesheet rec, to override proc int/every
  
  // Derived
  public $dateShort;  // 'Dec 2008' or '12-Dec-2008'
  public $nextShort;  // 'Dec 2010' or '2010'
  public $nextExpireText;   
  
  const TYPE_HM_PROC = 1;
  
  const SQL_FIELDS = 'data_hm_id, user_group_id, client_id, session_id, type, proc_id, proc, date_text, date_sort, results, next_text, next_timestamp, active, date_updated, cint, cevery';
  
  public function __construct($id, $userGroupId, $clientId, $sessionId, $type, $procId, $proc, $dateText, $dateSort, $results, $nextText, $nextTimestamp, $active, $updated, $interval, $every) {
    $this->id = $id;
    $this->userGroupId = $userGroupId;
    $this->clientId = $clientId;
    $this->sessionId = $sessionId;
    $this->type = $type;
    $this->procId = $procId;
    $this->proc = $proc;
    $this->setDate($dateText, $dateSort);
    $this->results = JDataHm::arrayify($results);
    $this->setNext($nextText, $nextTimestamp);
    $this->active = $active;
    $this->updated = $updated;
    $this->interval = $interval;
    $this->every = $every;
  }
  public function setNext($text, $timestamp) {
    $this->nextText = $text;
    $this->nextShort = calcShortDate($text);
    $this->nextTimestamp = $timestamp;
    if ($timestamp != null) {
      $this->nextExpireText = $this->calcExpires();
    } else {
      $this->nextExpireText = null;
    }
  }
  private function arrayify($s) {
    if ($s != null) {
      if (substr($s, 0, 1) != '[') {
        $s = "['$s']";
      }
    }
    return $s;
  }
  private function calcExpires() {
    $now = nowUnix();
    $ynow = date('Y', $now);
    $y = date('Y', $this->nextTimestamp);
    if ($ynow > $y) return 'past due';
    if ($ynow < $y) return null;
    if (strlen($this->nextText) == 7) {  // year only
      return 'due now';
    }
    $mnow = date('n', $now);
    $m = date('n', $this->nextTimestamp);
    if ($mnow > $m) return 'past due';
    if ($mnow == $m) return 'due now';
    return null;
  }
  public function setDate($text, $date) {
    $this->dateText = $text;
    $this->dateShort = calcShortDate($text);
    $this->dateSort = $date;
  }
  public function setHmDueNow() {
    $dt = strtotime("now");
    $s = date("F j, Y", $dt);  // March 14, 2009
    $this->setNext("on " . $s, $dt);
  }
	public function setHmNextDate($proc) {
    $every = $proc->every;
    $int = $proc->int;
    if ($this->every != null) {
      $every = $this->every;
      $int = $this->interval;
    }
    if ($this->dateSort == null || $this->dateText == null) {
      return;
    }
    if ($every == null) {
      return;
    }
    $dt = strtotime($this->dateSort);
    switch ($int) {
      case 0:  // year
        $dt = mktime(0, 0, 0, date("n", $dt), 1, date("Y", $dt) + $every);
        break;
      case 1:  // month
        $dt = mktime(0, 0, 0, date("n", $dt) + $every, 1, date("Y", $dt));
        break;
      case 2:  // week
        $dt = mktime(0, 0, 0, date("n", $dt), date("j", $dt) + $every * 7, date("Y", $dt));
        break;
      case 3:  // day
        $dt = mktime(0, 0, 0, date("n", $dt), date("j", $dt) + $every, date("Y", $dt));
        break;
    }
    if ($int < 2 || substr($this->dateText, 0, 2) == "in") {  // use vague date
      if (strlen($this->dateText) == 7) {  // year only
        $s = date("Y", $dt);  // 2009
      } else {  // month only
        $s = date("F \o\f\ Y", $dt);  // March of 2009
      }
      $this->setNext("in " . $s, $dt);
    } else {  // use specific date
      $s = date("F j, Y", $dt);  // March 14, 2009
      $this->setNext("on " . $s, $dt);
    }
  }
  public function out() {
    $out = '';
    $out = nqq($out, 'id', $this->id);
    $out = nqq($out, 'clientId', $this->clientId);
    $out = nqq($out, 'sessionId', $this->sessionId);
    $out = nqq($out, 'type', $this->type);
    $out = nqq($out, 'procId', $this->procId);
    $out = nqq($out, 'proc', $this->proc);
    $out = nqq($out, 'dateText', $this->dateText);
    $out = nqq($out, 'dateShort', $this->dateShort);
    $out = nqqo($out, 'results', $this->results);
    $out = nqq($out, 'nextText', $this->nextText);
    $out = nqq($out, 'nextShort', $this->nextShort);
    $out = nqq($out, 'nextExpireText', $this->nextExpireText);
    $out = nqqo($out, 'int', $this->interval);
    $out = nqqo($out, 'every', $this->every);
    return cb($out);
  }
  public function buildProcDateKey() {
    return "$this->procId,$this->dateSort";
  }
  
	// Static functions
	public static function copy($dto) {
	  return new JDataHm(
	     null,
       $dto->userGroupId,
       $dto->clientId,
       $dto->sessionId,
       $dto->type,
       $dto->procId,
       $dto->proc,
       $dto->dateText,
       $dto->dateSort,
       $dto->results,
       $dto->nextText,
       $dto->nextTimestamp,
       $dto->active,
       $dto->updated,
       $dto->interval,
       $dto->every
	     );
	}
	public static function areDifferent($hm1, $hm2) {  
	  return $hm1->dateText != $hm2->dateText 
	      || $hm1->results != $hm2->results;
  }
  public static function shouldApply($proc, $client, $icds) {
    if (! $proc->auto) {
      return false;
    }
    if (get($proc, 'icd')) {
      if (JDataHm::icdMatch($proc->icd, $icds)) {
        return true;
      }
      if ($proc->gender == null && $proc->after == null && $proc->until == null) {
        return false;
      }
    }
    if ($proc->gender != null && $client->sex != $proc->gender) {
      return false;
    }
    if ($proc->after != null && $client->age < $proc->after) {
      return false;
    }
    if ($proc->until != null && $client->age > $proc->until) {
      return false;
    }
    return true;
  }
  /*
   * $wildcard: '101.01,101.02'
   * $icds: ['101.01','163.4','200.01']
   */
  private static function icdMatch($wildcard, $icds) {
    if (count($icds) == 0) {
      return false;
    }
    $wildcards = explode(',', $wildcard);
    foreach ($wildcards as $icd) {
      if (in_array($icd, $icds)) {
        return true;
      }
    }
    return false;
  }
  /**
   * Static builders
   */
  public static function fromRows($res, $assocBy = null) {
    $hms = array();
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      $rec = JDataHm::fromRow($row);
      if ($assocBy != null) {
        $hms[$row[$assocBy]] = $rec;
      } else {
        $hms[] = $rec;
      }
    }
    return $hms;
  }
  public static function fromRow($row) {
    if (! $row) return null;
    return new JDataHm(
        $row["data_hm_id"],
        $row["user_group_id"],
        $row["client_id"],
        $row["session_id"],
        $row["type"],
        $row["proc_id"],
        $row["proc"],
        $row["date_text"],
        $row["date_sort"],
        $row["results"],
        $row["next_text"],
        $row["next_timestamp"],
        $row["active"],
        $row["date_updated"],
        $row["cint"],
        $row["cevery"]
        );
  }
	public static function save($dto, $escape = false, $audit = true) {
    if ($dto->id != null) {
      LoginDao::authenticateDataHm($dto->id);
      $sql = "UPDATE data_hm SET ";
      $sql .= "date_text=" . quote($dto->dateText);
      $sql .= ", date_sort=" . quoteDate($dto->dateSort);
      $sql .= ", results=" . quote($dto->results, $escape);
      $sql .= ", next_text=" . quote($dto->nextText);
      $sql .= ", next_timestamp=" . quote($dto->nextTimestamp);
      $sql .= ", active=" . toBoolInt($dto->active);
      $sql .= ", cint=" . quote($dto->interval);
      $sql .= ", cevery=" . quote($dto->every);
      $sql .= ", date_updated=NULL";
      $sql .=" WHERE data_hm_id=" . $dto->id;
      query($sql);
      if ($audit) AuditDao::log($dto->clientId, AuditDao::ENTITY_DATA_HM, $dto->id, AuditDao::ACTION_UPDATE, null, $dto->proc);
    } else {
      LoginDao::authenticateClientId($dto->clientId);
      $sql = "INSERT INTO data_hm VALUE(NULL";
      $sql .= ", " . quote($dto->userGroupId);
      $sql .= ", " . quote($dto->clientId);
      $sql .= ", " . quote($dto->sessionId);
      $sql .= ", " . quote($dto->type);
      $sql .= ", " . quote($dto->procId);
      $sql .= ", " . quote($dto->proc);
      $sql .= ", " . quote($dto->dateText);
      $sql .= ", " . quoteDate($dto->dateSort);
      $sql .= ", " . quote($dto->results, $escape);
      $sql .= ", " . quote($dto->nextTimestamp);
      $sql .= ", " . toBoolInt($dto->active);
      $sql .= ", NULL";  // date_updated
      $sql .= ", " . quote($dto->nextText);
      $sql .= ", NULL";  // cint
      $sql .= ", NULL";  // cevery
      $sql .= ")";
      $dto->id = insert($sql);
      if ($audit) AuditDao::log($dto->clientId, AuditDao::ENTITY_DATA_HM, $dto->id, AuditDao::ACTION_CREATE, null, $dto->proc);
    }
    return $dto;
  }
  // Return simple array of unread (null active) session recs
  public static function getUnreadSessionHms($clientId) {
    $sql = "SELECT " . JDataHm::SQL_FIELDS . "  FROM data_hm WHERE client_id=" . $clientId
        . " AND active IS NULL AND date_sort IS NOT NULL AND session_id>0 ORDER BY proc_id, date_sort, date_updated DESC";
    return JDataHm::fromRows(query($sql));
  }

  // Return collection of recs associated by proc_id, date_sort
  public static function getHistHmsByProcDateKey($clientId) {
    $sql = "SELECT " . JDataHm::SQL_FIELDS . " FROM data_hm WHERE client_id=" . $clientId
        . " AND session_id=0 ORDER BY proc_id, date_sort DESC";
    $res = query($sql);
    $hms = array();
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      $rec = JDataHm::fromRow($row);
      $hms[$rec->buildProcDateKey()] = $rec;
    }
    return $hms;
  }
  
  // Return collection of distinct (by proc) history recs, associated by proc
  public static function getHistHmsByProc($clientId) {
    $sql = "SELECT " . JDataHm::SQL_FIELDS . " FROM (SELECT * FROM data_hm WHERE client_id=" . $clientId
        . " AND active=1 AND session_id=0 ORDER BY date_sort DESC) a GROUP BY proc ORDER BY proc";
    return JDataHm::fromRows(query($sql), "proc");
  }

  // Return simple array of active health maintenance history recs
  public static function getHmsHistory($clientId, $includeSummaryRecs = true) {  // summary: session=0 recs
    $fields = JDataHm::SQL_FIELDS;
    $filter = ($includeSummaryRecs) ? "(session_id IS NULL OR session_id=0)" : "session_id=0";
    $sql = "SELECT $fields FROM data_hm WHERE client_id=$clientId AND $filter AND active>=1 ORDER BY proc, date_sort DESC";
    $hist = JDataHm::fromRows(query($sql));
    return $hist;
  }

  // Return collection of non-session diagnoses health maint records, associated by proc
  public static function getFacesheetHms($clientId) {
    $sql = "SELECT " . JDataHm::SQL_FIELDS . " FROM data_hm WHERE client_id=" . $clientId
        . " AND session_id IS NULL ORDER BY proc, data_hm_id";  // added data_hm_id to ensure most recent one is used
    return JDataHm::fromRows(query($sql), "proc");
  }

  public static function getHm($id) {
    $sql = "SELECT " . JDataHm::SQL_FIELDS . " FROM data_hm WHERE data_hm_id=" . $id;
    $hm = JDataHm::fromRow(fetch($sql));
    if ($hm != null) {
      LoginDao::authenticateDataHm($id);
    }
    return $hm;
  }
}  
?>