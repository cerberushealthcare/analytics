<?php

  // TODO array_walk + implode thruout  

define('C', ',');

function cb($x) {
  return '{' . $x . '}';
}

function q($x, $addSlashes = true) {
  if (is_null($x)) return 'null';
  $v = ($addSlashes) ? addSlashes($x) : $x;
  return '"' . $v. '"';
}

// For applying to array_walk 
function q2(&$value) {
  $value = q($value);
}
function out2(&$value) {
  $value = $value->out();
}
function out3(&$value, $key) {
  $value = qqj($key, $value);
}
function out4(&$value, $key) {
  $value = qqo($key, $value);
}
function out5(&$value, $key) {
  if (substr($value, 0, 1) == '[') {
    $value = qqo($key, $value);
  } else {
    $value = qq($key, $value);
  }
}

// String - String
function qq($k, $v, $addSlashes = true) {
  return q($k) . ':' . q($v, $addSlashes);
}
 
function aqq($out, $k, $v) {
  return nappend($out, q($k) . ':' . q($v));
}

function nqq($out, $k, $v) {  // ignore null version
  if ($v == null) {
    return $out;
  }
  return aqq($out, $k, $v);
}

// String - Associative array (key=string, value=JSON object)
function qqaa($k, $a) {
  $c = count($a);
  if ($c == 0) {
    return qqo($k, 'null');
  }
  $i = 0;
  $s = '{';
  foreach ($a as $key => $val) {
    if ($val != null) {
      if ($i > 0) $s .= ',';
      $s .= q($key) . ':' . $val->out();
      $i++;
    }
  }
  return qqo($k, $s . '}');
}

function nqqaa($out, $k, $a) {  // ignore null version
  if (is_null($a) || count($a) == 0) {
    return $out;
  }
  return nappend($out, qqaa($k, $a));
}

// String - Associative array (key=string, value=array of JSON objects)
function qqaaa($k, $a) {
  $c = count($a);
  if ($c == 0) {
    return qqo($k, 'null');
  }
  $i = 0;
  $s = '{';
  foreach ($a as $key => $val) {
    if ($i > 0) $s .= ',';
    $s .= q($key) . ':' . arr($a);
    $i++;
  }
  return qqo($k, $s . '}');
}

// Simple array of JSON objects
function arr($a) {
  if ($a == null || count($a) == 0) {
    return 'null';
  } else {
    return simpleOutArray($a);
  }
}

// Associated array of JSON objects
function aarr($a) {
  if ($a == null || count($a) == 0) {
    return 'null';
  } else {
    array_walk($a, 'out3');
    return '{' . implode(',', $a) . '}';
  }
}

// Associated array of objects (no quotes around values)
function aarro($a) {
  if ($a == null || count($a) == 0) {
    return 'null';
  } else {
    array_walk($a, 'out4');
    return '{' . implode(',', $a) . '}';
  }
}

// Associated array of mixed objects (no quotes around array values)
function aarrm($a) {
  if ($a == null || count($a) == 0) {
    return 'null';
  } else {
    array_walk($a, 'out5');
    return '{' . implode(',', $a) . '}';
  }
}

// String - Array of JSON objects
function qqa($k, $a) {
  return qqo($k, arr($a));
}

function nqqa($out, $k, $a) {  // ignore null version
  if (is_null($a) || count($a) == 0) {
    return $out;
  }
  return nappend($out, qqa($k, $a));
}

// String - Array of integers
function qqai($k, $a) {
  if (count($a) == 0) {
    $s = '[]';
  } else {
    $s = '[' . implode(',', $a) . ']';
  }
  return qqo($k, $s);
}

function nqqai($out, $k, $a) {  // ignore null version
  return nappend($out, qqai($k, $a));
}

// String - Array of strings
function qqas($k, $a) {
  if ($a == null) return q($k) . ':null';
  array_walk($a, 'q2');
  return qqai($k, $a);
}

function nqqas($out, $k, $a) {  // ignore null version
  return nappend($out, qqas($k, $a));
}

/*
 * Returns [$id:$o->out(),..]
 * 
 */
function assocOutArray($arr) {
  if ($arr == null) return '{}';
  array_walk($arr, 'out2');
  return '{' . implode_with_keys(',', $arr, ':') . '}';
}
/*
 * Returns [$o->out(),..]
 */
function simpleOutArray($arr) {
  if ($arr == null) return '[]';
  array_walk($arr, 'out2');
  return '[' . implode(',', $arr) . ']';
}
/*
 * Returns [$o,..]
 */
function simpleArray($arr) {
  if ($arr == null) return '[]';
  return '[' . implode(',', $arr) . ']';
}

// String - JSON object
function qqj($k, $o) {
  if ($o == null) {
    return q($k) . ':null';
  } else {
    return q($k) . ':' . $o->out();
  }
}

function nqqj($out, $k, $o) {  // ignore null version
  if (is_null($o)) {
    return $out;
  }
  return nappend($out, qqj($k, $o));
}

// String - Object (i.e., output without quotes)
function qqo($k, $o) {
  if ($o === null) $o = 'null';
  if ($o === false) $o = '0';
  return q($k) . ':' . $o;
}

function aqqo($out, $k, $o) {
  return nappend($out, qqo($k, $o));
}
function nqqo($out, $k, $o) {  // ignore null version
  if (is_null($o) || $o === false) {  
    return $out;
  }
  return nappend($out, q($k) . ':' . $o);
}

function nappend($out, $s) {
  if ($out == '') {
    return $s;
  } else {
    return $out . C . $s;
  }
}
?>