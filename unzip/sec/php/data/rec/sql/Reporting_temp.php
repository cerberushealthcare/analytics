<?php
class RepCritJoin extends Rec {
  //
  public $jt;
  public $ct;
  public $table;
  public /*RepCrit*/ $Recs;
  //
  const JT_HAVE = '1';
  const JT_NOT_HAVE = '2';
  const JT_HAVE_CT = '3';
  const JT_HAVE_CT_LT = '4';
  const JT_HAVE_CT_GT = '5';
  const JT_HAVE_ONE = '10';
  const JT_HAVE_ALL = '11';
  const JT_NOT_HAVE_ANY = '12';
  const JT_NOT_HAVE_ALL = '13';
  const JT_OPTIONAL = '99';
  static $JTS = array(
    self::JT_HAVE => 'having',
    self::JT_HAVE_CT => 'having exactly',
    self::JT_HAVE_CT_LT => 'having less than',
    self::JT_HAVE_CT_GT => 'having at least',
    self::JT_NOT_HAVE => 'not having',
    self::JT_HAVE_ONE => 'having at least one of',
    self::JT_HAVE_ALL => 'having all of',
    self::JT_NOT_HAVE_ANY => 'not having any of',
    self::JT_NOT_HAVE_ALL => 'not having all of'
  );
  //
  public function getClassFromJsonField($fid) {
    return RepCritRec::getClassFromTable($this->table);
  }
  public function isJoinedTo($table) {
    $rec = current($this->Recs);
    if ($rec && $rec->getTable() == $table)
      return true;
  }
  protected function getSingular($fid) {
    return $fid;
  }
  public function asSqlJoins($sqlIndex = null) {
    if ($this->hasData()) {
      $joins = array();
      $recs = RepCritRec::asSqlCriterias($this->Recs);
      switch ($this->jt) {
        case self::JT_HAVE:
          $joins[] = CriteriaJoin::requiresAsArray(current($recs))->usingSqlIndex($sqlIndex);
          break;
        case self::JT_NOT_HAVE:
          $joins[] = CriteriaJoin::notExists(current($recs))->usingSqlIndex($sqlIndex);
          break;
        case self::JT_HAVE_CT:
          $joins[] = CriteriaJoin::requiresCountEquals(current($recs), $this->ct)->usingSqlIndex($sqlIndex);
          break;
        case self::JT_HAVE_CT_LT:
          $joins[] = CriteriaJoin::requiresCountLessThan(current($recs), $this->ct)->usingSqlIndex($sqlIndex);
          break;
        case self::JT_HAVE_CT_GT:
          $joins[] = CriteriaJoin::requiresCountAtLeast(current($recs), $this->ct)->usingSqlIndex($sqlIndex);
          break;
        case self::JT_HAVE_ONE:
          $joins[] = CriteriaJoin::requiresAnyOf($recs)->usingSqlIndex($sqlIndex);
          break;
        case self::JT_HAVE_ALL:
          foreach ($recs as $rec) 
            $joins[] = CriteriaJoin::requiresAsArray($rec)->usingSqlIndex($sqlIndex);
          break;
        case self::JT_NOT_HAVE_ANY:
          foreach ($recs as $rec) 
            $joins[] = CriteriaJoin::notExists($rec)->usingSqlIndex($sqlIndex);
          break;
        case self::JT_NOT_HAVE_ALL:
          foreach ($recs as $rec) {
            $joins[] = CriteriaJoin::notExists($rec, null, 'or1')->usingSqlIndex($sqlIndex);
          }
          break;
        case self::JT_OPTIONAL:
          $joins[] = CriteriaJoin::optionalAsArray(current($recs))->usingSqlIndex($sqlIndex);
          break;
      }
      return $joins;
    }
  }
  public function hasData() {
    return ($this->jt && count($this->Recs) > 0);
  }
  //
  /**
   * @param string|int 
   * @return RepCritJoin
   */
  static function forTable($table, $joinType = null) {
    $rec = new self();
    $rec->jt = $joinType ?: static::JT_HAVE;
    $rec->table = (is_numeric($table)) ? $table : RepCritRec::getTableFromName($table);
    $class = $rec->getClassFromJsonField(null);
    $rec->Recs = array(new $class());
    return $rec;
  }
  static function asAddress() {
    return self::forTable(RepCritRec::T_ADDRESS, static::JT_OPTIONAL);
  }
  static function reviveAll($joins) {
    $us = array();
    foreach ($joins as $join) {
      $us[] = static::revive($join);
    }
  }
  static function revive($join) {
    $me = new static();
    $me->jt = $join->jt;
    $me->ct = $join->ct;
    $me->Recs = RepCritRec::reviveAll($join->Recs); 
  }
}
