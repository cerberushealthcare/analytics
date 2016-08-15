<?php
require_once "php/data/rec/_Rec.php";
require_once 'php/data/rec/sql/dao/Logger.php';
//
/**
 * Ajax Response
 * @author Warren Hornsby
 */
class AjaxResponse extends Rec implements SerializeNulls {
  //
  public $id;
  public $obj;
  //
  public function __construct($id, $obj = null) {
    $this->id = $id;
    $this->obj = $obj;
  }
  public function write() {
    if (isset($_GET['debug']))
      if ($_GET['debug'] == 2)
        echo htmlentities(print_r($this, true));
      else
        echo htmlentities(print_r(jsondecode($this->toJson())->obj, true));
    else
      echo $this->toJson();
  }
  public function toJson() {
    if ($this->obj instanceof Rec || $this->obj instanceof BasicRec)
      $json = $this->obj->toJson();
    else
      $json = jsonencode($this->obj);
    return '{"id":"' . $this->id . '","obj":' . $json . '}';
  }
  public function isError() {
    return $this->id == 'error';
  }
  //
  static function out($id, $obj = null) {
    $me = new static($id, $obj);
    $me->write();
  }
  static function exception($e) {
    $me = static::fromException($e);
    $me->write();
  }
  static function logException($e) {
    if ($e instanceof SessionExpiredException)
      return;
    Logger::logException($e);
  }
  static function nothing() {
    $me = static::asNull();
    $me->write();
  }
  //
  static function from($id, $obj = null) {
    return new static($id, $obj);
  }
  static function fromException($e) {
	  if ($e instanceof SessionExpiredException)
	    return static::asTimeout();
    if ($e instanceof UnauthorizedException) 
      Logger::logException($e);
    if (! $e instanceof DisplayableException) 
      if (! isset($_GET['debug'])) 
        $e = Logger::logException($e);  // Not meant for user eyes; log it and transform to UI-friendly
    $obj = new stdClass();
    $obj->type = get_class($e);
    $obj->message = $e->getMessage();
    if (isset($e->data))
      $obj->data = $e->data;
    return new static('error', $obj);
  }
  static function asTimeout() {
    return new static('save-timeout');
  }
  static function asNull() {
    return new static('null');
  }
}