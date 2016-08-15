<?php
require_once 'php/data/rec/sql/_ClientRec.php';
require_once 'php/data/rec/sql/_HdataRec.php';
require_once 'SqlOutput.php';
//
class Client_Hdc extends ClientRec {
  //
  public $clientId;
  public $userGroupId;
  public $uid;
  public $lastName;
  public $firstName;
  public $birth;
  public $huid;
  public /*Hd_CDob_Hdc*/ $Hd_Dob;
  public /*Hd_CName_Hdc*/ $Hd_Name;
  //
  public function getOutputSql() {
    return null;
  }
  public function setHdata() {
    $this->Hd_Dob = Hd_CDob_Hdc::from($this);
    $this->Hd_Name = Hd_CName_Hdc::from($this);
  }
  //
  static function fetchSet($ugid, $startPk = null, $limit = 5000) {
    $c = new static();
    $c->userGroupId = $ugid;
    if ($startPk)
      $c->setPkValue(CriteriaValue::greaterThanOrEqualsNumeric($startPk));
    $set = SqlSet::fetch($c, $limit);
    static::setHdatas($set->recs);
    return $set;
  }
  static function setHdatas($recs) {
    foreach ($recs as &$rec)
      $rec->setHdata();
  }
}
class Hd_CDob_Hdc extends Hdata_ClientDob  {
  //
}
class Hd_CName_Hdc extends Hdata_ClientName {
  //
}
//
class Proc_Hdc extends SqlRec implements AutoEncrypt {
  //
  public $procId;
  public $userGroupId;
  public $date;
  public /*Hd_PDate_Hdc*/ $Hd_Date;
  //
  public function getSqlTable() {
    return 'procedures';
  }
  public function getEncryptedFids() {
    return array('date');
  }
  public function getOutputSql() {
    return null;
  }
  public function setHdata() {
    $this->Hd_Date = Hd_PDate_Hdc::from($this);
  }
  //
  static function fetchSet($ugid, $startPk = null, $limit = 10000) {
    $c = new static();
    $c->userGroupId = $ugid;
    if ($startPk)
      $c->setPkValue(CriteriaValue::greaterThanOrEqualsNumeric($startPk));
    $set = SqlSet::fetch($c, $limit);
    static::setHdatas($set->recs);
    return $set;
  }
  static function setHdatas($recs) {
    foreach ($recs as &$rec)
      $rec->setHdata();
  }
}
class Hd_PDate_Hdc extends Hdata_ProcDate {
  //
}
