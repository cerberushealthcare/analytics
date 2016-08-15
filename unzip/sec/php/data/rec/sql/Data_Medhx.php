<?php
//
class Data_Medhx {
  /**
   * @param int $cid
   * @return array(Medhx_Rec,..)
   */
  function getAll($cid) {
    $summary = Medhx_Summary::fetch($cid);
    if ($summary)
      return $summary->fetchRecs();
  }
  /**
   * @param stdClass $obj Medhx_Rec
   * @return Medhx_Rec
   */
  function save($obj) {
    global $login;
    $summary = Medhx_Summary::fetch($cid);
    if ($summary == null)
      $summary = Medhx_Summary::asNew($cid);
    $rec = new MedHx_Rec($obj);
    $rec->save($login->userGroupId);
    $summary->save_add($login->userGroupId, $rec->name);
    return $rec;
  }
  /**
   * @param string $name 'Colonoscopy'
   */
  function remove($name) {
    global $login;
    $summary = Medhx_Summary::fetch($cid);
    if ($summary) 
      $summary->save_remove($login->userGroupId, $name);
  }
}
/**
 * Rec Medhx_Summary
 */
class Medhx_Summary extends Rec {
  //
  public $cid;
  public $names;  // array('Diagnosis',..)
  //
  public function has($name) {
    return in_array($name, $this->names);
  }
  public function save($ugid) {
    $rec = self::fetch($this->cid);
    if ($rec == null)
      $rec = DataSync_Medhx_Summary::asNew($ugid, $cid);
    $rec->setValueArray($this->names);
    $rec->save();
  }
  public function save_add($ugid, $name) {
    if (! $this->has($name)) {
      $this->names[] = $name;
      $this->save($ugid);
    }
  }
  public function save_remove($ugid, $name) {
    $i = array_search($name, $this->names);
    if ($i !== false) { 
      unset($this->names[$i]);
      $this->save($ugid);
    }
  }
  public function fetchRecs() {
    $recs = Medhx_Rec::fetchAll($this->cid);
    $mine = array();
    foreach ($this->names as $name) {
      $rec = geta($recs, $name);
      if ($rec == null)
        $rec = Medhx_Rec::asEmpty($this->cid, $name);
      $mine[] = $rec;
    }
    return $mine;
  }
  //
  static function fetch($cid) {
    $rec = DataSync_Medhx_Summary::fetch($cid);
    return self::fromRec($rec);
  }
  static function asNew($cid) {
    $me = new self();
    $me->cid = $cid;
    $me->names = array();
    return $me;
  }
  //
  static function fromRec($rec) {
    if ($rec) {  
      $me = new self();
      $me->cid = $rec->clientId;
      $me->names = $rec->getValueArray();
      return $me;
    } 
  }
}
/**
 * Rec Medhx_Rec
 */
class Medhx_Rec extends Rec {
  //
  public $cid;
  public $name;
  public $type;
  public $treatment;
  public $comment;
  //
  static $FIDS = array('type', 'treatment', 'comment');
  //
  public function save($ugid) {
    $recs = DataSync_Medhx_Rec::fetchAll($this->cid, $this->name);
    if ($recs == null)
      $recs = DataSync_Medhx_Rec::asNew($ugid, $this->cid, $this->name, self::$FIDS);
    foreach ($recs as $rec) {
      $fid = $rec->getFid();
      $rec->setValueElement($this->$fid);
    }
    SqlRec::saveAll($recs);
  }
  //
  static function fetchAll($cid) {
    $recs = DataSync_Medhx_Rec::fetchAll($cid);
    return static::fromRecs($recs);
  }
  static function fromRecs($recs) {
    $groups = array();
    foreach ($recs as $rec) {
      $name = $rec->getName();
      if (isset($groups[$name]))
        $groups[$name][] = $rec;
      else
        $groups[$name] = array($rec);
    }
    return self::fromGroups($groups);
  }
  static function fromGroups($groups) {
    $mes = array();
    foreach ($groups as $group) {
      $me = self::fromGroup($group);
      $mes[$me->name] = $me;
    }
    return $mes;
  }
  static function fromGroup($group) {
    $me = new self();
    $me->cid = current($group)->clientId; 
    $me->name = current($group)->getName();
    foreach ($group as $rec) {
      $fid = $rec->getFid();
      $me->$fid = $rec->getValueElement(); 
    }
    return $me;
  }
  static function asEmpty($cid, $name) {
    $me = new static();
    $me->cid = $cid;
    $me->name = $name;
    return $me;
  }
}
class DataSync extends SqlRec {
  //
  public $dataSyncId;
  public $userGroupId;
  public $clientId;
  public $dsyncId;
  public $dsync;
  public $dateSort;
  public $sessionId;
  public $value;
  public $active;
  public $dateUpdated;
  //
  public function getSqlTable() {
    return 'data_syncs';
  }
  public function getValueArray() {
    return jsondecode($this->value);
  }
  public function getValueElement() {
    return current($this->getValueArray());
  }
  public function setValueArray($object) {
    $this->value = jsonencode($object);
  }
  public function setValueElement($string) {
    $this->setValueArray(array($string));
  }
  //
  static function asNew($ugid, $cid, $dsync) {
    $me = new static();
    $me->userGroupId = $ugid;
    $me->clientId = $cid;
    $me->dsyncId = self::DSYNC_ID;
    $me->dsync = self::DSYNC_ID;
    $me->dateSort = nowNoQuotes();
    $me->active = true;
    return $me;
  }
}
class DataSync_Medhx_Summary extends DataSync {
  //
  const DSYNC_ID = 'pmhx';
  //
  static function fetch($cid) {
    $c = self::asCriteria($cid);
    $rec = self::fetchOneBy($c);
    return $rec;
  } 
  static function asCriteria($cid) {
    $c = new self();
    $c->clientId = $cid;
    $c->dsyncId = self::DSYNC_ID;
    return $c;
  }
  static function asNew($ugid, $cid) {
    $me = parent::asNew($ugid, $cid, self::DSYNC_ID);
    return $me;
  }
}
class DataSync_Medhx_Rec extends DataSync {
  //
  const DSYNC_ID = 'pmhx.';
  //
  public function getName() {
    $a = $this->splitDsync();
    return $a[1];
  }
  public function getFid() {
    $a = $this->splitDsync();
    return $a[2];
  }
  //
  static function fetchAll($cid, $name = null) {
    $c = self::asCriteria($cid, $name);
    $recs = self::fetchAllBy($c, new RecSort('dsyncId'));
    return $recs;
  }
  static function asCriteria($cid, $name = null) {
    $c = new self();
    $c->clientId = $cid;
    if ($name)
      $c->dsyncId = CriteriaValue::startsWith(self::DSYNC_ID . $name . '.');
    else
      $c->dsyncId = CriteriaValue::startsWith(self::DSYNC_ID);
    return $c;
  }
  static function asNewGroup($ugid, $cid, $name, $fids) {
    $mes = array();
    foreach ($fids as $fid)  
      $mes[] = self::asNew($ugid, $cid, $name, $fid);
    return $mes;
  }
  static function asNew($ugid, $cid, $name, $fid) {
    $me = parent::asNew($ugid, $cid, self::makeDsync($name, $fid));
    return $me;
  }
  //
  private function splitDsync() {
    if (! isset($this->_split)) 
      $this->_split = explode('.', $this->dsync);
    return $this->_split;
  }
  private static function makeDsync($name, $fid) {
    return self::DSYNC_ID . $name . '.' . $fid;
  }
}