<?php
/**
 * XML Parsing Functions
 */
class XmlParser {
  /**
   * Build array from XML input string
   * @param string $xml
   * @return array
   */
  public static function parse($xml) {
    $dom = new DomDocument('1.0', 'UTF8');
    $dom->preserveWhiteSpace = false;
    $dom->loadXml("<the-root-node>$xml</the-root-node>");
    return self::parseNode($dom->documentElement);
  }
  /**
   * Build array from XML file
   * @param string $filename
   * @return array
   */
  public static function open($filename) {
    $dom = new DOMDocument();
    $dom->load($filename);
    $xml = $dom->saveXML($dom->documentElement);
    return self::parse($xml);
  }
  //
  private static function parseNode($root) {
    $rec = null;
    if ($root->hasAttributes()) {
      $rec = new stdClass();
      foreach ($root->attributes as $name=>$node)
      $rec->$name = $node->nodeValue;
    }
    if ($root->hasChildNodes()) {
      foreach ($root->childNodes as $child) {
        $name = $child->nodeName;
        if ($name == '#text') {
          if ($root->hasAttributes()) {
            $rec->_value = $child->nodeValue;
            return $rec;
          } else
          return $child->nodeValue;
        }
        $e = self::get($rec, $name);
        if ($e == null)
        $rec->$name = self::parseNode($child);
        else {
          if (! is_array($e))
          $rec->$name = array($e);
          $rec->{$name}[] = self::parseNode($child);
        }
      }
    }
    return $rec;
  }
  private static function get($obj, $prop, $default = null) {
    return isset($obj->$prop) ? $obj->$prop : $default;
  }
}
