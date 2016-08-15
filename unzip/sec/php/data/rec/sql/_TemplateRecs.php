<?php
require_once 'php/data/rec/sql/_SqlRec.php';
/**
 * Template Base Classes
 * @author Warren Hornsby 
 */
abstract class TemplateRec extends SqlRec {
  /*
  public $templateId;
	public $userId;
	public $uid;
	public $name;
	public $public;
	public $dateCreated;
	public $dateUpdated;
	public $desc;
	public $title;
	public $userGroupId;
	*/
  const TID_MED_NOTE = 1;
  const TID_ORDER_ENTRY = 30;
  //
  public function getSqlTable() {
    return 'templates';
  }
	//
	protected function authenticateUserGroupId($ugid) {
	  if (! $this->public) 
	    SqlAuthenticator::authenticateUserGroupId($ugid);
	}
}
abstract class SectionRec extends SqlRec {
  /*
	public $sectionId;
	public $templateId;
	public $uid;
	public $name;
	public $desc;
	public $sortOrder;
	public $title;
  */
	const UID1_HIDE = '@';        // e.g. '@header', always hidden
	const UID1_HIDE_EMPTY = '&';  // e.g. '&addSec', hidden until a par loaded
  //
  public function getSqlTable() {
    return 'template_sections';
  }
}
abstract class ParRec extends SqlRec {
  /*
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
  */
  const UID1_CLONING = '+';  // e.g. '+dermExam'
  const UID1_CUSTOM = '=';   // e.g. '=greetingNar'
  //
  public function getSqlTable() {
    return 'template_pars';
  }
}
abstract class QuestionRec extends SqlRec {
  /*
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
  */
  //
  const TYPE_STANDARD = 0;
  const TYPE_ALLERGY = 1;
  const TYPE_CALC = 2;
  const TYPE_CALENDAR = 3;
  const TYPE_FREE = 4;
  const TYPE_MED = 5;  
  const TYPE_BUTTON = 6;
  const TYPE_COMBO = 7;
  const TYPE_DATA_HM = 8;
  const TYPE_HIDDEN = 9;
  //
  const LIST_TYPE_COMMA = 0;
  const LIST_TYPE_LF = 1;
  const LIST_TYPE_BULLET = 2;
  const LIST_TYPE_SPACE = 3;
  //
  public function getSqlTable() {
    return 'template_questions';
  }
  public function getType() {
    switch (substr($this->uid, 0, 1)) {
      case '!':
        return static::TYPE_ALLERGY;
      case '#':
        return static::TYPE_CALC;
      case '%':
        return static::TYPE_CALENDAR;
      case '$':
        return static::TYPE_FREE;
      case '@':
        return static::TYPE_MED;
      case '*':
        return static::TYPE_BUTTON;
      case '?':
        return static::TYPE_COMBO;
      case '^':
        return static::TYPE_DATA_HM;
      case '=':
        return static::TYPE_HIDDEN;
      default:
        return static::TYPE_STANDARD;
    }
  }
}
abstract class OptionRec extends SqlRec {
  /*  
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
  public $trackCat; // this is really the IPC code
  */  
  //
  public function getSqlTable() {
    return 'template_options';
  }
  public function getText() {
    return ($this->text == null) ? $this->uid : $this->text;
  }
}
abstract class CinfoRec extends SqlRec {
  /*
  public $cinfoId;
  public $parId;
  public $type;
  public $name;
  public $dateCreated;
  public $text;
  */
  //
  public function getSqlTable() {
    return 'template_cinfos';
  }
}
abstract class ParCacheRec extends SqlRec implements CompositePk, NoAudit {
  /*
  public $sectionId;
  public $parUid;
  public $json;
  */
  //
  public function getSqlTable() {
    return 'template_pcache';
  }
  public function getPkFieldCount() {
    return 2;
  }
}