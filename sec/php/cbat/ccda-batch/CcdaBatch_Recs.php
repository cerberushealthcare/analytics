<?php
// 
class CcdaBatch extends SqlRec implements NoAudit {
  //
  public $batchId;
  public $userGroupId;
  public $hash;
  public $status;
  public $on;
  public $total;
  public $dateStart;
  public $dateFinish;
  //
  const STATUS_ACTIVE = 1;
  const STATUS_COMPLETE = 2;
  const STATUS_STOPPED = 88;
  const STATUS_ERROR = 99;
  const STATUS_CLEAR = 100;
  //
  public function getSqlTable() {
    return 'ccda_batch';
  }
  public function toJsonObject($o) {
    $o->_status = $this->makeStatusLine();
  }
  public function isActive() {
    return $this->status == static::STATUS_ACTIVE;
  }
  public function isComplete() {
    return $this->status == static::STATUS_COMPLETE;
  }
  public function isStopped() {
    return $this->status == static::STATUS_STOPPED;
  }
  public function isClear() {
    return $this->status == static::STATUS_CLEAR;
  }
  public function hasMore() {
    return $this->isActive() && $this->on < $this->total;
  }
  public function stop() {
    if ($this->isActive()) {
      $this->status = static::STATUS_STOPPED;
      $this->dateFinish = nowNoQuotes();
      $this->save();
    }
  }
  public function complete() {
    if ($this->isActive()) {
      $this->status = static::STATUS_COMPLETE;
      $this->dateFinish = nowNoQuotes();
      $this->save();
    }
  }
  public function clear() {
    $this->status = static::STATUS_CLEAR;
    $this->save();
    ClientCcdaBatch::deleteGroup($this->userGroupId);
  }
  public function next() {
    if ($this->hasMore()) {
      $this->on++;
      $this->save();
    } else {
      $this->stop();
    }
  }
  public function /*ClientCcdaBatch*/getItem() {
    $cb = ClientCcdaBatch::fetchIncomplete($this->userGroupId, $this->batchId, $this->on);
    if ($cb == null) {
      $this->stop();
    }
    return $cb;
  }
  public function setItemComplete(/*ClientCcdaBatch*/$cba) {
    $cba->complete();
    if ($this->on >= $this->total) {
      $this->complete();
    }
  }
  //
  static function fetchLast($ugid) {
    $c = new static();
    $c->userGroupId = $ugid;
    $us = static::fetchAllBy($c, null, 1, null, 'T0.batch_id DESC');
    return current($us);
  }
  static function fetchActive($bid, $hash) {
    $c = new static();
    $c->batchId = $bid;
    $c->hash = $hash;
    $c->status = static::STATUS_ACTIVE;
    return static::fetchOneBy($c);
  }
  static function create($ugid, $clients) {
    $me = new static();
    $me->userGroupId = $ugid;
    $me->hash = MyCrypt_Auto::hash(nowNoQuotes());
    $me->status = static::STATUS_ACTIVE;
    $me->on = 0;
    $me->total = count($clients);
    $me->save();
    ClientCcdaBatch::createAll($me, $clients);
    return $me;
  }
  static function fetchAllActive($ugid) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->status = static::STATUS_ACTIVE;
    return static::fetchAllBy($c);
  }
  static function stopAllActive($ugid) {
    $us = static::fetchAllActive($ugid);
    if ($us) {
      foreach ($us as $me) {
        $me->stop();
      }
    }
  }
  //
  protected function makeStatusLine() {
    if ($this->isClear()) {
      return '';
    } else { 
      $s = 'Last batch #' . $this->batchId;
      if ($this->isActive()) {
        $s .= ' started: ' . formatInformalTime($this->dateStart);
        $s .= ' (on ' . $this->on . ' of ' . $this->total . ')'; 
      } else if ($this->isComplete()) {
        $s .= ' completed: ' . formatInformalTime($this->dateFinish);
        $s .= ' (' . $this->total . ' total)'; 
      } else {
        $s .= ' stopped: ' . formatInformalTime($this->dateFinish);
        $s .= ' (on ' . $this->on . ' of ' . $this->total . ')'; 
      }
      return $s;
    }
  }
}
class ClientCcdaBatch extends SqlRec implements NoAudit {
  //
  public $clientId;
  public $userGroupId;
  public $batchId;
  public $index;
  public $complete;
  //
  public function getSqlTable() {
    return 'client_ccda_batch';
  }
  public function isComplete() {
    return $this->complete == 1;
  }
  public function complete() {
    $this->complete = 1;
    $this->save();  
  }
  public function toJsonObject($o) {
    logit_r($o, 'before mf');
    if ($this->isComplete()) {
      $o->_filename = static::makeFilename($this->batchId, $this->clientId);
    } 
    logit_r($o, 'after mf');
  }
  //
  static function makeFilename($bid, $cid) {
    return "B$bid" . "_" . "$cid.xml";
  }
  static function fetchIncomplete($ugid, $bid, $index) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->batchId = $bid;
    $c->index = $index;
    $c->complete = 0;
    return static::fetchOneBy($c); 
  }
  static function createAll($batch, $clients) {
    static::deleteGroup($batch->userGroupId);
    foreach ($clients as $i => $client) {
      static::create($batch, $client, $i);
      // @todo - retrieve sql statements instead and submit multi
    }
  }
  static function create($batch, $client, $i) {
    $me = new static();
    $me->clientId = $client->clientId;
    $me->userGroupId = $client->userGroupId;
    $me->batchId = $batch->batchId;
    $me->index = $i + 1;
    $me->complete = 0;
    $me->saveAsInsert();
  }
  static function asJoin() {
    $c = new static();
    return CriteriaJoin::optional($c);
  }
  static function deleteGroup($ugid) {
    $sql = "DELETE FROM client_ccda_batch WHERE user_group_id=$ugid";
    Dao::query($sql);
  }
}