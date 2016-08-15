<?php
require_once "php/forms/Form.php";

class ItemForm extends Form {

	// Enterable fields
	public $id;	
	public $surveyId;
	public $uid;
	public $type;
	public $required = false;
	public $text;
	public $goto;
	public $sortOrder;
	
	// Readonly helper field
	public $page;
	
	// Choice lines
	public $choices = array();
	
	// Combo Lists
	public $sortOrders = array();
	public $types = array();

	const BLANK_LINES = 8;
	
	public function __construct() {
		$this->addBlanksToChoices();
	}
	
	public function key() {
		$key = $this->type;
		if (! isBlank($this->uid)) {
			$key .= " \"" . $this->uid . "\"";
		}
		if ($this->page > 0) {
			if ($this->type == "pagebreak") {
				$key .= " (start of page " . ($this->page + 1) . ")";
			} else {
				$key .= " (page " . $this->page . ")";
			}
		}
		return $key;
	}
	
	public function validate() {
		$this->resetValidationException();
		for ($i = 0; $i < count($this->choices) - self::BLANK_LINES; $i++) {
			$this->validateChoice($i, $this->choices[$i]);
		}	
		$this->throwValidationException();
	}
	
	private function validateChoice($i, $choiceLine) {
		if (isBlank($choiceLine->text)) {
			$this->validationException->add("chText[" . $i . "]", "Choice text is required.");
		}
	}

	public function buildItem() {
		$item = new Item($this->id, $this->surveyId, $this->uid, $this->type, $this->required, $this->text, $this->goto, $this->sortOrder);
		$item->choices = array();
		for ($i = 0; $i < count($this->choices) - self::BLANK_LINES; $i++) {
			$item->choices[] = $this->buildChoice($this->choices[$i]);
		}
		return $item;
	}
	
	private function buildChoice($choiceLine) {
		return new Choice(null, $this->id, $choiceLine->text, $choiceLine->goto, null);
	}
	
	public function setFromDatabase($dto) {
		$this->id = $dto->id;
		$this->surveyId = $dto->surveyId;
		$this->uid = $dto->uid;
		$this->type = $dto->type;
		$this->required = $dto->required;
		$this->text = $dto->text;
		$this->goto = $dto->goto;
		$this->sortOrder = $dto->sortOrder;
		$this->page = $dto->page;
		$this->choices = array();
		foreach ($dto->choices as $k => $choice) {
			$this->choices[] = new ChoiceLine($choice->text, $choice->goto);
		}
		$this->addBlanksToChoices();
	}

	public function setFromPost() {
		$this->id = $_POST["id"];
		$this->surveyId = $_POST["surveyId"];
		$this->uid = $_POST["uid"];
		$this->type = $_POST["type"];
		$this->required = $this->toBool(isset($_POST["required"]) ? $_POST["required"] : null);
		$this->text = $_POST["text"];
		$this->goto = $_POST["goto"];
		$this->sortOrder = $_POST["sortOrder"];
		$this->page = $_POST["page"];
		$text = $_POST["chText"];
		$goto = $_POST["chGoto"];
		$this->choices = array();
		for ($i = 0; $i < count($text); $i++) {
			if (! isBlank($text[$i]) || ! isBlank($goto[$i])) {
				$choiceLine = new ChoiceLine($text[$i], $goto[$i]);
				$this->choices[] = $choiceLine;
			}
		}
		$this->addBlanksToChoices();
	}
	
	function buildItemTypeCombo() {
		$combos = array();
		$combos["header"] = "header";
		$combos["paragraph"] = "paragraph";
		$combos["pagebreak"] = "pagebreak";
		$combos["text"] = "text";
		$combos["textarea"] = "textarea";
		$combos["checkboxes"] = "checkboxes";
		$combos["radiobuttons"] = "radiobuttons";
		$combos["dropdown"] = "dropdown";
		return $combos;
	}
	
	private function addBlanksToChoices() {
		$blankLine = new ChoiceLine("", "");
		for ($i = 0; $i < self::BLANK_LINES; $i++) {
			$this->choices[] = $blankLine;
		}
	}
}

class ChoiceLine {

	public $text;
	public $goto;
	
	public function __construct($text, $goto) {
		$this->text = $text;
		$this->goto = $goto;
	}
}
?>