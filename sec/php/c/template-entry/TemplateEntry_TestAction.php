<?php
//
class Test extends Rec {
  //
  public $id;
  public /*string[]*/$args;  
  public $op;
  //
  const ALWAYS = 1;
  const IS_SEL = 2;
  const NOT_SEL = 3;
  const IS_MALE = 4;
  const IS_FEMALE = 5;
  const OLDER_THAN = 6;
  const YOUNGER_THAN = 7;
  const IS_AGE = 8;
  const IS_BIRTHDATE_SET = 9;
  const IS_INJECTED = 11;
  //
  const OP_AND = 1;
  const OP_OR = 2;
  //
  static function from($s) {  
    if (! empty($s)) {  
      $us = array();
      $arr = explode(':', $s);
      foreach ($arr as $a) 
        $us[] = static::fromComp($a);
      return $us;
    }
  }
  static function fromComp($a) {
    $me = new static();
    $arr = preg_split('/\\(|\\)/', $a);
    $me->id = static::getId(current($arr));
    $me->args = static::getArgs(next($arr));
    $me->op = static::getOp(next($arr));
    return $me; 
  }
  static function getId($s) {
    switch (trim($s)) {
      case 'always':
        return static::ALWAYS;
      case 'isSel':
        return static::IS_SEL;
      case 'notSel':
        return static::NOT_SEL;
      case 'isMale':
        return static::IS_MALE;
      case 'isFemale':
        return static::IS_FEMALE;
      case 'olderThan':
        return static::OLDER_THAN;
      case 'youngerThan':
        return static::YOUNGER_THAN;
      case 'currentAge':
        return static::IS_AGE;
      case 'isBirthdateSet':
        return static::IS_BIRTHDATE_SET;
      case 'isInjected':
        return static::IS_INJECTED;
    }
  }
  static function getArgs($s) {
    $args = array();
    if (! empty($s)) {
      $arr = explode('.', $s);
      if (count($arr) < 4) {
        $args[] = implode('.', $arr);
      } else { 
        $opt = array_pop($arr);
        $args[] = implode('.', $arr);
        $args[] = $opt; 
      }
    }
    return $args;
  }
  static function getOp($s) {
    switch (trim($s)) {
      case 'and':
        return static::OP_AND;
      case 'or':
        return static::OP_OR;
    }
  }
}  
class Action extends Rec {
  //
  public /*Test[]*/$Tests;  
  public $id;
  public /*string[]*/$args;  
  //
  const INJECT = 1;
  const SET_DEFAULT = 2;
  const SET_CHECKED = 3;
  const SET_UNCHECKED = 4;
  const SYNC_ON = 5;
  const SYNC_OFF = 6;
  const SET_TEXT_FROM_SEL = 7;
  const CALC_BMI = 8;
  //
  public function isConditionless() {
    return empty($this->Tests);
  }
  public function isInject() {
    return $this->id == static::INJECT;
  }
  //
  static function from($s) {
    if (! empty($s)) {  
      $us = array();
      $arr = explode(';', $s);
      foreach ($arr as $a) 
        $us[] = static::fromComp($a);
      return $us;
    }
  }
  static function fromComp($a) {
    $me = new static();
    $arr = preg_split('/\\{|\\}/', $a);
    if (count($arr) == 1)
      array_unshift($arr, null);
    $me->Tests = static::getTests(reset($arr));
    $arr2 = preg_split('/\\(|\\)/', next($arr));
    $me->id = static::getId(current($arr2));
    $me->args = static::getArgs(next($arr2));
    return $me; 
  }
  static function getTests($s) {
    return Test::from($s);
  }
  static function getId($s) {
    switch (trim($s)) {
      case 'inject':
        return static::INJECT;
      case 'setDefault':
        return static::SET_DEFAULT;
      case 'setChecked':
        return static::SET_CHECKED;
      case 'setUnchecked':
        return static::SET_UNCHECKED;
      case 'syncOn':
        return static::SYNC_ON;
      case 'syncOff':
        return static::SYNC_OFF;
      case 'setTextFromSel':
        return static::SET_TEXT_FROM_SEL;
      case 'calcBmi':
        return static::CALC_BMI;
    }
  }
  static function getArgs($s) {
    return Test::getArgs($s);
  }
}
