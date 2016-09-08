<?php
set_include_path($_SERVER['DOCUMENT_ROOT'] . '/analytics/api/');
require_once 'rest/ApiException.php';
require_once 'Data.php';
/**
 * Api Data Class
 */
class Api {
  //
  protected $_prefix;
  //
  const NONE_REQUIRED = null;
  /**
   * Load fields from array
   * @param array $arr: ['fieldName'=>'value',..]  
   * @param(opt) array $validateRequired: ['fieldName',..] to throw ApiException if missing required values 
   * @param(opt) string $prefix: to load only from prefixed props in $arr 
   */
  protected function load($arr, $validateRequired = null, $prefix = null) {
    $this->_prefix = $prefix;
    $props = $this->getDataProps($prefix);
    $arr = array_change_key_case($arr, CASE_LOWER);
    foreach ($props as $prop => $var) {
      $this->$var = Data::get($arr, $prop);
    }
    if ($validateRequired) {
      $this->validateRequired($validateRequired);
    }
  }
  /**
   * Validate required fields; if any missing, throw ApiException
   * @param array $fields: ['fieldName',..]
   */
  public function validateRequired($fields) {
    $missing = array();
    foreach ($fields as $field) {
      if (Data::isBlank($this->$field)) {
        $missing[] = $field;
      }
    }
    if (count($missing) > 0) {
      $message = 'Missing required fields: ' . implode(', ', $missing);
      $this->throwApiException($message);
    }
  }
  /**
   * Throw ApiException, prefixing message with getMyName()
   * @param string $message: 'Error message'
   */
  public function throwApiException($message) {
    $message = '[' . strtoupper($this->getMyName()) . '] ' . $message;
    throw new ApiException($message);
  }
  /**
   * Get my class name with 'Api' prefix and including prefix from load() (if any) 
   * @return 'Patient'
   */
  public function getMyName() {
    $myName = substr(get_class($this), 3); 
    if ($this->_prefix) {
      $myName .= " ($this->prefix)";
    }
    return $myName; 
  }
  // 
  /**
   * Get data properties of object (not prefixed with '_')
   * @param(opt) string $prefix
   * @return ['prefixedpropname'=>'propName',..]
   */  
  private function getDataProps($prefix = '') {
    $vars = get_object_vars($this);
    $a = array();
    foreach ($vars as $var => $value) {
      if (substr($var, 0, 1) != '_') {
        $a[strtolower($prefix . $var)] = $var;
      }
    }
    return $a;
  }
}
?>
