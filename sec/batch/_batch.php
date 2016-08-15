<?php
require_once 'config/MyEnv.php';
MyEnv::$BATCH = true;
register_shutdown_function('onfatalb');
//
/**
 *
 *
 * [pfisher ~]$ echo "<?php
 * >     include('CommandLine.php');
 * >     \$args = CommandLine::parseArgs(\$_SERVER['argv']);
 * >     echo "\n", '\$out = '; var_dump(\$args); echo "\n";
 * > ?>" > test.php
 *
 * [pfisher ~]$ php test.php plain-arg --foo --bar=baz --funny="spam=eggs" --alsofunny=spam=eggs \
 * > 'plain arg 2' -abc -k=value "plain arg 3" --s="original" --s='overwrite' --s
 *
 * $out = array(12) {
 *   [0]                => string(9) "plain-arg"
 *   ["foo"]            => bool(true)
 *   ["bar"]            => string(3) "baz"
 *   ["funny"]          => string(9) "spam=eggs"
 *   ["alsofunny"]      => string(9) "spam=eggs"
 *   [1]                => string(11) "plain arg 2"
 *   ["a"]              => bool(true)
 *   ["b"]              => bool(true)
 *   ["c"]              => bool(true)
 *   ["k"]              => string(5) "value"
 *   [2]                => string(11) "plain arg 3"
 *   ["s"]              => string(9) "overwrite"
 * }
 *
 * @author              Patrick Fisher <patrick@pwfisher.com>
 * @since               August 21, 2009
 * @see                 http://www.php.net/manual/en/features.commandline.php
 *                      #81042 function arguments($argv) by technorati at gmail dot com, 12-Feb-2008
 *                      #78651 function getArgs($args) by B Crawford, 22-Oct-2007
 * @usage               $args = CommandLine::parseArgs($_SERVER['argv']);
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
function _blog_ex($exception) {
  $e = new stdClass();
  $e->date = date("Y-m-d H:i:s");
  $e->exception = get_class($exception);
  $e->message = $exception->getMessage();
  $e->code = $exception->getCode();
  $e->trace = $exception->getTraceAsString();
  return $e;
}
function onfatalb() {
  $error = error_get_last();
  if ($error && $error['type'] == 1) {
    blog($error);
    throw new FatalBatchException($error);
  }
}
class FatalBatchException extends Exception {
  public function __construct($error) {
    $this->message = 'Fatal Exception';
    $this->code = $error;
  }
}