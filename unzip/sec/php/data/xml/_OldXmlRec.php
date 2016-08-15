<?php
/**
 * XML Record
 * Extend this class for each <tag> in model 
 * Public vars used to create <tag> attributes, properties and children:
 *   $_attr = 'value'                 <this attr="value"..
 *   $_attr_ext = 'value'             <this attr:ext="value"..
 *   $tag = 'value'                   <this><tag>value</tag>..
 *   $tag_name = 'value'              <this><tag-name>value</tag-name>..
 *   $tag = XmlRec                    <this><tag><XmlRec>..</XmlRec></tag>..
 *   $tag = array(XmlRec,..)          <this><tag><XmlRec>..</XmlRec><XmlRec>..</XmlRec>..</tag>..
 *   $tag = array('attr'=>'value',..) <this><tag attr='value' attr='value'.. />..
 *   $_ = 'innerText'                 <this>innerText</this>
 *   $_cdata = 'text'                 <this><![CDATA[text]]></this>
 * @author Warren Hornsby
 */
class XmlRec {
  /**
   * Constructor
   * Assigns constructor args to public vars in definition order
   * To call this from an overriden constructor, use:
   *   $args = func_get_args(); 
   *   call_user_func_array(array('XmlRec', '__construct'), $args);
   */
  public function __construct() {
    $vars = array_keys(get_object_vars($this));
    $args = func_get_args();
    for ($i = 0, $l = func_num_args(); $i < $l; $i++) {
      $value = func_get_arg($i);
      if ($value !== null) {
        if (is_string($value))
          $value = self::xmlentities($value); 
        $this->$vars[$i] = $value;
      } 
    }
  }
  /**
   * Generate XML output string
   * @param $formatted true for CR+indents, false for compressed string (optional)
   * @param $includeEmpties default false
   * @param $noVersion to exclude <?xml version?> tag at top  
   * @return string '<root>..</root>'
   */
  public function toXml($formatted = false, $rootTagName = null, $includeEmpties = false, $noVersion = false) {
    $xml = self::buildXml($this, $formatted, $rootTagName, $includeEmpties);
    if ($noVersion) {
      $a = explode('?>', $xml, 2);
      if (count($a) == 2) 
        $xml = substr($a[1], 1, -1);
    }
    return $xml;
  }
  public function toXml_compressed($rootTagName = null, $includeEmpties = false) {
    return static::toXml(false, $rootTagName, $includeEmpties, true);
  }
  public function toXml_formatted($rootTagName = null, $includeEmpties = false) {
    return static::toXml(true, $rootTagName, $includeEmpties);
  }
  public function debug($rootTagName = null) {
    echo '<pre>' . htmlentities($this->toXml(true, $rootTagName)) . '</pre>';
  }
  /**
   * Field setter; will automatically convert to array if field already assigned
   * @param string $fid
   * @param string $value
   */
  public function set($fid, $value) {
    $current = $this->get($fid);
    if ($current == null)
      $this->$fid = $value;
    else if (is_array($current))
      array_push($this->$fid, $value);
    else
      $this->$fid = array($current, $value);
  }
  /**
   * Field getter
   * @param string $fid
   * @return string value if set, null otherwise
   */
  public function get($fid) {
    if (isset($this->$fid))
      return $this->$fid;
  }
  /**
   * Build array from XML input string
   * @param string $xml
   * @return array 
   */
  static function parse($xml) {
    $dom = new DomDocument('1.0', 'UTF8');
    $dom->preserveWhiteSpace = false;
    if (! $dom->loadXml($xml))
      throw new XmlParseException();
    return static::parseNode($dom->documentElement);
  }
  //
  private static $dom;
  private static $rootTagName;
  protected static $includeEmpties;
  private static function parseNode($root) {
    $rec = null;
    if ($root->hasAttributes()) {
      $rec = new stdClass();
      foreach ($root->attributes as $name=>$node) {
        $fid = "_$name";
        $rec->$fid = $node->nodeValue;
      }
    }
    $children = $root->childNodes;
    if ($rec || ($children && $children->length > 0)) 
      foreach ($children as $child) {
        $name = static::fixName($child->nodeName);
        if ($name == '#text') {
          if ($root->hasAttributes()) {
            $rec->_value = $child->nodeValue;
            return $rec; 
          } else 
            return $child->nodeValue;
        }
        $e = static::_get($rec, $name);
        if ($e == null) 
          $rec->$name = static::parseNode($child);
        else {
          if (! is_array($e)) 
            $rec->$name = array($e);
          $rec->{$name}[] = static::parseNode($child);
        }
      }
    return $rec;
  }
  private static function fixName($name) {
    $a = explode(':', $name);
    return end($a);
  }
  private static function _get($obj, $prop, $default = null) {
    return isset($obj->$prop) ? $obj->$prop : $default;
  }
  public static function buildXml($e, $formatted = false, $rootTagName = null, $includeEmpties = false) {
    static::$includeEmpties = $includeEmpties;
    self::$dom = new DOMDocument('1.0', 'UTF8');
    if ($formatted)
      self::$dom->formatOutput = true;
    self::defaultRootTag($e, $rootTagName);
    $root = ($rootTagName != null) ? 
      self::$dom->createElement($rootTagName) : 
      null;
    foreach ($e as $key => $value)
      self::createNode($key, $value, $root);
    if ($root) 
      self::$dom->appendChild($root);
    return self::$dom->saveXML();
  }
  public static function buildHtml($e, $formatted = false) {
    self::$dom = new DOMDocument('1.0', 'UTF8');
    if ($formatted)
      self::$dom->formatOutput = true;
    self::defaultRootTag($e, $rootTagName);
    $root = self::$dom->createElement('root'); 
    foreach ($e as $key => $value) 
      self::createNodeHtml($key, $value, $root);
    if ($root) 
      self::$dom->appendChild($root);
    return self::$dom->saveXML($root);
  }
  private static function defaultRootTag($e, &$name) {
    if ($name == null) {
      $name = get_class($e);
      if ($name == '' || $name == 'stdClass') 
        $name = null;
    }
  }
  private static function createNodeHtml($key, $value, &$parent) {
    $node = null;
    if (substr($key, 0, 1) == '_')  {
      if ($key == '_') {
        $node = self::$dom->createTextNode(self::toString($value)); 
      } else if ($key == '_value') {
        $node = self::$dom->createTextNode($value);
      } else {
        $node = self::createAttr(self::fixAttrName($key), self::toString($value));
      }
    } else { 
      $key = str_replace('_', '-', $key);
      if ($value === null) {
        if (self::$includeEmpties) 
          $node = self::$dom->createElement($key);
      } else if (is_scalar($value)) {
        $node = self::$dom->createElement($key, self::toString($value));
      } else if (is_object($value)) {
        $node = self::$dom->createElement($key);
        foreach ($value as $key => $value) {
          self::createNodeHtml($key, $value, $node);
        }
      } else if (is_assoc($value)) {
        $node = self::$dom->createElement($key);
        foreach ($value as $attr => $val) 
          self::appendNode($node, self::createAttr($attr, $val));
      } else if (is_array($value)) {
        foreach ($value as $e) 
          self::createNodeHtml($key, $e, $parent);
      }
    }    
    if ($node) 
      self::appendNode($parent, $node);
  }
  private static function createNode($key, $value, &$parent) {
    $node = null;
    if (substr($key, 0, 1) == '_')  {
      if ($key == '_')
        $node = self::$dom->createTextNode(self::toString($value)); 
      else if ($key == '_cdata')
        $node = self::$dom->createCDATASection(self::toString($value)); 
      else
        $node = self::createAttr(self::fixAttrName($key), self::toString($value));
    } else { 
      $key = str_replace('_', '-', $key);
      if ($value === null) {
        if (self::$includeEmpties) 
          $node = self::$dom->createElement($key);
      } else if (is_scalar($value)) {
        $node = self::$dom->createElement($key, self::toString($value));
      } else if (is_object($value)) {
        $node = self::$dom->createElement($key);
        foreach ($value as $key => $value) { 
          self::createNode($key, $value, $node);
        }
      } else if (is_assoc($value)) {
        $node = self::$dom->createElement($key);
        foreach ($value as $attr => $val) 
          self::appendNode($node, self::createAttr($attr, $val));
      } else if (is_array($value)) {
        foreach ($value as $e) 
          self::createNode($key, $e, $parent);
      }
    }    
    if ($node) 
      self::appendNode($parent, $node);
  }
  private static function toString($value) {
    if (is_bool($value)) 
      return ($value) ? 'true' : 'false';
    else
      return (string) $value;
  }
  private static function fixAttrName($key) {
    return str_replace('_', ':', substr($key, 1));
  }
  private static function appendNode(&$parent, $node) {
    if ($parent) 
      $parent->appendChild($node);
    else 
      self::$dom->appendChild($node);
  }
  private static function createAttr($key, $value) {
    $attr = null;
    if ($value != null) {
      $attr = self::$dom->createAttribute($key);
      $attr->appendChild(self::$dom->createTextNode($value));
    }
    return $attr;
  }
  private static function is_assoc($array) {
    return (is_array($array) && 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));
  }
  private static function xmlentities($string) {
    return str_replace ( array ( '&', '"', "'", '<', '>' ), array ( '&#38;' , '&quot;', '&apos;' , '&lt;' , '&gt;' ), $string );
  }
}
/**
 * Exceptions
 */
class XmlParseException extends DisplayableException {}
