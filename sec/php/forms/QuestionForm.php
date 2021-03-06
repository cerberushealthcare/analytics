<?php
require_once "php/forms/Form.php";
require_once "php/dao/JsonDao.php";

class QuestionForm extends Form {

	// Enterable fields
	public $id;
	public $templateId;
	public $sectionId;
	public $parId;
	public $uid;
	public $desc;
	public $bt;
	public $at;
	public $btms;
	public $atms;
	public $btmu;
	public $atmu;
	public $listType;
	public $break;
	public $test;
	public $outTable;
	public $outCols;
	public $inActions;
	public $actionIf;
	public $actionThen;
	public $defix;
	public $mix;
	public $mcap;
	public $mix2;
	public $mcap2;
	public $sortOrder;
	public $sync;
	public $anyMulti;  // boolean
	public $dsync;
	public $billing;
	
	public $inTable;  // from Par

	// Action lines
	public $actionIfs = array();
	public $actionThens = array();

	// Option lines
	public $singleOptions = array();
	public $multiOptions = array();
	public $multiOptions2 = array();

	// Combo lists
	public $sortOrders = array();
	public $breaks = array();
	public $listTypes = array();
	public $dataTables = array();
	public $trackCats = array();

	private $optionIds;  // Assoc array for validation

	const BLANK_LINES = 8;
	const BLANK_ACTIONS = 3;

	public function __construct() {
		$this->addBlanksToOptions();
		$this->buildQuestionBreakCombo();
		$this->buildListTypeCombo();
		$this->dataTables = CommonCombos::outDataTables();
		$this->trackCats = array(
		  "" => "",
		  "7" => "Immun",
		  "9" => "IOL",
			"1" => "Lab",
		  "2" => "Nuclear",
      "6" => "Proc", 
		  "3" => "Radio",
		  "4" => "Referral",
		  "8" => "RTO",
			"10" => "Surgical",
			"5" => "Test");
		$this->billingCodes = array(
		  '' => '',
		  'hpi.elem.qual' => 'HPI: Quality',
		  'hpi.elem.loc' => 'HPI: Location',
		  'hpi.elem.dur' => 'HPI: Duration',
		  'hpi.elem.sev' => 'HPI: Severity',
		  'hpi.elem.tim' => 'HPI: Timing',
		  'hpi.elem.cxt' => 'HPI: Context',
		  'hpi.elem.nod' => 'HPI: Modifying factors',
		  'hpi.elem.sx' => 'HPI: Assoc signs/symptoms',
		  'pfsh.fam' => 'HX: Family',
			'pfsh.med' => 'HX: Medical',
		  'pfsh.soc' => 'HX: Social',
		  'pfsh.surg' => 'HX: Surgical');
	}

	/*
	 * Build ACTIONS data field from form entry fields
	 */
	public function getActions() {
		$actions = array();
		for ($i = 0; $i < count($this->actionIfs); $i++) {
			if (! isBlank($this->actionIfs[$i]) || ! isBlank($this->actionThens[$i])) {
				if (! isBlank($this->actionIfs[$i])) {
					$action = trim($this->actionIfs[$i]) . " {" . trim($this->actionThens[$i]) . "}";
				} else {
					$action = trim($this->actionThens[$i]);
				}
				$actions[] = $action;
			}
		}
		return isempty($actions) ? "" : implode(";", $actions);
	}

	public function setOutData($outData) {
	  if ($outData == null) {
	    $this->outTable = null;
	    $this->outCols = null;
	  } else {
  	  $a = explode(":", $outData);
  	  if (count($a) > 1) {
    	  $this->outTable = $a[0];
    	  $this->outCols = $a[1];
  	  }
	  }
	}
	
	/*
	 * Set action entry fields from ACTIONS data field
	 */
	public function setActions($actions) {
		$this->actionIfs = array();
		$this->actionThens = array();
		$arr = explode(";", $actions);
		$count = count($arr);
		for ($i = 0; $i < $count; $i++) {
			$a = split("[{}]", $arr[$i]);
			if (count($a) == 1) {
				$this->actionIfs[] = null;
				$this->actionThens[] = trim($a[0]);
			} else {
				$this->actionIfs[] = trim($a[0]);
				$this->actionThens[] = trim($a[1]);
			}
		}
		$this->addBlanksToActions();
	}

	public function validate() {
	  $noteDate = nowNoQuotes();
		$this->resetValidationException();
		$this->addRequired("uid", "UID", $this->uid);
		$this->addRequired("desc", "Popup Question", $this->desc);
		if (! isBlank($this->test)) {
			try {
				JsonDao::parseTest($this->test, $this->templateId, $noteDate, true);
			} catch (ParseException $e) {
				$this->validationException->add("test", "Show condition invalid: " . $e->getMessage());
			}
		}
		for ($i = 0; $i < count($this->actionIfs); $i++) {
			if (! isBlank($this->actionIfs[$i])) {
				if (isBlank($this->actionThens[$i])) {
					$this->validationException->add("actionThens[" . $i . "]", "Action required when condition supplied.");
				}
				try {
					JsonDao::parseTest($this->actionIfs[$i], $this->templateId, $noteDate, true);
				} catch (ParseException $e) {
					$this->validationException->add("actionIfs[" . $i . "]", "Action condition invalid: " . $e->getMessage());
				}
			}
			if (! isBlank($this->actionThens[$i])) {
				try {
					JsonDao::parseThen($this->actionThens[$i], $this->id, null, $this->templateId, $noteDate, true);
				} catch (ParseException $e) {
					$this->validationException->add("actionThens[" . $i . "]", "Action invalid: " . $e->getMessage());
				}
			}
		}
		if (count($this->singleOptions) != self::BLANK_LINES) {
			$this->addRequired("desc", "Popup Question", $this->desc);
		}
		if (count($this->multiOptions) == self::BLANK_LINES && count($this->multiOptions2) > self::BLANK_LINES) {
			$this->validationException->add("optDesc[" . self::BLANK_LINES . "]", "First section of multi options must be entered if second section has entries.");
		}
		$this->optionsIds = array();
		for ($i = 0; $i < count($this->singleOptions) - self::BLANK_LINES; $i++) {
			$this->validateOption($i, $this->singleOptions[$i], "Single option");
		}
		for ($i = 0; $i < count($this->multiOptions) - self::BLANK_LINES; $i++) {
			$this->validateOption($i + count($this->singleOptions), $this->multiOptions[$i], "Multiple option");
		}
		for ($i = 0; $i < count($this->multiOptions2) - self::BLANK_LINES; $i++) {
			$this->validateOption($i + count($this->singleOptions) + count($this->multiOptions), $this->multiOptions2[$i], "Multiple option");
		}
		if (! isBlank($this->outTable) || ! isBlank($this->outCols)) {
		  if (isBlank($this->outTable)) {
		    $this->validationException->add("outTable", "Out table required when data specified.");
		  } else if (isBlank($this->outCols)) {
        $this->validationException->add("outTable", "Data required when out table specified.");
		  }
		}
		$this->throwValidationException();
	}

	private function validateOption($i, $optionLine, $desc) {	
		if (isBlank($optionLine->uid)) {
			$this->validationException->add("optDesc[" . $i . "]", $desc . " UID is required.");
		} else {
			if (isset($this->optionIds[$optionLine->uid])) {
				$this->validationException->add("optUid[" . $i . "]", "More than one option is using the name \"" . $optionLine->uid . "\".");
			} else {
				$this->optionIds[$optionLine->uid] = true;
			}
		}
	}

	public function buildQuestion() {
		$question = new Question($this->id, $this->parId, $this->uid, $this->desc, $this->bt, $this->at, $this->btms, $this->atms, $this->btmu, $this->atmu, $this->listType, $this->break, $this->test, $this->getActions(), $this->defix, $this->mix, $this->mcap, $this->mix2, $this->mcap2, null, $this->sortOrder, $this->sync, $this->buildOutData(), $this->inActions, $this->dsync, $this->billing);
		$question->options = array();
		for ($i = 0; $i < count($this->singleOptions) - self::BLANK_LINES; $i++) {
			$question->options[] = $this->buildOption($this->singleOptions[$i]);
		}
		$question->mix = count($question->options);
		for ($i = 0; $i < count($this->multiOptions) - self::BLANK_LINES; $i++) {
			$question->options[] = $this->buildOption($this->multiOptions[$i]);
		}
		$question->mix2 = count($question->options);
		for ($i = 0; $i < count($this->multiOptions2) - self::BLANK_LINES; $i++) {
			$question->options[] = $this->buildOption($this->multiOptions2[$i]);
		}
		if (count($this->multiOptions) == self::BLANK_LINES) {
			$question->mix = null;
			$question->mix2 = null;
		} else if (count($this->multiOptions2) == self::BLANK_LINES) {
			$question->mix2 = null;
		}
		return $question;
	}
	
	private function buildOutData() {
	  if (isBlank($this->outTable)) {
	    return null; 
	  } else {
	    return $this->outTable . ":" . $this->outCols;
	  }
	}

	private function buildOption($optionLine) {
		return new Option(
		  null, 
		  $this->id, 
		  $optionLine->uid, 
		  $optionLine->desc, 
		  $optionLine->text, 
		  null, 
		  $optionLine->coords, 
		  null, 
		  $optionLine->sync, 
		  $optionLine->cpt,
		  $optionLine->trackCat
		  );
	}

	public function setFromDatabase($dto) {	
		$this->id = $dto->id;
		$this->parId = $dto->parId;
		$this->uid = $dto->uid;
		$this->desc = $dto->desc;
		$this->bt = $dto->bt;
		$this->at = $dto->at;
		$this->btms = $dto->btms;
		$this->atms = $dto->atms;
		$this->btmu = $dto->btmu;
		$this->atmu = $dto->atmu;
		$this->listType = $dto->listType;
		$this->break = $dto->break;
		$this->test = $dto->test;
		$this->setOutData($dto->outData);
		$this->inActions = $dto->inActions;
		$this->inTable = $dto->inTable;
		$this->setActions($dto->actions);
		$this->defix = $dto->defix;
		$this->mcap = $dto->mcap;
		$this->mcap2 = $dto->mcap2;
		$this->sortOrder = $dto->sortOrder;
		$this->sync = $dto->sync;
		$this->inTable = $dto->inTable;
		$this->dsync = $dto->dsync;
		$this->billing = $dto->billing;
		
		// Iterate thru options and create appropriate OptionLines
		$this->singleOptions = array();
		$this->multiOptions = array();
		$this->multiOptions2 = array();
		$i = 0;
		$mix = (is_null($dto->mix)) ? 998 : $dto->mix;
		$mix2 = (is_null($dto->mix2)) ? 999 : $dto->mix2;
		$this->anyMulti = false;
		foreach ($dto->options as $k => $option) {
			$optionLine = OptionLine::fromOption($option);
			if ($i < $mix) {
				$this->singleOptions[] = $optionLine;
			} else if ($i < $mix2) {
				$this->multiOptions[] = $optionLine;
				$this->anyMulti = true;
			} else {
				$this->multiOptions2[] = $optionLine;
			}
			$i++;
		}

		// Adjust defix to account for blank options added
		if ($this->defix >= count($this->singleOptions) + count($this->multiOptions)) {
			$this->defix = $this->defix + self::BLANK_LINES * 2;
		} else if ($this->defix >= count($this->singleOptions)) {
			$this->defix = $this->defix + self::BLANK_LINES;
		}
		$this->addBlanksToOptions();
	}

	public function setFromPost($addOptionBlanks = true) {
		$this->templateId = $_POST["templateId"];
		$this->sectionId = $_POST["sectionId"];
		$this->id = $_POST["id"];
		$this->parId = $_POST["parId"];
		$this->uid = $_POST["uid"];
		$this->desc = $_POST["desc"];
		$this->bt = $_POST["bt"];
		$this->at = $_POST["at"];
		$this->btms = $_POST["btms"];
		$this->atms = $_POST["atms"];
		$this->btmu = $_POST["btmu"];
		$this->atmu = $_POST["atmu"];
		$this->listType = $_POST["listType"];
		$this->break = $_POST["break"];
		$this->test = $_POST["test"];
		$this->defix = isset($_POST["defix"]) ? $_POST["defix"] : null;
		$this->mix = $_POST["mix"];
		$this->mix2 = $_POST["mix2"];
		$this->mcap = $_POST["mcap"];
		// $this->mcap2 = $_POST["mcap2"];
		$this->sortOrder = $_POST["sortOrder"];
		$this->sync = $_POST["sync"];
		$this->outTable = $_POST["outTable"];
		$this->outCols = $_POST["outCols"];
		$this->inActions = $_POST["inActions"];
		$this->inTable = $_POST["inTable"];
		$this->dsync = $_POST["dsync"];
		$this->billing = $_POST["billing"];
		
		// Gather actions
		$ifs = isset($_POST["actionIfs"]) ? $_POST["actionIfs"] : null;
		$thens = isset($_POST["actionThens"]) ? $_POST["actionThens"] : null;
		$this->actionIfs = array();
		$this->actionThens = array();
		for ($i = 0; $i < count($ifs); $i++) {
			if (! isBlank($ifs[$i]) || ! isBlank($thens[$i])) {
				$this->actionIfs[] = $ifs[$i];
				$this->actionThens[] = $thens[$i];
			}
		}
		$this->addBlanksToActions();

		// Gather options; note that uid for options will be copied from the desc field
		// $uid = $_POST["optUid"];
		$desc = $_POST["optDesc"];
		$text = $_POST["optText"];
		$sync = $_POST["optSync"];
    $cpt = $_POST["optCpt"];
    $track = $_POST["optTrack"];
    $coords = $_POST['optCoords'];
    $this->singleOptions = array();
		$this->multiOptions = array();
		$this->multiOptions2 = array();
		if (count($desc) > 0 && $this->defix == null) {
			$this->defix = 0;
		}
		$origDefix = $this->defix;
		for ($i = 0; $i < count($desc); $i++) {
			if (! isBlank($desc[$i]) || ! isBlank($text[$i])) {
				if ($i < $this->mix) {
					// All single options share the first single option's sync ID
					$optionLine = new OptionLine($desc[$i], $desc[$i], $text[$i], $sync[0], $cpt[$i], $track[$i], $coords[$i]);
					$this->singleOptions[] = $optionLine;
				} else if ($i < $this->mix2) {
					$optionLine = new OptionLine($desc[$i], $desc[$i], $text[$i], $sync[$i], $cpt[$i], $track[$i], $coords[$i]);
					$this->multiOptions[] = $optionLine;
				} else {
					$optionLine = new OptionLine($desc[$i], $desc[$i], $text[$i], $sync[$i], $cpt[$i], $track[$i], $coords[$i]);
					$this->multiOptions2[] = $optionLine;
				}
			} else {
				if ($i <= $origDefix && $this->defix > 0) {
					$this->defix--;
				}
			}
		}
		if ($addOptionBlanks) {
		  $this->addBlanksToOptions();
		}
	}

  private function buildQuestionBreakCombo() {
    $combos = array();
    $combos["0"] = "0 - End with a period (\".\") and stay in paragraph";
    $combos["1"] = "1 - Continue with a space (\" \") and stay in paragraph";
    $combos["2"] = "2 - Continue with a colon (\":\") and stay in paragraph";
    $combos["3"] = "3 - Continue with a semicolon (\";\") and stay in paragraph";
    $combos["4"] = "4 - End with a period and start a new line";
    $combos["5"] = "5 - End with a period and start a new paragraph";
    $combos["6"] = "6 - Start a new line (no end character)";
    $combos["7"] = "7 - List fragment";
    $this->breaks = $combos;
  }

  private function buildListTypeCombo() {
    $combos = array();
    $combos["0"] = "0 - Comma-delimited";
    $combos["1"] = "1 - Regular list";
    $combos["2"] = "2 - Bulleted list";
    $combos["3"] = "3 - Space-delimited";
    $this->listTypes = $combos;
  }
  
	private function addBlanksToActions() {
		for ($i = 0; $i < self::BLANK_ACTIONS; $i++) {
			$this->actionIfs[] = null;
			$this->actionThens[] = null;
		}
	}

  public function sortOptions($byDesc = true) {
    foreach ($this->multiOptions as &$line) {
      $line->sort = strtoupper(($byDesc) ? $line->desc : (($line->text != null) ? $line->text : $line->desc));
    }
    sort($this->multiOptions);
    $this->addBlanksToOptions();
  }
  public function sortSingleOptions() {
    sort($this->singleOptions);
    $this->addBlanksToOptions();
  }
  
	private function addBlanksToOptions() {
		$this->singleOptions = $this->addBlanks($this->singleOptions);
		$this->mix = count($this->singleOptions);
		$this->multiOptions = $this->addBlanks($this->multiOptions);
		$this->mix2 = $this->mix + count($this->multiOptions);
		$this->multiOptions2 = $this->addBlanks($this->multiOptions2);
	}

	private function addBlanks($arr) {
		$blankLine = OptionLine::blankLine();
		for ($i = 0; $i < self::BLANK_LINES; $i++) {
			$arr[] = $blankLine;
		}
		return $arr;
	}
	
	public function insertBlankAfter($ix) {
	  $ix -= $this->mix - 1;
    array_splice($this->multiOptions, $ix, 0, "");
    $this->multiOptions[$ix] = OptionLine::blankLine();
	}
}

class OptionLine {
  //
  public $sort;  // bogus field declared first to be the sortable field
  public $desc;  
  public $uid;
	public $text;
	public $coords; 
	public $sync;
  public $cpt;
  public $trackCat;  
  //
	public function __construct($uid, $desc, $text, $sync, $cpt, $trackCat, $coords) {
		$this->uid = $uid;
		$this->desc = $desc;
		$this->text = $text;
		$this->coords = $coords;
		$this->sync = $sync;
    $this->cpt = $cpt;
    $this->trackCat = $trackCat;
    $this->coords = $coords;
	}
	//
	public static function fromOption($option) {
	  return new OptionLine($option->uid, $option->desc, $option->text, $option->sync, $option->cpt, $option->trackCat, $option->coords);
	}
	public static function blankLine() {
	  return new OptionLine("", "", "", "", "", "", "");
	}
}
?>