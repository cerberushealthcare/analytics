<?php
require_once 'config/MyEnv.php';
require_once 'inc/serverFunctions.php';
// 
/**
 * Logger 
 * @author Warren Hornsby
 */
class Logger {
  //
  static function getLogFile() {
    return MyEnv::$LOG_PATH . '\log.txt';
  }
  static function getErrorFile() {
    return MyEnv::$LOG_PATH . '\error.txt';
  }
  /**
   * Append to log file 
   * @param string $msg
   */
  static function log($msg) {
    $fp = @fopen(self::getLogFile(), 'a');
    if ($fp) { 
      static $lastts;
      static $blank = '';
      $ts = static::now() . "\n";
      if ($lastts == $ts) 
        $ts = $blank;
      else 
        $lastts = $ts;
      $msg = join("\n    $blank", explode("\n", $msg));
      fputs($fp, "$ts  $msg\n");
      fclose($fp);
    }
  }
  /**
   * Append to log file (if in test environment)
   * @param string $msg
   */
  static function debug($msg) {
    if (MyEnv::$LOG)
      if (substr(currentUrl(), 0, 13) != 'serverPolling') 
        self::log(self::formatSql($msg));
  }
  static function debug_server() {
    Logger::debug(currentUrl());
    if (! empty($_POST))
      Logger::debug_r($_POST, '$_POST');
  }
  /**
   * PHP print_r to log file (if in test environment)
   * @param mixed $o e.g. array, object
   * @param string $caption (optional wrapper text)
   */
  static function debug_r($o, $caption = null) {
    global $login;
    if (MyEnv::$LOG || $login->userId == 3577) 
      self::log((($caption) ? "=== $caption === " : '') . self::p_r($o) . (($caption) ? "=== /$caption ===" : ''));  
  }
  protected static function p_r($o) {
    if (is_string($o)) 
      $o = self::formatSql($o);
    return print_r($o, true);     
  }
  protected static function addLfs(&$s, $words) {
    foreach ($words as $word)
      $s = self::addLf($s, $word);
    return $s;
  }
  protected static function addLf(&$s, $w) {
    return str_replace(" $w", "\n$w", $s);
  }
  static function formatSql($s) {
    static $words = array(
      'FROM', 'GROUP BY', 'LEFT JOIN', 'JOIN', 'LIMIT', 'ORDER BY', 'SET', 'VALUES', 'WHERE'); 
    $w = substr($s, 0, 7);
    switch ($w) {
      case 'SELECT ':
        $s = self::addLfs($s, $words);
        $s = str_replace("LEFT\n", "LEFT ", $s);
        if (substr($s, -1, 1) != ';')
          $s .= ';';
        break;
      case 'INSERT ':
      case 'UPDATE ':
      case 'DELETE ':
        $s = self::addLfs($s, $words);
        if (substr($s, -1, 1) != ';')
          $s .= ';';
        break;
    }
    return $s;
  }
  /**
   * Output exception to error file
   * @param Exception $exception
   * @return LoggedException (user-friendly exception to throw in place of original)
   */
  static function logException($exception) {
    if ($exception instanceof UserFriendly)
      return $exception;
    global $login;
    $trace = $exception->getTraceAsString();
    $e = new stdClass();
    $e->id = get($login, 'userId') . substr(strtotime(static::now()), 5);
    $e->date = static::now();
    $e->exception = get_class($exception);
    $e->message = $exception->getMessage();
    $e->code = $exception->getCode();
    $e->source = currentUrl();
    if (! empty($_POST))
      $e->post = print_r($_POST, true);
    $e->userId = get($login, 'userId');
    $e->uid = get($login, 'uid');
    $e->name = get($login, 'name');
    $e->sessId = session_id();
    $error = substr(print_r($e, true), 17, -2);
    $fp = @fopen(self::getErrorFile(), 'a');
    if ($fp) 
      fputs($fp, "$error\n$trace\n");
    return new LoggedException($e->id); 
  }
  static function logBatchException($source, $exception, $idPrefix = 'BATCH', $post = null) {
    $trace = $exception->getTraceAsString();
    $e = new stdClass();
    $e->id = $idPrefix . substr(strtotime(static::now()), 5);
    $e->date = static::now();
    $e->exception = get_class($exception);
    $e->message = $exception->getMessage();
    $e->code = $exception->getCode();
    $e->source = $source;
    $e->url = currentUrl();
    $e->post = print_r($post, true);
    $error = substr(print_r($e, true), 17, -2);
    $fp = @fopen(self::getErrorFile(), 'a');
    if ($fp) 
      fputs($fp, "$error\n$trace\n");
    return $e->id;
  }
  static function logError($text) {
    $fp = @fopen(self::getErrorFile(), 'a');
    if ($fp)
      fputs($fp, "$text\n");
  }
  //
  protected static function now() {
    return date("Y-m-d H:i:s");
    $utimestamp = microtime(true);
    $timestamp = floor($utimestamp);
    $ms = round(($utimestamp - $timestamp) * 1000000);
    return date(preg_replace('`(?<!\\\\)u`', $ms, 'Y-m-d H:i:s.u'), $timestamp);
  }
}
/**
 * Exception
 */
interface UserFriendly {}  // Exception text is formatted for use on UI
// 
class LoggedException extends Exception implements UserFriendly {
  public function __construct($errorId) {
    $this->message = "<b>Application error encountered.</b><br><br>If you continue to have problems, please contact our support line and<br>provide the following error code: #$errorId.<br><br>Thank you for your assistance.";
  }
}