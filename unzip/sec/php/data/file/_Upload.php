<?php
//
require_once 'php/data/_BasicRec.php';
//
abstract class Upload extends BasicRec {
  //
  public $name;     // 'original.jpg'
  public $type;     // 'image/jpeg'
  public $tmpName;  // 'C:\Windows\temp\phpE74.tmp'
  public $error;    // 0
  public $size;     // 23308
  //
  const M = 1000000;
  const K = 1000;
  //
  public function save($filename) {
    $result = move_uploaded_file($this->tmpName, $filename);
    return $result;
  }
  //
  static function fetchAll() {
    $us = static::fromHttpPostFile(current($_FILES));
    return /*Upload[]*/$us;      
  }
  static function fetch() {
    $us = static::fetchAll();
    $me = current($us);
    return /*Upload*/$me;
  }
  protected static function fromHttpPostFile($f) {
    $us = array();
    arrayifyEach($f);
    for ($i = 0, $j = count($f['name']); $i < $j; $i++) {
      if ($name = $f['name'][$i]) 
        $us[] = new static(static::fixName($name), $f['type'][$i], $f['tmp_name'][$i], $f['error'][$i], $f['size'][$i]);
    }
    return $us;
  }
  protected static function fixName($name) {
    $name = '0_' . str_replace(' ', '_', $name);  // to ensure names do not begin with dash or contain spaces or commas, which messes with bat commands
    $name = str_replace(';', '_', $name);  
    $name = str_replace(',', '_', $name);  
    return $name;
  }
}
