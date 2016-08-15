<?php
require_once "php/data/db/_util.php";

class Item {

	// Database fields
	public $id;
	public $surveyId;
	public $uid;
	public $type;
	public $required;
	public $text;
	public $goto;
	public $sortOrder;
	
	// Children
	public $choices;  // Associated by choice ID
	
	// Admin UI-helper fields
	public $page;
	
	// Survey taking UI-helper fields
	public $index;
	public $responses;  // Array. Most will be single-element, checkboxes can have multiple. Items with choices store the selected choiceId here.
	public $missing;  // Boolean indicating required value not supplied    
	
	public function __construct($id, $surveyId, $uid, $type, $required, $text, $goto, $sortOrder) {
		$this->id = $id;
		$this->surveyId = $surveyId;
		$this->uid = $uid;
		$this->type = $type;
		$this->required = toBool($required);
		$this->text = $text;
		$this->goto = $goto;
		$this->sortOrder = $sortOrder;
	}
	
	public function isQuestion() {
		return ($this->type == "text" || $this->type == "textarea" || $this->type == "checkboxes" || $this->type == "radiobuttons" || $this->type == "dropdown");
	}
	public function hasChoices() {
		return (count($this->choices) > 0);
	}
	public function getOptionList() {
		$list = "";
		foreach ($this->choices as $choice) {
			if (! isBlank($list)) $list .= ", ";
			$list .= $choice->text;
			if (! isBlank($choice->goto)) {
				$list .= " <i style='color:green'>(goto " . $choice->goto . ")</i>";
			}
		}
		return $list;
	}
	public function getShortText() {
		$text = $this->text;
		if (strlen($text) > 80) {
			$text = substr($text, 0, 80) . "...";
		} 
		return $text;
	}
}
?>
