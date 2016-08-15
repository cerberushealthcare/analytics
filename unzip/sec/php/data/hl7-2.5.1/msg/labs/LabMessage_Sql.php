<?php
require_once 'php/data/rec/sql/Clients.php';
require_once 'php/data/rec/sql/Procedures.php';
require_once 'php/data/rec/sql/OrderEntry.php';
require_once 'php/data/rec/sql/_LabXrefRec.php';
require_once 'php/data/rec/sql/_Hl7ProcXrefRec.php';
// 
class LabXref extends LabXrefRec {
  //
  public $labId;
  public $type;
  public $fromId;
  public $userGroupId;
  public $fromText;
  public $toId;  // ipc
  //
  static function asNew($ugid, $labId, $fromId, $fromText, $toId) {
    $me = new static();
    $me->labId = $labId;
    $me->type = static::TYPE_PROC;
    $me->fromId = $fromId;
    $me->userGroupId = $ugid;
    $me->fromText = $fromText;
    $me->toId = $toId;
    return $me;
  }
  static function fetchMap_byId($ugid, $labId) {
    return static::fetchAll($ugid, $labId, 'fromId');
  }
  static function fetchMap_byText($ugid, $labId) {
    return static::fetchAll($ugid, $labId, 'fromText');
  }
  protected static function fetchAll($ugid, $labId, $keyFid = null) {
    $c = new static();
    $c->labId = $labId;
    $c->type = static::TYPE_PROC;
    $c->setUserGroupCriteria($ugid);
    $recs = static::fetchTopLevelsBy($c);
    logit_r($recs, 'fetched top levels');
    if ($keyFid)
      $recs = array_keyify($recs, $keyFid);
    return $recs;
  }
}
class Client_Recon extends Client {
  //
  static function fetch($cid) {
    $me = parent::fetch($cid);
    $me->Address_Home = ClientAddress::fetchHome($cid);
    $ugid = $me->userGroupId;
    $me->TrackItems = TrackItem_Recon::fetchAllOpen($ugid, $cid);
    if ($me->primaryPhys) 
      $me->reviewer_ = $me->primaryPhys;
    else 
      $me->reviewer_ = UserGroups::getFirstDoc()->userId;  // TODO
    return $me;
  }
}
class TrackItem_Recon extends TrackItem {
  //
  public function saveAsReceived($notes, $closedBy) {
    if ($this->trackItemId) {
      $rec = static::fetch($this->trackItemId);
      if ($rec) {
        $rec->status = self::STATUS_CLOSED;
        $rec->closedFor = self::CLOSED_FOR_RECEIVED;
        $rec->closedBy = $closedBy;
        $rec->closedDate = nowNoQuotes();
        $rec->closedNotes = $notes;
        $rec->save();
      }
    }
  } 
  //
  /**
   * @param TrackItem[] $recs
   * @param int $ipc
   * @return TrackItem if IPC found (and not already found on a prior call)
   */
  static function find(&$recs, $ipc) {
    if (! empty($recs) && $ipc) {
      foreach ($recs as &$rec) {
        if ($rec->cptCode == $ipc && ! isset($rec->_found)) {
          $rec->_found = true;
          return $rec;
        }
      }
    }
  }
  static function fetchAllOpen($ugid, $cid) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->clientId = $cid;
    $c->status = CriteriaValue::lessThanNumeric(self::STATUS_CLOSED);
    $recs = self::fetchAllBy($c);
    return $recs;
  }
  static function fetch($id) {
    $c = new static($id);
    return SqlRec::fetchOneBy($c);
  }
  static function revive($json) {
    if (! empty($json)) {
      return new static($json);
    }
  }
}
class Proc_Recon extends Proc {
  //
  static function from($obr, $client, $map) {
    $me = new static();
    $me->Ipc = Ipc_Recon::fromObr($obr, $client->userGroupId, $map);
    $me->ipc = get($me->Ipc, 'ipc');
    $me->userGroupId = $client->userGroupId;
    $me->clientId = $client->clientId;
    $me->date = $obr->obsDateTime->asSqlValue();
    $me->comments = self::makeComments($obr);
    return $me;
  }
  static function revive($json, $ugid) {
    if (! empty($json)) {
      $me = new static($json);
      if ($me->ipc) 
        $me->Ipc = Ipc::fetchTopLevel($me->ipc, $ugid);
      return $me;
    }
  }
  protected static function makeComments($obr) {
    return implode('<br>', $obr->getComments());
  }
}
class ProcResult_Recon extends ProcResult {
  //
  function save($proc) {
    $this->procId = $proc->procId;
    parent::save();
  }
  //
  static function from($obx, $proc, $index, $map) {
    $me = new static();
    $me->clientId = $proc->clientId;
    $me->seq = $index;
    //$me->date = $proc->date;
    $me->Ipc = Ipc_Recon::fromObx($obx, $proc->userGroupId, $map);
    $me->ipc = get($me->Ipc, 'ipc');
    $encoding = ST_EncodingChars::asStandard();
    $me->value = $encoding->unencode($obx->get('value'));
    $me->valueUnit = $obx->get('units.id');
    $me->range = $obx->get('range');
    $me->interpretCode = self::makeInterpretCode($obx);
    $me->comments = self::makeComments($obx);
    return $me;
  }
  static function revive($json, $ugid) {
    if (! empty($json)) {
      $me = new static($json);
      if ($me->ipc) 
        $me->Ipc = Ipc::fetchTopLevel($me->ipc, $ugid);
      return $me;
    }
  }
  //
  protected static function makeInterpretCode($obx) {
    return $obx->get('abnormal');
  }
  protected static function makeComments($obx) {
    return implode('<br>', $obx->getComments());
  }
}
class Ipc_Recon extends Ipc {
  //
  static function fromObr($obr, $ugid, $map) {
    return static::from($map->get_fromObr($obr), $ugid, $obr->serviceId->text);
  } 
  static function fromObx($obx, $ugid, $map) {
    return static::from($map->get_fromObx($obx), $ugid, $obx->obsId->text);
  } 
  static function from($ipc, $ugid, $name) {
    logit_r('Ipc_Recon::from(' . $ipc . ',' . $ugid . ',' . $name . ')');
    if ($ipc)
      return static::fetchTopLevel($ipc, $ugid);
    else if ($name)
      return static::fetchByName($ugid, $name);
  }
}
class Hl7ProcXref extends Hl7ProcXrefRec implements NoAudit {
  //
  public $hpxId;
  public $clientId;
  public $labId;
  public $hl7InboxId; 
  public $orderNo;
  public $ipc;
  public $procId;
  public $supercededBy;
  public /*Proc_Xref*/$Proc;
  //
  static function /*Hl7ProcXref*/create($cid, $labId, $inboxId, $orderNo, $ipc, $proc) {
    $us = static::fetchAll($cid, $labId, $inboxId, $orderNo, $ipc);
    static::saveAllAsSuperceded($us, $proc);
    $me = new static(null, $cid, $labId, $inboxId, $orderNo, $ipc, $proc->procId, null);
    $me->save();
    return $me;
  }
  protected static function fetchAll($cid, $labId, $inboxId, $orderNo, $ipc) {
    $c = new static();
    $c->clientId = $cid;
    $c->labId = $labId;
    $c->hl7InboxId = CriteriaValue::lessThanNumeric($inboxId);
    $c->orderNo = $orderNo;
    $c->ipc = $ipc;
    $c->Proc = new Proc_Xref();
    return static::fetchAllBy($c);
  } 
  protected static function saveAllAsSuperceded($us, $proc) {
    foreach ($us as $me) {
      $me->supercededBy = $proc->procId;
      $me->save();
      $me->Proc->saveSupercededComment($proc);
    }
  }
}
class Proc_Xref extends Proc implements NoAudit {
  //
  public function saveSupercededComment($proc) {
    $now = nowNoQuotes();
    $c = "<B>NOTE: THIS RESULT WAS SUPERCEDED BY ANOTHER RESULT ON $now.</B>";
    if (! empty($this->comments))
      $this->comments = $c . '<br><br>' . $this->comments;
    else
      $this->comments = $c;
    return $this->save();
  }
}