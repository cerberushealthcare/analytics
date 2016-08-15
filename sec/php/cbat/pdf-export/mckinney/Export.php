<?php
require_once 'Export_Input.php';
require_once 'Export_Output.php';
//
class Export {
  //
  static function exec() {
    $input = PatientCsv::fetch();
    $cids = $input->getCids();
    $fallouts = Client_Import::extractFallouts($clients);
    $insurance->addICardsTo($clients);
    OutputSql::create($clients)->save();
    InsuranceOnlySql::create($clients)->save();
    OutputFalloutsCsv::create($fallouts)->save();
  }
}
//
require_once 'php/data/rec/sql/_ClientRec.php';
//
class Client_Exp extends ClientRec {
  //
  public $clientId;
  public $userGroupId;
  public $uid;
  public $lastName;
  public $firstName;
  public $middleName;
  public $sex;
  public $birth;
  public $primaryPhys;
  public $deceased;
  public $active;
  public $inactiveCode;
  //
  
}