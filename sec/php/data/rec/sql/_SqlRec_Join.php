<?php
/**
 * Criteria Join
 * To describe join relationship when assigning constituent rec properties 
 */
class CriteriaJoin {
  //
  public $rec;   // criteria to join
  public $join;  // inner, left, left-is-null
  public $as;    // one or array
  public $fid;   // fk fid
  public $ct;    // required count
  public $op;    // count op equals, lt, gt
  public $orWhere;  // to combine where clauses in OR
  public $case;
  public $hardValue;  
  public $indexHint; 
  //
  public $recs;
  //
  const JOIN_TBD = null;  // to be determined (implied join thru Rec assignment to criteria value)
  const JOIN_INNER = 1;
  const JOIN_LEFT = 2;
  const JOIN_LEFT_IS_NULL = 3;  // e.g. 'not having'
  const JOIN_LEFT_IS_NOT_NULL = 4;  // e.g. 'having at least one of'
  //
  const AS_ONE = null;
  const AS_ARRAY = true;
  //
  const OP_EQ = '=';
  const OP_LT = '<';
  const OP_GT = '>';
  const OP_GTE = '>=';
  //
  const NO_JOIN_FID = -1; 
  //
  /**
   * @param SqlRec|SqlRec[] $rec
   * @param int $join JOIN_
   * @param bool $as AS_ONE|AS_ARRAY (default AS_ONE)
   * @param string $fid of foreign key (optional)
   * @param bool $asArray
   * @param int $ct if joining for specific count
   * @param string $op OP_ if joining for specific count
   * @param string $orWhere 'key' to associate join wheres into "OR" clause e.g. (.. OR .. OR ..)
   * @param string $hardValue to assign hardcoded value to join $fid
   * @param string $case 'key' to group orWheres e.g. ((.. AND ..) OR (.. AND .. AND ..))
   */
  public function __construct($rec, $join, $as = null, $fid = null, $asArray = null, $ct = null, $op = null, $orWhere = null, $hardValue = null, $case = null) {
    $this->rec = $rec;
    $this->join = $join;
    $this->as = $as;
    $this->fid = $fid;
    if ($asArray) 
      if (! is_array($rec)) 
        throw new SqlRecException($rec, 'Non-array used as CriteriaJoin array');
    if (is_array($rec)) { 
      $this->recs = $rec;
      $this->rec = current($rec); 
    }
    if ($ct !== null) {
      $this->ct = $ct;
      $this->op = $op;
    }
    $this->orWhere = $orWhere;
    $this->hardValue = $hardValue;
    $this->hardOp = '=';
    $this->case = $case;
  }
  /** Join on provided value */
  public function on($value, $op = '=') { 
    $this->hardValue = $value;
    $this->hardOp = $op;
    return $this;
  }
  /** Join on criteria only; i.e. do not try to determine pk-fk join index */ 
  public function onCriteriaOnly() { 
    $this->fid = self::NO_JOIN_FID;
    return $this;
  }
  /** Provide SQL index hinting */ 
  public function usingSqlIndex($index) { /*index hinting*/
    $this->indexHint = $index;
    return $this;
  }
  /** Association key(s) for combining join wheres into ORs (level1) or OR/ANDs (level 2) */
  public function groupWheres($level1, $level2 = null) {
    $this->orWheres = $level1;
    $this->case = $level2;
    return $this;
  }
  //
  /**
   * @param SqlRec $parent
   * @param string $parentAlias 'T0'
   * @param string $parentPk 'track_item_id'
   * @param string $parentPkFid 'trackItemId'
   * @param string $parentFkFid 'User_orderBy'
   * @param string $table 'users'
   * @param string $alias 'T1'
   * @param string $childPk 'user_id' 
   * @param string $where 'T1.`active`=1' (may be null)
   * @param array $cts to accumulate "GROUP BY .. HAVING" counts
   * Sets $this->sql and $this->where (modified where)
   */
  public function calcSql($parent, $parentAlias, $parentPk, $parentPkFid, $parentFkFid, $table, $alias, $childPk, $where, &$cts) {
    $on = null;
    if ($this->fid != self::NO_JOIN_FID) {
      $overFid = ($this->fid) ? $this->fid : self::getOverrideFid($parentFkFid);
      $over = ($overFid) ? SqlRec::camelToSql($overFid) : null;
      if ($childPk == null) 
        $childPk = $this->rec->getJoinPkField();
      if ($over && $childPk == null) {
        $childPk = $over;
      }
      $fk = ($over) ? $over : $childPk;
      $fkFid = ($overFid) ? $overFid : SqlRec::sqlToCamel($fk);
      //logit_r("fk=$fk, fkfid=$fkFid");
      if ($this->hardValue) {
        $hv = $this->hardValue;
        $hv = str_replace('{PARENT_ALIAS}', $parentAlias, $hv);
        $on = "$alias.$overFid " . $this->hardOp . " $hv";
        if ($this->join == self::JOIN_TBD) 
          $this->join = self::JOIN_LEFT;
      } else {
        if (property_exists($parent, $fkFid)) {  // parent.fkFid exists
          if (property_exists($this->rec, $fkFid))
            $on = "$parentAlias.$fk=$alias.$fk";
          else 
            $on = "$parentAlias.$fk=$alias.$childPk";
          if ($this->join == self::JOIN_TBD) 
            $this->join = self::JOIN_LEFT;
        } else {
          $pk = ($over) ? $over : $parentPk;
          $pkFid = ($overFid) ? $overFid : $parentPkFid; 
          //logit_r("pk=$pk, pkfid=$pkFid");
          if (property_exists($this->rec, $pkFid)) { // child.pkFid exists
            $on = "$parentAlias.$parentPk=$alias.$pk";
            if ($this->join == self::JOIN_TBD) 
              $this->join = self::JOIN_LEFT;
          } else {
            if ($where == null)
              throw new InvalidCriteriaException($this->rec, 'No criteria specified for ' . $table . ': pk=' . $pk . ', pkfid=' . $pkFid . ', parentFkFid=' . $parentFkFid);
            $on = $where;
            $where = null;
            if ($this->join == self::JOIN_TBD) 
              $this->join = self::JOIN_INNER;
          }
        }
      }
    }
    $on = self::appendCond($on, $where);
    $this->sql = " " . $this->_getJoinSql() .  " ($table $alias";
    if ($this->indexHint)
      $this->sql .= ' USE INDEX FOR JOIN (' . $this->indexHint . ')';
    $this->on = ") ON $on";
    if ($this->join == self::JOIN_LEFT_IS_NULL) {
      $this->where = "$alias.$childPk IS NULL";
      if ($this->orWhere)
        $this->where = array($this->orWhere => $this->where);
    } else if ($this->join == self::JOIN_LEFT_IS_NOT_NULL) {
      $this->where = "$alias.$childPk IS NOT NULL";
      if ($this->orWhere) {
        if ($this->case)
          $this->where = array($this->orWhere => array($this->case => $this->where));
        else
          $this->where = array($this->orWhere => $this->where);
      }
    } else {
      $this->where = '';
    }
    if ($this->op) {
      $cts[] = "COUNT(DISTINCT $alias.$childPk)" . $this->op . $this->ct;
    }
    //logit_r($this, 'calcSql');
  }
  protected function _getJoinSql() {
    if ($this->join == self::JOIN_INNER)
      return 'JOIN';
    else
      return 'LEFT JOIN';
  }
  //
  static function optional($rec, $fid = null) {
    return new self($rec, self::JOIN_LEFT, self::AS_ONE, $fid);
  }
  static function optionalAsArray($rec, $fid = null) {
    return new self($rec, self::JOIN_LEFT, self::AS_ARRAY, $fid);
  }
  static function requires($rec, $fid = null) {
    return new self($rec, self::JOIN_INNER, self::AS_ONE, $fid);
  }
  static function requiresAsArray($rec, $fid = null) {
    return new self($rec, self::JOIN_INNER, self::AS_ARRAY, $fid);
  }
  static function requiresCountEquals($rec, $ct, $fid = null) {
    return new self($rec, self::JOIN_LEFT, self::AS_ARRAY, $fid, false, $ct, self::OP_EQ);
  }
  static function requiresCountGreaterThan($rec, $ct = 0, $fid = null) {
    return new self($rec, self::JOIN_INNER, self::AS_ARRAY, $fid, false, $ct, self::OP_GT);
  }
  static function requiresCountAtLeast($rec, $ct = 0, $fid = null) {
    return new self($rec, self::JOIN_INNER, self::AS_ARRAY, $fid, false, $ct, self::OP_GTE);
  }
  static function requiresCountLessThan($rec, $ct, $fid = null) {
    return new self($rec, self::JOIN_LEFT, self::AS_ARRAY, $fid, false, $ct, self::OP_LT);
  }
  static function notExists($rec, $fid = null, $orWhere = null) {
    return new self($rec, self::JOIN_LEFT_IS_NULL, null, $fid, null, null, null, $orWhere);
  }
  static function requiresAnyOf($rec, $fid = null, $orWhere = null, $case = null) {
    return new self($rec, self::JOIN_LEFT_IS_NOT_NULL, null, $fid, null, null, null, $orWhere, null, $case);
    //return new self($recs, self::JOIN_INNER, self::AS_ARRAY, $fid, true);
  }
  static function requiresOneOf($recs, $fid = null) {
    return new self($recs, self::JOIN_INNER, self::AS_ONE, $fid, true);
  }
  //
  private static function appendCond($cond, $where) {
    if ($cond)
      return ($where) ? "$where AND $cond" : $cond;
    else
      return $where;
  }
  private static function getOverrideFid($parentFkFid) {  // 'order_by from $User_orderBy'
    $fid = end(explode('.', $parentFkFid));
    $a = explode('_', $fid);
    if (count($a) > 1) 
      return $a[1];  
  }
}
