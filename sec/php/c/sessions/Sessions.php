<?php
require_once 'php/data/rec/sql/_SessionRec.php';
require_once 'php/data/rec/sql/_TemplateRecs.php';
//
/**
 * Sessions Notes
 * @author Warren Hornsby 
 */
class Sessions {
  //
  /** Get all stubs */
  static function /*SessionNoteStub[]*/getStubs($cid) {
    $recs = SessionNoteStub::fetchAll($cid); 
    Rec::sort($recs, new RecSort('-dateService'));
    return $recs;
  }
  /** Get unsigned stubs */
  static function /*SessionNoteStub[]*/getUnsigned($userId = null) {
    global $login;
    return SessionNoteStub::fetchAll_unsigned($login->userGroupId, $userId);
  }
  /** Save uploaded image to note */
  static function /*GroupUpload_SessionImage*/uploadImage($sid) {
    require_once 'php/data/rec/group-folder/GroupFolder_SessionImages.php';
    $rec = SessionNoteStub::fetch($sid);
    if ($rec) {
      $upload = GroupFolder_SessionImages::open()->upload($sid);
      return $upload;
    }
  }
  /** Sign a note */
  static function sign($sid) {
    $cache = SessionSigCache::fetch($sid);
    $session = SessionNote::fetch($sid);
    $session->saveAsSigned(get($cache, 'html'));
    if ($cache)
      SessionSigCache::delete($cache);
  }
  /** Unsign a note */
  static function unsign($sid) {
    global $login;
    $session = SessionNote::fetch($sid);
    if (! $login->admin && $session->closedBy != $login->userId)
      throw new InvalidDataException('Notes may be unsigned only by original signer.');
    Dao::begin();
    try {
      $html = $session->getSigAsUnsigned();
      SessionSigCache::cache($session->sessionId, $html);
      $session->saveAsUnsigned();
      $cid = $session->clientId;
      Dao::query("DELETE FROM data_meds WHERE client_id=$cid AND session_id=$sid");
      Dao::query("DELETE FROM data_allergies WHERE client_id=$cid AND session_id=$sid");
      Dao::query("DELETE FROM data_diagnoses WHERE client_id=$cid AND session_id=$sid");
      Dao::query("DELETE FROM data_vitals WHERE client_id=$cid AND session_id=$sid");
      Dao::query("DELETE FROM data_syncs WHERE client_id=$cid AND session_id=$sid");
      Dao::commit();
    } catch (Exception $e) {
      Dao::rollback();
      throw $e;
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
  //
  protected static function asCriteria_unsigned($ugid, $userId = null) {
    $c = new static();
    $c->userGroupId = $ugid;
    if ($userId)
      $c->assignedTo = CriteriaValues::_or(CriteriaValue::equalsNumeric($userId), CriteriaValue::isNull());
    $c->closed = 0;
    return $c;
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
  public function /*string*/getSig() {
    $delim = 'id=sig>';
    $a = explode($delim, $this->html, 2);
    if (count($a) > 1) 
      return "<DIV " . $delim . $a[1];
  }
  public function /*string*/getSigAsUnsigned() {
    $html = $this->getSig();
    return $this->appendSig($html, 'Unlocked and Re-Opened:');
  }
  public function /*SessionNote*/saveAsSigned($html/*cached sigs*/ = null) {
    global $login;
    logit_r($this, 'saveAsSigned');
    $this->closed = 3;
    $this->closedBy = $login->userId;
    $this->dateClosed = nowNoQuotes();
    $this->html .= $this->appendSig($html);
    logit_r($this, 'saveAsSigned after');
    return $this->saveAsAuditAction(AuditRec::ACTION_SIGN);
  }
  public function /*SessionNote*/saveAsUnsigned() {
    $this->closed = 0;
    $this->closedBy = null;
    $this->dateClosed = null;
    $this->html = null;
    return $this->saveAsAuditAction(AuditRec::ACTION_UNSIGN);
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
  public function getAuditRecName() {
    return 'Session';
  }
  protected function appendSig(&$html, $title = 'Digitally Signed:') {
    global $login;
    $sig = $this->makeSig($login->User->name, $login->User->UserGroup->name, $title);
    if (empty($html))
      $html = $sig;
    else
      $html .= '<p></p>' . $sig;
    return $html;
  }
  protected function makeSig($name, $groupName, $title) {
    $now = formatNowTimestamp();
    $h = array("<DIV id=sig><TABLE border=1><TR><TD align=center>");
    $h[] = "<b><i>$title</b></i><br><i>$now by $name";
    if (! empty($groupName)) 
      $h[] = " ($groupName)";
    $h[] = "</i></TD></TR></TABLE></DIV>";
    return implode('', $h);
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
class SessionSigCache extends SqlRec implements CompositePk, NoAudit {
  //
  public $sessionId;
  public $html;
  //
  public function getSqlTable() {
    return 'session_sigcache';
  }
  //
  static function cache($sid, $html) {
    $me = new static($sid, $html);
    return $me->save();
  }
}