<?php
require_once 'php/data/rec/sql/_TemplateRecs.php';
//
/**
 * Templates 
 * @author Warren Hornsby
 */
class Templates {
  //
  static function getPar($pid) {
    $par = TPar::fetch($pid);
    return $par;
  }
}
//
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
  public $inDataTable;
  public $inDataType;
  public $inDataCond;
  //
  public /*TSection*/ $Section;
  public /*TQuestion[]*/ $Questions;
  //
  public function toJsonObject(&$o) {
    // static::sort($o->Questions, new RecSort('sortOrder'));
    $o->suid = $o->Section->uid;
    $o->id = intval($o->parId);
    unset($o->parId);
    unset($o->sectionId);
    unset($o->dateEffective);
    unset($o->current);
    unset($o->inDataTable);
    unset($o->inDataType);
    unset($o->inDataCond);
    unset($o->Section);
  }
  public function load() {
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
    //$c->Questions = TQuestion::asJoin();
    //return static::fetchOneBy($c, 5000);
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
  public $inDataActions;
  public $dsyncId;
  //
  public /*TOption[]*/ $Opts;
  public /*Test[]*/ $Tests;
  public /*Action[]*/ $Actions;
  //
  public function load() {
    $this->Opts = TOption::fetchAll($this->questionId);
  }
  public function isTypeStandard() {
    return $this->getType() == static::TYPE_STANDARD;
  }
  public function needsOther($loix) {
    if ($this->isTypeStandard())
      return $loix > - 1;
  }
  public function toJsonObject(&$o) {
    // static::sort($o->Opts, new RecSort('sortOrder'));
    $o->type = $this->getType();
    $o->loix = isset($o->Opts) ? count($o->Opts) - 1 : -1;
    $o->Tests = Test::from($this->test);
    $o->Actions = Action::from($this->actions);
    $o->brk = intval($o->noBreak);
    $o->id = intval($o->questionId);
    $o->lt = intval($o->listType);
    if ($this->needsOther($o->loix))
      $o->Opts[] = TOption::asOther();    
    unset($o->noBreak);
    unset($o->parId);
    unset($o->questionId);
    unset($o->sortOrder);
    unset($o->inDataActions);
    unset($o->test);
    unset($o->actions);
    unset($o->listType);
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
    //$c->Opts = TOption::asJoin();
    $recs = static::fetchAllBy($c, new RecSort('sortOrder'));
    //return $recs;
    return static::loadAll($recs);
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
    unset($o->optionId);
    unset($o->questionId);
    unset($o->sortOrder);
    $o->rename('cptCode', 'cpt');
    $o->rename('trackCat', 'track');
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
class Test extends Rec {
  //
  public $id;
  public $args;  // string[]
  public $op;
  //
  const ALWAYS = 1;
  const IS_SEL = 2;
  const NOT_SEL = 3;
  const IS_MALE = 4;
  const IS_FEMALE = 5;
  const OLDER_THAN = 6;
  const YOUNGER_THAN = 7;
  const IS_AGE = 8;
  const IS_BIRTHDATE_SET = 9;
  const IS_INJECTED = 11;
  //
  const OP_AND = 1;
  const OP_OR = 2;
  //
  /**
   * @param string $s 
   * @return array(static,..)
   */
  static function from($s) {
    if (! empty($s)) {  
      $mes = array();
      $arr = explode(':', $s);
      foreach ($arr as $a) 
        $mes[] = static::fromComp($a);
      return $mes;
    }
  }
  static function fromComp($a) {
    $me = new static();
    $arr = preg_split('/\\(|\\)/', $a);
    $me->id = static::getId(current($arr));
    $me->args = static::getArgs(next($arr));
    $me->op = static::getOp(next($arr));
    return $me; 
  }
  static function getId($s) {
    switch (trim($s)) {
      case 'always':
        return static::ALWAYS;
      case 'isSel':
        return static::IS_SEL;
      case 'notSel':
        return static::NOT_SEL;
      case 'isMale':
        return static::IS_MALE;
      case 'isFemale':
        return static::IS_FEMALE;
      case 'olderThan':
        return static::OLDER_THAN;
      case 'youngerThan':
        return static::YOUNGER_THAN;
      case 'currentAge':
        return static::IS_AGE;
      case 'isBirthdateSet':
        return static::IS_BIRTHDATE_SET;
      case 'isInjected':
        return static::IS_INJECTED;
    }
  }
  static function getArgs($s) {
    $args = array();
    if (! empty($s)) {
      $arr = explode('.', $s);
      if (count($arr) < 4) {
        $args[] = implode('.', $arr);
      } else { 
        $opt = array_pop($arr);
        $args[] = implode('.', $arr);
        $args[] = $opt; 
      }
    }
    return $args;
  }
  static function getOp($s) {
    switch (trim($s)) {
      case 'and':
        return static::OP_AND;
      case 'or':
        return static::OP_OR;
    }
  }
}  
class Action extends Rec {
  //
  public $Tests;  // Test
  public $id;
  public $args;  // string[]
  //
  const INJECT = 1;
  const SET_DEFAULT = 2;
  const SET_CHECKED = 3;
  const SET_UNCHECKED = 4;
  const SYNC_ON = 5;
  const SYNC_OFF = 6;
  const SET_TEXT_FROM_SEL = 7;
  const CALC_BMI = 8;
  //
  /**
   * @param string $s 
   * @return array(static,..)
   */
  static function from($s) {
    if (! empty($s)) {  
      $mes = array();
      $arr = explode(';', $s);
      foreach ($arr as $a) 
        $mes[] = static::fromComp($a);
      return $mes;
    }
  }
  static function fromComp($a) {
    $me = new static();
    $arr = preg_split('/\\{|\\}/', $a);
    if (count($arr) == 1)
      array_unshift($arr, null);
    $me->Tests = static::getTests(reset($arr));
    $arr2 = preg_split('/\\(|\\)/', next($arr));
    $me->id = static::getId(current($arr2));
    $me->args = static::getArgs(next($arr2));
    return $me; 
  }
  static function getTests($s) {
    return Test::from($s);
  }
  static function getId($s) {
    switch (trim($s)) {
      case 'inject':
        return static::INJECT;
      case 'setDefault':
        return static::SET_DEFAULT;
      case 'setChecked':
        return static::SET_CHECKED;
      case 'setUnchecked':
        return static::SET_UNCHECKED;
      case 'syncOn':
        return static::SYNC_ON;
      case 'syncOff':
        return static::SYNC_OFF;
      case 'setTextFromSel':
        return static::SET_TEXT_FROM_SEL;
      case 'calcBmi':
        return static::CALC_BMI;
    }
  }
  static function getArgs($s) {
    return Test::getArgs($s);
  }
}