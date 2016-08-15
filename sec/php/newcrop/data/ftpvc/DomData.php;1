<?php
/**
 * DOM Data Element
 * Extend this class for each <tag> in model 
 * Public vars used to create <tag> children and attributes according to naming convention:
 * - $TitleCase (DomElement child): <tag><TitleCase>..<value>..</TitleCase> 
 * - $camelCase (string child):     <tag><camelCase>value</camelCase>
 * - $_leadUnderscore (attribute):  <tag leadUnderscore='value'>
 */
class DomData {
  //
  // Initial assignment value for required vars, to raise DomDataRequiredException during toXml()
  const REQUIRED = '@DomData.REQUIRED';
  /**
   * Constructor
   * Assigns constructor args to public vars in definition order
   * To call this from an overriden constructor, use:
   *   $args = func_get_args(); 
   *   call_user_func_array(array('DomData', '__construct'), $args);
   */
  public function __construct() {
    $vars = array_keys(get_object_vars($this));
    $args = func_get_args();
    for ($i = 0, $l = func_num_args(); $i < $l; $i++) {
      $value = func_get_arg($i);
      if ($value != null) 
        $this->$vars[$i] = $value; 
    }
  }
  /**
   * Generate XML output string
   * @param string $rootTagName: optional <root> tag, defaulted to $e's class name if available
   * @param bool $includeEmpties: if false (default), empty elements are suppressed  
   * @return string '<root>..</root>'
   * @throws DomDataRequiredException 
   */
  public function toXml($rootTagName = null, $includeEmpties = false) {
    return DomData::buildXml($this, $rootTagName, $includeEmpties);
  }
  // 
  // Private statics
  private static $dom;
  private static $rootTagName;
  private static $includeEmpties;
  private static function buildXml($e, $rootTagName, $includeEmpties) {
    DomData::$dom = new DOMDocument('1.0', 'UTF8');
    DomData::defaultRootTag($e, $rootTagName);
    $root = ($rootTagName != null) ? 
      DomData::$dom->createElement($rootTagName) : 
      null;
    $required = array();
    foreach ($e as $key => $value) {
      DomData::createNode($key, $value, $root, $required);
    }
    if (count($required) > 0)
      throw new DomDataRequiredException($required);
    if ($root) 
      DomData::$dom->appendChild($root);
    return DomData::$dom->saveXML();
  }
  private static function defaultRootTag($e, &$name) {
    if ($name == null) {
      $name = get_class($e);
      if ($name == '' || $name == 'stdClass') 
        $name = null;
    }
  }
  private static function createNode($key, $value, &$parent, &$required) {
    $node = null;
    if ($value == DomData::REQUIRED) 
      $required[] = (($parent) ? $parent->tagName : '') . ".$key";
    if (substr($key, 0, 1) == '_') 
      $node = DomData::createAttr(substr($key, 1), $value);
    else if (is_string($value) || is_numeric($value) || is_bool($value) || $value == null) {
      if (DomData::$includeEmpties && $value == null) 
        $node = DomData::$dom->createElement($key);
      else if ($value != null) 
        $node = DomData::$dom->createElement($key, (string) $value);
    } else {
      if (is_object($value) || DomData::is_assoc($value)) {
        $node = DomData::$dom->createElement($key);
        if ($value != null) {
          foreach ($value as $key => $value) {
            DomData::createNode($key, $value, $node, $required);
          }
        }
      } else if ($value != null) {
        foreach ($value as $e) 
          DomData::createNode($key, $e, $parent, $required);
      }
    }
    if ($node) 
      DomData::appendNode($parent, $node);
  }
  private static function appendNode(&$parent, $node) {
    if ($parent) 
      $parent->appendChild($node);
    else 
      DomData::$dom->appendChild($node);
  }
  private static function createAttr($key, $value) {
    $attr = null;
    if ($value != null) {
      $attr = DomData::$dom->createAttribute($key);
      $attr->appendChild(DomData::$dom->createTextNode($value));
    }
    return $attr;
  }
  private static function is_assoc($array) {
    return (is_array($array) && 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));
  }
}
/**
 * Exceptions
 */
class DomDataRequiredException extends Exception {
  /**
   * @param array $required ['tagName.field',..]
   */
  public $required;
  public function __construct($required) {
    $this->required = $required;
  }
}
?>