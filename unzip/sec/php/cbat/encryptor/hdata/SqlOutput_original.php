<?php
require_once 'php/data/file/_File.php';
//
interface CustomOutputSql {
  public function getOutputSql(); /*for returning other than default update SQL*/
} 
//
class SqlOutputFile extends TextFile {
  //
  static function /*string(filename)[]*/saveAll(/*SqlSet[],SqlSet[]..*/) {
    $args = func_get_args();
    $filenames = array();
    foreach ($args as $sets) { 
      foreach ($sets as $index => $set) {
        $me = static::from($set, $index);
        $me->save();
        $filenames[] = $me->filename;
      }
    }
    return $filenames;
  }
  //
  protected static function from(/*SqlSet*/$set, $index) {
    $lines = $set->getSqls();
    $me = static::create($lines);
    $index = str_pad($index, 2, '0', STR_PAD_LEFT);
    $ugid = $set->ugid ? 'G' . str_pad($set->ugid, 4, '0', STR_PAD_LEFT) . '_' : '';
    $me->setFilename('SQL_' . $ugid . $set->name . '_' . $index . '.sql');
    return $me;
  }
}
class SqlBatFile extends TextFile {
  //
  static function create($ugid, /*string[]*/$filenames) {
    $lines = array();
    $mysql = MyEnv::$BATCH_MYSQL;
    foreach ($filenames as $file) {
      $lines[] = "echo $file";
      $lines[] = "\"$mysql\" -uroot -pclick01 -Demrtest < $file";
    }
    $lines[] = 'pause';
    $me = parent::create($lines);
    $me->setFilename('run_all_G' . str_pad($ugid, 4, '0', STR_PAD_LEFT) . '.bat')->save();
  }
}
class SqlSet {
  //
  public $name; /*e.g. DataMeds*/
  public $ugid;
  public $recs;
  public $nextPk;
  //
  static function fetchAll($class/*e.g. 'Client_Hdc'*/, $ugid = null) {
    $rec = new $class();
    $name = SqlRec::sqlToCamel($rec->getSqlTable(), true);
    $us = array();
    static::append($us, $name, $ugid, $rec);
    return $us;
  }
  public static function fetch(/*SqlRec*/$criteria, /*int*/$limit = 500) {
    $a = SqlRec::fetchAllAndFlatten($criteria, null, $limit + 1);
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
  //
  public function getSqls() {
    return SqlStringer::all($this->recs);
  }
  //
  protected static function append(&$us, $name, $ugid, $rec, $startPk = null) {
    $me = $rec::fetchSet($ugid, $startPk);
    $me->name = $name;
    $me->ugid = $ugid;
    $us[] = $me;
    if ($me->nextPk)
      static::append($us, $name, $ugid, $rec, $me->nextPk);
  }
}
class SqlStringer {
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
        if ($rec instanceof CustomOutputSql)
          $sql = $rec->getOutputSql();
        else if ($rec instanceof CompositePk)
          $sql = $rec->getSqlInsertOnDupeUpdate();
        else
          $sql = $rec->getSqlUpdate();
        if (! empty($sql))
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
