<?php
require_once "php/data/json/_util.php";

// Returns a key/value array for rendering combos
class JHtmlCombo {

  public $keyValues;

  // firstOptBlankValue: if not-null, a blank opt generated with supplied value
  // rows: database rows to load
  // keyFieldName, valueFieldName: field names contained in rows to use as key and value
  public function __construct($firstOptBlankValue, $rows, $keyFieldName, $valueFieldName, $selKey = "") {
    $this->keyValues = array();
    if ($firstOptBlankValue !== null) {
      $this->keyValues[] = new JKeyValue("", $firstOptBlankValue);
    }
    while ($row = mysql_fetch_array($rows, MYSQL_ASSOC)) {
      $sel = $row[$keyFieldName] == $selKey; 
      $this->keyValues[] = new JKeyValue($row[$keyFieldName], $row[$valueFieldName], $sel);
    }
  }
  public function out() {
    return JsonDelegate::outSimpleArray($this->keyValues);
  }
}

class JKeyValue {

  public $k;
  public $v;
  
  public function __construct($key, $value, $sel = false) {
    $this->k = $key;
    $this->v = $value;
    if ($sel) $this->sel = $sel;
  }
  public function out() {
    return jsonencode($this);
  }
}
?>