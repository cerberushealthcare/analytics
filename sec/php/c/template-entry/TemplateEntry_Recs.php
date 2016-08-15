<?php
require_once 'php/data/rec/sql/_TemplateRecs.php';
require_once 'TemplateEntry_TestAction.php';
//
class TTemplate extends TemplateRec implements ReadOnly {
  //
  public $templateId;
	public $uid;
	public $name;
	public $public;
	public $userGroupId;
  //
  static function fetchAll($ugid) {
    $c = new static();
    $c->public = 1;
    $recs1 = static::fetchAllBy($c);
    if ($ugid == 1)
      return $recs1;
    $c = new static();
    $c->public = 0;
    $c->userGroupId = $ugid;
    $recs2 = static::fetchAllBy($c);
    return array_merge($recs1, $recs2);
  }
}
class TSection extends SectionRec implements ReadOnly {
  //
	public $sectionId;
	public $templateId;
	public $uid;
	public $name;
	public $desc;
	public $sortOrder;
	public $title;
}
//
class TPar extends ParRec implements ReadOnly {
  //
  public $parId;
	public $sectionId;
	public $uid;
	public $major;
	public $sortOrder;
	public $desc;
	public $noBreak;
	public $injectOnly;
	public $dateEffective;
	public $current;
  //
  public /*TSection*/ $Section;
  public /*TQuestion[]*/ $Questions;
  //
  public function toJsonObject(&$o) {
    if (isset($this->Section)) {
      $o->suid = $this->Section->uid;
      $o->tid = $this->Section->templateId;
    }
    $o->sid = $this->sectionId;
    $o->id = intval($this->parId);
    $o->remove('parId', 'sectionId', 'dateEffective', 'current', 'Section');
    $o->bool('injectOnly', 'noBreak', 'major');
  }
  protected function load() {
    $this->Questions = TQuestion::fetchAll($this->parId);
  }
  //
  static function fetch($pid) {
    $c = new static();
    $c->parId = $pid;
    $c->Section = CriteriaJoin::requires(new TSection());
    $me = static::fetchOneBy($c);
    $me->load();
    return $me;
  }
  static function fetchBy($sid, $uid) {
    $c = new static();
    $c->uid = $uid;
    $c->sectionId = $sid;
    $c->current = 1;
    $c->Section = CriteriaJoin::requires(new TSection());
    $me = static::fetchOneBy($c);
    $me->load();
    return $me;
  }
}
class TPars {
  //
  static function /*TPar[]*/fetchAll($pid, $userId) {
    $recs = array();
    $rec = TPar::fetch($pid);  //, $userId);
    static::append($recs, $rec, $rec->Section->uid);
    return array_values($recs);
  }
  protected static function append(&$recs, $par, $suid) {
    if (! isset($recs[$par->parId])) {
      $recs[$par->parId] = $par;
      $pars = static::fetchInjectsFor($par, $suid);
      foreach ($pars as $par)
        static::append($recs, $par, $suid);
    }
  }
  protected function fetchInjectsFor($par, $suid) {
    $uids = TQuestion::getAllInjectUids($par->Questions, $suid);
    $pars = array();
    foreach ($uids as $uid)
      $pars[] = TPar::fetchBy($par->sectionId, $uid);
    return $pars;
  }
}
//
class TQuestion extends QuestionRec implements ReadOnly {
  //
  public $questionId;
  public $parId;
  public $uid;
  public $desc;
  public $bt;
  public $at;
  public $btms;
  public $atms;
  public $btmu;
  public $atmu;
  public $listType;
  public $noBreak;
  public $test;
  public $defix;
  public $mix;
  public $mcap;
  public $sortOrder;
  public $actions;
  public $syncId;
  public $outData;
  public $dsyncId;
  //
  public /*TOption[]*/$Opts;
  public /*Test[]*/$Tests;
  public /*Action[]*/$Actions;
  //
  public function load() {
    $this->Opts = TOption::fetchAll($this->questionId);
    $this->Tests = Test::from($this->test);
    $this->Actions = Action::from($this->actions);
  }
  public function isTypeStandard() {
    return $this->getType() == static::TYPE_STANDARD;
  }
  public function needsOther($loix) {
    if ($this->isTypeStandard())
      return $loix > - 1;
  }
  public function _toJsonObject() {
    $this->loix = isset($this->Opts) ? count($this->Opts) - 1 : -1;
    if ($this->needsOther($this->loix))
      $this->other = 1;
    return parent::_toJsonObject();
  }
  public function toJsonObject(&$o) {
    $o->type = $this->getType();
    $o->id = $this->questionId;
    $o->lt = $this->listType;
    $o->brk = $this->noBreak;
    $o->mix = intval($this->mix);
    $o->defix = intval($this->defix);
    $o->dsync = $this->dsyncId;
    $o->remove('noBreak', 'parId', 'questionId', 'sortOrder', 'test', 'actions', 'listType', 'dsyncId');
    $o->int('lt', 'brk');
  }
  public function getInjectUids($suid) {
    $uids = array();
    if ($this->Actions) {
      foreach ($this->Actions as $action) {
        if ($action->isInject() && $action->isConditionless()) {
          $a = explode('.', $action->args[0]);
          if ($a[0] == $suid)
            $uids[] = $a[1];
        }
      }
    }
    return $uids;
  }
  //
  static function asJoin() {
    $c = new static();
    $c->Opts = TOption::asJoin();
    return CriteriaJoin::optionalAsArray($c);
  }
  static function fetchAll($pid) {
    $c = new static();
    $c->parId = $pid;
    $recs = static::fetchAllBy($c, new RecSort('sortOrder'));
    return static::loadAll($recs);
  }
  static function getAllInjectUids($recs, $suid) {
    $uids = array();
    foreach ($recs as $q)
      static::appendInjectUids($uids, $q, $suid);
    return $uids;
  }
  protected static function appendInjectUids(&$uids, $q, $suid) {
    $puids = $q->getInjectUids($suid);
    foreach ($puids as $uid)
      $uids[] = $uid;
  }
  protected static function loadAll(&$recs) {
    foreach ($recs as $rec)
      $rec->load();
    return $recs;
  }
}
//
class TOption extends OptionRec implements ReadOnly {
  //
  public $optionId;
  public $questionId;
  public $uid;
  public $desc;
  public $text;
  public $shape;
  public $coords;
  public $sortOrder;
  public $syncId;
  public $cptCode;
  public $trackCat;
  //
  public function toJsonObject(&$o) {
    if ($o->desc == $o->uid)
      unset($o->desc);
    $o->cpt = $this->cptCode;
    $o->tcat = $this->trackCat;
    $o->int('cpt', 'tcat');
    $o->remove('optionId', 'questionId', 'sortOrder', 'trackCat', 'cptCode');
  }
  //
  static function asJoin() {
    $c = new static();
    return CriteriaJoin::optionalAsArray($c);
  }
  static function fetchAll($qid) {
    $c = new static();
    $c->questionId = $qid;
    return static::fetchAllBy($c, new RecSort('sortOrder'));
  }
  static function asOther() {
    $me = new static();
    $me->uid = 'other';
    $me->desc = 'other';
    $me->text = 'other';
    return $me;
  }
}
//
class TOther extends SqlRec implements CompositePk, NoAudit {
  //
  public $userId;
  public $templateId;
  public $sectionId;
  public $puid;
  public $quid;
  public $values;
  //
  public function getSqlTable() {
    return 'template_others';
  }
  public function getPkFieldCount() {
    return 5;
  }
  public function getValuesAsArray() {
    return explode('`', $this->values);
  }
  //
  static function fetchValueMap($userId, $tid, $sid, $puid) {  // array('quid'=>array('custom1','custom2'),..)
    $recs = static::fetchAll($userId, $tid, $sid, $puid);
    $map = array();
    foreach ($recs as $rec)
      $map[$rec->quid] = $rec->getValuesAsArray();
    return $map;
  }
  static function fetchValueArray($userId, $tid, $sid, $puid, $quid) {  // array('custom1','custom2',..)
    $rec = static::fetch($userId, $tid, $sid, $puid, $quid);
    if ($rec)
      return $rec->getValuesAsArray();
  }
  static function saveFromUi($userId, $o) {
    $me = new static();
    $me->userId = $userId;
    $me->templateId = $o->tid;
    $me->sectionId = $o->sid;
    $me->puid = $o->puid;
    $me->quid = $o->quid;
    $me->values = $o->values;
    if ($me->values == '')
      static::delete($me);
    else
      $me->save();
  }
  //
  static function fetchAll($userId, $tid, $sid, $puid) {
    $c = new static($userId, $tid, $sid, $puid);
    return static::fetchAllBy($c);
  }
  static function fetch($userId, $tid, $sid, $puid, $quid) {
    $c = new static($userId, $tid, $sid, $puid, $quid);
    return static::fetchOneBy($c);
  }
}
