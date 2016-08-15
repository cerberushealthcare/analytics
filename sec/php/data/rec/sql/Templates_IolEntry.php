<?php
require_once 'php/c/template-entry/TemplateEntry.php';
require_once 'php/data/rec/sql/_IpcRec.php';
//
/**
 * Templates_IolEntry
 * IOL (lab panel) template entry
 * @author Warren Hornsby
 */
class Templates_IolEntry {
  //
  /**
   * @return array(pid=>'Name of Lab',..)
   */
  static function getIols() {
    $map = IolPar::fetchNameMap();
    return $map;
  }
  /**
   * @param int $pid
   * @return IolEntry
   */
  static function getEntry($pid) {
    $entry = IolEntry::fetch($pid);
    return $entry;
  }
}
//
class IolEntry extends Rec {
  //
  public $type;
  public /*Ipc*/ $Ipc;
  public /*IolPar*/ $Par;
  public $dateQid;
  //
  const TYPE_FORM = 1;
  const TYPE_PAR = 2;
  //
  static function fetch($pid) {
    $par = IolPar::fetch($pid);
    return static::from($par);
  }
  static function from($par) {
    $q = $par->getProcOut_setDate();
    $q->desc = 'Date';
    $ipc = IolIpc::fetchAppLevel($q->OutData->args[1]);
    if ($par->hasProcOut_setPar())
      return static::asPar($par, $ipc, $q);
    else
      return static::asForm($par, $ipc, $q);
  }
  static function asPar($par, $ipc, $q) {
    $me = new static();
    $me->type = static::TYPE_PAR;
    $me->Ipc = $ipc;
    $me->Par = $par;
    $me->dateQid = $q->questionId;
    return $me; 
  }
  static function asForm($par, $ipc, $q) {
    $me = new static();
    $me->type = static::TYPE_FORM;
    $me->Ipc = IolIpc::fetchAppLevel($ipc);
    $me->Par = IolPar::extractResultValues($par);
    $me->dateQid = $q->questionId;
    return $me; 
  }
}
class IolPar extends TPar {
  //
  const SECTION_ID = 22;
  //
  public function load() {
    $this->Questions = IolQuestion::fetchAll($this->parId);
  }
  public function getProcOut_setDate() {
    return IolQuestion::getProcOut_setDate($this->Questions);
  }
  public function hasProcOut_setPar() {
    return IolQuestion::getProcOut_setPar($this->Questions) != null;
  }
  //
  static function fetchNameMap() {
    $recs = static::fetchMap();
    foreach ($recs as &$rec)
      $rec = $rec->desc;
    return $recs;
  }
  static function fetchMap() {
    $c = new static();
    $c->sectionId = self::SECTION_ID;
    $c->current = true;
    $c->injectOnly = CriteriaValue::notEquals('1');
    $c->Questions = IolQuestion::asJoin_requiresOutData();
    return static::fetchMapBy($c);
  }
  static function extractResultValues($par) {
    $par->Questions = IolQuestion::extractResultValues($par->Questions);
    return $par;
  }
}
class IolQuestion extends TQuestion implements ReadOnly {
  //
  public function load() {
    parent::load();
    $this->OutData = OutData::from($this->outData);
  }
  public function isProcOut() {
    return $this->OutData && $this->OutData->table == 'proc'; 
  }
  public function isProcOut_setDate() {
    return $this->isProcOut() && $this->OutData->method == 'setExactDate'; 
  }
  public function isProcOut_setPar() {
    return $this->isProcOut() && $this->OutData->method == 'setPar'; 
  }
  public function isResultOut() {
    return $this->OutData && $this->OutData->table == 'result';
  } 
  public function setIpc($ipc) {
    $this->ipc = $ipc;
    $this->Ipc = IolIpc::fetchAppLevel($ipc);
    $this->desc = $this->Ipc->name;
    return $this;
  }
  //
  static function asJoin_requiresOutData() {
    $c = new static();
    $c->outData = CriteriaValue::isNotNull();
    return CriteriaJoin::requiresCountGreaterThan($c);
  }
  static function getProcOut_setDate($recs) {
    foreach ($recs as $rec) {
      if ($rec->isProcOut_setDate()) 
        return $rec;
    }
  }
  static function getProcOut_setPar($recs) {
    foreach ($recs as $rec) {
      if ($rec->isProcOut_setPar()) 
        return $rec;
    }
  }
  static function extractResultValues($recs) {
    $mes = array();
    foreach ($recs as &$rec) {
      if ($rec->isProcOut_setDate()) 
        $mes[] = $rec;
      else if ($rec->isResultOut()) 
        $mes[] = $rec->setIpc(end($rec->OutData->args));
    }
    return $mes;
  }
}
class OutData {
  //
  public $table;
  public $method;
  public $args;
  //
  static function from($s) {
    if (! empty($s)) {
      $me = new static();
      $arr = preg_split('/\\(|\\)/', $s);
      $tm = explode(':', current($arr));
      $me->table = $tm[0];
      $me->method = $tm[1];
      $me->args = explode(',', next($arr));
      return $me;
    }
  }
}
class IolIpc extends IpcRec {
  //
  public $ipc;
  public $userGroupId;
  public $name;
  public $desc;
}