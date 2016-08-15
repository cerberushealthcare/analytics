<?php
require_once "php/data/json/JDataException.php";

class JDataValidator {
  
  public $dataobject; 
  public $errors;  // [{id:,msg:},{id:,msg:},...} where id=prop (or null for whole record)

  public function __construct($dataobject) {
    $this->dataobject = $dataobject;
    $this->errors = array();
  }
  public function addError($id, $msg) {  
    $this->errors[] = array("id"=>$id, "msg"=>$msg);
  }
  public function addRequired($id, $fieldName) {
    $this->addError($id, $fieldName . " is a required field.");
  }
  public function throwAnyErrors() {
    if (! empty($this->errors)) {
      throw new JDataException($this->dataobject, $this->errors);
    }
  }
}
?>