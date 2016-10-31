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
    return parent::saveDemo();
  }
  public function import(/*ContinuityCareDocument*/$ccd) {
    $ugid = $this->userGroupId;
    $cid = $this->clientId;
    Diagnosis_Ci_Ccd::saveAll($ugid, $cid, $ccd->getSection_Problems());
    Allergy_Ci_Ccd::saveAll($ugid, $cid, $ccd->getSection_Alerts());
    Med_Ci_Ccd::saveAll($ugid, $cid, $ccd->getSection_Meds());
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
    $recs = array();
    if ($problems && $problems->has()) { 
      foreach ($problems->entry as $problem)
        $recs[] = static::from($ugid, $cid, $problem, $problems->text);
    }
    return $recs;
  }
  static function saveAll($ugid, $cid, /*Ccd_Section_Problems*/$problems) {
    $us = static::all($ugid, $cid, $problems);
    foreach ($us as $me) 
      $me->save();
  }
  static function from($ugid, $cid, /*Ccd_ProblemEntry*/$problem, /*Ccd_Text*/$text) {
    //logit_r($problem, 'problem');
    //logit_r($text, 'text');
    $date = $problem->getSqlDate();
    $cd = $problem->getIcdCode();
    logit_r($cd, 'geticdcode');
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
    $status = static::asStatus($problem->getStatus());
    $me = static::create($ugid, $cid, $icd, $date, $desc, $status, $snomed);
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
