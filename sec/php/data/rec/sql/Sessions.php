<?php
require_once 'php/data/rec/sql/_SessionRec.php';
require_once 'php/data/rec/sql/_TemplateRecs.php';
/**
 * Sessions DAO 
 */
class Sessions {
  //
  static function /*SessionNote[]*/getNotes($cid) {
    $recs = SessionNote::fetchAll($cid); 
    Rec::sort($recs, new RecSort('-dateService'));
    return $recs;
  }
  static function /*SessionNoteStub[]*/getStubs($cid) {
    $recs = SessionNoteStub::fetchAll($cid); 
    Rec::sort($recs, new RecSort('-dateService'));
    return $recs;
  }
  static function /*SessionNoteStub[]*/getUnsigned($userId = null) {
    global $login;
    return SessionNoteStub::fetchAll_unsigned($login->userGroupId, $userId);
  }
  static function /*filename*/uploadImage($sid) {
    require_once 'php/data/rec/group-folder/GroupFolder_SessionImages.php';
    $rec = SessionNoteStub::fetch($sid);
    if ($rec) {
      $upload = GroupFolder_SessionImages::open()->upload($sid);
      return $upload;
    }
  }
  //
  static function getTemplateJsonList() {
    return MethodCache::getset(__METHOD__, func_get_args(), function() {
      global $login;
      $a = array();
      $recs = MyTemplate::fetchAll($login->userGroupId);
      foreach ($recs as $rec) 
        $a[$rec->templateId] = $rec->name;
      return jsonencode($a);
    });
  }
}
class SessionNote extends SessionRec {
  //
  public $sessionId;
  public $userGroupId;
  public $clientId;
  public $templateId;
  public $dateCreated;
  public $dateUpdated;
  public $dateService;
  public $closed;
  public $closedBy;
  public $dateClosed;
  public $billed;
  public $schedId;
  public $data;
  public $createdBy;
  public $updatedBy;
  public $sendTo;
  public $assignedTo;
  public $html;
  public $title;
  public $standard;
  public $noteDate;
  //
  public function saveAsSigned() {
    // TODO
  }
  public function toJsonObject(&$o) {
    parent::toJsonObject($o);
    if ($this->closed == 2) {
      $o->_html = $this->data;
      $o->data = null;
    } else {
      $o->_html = $this->html;
      $o->html = null;
    }
  }
}
class SessionNoteStub extends SessionRec implements ReadOnly { 
  //
  public $sessionId;
  public $userGroupId;
  public $clientId;
  public $templateId;
  public $dateService;
  public $closed;
  public $closedBy;
  public $dateClosed;
  public $createdBy;
  public $updatedBy;
  public $sendTo;
  public $assignedTo;
  public $title;
  //
  static function fetchAll($cid) {
    $c = new static();
    $c->clientId = $cid;
    $recs = static::fetchAllBy($c);
    return $recs;
  }
  static function fetchAll_unsigned($ugid, $userId = null) {
    $c = static::asCriteria_unsigned($ugid, $userId);
    return static::fetchAllBy($c, null, 50);
  }
  static function asCriteria_unsigned($ugid, $userId = null) {
    $c = new static();
    $c->userGroupId = $ugid;
    if ($userId)
      $c->assignedTo = CriteriaValues::_or(CriteriaValue::equalsNumeric($userId), CriteriaValue::isNull());
    $c->closed = 0;
    return $c;
  }
}
class MyTemplate extends TemplateRec implements ReadOnly, NoAuthenticate {
  //
  public $templateId;
  public $name;
  public $public;
  public $userGroupId;
  //
  static function fetchAll($ugid) {
    $recsMy = self::fetchAllBy(self::asMyCriteria($ugid));
    $recsPublic = self::fetchAllBy(self::asPublicCriteria());
    return array_merge($recsMy, $recsPublic);
  }
  static function asMyCriteria($ugid) {
    $c = new self();
    $c->userGroupId = $ugid;
    return $c;
  }
  static function asPublicCriteria() {
    $c = new self();
    $c->public = true;
    return $c;
  }
}
