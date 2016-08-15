<?php
require_once 'config/MyEnv.php';
MyEnv::$BATCH = true;
//
/**
 * $args = arguments($argv);
 * $arg1 = $args[0];
 * $arg2 = $args[1];
 */
function arguments($argv){
  array_shift($argv);
  $out = array();
  foreach ($argv as $arg){
    if (substr($arg,0,2) == '--'){
      $eqPos = strpos($arg,'=');
      if ($eqPos === false){
        $key = substr($arg,2);
        $out[$key] = isset($out[$key]) ? $out[$key] : true;
      } else {
        $key = substr($arg,2,$eqPos-2);
        $out[$key] = substr($arg,$eqPos+1);
      }
    } else if (substr($arg,0,1) == '-'){
      if (substr($arg,2,1) == '='){
        $key = substr($arg,1,1);
        $out[$key] = substr($arg,3);
      } else {
        $chars = str_split(substr($arg,1));
        foreach ($chars as $char){
          $key = $char;
          $out[$key] = isset($out[$key]) ? $out[$key] : true;
        }
      }
    } else {
      $out[] = $arg;
    }
  }
  return $out;
}
/**
 * Batch logging
 * @examples
 *   blog('Hello world');
 *   blog($object, 'some object');
 *   blog($exception);
 */
function blog($o, $caption = null) {
  if ($o instanceof Exception) {
    $o = _blog_ex($o);
    $caption = 'EXCEPTION';
  }
  $msg = (($caption) ? "=== $caption === " : '') . print_r($o, true) . (($caption) ? "=== /$caption ===" : '');
  $fp = @fopen('blog.txt', 'a');
  if ($fp) { 
    static $lastts;
    static $blank = '';
    $ts = date("Y-m-d H:i:s") . "\n";
    if ($lastts == $ts) 
      $ts = $blank;
    else 
      $lastts = $ts;
    $msg = join("\n    $blank", explode("\n", $msg));
    fputs($fp, "$ts  $msg\n");
    fclose($fp);
  }
}
function blog_start($argv) {
  blog(null, "START BATCH");
  echo "\n== START BATCH: " . implode(' ', $argv) . " ==\n";
}
function blog_end() {
  blog(null, 'END BATCH');
  echo "== END BATCH ==\n";
}
function _blog_ex($exception) {
  $e = new stdClass();
  $e->date = date("Y-m-d H:i:s");
  $e->exception = get_class($exception);
  $e->message = $exception->getMessage();
  $e->code = $exception->getCode();
  $e->trace = $exception->getTraceAsString();
  return $e;
}
function becho($text) { 
  $ts = date("Y-m-d H:i:s");
  echo("$ts $text\n");
}