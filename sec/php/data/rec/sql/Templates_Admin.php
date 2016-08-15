<?php
require_once 'php/data/rec/sql/_TemplateRecs.php';
//
/**
 * Template Administration
 * @author Warren Hornsby
 */
class Templates_Admin {
  //
  public function getCinfos($pid) {
    $recs = Cinfo_A::fetchAll($pid);
    return $recs;
  }
  public function saveCinfo($obj) {
    $rec = new Cinfo_A($obj);
    $rec->save();
    return $rec;
  }
  public function deleteCinfo($id) {
    $rec = Cinfo_A::fetch($id);
    Cinfo_A::delete($rec);
  }
} 
//
class Par_A extends ParRec implements AdminOnly {
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
  public $notes; 
}
//
class Cinfo_A extends CinfoRec implements AdminOnly {
  public $cinfoId;
  public $parId;
  public $type;
  public $name;
  public $dateCreated;
  public $text;
  //
  static function fetchAll($pid) {
    $c = new self();
    $c->parId = $pid;
    return self::fetchAllBy($c);
  }
}
