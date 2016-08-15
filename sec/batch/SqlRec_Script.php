<?php
require_once 'php/data/rec/sql/_SqlRec.php';
//
abstract class SqlRec_Script extends SqlRec implements ReadOnly {
  //
  static $NEXT_ID;
  static $ID_MAP = array();
  //
  protected function setMappedPk() {
    $oldId = $this->getPkValue();
    if (static::$NEXT_ID) {
      $new = static::$NEXT_ID + 1;
      static::$NEXT_ID =& $new;
      $this->setPkValue($new);
      static::$ID_MAP[$oldId] =& $new;
    } else {
      $this->setPkValue(null);
    }
    $this->_pk = $oldId;
  }
  //
  static function setNextId($id) {
    static::$NEXT_ID =& $id;
  }
  static function setNextId_byCount($dao) {
    if (static::$NEXT_ID == null) {
      $c = new static();
      $pk = $c->getPkField();
      $table = $c->getSqlTable();
      $sql = "SELECT MAX($pk) FROM $table";
      static::setNextId($dao::fetchValue($sql));
    } 
  }
  static function getMappedPk($oldId) {
    return static::$ID_MAP[$oldId];
  }
  /**
   * @param FileManager $fm
   * @param SqlRec[] $recs
   */
  protected static function appendSqlInserts($fm, $recs) {
    foreach ($recs as $rec) 
      static::appendSqlInsert($fm, $rec);
  }
  protected static function appendSqlInsert($fm, $rec) {
    $rec->setMappedPk();
    $fm->write($rec->getSqlInsert());
  }
}
//
class Dao_Emr extends Dao {
  //
  static function open() {
    parent::open('emr');
  }
}
class Dao_Test extends Dao {
  //
  static function open() {
    parent::open('emrtest');
  }
}
class Dao_Cert extends Dao {
  //
  static function open() {
    parent::open('cert');
  }
}