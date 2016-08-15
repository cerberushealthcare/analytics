<?php
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
/**
 * Supports $props of form 'obj.obj.prop'
 */
function getr($obj, $prop, $default = null) {
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
/**
 * Array getter: if $key not found, returns $default instead of throwing error
 * Usage: geta($array, $key) instead of $array[$key]
 * @param array $arr
 * @param string $key
 * @param(opt) mixed $default (null by default)
 * @return mixed
 */
function geta(&$arr, $key, $default = null) {
  return isset($arr[$key]) ? $arr[$key] : $default;
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
  p_((($caption) ? "{" . $caption . "}" : '') . print_r($o, true) . (($caption) ? "{/$caption}" : ''));
}
function get_current_url() {
  $a = array('http');
  if (geta($_SERVER, 'HTTPS') == 'on')
    $a[] = 's';
  $a[] = '://';
  if (geta($_SERVER, 'SERVER_PORT') != '80')
    $a[] = $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
  else
    $a[] = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
  return implode('', $a);
}