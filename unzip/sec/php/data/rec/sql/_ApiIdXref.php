<?php
require_once 'php/data/rec/sql/_SqlRec.php';
//
abstract class ApiIdXref extends SqlRec implements CompositePk {
  //
  public $partner;
  public $practiceId; 
  public $type;
  public $externalId;
  public $internalId;
  //
  const TYPE_PRACTICE = 1;
  const TYPE_USER = 2/*not used currently*/;
  const TYPE_PATIENT = 3/*extenders may define types 10,11,..*/;
  //
  static $PARTNER = 0/*extenders must supply*/;
  //
  public function getSqlTable() {
    return 'api_id_xref';
  }
  public function getPkFieldCount() {
    return 4;
  }
  //
  static function fetchExternalId($practiceId, $type, $internalId) {
    $c = new static(static::$PARTNER, $practiceId, $type, null, $internalId);
    $me = static::fetchOneBy($c); 
    if ($me)
      return $me->externalId;
  }
  static function fetchInternalId($practiceId, $type, $externalId) {
    $c = new static(static::$PARTNER, $practiceId, $type, $externalId);
    $me = static::fetchOneBy($c);
    if ($me)
      return $me->internalId;
  }
  static function lookupPracticeId($ugid) {
    return static::fetchExternalId(null, static::TYPE_PRACTICE, $ugid);
  } 
  static function lookupPatientId($practiceId, $cid) {
    return static::fetchExternalId($practiceId, static::TYPE_PATIENT, $cid);
  }
  static function lookupClientId($practiceId, $patientId) {
    return static::fetchInternalId($practiceId, static::TYPE_PATIENT, $patientId);
  }
  static function save_asPatient($practiceId, $cid, $externalId) {
    $me = new static(static::$PARTNER, $practiceId, static::TYPE_PATIENT, $externalId, $cid);
    return $me->save();
  }
  /** User functions currently not in use */
  static function lookupUserId($practiceId, $userId) {
    return static::fetchExternalId($practiceId, static::TYPE_USER, $userId);
  }
  static function save_asUser($practiceId, $userId, $externalId) {
    $me = static::asUser($praticeId, $userId, $externalId);
    return $me->save();
  }
  static function asUser($practiceId, $userId = null, $externalId = null) {
    return new static(static::$PARTNER, $practiceId, static::TYPE_USER, $externalId, $userId);
  }
}
class ApiIdXref_Cerberus extends ApiIdXref {
  //
  static $PARTNER = 5001;
  //
  const TYPE_ENCOUNTER = 100;
  //
  static function lookupEncounterId($practiceId, $sid) {
    return static::fetchExternalId($practiceId, static::TYPE_ENCOUNTER, $sid);
  }
  static function save_asEncounter($practiceId, $sid, $externalId) {
    $me = static::asEncounter($practiceId, $sid, $externalId);
    return $me->save();
  }
  static function asEncounter($practiceId, $sid = null, $externalId = null) {
    return new static(static::$PARTNER, $practiceId, static::TYPE_ENCOUNTER, $externalId, $sid);
  }
}
