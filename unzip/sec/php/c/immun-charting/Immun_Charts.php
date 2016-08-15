<?php
require_once 'Immun_VCats.php';
require_once 'php/data/rec/sql/_ClientRec.php';
require_once 'php/data/rec/sql/_AddressRec.php';
require_once 'php/data/rec/sql/_ImmunRec.php';
//
class ImmunChart {
  //
  public /*PStub_C*/$Client;
  public /*Immun_C[id]*/$Immuns;
  public /*Row_C[vcat]*/$Rows;
  //
  static function fetch($cid, $asScheduled = false) {
    $me = new static();
    $me->Client = PStub_C::fetch($cid);
    $me->Immuns = Immun_C::fetchAll($cid);
    $me->Rows = $asScheduled ? Row_C::scheduled() : Row_C::all();
    $me->setRows();
    return $me;
  }
  static function fetch_asScheduled($cid) {
    return static::fetch($cid, true);
  }
  //
  public function /*Immun_C*/get($vcat, $index) {
    $row = $this->Rows[$vcat];
    return $row->get($index);
  }
  //
  protected function setRows() {
    foreach ($this->Immuns as $imm) {
      if ($imm->wasGiven()) {
        $this->setRowsFrom($imm);
      }
    }
  }
  protected function setRowsFrom($imm) {
    $vcats = $imm->getVCats()->array;
    foreach ($vcats as $vcat) {
      $row = geta($this->Rows, $vcat);
      if ($row)
        $row->add($imm);
    }
  }
}
class Row_C {
  //
  public $cat;
  public /*Immun_C[]*/$Immuns;
  //
  static function all() {
    return static::from(VCats::getAll());
  }
  static function scheduled() {
    return static::from(VCats::getScheduled());
  }
  protected static function from($cats) {
    $us = array();
    foreach ($cats as $cat) 
      $us[$cat] = static::one($cat);
    return $us;
  } 
  protected static function one($cat) {
    $me = new static();
    $me->cat = $cat;
    $me->Immuns = array();
    return $me;
  }
  //
  public function get($index/*negative to get from end of array, e.g. -1 = last*/) {
    if ($index < 0) {
      $index = count($this->Immuns) + $index;
    }
    if ($index >= 0 && $index < count($this->Immuns))
      return $this->Immuns[$index];
  }
  public function add($imm) {
    $this->Immuns[] = $imm;
  }
}
class PStub_C extends PatientStub {
  //
  /*
  public $Address_Father;
  public $Address_Mother;
  public $age;  e.g. '18', '0y 9m 6d'
  public $ageYears;
   */
  //
  static function fetch($cid) {
    $c = new static($cid);
    $c->Address_Mother = ClientAddress::asJoinMother();
    $c->Address_Father = ClientAddress::asJoinFather();
    $c->Address_Home = ClientAddress::asJoinHome();
    return static::fetchOneBy($c);
  }
  //
  public function /*string*/getParent() {
    if (isset($this->Address_Mother) && isset($this->Address_Father))
      return $this->Address_Father->name . ', ' . $this->Address_Mother->name;
    if (isset($this->Address_Mother))
      return $this->Address_Mother->name;
    if (isset($this->Address_Father)) 
      return $this->Address_Father->name;
  }
  public function /*string*/getHomeAddress() {
    if (isset($this->Address_Home))
      return $this->Address_Home->format(false);
    if (isset($this->Address_Mother))
      return $this->Address_Mother->format(false);
    if (isset($this->Address_Father)) 
      return $this->Address_Father->format(false);
  }
}
class Immun_C extends ImmunRec implements ReadOnly {
  //
  public $dataImmunId;
  public $userGroupId;
  public $clientId;
  public $dateGiven;
  public $name;
  public $tradeName;
  public $lot;
  public $dose;
  public $adminBy;
  public $comment;
  public $status;
  //
  static function fetchAll($cid) {
    $c = new static();
    $c->clientId = $cid;
    return SqlRec::fetchAllBy($c, new RecSort('dateGiven'));
  }
  //
  public function getJsonFilters() {
    return array(
      'dateGiven' => JsonFilter::reportDate());
  }
  public function getVCats() {
    return VCats::from($this->getVTypes());
  }
  public function getVTypes() {
    return VTypes::from($this);
  }
  public function nameIs($text) {
    return $this->name == $text;
  }
  public function nameHas($text) {
    return stripos($this->name, $text) !== false;
  }
  public function tradeNameHas($text) {
    return stripos($this->tradeName, $text) !== false;
  }
  public function isAdult() {
    return $this->tradeNameHas('adult');
  }
} 
