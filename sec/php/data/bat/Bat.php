<?php 
/**
 * Bat file execution
 * @author Warren Hornsby
 */
class Bat {
  //
  static $BAT_FILE;  // = 'some.bat'
  //
  public /*string[]*/ $output; 
  public /*int*/      $return;
  //
  protected $args;
  //
  public function __construct() {
    $args = func_get_args();
    $this->setArgs($args);
    $this->exec();
  }
  //
  protected function setArgs($args) {
    $this->args = (empty($args)) ? null : ' ' . implode(' ', $args);
  }
  protected function getArgs() {
    return $this->args ?: ''; 
  }
  protected function getPath() {
    return '"' . realpath(static::$BAT_FILE) . '"';
  }
  protected function getCmd() {
    return $this->getPath() . $this->getArgs() . ' 2>&1';  // 2>&1 sends warnings to stdout
  }
  protected function exec() {
    exec($this->getCmd(), $this->output, $this->return);
    logit_r($this->getCmd());
    logit_r($this, __CLASS__);
    return $this;
  }
  //
  static function run() {
    return new static();
  } 
}
