<?php
require_once 'CMS002v4.php';
require_once 'CMS050v3.php';
require_once 'CMS068v4.php';
require_once 'CMS069v3.php';
require_once 'CMS090v4.php';
require_once 'CMS138v3.php';
require_once 'CMS156v3.php';
require_once 'CMS165v3.php';
require_once 'CMS166v4.php';
require_once 'php/data/xml/ccd/qrda/ClinicalDocument_Qrda.php';
//
/**
 * CQM Reports (MU Stage 2)
 * @author Warren Hornsby
 */
class CqmReports {
  //
  static function /*CqmReport*/get($id, $from, $to, $userId) {
    return static::$id($from, $to, $userId);
  }
  static function /*CqmReportSet_204*/getSet($userId, $from = null, $to = null) {
    return CqmReportSet_2014::create($userId, $from, $to);
  }
  static function /*filename*/saveCat3($cqmset) {
    $cd3 = static::makeCat3($cqmset);
    $filename = static::save($cd3);
    return $filename;
  }
  static function /*filename*/zipCat1s($cqm) {
    $filenames = static::saveCat1s($cqm);
    $filename = static::zip($filenames, $cqm);
    return $filename;
  }
  static function /*filename[]*/saveCat1s($cqm) {
    $cd1s = static::makeCat1s($cqm);
    $filenames = array();
    foreach ($cd1s as $cd1) {
      $filenames[] = static::save($cd1);
    }
    return $filenames;
  }
  static function /*ClinicalDocument_Qrda*/makeCat3($cqmset) {
    $cd = ClinicalDocument_Qrda::asCategory3($cqmset);
    return $cd;
  }
  static function /*ClinicalDocument_Qrda[]*/makeCat1s($cqm) {
    $cds = array();
    foreach ($cqm->forQrdaCat1() as $client) {
      $cds[] = ClinicalDocument_Qrda::asCategory1($cqm, $client);
    }
    return $cds;
  }
  static function /*filename*/save($cd) {
    $folder = GroupFolder_Cqm::open();
    $file = $folder->save($cd);
    return $file->filename;
  }
  static function /*filename*/zip($filenames, $cqm) {
    $folder = GroupFolder_Cqm::open();
    $filename = $folder->zip($filenames, $cqm);
    return $filename;
  }
  //
  /** 002: Preventive Care and Screening: Screening for Clinical Depression and Follow-Up Plan */
  static function CMS002($from, $to, $userId) {
    return CMS002v4::create($from, $to, $userId);
  }
  /** 050: Closing the Referral Loop: Receipt of Specialist Report */
  static function CMS050($from, $to, $userId) {
    return CMS050v3::create($from, $to, $userId);
  }
  /** 068: Documentation of Current Medications in the Medical Record */
  static function CMS068($from, $to, $userId) {
    return CMS068v4::create($from, $to, $userId);
  }
  /** 069: Preventive Care and Screening: Body Mass Index (BMI) Screening and Follow-Up Plan */
  static function CMS069($from, $to, $userId) {
    return CMS069v3::create($from, $to, $userId);
  }
  /** 090: Functional Status Assessment for Complex Chronic Conditions */
  static function CMS090($from, $to, $userId) {
    return CMS090v4::create($from, $to, $userId);
  }
  /** 138: Preventive Care and Screening: Tobacco Use: Screening and Cessation Intervention */
  static function CMS138($from, $to, $userId) {
    return CMS138v3::create($from, $to, $userId);
  }
  /** 156: Use of High-Risk Medications in the Elderly */
  static function CMS156($from, $to, $userId) {
    return CMS156v3::create($from, $to, $userId);
  }
  /** 165: Controlling High Blood Pressure */
  static function CMS165($from, $to, $userId) {
    return CMS165v3::create($from, $to, $userId);
  }
  /** 166: Use of Imaging Studies for Low Back Pain */
  static function CMS166($from, $to, $userId) {
    return CMS166v4::create($from, $to, $userId);
  }
}
abstract class CqmReport {
  //
  static $CQM/*CMS number*/;
  static $ID/*version specific OID*/;
  static $SETID/*version neutral OID*/;
  static $NQF/*number or null (N/A)*/;
  static $VERSION/*number*/;
  static $TITLE;
  static $POPCLASSES/*class names of $Pops*/;
  //
  public $from/*reporting period start date*/;
  public $to/*reporting period end date*/;
  public $id;
  public $setid;
  public $nqf;
  public $version;
  public $title;
  //
  public /*CqmPop[]*/$Pops;
  public $ErxUser;
  public $UserGroup;
  //
  static function create($from, $to, $userId) {
    global $login;
    $me = new static();
    $me->from = $from;
    $me->to = $to;
    $me->title = static::$TITLE;
    $me->id = static::$ID;
    $me->setid = static::$SETID;
    $me->nqf = static::$NQF ?: 'N/A';
    $me->version = static::$VERSION;
    $me->ErxUser = ErxUsers::get($userId, $login->userGroupId);
    $me->UserGroup = UserGroups::getMineWithAddress();
    $me->fetchPops($me->ErxUser->userGroupId, $from, $to, $userId); 
    return $me;
  }
  //
  /*complete list of patients for reporting in QRDA cat1*/
  public function /*Client[]*/forQrdaCat1() {
    $clients = array();
    foreach ($this->Pops as $pop) {
      $clients = array_merge($clients, $pop->forQrdaCat1());
    }
    return $clients;
  }
  //
  protected function fetchPops($ugid, $from, $to, $userId) {
    $this->Pops = array();
    foreach (static::$POPCLASSES as $POPCLASS) {
      $this->Pops[] = $POPCLASS::create($ugid, $from, $to, $userId); 
    }
  } 
}
//
abstract class CqmPop {
  //
  static $IPP/*initial patient population OID*/;
  static $DENOM/*denominator OID*/;
  static $DENEX/*optional denominator exclusions OID*/;
  static $NUMER/*numerator OID*/;
  static $NUMER2/*optional 2nd numerator OID*/;
  static $DENEXCEP/*optional denominator exceptions OID*/;
  //
  public /*Client_Cqm[]*/$IppClients;
  public /*Client_Cqm[]*/$DenomClients;
  public /*Client_Cqm[]*/$ExcluClients;
  public /*Client_Cqm[]*/$NumerClients;
  public /*Client_Cqm[]*/$NumerClients2;
  public /*Client_Cqm[]*/$ExcepClients;    
  //
  static function create($ugid, $from, $to, $userId) {
    $me = new static();
    $me->IppClients = $me->getIpp($ugid, $from, $to, $userId);
    $me->DenomClients = $me->getDenom($ugid, $from, $to, $userId);
    $me->ExcluClients = $me->getExclu($ugid, $from, $to, $userId);
    $me->NumerClients = $me->getNumer($ugid, $from, $to, $userId);
    $me->NumerClients2 = $me->getNumer2($ugid, $from, $to, $userId);
    $me->ExcepClients = $me->getExcep($ugid, $from, $to, $userId);
    if (! empty($me->ExcluClients)) 
      $me->NumerClients = array_diff_key($me->NumerClients, $me->ExcluClients);
    if (! empty($me->ExcepClients)) 
      $me->NumerClients = array_diff_key($me->NumerClients, $me->ExcepClients);
    return $me; 
  }
  //
  /*required overrides*/
  abstract protected function /*Client_Cqm[]*/getIpp($ugid, $from, $to, $userId);
  abstract protected function /*Client_Cqm[]*/getNumer($ugid, $from, $to, $userId);
  //
  /*optional overrides*/
  protected function /*Client_Cqm[]*/getDenom($ugid, $from, $to, $userId) {
    return $this->IppClients; /*usually defaulted to initial pop*/
  }
  protected function /*Client_Cqm[]*/getExcep($ugid, $from, $to, $userId) {
    return null;
  }
  protected function /*Client_Cqm[]*/getNumer2($ugid, $from, $to, $userId) {
    return null;
  }
  protected function /*Client_Cqm[]*/getExclu($ugid, $from, $to, $userId) {
    return null;
  }
  //
  /*complete list of patients for reporting in QRDA cat1, using most descriptive class for each*/
  public function forQrdaCat1() {
    $clients = static::dedupe($this->DenomClients);
    if (! empty($this->ExcluClients))
      $clients = array_replace($clients, static::dedupe($this->ExcluClients));
    if (! empty($this->NumerClients))
      $clients = array_replace($clients, static::dedupe($this->NumerClients));
    if (! empty($this->NumerClients2))
      $clients = array_replace($clients, static::dedupe($this->NumerClients2));
    if (! empty($this->ExcepClients))
      $clients = array_replace($clients, static::dedupe($this->ExcepClients));
    return static::addAddresses(array_values($clients));
  }
  /*cat III scorecard*/
  public function scorecard_IPP() {
    return CqmScorecard::from($this->IppClients);
  }
  public function scorecard_DENOM() {
    return CqmScorecard::from($this->DenomClients);
  }
  public function scorecard_DENEX() {
    return CqmScorecard::from($this->ExcluClients);
  }
  public function scorecard_NUMER() {
    return CqmScorecard::from($this->NumerClients);
  }
  public function scorecard_NUMER2() {
    return CqmScorecard::from($this->NumerClients2);
  }
  public function scorecard_DENEXCEP() {
    return CqmScorecard::from($this->ExcepClients);
  }
  //
  static function dedupe($recs) {/*in case population is encounter-based*/
    $clients = array();
    foreach ($recs as $rec) {
      $clients[$rec->clientId] = $rec;
    }
    return $clients;
  }
  static function addAddresses($recs) {
    foreach ($recs as &$rec) {
      $rec->fetchAddress();
    }
    return $recs;
  }
}
/** Scorecard for cat III aggregate/supplemental data */
class CqmScorecard {
  //
  public /*int*/$aggregate;
  public /*array('code'=>count)*/$ethnicity;
  public /*array('code'=>count)*/$race;
  public /*array('code'=>count)*/$sex;
  public /*array('code'=>count)*/$payer;
  //
  static function from(/*$Client_Cqm[]*/$recs) {
    $me = new static();
    $me->aggregate = count($recs);
    $clients = CqmPop::dedupe($recs);
    foreach ($clients as $client) {
      $me->add('ethnicity', $client->ethnicity);
      $me->add('race', $client->race);
      $me->add('sex', $client->sex);
      $me->add('payer', "other");
    }
    return $me;
  }
  //
  protected function add($fid, $code) {
    $code = "$code";
    $array = $this->$fid ?: array();
    $count = geta($array, $code);
    if ($count == null) {
      $array[$code] = 1;
    } else {
      $array[$code] = $count + 1;
    }
    $this->$fid = $array;
  }
}
//
/** Complete set of CQM's for reporting year */
class CqmReportSet_2014 {
  //
  static $ID = null;
  static $TITLE = 'Clinical Quality Measure Set';
  static $FROM = '2014-01-01';
  static $TO = '2015-01-01';
  //
  //
  public $userId;
  public $from;
  public $to;
  public /*CqmReport[]*/$cqms;
  //
  static function create($userId, $from = null, $to = null) {
    $me = new static();
    $me->userId = $userId;
    $me->from = $from ?: static::$FROM;
    $me->to = $to ?: static::$TO;
    $me->load();
    return $me;
  }
  //
  public function getTitle() {
    return static::$TITLE . " " . substr($this->from, 0, 4) . "-" . substr($this->to, 0, 4);
  }
  public function getUserGroup() {
    return reset($this->cqms)->UserGroup;
  }
  public function getErxUser() {
    return reset($this->cqms)->ErxUser;
  }
  public function load() {
    $this->cqms = array(
      CMS002v4::create($this->from, $this->to, $this->userId),
      CMS050v3::create($this->from, $this->to, $this->userId),
      CMS068v4::create($this->from, $this->to, $this->userId),
      CMS069v3::create($this->from, $this->to, $this->userId),
      CMS090v4::create($this->from, $this->to, $this->userId),
      CMS138v3::create($this->from, $this->to, $this->userId),
      CMS156v3::create($this->from, $this->to, $this->userId),
      CMS165v3::create($this->from, $this->to, $this->userId),
      CMS166v4::create($this->from, $this->to, $this->userId));
  }
}
