<?php
require_once 'config/MyEnv.php';
require_once "php/dao/_exceptions.php";
require_once 'inc/uiFunctions.php';
require_once "php/data/Services_JSON.php";
require_once 'php/data/rec/sql/dao/Logger.php';

function jsondecode($data) {
  $json = new Services_JSON();
  return $json->decode($data);
}
function jsonencode_old($data) {
  $json = new Services_JSON();
  return $json->encode($data);
}
function jsonencode($data) {
  $data = jeval($data);
  return json_encode($data);
}
function jeval($value) {
  if ($value instanceof Rec) {
    return $value->_toJsonObject();
  } else if (is_array($value)) {
    foreach ($value as $i => &$e) 
      $e = jeval($e);
  /* not sure it's worth it
  } else if (is_string($value)) {
    if (! isUtf($value))
      return null;
  */
  }
  return $value;
}
function isUtf($value) {
  return preg_match('//u', $value);
}
function formatSerializedObject($s) {
  switch (substr($s, 0, 1)) {
    case '[':
    case '{':
    case '"':
      return jsondecode($s);
    default:
      return $s;
  }
}
function formatFromSerializedObject($o) {
  return jsonencode($o);
}
if(!function_exists('get_called_class')) {
    class class_tools {
        static $i = 0;
        static $fl = null;

        static function get_called_class() {
            $bt = debug_backtrace();

            if(self::$fl == $bt[2]['file'].$bt[2]['line']) {
                self::$i++;
            } else {
                self::$i = 0;
                self::$fl = $bt[2]['file'].$bt[2]['line'];
            }

            $lines = file($bt[2]['file']);

            preg_match_all('
                /([a-zA-Z0-9\_]+)::'.$bt[2]['function'].'/',
                $lines[$bt[2]['line']-1],
                $matches
            );

            return $matches[1][self::$i];
        }
    }

    function get_called_class() {
        return class_tools::get_called_class();
    }
}
/**
 * StdClass getter: if $prop no found, returns $default instead of throwing error
 * Usage: get($address, 'country') instead of $address->country
 * @param object $obj
 * @param string $prop
 * @param(opt) mixed $default (null by default)
 * @return mixed
 */
function get($obj, $prop, $default = null) {
  return isset($obj->$prop) ? $obj->$prop : $default;
}
function gets($obj, $prop) {
  $array = get($obj, $prop);
  if ($array == null)
    $array = array();
  return $array;
}
/**
 * Supports $props of form 'obj.obj.prop'
 */
function get_recursive($obj, $prop, $default = null) {
  if (strpos($prop, '.') !== false) {
    $props = explode('.', $prop, 2);
    $o = get($obj, $props[0]);
    if ($o === null)
      return $default;
    else
      return get_recursive($o, $props[1], $default);
  } else {
    return get($obj, $prop, $default);
  }
}
function getr($obj, $prop, $default = null) {
  return get_recursive($obj, $prop, $default);
}
/**
 * Array getter: if $key not found, returns $default instead of throwing error
 * Usage: geta($array, $key) instead of $array[$key]
 * @param array $arr
 * @param string $key
 * @param(opt) mixed $default (null by default)
 * @return mixed
 */
function geta($arr, $key, $default = null) {
  return isset($arr[$key]) ? $arr[$key] : $default;
}
function seta(&$arr, $key, $value) {
  if (is_array($arr)) 
    $arr[$key] = $value;
  else
    $arr->$key = $value;
}
/**
 * @param mixed $e1
 * @param mixed $e2
 * @return int
 */
function icmp($e1, $e2) {
  if ($e1 != null && $e2 != null)
    return ($e1 == $e2) ? 0 : (($e1 > $e2) ? 1 : -1);
  else
    return ($e1 == null && $e2 == null) ? 0 : (($e2 == null) ? 1 : -1);
}
/**
 * Convert $e to an array, if it isn't already
 * @param string/array $e
 * @return array
 */
function arrayify(&$e) {
  if (! is_array($e))
    $e = array($e);
  return $e;
}
/**
 * @param array $a [1, [2, 3], 4, [5, 6, 7]]
 * @return array [1, 2, 3, 4, 5, 6, 7]
 */
function array_flatten($a) {
  foreach ($a as $k => $v)
    $a[$k] = (array) $v;
  return call_user_func_array('array_merge', $a);
}
/**
 * Insert $values[] into $array[] immediately before $offset while preserving keys of both
 */
function array_insert($array, $values, /*int*/$offset = false/*end*/) {
  if ($offset === false)
    return $array + $values;
  else
    return array_slice($array, 0, $offset, true) + $values + array_slice($array, $offset, NULL, true);  
}
/**
 * Get offset index of array key 
 */
function /*int*/key_offset(/*string*/$key, $array) {
  return array_search($key, array_keys($array));
}
/**
 * Convert all props of $a to arrays, if not already
 * @param array() $a
 */
function arrayifyEach(&$a) {
  if ($a)
    foreach ($a as $fid => &$prop)
      arrayify($prop);
}
/**
 * Push element into an inner array defined at $map[$key]
 * If $map[$key] undefined, this location will be auto-initialized
 * @param array $arr
 * @param string $key
 * @param mixed $e
 */
function push(&$arr, $key, &$e) {
  if (isset($arr[$key])) {
    $arr[$key][] = $e;
  } else {
    $arr[$key] = array($e);
  }
}
/**
 * @return array of arguments passed with null args removed
 */
function nonNulls() {
  $args = func_get_args();
  $a = array();
  foreach ($args as $arg)
    pushIfNotNull($a, $arg);
  return $a;
}
function pushIfNotNull(&$arr, &$e) {
  if ($e !== null)
    $arr[] = $e;
}
function pushIfNotEmpty(&$arr, &$e) {
  if (! empty($e))
    $arr[] = $e;
}
/**
 * Denullify string
 * @param string $s
 * @return string (not null)
 */
function denull($s) {
  return isNull($s) ? "" : $s;
}
/**
 * @param string $s 'line1\r\nline2'
 * @return array('line1','line2')
 */
function split_crlf($s) {
  return preg_split('/\n|\r\n?/', $s);
}
/**
 * True if array is associated {'key':value,..} not simple [value,..]
 * @param array $array
 * @return bool
 */
function is_assoc($array) {
  return (is_array($array) && 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));
}
/*
 * Given: $glue=','
 *        $pieces=['a'=>'apple','b'=>'bear']
 *        $glueKeyValue='='
 * Return "a=apple,b=bear"
 */
function implode_with_keys($glue, $pieces, $glueKeyValue = "=", $excludeNullValues = false) {
  $a = array();
  foreach ($pieces as $key => $value)
    if (! $excludeNullValues || $value !== null)
      $a[] = $key . $glueKeyValue . $value;
  return implode($glue, $a);
}
/*
 * Given: ['a','x','a','z']
 * Return ['a','x','z']
 * For simple arrays only; keys of original array not preserved
 */
function array_distinct($arr) {
  return array_keys(array_flip($arr));
}
/*
 * Assign keys to array of records using field value
 * @param array $recs array({'id':'a','name':'Apple'},{'id':'b','name':'Bear'},..)
 * @param string $fid 'id'
 * @return array array('a'=>{'id':'a','name':'Apple'},'b'=>{'id':'b','name':'Bear'},..)
 */
function array_keyify($recs, $fid) {
  $arr = array();
  foreach ($recs as $rec)
    $arr[$rec->$fid] = $rec;
  return $arr;
}
/*
 * Fetch single row
 * If nothing found, returns false
 * Returns [
 *    colName=>value,..
 *   ]
 */
function fetch($sql, $logging = true) {
  $res = query($sql, $logging);
  
  if (MyEnv::$IS_ORACLE) {
	//oci_execute($res);
	$rows = oci_fetch_all($res, $resultArray);
	if ($rows < 1) {
		return false;
	}
	
	$row = oci_fetch_array($res, OCI_ASSOC);
	oci_free_statement($res);
	return $row;
  }
  else {
	  if (mysql_num_rows($res) < 1) {
		return false;
	  }
	  return mysql_fetch_array($res, MYSQL_ASSOC);
  }
}
/*
 * Fetch single field
 * Assumes the first SELECT field, but can be overridden by either field position or name
 * If nothing found, returns false, else returns requested field's value
 */
function fetchField($sql, $field = 0, $logging = true) {
  $res = query($sql, $logging);
  //$field = null;
  
  Logger::debug('fetchField: field is ' . $field);
  
  if (MyEnv::$IS_ORACLE) {
	$field = strtoupper($field);
	//$rows = oci_fetch_all($res, $resultArray);
	/*$rows = oci_num_rows($res);
	
	Logger::debug('FetchFIELD: rows is ' . $rows);
	if ($rows < 1) {
		return false;
	}*/
	
	$resultArray = oci_fetch_array($res, OCI_BOTH);
	//$row = oci_fetch_all($stid, $res);
	
	
	//$row = oci_fetch_array($res, OCI_BOTH);
	Logger::debug('util::fetchField: Got this as a result: ');
	logit(print_r($resultArray, true));
	//echo 'fetchField: Our row is ';
	//print_r($resultArray);
	//echo 'fetchFIELD: returning ' . $resultArray[strtoupper($field)][0];
	oci_free_statement($res);
	return $resultArray[strtoupper($field)][0];
  }
  else {
	  if (mysql_num_rows($res) < 1) {
		return false;
	  }
	  $row = mysql_fetch_array($res, MYSQL_BOTH);
	  return $row[$field];
  }
}
/*
 * If key field supplied, returns [
 *    keyValue=>[field=>value,..],   // rec 1
 *    keyValue=>[field=>value,..],.. // rec 2
 *   ]
 * If key field not supplied, returns [
 *    [field=>value,..],  // rec 1
 *    [field=>value,..],  // rec 2
 *   ]
 */
function fetchArray($sql, $keyField = null, $logging = true) {
  $rows = array();
  $res = query($sql, $logging);
  
  if (!$res) {
	Logger::debug('Error in _util::fetchArray: Query failed: ' . $sql . '. Trace is ' . getStackTrace());  
  }
  
  if (MyEnv::$IS_ORACLE) {
	if (isnull($keyField)) {
		while ($row = oci_fetch_array($res, OCI_ASSOC)) {
			$rows[] = $row;
		}
	}
	else {
		while ($row = oci_fetch_assoc($res, OCI_ASSOC)) {
		  $rows[$row[$keyField]] = $row;
		}
	}
  }
  else {
	  if (isnull($keyField)) {
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
		  $rows[] = $row;
		}
	  } else {
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
		  $rows[$row[$keyField]] = $row;
		}
	  }
  }
  return $rows;
}
/*
 * If key field supplied:     returns [key=>value,key=>value,..]
 * If key field not supplied: returns [value,value,..]
 */
function fetchSimpleArray($sql, $valueField = 0, $keyField = null, $decrypt = false) {
  $a = array();
  $res = query($sql);
  if (isnull($keyField)) {
    if ($valueField == 0) {
      while ($row = mysql_fetch_array($res, MYSQL_NUM))
        $a[] = decr($row[0], $decrypt);
    } else {
      while ($row = mysql_fetch_array($res, MYSQL_ASSOC))
        $a[] = decr($row[$valueField], $decrypt);
    }
  } else {
    while ($row = Dao::fetchRowFromResource($res))  //mysql_fetch_array($res, MYSQL_ASSOC)) {
      $a[$row[$keyField]] = decr($row[$valueField], $decrypt);
  }
  return $a;
}
//
function p_($o = null) {
  static $last;
  echo '<pre>';
  if ($o == null) {
    $t = microtime(true);
    $micro = sprintf("%06d",($t - floor($t)) * 1000000);
    $d = new DateTime( date('Y-m-d H:i:s.'.$micro,$t) );
    print $d->format("Y-m-d H:i:s.u");
    if ($last) {
      $diff = round($t - $last, 2);
      echo " ($diff)";
    }
    $last = $t;
  }
  if ($o) {
    print_r($o);
  }
  echo '</pre>';
}
function p_r($o, $caption = null) {
  p_((($caption) ? "{" . $caption . "}\n" : '') . print_r($o, true) . (($caption) ? "{/$caption}" : ''));
}
function p_i($file) {
  //echo("INCLUDE: $file<br>");
}
/*
 * Cast fields from MySql default string
 */
function castAsInt(&$row, $fields) {
  foreach ($fields as &$field) {
    $row[$field] = intval($row[$field]);
  }
  return $row;
}
function castRowsAsInt(&$rows, $fields) {
  foreach ($rows as &$row) {
    castAsInt($row, $fields);
  }
  return $rows;
}
function castAsInformalTime(&$row, $fields) {
  foreach ($fields as &$field) {
    $row[$field] = formatInformalTime($row[$field]);
  }
  return $row;
}
function castRowsAsInformalTime(&$rows, $fields) {
  foreach ($rows as &$row) {
    castAsInformalTime($row, $fields);
  }
  return $rows;
}
function castAsDate(&$row, $fields) {
  foreach ($fields as &$field) {
    $row[$field] = formatDate($row[$field]);
  }
  return $row;
}
function castRowsAsDate(&$rows, $fields) {
  foreach ($rows as &$row) {
    castAsDate($row, $fields);
  }
  return $rows;
}
// Returns resource for SQL statements and an associative array if it's Oracle.
function query($sql, $logging = true) {
  Logger::debug('sec/php/dao/_util.php: Entered _util query function with query ' . $sql);
  //Logger::debug('_util: Trace is ' . print_r(debug_backtrace(), true));
  if ($logging) logit($sql);
  if (MyEnv::$IS_ORACLE) {
	try {
		
		$conn = openOracle();
		
		$sql = str_replace('`', "'", $sql);
		
		//Logger::debug('sec/php/dao/_util.php: Util Query: Doing query ' . $sql . '. Trace is ' . getStackTrace());
		
		/*
			This works!
			$stid = oci_parse($conn, $sql);
			oci_execute($stid);
			echo 'We have ' . oci_fetch_all($stid, $res) . ' rows!';
		
		*/
		
		try {
			$res = oci_parse($conn, $sql);
			$executed = oci_execute($res);
			
			if (!$executed) {
				$err = oci_error($res);
				throw new RuntimeException('parse / execute failed: ' . $err['message']);
			}
		}
		catch (Exception $e) {
			Logger::debug('QUERY ERROR in _util.php: ' . $e->getMessage() . '. Query is ' . $sql);
		}
		//oci_free_statement($res);
		oci_close($conn);
	}
	catch (Exception $e) {
		logit('ERROR: ' . $e->getMessage() . ' in ' . $e->getFilename() . ' on line ' . $e->getLine());
		throw new RuntimeException('Could not execute the Oracle query: ' . $e->getMessage());
	}
  }
  else {
	  Logger::debug('sec/php/dao/_util: We are NOT using oracle. Run SQL version.');
	  $conn = open();
	  $res = mysql_query($sql) or die("Query failure: " . mysql_error());
  }
  //echo 'query: Returning a result!';
  return $res;
}
function queryNoDie($sql) {
  logit($sql);
  $conn = open();
  $res = mysql_query($sql);
  close($conn);
  return $res;
}
function batchopen() {  // returns $conn
  return open();
}
function batchquery($sql) {  // returns $res
  return query($sql);  //, IN_BATCH);
}
function batchfetch($sql) {
  return fetch($sql);  //, IN_BATCH);
}
function batchclose($conn) {
  close($conn);
}

// Returns number of affected rows
function update($sql) {
  logit($sql);
  $conn = open();
  $res = mysql_query($sql) or die("Query failure: " . mysql_error());
  $rows = mysql_affected_rows();
  close($conn);
  return $rows;
}

// Returns last inserted ID
function insert($sql) {
  logit($sql);
  $conn = open();
  $res = mysql_query($sql);
  if (! $res) {
    throwSqlException(mysql_error(), mysql_errno($conn));
  }
  $id = mysql_insert_id();
  return $id;
}

function throwSqlException($msg, $code) {
  switch ($code) {
  case 1062:
    throw new DuplicateInsertException($msg, $code);
    break;
  }
  throw new SqlException($msg, $code);
}
function encr($field) {
  if (isNull($field))
    return "null";
  return quote(MyCrypt_Auto::encrypt($field), true);
}
function decr($value, $decrypt = true) {
  return ($decrypt) ? MyCrypt_Auto::decrypt($value) : $value; 
}
function quote($field, $escape = false) {
   Logger::debug('dao _util.php::quote: Got field ' . gettype($field) . ' ' . $field);
  //$field = str_replace(array("\r", "\n"), " ", $field);
  
  /*In oracle, escaping strings is done like "O''Connor", whereas in SQL it's "O\'Connor"
  
  We must update this to support this. The below screws things up.*/
  
  $value = null;
  
  if ($escape) {
	  if (MyEnv::$IS_ORACLE) {
		$value = $field;
		$value = str_replace("'", "''", $value);
	  }
	  else {
		$value = addslashes($field);
	  }
  }
  else {
	  $value = $field;
  }
  
  Logger::debug(' _util.php::quote: Returning ' . ((isNull($field)) ?  "null" : "'" . $value . "'"));
  
  //$value = ($escape) ? addslashes($field) : $field; //Old SQL-only code
  return (isNull($field)) ?  "null" : "'" . $value . "'";
}
function gquote($obj, $prop, $escape = false) {
  return quote(get($obj, $prop), $escape);
}
function dquote($s) {
  return "\"" . $s . "\"";
}
function squote($s) {
  return "'" . $s . "'";
}
function asBool($field) {
  return ($field == '1') ? true : false;
}
function asBoolInt($field) {
  return asBool($field) ? 1 : 0;
}
function asBoolNull($field) {
  return asBool($field) ? 1 : null;
}

// Converts boolean to SQL tinyint
function toBoolInt($field) {
  return ($field && $field !== 'false' && $field !== '0') ?  1 : 0;
}

// Returns '1966-11-23 13:23:22' (SQL format)
function now() {
  return quote(date("Y-m-d H:i:s"));
}
function nowNoQuotes() {
  return date("Y-m-d H:i:s");
}
function nowShort() {
  return quote(date("Y-m-d"));
}
function nowShortNoQuotes() {
  return date("Y-m-d");
}
function nowTime() {
  return date("H:i:s");
}
function nowYYYYMMDD() {
  return date('Ymd');
}

// Returns 23-Nov-1966 01:23PM
function nowTimestamp() {
  return date("d-M-Y, g:iA");
}

function quoteDate($date) {
  return (isNull($date)) ? "null" : quote(dateToString($date));
}

// Returns 1966-11-23 (SQL format)
function dateToString($date) {
  if ($date == null)
    return date("Y-m-d");
  return unixToSqlDate(strtotime($date));
}
function unixToSqlDate($i) {
  return date("Y-m-d", $i);
}
function datetimeToString($date, $adj = 0) {
  return date("Y-m-d H:i:s", strtotimePlus($date, $adj));
}
function unixToSqlTime($i, $adj = 0) {
  if ($adj)
    $i = unixPlusHours($i, $adj);
  return date("Y-m-d H:i:s", $i);
}

// Date comparison routines
function isTodayOrFuture($date) {
  return (compareDates($date, date("Y-m-d")) >= 0);
}
function isTodayOrPast($date) {
  return (compareDates($date, date("Y-m-d"), true) <= 0);
}
function isPast($date) {
  return (compareDates($date, date("Y-m-d")) < 0);
}
function isToday($date) {
  return (compareDates($date, date("Y-m-d"), true) == 0);
}
function daysUntil($date, $noNeg = false) {
  $dt0 = strtotime(date("Y-m-d"));
  $dt1 = strtotime(dateToString($date));
  $days = round(($dt1 - $dt0) / 86400);
  return ($noNeg && $days < 0) ? null : $days;
}
function daysFrom($date, $noNeg = false) {
  if ($date) {
    $dt0 = strtotime(date("Y-m-d"));
    $dt1 = strtotime(dateToString($date));
    $days = round(($dt0 - $dt1) / 86400);
    return ($noNeg && $days < 0) ? null : $days;
  }
}
function daysBetween($from, $to) {
  $n = strtotime($to);
  $b = strtotime($from);
  return floor(($n - $b) / 86400);
}
function weeksBetween($from, $to) {
  $n = strtotime($to);
  $b = strtotime($from);
  return floor(($n - $b) / 604800);
}
function isWithin($date, $from, $to) {
  if ($to && $from) 
    return $date >= $from && $date <= $to;
  if ($to)
    return $date <= $to;
  if ($from)
    return $date >= $from;
}
/**
 * @return -1 if $date1 < $date2
 *         +1 if $date1 > $date2
 *          0 if $date1 == $date2
 */
function compareDates($date1, $date2, $ignoreTime = false) {  
  if ($ignoreTime) {
    $date1 = dateToString($date1);
    $date2 = dateToString($date2);
  }
  $d1 = strtotime($date1);
  $d2 = strtotime($date2);
  if ($d1 < $d2) {
    return -1;
  } else if ($d1 > $d2) {
    return 1;
  } else {
    return 0;
  }
}
/**
 * @param int $days/$months/$years in the future
 * @param 'Y-m-d' $date (optional, default now)
 * @returns 'Y-m-d'
 */
function futureDate($days = 0, $months = 0, $years = 0, $date = null, $fmt = "Y-m-d") {
  if ($date == null)
    $date = date("Y-m-d");
  $dt = strtotime($date);
  $dt = mktime(0, 0, 0, date("n", $dt) + $months, date("j", $dt) + $days, date("Y", $dt) + $years);
  return date($fmt, $dt);
}
/**
 * @param int $years/$months/$days in the past
 * @param 'Y-m-d' $date (optional, default now)
 * @returns 'Y-m-d'
 */
function pastDate($years = 0, $months = 0, $days = 0, $date = null) {
  if ($date == null)
    $date = date("Y-m-d");
  $dt = strtotime($date);
  $dt = mktime(0, 0, 0, date("n", $dt) - $months, date("j", $dt) - $days, date("Y", $dt) - $years);
  return date("Y-m-d", $dt);
}
function subtractYears($date, $years) {
  return pastDate($years, null, null, $date);
}
function subtractOffset($date, $offset) {
  $dt = strtotime($date) - $offset;
  return date("Y-m-d", $dt);
}

// Adjust time for user's timezone (apply EST adjustment assigned to user group)
function strtotimeAdjusted($date) {
  $estAdjust = getMyEstAdjust();
  if ($estAdjust != 0) {
    $dt = strtotimePlus($date, $estAdjust);
  } else {
    $dt = strtotime($date);
  }
  return $dt;
}
function getMyEstAdjust() {
  global $login;
  if ($login)
    return $login->getEstAdjust();
  $sess = PortalSession::get();
  if ($sess)
    return $sess->estAdjust;
  return 0;
}
function strtotimePlus($date, $adj) {
  $dt = strtotime($date);
  return unixPlusHours($dt, $adj);
}
function unixPlusHours($dt, $adj) {
  return mktime(date("H", $dt) + $adj, date("i", $dt), date("s", $dt), date("n", $dt), date("j", $dt), date("Y", $dt));
}
/**
 * Calculate chronological age
 * @param string $birth
 * @param-opt string $from (default to current date)
 * @return ['y'=>years,'m'=>months,'d'=>days]
 */
function chronAge($birth, $from = 'now') {
  $n = ymd(strtotime($from));
  $b = ymd(strtotime($birth));
  if ($n['d'] < $b['d']) {
    $n['d'] += 30;
    $n['m']--;
  }
  if ($n['m'] < $b['m']) {
    $n['m'] += 12;
    $n['y']--;
  }
  return array(
    'y' => $n['y'] - $b['y'],
    'm' => $n['m'] - $b['m'],
    'd' => $n['d'] - $b['d']);
}
function ymd($time) {
  return array(
    'y' => date('Y', $time),
    'm' => date('n', $time),
    'd' => date('j', $time));
}
function formatUnixTimestamp($ts) {
  return date("d-M-Y", $ts);
}
function nowUnix() {
  return strToTime(nowShortNoQuotes());
}
/**
 * @param string $date
 * @return 'Today (Sun), 2:30PM'
 */
function formatInformalTimeDay($date) {
  return formatInformalDate($date) . date(" (D), g:iA", strtotimeAdjusted($date));
}
function formatInformalTimeNoAdj($date) {
  $d = formatInformalDay($date);
  $t = date("g:iA", strtotime($date));
  if ($t !== '12:00AM')
    $d .= ', ' . $t;
  return $d;
}
function formatInformalDay($date) {
  return formatInformalDate($date) . date(" (D)", strtotime($date));
}
function formatNowInformal() {
  $date = nowTimestamp();
  return formatDate($date) . date(", g:iA", strtotimeAdjusted($date));
}
function calcShortDate($text) {
  if ($text == null) {
    return null;
  }
  if ($text == "on an unknown date") {
    return "unknown";
  }
  if (substr($text, 0, 3) == "on ") {
    return formatDate(substr($text, 3));
  }
  if (substr($text, 0, 3) == "in ") {
    if (strlen($text) == 7) {
      return substr($text, 3);
    }
    return substr($text, 3, 3) . " " . substr($text, -4);
  }
}

/**
 * @param string $date from SQL
 * @return string '23-Nov-1996'
 */
function formatDate($date) {
  if (is_null($date)) {
    return null;
  }
  return date("d-M-Y", strtotime($date));
}
/**
 * @param string $date '23-Nov-1996'
 * @return string '1996-11-23' (SQL format)
 */
function formatFromDate($date) {
  if (empty($date))
    return null;
  else
    return dateToString($date);
}
/**
 * @param string $date from SQL
 * @return 'Today' or '23-Nov-2009'
 */
function formatInformalDate($date, $withDay = false) {
  $x = strtotime($date);
  $d = date("d-M-Y", $x);
  $today = date("d-M-Y");
  $yester = date("d-M-Y", mktime(0, 0, 0, date("n"), date("j") - 1, date("Y")));
  if ($today == $d) {
    return "Today";
  } else if ($yester == $d) {
    return "Yesterday";
  } else {
    return $withDay ? date("D", $x) . ', ' . $d : $d;
  }
}
/**
 * @param string $date
 * @return 'Today, 2:30PM'
 */
function formatInformalTime($date, $adjusted = true) {
  $ts = ($adjusted) ? strtotimeAdjusted($date) : strtotime($date);
  return formatInformalDate($date) . date(", g:iA", $ts);
}
/**
 * Format approximate date based upon time setting
 * @param string $datetime from SQL
 * @return '23-Nov-2010' from '2010-11-23 00:00:00'
 * @return 'Nov 2010'    from '2010-11-01 01:00:00'
 * @return '2010'        from '2010-01-01 02:00:00'
 * @return 'unknown'     from '1970-01-01 03:00:00'
 */
function formatApproxDate($datetime) {
  if ($datetime) {
    $ts = strtotime($datetime);
    $time = date('H:i:s', $ts);
    if ($time == '01:00:00')
      return date('M Y', $ts);
    if ($time == '02:00:00')
      return date('Y', $ts);
    if ($time == '03:00:00') {
      $date = date('Y-m-d', $ts);
      if ($date == '1970-01-01')
        return 'unknown';
    }
    return date("d-M-Y", $ts);
  }
}

function formatApproxDateTime($datetime) {
  if ($datetime) {
    $ts = strtotime($datetime);
    $time = date('H:i:s', $ts);
    if ($time == '01:00:00')
      return date('M Y', $ts);
    if ($time == '02:00:00')
      return date('Y', $ts);
    if ($time == '03:00:00') {
      $date = date('Y-m-d', $ts);
      if ($date == '1970-01-01')
        return 'unknown';
    }
    if ($time == '00:00:00') {
      return date("d-M-Y", $ts);
    }
    return date("d-M-Y g:iA", $ts);
  }
}
/**
 * Format approximate date based upon time setting
 * @param string $datetime from SQL
 * @return '20101123' from '2010-11-23 00:00:00'
 * @return '201011'   from '2010-11-01 01:00:00'
 * @return '2010'     from '2010-01-01 02:00:00'
 */
function formatApproxDateCCYYMMDD($datetime) {
  $ts = strtotime($datetime);
  $time = date('H:i:s', $ts);
  if ($time == '01:00:00')
    return date('Ym', $ts);
  if ($time == '02:00:00')
    return date('Y', $ts);
  return date('Ymd', $ts);
}
/**
 * @param string $s
 * @return '2010-11-23 00:00:00' from '23-Nov-2010'
 * @return '2010-11-01 01:00:00' from 'Nov 2010'
 * @return '2010-01-01 02:00:00' from '2010'
 * @return '1970-01-01 03:00:00' from 'unknown'
 */
function formatFromApproxDate($s) {
  if (empty($s))
    return null;
  if ($s == 'unknown')
    return formatAsUnknownDate();
  switch (strlen($s)) {
    case 4:
      $s = "01-01-$s 02:00:00";
      break;
    case 8:
      $s = "$s 01:00:00";
      break;
    default:
      $s = "$s 00:00:00";
  }
  $ts = strtotime($s);
  return date("Y-m-d H:i:s", $ts);
}
function formatAsUnknownDate() {
  return '1970-01-01 03:00:00';
}
/**
 * @param string $s
 * @return '2010-11-23 00:00:00' from 'on November 23, 2010'
 * @return '2010-11-01 01:00:00' from 'in November of 2010'
 * @return '2010-01-01 02:00:00' from 'in 2010'
 * @return '1970-01-01 03:00:00' from 'on an unknown date'
 */
function formatFromLongApproxDate($s) {
  if (strpos('unknown', $s) !== false)
    return formatAsUnknownDate();
  $s = str_replace('on ', '', $s);
  $s = str_replace('in ', '', $s);
  $s = str_replace('of ', '', $s);
  $a = explode(' ', $s);
  if (count($a) > 1)
    $a[0] = substr($a[0], 0, 3);
  $s = implode(' ', $a);
  return formatFromApproxDate($s);
}
/**
 * @param string $datetime from SQL
 * @return 'on November 23, 2010' from '2010-11-23 00:00:00'
 * @return 'in November of 2010'  from '2010-11-01 01:00:00'
 * @return 'in 2010'              from '2010-01-01 02:00:00'
 * @return 'on an unknown date'   from '1970-01-01 03:00:00'
 */
function formatLongApproxDate($datetime) {
  if ($datetime) {
    $ts = strtotime($datetime);
    $time = date('H:i:s', $ts);
    if ($time == '01:00:00')
      return date('\i\n F \o\f Y', $ts);
    if ($time == '02:00:00')
      return date('\i\n Y', $ts);
    if ($time == '03:00:00') {
      $date = date('Y-m-d', $ts);
      if ($date == '1970-01-01')
        return 'on an unknown date';
    }
    return date('\o\n F j, Y', $ts);
  }
}
/**
 * Format date with optional time
 * @param string $datetime from SQL
 * @return '23-Nov-2010 04:00PM' from '2010-11-23 16:00:01' (:01 second indicates time entry)
 * @return '23-Nov-2010'         from '2010-11-23 00:00:00'
 */
function formatDateTime($datetime) {
  $ts = strtotime($datetime);
  return unixToTime($ts);
}
function unixToTime($ts) {
  $time = date('H:i:s', $ts);
  if ($time == '00:00:00')
    return date("d-M-Y", $ts);
  return date("d-M-Y h:iA", $ts);
}
/**
 * @param string $s
 * @return '2010-11-23 00:00:00' from '23-Nov-2010'
 * @return '2010-11-23 16:00:01' from '23-Nov-2010 04:00PM' (:01 second indicates time entry)
 */
function formatFromDateTime($s) {
  if (empty($s))
    return null;
  switch (strlen($s)) {
    case 11:
      $s = "$s 00:00:00";
      break;
    case 19:
      $s = substr($s, 0, -2) . ':01' . substr($s, -2);
      break;
  }
  $ts = strtotime($s);
  return date("Y-m-d H:i:s", $ts);
}

/**
 * Extract time from SQL date, if not default 00:00:00
 * @param string $date
 * @return string '01:30PM' (or '' if 00:00:00)
 */
function formatTime($date) {
  $time = date('H:i:s', strtotime($date));
  if ($time != '00:00:00')
    return date('g:i A', strtotimeAdjusted($date));
}

// Returns 23-Nov-1966, 1:23PM
function formatNowTimestamp() {
  return date("d-M-Y, g:iA", strtotimeAdjusted(nowTimestamp()));
}

// Returns 23-Nov-1966, 1:23PM
function formatTimestamp($date) {
  if ($date) return date("d-M-Y, g:iA", strtotimeAdjusted($date));
}

// Returns 09/30, 10:22PM
function formatShortTimestamp($date) {
  if ($date) {
    return formatShortDate($date) . ", " . date("g:iA", strtotime($date));  // don't need to strtotimeAdjust here because it was already adjusted in the JSON via formatTimestamp
  }
}

// Returns 23-Nov-1966 01:23PM (if time provided)
function formatTimestampOptional($date) {
  $dt = date("H:i:s", strtotime($date));
  if ($dt == "00:00:00") {
     return formatDate($date);
  } else {
    return formatTimestamp($date);
  }
}

function formatShortDate($date) {
  if ($date) {
    $ts = strtotime($date);
    $d = date("m/d", $ts);
    if ($d == date("m/d")) {
      $d = "Today";
    }
    return $d;
  }
}
function formatMDY($date) {  // 11/23/66
  if ($date)
    return date("m/d/y", strtotime($date));
}
function formatLongDateOnly($date) {  // September 3, 2012
  if ($date)
    return date("F j, Y", strtotime($date));
}
function formatLongDate($date) {  // Tuesday, September 3, 2012
  if ($date)
    return date("l, F j, Y", strtotime($date));
}
// Returns 11/23/1966, original console format for DOB
function formatConsoleDate($date) {
  if ($date)
    return date("m/d/Y", strtotime($date));
}

// Returns Thursday, 23-Nov-1966
function formatFullDate($date) {
  return date("l, d-M-Y", strtotime($date));
}

function getWorkingDays($startDate,$endDate,$holidays){
    // do strtotime calculations just once
    $endDate = strtotime($endDate);
    $startDate = strtotime($startDate);


    //The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
    //We add one to inlude both dates in the interval.
    $days = ($endDate - $startDate) / 86400 + 1;

    $no_full_weeks = floor($days / 7);
    $no_remaining_days = fmod($days, 7);

    //It will return 1 if it's Monday,.. ,7 for Sunday
    $the_first_day_of_week = date("N", $startDate);
    $the_last_day_of_week = date("N", $endDate);

    //---->The two can be equal in leap years when february has 29 days, the equal sign is added here
    //In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
    if ($the_first_day_of_week <= $the_last_day_of_week) {
        if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
        if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
    }
    else {
        // (edit by Tokes to fix an edge case where the start day was a Sunday
        // and the end day was NOT a Saturday)

        // the day of the week for start is later than the day of the week for end
        if ($the_first_day_of_week == 7) {
            // if the start date is a Sunday, then we definitely subtract 1 day
            $no_remaining_days--;

            if ($the_last_day_of_week == 6) {
                // if the end date is a Saturday, then we subtract another day
                $no_remaining_days--;
            }
        }
        else {
            // the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
            // so we skip an entire weekend and subtract 2 days
            $no_remaining_days -= 2;
        }
    }

    //The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
//---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
   $workingDays = $no_full_weeks * 5;
    if ($no_remaining_days > 0 )
    {
      $workingDays += $no_remaining_days;
    }

    //We subtract the holidays
    if ($holidays) {
      foreach($holidays as $holiday){
          $time_stamp=strtotime($holiday);
          //If the holiday doesn't fall in weekend
          if ($startDate <= $time_stamp && $time_stamp <= $endDate && date("N",$time_stamp) != 6 && date("N",$time_stamp) != 7)
              $workingDays--;
      }
    }

    return $workingDays;
}

function isNull($field) {
  return (trim($field) == "");
}

// Return database based on current environment
/*
function dbFromEnv() {
  static $db;
  if ($db == null) {
    require_once "php/data/LoginSession.php";
    if (isset($_SERVER['HTTP_HOST'])) {
      switch (LoginSession::getEnv()) {
        case LoginSession::ENV_PRODUCTION:
          $db = 'cert';
          break;
        case LoginSession::ENV_LOCAL:
        case LoginSession::ENV_TEST:
        case LoginSession::ENV_PAPYRUS_LOCAL:
        case LoginSession::ENV_PAPYRUS_TEST:
          $db = 'emrtest';
          break;
        case LoginSession::ENV_PAPYRUS_PROD:
          $db = 'cert';
          break;
      }
    } else {
      global $myHost;
      if ($myHost == "test") {
        $db = "emrtest";
      } else if ($myHost == "npp") {
        $db = "ctnpp";
      } else if ($myHost == "prod") {
        $db = "cert";
      } else {
        $db = "emrtest";
      }
    }
  }
  return $db;
}
*/
/*
function dbCredFromEnv() {
  static $cred;
  if ($cred == null) {
    require_once "php/data/LoginSession.php";
    $cred = new stdClass();
    if (isset($_SERVER['HTTP_HOST'])) {
      switch (LoginSession::getEnv()) {
        case LoginSession::ENV_PRODUCTION:
        case LoginSession::ENV_LOCAL:
        case LoginSession::ENV_TEST:
        case LoginSession::ENV_PAPYRUS_LOCAL:
        case LoginSession::ENV_PAPYRUS_TEST:
        case LoginSession::ENV_PAPYRUS_PROD:
          $cred->server = 'localhost';
          $cred->user = 'webuser';
          $cred->pw = 'click01';
          break;
      }
    } else {
      global $myHost;
      if ($myHost == "prod") {
        $cred->server = 'localhost';
        $cred->user = 'webuser';
        $cred->pw = 'click01';
      } else {
        $cred->server = 'localhost';
        $cred->user = 'webuser';
        $cred->pw = 'click01';
      }
    }
  }
  return $cred;
}
*/
function open() {
  //echo 'Connecting to ' . MyEnv::$DB_NAME . ' on ' . MyEnv::$DB_SERVER . ' ' .  MyEnv::$DB_USER . ' ' . MyEnv::$DB_PW;
  //throw new RuntimeException('Cheezit time! ' . print_r(debug_backtrace()));
  $conn = mysql_connect(MyEnv::$DB_SERVER, MyEnv::$DB_USER, MyEnv::$DB_PW) or die("Connection failure: " . mysql_error());
  mysql_select_db(MyEnv::$DB_NAME) or die("Database select failure: " . mysql_error());
  return $conn;
}

function openOracle() {
	Logger::debug('openOracle: entered');
	Logger::debug('openOracle: Login ' . MyEnv::$DB_USER . ' ' . MyEnv::$DB_PW . ' to server ' . MyEnv::$DB_SERVER);
	try {
		$conn = oci_connect(MyEnv::$DB_USER, MyEnv::$DB_PW, MyEnv::$DB_SERVER . '/' . MyEnv::$DB_PROC_NAME);
	}
	catch (Exception $e) {
		throw new RuntimeException('Could not connect to the oracle database ' . MyEnv::$DB_SERVER . ' with proc name ' . MyEnv::$DB_PROC_NAME . ': ' . $e->getMessage());
	}
	//echo 'Returning a connection.';
	return $conn;
}

function close($conn) {
  if (MyEnv::$IS_ORACLE) {
  	return oci_close($conn);
  }
  
  return mysql_close($conn);
}

function logit($msg) {
  Logger::debug($msg);
}

function logit_r($o, $caption = null) {
  Logger::debug_r($o, $caption);
}

function getStackTrace() {
	ob_start();
	debug_print_backtrace();
	$trace = ob_get_contents();
	ob_end_clean();
	
	return $trace;
}

function convert_line_breaks($string, $line_break=PHP_EOL) {
    $patterns = array(    
                        "/(<br>|<br \/>|<br\/>)\s*/i",
                        "/(\r\n|\r|\n)/"
    );
    $replacements = array(    
                            PHP_EOL,
                            $line_break
    );
    $string = preg_replace($patterns, $replacements, $string);
    return $string;
}

/*
 * Base class for exceptions meant to be displayed to user
 * All others are logged and user gets generic message
 */
class DisplayableException extends Exception {
  public $data;
  public function __construct($message, $data = null) {
    $this->message = $message;
    $this->data = $data;
  }
}