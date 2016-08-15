<?php
require_once 'php/data/rec/sql/OrderEntry.php';
require_once 'php/data/rec/sql/_TemplateRecs.php';
//
/**
 * Templates_OrderEntry
 * DAO for Order Entry template
 * @author Warren Hornsby
 */
class Templates_OrderEntry {
  //
  /**
   * @return OeTemplate
   */
  static function get($withQuestions = false) {
    $t = OeTemplate::fetch();
    $t->Section->loadPar($withQuestions);
    return $t;
  }
  static function getWithQuestions() {
    return self::get(true);
  }
  /**
   * @return int PID for use with TemplateUi
   */
  static function getPid() {
    $t = self::get();
    return $t->getPar()->parId;
  }
}
//
class OeTemplate extends TemplateRec implements ReadOnly {
  //
  public $templateId;
  //
  public function getPar() {
    return $this->Section->Par;
  }
  //
  static function getTemplateId() {
    return TemplateRec::TID_ORDER_ENTRY;
  }
  static function fetch() {
    $c = self::asCriteria();
    $rec = parent::fetchOneBy($c);
    return $rec;
  }
  static function asCriteria() {
    $c = new self();
    $c->templateId = self::getTemplateId();
    $c->Section = new OeSection();
    return $c;
  }
}
class OeSection extends SectionRec implements ReadOnly {
  //
  public $sectionId;
  public $templateId;
  public $uid;
  //
  public function loadPar($withQuestions = false) {
    $this->Par = OePar::fetch($this->sectionId, $withQuestions);
  }
}
class OePar extends ParRec implements ReadOnly {
  //
  public $parId;
  public $sectionId;
  public $uid;
  public $current;
  //
  static function fetch($sid, $withQuestions = false) {
    $c = self::asCriteria($sid);
    $par = parent::fetchOneBy($c);
    if ($withQuestions)
      $par->Questions = OeQuestion::fetchAll($par->parId);
    return $par;
  }
  static function asCriteria($sid) {
    $c = new self();
    $c->sectionId = $sid;
    $c->current = true;
    return $c;
  }
}
class OeQuestion extends QuestionRec implements ReadOnly {
  //
  public $questionId;
  public $parId;
  public $uid;
  //
  static function fetchAll($pid) {
    $c = self::asCriteria($pid);
    return parent::fetchAllBy($c, null, 3000);
  }
  static function asCriteria($pid) {
    $c = new self();
    $c->parId = $pid;
    $c->Options = array(OeOption::asCriteria());
    return $c;
  }
}
class OeOption extends OptionRec implements ReadOnly {
  //
  public $optionId;
  public $questionId;
  public $uid;
  public $text;
  public $cptCode;
  public $trackCat;
  //
  static function asCriteria() {
    $c = new self();
    $c->trackCat = CriteriaValue::notEquals(TrackItem::TCAT_REFER);
    return $c;
  }
}
