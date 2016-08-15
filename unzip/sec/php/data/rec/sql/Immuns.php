<?php
p_i('Immuns');
require_once 'php/c/template-entry/TemplateEntry.php';
require_once 'php/data/rec/sql/_ImmunRec.php';
require_once 'php/data/rec/sql/_HdataRec.php';
//
/**
 * Immunizations 
 * DAO for DataImmun
 * @author Warren Hornsby
 */
class Immuns {
  //
  /**
   * @param int $cid
   * @return array(Immun,..)
   */
  static function getActive($cid) {
    return Immun::fetchAll($cid);
  }
  /**
   * @param stdClass $o JSON object
   * @return Immun
   */
  static function save($o) {
    global $login;
    $rec = new Immun($o);
    $rec->userGroupId = $login->userGroupId;
    $rec->save();
    return $rec;
  }
  /**
   * @param int $id
   * @return int client ID
   */
  static function delete($id) {
    $rec = Immun::fetch($id);
    if ($rec) {
      $cid = $rec->clientId;
      Immun::delete($rec);
      return $cid;
    }
  }
  /**
   * @return array('HPV|Gardasil'=>LastLot,..)
   */
  static function getLastLots() {
    global $login;
    $map = LastLot::fetchMap($login->userGroupId);
    return $map;
  }
  /**
   * @return int PID of immunization template
   */
  static function getPid() {
    $ref = 'immCert.+immunRecord';
    $tid = 12;
    return JsonDao::toPid($ref, $tid);
  }
  /**
   * @param int $pid
   */
  static function getParAndLots($pid) {
    $par = TemplateEntry::getPar($pid);
    $lots = static::getLastLots();
    $o = new Rec();
    $o->par = $par;
    $o->lots = $lots;
    return $o;
  }
  /**
   * @return Immun_HL7Codes
   */
  static function getHL7Codes() {
    return Immun_HL7Codes::fetch(Immuns::getPid());
  }
}
//
/**
 * Immunization
 */
class Immun extends ImmunRec {
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
  //
  static $FRIENDLY_NAMES = array(
    'name' => 'Immunization');
  //
  public function toJsonObject(&$o) {
    $o->_dateOnly = formatApproxDate($this->dateGiven);
    if ($this->dateUpdated > $this->dateCreated)
      $o->_updated = true;
  }
  public function getJsonFilters() {
    return array(
      'dateGiven' => JsonFilter::editableDateTime(),
      'dateExp' => JsonFilter::editableDate(),
      'dateUpdated' => JsonFilter::informalDate(),
      'dateVis' => JsonFilter::editableDate(),
      'dateVis2' => JsonFilter::editableDate(),
      'dateVis3' => JsonFilter::editableDate());
  }
  public function save() {
    if ($this->dataImmunId == null) {
      $now = nowNoQuotes();
      $this->dateUpdated = $now;
      $this->dateCreated = $now;
    }
    parent::save();
    Hdata_ImmunDate::from($this)->save();
  }
  public function getAuditRecName() {
    return 'Immun';
  }
  public function getAuditLabel() {
    return $this->name;
  }
  public function validate(&$v) {
    $v->requires('dateGiven', 'name');
  }
  public function isAdministered() {
    return $this->status == 'Administered';
  }
  public function isImmune() {
    return $this->status == 'Not Given Due to Presumed Immunity';
  }
  public function isHistorical() {
    return substr($this->adminBy, 0, 22) == 'Historical information';
  }
  public function isRefused() {
    return ! empty($this->refusalReason);
  }
  public function hasFinClass() {
    return ! empty($this->financialClass);
  }
  public function getDateVis($i) {
    switch ($i) {
      case 0:
        return $this->dateVis; 
      case 1:
        return $this->dateVis2; 
      case 2:
        return $this->dateVis3; 
      case 3:
        return $this->dateVis4; 
    }
  }
  //
  static function fetchAll($cid) {
    $c = new static();
    $c->clientId = $cid;
    $c->sessionId = CriteriaValue::isNull();
    return SqlRec::fetchAllBy($c, new RecSort('-dateGiven', 'name'));
  }
}
class LastLot extends Rec {
  //
  public $fullname; 
  public $lot;
  public $dateExp;
  //
  public function getJsonFilters() {
    return array(
      'dateExp' => JsonFilter::editableDate());
  }
  static function fetchMap($ugid) {
    $sql = <<<eos
SELECT full_name, lot, date_exp from (
SELECT CONCAT_WS('|', name, trade_name) AS full_name, date_given, lot, date_exp FROM data_immuns
WHERE user_group_id=$ugid
ORDER BY full_name, date_given desc) a
GROUP BY full_name;
eos;
    $recs = array();
    $rows = Dao::fetchRows($sql);
    foreach ($rows as $row) { 
      $rec = static::from($row);
      $key = $rec->getMapKey();
      if ($key)
        $recs[$key] = $rec;
    }
    return $recs;
  }
  static function from($row) {
    $me = new static();
    $me->fullname = $row['full_name'];
    $me->lot = $row['lot'];
    $me->dateExp = $row['date_exp'];
    return $me;
  }
  //
  protected function getMapKey() {
    $key = $this->fullname;
    if (empty($key))
      return;
    if (strpos($key, '|') === false) 
      $key .= '|';
    return $key;
  }
}
/**
 * Immun_HL7Codes
 */
class Immun_HL7Codes extends Rec {
  //
  public $CVX;  // Vaccine code by trade name; {'Comvax (HepB/Hib)':'51','HIB (Brand Unknown)':'17',..} 
  public $CVXI;  // Vaccine code by immunization; {'DTaP':'20',..} 
  public $MVX;  // Manufacturer code; {'Akorn, Inc.':'AKR',..}
  public $CVX2VG;  // CVX to vaccine group; {'51':['17','45'],'17':['17'],..}  
  public $VG;  // Vaccine group; {'107':'DTap',..}
  public $site;  // Site code; {'Right Arm':'RA',..} 
  public $route;  // Route code; {'Nasal':'NS',..} 
  public $source;  // Source code; {'Historical unspecified':'01',..}
  public $refusal;  // Refusal reason; {'Parental decision':'03',..}
  public $finclass;  // Financial class; {'Not VFC Eligible':'V01',..} 
  //
  static function fetch($pid) {
    $me = new static();
    $me->CVX = static::build(Question_Immun::fetchTradeNames($pid)); 
    $me->CVXI = static::build(Question_Immun::fetchNames($pid)); 
    $me->MVX = static::build(Question_Immun::fetchManufacs($pid));
    $me->CVX2VG = static::buildGroupMap();
    $me->VG = static::buildGroups();
    $me->site = static::build(Question_Immun::fetchSites($pid));
    $me->route = static::build(Question_Immun::fetchRoutes($pid));
    $me->source = static::build(Question_Immun::fetchSources($pid));
    $me->refusal = static::build(Question_Immun::fetchRefusals($pid));
    $me->finclass = static::build(Question_Immun::fetchFinancialClasses($pid));
    return $me; 
  }
  public function /*[CVX,..]*/getCvxGroupsFor($cvx) {
    $groups = geta($this->CVX2VG, $cvx);
    if ($groups == null)
      $groups = array($cvx);
    return $groups;
  }
  public function getVacGroupDesc($gcvx) {
    return geta($this->VG, $gcvx);
  }
  //
  private static function build($qs) {
    $codes = array();
    foreach ($qs as $q) 
      static::append($codes, $q);
    return $codes;
  }
  private static function append(&$codes, $q) {
    if (isset($q->Options))
      foreach ($q->Options as $o) 
        if ($o->cptCode) 
          $codes[$o->getText()] = $o->cptCode;
  }
  private static function buildGroups() {
    return array(
      '107' => 'DTAP',
      '108' => 'MENING',
      '122' => 'ROTAVIRUS',
      '128' => 'H1N1 flu',
      '139' => 'Td',
      '14' => 'IG',
      '152' => 'PneumoPCV',
      '164' => 'MeningB',
      '17' => 'HIB',
      '26' => 'cholera',
      '45' => 'HepB',
      '82' => 'ADENO',
      '85' => 'HepA',
      '88' => 'FLU',
      '89' => 'POLIO',
      '90' => 'RABIES',
      '91' => 'TYPHOID',
      '92' => 'VEE'        
    );
  }
  private static function buildGroupMap() { /*see http://www2a.cdc.gov/vaccines/iis/iisstandards/vaccines.asp?rpt=vg*/
    return array(
      '01' => array('107'),
      '02' => array('89'),
      '08' => array('45'),
      '09' => array('139'),
      '10' => array('89'),
      '100' => array('152'),
      '101' => array('91'),
      '102' => array('107', '45', '17'),
      '103' => array('108'),
      '104' => array('85', '45'),
      '106' => array('107'),
      '107' => array('107'),
      '108' => array('108'),
      '110' => array('107', '45', '89'),
      '111' => array('88'),
      '113' => array('139'),
      '114' => array('108'),
      '115' => array('139'),
      '116' => array('122'),
      '119' => array('122'),
      '120' => array('107', '17', '89'),
      '122' => array('122'),
      '125' => array('128'),
      '126' => array('128'),
      '127' => array('128'),
      '128' => array('128'),
      '130' => array('107', '89'),
      '132' => array('107', '45', '17', '89'),
      '133' => array('152'),
      '135' => array('88'),
      '136' => array('108'),
      '138' => array('139'),
      '139' => array('139'),
      '14' => array('14'),
      '140' => array('88'),
      '141' => array('88'),
      '143' => array('82'),
      '144' => array('88'),
      '146' => array('107', '45', '17', '89'),
      '147' => array('108'),
      '148' => array('17', '108'),
      '149' => array('88'),
      '15' => array('88'),
      '150' => array('88'),
      '151' => array('88'),
      '152' => array('152'),
      '153' => array('88'),
      '155' => array('88'),
      '158' => array('88'),
      '16' => array('88'),
      '161' => array('88'),
      '162' => array('164'),
      '163' => array('164'),
      '164' => array('164'),
      '17' => array('17'),
      '18' => array('90'),
      '20' => array('107'),
      '22' => array('107', '17'),
      '25' => array('91'),
      '26' => array('26'),
      '28' => array('107'),
      '29' => array('14'),
      '31' => array('85'),
      '32' => array('108'),
      '34' => array('14'),
      '40' => array('90'),
      '41' => array('91'),
      '42' => array('45'),
      '43' => array('45'),
      '44' => array('45'),
      '45' => array('45'),
      '46' => array('17'),
      '47' => array('17'),
      '48' => array('17'),
      '49' => array('17'),
      '50' => array('107', '17'),
      '51' => array('45', '17'),
      '52' => array('85'),
      '53' => array('91'),
      '54' => array('82'),
      '55' => array('82'),
      '71' => array('14'),
      '74' => array('122'),
      '80' => array('92'),
      '81' => array('92'),
      '82' => array('82'),
      '83' => array('85'),
      '84' => array('85'),
      '85' => array('85'),
      '86' => array('14'),
      '87' => array('14'),
      '88' => array('88'),
      '89' => array('89'),
      '90' => array('90'),
      '91' => array('91'),
      '92' => array('92')
    );
  } 
}
class Question_Immun extends QuestionRec {
  //
  public $questionId;
  public $parId;
  public $dsyncId;
  public /*Option_Immun[]*/ $Options;
  //
  static function fetchTradeNames($pid) {
    return static::fetchAll($pid, 'imm.tradeName');
  }
  static function fetchNames($pid) {
    return static::fetchAll($pid, 'imm.name');
  }
  static function fetchManufacs($pid) {
    return static::fetchAll($pid, 'imm.manufac');
  }
  static function fetchSites($pid) {
    return static::fetchAll($pid, 'imm.site');
  }
  static function fetchRoutes($pid) {
    return static::fetchAll($pid, 'imm.route');
  }
  static function fetchSources($pid) {
    return static::fetchAll($pid, 'imm.adminBy');
  }
  static function fetchRefusals($pid) {
    return static::fetchAll($pid, 'imm.refusalReason');
  }
  static function fetchFinancialClasses($pid) {
    return static::fetchAll($pid, 'imm.financialClass');
  }
  protected static function fetchAll($pid, $dsync) {
    $c = self::asCriteria($pid, $dsync);
    return self::fetchAllBy($c);
  }
  protected static function asCriteria($pid, $dsync) {
    $c = new static();
    $c->parId = $pid;
    $c->dsyncId = $dsync;
    $c->Options = CriteriaJoin::optionalAsArray(new Option_Immun());
    return $c;
  }
} 
class Option_Immun extends OptionRec {
  //
  public $optionId;
  public $questionId;
  public $uid;
  public $text;
  public $cptCode;
  //
}
//
require_once 'php/dao/JsonDao.php';
