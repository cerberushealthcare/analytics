<?php
//
class Client_Ci_Ccd extends Client_Ci {
  //
  static function create($ugid) {
    return parent::create($ugid, Address_Ci_Ccd::create());
  }
  static function fetch($cid) {
    return parent::fetch($cid, Address_Ci_Ccd::create($cid));
  }
  //
  public function getDiags($ccd) {
    return Diagnosis_Ci_Ccd::all($this->userGroupId, $this->clientId, $ccd->getSection_Problems());
  }
  public function getMeds($ccd) {
    return Med_Ci_Ccd::all($this->userGroupId, $this->clientId, $ccd->getSection_Meds());
  }
  public function getAllergies($ccd) {
    return Allergy_Ci_Ccd::all($this->userGroupId, $this->clientId, $ccd->getSection_Alerts());
  }
  public function saveDemo(/*ContinuityCareDocument*/$ccd) {
    $patientRole = $ccd->getPatientRole();
    $this->setFrom($patientRole);
    $this->Address->setFrom($patientRole);
    //$this->Address->tableId = 5;
    return parent::saveDemo();
  }
  public function import(/*ContinuityCareDocument*/$ccd) {
    //Logger::debug('ClinicalImport_Ccd::import: Trace is ' . getStackTrace());
	//Logger::debug('ClinicalImport_Ccd: Entered import.');
    $ugid = $this->userGroupId;
    $cid = $this->clientId;
	/*ob_start();
	debug_print_backtrace();
	$trace = ob_get_contents();
	ob_end_clean();
	Logger::debug('ClinicalImport_Ccd: Entered import. ugid is ' . $ugid . ', cid is a ' . gettype($cid) . ', trace: ' . $trace);*/
	
	
	try {
		Diagnosis_Ci_Ccd::saveAll($ugid, $cid, $ccd->getSection_Problems()); //This is where it breaks. At this point we would be looking at "DeQuervian's"
	}
	catch (Exception $e) {
		if ($_POST['IS_BATCH']) echo 'ERROR importing the patient diagnoses: ' . $e->getMessage() . ' - continuing with import....';
		Logger::debug('ClinicalImport_Ccd::import: ERROR importing the patient diagnoses: ' . $e->getMessage() . ' - continuing with import....');
	}
	
	Logger::debug('ClinicalImport_Ccd::import: Imported the patient diagnoses. Importing allergies. Alerts is a ' . gettype($ccd->getSection_Alerts()));
    
	try {
		Allergy_Ci_Ccd::saveAll($ugid, $cid, $ccd->getSection_Alerts());
	}
	catch (Exception $e) {
		if ($_POST['IS_BATCH']) echo 'ERROR importing the patient allergies: ' . $e->getMessage() . ' - continuing with import....';
		Logger::debug('ClinicalImport_Ccd::import: ERROR importing the patient allergies: ' . $e->getMessage() . ' - continuing with import....');
	}
	
	Logger::debug('ClinicalImport_Ccd::import: Imported the patient allergies. Importing meds....');
    
	try {
		Med_Ci_Ccd::saveAll($ugid, $cid, $ccd->getSection_Meds());
	}
	catch (Exception $e) {
		if ($_POST['IS_BATCH']) echo 'ERROR importing the patient medications: ' . $e->getMessage() . ' - continuing with import....';
		Logger::debug('ClinicalImport_Ccd::import: ERROR importing the patient medications: ' . $e->getMessage() . ' - continuing with import....');
	}
	
	Logger::debug('ClinicalImport_Ccd::import: Imported the patient meds!');
	Logger::debug('ClinicalImport_Ccd::import: Importing vitals....');
	//12/20/16 - NEW VITALS import
	try {
		Vitals_Ci_Ccd::saveAll($ugid, $cid, $ccd->getSection_Vitals());
	}
	catch (Exception $e) {
		if ($_POST['IS_BATCH']) echo 'ERROR importing the patient vitals: ' . $e->getMessage() . ' - continuing with import....';
		Logger::debug('ClinicalImport_Ccd::import: ERROR importing the patient vitals: ' . $e->getMessage() . ' - continuing with import....');
	}
    
    return;  // do not need to do these for mu2
    Immun_Ci_Ccd::saveAll($ugid, $cid, $ccd->getSection_Immuns());
    Proc_Ci_Ccd::saveAll($ugid, $cid, $ccd->getSection_Procedures());
    Proc_Ci_Results_Ccd::saveAll($ugid, $cid, $ccd->getSection_Results());
  }
  //
  protected function setFrom(/*Ccd_PatientRole*/$patientRole) {
    $patient = $patientRole->patient;
    $name = $patient->first('name');
    $this->lastName = $name->getLast();
    $this->firstName = $name->getFirst();
    $this->middleName = $name->getMiddle();
    $this->sex = $patient->administrativeGenderCode->asSex();
    $this->birth = $patient->birthTime->getSqlDate();
  }
}
class Address_Ci_Ccd extends Address_Ci {
  //
  public function setFrom(/*Ccd_PatientRole*/$patientRole) {
    $addr = $patientRole->first('addr');
    if ($addr) {
      $this->addr1 = $addr->getAddr1();
      $this->addr2 = $addr->getAddr2();
      $this->city = $addr->city;
      $this->state = $addr->state;
      $this->zip = $addr->postalCode;
    }
	//echo 'ClinicalImport_Ccd setFrom: Got data ' . $this->type;// . '|' . $this->addr2 . '|' . $city . '|' . $state . '|' . $zip;
    $phone = $patientRole->first('telecom');
    if ($phone) {
      $this->phone1 = $phone->getValue();
      $this->phone1Type = static::PHONE_TYPE_PRIMARY;
    }
  }
}
class Diagnosis_Ci_Ccd extends Diagnosis_Ci {
  //
  static function all($ugid, $cid, /*Ccd_Section_Problems*/$problems) {
	  
	  ob_start();
	  var_dump($problems);
	  $content = ob_get_contents();
	  ob_end_clean();
	  
	  
	Logger::debug('Diagnosis_Ci_Ccd::all: Got ugid ' . $ugid . ', cid ' . $cid . ' and problems as string with length ' . strlen($content));
    $recs = array();
    if ($problems && $problems->has()) { 
      foreach ($problems->entry as $problem) {
		Logger::debug('ClinicalImport_Ccd::all: We got a problem. The text is ' . $text);
        $recs[] = static::from($ugid, $cid, $problem, $problems->text);
	  }
    }
    return $recs;
  }
  static function saveAll($ugid, $cid, /*Ccd_Section_Problems*/$problems) {
	  
	  ob_start();
	  var_dump($problems);
	  $content = ob_get_contents();
	  ob_end_clean();
	  
  
	Logger::debug('Diagnosis_Ci_Ccd::saveAll:Entered with ' . $ugid . ', cid ' . $cid . ' and problems as a string with length ' . strlen($content));
    $us = static::all($ugid, $cid, $problems);
    foreach ($us as $me) {
      $me->save();
	}
	Logger::debug('Diagnosis_Ci_Ccd::saveAll: Finished.');
  }
  static function from($ugid, $cid, /*Ccd_ProblemEntry*/$problem, /*Ccd_Text*/$text) {
    //logit_r($problem, 'ClinicalImport_Ccd::from: The problem');
    //logit_r($text, 'text');
    $date = $problem->getSqlDate();
    $cd = $problem->getIcdCode();
    //logit_r($cd, 'geticdcode');
    $icd = null;
    $snomed = null;
    $desc = null;
    if ($cd) {
      $icd = $cd->_code;
      $desc = $cd->_displayName;
    } else {
      $cd = $problem->getSnomed();
      if ($cd) {
        $snomed = $cd->_code;
        $desc = $cd->_displayName;
      } else {
        if ($text)
          $desc = $text->get($problem->getTextRef());
        if ($desc == null) 
          $desc = $problem->getValueName();
      }
    }
	Logger::debug('ClinicalImport_Ccd::from: Getting status....');
    $status = static::asStatus($problem->getStatus());
	Logger::debug('ClinicalImport_Ccd::from: Received status. Creating with params ' . $ugid . '|' . $cid . '|' . $date . '|' . $desc);
    $me = static::create($ugid, $cid, $icd, $date, $desc, $status, $snomed);
	Logger::debug('ClinicalImport_Ccd::from: We created it. Returning a ' . gettype($me)); //this doesn't get echoed.
    return $me;
  }
  protected static function asStatus($s) {
    switch ($s) {
      case '55561003':
        return static::STATUS_ACTIVE;    
      case '73425007':
        return static::STATUS_INACTIVE;
      case '90734009':
        return static::STATUS_CHRONIC;
      case '7087005':
        return static::STATUS_INTERMITTENT;
      case '255227004':
        return static::STATUS_RECURRENT;
      case '415684004':
        return static::STATUS_RULE_OUT;
      case '410516002':    
        return static::STATUS_RULED_OUT;
      case '413322009':    
        return static::STATUS_RESOLVED;
      default:
        return static::STATUS_ACTIVE;
    } 
  }
  protected static function asText($s, $icd) {
    $icd = '(' . $icd . ')';
    $text = str_replace($icd, '', $s);
    $text = str_replace(';', '', $text);
    return $text;
  }
}

class Vitals_Ci_Ccd extends Vital_Ci implements NoAudit {
	//
	static function all($ugid, $cid, /*Ccd_Section_Vitals_Sql*/$vitals) {
		$recs = array();
		//Logger::debug('ClinicalImport_Ccd Vitals_Ci_Ccd::all: Entered with ' . $ugid . ' | ' . $cid);
		//Logger::debug('ClinicalImport_Ccd Vitals_Ci_Ccd::all: Entered with vitals as ' . print_r($vitals, true));
		if ($vitals) {// && $vitals->has()) {
			Logger::debug('ClinicalImport_Ccd Vitals_Ci_Ccd::all: There are vitals!');
			//foreach ($vitals as &$vitalsOccurance) {
				//Logger::debug('ClinicalImport_Ccd Vitals_Ci_Ccd::all: Got a vitals entry: ' . print_r($vitalsOccurance, true));
				$recs[] = static::from($ugid, $cid, $vitals);
			//}
		}
		Logger::debug('ClinicalImport_Ccd Vitals_Ci_Ccd::all: Returning ' . print_r($recs, true));
		return $recs;
	}
	static function saveAll($ugid, $cid, /*Ccd_Section_Alerts*/$vitals) {
		Logger::debug('ClinicalImport_Ccd Vitals_Ci_Ccd::saveAll: Entered with vitals as ' . print_r(var_dump($vitals), true));
		$us = static::all($ugid, $cid, $vitals);
		logit_r($us, 'us vitals');
		foreach ($us as $me) {
			Logger::debug('Vitals_Ci_Ccd: gonna save....');
			$me->save();
			//parent::save();
		}
	}
	
	static function from($ugid, $cid, /*Ccd_Entry_Vitals*/$vital) {
		Logger::debug('ClinicalImport_Ccd::Vitals_Ci_Ccd::from: Got alert as object type ' . get_class($vital) . '. Contents: ' . print_r($vital, true));
		Logger::debug('ClinicalImport_Ccd::Vitals_Ci_Ccd::from: Trace is ' . getStackTrace());
		//The below code fails because $vital is a object of type XmlRec, when it should be a type of Ccd_VitalEntry.
		//we are getting a Vitals_Ci_Ccd object here and it doesn't have the getSqlDate() method.
		$date = $vital->getSqlDate();
		$pulse = $vital->getPulse();
		$resp = $vital->getResp();
		$bpLoc = $vital->getBloodPressureLoc();
		$bpDia = $vital->getBloodPressureDiastolic();
		$bpSys = $vital->getBloodPressureSystolic();
		$temperature = $vital->getTemperature();
		$wt = $vital->getWeight();
		$height = $vital->getHeight();
		$bmi = $vital->getBMI();
		Logger::debug('Blood pressure is ' . $bpDia . ' / ' . $bpSys . ' ' . $bpLoc);
		//$mat = $vital->getMaterial();
		//$name = static::asName($mat, $text);
		//$desc = static::asDesc($mat, $text);
		//$ = $vital->getAmtText();
		$me = static::create($ugid, $cid, $date, $pulse, $resp, $bpDia, $bpSys, $bpLoc, $temperature, $wt, $height, $bmi);//$date, $pulse, $weight);
		logit_r($me, 'vital_ci_ccd');
		return $me;
	}
	
	//Or maybe:
	
	///static function from($ugid, $cid, $date, $pulse, $weight) {
/*	$rec = self::asCriteria($cid);
		$rec->pulse = $pulse;
		$rec->weight = $weight;
		return $rec;
	}*/
	
	/*protected static function asAgent($alert, $text) {
		Logger::debug('ClinicalImport_Ccd::Ci CCD vitals::asAgent: Got trace ' . getStackTrace());
		$agent = null;
		$entity = $alert->getEntity();
		if ($entity)
			$agent = $entity->getName();
			if ($agent == null && $text)
				$agent = $text->get($alert->getTextRef());
				return $agent;
	}
	protected static function asReactions($alert, $text) {
		$a = array();
		$obs = $alert->getReactionObs();
		foreach ($obs as $ob)
			$a[] = static::asReaction($ob, $text);
			return implode('; ', $a);
	}
	protected static function asReaction($ob, $text) {
		$r = $ob->getValueName();
		if ($r == null && $text)
			$r = $text->get($ob->getTextRef());
			return $r;
	}*/
}

class Allergy_Ci_Ccd extends Allergy_Ci {
  //
  static function all($ugid, $cid, /*Ccd_Section_Alerts*/$alerts) {
    $recs = array();
    if ($alerts && $alerts->has()) { 
      foreach ($alerts->entry as $alert) 
        $recs[] = static::from($ugid, $cid, $alert, $alerts->text);
    }
    return $recs;
  }
  static function saveAll($ugid, $cid, /*Ccd_Section_Alerts*/$alerts) {
    $us = static::all($ugid, $cid, $alerts);
    logit_r($us, 'us allergies');
    foreach ($us as $me) {
      logit_r('save 123');
      $me->save();
    }
  }
  static function from($ugid, $cid, /*Ccd_AlertEntry*/$alert, /*Ccd_Text*/$text) {
  	Logger::debug('ClinicalImport_Ccd::Allergy_Ci_Ccd::from: Got alert as object type ' . get_class($alert));
    Logger::debug('ClinicalImport_Ccd::allergy_Ci_Ccd::from: Trace is ' . getStackTrace());
    //Ccd_AlertEntry is type type of $alert, which is correct.
    $agent = static::asAgent($alert, $text);
    $reactions = static::asReactions($alert, $text);
    $active = $alert->getStatus() != '73425007'; 
    $me = static::create($ugid, $cid, $agent, $reactions, $active);
    $me->date = $alert->getDate();
    return $me;
  }
  protected static function asAgent($alert, $text) {
    $agent = null;
    $entity = $alert->getEntity();
    if ($entity) 
      $agent = $entity->getName();
    if ($agent == null && $text)
      $agent = $text->get($alert->getTextRef());
    return $agent;
  }
  protected static function asReactions($alert, $text) {
    $a = array();
    $obs = $alert->getReactionObs();
    foreach ($obs as $ob) 
      $a[] = static::asReaction($ob, $text);
    return implode('; ', $a);
  }
  protected static function asReaction($ob, $text) {
    $r = $ob->getValueName();
    if ($r == null && $text)
      $r = $text->get($ob->getTextRef());
    return $r;
  }
}
class Med_Ci_Ccd extends Med_Ci {
  //
  static function all($ugid, $cid, /*Ccd_Section_Meds*/$meds) {
    $recs = array();
    if ($meds && $meds->has()) { 
      foreach ($meds->entry as $med) 
        $recs[] = static::from($ugid, $cid, $med, $meds->text);
    }
    return $recs;
  }
  static function saveAll($ugid, $cid, /*Ccd_Section_Meds*/$meds) {
    $us = static::all($ugid, $cid, $meds);
    foreach ($us as $me) 
      $me->save();
  }
  static function from($ugid, $cid, /*Ccd_MedEntry*/$med, /*Ccd_Text*/$text) {
    $date = $med->getSqlDate();
    $mat = $med->getMaterial();
    $name = static::asName($mat, $text);
    //$desc = static::asDesc($mat, $text);
    $active = $med->getStatus() != '73425007'; 
    $amt = $med->getAmtText();
    $freq = $med->getFreqText();
    $route = $med->getRouteText();
    $me = static::create($ugid, $cid, $date, $name, null, $active, $amt, $freq, $route);
    logit_r($me, 'med_ci_ccd');
    return $me;
  }
  protected static function asName($mat, $text) {
    $name = $mat->getName();
    if ($name == null && $text)
      $name = $text->get($mat->getTextRef());
    return $name;
  }
  protected static function asDesc($mat, $text) {
    // TODO 
  }
}
class Immun_Ci_Ccd extends Immun_Ci {
  //
  static function saveAll($ugid, $cid, /*Ccd_Section_Immuns*/$imms) {
    if ($imms && $imms->has()) { 
      foreach ($imms->entry as $imm) 
        static::from($ugid, $cid, $imm, $imms->text)->save();
    }
  }
  static function from($ugid, $cid, /*Ccd_ImmunEntry*/$imm, /*Ccd_Text*/$text) {
    $dateGiven = $imm->getSqlDate();
    $mat = $imm->getMaterial();
    $name = static::asName($mat, $text);
    return static::create($ugid, $cid, $dateGiven, $name);
  }
  protected static function asName($mat, $text) {
    $name = $mat->getName();
    if ($name == null && $text)
      $name = $text->get($mat->getTextRef());
    return $name;
  }
}
class Ipc_Ci_Ccd extends Ipc_Ci {
  //
  static function getIpc($ugid, $name, $cat = self::CAT_PROC) {
    $me = static::fetchOrCreate($ugid, $name, $cat);
    return $me->ipc;
  }
  static function getIpc_asResult($ugid, $name) {
    return static::getIpc($ugid, $name, static::CAT_TEST);
  }
}
class Proc_Ci_Ccd extends Proc_Ci {
  //
  static function saveAll($ugid, $cid, /*Ccd_Section_Procedures*/$procs) {
    if ($procs && $procs->has()) { 
      foreach ($procs->entry as $proc) 
        static::from($ugid, $cid, $proc, $procs->text)->save();
    }
  }
  static function from($ugid, $cid, /*Ccd_ProcEntry*/$proc, /*Ccd_Text*/$text) {
    $name = static::asName($proc, $text);
    $ipc = Ipc_Ci_Ccd::getIpc($ugid, $name);
    $date = $proc->getSqlDate();
    return static::create($ugid, $cid, $date, $ipc);
  }
  protected static function asName($proc, $text) {
    $name = $proc->getName();
    if ($name == null && $text)
      $name = $text->get($proc->getTextRef());
    return $name;
  }
}
class Proc_Ci_Results_Ccd extends Proc_Ci_Results {
  //
  static function saveAll($ugid, $cid, /*Ccd_Section_Results*/$results) {
    if ($results && $results->has()) {
      foreach ($results->entry as $result) 
        if ($result->has())
          static::from($ugid, $cid, $result)->save();
    }
  }
  static function from($ugid, $cid, /*Ccd_ResultEntry*/$result) {
    $name = $result->getProcName();
    $ipc = Ipc_Ci_Ccd::getIpc_asResult($ugid, $name);
    $results = Result_Ci_Ccd::all($ugid, $cid, $result->getResultObs());
    $date = $result->getSqlDate();
    return static::create($ugid, $cid, $date, $ipc, $results);
  }
}
class Result_Ci_Ccd extends Result_Ci {
  //
  static function all($ugid, $cid, /*Ccd_Ob[]*/$obs) {
    $us = array();
    foreach ($obs as $ob)
      $us[] = static::from($ugid, $cid, $ob);
    return $us;
  }
  static function from($ugid, $cid, /*Ccd_Ob*/$ob) {
    $name = $ob->getName();
    $ipc = Ipc_Ci_Ccd::getIpc_asResult($ugid, $name);
    $value = getr($ob, 'value._value');
    $unit = static::asUnit($ob); 
    $comment = null;
    $interpret = static::asInterpret(getr($ob, 'interpretationCode._code'));
    return static::create($cid, $ipc, $value, $unit, $comment, $interpret);
  }
  protected static function asUnit($ob) {
    $unit = getr($ob, 'value._unit');
    if ($unit == '1')
      $unit = null;
    return $unit;
  }
  protected static function asInterpret($s) {
    if ($s) {
      $desc = geta(static::$INTERPRET_CODES, $s);
      if ($desc)
        return $s;
      $code = geta(static::$INTERPRET_MAP, strtoupper($s));
      if ($code)
        return $code;
      if (strpos($code, 'ABNORMAL') !== false)
        return static::IC_ABNORMAL;
    }
  }
}
