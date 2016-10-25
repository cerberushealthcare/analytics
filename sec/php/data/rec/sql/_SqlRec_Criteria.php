<?php
/**
 * Criteria Value 
 * To extend functionality of criteria record value beyond simple = comparison 
 */
class CriteriaValue {
  //
  public $comparator;
  public $value;
  //
  // Comparators
  const EQ = 'eq';
  const EQN = 'eqn';
  const NEQ = 'neq';
  const NEQN = 'neqn';
  const LT = 'lt';
  const LTE = 'lte';
  const GT = 'gt';
  const GTE = 'gte';
  const LTN = 'ltn';
  const LTEN = 'lten';
  const GTN = 'gtn';
  const GTEN = 'gten';
  const BTW = 'btw';
  const SW = 'sw';
  const NSW = 'nsw';
  const CW = 'cw';
  const NUL = 'nul';
  const NNUL = 'nnul';
  const IN = 'in';
  const NIN = 'nin';
  const DTEQ = 'dteq';
  const REX = 'rex';
  const NREX = 'nrex';
  const SQL = 'sql';  // ignore fid, just put in SQL
  //
  public function __construct($comparator, $value = null) {
    $this->comparator = $comparator;
    $this->value = $value;
  } 
  public function isValueArray() {
    switch ($this->comparator) {
      case self::IN:
      case self::BTW:
      case self::NIN:
        return true;
      default:
        return false;
    }
  }
  public function _toString($fid) {
  
    if (MyEnv::$IS_ORACLE) {
		$value = $this->value;
	}
	else {
		if ($this->isValueArray())
		  $value = array_map('addslashes', $this->value);
		else
		  $value = addslashes($this->value);
	}
    switch ($this->comparator) {
      case self::EQ:
        return "$fid='$value'";
      case self::EQN:
        return "$fid=$value";
      case self::NEQ:
        return "$fid<>'$value'";
      case self::NEQN:
        return "$fid<>$value";
      case self::LT:
        return "$fid<'$value'";
      case self::LTE:
        return "$fid<='$value'";
      case self::GT:
        return "$fid>'$value'";
      case self::GTE:
        return "$fid>='$value'";
      case self::LTN:
        return "$fid<$value";
      case self::LTEN:
        return "$fid<=$value";
      case self::GTN:
        return "$fid>$value";
      case self::GTEN:
        return "$fid>=$value";
      case self::BTW:
        return "$fid BETWEEN " . $value[0] . " AND " . $value[1];
      case self::SW:
        return "$fid LIKE '$value%'";
      case self::NSW:
        return "$fid NOT LIKE '$value%'";
      case self::CW:
        return "$fid LIKE '%$value%'";
      case self::NUL:
        return "$fid IS NULL";
      case self::NNUL:
        return "$fid IS NOT NULL";
      case self::IN:
        return "$fid IN ('" . implode("','", $value) . "')";
      case self::NIN:
        return "$fid NOT IN ('" . implode("','", $value) . "')";
      case self::DTEQ:
        return "SUBSTR($fid,1,10)='$value'";
      case self::REX:
        return stripslashes("$fid REGEXP $value");
      case self::NREX:
        return stripslashes("$fid NOT REGEXP $value");
      case self::SQL:
        return stripslashes($value);
    }
  }
  //
  static function equals($value) {
    return new static(self::EQ, $value);
  }
  static function equalsNumeric($value) {
    return new static(self::EQN, $value);
  }
  static function notEquals($value) {
    return new static(self::NEQ, $value);
  }
  static function notEqualsNumeric($value) {
    return new static(self::NEQN, $value);
  }
  static function lessThan($value) {
    return new static(self::LT, $value);
  }
  static function lessThanOrEquals($value) {
    return new static(self::LTE, $value);
  }
  static function greaterThan($value) {
    return new static(self::GT, $value);
  }
  static function greaterThanOrEquals($value) {
    return new static(self::GTE, $value);
  }
  static function lessThanNumeric($value) {
    return new static(self::LTN, $value);
  }
  static function lessThanOrEqualsNumeric($value) {
    return new static(self::LTEN, $value);
  }
  static function greaterThanNumeric($value) {
    return new static(self::GTN, $value);
  }
  static function greaterThanOrEqualsNumeric($value) {
    return new static(self::GTEN, $value);
  }
  static function startsWith($value) {
    return new static(self::SW, $value);
  }
  static function notStartsWith($value) {
    return new static(self::NSW, $value);
  }
  static function contains($value) {
    return new static(self::CW, $value);
  }
  static function isNull() {
    return new static(self::NUL);
  }
  static function isNotNull() {
    return new static(self::NNUL);
  }
  static function in($values) {  // string[] $values
    return new static(self::IN, $values);
  }
  static function notIn($values) {  // string[] $values
    return new static(self::NIN, $values);
  }
  static function between($from, $to) { 
    return new static(self::BTW, array($from, $to));
  }
  static function datePortionEquals($value) {
    return new static(self::DTEQ, $value);
  }
  static function regexp($value) {
    return new static(self::REX, $value);
  }
  static function notRegexp($value) {
    return new static(self::NREX, $value);
  }
  static function isNumeric() {
    return static::regexp("'^(-|\\\\+){0,1}([0-9]+\\\\.[0-9]*|[0-9]*\\\\.[0-9]+|[0-9]+)$'");
  }
  static function sql($value) {
    return new static(self::SQL, $value);
  }
  /**
   * @param string[] values [from, to]
   * @example [1, 5] for 1 <= x < 5
   */
  static function betweenNumeric($values) {
    $from = $values[0];
    $to = $values[1];
    if ($from !== null && $to !== null) 
      return CriteriaValues::_and(self::greaterThanOrEqualsNumeric($from), self::lessThanNumeric($to));
    else if ($from !== null) 
      return self::greaterThanOrEqualsNumeric($from);
    else
      return self::lessThanNumeric($to);
  }
  /**
   * @param string[] values [from, to]
   */
  static function betweenDates($values) {
    $from = $values[0];
    $to = $values[1];
    if ($from !== null && $to !== null) 
      return CriteriaValues::_and(self::greaterThanOrEquals($from), self::lessThan($to));
    else if ($from !== null) 
      return self::greaterThanOrEquals($from);
    else
      return self::lessThan($to);
  }
  /**
   * @param string[] $values [from, to]
   * @example [2, 10] for 2 <= age < 10
   *          [2, null] for age >= 2
   *          [null, 2] for age < 2
   */
  static function betweenAge($values, $startDate = null, $endDate = null) {
    $from = self::calcPastDate($values[0], 'y', $endDate);
    $to = self::calcPastDate($values[1], 'y', $startDate);
    if ($from !== null && $to !== null) 
      return CriteriaValues::_and(self::lessThanOrEquals($from), self::greaterThan($to));
    else if ($from !== null) 
      return self::lessThanOrEquals($from);
    else
      return self::greaterThan($to);
  }
  /**
   * @param string[] $values [from, to]
   * @example [2, 10] for age < 2 | age >= 10
   */
  static function splitAgeRange($values, $startDate = null, $endDate = null) {
    $from = self::calcPastDate($values[0], 'y', $endDate);
    $to = self::calcPastDate($values[1], 'y', $startDate);
    return CriteriaValues::_or(self::greaterThan($from), self::lessThanOrEquals($to));
  }
  /*
  static function betweenAge($values, $startDate = null, $endDate = null) {
    logit_r($values, $startDate);
    if ($startDate || $endDate) {
      if ($values[0] == null)
        $values[0] = 0;
      if ($values[1] == null) 
        $values[1] = 100;
      $from = ($endDate) ? subtractYears($endDate, $values[0]) : self::calcPastDate($values[0]);
      $to = ($startDate) ? subtractYears($startDate, $values[1]) : self::calcPastDate($values[1]);
    } else {
      $from = $values[0];
      $to = $values[1];
    }
    if ($from !== null && $to !== null) 
      return CriteriaValues::_and(self::lessThanOrEquals($from), self::greaterThan($to));
    else if ($from !== null) 
      return self::lessThanOrEquals($from);
    else
      return self::greaterThan($to);
  }
  */
  /**
   * @param string $years '2' for age >= 3
   */
  static function olderThan($years) {
    return self::betweenAge(array(intval($years), null));
  }
  /**
   * @param string[] $values [value, unit] allowable units 'y', 'm', 'w',' 'd'
   * @example (2, 'm') for age <= 2m
   */
  static function withinPast($values) {
    return self::greaterThanOrEquals(self::calcPastDate($values[0], $values[1]));
  }
  /**
   * @param string[] $values [value, ymd]
   * @example (2, 'm') for age > 2m
   */
  static function over($values) {
    return self::lessThan(self::calcPastDate($values[0], $values[1]));
  }
  /**
   * @param string $interval e.g. '5 MINUTE'
   */
  static function withinInterval($interval) {
	if (MyEnv::$IS_ORACLE) {
		return self::greaterThanNumeric("sysdate + interval $interval");
	}
	
    return self::greaterThanNumeric("DATE_SUB(NOW(), INTERVAL $interval)");
  }
  //
  static function _toSql($values) {
    $conds = array();
    foreach ($values as $fid => &$value)
      $conds[] = $value->_toString($fid);
    return implode(' AND ', $conds);
  }
  static function calcPastDate($value, $unit = 'y', $fromDate = null) {  
    if ($value !== null) {
      $y = ($unit == 'y') ? intval($value) : 0;
      $m = ($unit == 'm') ? intval($value) : 0;
      $d = ($unit == 'd') ? intval($value) : 0;
      if ($unit == 'w') 
        $d = intval($value) * 7;
      return pastDate($y, $m, $d, $fromDate);
    } else {
      return null;
    }
  } 
}
/**
 * e.g. CriteriaValues::_and(CriteriaValue::greaterThan('V'), criteriaValue::lessThan('W'))
 *      CriteriaValues::_and($rec->field, CriteriaValue::equals('A')) appends value to existing criteria value
 */
class CriteriaValues {
  //
  public $conj;
  public $values;
  //
  public function append($values) {
    $this->values = array_merge($this->values, $values);
    return $this;
  }
  public function __construct($conj, $values) {
    $this->conj = $conj;
    $this->values = $values;
  }
  public function _toString($fid) {
    $conds = array();
    foreach ($this->values as $value) {
      $conds[] = $value->_toString($fid);
    }
    return '(' . implode($this->conj, $conds) . ')';
  }
  public function getInnerValues() {
    $ivs = array();
    foreach ($this->values as $value) 
      $ivs[] = $value->value;
    return $ivs;
  }
  /**
   * @params CriteriaValue,..
   */
  static function _and() {
    $f = func_get_args();
    return self::create(' AND ', $f);
  }
  static function _or() {
    $f = func_get_args();
    return self::create(' OR ', $f);
  }
  static function _orArray($f) {
    return self::create(' OR ', $f);
  }
  private static function create($conj, $values) {
    if (current($values) instanceof CriteriaValue)  
      return new self($conj, $values);
    $value = array_shift($values);
    if ($value instanceof CriteriaValues) 
      return $value->append($values);
    if (count($values) > 1) 
      return new self($conj, array_shift($values));
    else 
      return current($values);
  }
}
/**
 * Standard authenticator
 */
class SqlAuthenticator {
  //
  static function authenticateUserGroupId($ugid, $forReadOnly = false) {
    LoginDao::authenticateUserGroupId($ugid);
  }
  static function authenticateClientId($cid, $forReadOnly = false) {
    LoginDao::authenticateClientId($cid);
  }
  static function authenticateUserId($id, $forReadOnly = false) {
    LoginDao::authenticateUserId($id);
  }
}
/**
 * Exceptions
 */
class SqlRecException extends Exception {
  public /*SqlRec*/ $rec;
  public function __construct($rec, $message) {
    $this->rec = $rec;
    $name = ($rec) ? $rec->getMyName() : '[null]';
    $this->message = "$name: $message";
  }
}
class ReadOnlySaveException extends SqlRecException {
  public function __construct($rec) {
    parent::__construct($rec, 'Cannot save read-only record');
  }
}
class ReadOnlyDeleteException extends SqlRecException {
  public function __construct($rec) {
    parent::__construct($rec, 'Cannot delete read-only record');
  }
}
class InvalidCriteriaException extends SqlRecException {
  public function __construct($criteria, $message) {
    parent::__construct($criteria, $message);
  }
}
/**
 * Temporary "static call" fn until upgrade to PHP 5.3
 * @example sc(get_class($rec), 'asActiveCriteria', $rec->clientId);
 */
function sc() {
  $args = func_get_args();
  return sc_($args);
}
function sc_($args) {
  $class = array_shift($args);
  $fn = array_shift($args);
  return call_user_func_array(array($class, $fn), $args);
}
require_once 'php/data/rec/sql/Auditing.php';