<?php
require_once 'php/data/json/_util.php';

/*
 * DEPRECATED... now using data syncs
 * 
 * Medical History
 * Facesheet Data Record
 *  
 * Organization:
 *   ugid, cid, crel, hcat...
 *     hpcid, date_sort, sid=NULL  // Facesheet rec 
 *     hpcid, date_sort, sid>0     // SESSIONS rec (active=NULL is not processed by FacesheetDAO, active=0 is processed)    
 */
class JDataHist {

  public $id;
  public $userGroupId;
  public $clientId;
  public $clientRel;     // relation to client (0=self)
  public $sessionId;
  public $hcat;          // medical/surgical...
  public $hprocId;       // lookup instance if selected from lookup table, else null for freetext
  public $hproc;         // supplied from proc list if selected, else freetexted
  public $dateText;      // 'in December of 2008'
  public $dateSort;      // 1/1/2008 (approximation of dateText)
  public $type;          // subtype of hproc, e.g. 'squamous cell carcinoma'
  public $custom1;
  public $custom2;
  public $custom3;
  public $updated;
  public $active;
  
  // Derived
  public $dateShort;  // 'Dec 2008' or '12-Dec-2008'
  
  const HCAT_MED  = 0;
  const HCAT_SURG = 1;

  const SQL_FIELDS = 'data_hist_id, user_group_id, client_id, client_relation, session_id, hcat, hproc_id, hproc, date_text, date_sort, type, custom1, custom2, custom3, active, date_updated';

  public static $CLIENT_RELS = array(
    '0' => '(Self)',
    '100' => 'Father',
    '200' => 'Mother',
    '10' => 'Brother 1', 
    '11' => 'Brother 2', 
    '12' => 'Brother 3', 
    '13' => 'Brother 4', 
    '14' => 'Brother 5', 
    '15' => 'Brother 6', 
    '16' => 'Brother 7', 
    '17' => 'Brother 8', 
    '18' => 'Brother 9', 
    '20' => 'Sister 1', 
    '21' => 'Sister 2', 
    '22' => 'Sister 3', 
    '23' => 'Sister 4', 
    '24' => 'Sister 5', 
    '25' => 'Sister 6', 
    '26' => 'Sister 7', 
    '27' => 'Sister 8', 
    '28' => 'Sister 9',
    '110' => 'Paternal Uncle 1', 
    '111' => 'Paternal Uncle 2', 
    '112' => 'Paternal Uncle 3', 
    '113' => 'Paternal Uncle 4', 
    '114' => 'Paternal Uncle 5', 
    '115' => 'Paternal Uncle 6', 
    '116' => 'Paternal Uncle 7', 
    '117' => 'Paternal Uncle 8', 
    '118' => 'Paternal Uncle 9', 
    '120' => 'Paternal Aunt 1', 
    '121' => 'Paternal Aunt 2', 
    '122' => 'Paternal Aunt 3', 
    '123' => 'Paternal Aunt 4', 
    '124' => 'Paternal Aunt 5', 
    '125' => 'Paternal Aunt 6', 
    '126' => 'Paternal Aunt 7', 
    '127' => 'Paternal Aunt 8', 
    '128' => 'Paternal Aunt 9', 
    '210' => 'Maternal Uncle 1', 
    '211' => 'Maternal Uncle 2', 
    '212' => 'Maternal Uncle 3', 
    '213' => 'Maternal Uncle 4', 
    '214' => 'Maternal Uncle 5', 
    '215' => 'Maternal Uncle 6', 
    '216' => 'Maternal Uncle 7', 
    '217' => 'Maternal Uncle 8', 
    '218' => 'Maternal Uncle 9', 
    '220' => 'Maternal Aunt 1', 
    '221' => 'Maternal Aunt 2', 
    '222' => 'Maternal Aunt 3', 
    '223' => 'Maternal Aunt 4', 
    '224' => 'Maternal Aunt 5', 
    '225' => 'Maternal Aunt 6', 
    '226' => 'Maternal Aunt 7', 
    '227' => 'Maternal Aunt 8', 
    '228' => 'Maternal Aunt 9',
    '1100' => 'Paternal Grandfather', 
    '1200' => 'Paternal Grandmother', 
    '1100' => 'Maternal Grandfather', 
    '1200' => 'Maternal Grandmother'
    ); 
  
  public function __construct($id, $userGroupId, $clientId, $clientRel, $sessionId, $hcat, $hprocId, $hproc, $dateText, $dateSort, $type, $custom1, $custom2, $custom3, $active, $updated) {
    $this->id = $id;
    $this->userGroupId = $userGroupId;
    $this->clientId = $clientId;
    $this->clientRel = $clientRel;
    $this->sessionId = $sessionId;
    $this->hcat = $hcat;
    $this->hprocId = $hprocId;
    $this->hproc = $hproc;
    $this->setDate($dateText, $dateSort);
    $this->type = $type;
    $this->custom1 = $custom1;
    $this->custom2 = $custom2;
    $this->custom3 = $custom3;
    $this->active = $active;
    $this->updated = $updated;
  }
  public function setDate($text, $date) {
    $this->dateText = $text;
    $this->dateShort = calcShortDate($text);
    $this->dateSort = $date;
  }
  public function out() {
    $out = '';
    $out = nqq($out, 'id', $this->id);
    $out = nqq($out, 'clientId', $this->clientId);
    $out = nqq($out, 'clientRel', $this->clientRel);
    $out = nqq($out, 'sessionId', $this->sessionId);
    $out = nqq($out, 'hcat', $this->hcat);
    $out = nqq($out, 'hprocId', $this->hprocId);
    $out = nqq($out, 'hproc', $this->hproc);
    $out = nqq($out, 'dateText', $this->dateText);
    $out = nqq($out, 'dateShort', $this->dateShort);
    $out = nqqo($out, 'type', $this->type);
    $out = nqqo($out, 'custom1', $this->custom1);
    $out = nqqo($out, 'custom2', $this->custom2);
    $out = nqqo($out, 'custom3', $this->custom3);
    return cb($out);
  }
  public function buildProcDateKey() {
    return "$this->hprocId,$this->dateSort";
  }
    
	// Static functions
	public static function copy($dto) {
	  return new JDataHist(
	     null,
       $dto->userGroupId,
       $dto->clientId,
       $dto->clientRel,
       $dto->sessionId,
       $dto->hcat,
       $dto->hprocId,
       $dto->hproc,
       $dto->dateText,
       $dto->dateSort,
       $dto->type,
       $dto->custom1,
       $dto->custom2,
       $dto->custom3,
       $dto->active,
       $dto->updated
	     );
	}
  public static function areDifferent($h1, $h2) {  
    return $h1->dateText != $h2->dateText 
        || $h1->type != $h2->type
        || $h1->custom1 != $h2->custom1
        || $h1->custom2 != $h2->custom2
        || $h1->custom3 != $h2->custom3;
  }
  public static function fromRows($res, $assocBy = null) {
    $hists = array();
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      $rec = JDataHist::fromRow($row);
      if ($assocBy != null) {
        $hists[$row[$assocBy]] = $rec;
      } else {
        $hists[] = $rec;
      }
    }
    return $hists;
  }
  public static function fromRow($row) {
    if (! $row) return null;
    return new JDataHist(
        $row["data_hist_id"],
        $row["user_group_id"],
        $row["client_id"],
        $row["client_relation"],
        $row["session_id"],
        $row["hcat"],
        $row["hproc_id"],
        $row["hproc"],
        $row["date_text"],
        $row["date_sort"],
        $row["type"],
        $row["custom1"],
        $row["custom2"],
        $row["custom3"],
        $row["active"],
        $row["date_updated"]
        );
  }
  public static function save($dto, $escape = false, $audit = true) {
    if ($dto->id != null) {
      LoginDao::authenticateDataHist($dto->id);
      $sql = "UPDATE data_hists SET ";
      $sql .= "date_text=" . quote($dto->dateText);
      $sql .= ", date_sort=" . quoteDate($dto->dateSort);
      $sql .= ", type=" . quote($dto->type, $escape);
      $sql .= ", custom1=" . quote($dto->custom1, $escape);
      $sql .= ", custom2=" . quote($dto->custom2, $escape);
      $sql .= ", custom3=" . quote($dto->custom3, $escape);
      $sql .= ", active=" . toBoolInt($dto->active);
      $sql .= ", date_updated=NULL";
      $sql .=" WHERE data_hist_id=" . $dto->id;
      query($sql);
      if ($audit) AuditDao::log($dto->clientId, AuditDao::ENTITY_DATA_MED_HIST, $dto->id, AuditDao::ACTION_UPDATE, null, $dto->hproc);
    } else {
      LoginDao::authenticateClientId($dto->clientId);
      $sql = "INSERT INTO data_hists VALUE(NULL";
      $sql .= ", " . quote($dto->userGroupId);
      $sql .= ", " . quote($dto->clientId);
      $sql .= ", " . quote($dto->clientRel);
      $sql .= ", " . quote($dto->sessionId);
      $sql .= ", " . quote($dto->hcat);
      $sql .= ", " . quote($dto->hprocId);
      $sql .= ", " . quote($dto->hproc);
      $sql .= ", " . quote($dto->dateText);
      $sql .= ", " . quoteDate($dto->dateSort);
      $sql .= ", " . quote($dto->type, $escape);
      $sql .= ", " . quote($dto->custom1, $escape);
      $sql .= ", " . quote($dto->custom2, $escape);
      $sql .= ", " . quote($dto->custom3, $escape);
      $sql .= ", " . toBoolInt($dto->active);
      $sql .= ", NULL";  // date_updated
      $sql .= ")";
      $dto->id = insert($sql);
      if ($audit) AuditDao::log($dto->clientId, AuditDao::ENTITY_DATA_MED_HIST, $dto->id, AuditDao::ACTION_CREATE, null, $dto->hproc);
    }
    return $dto;
  }

  // Return simple array of unread (null active) session recs
  public static function getUnreadSessionHists($clientId, $hcat) {
    $sql = "SELECT " . JDataHist::SQL_FIELDS . "  FROM data_hists WHERE client_id=" . $clientId . " AND hcat=" . $hcat
        . " AND active IS NULL AND session_id>0 ORDER BY hproc_id, date_sort, date_updated DESC";
    return JDataHist::fromRows(query($sql));
  }

  // Return collection of facesheet recs associated by proc_id, date_sort
  public static function getFaceHists($clientId, $hcat) {
    $sql = "SELECT " . JDataHist::SQL_FIELDS . " FROM data_hists WHERE client_id=" . $clientId . " AND hcat=" . $hcat
        . " AND session_id IS NULL ORDER BY hproc_id, date_sort DESC";
    $res = query($sql);
    $hists = array();
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      $rec = JDataHist::fromRow($row);
      $hists[$rec->buildProcDateKey()] = $rec;
    }
    return $hists;
  }
  
  // Return collection of distinct (by proc) history recs, associated by proc
  public static function getHistHistsByProc($clientId, $hcat) {
    $sql = "SELECT " . JDataHist::SQL_FIELDS . " FROM (SELECT * FROM data_hists WHERE client_id=" . $clientId . " AND hcat=" . $hcat
        . " AND active=1 AND session_id=0 ORDER BY date_sort DESC) a GROUP BY hproc ORDER BY hproc";
    return JDataHist::fromRows(query($sql), "hproc");
  }

  // Return simple array of active medical history recs
  public static function getHistsHistory($clientId) {
    $sql = "SELECT " . JDataHist::SQL_FIELDS . " FROM data_hists WHERE client_id=" . $clientId
        . " AND (session_id IS NULL OR session_id=0) AND active=1 ORDER BY hproc, date_sort DESC";
    $hist = JDataHist::fromRows(query($sql));
    return $hist;
  }
  
  // Return collection of non-session diagnoses health maint records, associated by proc
  public static function getFacesheetHists($clientId, $hcat) {
    $sql = "SELECT " . JDataHist::SQL_FIELDS . " FROM data_hists WHERE client_id=" . $clientId . " AND hcat=" . $hcat
        . " AND session_id IS NULL ORDER BY hproc";
    return JDataHist::fromRows(query($sql), "hproc");
  }

  public static function getHist($id) {
    $sql = "SELECT " . JDataHist::SQL_FIELDS . " FROM data_hists WHERE data_hist_id=" . $id;
    $hm = JDataHist::fromRow(fetch($sql));
    if ($hm != null) {
      LoginDao::authenticateDataHist($id);
    }
    return $hm;
  }
}
?>