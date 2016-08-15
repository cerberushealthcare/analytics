<?php
require_once 'php/data/rec/sql/_HdataRec.php';
require_once 'SqlOutput.php';
//
abstract class Sql_Hdc extends SqlRec implements AutoEncrypt {
  //
  public function getOutputSql() {
    return null;
  }
  abstract public function setHdata();
  //
  static function fetchSet($ugid, $startPk = null, $limit = 5000) {
    $c = static::asSetCriteria($ugid);
    if ($startPk)
      $c->setPkValue(CriteriaValue::greaterThanOrEqualsNumeric($startPk));
    $set = SqlSet::fetch($c, $limit);
    static::setHdatas($set->recs);
    return $set;
  }
  static function asSetCriteria($ugid) {
    $c = new static();
    $c->userGroupId = $ugid;
    return $c;
  }
  static function setHdatas($recs) {
    foreach ($recs as &$rec)
      $rec->setHdata();
  }
}
abstract class Face_Hdc extends Sql_Hdc {
  //
  public function getEncryptedFids() {
    return array('date');
  }
  static function asSetCriteria($ugid) {
    $c = parent::asSetCriteria($ugid);
    $c->sessionId = CriteriaValue::isNull();
    return $c;
  }
}
class Vitals_Hdc extends Face_Hdc {
  //
  public $dataVitalsId;
  public $userGroupId;
  public $date;
  //
  public function getSqlTable() {
    return 'data_vitals';
  }
  public function setHdata() {
    $this->Hd_Date = Hdata_VitalsDate::from($this);
  }
}
class Med_Hdc extends Face_Hdc {
  //
  public $dataMedId;
  public $userGroupId;
  public $date;
  //
  public function getSqlTable() {
    return 'data_meds';
  }
  public function setHdata() {
    $this->Hd_Date = Hdata_MedDate::from($this);
  }
  //
  static function asSetCriteria($ugid) {
    $c = parent::asSetCriteria($ugid);
    $c->sessionId = CriteriaValues::_or(CriteriaValue::isNull(), CriteriaValue::equalsNumeric(0));  // SID=0 are New Crop history records
    return $c;
  }
}
class Diagnosis_Hdc extends Face_Hdc {
  //
  public $dataDiagnosesId;
  public $userGroupId;
  public $date;
  public $dateClosed;
  //
  public function getSqlTable() {
    return 'data_diagnoses';
  }
  public function getEncryptedFids() {
    return array('date','dateClosed');
  }
  public function setHdata() {
    $this->Hd_Date = Hdata_DiagnosisDate::from($this);
    $this->Hd_DateClosed = Hdata_DiagnosisDateClosed::from($this);
  }
}
class Immun_Hdc extends Face_Hdc {
  //
  public $dataImmunId;
  public $userGroupId;
  public $dateGiven;
  //
  public function getSqlTable() {
    return 'data_immuns';
  }
  public function getEncryptedFids() {
    return array('dateGiven');
  }
  public function setHdata() {
    $this->Hd_Date = Hdata_ImmunDate::from($this);
  }
}
class Session_Hdc extends Sql_Hdc {
  //
  public $sessionId;
  public $userGroupId;
  public $dateService;
  //
  public function getSqlTable() {
    return 'sessions';
  }
  public function getEncryptedFids() {
    return array('dateService');
  }
  public function setHdata() {
    $this->Hd_Date = Hdata_SessionDos::from($this);
  }
}
class Sched_Hdc extends Sql_Hdc {
  //
  public $schedId;
  public $userGroupId;
  public $date;
  //
  public function getSqlTable() {
    return 'scheds';
  }
  public function getEncryptedFids() {
    return array('date');
  }
  public function setHdata() {
    $this->Hd_Date = Hdata_SchedDate::from($this);
  }
}
class Track_Hdc extends Sql_Hdc {
  //
  public $trackItemId;
  public $userGroupId;
  public $dueDate;
  //
  public function getSqlTable() {
    return 'track_items';
  }
  public function getEncryptedFids() {
    return array('dueDate');
  }
  public function setHdata() {
    $this->Hd_Date = Hdata_TrackDueDate::from($this);
  }
}
