<?php
require_once 'php/data/rec/sql/_ClientRec.php';
require_once 'php/data/rec/sql/_AuditMruRec.php';
//require_once 'php/data/rec/sql/_HdataRec.php';
//
class PStub_Mru extends PatientStub {
  //
  static function fetchLimit($ugid, $limit = 30, $page = null, $activeOnly = false) {
    $c = static::asCriteria($ugid, $activeOnly);
    return static::fetchAllBy($c, null, $limit, null, 'T1.DATE_ DESC', $page); //Setting this to T0.LAST_NAME kills it. T0.BIRTH and T0.SEX works, T0.FIRST_NAME does not. It will throw a "class 'label' not found" exception in _Rec.php. To work around this, add your order by in the Oracle wrapper.
  }
  static function fetchLimit_Ayoub($ugid, $limit = 30, $page = null, $activeOnly = false) {
    $c = static::asCriteria($ugid, $activeOnly);
    $c->clientId = CriteriaValue::in(array(7383, 8164));
    return static::fetchAllBy($c, null, $limit, null, 'T1.DATE_ DESC', $page);
  }
  static function asCriteria($ugid, $activeOnly) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->Mru = AuditMru_List::asClientJoin();
    $c->Batch = ClientCcdaBatch::asJoin();
    $c->setActiveOnly($activeOnly);
    return $c;
  }
}
class PStub_Search extends PatientStub {
  /*
   public $Hd_name;
   public $Hd_dob;
   public $_score;  // closeness of match; 100=perfect
   */
  //
  public function score(/*SearchCrit*/$sc) {
    $lev = $sc->lev($this->lastName, $this->firstName);    
    $this->_score = 100 - $lev;
  }
  public function setPerfect() {
    $this->_score = 100;
    return $this;
  }
  public function isPerfect() { 
    return $this->_score == 100;
  }
  //
  static function /*PStub_Search[]*/search($ugid, $last, $first, $uid, $birth = null, $activeOnly = false) {
	$last = strtoupper($last);
	$first = strtoupper($first);
    $recs = array();
    if (! empty($uid)) {
      $rec = static::searchForUid($ugid, $uid, null, $activeOnly);
      if ($rec) 
        $recs[] = $rec->setPerfect();
    }
    if (count($recs) == 0) {
      if (! empty($birth))
        $recs = static::fetchAllByBirth($ugid, $birth, $activeOnly); 
      else if (! empty($last)) 
        $recs = static::searchByName($ugid, $last, $first, false, $activeOnly);
    }
    return $recs;
  }
  static function /*PStub_Search[]*/searchByName($ugid, $last, $first, $perfect = false, $activeOnly = false, $limit = 100) {
    $recs = static::fetchCandidates($ugid, $last, 400, $activeOnly);
    logit_r($recs, 'candidates');
	//filterCandidates($recs, $perfect = false, $limit = 20) {
	Logger::debug('PatientList_Sql::searchByName: Before filtering, recs size is ' . sizeof($recs));
    if (! empty($recs)) {
      $sc = SearchCrit::create($last, $first);
      $recs = static::scoreCandidates($recs, $sc);
      $recs = static::filterCandidates($recs, $perfect, $limit);
    }
	Logger::debug('PatientList_Sql::searchByName: AFTER filtering, recs size is ' . sizeof($recs));
    return $recs;
  }
  static function searchByName_perfect($ugid, $last, $first) {
    return static::searchByName($ugid, $last, $first, true);
  }
  static function /*PStub_Search*/searchForExact($ugid, $last, $first, $birth, $sex = null) {
    $recs = static::searchByName_perfect($ugid, $last, $first);
    foreach ($recs as $rec)
      if ($sex == null || $rec->sex == $sex)
        if ($rec->birth == $birth)
          return $rec;
  }
  static function /*PStub_Search[]*/searchForMatches($ugid, $last, $first, $birth, $sex) {
    $recs = static::searchByName($ugid, $last, $first);
    foreach ($recs as $rec)
      if ($rec->isPerfect() && $rec->birth == $birth && $rec->sex == $sex)
        return array($rec);
    return $recs;
  }
  static function /*PStub_Search*/searchForUid($ugid, $uid, $cid = null, $activeOnly = false) {
    $c = new static();
    $c->userGroupId = $ugid;
    if ($cid)
      $c->clientId = CriteriaValue::notEquals($cid);
    $c->setHuid($uid);
    $c->setActiveOnly($activeOnly);
    return static::fetchOneBy($c); 
  }
  // 
  protected static function fetchCandidates($ugid, $last, $limit = 400, $activeOnly = false) {
	Logger::debug('PatientList_Sql::FetchCandidates: Entered with ' . $ugid . ', ' . $last . ', limit ' . $limit);
    $c = new static();
    $c->userGroupId = $ugid;
    //$c->Hd_name = Hdata_ClientName::create()->setValue($last)->asJoin($ugid); //Encrypted and therefore hits the HDATA tables, but in Analytics we don't have encrypted fields. So just use the last name as criteria.
	$c->lastName = $last;
    $c->setActiveOnly($activeOnly);
	
	try {
		$recs = static::fetchAllBy($c, null, $limit);
	}
	catch (Exception $e) {
		Logger::debug('PatientList_Sql::fetchCandidates: Got ERROR ' . $e->getMessage());
	}
	//Logger::debug('PatientList_Sql::fetchCandidates: Got result ' . print_r($recs, true));
    return $recs;
  }
  protected static function fetchAllByBirth($ugid, $birth, $activeOnly = false) {
    $c = new static();
    $c->userGroupId = $ugid;
    //$c->Hd_dob = Hdata_ClientDob::create()->setValue($birth)->asJoin($ugid); //Encrypted and therefore hits the HDATA tables, but in Analytics we don't have encrypted fields. So just use the last name as criteria.
	//Since we aren't using HDATA to format the date anymore, we have to manually format it to the way the DB stores it.
	//Convert from the format 02-Jul-1960 (that Javascript has) to 1960-07-02 (that the database has)
	
	try {
		$dateObj = DateTime::createFromFormat('d-M-Y', $birth);
		$birth = $dateObj->format('Y-m-d');
	}
	catch (Exception $e) {
		throw new RuntimeException('Could not format the date ' . $birth);
	}
	$c->birth = $birth;
    $c->setActiveOnly($activeOnly);    
    $recs = static::fetchAllBy($c, null, 50);
    return $recs;
  }
  protected static function scoreCandidates($recs, $c) {
    foreach ($recs as $rec)
      $rec->score($c);
    return RecSort::sort($recs, '-_score');
  }
  protected static function filterCandidates($recs, $perfect = false, $limit = 20) {
    if (count($recs) > 1) {
      for ($i = 0; $i < count($recs); $i++) {
        if (! $recs[$i]->isPerfect()) {
          if ($i == 0 && $perfect)
            return array();
          if ($i > 0)
            $limit = $i;
          break;
        }
      }
      $recs = array_slice($recs, 0, $limit);  /* will either be all perfect or set (limit) of matches */
    }
    return $recs;
  }
}
class SearchCrit {
  public $last;
  public $first;
  public $lenLast;
  public $lenFirst;
  //
  static function create($last, $first) {
    $me = new static();
    $me->last = strtoupper($last);
    $me->first = strtoupper($first);
    $me->lenLast = strlen($last);
    $me->lenFirst = $first ? strlen($first) : 0;
    return $me;
  }
  //
  public function lev($last, $first) {
    $l1 = levenshtein(strtoupper(substr($last, 0, $this->lenLast)), $this->last);
    $l2 = $this->lenFirst ? levenshtein(strtoupper(substr($first, 0, $this->lenFirst)), $this->first) : 0;
    return $l1 * 10 + $l2;    
  }
}
class AuditMru_List extends AuditMruRec implements ReadOnly {
  //
  public $clientId;
  public $userId;
  public $date;
  public $action;
  public $recName;
  public $recId;
  public $label;
  //
  static function asClientJoin() {
    $c = new static();
    return CriteriaJoin::optional($c, 'clientId');
  }
  public function getJsonFilters() {
    return array(
      'date' => JsonFilter::informalDateTime());
  }
} 