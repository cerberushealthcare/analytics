<?php
require_once 'php/dao/JsonDao.php';
require_once 'php/data/rec/sql/_SqlRec.php';
//
/**
 * Immunizations 
 * DAO for DataImmun
 * @author Warren Hornsby
 *
 */
class DataImmuns {
  //
  /**
   * @param int $cid
   * @return array(DataImmun,..)
   */
  public static function getActive($cid) {
    $c = new DataImmun();
    $c->clientId = $cid;
    $c->sessionId = CriteriaValue::isNull();
    return DataImmun::fetchAllBy($c, new RecSort('-dateGiven', 'name'));
  }
  /**
   * @param stdClass $o JSON object
   * @return DataImmun
   */
  public static function save($o) {
    $rec = new DataImmun($o);
    $rec->save();
    return $rec;
  }
  /**
   * @return int PID of immunization template
   */
  public static function getPid() {
    $ref = 'immCert.+immunRecord';
    $tid = 12;
    return JsonDao::toPid($ref, $tid);
  }
}
//
/**
 * Immunization
 */
class DataImmun extends SqlRec {
  //
  public $dataImmunId;
  public $userGroupId;
  public $clientId;
  public $sessionId;
  public $dateGiven;
  public $name;
  public $tradeName;
  public $manufac;
  public $lot;
  public $dateExp;
  public $dateVis;
  public $dateVis2;
  public $dateVis3;
  public $dateVis4;
  public $dose;
  public $route;
  public $site;
  public $adminBy;
  public $comment;
  public $dateUpdated;
  //
  public function getSqlTable() {
    return 'data_immuns';
  }
  public function getJsonFilters() {
    return array(
      'dateGiven' => JsonFilter::editableDateApprox(),
      'dateExp' => JsonFilter::editableDate(),
      'dateUpdated' => JsonFilter::informalDate(),
      'dateVis' => JsonFilter::editableDate(),
      'dateVis2' => JsonFilter::editableDate(),
      'dateVis3' => JsonFilter::editableDate());
  }
  /**
   * @param int $id
   * @return DataImmun
   */
  public static function fetch($id) {
    return parent::fetch($id, 'DataImmun');
  }
}
