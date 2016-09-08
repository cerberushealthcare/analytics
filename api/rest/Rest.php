<?php
class Rest {
  //
  public $host;     // 'api.clicktate.com' / 'localhost:2900'
  public $fullUrl;  // 'http://api.clicktate.com/practice/'   
  public $uri;      // 'practice'
  public $method;   // 'POST'
  public $data;     // [field=>value,..]
  //
  const BASE_PATH = '/clicktate/api/';
  /**
   * Constructor
   */
  public function __construct() {
    $this->setUrl();
    $this->uri = substr(str_replace(Rest::BASE_PATH, '', $_SERVER['REQUEST_URI']), 0, -1);
    $this->method = $_SERVER['REQUEST_METHOD'];
	$a = file_get_contents("php://input");
	//echo 'The contents:';
	//print_r($a);
    parse_str(file_get_contents("php://input"), $this->data);
  }
  //
  protected function setUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
    $location = $_SERVER['REQUEST_URI'];
    if ($_SERVER['QUERY_STRING']) {
      $location = substr($location, 0, strrpos($location, $_SERVER['QUERY_STRING']) - 1);
    }
    $this->host = $_SERVER['HTTP_HOST'];
    $this->fullUrl = $protocol . '://' . $this->host . $location;
  }
}
?>