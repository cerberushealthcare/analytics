<?php
class ValidationException extends Exception {
  
  private $errors = array();

  public function add($key, $msg) {
    $this->errors[$key] = $msg;
  }

  public function containsErrors() {
    return (count($this->errors) > 0);
  }

  public function getErrors() {
    return $this->errors;
  }
}
?>