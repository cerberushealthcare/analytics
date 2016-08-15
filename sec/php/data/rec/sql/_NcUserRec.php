<?php
require_once 'php/data/rec/sql/_SqlRec.php';
/**
 * NewCrop User Base Class
 * @author Warren Hornsby
 */
abstract class NcUserRec extends SqlRec {
  //
  public $userId;
  public /*NCScript.UserType*/ $userType;
  public /*NCScript.RoleType*/ $roleType;
  public $partnerId;
  public $nameLast;
  public $nameFirst;
  public $nameMiddle;
  public $namePrefix;
  public $nameSuffix;
  public $freeformCred;
  //
  static $PREFIXES = array(
  	'Ms.', 'Mr.', 'Dr.', 'Sr.', 'Sra.');
  static $SUFFIXES = array(
  	'DDS', 'DO', 'Jr', 'LVN', 'MD', 'NP', 'PA', 'RN', 'Sr', 'I', 'II', 'III', 'PhD', 'PharmD', 'RPh', 'MA', 'OD', 'CNP', 'CNM', 'RPAC', 'FACC', 'FACP', 'LPN', 'Jr.', 'Sr.', 'Esq.', 'Esq', 'IV', 'DPM', 'PAC');
  //
  public function getSqlTable() {
    return 'nc_users';
  }
  public function canPrescribe() {
    switch ($this->roleType) {
      case 'doctor':
      case 'nurse':
        return true;
    }
  }
}





