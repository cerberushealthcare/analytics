<?php
//
class Client_Ci_Ccr extends Client_Ci {
  //
  static function create($ugid) {
    return parent::create($ugid, Address_Ci_Ccr::create());
  }
  static function fetch($cid) {
    return parent::fetch($cid, Address_Ci_Ccr::create($cid));
  }
  //
  public function getDiags($ccr) {
    return Diagnosis_Ci_Ccr::all($this->userGroupId, $this->clientId, $ccr->Body->Problems);
  }
  public function getMeds($ccr) {
    return Med_Ci_Ccr::all($this->userGroupId, $this->clientId, $ccr->Body->Medications);
  }
  public function getAllergies($ccr) {
    return Allergy_Ci_Ccr::all($this->userGroupId, $this->clientId, $ccr->Body->Alerts);
  }
  public function saveDemo(/*ContinuityCareRecord*/$ccr) {
    $actor = $ccr->getPatient();
    $this->setFrom($actor);
    $this->Address->setFrom($actor);
    return parent::saveDemo();
  }
  public function import(/*ContinuityCareRecord*/$ccr) {
    $ugid = $this->userGroupId;
    $cid = $this->clientId;
    $body = $ccr->Body;
    Diagnosis_Ci_Ccr::saveAll($ugid, $cid, $body->Problems);
    Allergy_Ci_Ccr::saveAll($ugid, $cid, $body->Alerts);
    Med_Ci_Ccr::saveAll($ugid, $cid, $body->Medications);
    return;  // do not need to do these for mu2 
    Immun_Ci_Ccr::saveAll($ugid, $cid, $body->Immunizations);
    Proc_Ci_Ccr::saveAll($ugid, $cid, $body->Procedures);
    Proc_Ci_Results_Ccr::saveAll($ugid, $cid, $body->Results);
  }
  //
  protected function setFrom(/*Ccr_Actor*/$actor) {
    $person = $actor->Person;
    $name = $person->Name->CurrentName ?: $person->Name->BirthName;
    $this->lastName = $name->getLast();
    logit_r($this->lastName);
    $this->firstName = $name->getFirst();
    $this->middleName = $name->getMiddle();
    $this->sex = $person->Gender->asSex();
    $this->birth = $person->DateOfBirth->getSqlDate();
  }
}
class Address_Ci_Ccr extends Address_Ci {
  //
  public function setFrom(/*Ccr_Actor*/$actor) {
    $addr = $actor->getPrimaryAddr();
    $this->addr1 = $addr->Line1;
    $this->addr2 = $addr->Line2;
    $this->city = $addr->City;
    $this->state = $addr->State;
    $this->zip = $addr->PostalCode;
    $this->phone1 = $actor->getPrimaryPhone();
    $this->phone1Type = static::PHONE_TYPE_PRIMARY;
    $this->email1 = $actor->getPrimaryEmail();
  }
}
class Diagnosis_Ci_Ccr extends Diagnosis_Ci {
  //
  static function all($ugid, $cid, /*Ccr_Body_Problems*/$problems) {
    $recs = array();
    if ($problems && $problems->has()) { 
      foreach ($problems->Problem as $problem) {
        $recs[] = static::from($ugid, $cid, $problem);
      }
    }
    return $recs;
  }
  static function saveAll($ugid, $cid, /*Ccr_Body_Problems*/$problems) {
    $us = static::all($ugid, $cid, $problems);
    foreach ($us as $me) {
      $me->save();
    }
  }
  static function from($ugid, $cid, /*Ccr_ProblemType*/$problem) {
    $icd = $problem->getIcd();
    $snomed = $problem->getSnomed();
    $date = $problem->getSqlDate();
    $text = static::asText($problem->getDesc(), $icd);
    $status = static::asStatus($problem->getStatus());
    $active = true;
    return static::create($ugid, $cid, $icd, $date, $text, $status, $snomed);
  }
  protected static function asStatus($s) {
    switch (strtoupper($s)) {
      // TODO
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
class Allergy_Ci_Ccr extends Allergy_Ci {
  //
  static function all($ugid, $cid, /*Ccr_Body_Alerts*/$alerts) {
    $recs = array();
    if ($alerts && $alerts->has()) { 
      foreach ($alerts->Alert as $alert) 
        $recs[] = static::from($ugid, $cid, $alert);
    }
    return $recs;
  }
  static function saveAll($ugid, $cid, /*Ccr_Body_Alerts*/$alerts) {
    $us = static::all($ugid, $cid, $alerts);
    foreach ($us as $me) {
      $me->save();
    }
  }
  static function from($ugid, $cid, /*Ccr_AlertType*/$alert) {
    $agent = static::asAgent($alert);
    $reactions = static::asReactions($alert);
    $active = static::asActive($alert->getStatus());
    $me = static::create($ugid, $cid, $agent, $reactions, $active);
    return $me;
  }
  protected static function asAgent($alert) {
    $prod = $alert->getSingleProduct();
    if ($prod)
      return $prod->getProductName();
    $env = $alert->getSingleEnvAgent();
    if ($env)
      return $env->getDesc();
  }
  protected static function asReactions($alert) {
    $texts = $alert->getReactionTexts();
    return implode('; ', $texts);
  }
  protected static function asActive($s) {
    switch (strtoupper($s)) {
      // TODO
      default:
        return true;
    } 
  }
}
class Med_Ci_Ccr extends Med_Ci {
  //
  static function all($ugid, $cid, /*Ccr_Body_Meds*/$meds) {
    $recs = array();
    if ($meds && $meds->has()) { 
      foreach ($meds->Medication as $med) 
        $recs[] = static::from($ugid, $cid, $med);
    }
    return $recs;
  }
  static function saveAll($ugid, $cid, /*Ccr_Body_Meds*/$meds) {
    $us = static::all($ugid, $cid, $meds);
    foreach ($us as $me) {
      $me->save();
    }
  }
  static function from($ugid, $cid, /*Ccr_StructProdType*/$med) {
    $date = $med->getSqlDate();
    $name = $med->getProductName();
    $text = $med->getDirectionsDesc();
    $active = static::asActive($med->getStatus());
    return static::create($ugid, $cid, $date, $name, $text, $active);
  }
  protected static function asActive($s) {
    switch (strtoupper($s)) {
      // TODO
      default:
        return true;
    } 
  }
}
class Immun_Ci_Ccr extends Immun_Ci {
  //
  static function saveAll($ugid, $cid, /*Ccr_Body_Immuns*/$imms) {
    if ($imms && $imms->has()) { 
      foreach ($imms->Immunization as $imm) 
        static::from($ugid, $cid, $imm)->save();
    }
  }
  static function from($ugid, $cid, /*Ccr_StructProdType*/$imm) {
    $dateGiven = $imm->getSqlDateTime();
    $name = $imm->getProductName();
    return static::create($ugid, $cid, $dateGiven, $name);
  }
}
class Proc_Ci_Ccr extends Proc_Ci {
  //
  static function saveAll($ugid, $cid, /*Ccr_Body_Procedures*/$procs) {
    if ($procs && $procs->has()) { 
      foreach ($procs->Procedure as $proc) 
        static::from($ugid, $cid, $proc)->save();
    }
  }
  static function from($ugid, $cid, /*CCr_ProcedureType*/$proc) {
    $ipc = Ipc_Ci_Ccr::getIpc($ugid, $proc);
    $date = $proc->getSqlDateTime();
    return static::create($ugid, $cid, $date, $ipc);
  }
}
class Proc_Ci_Results_Ccr extends Proc_Ci_Results {
  //
  static function saveAll($ugid, $cid, /*Ccr_Body_Results*/$results) {
    if ($results && $results->has()) {
      foreach ($results->Result as $result)
        static::from($ugid, $cid, $result)->save();
    }
  }
  static function from($ugid, $cid, /*CCr_ResultType*/$result) {
    $results = Result_Ci_Ccr::all($ugid, $cid, $result);
    $ipc = Ipc_Ci_Ccr::getIpc_asResult($ugid, $result);
    $date = $result->getSqlDateTime();
    return static::create($ugid, $cid, $date, $ipc, $results);
  }
}
class Result_Ci_Ccr extends Result_Ci {
  //
  static function all($ugid, $cid, /*CCr_ResultType*/$result) {
    $us = array();
    foreach ($result->Test as $test)
      $us[] = static::from($ugid, $cid, $test);
    return $us;
  }
  static function from($ugid, $cid, /*Ccr_TestType*/$test) {
    $ipc = Ipc_Ci_Ccr::getIpc_asResult($ugid, $test);
    $value = $test->getValue();
    $unit = $test->getUnit(); 
    $comment = $test->getResultDesc();
    $interpret = static::asInterpret($test->getFlag());
    return static::create($cid, $ipc, $value, $unit, $comment, $interpret);
  }
  protected static function asInterpret($s) {
    if ($s) {
      $code = geta(static::$INTERPRET_MAP, strtoupper($s));
      if ($code)
        return $code;
      if (strpos($code, 'ABNORMAL') !== false)
        return static::IC_ABNORMAL;
    }
  }
}
class Ipc_Ci_Ccr extends Ipc_Ci {
  //
  static function getIpc($ugid, /*Ccr_ProcedureType|Ccr_ResultType*/$pr, $defaultCat = self::CAT_PROC) {
    $name = $pr->getDesc();
    $cat = static::asCat($pr->getType(), $defaultCat);
    $me = static::fetchOrCreate($ugid, $name, $cat);
    return $me->ipc;
  }
  static function getIpc_asResult($ugid, $pr) {
    return static::getIpc($ugid, $pr, static::CAT_TEST);
  }
  protected static function asCat($s, $default) {
    switch (strtoupper($s)) {
      case 'SURGERY':
        return static::CAT_SURG;
      default:
        return $default;
    }
  }
}