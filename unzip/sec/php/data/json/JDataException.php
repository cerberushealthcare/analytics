<?php

class JDataException extends Exception {
  
  public $dataobject; 
  public $dataclass;  // e.g. "JDataHm"
  public $errors;     // [{id:,msg:},{id:,msg:},...} where id=prop (or null for whole record)

  public function __construct($dataobject, $errors) {
    $this->dataobject = $dataobject;
    $this->dataclass = get_class($dataobject);
    $this->errors = $errors;
  }
  public function out() {
    return jsonencode($this);
  }
}
?>