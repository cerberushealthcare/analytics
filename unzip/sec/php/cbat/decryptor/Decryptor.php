<?php
require_once 'Decrypt_Sql.php';
require_once 'php/data/file/_File.php';
//
interface CustomSql {
  public function getCustomSql(); /*for returning other than default update SQL*/
} 
//
class Decryptor {
  //
  static function getAllSets($ugid) {
    return array_merge(
      static::getSets('Client', $ugid),
      static::getSets('Session', $ugid, 5000),
      static::getSets('Proc', $ugid),
      static::getSets('DataSync', $ugid),
      static::getSets('AuditRec', $ugid, 5000),
      static::getSets('Sched', $ugid),
      static::getSets('TrackItem', $ugid),
      static::getSets('HL7Inbox', $ugid),
      static::getSets('MsgThread', $ugid),
      static::getSets('PortalUser', $ugid));
  }
  //
  protected static function getSets($name, $ugid, $limit = 2000) {
    $sets = array();
    static::appendSets($sets, $name, $ugid, $limit);
    return $sets;
  }
  protected static function appendSets(&$sets, $name, $ugid, $limit, $index = 1, $startPk = null) {
    $class = $name . '_E';
    $lset = $class::fetchSet($ugid, $startPk, $limit);
    $sets[] = DecryptSet::from($lset, $name, $ugid, $index);
    if ($lset->nextPk)
      static::appendSets($sets, $name, $ugid, $limit, ++$index, $lset->nextPk);
  }
  static function getSessionSql($ugid) {
    return Stringer::all(
      Session_E::fetchAll($ugid));
  }
  static function getProcSql($ugid) {
    return Stringer::all(
      Proc_E::fetchAll($ugid));
  }
  static function getDataSql($ugid) {
    return Stringer::all(
      DataSync_E::fetchAll($ugid));
  }
  static function getOtherSql($ugid) {
    return Stringer::all(
      Sched_E::fetchAll($ugid),
      TrackItem_E::fetchAll($ugid),
      HL7Inbox_E::fetchAll($ugid),
      MsgThread_E::fetchAll($ugid),
      PortalLogin_E::fetchAll($ugid),
      PortalUser_E::fetchAll($ugid));

  }
  static function getAuditSql($ugid) {
    return Stringer::all(
      AuditRec_E::fetchAll($ugid));
  }
}
class DecryptSet {
  //
  public $name;
  public $ugid;
  public $index;
  public $sqls;
  //
  static function from(/*LimitedSet*/$lset, $name, $ugid, $index) {
    $me = new static();
    $me->name = $name;
    $me->ugid = $ugid;
    $me->index = $index;
    $me->sqls = Stringer::all($lset->recs);
    return $me;
  }
}
class SqlFile_E extends TextFile {
  //
  static function from(/*DecryptSet*/$set) {
    $me = static::create($set->sqls);
    $index = str_pad($set->index, 3, '0', STR_PAD_LEFT);
    $me->setFilename('Sql_' . $set->name . '_' . $set->ugid . '_' . $index . '.sql');
    return $me;
  }
}
class LimitedSet {
  //
  public $recs;
  public $nextPk;
  //
  static function fetch(/*SqlRec*/$criteria, /*int*/$limit) {
    $a = SqlRec::fetchAllAndFlatten($criteria, null, $limit);
    $me = new static();
    $recs = $a[0];
    $count = count($a[1]);
    $limit = $a[2];
    if ($count == $limit && count($recs) > 1) {
      $rec = array_pop($recs);
      $me->nextPk = $rec->getPkValue();
    }
    $me->recs = $recs;
    return $me;
  }
}
class Stringer {
  //
  static function /*string[]*/all(/*Rec[],..*/) {
    $args = func_get_args();
    $recs = static::flatten($args);
    $sqls = array();
    static::append($sqls, $recs);
    return $sqls;
  }
  protected static function append(&$sqls, $rec) {
    if ($rec) {
      if ($rec instanceof SqlRec) {
        if ($rec instanceof CustomSql)
          $sql = $rec->getCustomSql();
        else if ($rec instanceof CompositePk)
          $sql = $rec->getSqlInsertOnDupeUpdate();
        else
          $sql = $rec->getSqlUpdate();
        $sqls[] = $sql . ';';
      }
      foreach ($rec as $fid => $value)
        if (is_array($value) || $value instanceof SqlRec)
          static::append($sqls, $value);
    }
  }
  protected static function flatten($args) {
    $recs = array();
    foreach ($args as &$arg) {
      if ($arg) {
        if (is_object($arg))
          $arg = array($arg);
        $recs[] = $arg;
      }
    }
    if (! empty($recs))
      $recs = call_user_func_array('array_merge', $recs);
    return $recs;
  }
}
