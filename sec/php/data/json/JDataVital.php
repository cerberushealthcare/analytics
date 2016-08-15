<?php
require_once 'php/data/json/_util.php';

/**
 * Vitals
 * Facesheet Data Record
 *
 * Organization:
 *   ugid, cid,..
 *     sid>0, date=DOS, index=oix  // built from closed note
 *     sid=NULL, agent             // facesheet summary record
 */
class JDataVital {
  //
  public $id;
  public $userGroupId;
  public $clientId;
  public $sessionId;
  public $date;     // '2009-05-20'
  public $pulse;
  public $resp;
  public $bpSystolic;
  public $bpDiastolic;
  public $bpLoc;
  public $temp;
  public $tempRoute;
  public $wtUnits;
  public $height;
  public $hc;
  public $hcUnits;
  public $wc;
  public $wcUnits;
  public $o2Sat;
  public $o2SatOn;
  public $bmi;
  public $htUnits;
  public $updated;
  //
  // Date helpers
  public $time;
  public $dateText; // 'Today 1:30PM'
  public $dateCal;  // '02-Jun-2009'
  //
  // Metric equivalents
  public $htcm;  
  public $wtkg;
  //
  // Chron age assigned after construction, for graphing
  public $cagey;
  public $cagem;  
  //
  const SQL_FIELDS = 'data_vitals_id, user_group_id, client_id, session_id, date, pulse, resp, bp_systolic, bp_diastolic, bp_loc, temp, temp_route, wt, wt_units, height, ht_units, hc, hc_units, wc, wc_units, o2_sat, o2_sat_on, bmi, date_updated';
  //
  public static $propsToQuid = array(
    'pulse'        => 'vitals.pulse',
    'resp'         => 'vitals.rr', 
    'bpSystolic'   => 'vitals.sbp', 
    'bpDiastolic'  => 'vitals.dbp', 
    'bpLoc'        => 'vitals.loc', 
    'temp'         => 'vitals.temp', 
    'tempRoute'    => 'vitals.tempRoute', 
  	'wt'           => 'vitals.#Weight', 
    'wtUnits'      => 'vitals.lbsKgs', 
    'height'       => 'vitals.#Height', 
    'htUnits'      => 'vitals.unitsHt', 
    'hc'           => 'vitals.#hc', 
    'hcUnits'      => 'vitals.inCm', 
    'wc'           => 'vitals.#wc', 
    'wcUnits'      => 'vitals.inCmWC', 
    'o2Sat'        => 'vitals.#O2Sat', 
    'o2SatOn'      => 'vitals.O2SatOn', 
    'bmi'          => 'vitals.bmi' 
    );

  public function __construct($id, $userGroupId, $clientId, $sessionId, $date, $pulse, $resp, $bpSystolic, $bpDiastolic, $bpLoc, $temp, $tempRoute, $wt, $wtUnits, $height, $htUnits, $hc, $hcUnits, $wc, $wcUnits, $o2Sat, $o2SatOn, $bmi, $updated) {
    $this->id = $id;
    $this->userGroupId = $userGroupId;
    $this->clientId = $clientId;
    $this->sessionId = $sessionId;
    $this->date = dateToString($date);
    $this->dateText = formatInformalDate($date, true);
    $time = formatTime($date);
    if ($time)
      $this->time = $time; 
      $this->dateText .= " $time";          
    $this->dateCal = formatDate($date);
    $this->pulse = ($pulse == 'Reviewed') ? null : $pulse;
    $this->resp = $resp;
    $this->bpSystolic = $bpSystolic;
    $this->bpDiastolic = $bpDiastolic;
    $this->bpLoc = $bpLoc;
    $this->temp = $temp;
    $this->tempRoute = $tempRoute;
    $this->wt = $wt;
    $this->wtUnits = $wtUnits;
    $this->height = $height;
    $this->htUnits = $htUnits;
    if ($this->height) {
      $this->htcm = $this->height * 2.54;  // todo standardize
    }
    if ($this->wt) {
      $this->wtkg = $this->wt * 0.45359;
    }
    $this->hc = $hc;
    $this->hcUnits = $hcUnits;
    $this->wc = $wc;
    $this->wcUnits = $wcUnits;
    $this->o2Sat = $o2Sat;
    $this->o2SatOn = $o2SatOn;
    $this->bmi = $bmi;
    $this->updated = $updated;
  }
  public function getBp() {
    return $this->bpSystolic . $this->bpDiastolic . ' ' . $this->bpLoc;
  }
  public function getO2() {
    $s = $this->o2Sat;
    if ($s != null) {
      $s .= ' - ' . $this->o2SatOn;
    }
    return $s;
  }
  public function getAllValues() {
    $a = array();
    if ($this->pulse != null) {
      $a[] = 'Pulse: ' . $this->pulse;
    }
    if ($this->resp != null) {
      $a[] = 'Resp: ' . $this->resp;
    }
    if ($this->bpSystolic != null) {
      $a[] = 'BP: ' . $this->getBp();
    }
    if ($this->temp != null) {
      $a[] = 'Temp: ' . $this->temp;
    }
    if ($this->wt != null) {
      $a[] = 'Wt: ' . $this->wt;
    }
    if ($this->height != null) {
      $a[] = 'Height: ' . $this->height;
    }
    if ($this->hc != null) {
      $a[] = 'HC: ' . $this->hc;
    }
    if ($this->wc != null) {
      $a[] = 'WC: ' . $this->wc;
    }
    if ($this->o2Sat != null) {
      $a[] = 'O2: ' . trim($this->o2Sat . ' ' . $this->o2SatOn);
    }
    if ($this->bmi != null) {
      $a[] = 'BMI: ' . $this->bmi;
    }
    return $a;
  }
  public function out() {
    $out = '';
    $out = nqq($out, 'id', $this->id);
    $out = nqq($out, 'clientId', $this->clientId);
    $out = nqq($out, 'sessionId', $this->sessionId);
    $out = nqq($out, 'date', $this->date);
    $out = nqq($out, 'time', $this->time);
    $out = nqq($out, 'dateText', $this->dateText);
    $out = nqq($out, 'dateCal', $this->dateCal);
    $out = nqq($out, 'cagey', $this->cagey);
    $out = nqq($out, 'cagem', $this->cagem);
    $out = nqq($out, 'pulse', $this->pulse);
    $out = nqq($out, 'resp', $this->resp);
    $out = nqq($out, 'bp', $this->getBp());
    $out = nqq($out, 'bpSystolic', $this->bpSystolic);
    $out = nqq($out, 'bpDiastolic', $this->bpDiastolic);
    $out = nqq($out, 'bpLoc', $this->bpLoc);
    $out = nqq($out, 'temp', $this->temp);
    $out = nqq($out, 'tempRoute', $this->tempRoute);
    $out = nqq($out, 'wt', $this->wt);
    $out = nqq($out, 'wtUnits', $this->wtUnits);
    $out = nqq($out, 'wtkg', $this->wtkg);
    $out = nqq($out, 'height', $this->height);
    $out = nqq($out, 'htcm', $this->htcm);
    $out = nqq($out, 'htUnits', $this->htUnits);
    $out = nqq($out, 'hc', $this->hc);
    $out = nqq($out, 'hcUnits', $this->hcUnits);
    $out = nqq($out, 'wc', $this->wc);
    $out = nqq($out, 'wcUnits', $this->wcUnits);
    $out = nqq($out, 'o2Sat', $this->o2Sat);
    $out = nqq($out, 'o2SatOn', $this->o2SatOn);
    $out = nqq($out, 'o2', $this->getO2());
    $out = nqq($out, 'bmi', $this->bmi);
    $out = nqqas($out, 'all', $this->getAllValues());
    return cb($out);
  }
  public function anyDataSet() {
    return (
    $this->pulse != null ||
    $this->resp != null ||
    $this->bpSystolic != null ||
    $this->temp != null ||
    $this->wt != null ||
    $this->height != null ||
    $this->hc != null ||
    $this->wc != null ||
    $this->o2Sat != null);
  }
  public function copySetData($dto) {
    if ($dto->pulse != null) {
      $this->pulse = $dto->pulse;
    }
    if ($dto->resp != null) {
      $this->resp = $dto->resp;
    }
    if ($dto->bpSystolic != null) {
      $this->bpSystolic = $dto->bpSystolic;
      $this->bpDiastolic = $dto->bpDiastolic;
      $this->bpLoc = $dto->bpLoc;
    }
    if ($dto->temp != null) {
      $this->temp = $dto->temp;
      $this->tempRoute = $dto->tempRoute;
    }
    if ($dto->wt != null) {
      $this->wt = $dto->wt;
      $this->wtUnits = $dto->wtUnits;
    }
    if ($dto->height != null) {
      $this->height = $dto->height;
      $this->htUnits = $dto->htUnits;
    }
    if ($dto->bmi != null) {
      $this->bmi = $dto->bmi;
    }
    if ($dto->hc != null) {
      $this->hc = $dto->hc;
      $this->hcUnits = $dto->hcUnits;
    }
    if ($dto->wc != null) {
      $this->wc = $dto->wc;
      $this->wcUnits = $dto->wcUnits;
    }
    if ($dto->o2Sat != null) {
      $this->o2Sat = $dto->o2Sat;
      $this->o2SatOn = $dto->o2SatOn;
    }
  }

  // Static functions
  public static function copy($dto) {
    return new JDataVital(
    null,
    $dto->userGroupId,
    $dto->clientId,
    $dto->sessionId,
    $dto->date,
    $dto->pulse,
    $dto->resp,
    $dto->bpSystolic,
    $dto->bpDiastolic,
    $dto->bpLoc,
    $dto->temp,
    $dto->tempRoute,
    $dto->wt,
    $dto->wtUnits,
    $dto->height,
    $dto->htUnits,
    $dto->hc,
    $dto->hcUnits,
    $dto->wc,
    $dto->wcUnits,
    $dto->o2Sat,
    $dto->o2SatOn,
    $dto->bmi,
    $dto->updated
    );
  }
  public static function fromRows($res, $assocBy = null) {
    $vitals = array();
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      $rec = JDataVital::fromRow($row);
      if ($assocBy != null) {
        $vitals[$row[$assocBy]] = $rec;
      } else {
        $vitals[] = $rec;
      }
    }
    return $vitals;
  }
  public static function fromRow($row) {
    if (! $row) return null;
    return new JDataVital(
        $row["data_vitals_id"],
        $row["user_group_id"],
        $row["client_id"],
        $row["session_id"],
        $row["date"],
        $row["pulse"],
        $row["resp"],
        $row["bp_systolic"],
        $row["bp_diastolic"],
        $row["bp_loc"],
        $row["temp"],
        $row["tempRoute"],
        $row["wt"],
        $row["wt_units"],
        $row["height"],
        $row["ht_units"],
        $row["hc"],
        $row["hc_units"],
        $row["wc"],
        $row["wc_units"],
        $row["o2_sat"],
        $row["o2_sat_on"],
        $row["bmi"],
        $row["date_updated"]
        );
  }
  
  public static function save($dto, $audit = true) {
    if ($dto->id != null) {
      if ($dto->time != null) {
        $date = datetimeToString($dto->date . ' ' . $dto->time);
      } else {
        $date = dateToString($dto->date);
      }
      LoginDao::authenticateDataVitals($dto->id);
      $sql = "UPDATE data_vitals SET ";
      $sql .= "date=" . quote($date);
      $sql .= ", pulse=" . quote($dto->pulse);
      $sql .= ", resp=" . quote($dto->resp);
      $sql .= ", bp_systolic=" . quote($dto->bpSystolic);
      $sql .= ", bp_diastolic=" . quote($dto->bpDiastolic);
      $sql .= ", bp_loc=" . quote($dto->bpLoc);
      $sql .= ", temp=" . quote($dto->temp);
      $sql .= ", tempRoute=" . quote($dto->tempRoute);
      $sql .= ", wt=" . quote($dto->wt);
      $sql .= ", wt_units=" . quote($dto->wtUnits);
      $sql .= ", height=" . quote($dto->height);
      $sql .= ", ht_units=" . quote($dto->htUnits);
      $sql .= ", hc=" . quote($dto->hc);
      $sql .= ", hc_units=" . quote($dto->hcUnits);
      $sql .= ", wc=" . quote($dto->wc);
      $sql .= ", wc_units=" . quote($dto->wcUnits);
      $sql .= ", o2_sat=" . quote($dto->o2Sat);
      $sql .= ", o2_sat_on=" . quote($dto->o2SatOn);
      $sql .= ", bmi=" . quote($dto->bmi);
      $sql .= ", date_updated=NULL";
      $sql .= " WHERE data_vitals_id=" . $dto->id;
      query($sql);
      if ($audit) AuditDao::log($dto->clientId, AuditDao::ENTITY_DATA_VITALS, $dto->id, AuditDao::ACTION_UPDATE, null, formatDate($dto->date));
    } else {
      if ($dto->time != null) {
        $date = datetimeToString($dto->date . ' ' . $dto->time);
      } else {
        $date = dateToString($dto->date);
      }
      LoginDao::authenticateClientId($dto->clientId);
      $sql = "INSERT INTO data_vitals VALUE(NULL";
      $sql .= ", " . quote($dto->userGroupId);
      $sql .= ", " . quote($dto->clientId);
      $sql .= ", NULL";  // sessionId
      $sql .= ", " . quote($date);
      $sql .= ", " . quote($dto->pulse);
      $sql .= ", " . quote($dto->resp);
      $sql .= ", " . quote($dto->bpSystolic);
      $sql .= ", " . quote($dto->bpDiastolic);
      $sql .= ", " . quote($dto->bpLoc);
      $sql .= ", " . quote($dto->temp);
      $sql .= ", " . quote($dto->tempRoute);
      $sql .= ", " . quote($dto->wt);
      $sql .= ", " . quote($dto->wtUnits);
      $sql .= ", " . quote($dto->height);
      $sql .= ", " . quote($dto->hc);
      $sql .= ", " . quote($dto->hcUnits);
      $sql .= ", " . quote($dto->wc);
      $sql .= ", " . quote($dto->wcUnits);
      $sql .= ", " . quote($dto->o2Sat);
      $sql .= ", " . quote($dto->o2SatOn);
      $sql .= ", " . quote($dto->bmi);
      $sql .= ", " . quote($dto->htUnits);
      $sql .= ", NULL";  // date_updated
      $sql .= ")";
      $dto->id = insert($sql);
      if ($audit) AuditDao::log($dto->clientId, AuditDao::ENTITY_DATA_VITALS, $dto->id, AuditDao::ACTION_CREATE, null, formatDate($dto->date));
    }
    return $dto;
  }
  public static function delete($dto, $id, $audit = true) {
    query("DELETE FROM data_vitals WHERE data_vitals_id=" . $id);
    if ($audit) AuditDao::log($dto->clientId, AuditDao::ENTITY_DATA_VITALS, $dto->id, AuditDao::ACTION_DELETE);
  }
  public static function getVital($id) {
    $sql = "SELECT " . JDataVital::SQL_FIELDS . " FROM data_vitals WHERE data_vitals_id=" . $id;
    $vital = JDataVital::fromRow(fetch($sql));
    if ($vital != null) {
      LoginDao::authenticateDataVitals($id);
    }
    return $vital;
  }

  // Return collection of distinct (by date) vitals from session history, associated by date
  public static function getSessionVitals($clientId) {
    $sql = "SELECT " . JDataVital::SQL_FIELDS . " FROM (SELECT * FROM data_vitals WHERE client_id=" . $clientId . " AND session_id IS NOT NULL ORDER BY date DESC, date_updated DESC) a GROUP BY date ORDER BY date DESC";
    return JDataVital::fromRows(query($sql), "date");
  }

  // Return collection of non-session vital data records, associated by date
  public static function getFacesheetVitals($clientId) {
    $sql = "SELECT " . JDataVital::SQL_FIELDS . " FROM data_vitals WHERE client_id=" . $clientId . " AND session_id IS NULL ORDER BY date DESC";
    return JDataVital::fromRows(query($sql), "date");
  }
}
?>