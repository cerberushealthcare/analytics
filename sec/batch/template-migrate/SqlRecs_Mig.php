<?php
//
class Par_Mig extends Par_Script {
  //
  static $EFF_DATE;
  //
  static function migrate($fm, $pids, $effectiveDate) {
    static::$EFF_DATE = $effectiveDate;
    static::setNextId_byCount('Dao_Emr');
    $c = static::asCriteria($pids);
    $recs = static::fetchAllBy($c);
    static::appendSqlInserts($fm, $recs);
  }
  //
  protected static function appendSqlInsert($fm, $rec) {
    static::appendUpdateCurrent($fm, $rec);
    $rec->effectiveDate = static::$EFF_DATE;
    parent::appendSqlInsert($fm, $rec);
    Question_Mig::migrate($fm, $rec); 
  }
  protected static function appendUpdateCurrent($fm, $rec) {
    if ($rec->current == '1') 
      $fm->write("UPDATE template_pars SET current=0 WHERE section_id=$rec->sectionId AND uid='$rec->uid'");
  }
  protected static function asCriteria($pids) {
    $c = new static();
    $c->parId = CriteriaValue::in($pids);
    return $c;
  }
}
class Question_Mig extends Question_Script {
  //
  static $PARENT;
  //
  static function migrate($fm, $parent) {
    static::setNextId_byCount('Dao_Emr');
    static::$PARENT = $parent;
    $c = static::asCriteria($parent->_pk);
    $recs = static::fetchAllBy($c);
    static::appendSqlInserts($fm, $recs);
  }
  //
  protected static function appendSqlInsert($fm, $rec) {
    $rec->parId = static::$PARENT->parId;
    parent::appendSqlInsert($fm, $rec);
    Option_Mig::migrate($fm, $rec);
  }
  protected static function asCriteria($pid) {
    $c = new static();
    $c->parId = $pid;
    return $c;
  }
}
class Option_Mig extends Option_Script {
  //
  static $PARENT;
  //
  static function migrate($fm, $parent) {
    static::setNextId_byCount('Dao_Emr');
    static::$PARENT = $parent;
    $c = static::asCriteria($parent->_pk);
    $recs = static::fetchAllBy($c);
    static::appendSqlInserts($fm, $recs);
  }
  //
  protected static function appendSqlInsert($fm, $rec) {
    $rec->questionId = static::$PARENT->questionId;
    parent::appendSqlInsert($fm, $rec);
  }
  protected static function asCriteria($qid) {
    $c = new static();
    $c->questionId = $qid;
    return $c;
  }
}
//
class Par_Script extends SqlRec_Script {
  //
  public $parId;
	public $sectionId;
	public $uid;
	public $major;
	public $sortOrder;
	public $desc;
	public $noBreak;
	public $actions;
	public $injectOnly;
	public $dateEffective;
	public $current;
  public $inDataTable;
  public $inDataType;
  public $inDataCond;
  //
  public function getSqlTable() {
    return 'template_pars';
  }
}
class Question_Script extends SqlRec_Script {
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
  public $mix2;
  public $mcap2;
  public $img;
  public $sortOrder;
  public $actions;
  public $syncId;
  public $outData;
  public $inDataActions;
  public $dsyncId;
  //
  public function getSqlTable() {
    return 'template_questions';
  }
}
class Option_Script extends SqlRec_Script {
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
  public function getSqlTable() {
    return 'template_options';
  }
}
