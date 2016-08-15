<?php
require_once 'php/data/rec/sql/_SqlRec.php';
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
  public $dose;
  public $route;
  public $site;
  public $adminBy;
  public $comment;
  public $updated;
  //
  public function getSqlTable() {
    return 'data_immuns';
  }
  //
  public static function fetch($sid) {
    return SqlRec::fetch($sid, 'DataImmun');
  }
}
