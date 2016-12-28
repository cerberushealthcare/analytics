<?php
require_once 'php/data/xml/_XmlRec.php';
//
/** Structures */
class Ccd_Entry_Act extends XmlRec {
  public $act = 'Ccd_Act';
  //
  public function getSqlDate() {
    $e = getr($this->act->getOb(), 'effectiveTime');
    if ($e)
      return $e->getSqlDate();
  }
  public function getTextRef() {
    return $this->act->getOb()->getTextRef();
  }
  public function getValueName() {
    return $this->act->getOb()->getValueName();
  }
  public function /*string(SNOMED)*/getStatus() {
    return getr($this->act->getOb()->getOb('REFR'), 'value._code');
  }
}
class Ccd_Entry_Substance extends XmlRec {
  public $substanceAdministration = 'Ccd_Substance';
  //
  public function getSqlDate() {
    $e = $this->substanceAdministration->first('effectiveTime');
    if ($e instanceof Ccd_TS || $e instanceof Ccd_IVL_TS)
      return $e->getSqlDate();
  }
  public function /*string(SNOMED)*/getStatus() {
    return getr($this->substanceAdministration->getOb('REFR'), 'value._code');
  }
  public function /*Ccd_Material*/getMaterial() {
    return $this->substanceAdministration->consumable->manufacturedProduct->manufacturedMaterial;
  }
  public function getRouteText() {
    $e = get($this->substanceAdministration, 'routeCode');
    if ($e) {
      return $e->_displayName ?: $e->originalText;
    }
  }
  public function getAmtText() {
    $e = get($this->substanceAdministration, 'doseQuantity');
    if ($e) {
      $amt = $e->_value;
      if (get($e, '_unit')) {
        $amt .= ' ' . $e->_unit;
      }
      return $amt;
    }
  }
}
//We don't need a vitals entry object because entries seem to exist for things that may appear
//multiple times in a CCD document. eg. meds, diagnoses....patients can have many entries for those.
//However for vitals, every CCD only has ONE 'entry'.
/*class Ccd_Entry_Vitals extends XmlRec {
	public function getSqlDate() {
		$e = $this->substanceAdministration->first('effectiveTime');
		if ($e instanceof Ccd_TS || $e instanceof Ccd_IVL_TS)
			return $e->getSqlDate();
	}
	
	public function getPulse() {
		return 'PULSE!!';
	}
}*/
class Ccd_Entry_Organizer extends XmlRec {
  public $organizer = 'Ccd_Organizer';
  //
  public function getSqlDate() {
    return $this->organizer->effectiveTime->getSqlDate();
  }
  public function getName() {
    return getr($this->organizer, 'code._displayName'); 
  }
}
class Ccd_Organizer extends XmlRec {
  public $code = 'Ccd_CD';
  public $effectiveTime = 'Ccd_TS';
  public $component = 'Ccd_OrgComponent[]';
  //
  public function getObs() {
    $obs = array();
    foreach ($this->component as $comp)
      $obs[] = $comp->observation;
    return $obs;
  }
}
class Ccd_OrgComponent extends XmlRec {
  public $observation = 'Ccd_Ob';
}
class Ccd_Entry_Proc extends XmlRec {
  public $procedure = 'Ccd_Procedure';
  //
  public function getSqlDate() {
    return $this->procedure->effectiveTime->getSqlDate();
  }
  public function getTextRef() {
    return $this->procedure->getTextRef();
  }
  public function getName() {
    return getr($this->procedure, 'code._displayName'); 
  }
}
class Ccd_Procedure extends XmlRec {
  public $code = 'Ccd_CD';
  public $text = 'Ccd_TextRef';
  public $effectiveTime = 'Ccd_IVL_TS';
  //
  public function getTextRef() {
    if ($this->text)
      return $this->text->getRef();
  }
}
class Ccd_Substance extends XmlRec {
  public $effectiveTime = 'Ccd_SXCM_TS[]';
  public $consumable = 'Ccd_Consumable';  
  public $entryRelationship = 'Ccd_EntryRel[]';
  //
  public function getOb($typeCode) {
    $obs = $this->getObs($typeCode);
    if ($obs)
      return reset($obs);
  }
  public function getObs($typeCode = null) {
    $obs = array();
    if ($this->entryRelationship)
      foreach ($this->entryRelationship as $rel)
        if ($typeCode == null || $rel->_typeCode == $typeCode) 
          $obs[] = $rel->observation;
    return $obs;
  }
}
class Ccd_Consumable extends XmlRec {
  public $manufacturedProduct = 'Ccd_Product';
}
class Ccd_Product extends XmlRec {
  public $manufacturedMaterial = 'Ccd_Material';
} 
class Ccd_Material extends XmlRec {
  public $code = 'Ccd_CD';
  public $name;
  //
  public function getName() {
    return $this->code->_displayName ?: $this->name;
  }
  public function getRefText() {
    return getr($this->code, 'originalText.reference._value');
  }
}
class Ccd_Act extends XmlRec {
  public $effectiveTime = 'Ccd_IVL_TS';
  public $entryRelationship = 'Ccd_EntryRel[]';
  //
  public function getOb() {
    return $this->first('entryRelationship')->observation;
  }
}
class Ccd_EntryRel extends XmlRec {
  public $observation = 'Ccd_Ob';
}
class Ccd_Ob extends XmlRec {
  public $code = 'Ccd_CD';
  public $text = 'Ccd_TextRef';
  public $effectiveTime = 'Ccd_IVL_TS';
  public $value = 'Ccd_Value';
  public $interpretationCode = 'Ccd_CD';
  public $entryRelationship = 'Ccd_EntryRel[]';
  public $participant = 'Ccd_Participant[]';
  //
  public function getOb($typeCode = null) {
    $obs = $this->getObs($typeCode);
    if ($obs)
      return reset($obs);
  }
  public function getObs($typeCode = null) {
    $obs = array();
    if ($this->entryRelationship) {
      foreach ($this->entryRelationship as $rel)
        if ($typeCode == null || $rel->_typeCode == $typeCode) 
          $obs[] = $rel->observation;
    }
    return $obs;
  }
  public function getTextRef() {
    if ($this->text)
      return $this->text->getRef();
  }
  public function getName() {
    return getr($this->code, '_displayName');
  }
  public function getValueName() {
    return getr($this->value, '_displayName');
  }
  public function getEntity() {
    $p = $this->first('participant');
    if ($p)  
      return getr($p, 'participantRole.playingEntity');
  }
}
class Ccd_Participant extends XmlRec {
  public $participantRole = 'Ccd_ParticipantRole';
} 
class Ccd_ParticipantRole extends XmlRec {
  public $playingEntity = 'Ccd_PlayingEntity';
}
class Ccd_PlayingEntity extends XmlRec {
  public $code = 'Ccd_CD';
  public $name;
  //
  public function getName() {
    return $this->code->_displayName ?: $this->name;
  }
}
class Ccd_RecordTarget extends XmlRec {
  public $patientRole = 'Ccd_PatientRole';
}
class Ccd_PatientRole extends XmlRec {
  public $addr = 'Ccd_AD[]';
  public $telecom = 'Ccd_TEL[]';
  public $patient = 'Ccd_Patient';
  public $providerOrganization = 'Ccd_Organization';
}
class Ccd_Patient extends XmlRec {
  public $name = 'Ccd_PN[]';
  public $administrativeGenderCode = 'Ccd_CE_Gender';
  public $birthTime = 'Ccd_TS';
  public $maritalStatusCode = 'Ccd_CD';
  public $raceCode  = 'Ccd_CD';
  public $guardian = '[]';
  public $birthplace;
  public $languageCommunication;
}
class Ccd_Organization extends XmlRec {
  public $id;
  public $name;
  public $telecom = 'Ccd_TEL[]';
  public $addr = 'Ccd_AD[]';
}
/** Datatypes */
class Ccd_ANY extends XmlRec {
  public $_nullFlavor;
}
class Ccd_CD extends Ccd_ANY {
  public $_code;
  public $_codeSystem;
  public $_codeSystemName;
  public $_displayName;
  public $translation = 'Ccd_CD[]';
  public $originalText;
  //
  public function isIcd() {
    return $this->_codeSystemName == 'ICD9' || $this->_codeSystemName == 'I9' || $this->_codeSystemName == 'ICD-9' || $this->_codeSystem == '2.16.840.1.113883.6.103' || $this->_codeSystem == '2.16.840.1.113883.6.42';
  }
  public function isSnomed() {
    return $this->_codeSystem == '2.16.840.1.113883.6.96';
  }
  public function getIcd() {
    if ($this->isIcd())
      return $this;
    return static::getIcdFrom($this->translation);
  }
  public function getSnomed() {
    if ($this->isSnomed())
      return $this;
  }
  //
  static function getIcdFrom($cds) {
    logit_r($cds, 'geticdfrom');
    if ($cds) 
      foreach ($cds as $cd)
        if ($cd->isIcd())
          return $cd;
  }
}
class Ccd_CE_Gender extends Ccd_CD {
  //
  public function asSex() {
    return substr($this->_code, 0, 1);
  }
}
class Ccd_Value extends Ccd_ANY {
  protected static function getInstanceFor($o) {
    switch (getr($o, '_xsi_type')) {
      case 'IVL_PQ':
        return new Ccd_IVL_PQ();
      case 'PQ':
        return new Ccd_PQ();
      default:
        return new Ccd_CD();
    }
  }
}
class Ccd_PQ extends Ccd_ANY {
  public $_value;
  public $_unit;
}
class Ccd_IVL_PQ extends Ccd_ANY {
  public $low = 'Ccd_PQ';
  public $high = 'Ccd_PQ';
}
class Ccd_SXCM_TS extends Ccd_ANY {
  protected static function getInstanceFor($o) {
    switch (getr($o, '_xsi_type')) {
      case 'IVL_TS':
        return new Ccd_IVL_TS();
      case 'PIVL_TS':
        return new Ccd_PIVL_TS();
      default:
        return new Ccd_TS();
    }
  }
}
class Ccd_TS extends Ccd_ANY {
  public $_value;
  //
  public function getSqlDate() {
    $v = $this->_value;
    if ($v) {
      if (strlen($v) == 6) {
        $v = substr($v, 0, 4) . '-' . substr($v, 4) . '-01 01:00:00'; /*approx M-Y*/
        return date('Y-m-d H:i:s', strtotime($v));
      }
      return date('Y-m-d', strtotime($v));
    }
  }
}
class Ccd_IVL_TS extends Ccd_ANY {
  public $low = 'Ccd_TS';
  public $high = 'Ccd_TS';
  public $center = 'Ccd_TS';
  //
  public function getSqlDate() {
    if ($this->low)
      return $this->low->getSqlDate();
    if ($this->center)
      return $this->center->getSqlDate();
    if ($this->high)
      return $this->high->getSqlDate();
  }
  public function getSqlDateHigh() {
    if (isset($this->high))
      return $this->high->getSqlDate();
  }
}
class Ccd_PIVL_TS extends Ccd_ANY {
  public $period = 'PQ';
  public $frequency;
}
class Ccd_AD extends Ccd_ANY {
  public $streetAddressLine = '[]'; 
  public $city; 
  public $state; 
  public $postalCode; 
  //
  public function getAddr1() {
    return $this->first('streetAddressLine');
  }
  public function getAddr2() {
    return geta(getr($this, 'streetAddressLine'), 1);
  }
}
class Ccd_TEL extends Ccd_ANY {
  public $_value;
  public $_use;
  //
  public function getValue() {
    $v = $this->_value;
    if (substr($v, 0, 4) == 'tel:')
      $v = substr($v, 4);
    return $v;
  }
}
class Ccd_PN extends Ccd_ANY {
  public $family; 
  public $given = '[]'; 
  public $prefix; 
  public $suffix; 
  //
  public function getLast() {
    return $this->family;
  }
  public function getFirst() {
    return $this->first('given');
  }
  public function getMiddle() {
    return geta(getr($this, 'given'), 1);
  }
}
class Ccd_Text extends XmlRec {
  //
  public function get($id) {
    if ($id)
      return static::rget($this, $id);
  }
  //
  protected static function rget($e, $id) {
    if ($e) {
      if (is_object($e) && get($e, '_ID') == $id) 
        return $e->_inner;
      foreach ($e as $i => $value) {
        if (! is_scalar($value)) {  
          $get = static::rget($value, $id);
          if ($get)
            return $get;
        }
      }
    }
  }  
}
class Ccd_TextRef extends XmlRec {
  public $reference;
  //
  public function getRef() {
    $ref = $this->reference->_value;
    if (substr($ref, 0, 1) == '#')
      $ref = substr($ref, 1);
    return $ref;
  }
}
