<?php
require_once 'php/data/xml/clinical/ClinicalXml.php';
require_once 'CcdData.php';
require_once 'php/data/xml/ccd/POCD_MT000040.php';
require_once 'php/data/xml/ccd/datatypes_base.php';
require_once 'php/data/xml/ccd/Section_Types.php';
//
/**
 * Continuity Care Document (CCD) 
 */
class ContinuityCareDocument extends ClinicalXml {
  //
  public $_nullFlavor;
  public $_classCode;
  public $_moodCode;
  public $realmCode;
  public $typeId; 
  public $templateId = 'Ccd_TemplateId[]';
  public $id; 
  public $code;
  public $title;
  public $effectiveTime; 
  public $confidentialityCode; 
  public $languageCode;
  public $recordTarget = 'Ccd_RecordTarget[]'; 
  public $author = '[]';
  public $custodian;
  public $informationRecipient = '[]';
  public $legalAuthenticator;
  public $authenticator = '[]';
  public $participant = '[]';
  public $documentationOf;
  public $relatedDocument = '[]';
  public $authorization = '[]';
  public $componentOf;
  public $component = 'Ccd_Component[]';
  //
  static $TYPE = self::TYPE_CCD;
  //
  public function getDocId() {
    return getr($this->getPatientRole(), 'id');
  }
  public function /*Ccd_PatientRole*/getPatientRole() {
    return getr($this->first('recordTarget'), 'patientRole');
  }
  public function getSection_Problems() {
    return $this->getSection('Ccd_Section_Problems');
  }
  public function getSection_Vitals() {
    return $this->getSection('Ccd_Section_Vitals_Sql');
  }
  public function getSection_Alerts() {
    return $this->getSection('Ccd_Section_Alerts');
  }
  public function getSection_Meds() {
    return $this->getSection('Ccd_Section_Meds');
  }
  public function getSection_Immuns() {
    return $this->getSection('Ccd_Section_Immuns');
  }
  public function getSection_Procedures() {
    return $sthis->getSection('Ccd_Section_Procedures');
  }
  public function getSection_Results() {
    return $this->getSection('Ccd_Section_Results');
  }
  protected function getSection($id) {
    $body = $this->first('component')->structuredBody;
    Logger::debug('php/data/xml/clinical/ccd/ContinutyCareDocument::getSection: Body is a ' . gettype($body) . ', id is ' . gettype($id) . ' ' . $id);
    Logger::debug('php/data/xml/clinical/ccd/ContinutyCareDocument::getSection:: Body is THIS:');
    //Logger::debug(print_r($body, true));
    $result = $body->getSection($id);
    //Logger::debug('php/data/xml/clinical/ccd/ContinutyCareDocument::getSection:: Returning this: ');
    //Logger::debug(print_r($result, true));
    Logger::debug('------------No section for ' . $id . ' Found in the CCD--------');
    return $result;
  }
}
class Ccd_TemplateId extends XmlRec {
  public $_root;
}
class Ccd_Component extends XmlRec {
  public $structuredBody = 'Ccd_Body';
}
class Ccd_Body extends XmlRec {
  public $component = 'Ccd_BodyComponent[]';
  //
  public function getSection($type) {
  	Logger::debug('php/data/xml/clinical/ccd/CCD.php::getSection: type is a ' . gettype($type) . ' ' . $type);
    foreach ($this->component as $comp) {
      Logger::debug('Looping through the components. Type is ' . $comp->getSectionType() . '. Comp: ' . print_r($comp, true));
      if ($comp->getSectionType() == $type)
        return $comp->section;
    }
  }
}
class Ccd_BodyComponent extends XmlRec {
  public $section = 'Ccd_Section';
  //
  public function getSectionType() {
    return get_class($this->section);
  }
}
class Ccd_Section extends XmlRec {
  protected static function getInstanceFor($o) {
    $tids = arrayify($o->templateId);
    foreach ($tids as $tid) {
      switch ($tid->_root) {
        case '2.16.840.1.113883.10.20.1.2':
        case '2.16.840.1.113883.10.20.22.2.6.1'/*CCDA*/:
          return new Ccd_Section_Alerts();
        case '2.16.840.1.113883.10.20.1.14':
          return new Ccd_Section_Results();
        case '2.16.840.1.113883.10.20.1.11':
        case '2.16.840.1.113883.10.20.22.2.5.1'/*CCDA*/:
          return new Ccd_Section_Problems();
        case '2.16.840.1.113883.10.20.1.3':
          return new Ccd_Section_Encounters();
        case '2.16.840.1.113883.10.20.1.6':
          return new Ccd_Section_Immuns();
        case '2.16.840.1.113883.10.20.1.8':
        case '2.16.840.1.113883.10.20.22.2.1.1'/*CCDA*/:
          return new Ccd_Section_Meds();
        case '2.16.840.1.113883.10.20.1.12':
          return new Ccd_Section_Procedures();
        //case '2.16.840.1.113883.10.20.1.16': //12-22-16: Old? When we use the template code below it gets the vitals correctly - 1.16 doesn't find anything
        case '2.16.840.1.113883.10.20.22.2.4':
          return new Ccd_Section_Vitals_Sql();
        case '2.16.840.1.113883.10.20.4.9':
          return new Ccd_Section_MedHx();
        case '2.16.840.1.113883.10.20.1.15':
          return new Ccd_Section_SocHx();
        case '2.16.840.1.113883.10.20.1.10':
          return new Ccd_Section_Plan();
      }
    }
    return new static();
  }
  //
  public $templateId = 'Ccd_TemplateId[]';
  public $code;
  public $title;
  public $text = 'Ccd_Text';
  public $entry = '[]';
  //
  public function has() {
    return ! empty($this->entry);
  }
}
class Ccd_Section_Problems extends Ccd_Section {
  public $entry = 'Ccd_ProblemEntry[]';
}
class Ccd_ProblemEntry extends Ccd_Entry_Act {
  //
  public function /*string(SNOMED)*/getProblemCode() {
    return $this->act->getOb()->code->_code;  
  }
  public function /*Ccd_CD*/getIcdCode() {
    logit_r($this->act->getOb()->value, 'value to getIcd()');
    return $this->act->getOb()->value->getIcd();
  }
  public function /*Ccd_CD*/getSnomed() {
    return $this->act->getOb()->value->getSnomed();
  }
}
class Ccd_Section_Alerts extends Ccd_Section {
  public $entry = 'Ccd_AlertEntry[]';
}
class Ccd_AlertEntry extends Ccd_Entry_Act {
  //
  public function /*Ccd_PlayingEntity*/getEntity() {
    return $this->act->getOb()->getEntity();
  }
  public function /*Ccd_Ob[]*/getReactionObs() {
    return $this->act->getOb()->getObs('MFST');
  }
  public function getDate() {
    $e = get($this->act, 'effectiveTime');
    if ($e && $e instanceof Ccd_IVL_TS) {
      return dateToString($e->low->_value);
    }
  }
  
}
//12-27-16 - added support for vitals here
class Ccd_Section_Vitals_Sql extends Ccd_Section {
	public $entry = 'Ccd_Vitals[]';
	
	public function getSqlDate() {
		return $this->text->table->tbody->tr->td[0];
		//$e = $this->substanceAdministration->first('effectiveTime');
		//if ($e instanceof Ccd_TS || $e instanceof Ccd_IVL_TS)
			//return $e->getSqlDate();
	}
	
	public function getPulse() {
		return $this->text->table->tbody->tr->td[1];
	}
	
	public function getResp() {
		return $this->text->table->tbody->tr->td[2];
	}
	
	public function getBloodPressureLoc() {
		$arr = preg_split( "/(\/| )/", $this->text->table->tbody->tr->td[3]);
		Logger::debug('getBloodPressureLoc: Got array as ' . print_r($arr, true) . ', this is ' . $this->text->table->tbody->tr->td[3]);
		return $arr[2];
	}
	
	public function getBloodPressureDiastolic() {
		$arr = preg_split( "/(\/| )/", $this->text->table->tbody->tr->td[3]);
		return $arr[0];
	}
	
	public function getBloodPressureSystolic() {
		$arr = preg_split( "/(\/| )/", $this->text->table->tbody->tr->td[3]);
		return $arr[1];
	}
	
	public function getTemperature() {
		return $this->text->table->tbody->tr->td[4];
	}
	
	public function getWeight() {
		return $this->text->table->tbody->tr->td[5];//->table->tbody->tr->td[6];
	}
	
	public function getHeight() {
		return $this->text->table->tbody->tr->td[6];
	}
	
	public function getBMI() {
		return $this->text->table->tbody->tr->td[7];
	}
}
class Ccd_VitalsEntry extends Ccd_Entry_Act {
	//
	public function /*Ccd_PlayingEntity*/getEntity() {
		return $this->act->getOb()->getEntity();
	}
	public function /*Ccd_Ob[]*/getReactionObs() {
		return $this->act->getOb()->getObs('MFST');
	}
	public function getDate() {
		$e = get($this->act, 'effectiveTime');
		if ($e && $e instanceof Ccd_IVL_TS) {
			return dateToString($e->low->_value);
		}
	}

}
class Ccd_Section_Meds extends Ccd_Section {
  public $entry = 'Ccd_MedEntry[]';
}
class Ccd_MedEntry extends Ccd_Entry_Substance {
  //
  public function getFreqText() {
    $e = $this->getFreqPeriod();
    logit_r($e, 'period');
    if ($e) {
      $freq = $e->_value;
      if ($e->_unit) {
        $e->_unit = strtolower($e->_unit);
        $freq .= $e->_unit;
        if ($e->_unit == 'h') {
          switch ($freq) {
          case '24h':
            return 'DAILY';
          case '12h':
            return 'BID';
          case '8h':
            return 'TID';
          case '6h':
            return 'QID';
          default:
            return 'Q' . $freq;
          }
        }
      }
      return $freq;
    }
  }
  public function /*Ccd_PQ*/getFreqPeriod() {
    $e = get($this->substanceAdministration, 'effectiveTime');
    if (is_array($e) && count($e) > 1) {
      $e = $e[1];
      if ($e && $e instanceof Ccd_PIVL_TS) {
        return $e->period;
      }
    }
  } 
}
class Ccd_Section_Immuns extends Ccd_Section {
  public $entry = 'Ccd_ImmunEntry[]';
}
class Ccd_ImmunEntry extends Ccd_Entry_Substance {
}
class Ccd_Section_Procedures extends Ccd_Section {
  public $entry = 'Ccd_ProcEntry[]';
}
class Ccd_ProcEntry extends Ccd_Entry_Proc {
}
class Ccd_Section_Results extends Ccd_Section {
  public $entry = 'Ccd_ResultEntry[]';
}
class Ccd_ResultEntry extends Ccd_Entry_Organizer {
  //
  public function has() {
    return getr($this, 'organizer') != null;
  }
  public function getProcName() {
    return $this->getName();
  }
  public function /*Ccd_Ob[]*/getResultObs() {
    return $this->organizer->getObs();
  }
}
class Ccd_Section_Encounters extends Ccd_Section {
}
class Ccd_Section_Vitals extends Ccd_Section {
}
class Ccd_Section_MedHx extends Ccd_Section {
}
class Ccd_Section_SocHx extends Ccd_Section {
}
class Ccd_Section_Plan extends Ccd_Section {
}
