<?php
require_once 'php/data/rec/sql/_SqlRec.php';
//
abstract class ImmunRec extends SqlRec implements AutoEncrypt {
  //
  /*
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
  public $formVis;
  public $formVis2;
  public $formVis3;
  public $formVis4;
  public $status;
  public $refusalReason;
  public $priorReaction;
  public $orderBy;
  public $orderEnterBy;
  public $fundingSource;
  public $financialClass;
  public $dateCreated;
  */
  //
  public function getSqlTable() {
    return 'data_immuns';
  }
  public function getEncryptedFids() {
    return array('dateGiven','comment');
  }  
  public function wasGiven() {
    switch ($this->status) {
      case 'Refused':
      case 'Not Given Due to Prior Reaction':
        return false;
      default:
        return true;
    }
  }
}
