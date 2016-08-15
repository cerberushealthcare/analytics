<?php
require_once 'php/data/file/_File.php';
require_once 'php/data/rec/sql/_HdataRec.php';
//
class SqlOutputFile extends TextFile {
  //
  static function /*string(filename)[]*/saveAll(/*SqlSet[],SqlSet[]..*/) {
    $args = func_get_args();
    $filenames = array();
    $db = MyEnv::$DB_NAME;
    foreach ($args as $sets) { 
      foreach ($sets as $index => $set) {
        $me = static::from($set, $index, $db);
        if ($me) {
          $me->save();
          $filenames[] = $me->filename;
        }
      }
    }
    return $filenames;
  }
  //
  protected static function from(/*SqlSet*/$set, $index, $db) {
    $lines = $set->getSqls();
    if (! empty($lines)) {
      array_unshift($lines, "USE $db;");
      $me = static::create($lines);
      $index = str_pad($index, 2, '0', STR_PAD_LEFT);
      $ugid = $set->ugid ? 'G' . str_pad($set->ugid, 4, '0', STR_PAD_LEFT) . '_' : '';
      $me->setFilename('SQL_' . $ugid . $set->name . '_' . $index . '.sql');
      return $me;
    }
  }
}
class SqlBatFile extends TextFile {
  //
  static function create($ugid, /*string[]*/$filenames) {
    $lines = array();
    $mysql = MyEnv::$BATCH_MYSQL;
    $db = MyEnv::$DB_NAME;
    $pw = MyEnv::$DB_PW;
    foreach ($filenames as $file) {
      $lines[] = "\"$mysql\" -uroot -p$pw -D$db < $file";
    }
    $me = parent::create($lines);
    $me->setFilename('run_all_G' . str_pad($ugid, 4, '0', STR_PAD_LEFT) . '.bat');
    return $me->save();
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
    $inserts = array();
    static::append($inserts, $recs);
    $sqls = SqlRec::getSqlInsertOnDupeUpdates($inserts, 50);
    return $sqls;
  }
  protected static function append(&$inserts, $rec) {
    if ($rec) {
      if ($rec instanceof Hdata) {
        $inserts[] = $rec;
      } else {
        foreach ($rec as $fid => $value)
          if (is_array($value) || $value instanceof SqlRec)
            static::append($inserts, $value);
      }
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
