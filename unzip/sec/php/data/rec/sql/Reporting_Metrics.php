<?php
require_once 'php/data/rec/sql/_SqlRec.php';
//
class ReportMetric extends SqlRec {
  //
  public $reportId;
  public $targetValue;
  public $redValue;
  //
  public function getSqlTable() {
    return 'report_metrics';
  }
}
class ReportHist extends SqlRec implements CompositePk, NoAuthenticate {
  //
  public $reportId;
  public $userGroupId;
  public $userId;
  public $date;
  public $value;
  public $num;
  public $den;
  //
  const ALL = 0;
  const CURRENT = '0000-00-00 00:00:00';
  //
  public function getSqlTable() {
    return 'report_history';
  }
  public function getPkFieldCount() {
    return 4;
  }
  public function isAll() {
    return $this->userGroupId == static::ALL && $this->userId == static::ALL;
  }
  //
  static function asJoinCurrent() {
    $c = new static();
    $c->userGroupId = CriteriaValue::equalsNumeric(static::ALL);
    $c->userId = CriteriaValue::equalsNumeric(static::ALL);
    $c->date = static::CURRENT;
    return CriteriaJoin::optional($c);
  }
  static function saveFrom(/*ReportCriteria*/$rc) {
    $me = static::from($rc);
    $me->save();
    if ($me->isAll())
      static::saveAsCurrent($me);
    return $me;
  }
  static function from(/*ReportCriteria*/$rc) {
    $ugid = null;
    $userId = null;
    static::searchCrit($rc, $ugid, $userId);
    $me = new static();
    $me->reportId = $rc->reportId;
    $me->userGroupId = $ugid ?: static::ALL;
    $me->userId = $userId ?: static::ALL;
    $me->date = nowShortNoQuotes();
    $me->num = count($rc->recs);
    $me->den = count($rc->recsDenom);
    $me->value = $me->den ? ($me->num / $me->den) * 100 : 0; 
    return $me;
  }
  static function saveAsCurrent($from) {
    $rec = new static($from);
    $rec->date = static::CURRENT;
    $rec->save();
  }
  //
  protected static function searchCrit(/*ReportCriteria*/$rc, &$ugid, &$userId) {
    $client = $rc->Rec;
    static::setValues($client, $ugid, $userId);
    if ($client->Joins) 
      foreach ($client->Joins as $join) 
        foreach ($join->Recs as $rec) 
          static::setValues($rec, $ugid, $userId);
  }
  protected static function setValues(/*RepCritRec*/$rec, &$ugid, &$userId) {
    static::setValue($rec, 'userGroupId', $ugid);
    static::setValue($rec, 'userId', $userId);
  }
  protected static function setValue(/*RepCritRec*/$rec, $fid, &$to) {
    if ($to == null) {
      if (isset($rec->$fid)) 
        $to = static::getValue($rec->$fid);
    }
  }
  protected static function getValue(/*RepCritValue*/$rcv) {
    if ($rcv->op == RepCritValue::OP_IS)
      return $rcv->value;
  }
} 
