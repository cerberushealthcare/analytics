<?php
require_once "php/forms/_exceptions.php";

class Form {
	
	protected $validationException;
	private $breadcrumb;

	public static function getFormVariable($name, $default = null) {
    if (isset($_GET[$name])) {
      return $_GET[$name];
    } else {
      if (isset($_POST[$name])) {
        return $_POST[$name];
      } else {
        return $default;
      }
    }
  }
	
  protected function addDateCheck($id, $name, $value) {
    if (! isBlank($value)) {
      if (! strtotime($value)) {
        $this->validationException->add($id, $name . " is not a valid date; please format as month/date/year.");
      }
    }
  }
	
	protected function resetValidationException() {
		$this->validationException = new ValidationException();
	}
	
	protected function throwValidationException() {
		if ($this->validationException->containsErrors()) {
			throw $this->validationException;
		}
	}	

	protected function addRequired($id, $name, $value) {
		if (isBlank($value)) {
			$this->validationException->add($id, $name . " is a required field.");
		}
	}

	protected function toBool($field) {
		return ($field == "Y");
	}	
}
?>