<?php
require_once 'php/data/rec/sql/Clients.php';
require_once 'php/data/rec/sql/_SessionRec.php';
require_once 'php/data/rec/sql/_ProcRec.php';
require_once 'php/data/rec/sql/_ICardRec.php';
require_once 'php/data/rec/sql/_ApiIdXref.php';
//
class Client_Cb extends Client implements ReadOnly {
  /*
  public $_externalId;
  */
  //
  function saveExternalId($practiceId, $id) {
    if ($this->_externalId != $id) { 
      $this->_externalId = $id;
      ApiIdXref_Cerberus::save_asPatient($practiceId, $this->clientId, $id);
    }
  }
  static function fetch($practiceId, $cid) {
    $me = parent::fetchWithDemo($cid);
    $me->_externalId = ApiIdXref_Cerberus::lookupPatientId($practiceId, $me->clientId);
    return $me; 
  }
}
class Session_Cb extends SessionRec implements ReadOnly {
  //
  public $sessionId;
  public $userGroupId;
  public $clientId;
  public $dateService;
  public $title;
  public $closed;
  public /*Client_Sb*/$Client;
  public /*string[]*/ $_icds;
  public $_externalId;
  //
  public function saveExternalId($practiceId, $id) {
    if ($this->_externalId != $id) { 
      $this->_externalId = $id;
      ApiIdXref_Cerberus::save_asEncounter($practiceId, $this->sessionId, $id);
    }
  }
  public function getIcds() {
    if ($this->_icds == null)
      $this->_icds = Diagnoses_Cb::fetchIcds($this->userGroupId, $this->sessionId);
    return $this->_icds;
  }
  public function getJsonFilters() {
    return array(
      'dateService' => JsonFilter::informalDate());
  }
  //
  static function fetch($practiceId, $sid) {
    $me = parent::fetch($sid);
    $me->Client = Client_Cb::fetch($practiceId, $me->clientId);
    if ($me->Client->_externalId == null)
      throw new CerberusPatientNotFound($me->clientId); 
    $me->_externalId = ApiIdXref_Cerberus::lookupEncounterId($practiceId, $me->sessionId);
    return $me;
  }
  static function asCriteria($cid) {
    $c = new static();
    $c->clientId = $cid;
    return $c;
  }
}
class Diagnoses_Cb extends SqlRec implements ReadOnly {
  //
  public $dataDiagnosesId;
  public $userGroupId;
  public $sessionId;
  public $icd;
  //
  public function getSqlTable() {
    return 'data_diagnoses';
  }
  //
  static function /*string[]*/fetchIcds($ugid, $sid) {  
    $us = static::fetchAll($ugid, $sid);
    $icds = array();
    foreach ($us as $rec)
      $icds[$rec->icd10] = $rec;
    return array_keys($icds);
  }
  static function fetchAll($ugid, $sid) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->sessionId = $sid;
    $c->icd10 = CriteriaValue::isNotNull();
    $us = static::fetchAllBy($c);
    return $us;
  }
}
class ICard_Cb extends ICardRec {
  //
  public $clientId;
  public $seq;
  public $planName;
  public $subscriberName;
  public $nameOnCard;
  public $groupNo;
  public $subscriberNo;
  public $dateEffective;
  public $active;
  public $external;
  //
  static function refresh($cid, /*CR_Insurance[]*/$entries) {
    if ($cid == null)
      return;
    static::deleteAll($cid);
    if (! empty($entries)) {
      $icards = static::all($cid, $entries);
      static::saveAll($icards);
      return $icards;
    }
  }
  static function all($cid, $entries) {
    $us = array();
    $seq = 1;
    foreach ($entries as $entry)
      $us[] = static::from($entry, $cid, $seq++);
    return $us;
  }
  static function from($entry, $cid, $seq) {
    $me = new static();
    $me->clientId = $cid;
    $me->seq = $seq;
    $me->setExternal($entry);
    return $me;
  }
  static function deleteAll($cid) {
    if ($cid) {
      $sql = "DELETE FROM client_icards WHERE client_id=$cid";
      Dao::query($sql);
    }
  }
}
class Proc_Cb extends ProcRec {
  //
  public $procId;
  public $userGroupId;
  public $clientId;
  public $date;  
  public $ipc;
  public $priority;
  public $location;
  public $providerId;
  public $addrFacility;
  public $recipient;
  public $userId;
  public $comments;
  public /*Ipc_Cb*/$Ipc;
  //
  static function fetchCpts($cid, $date) {
    $cpts = array();
    $recs = static::fetchAll($cid, $date);
    foreach ($recs as $rec) 
      $cpts[] = $rec->Ipc->code;
    return $cpts;
  }
  static function fetchAll($cid, $date) {
    $c = new static();
    $c->clientId = $cid;
    $c->Hd_date = Hdata_ProcDate::create()->setCriteriaValue(CriteriaValue::equals($date))->asJoin();
    $c->Ipc = Ipc_Cb::asJoin();
    return static::fetchAllBy($c);
  }
}
class Ipc_Cb extends IpcRec {
  //
  public $ipc;
  public $userGroupId;
  public $name;
  public $cat;
  public $code;  
  public $codeSystem;
  //
  static function asJoin() {
    $c = new static();
    $c->code = CriteriaValue::isNotNull();
    $c->codeSystem = static::CS_CPT4;
    return CriteriaJoin::requires($c);
  }
}
class ApiIdXref_Cb extends ApiIdXref_Cerberus {
  //
  public $partner;
  public $practiceId; 
  public $type;
  public $externalId;
  public $internalId;
  //
  static function fetchEncounters($practiceId, $cid) {
    $c = static::asEncounter($practiceId);
    $c->Session = CriteriaJoin::requires(Session_Cb::asCriteria($cid), 'internalId');
    return static::fetchAllBy($c, new RecSort('-Session.dateService'));
  }
}