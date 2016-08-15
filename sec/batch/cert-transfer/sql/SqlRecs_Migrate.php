<?php 
//
class PkMapper {
  //
  public $clientId;
  public $sessionId;
  public $threadId;
  public $postId;
  public $schedEventId;
  public $ipc;
  //
  public $map = array(
    'clientId' => array(),
    'sessionId' => array(),
    'threadId' => array(),
    'postId' => array(),
    'schedEventId' => array(),
    'ipc' => array(
      '1' => '918089',
      '2' => '691795',
      '3' => '710124',
      '4' => '600085',
      '5' => '699288',
      '6' => '600001',
      '7' => '600086',
      '8' => '719487',
      '9' => '918211',
      '10' => '686298',
      '11' => '600087',
      '12' => '918171',
      '13' => '600088',
      '14' => '719539',
      '15' => '917689',
      '16' => '697779',
      '17' => '719475',
      '18' => '686259',
      '19' => '719450',
      '20' => '917989',
      '21' => '600089',
      '22' => '600090',
      '23' => '600091',
      '24' => '600092',
      '25' => '699285'));
  //
  public function next($pk, $rec) {
    $current = $rec->$pk;
    $next = $this->$pk + 1;
    $this->$pk = $next;
    $this->map[$pk][$current] = $next;
    $rec->$pk = $next;
    $rec->{"_$pk"} = $current;
  }
  public function map($pk, $current) {
    if (empty($current)) 
      return $current;
    if (isset($this->map[$pk][$current]))
      return $this->map[$pk][$current];
    throw new Exception("FK mapping not found: $pk=$current");
  }
  public function write($fp, $from, $to) {
    static::writeCols($fp, array('TABLE', $from, $to));
    foreach ($this->map as $table => $map) {
      foreach ($map as $old => $new) 
        static::writeCols($fp, array($table, $old, $new));
    }
  }
  //
  static function fetch() {
    $me = new static();
    $me->clientId = Dao_Cert::fetchValue(static::sql('clients', 'client_id'));
    $me->clientId = 24924;
    $me->sessionId = Dao_Cert::fetchValue(static::sql('sessions', 'session_id'));
    $me->threadId = Dao_Cert::fetchValue(static::sql('msg_threads', 'thread_id'));
    $me->postId = Dao_Cert::fetchValue(static::sql('msg_posts', 'post_id'));
    $me->schedEventId = Dao_Cert::fetchValue(static::sql('sched_events', 'sched_event_id'));
    $me->ipc = 1000000;
    return $me;
  }
  static function sql($table, $pk) {
    return "SELECT MAX($pk) FROM $table";
  }
  static function writeCols($fp, $cols) {
    fwrite($fp, '"' . implode('","', $cols) . '"' . "\n");
  }
}
abstract class SqlRec_Migrate extends SqlRec {
  //
  public function nextPk($pkmap) {
    $pk = $this->getPkFid();
    $pkmap->next($pk, $this);
  }
  public function clearPk() {
    $pk = $this->getPkFid();
    $this->$pk = null;
  }
  public function mapFks() {
    try {
      $fids = func_get_args();
      $pkmap = array_shift($fids);
      foreach ($fids as $fid) 
        $this->$fid = $pkmap->map($fid, $this->$fid);
    } catch (Exception $e) {
      p_r($e->getMessage());
      p_r($this);
      exit;
    }
  }
  //
  static function fromRows($rows, $pkmap = null) {
    $mes = array();
    foreach ($rows as $row)
      $mes[] = static::fromRow($row, $pkmap);
    return $mes;
  }
  static function fromRow($row, $pkmap = null) {
    $me = new static($row);
    return $me;
  }
  static function appendSqlInserts($fm, $recs, $pkmap = null) {
    foreach ($recs as $rec)  
      static::appendSqlInsert($fm, $rec, $pkmap);
  }
  static function appendSqlInsert($fm, $rec, $pkmap = null) {
    $fm->write($rec->getSqlInsert() . ";");
  }
}
class UserGroup_Migrate extends SqlRec_Migrate implements ReadOnly {
  public $userGroupId;
  public $name;
  public $usageLevel;
  public $estTzAdj;
  public $sessionTimeout;
  //
  public function getSqlTable() {
    return 'user_groups';
  }
  static function migrate($fm, $ugid, $pkmap) {
    $row = Dao_Migrate::fetchRow("SELECT * FROM user_groups WHERE user_group_id=$ugid");
    static::appendSqlInsert($fm, static::fromRow($row));
    LookupData_Migrate::migrateByGroup($fm, $ugid);
    Ipc_Migrate::migrate($fm, $ugid, $pkmap);
    Address_Migrate::migrateUserGroup($fm, $ugid);
  }
  static function fromRow($row) {
    $me = parent::fromRow($row);
    $me->sessionTimeout = 60;
    return $me;
  }
}
class User_Migrate extends SqlRec_Migrate implements ReadOnly {
  public $userId;
  public $uid;
  public $pw;
  public $name;
  public $admin;
  public $subscription;
  public $active;
  public $regId;
  public $trialExpdt;
  public $userGroupId;
  public $userType;
  public $dateCreated;
  public $licenseState;
  public $license;
  public $dea;
  public $npi;
  public $email;
  public $expiration;
  public $expireReason;
  public $pwExpires;
  public $tosAccepted;
  //
  public function getSqlTable() {
    return 'users';
  }
  static function migrate($fm, $ugid) {
    $rows = Dao_Migrate::fetchRows("SELECT * FROM users WHERE user_group_id=$ugid");
    static::appendSqlInserts($fm, static::fromRows($rows));
  }
  static function appendSqlInsert($fm, $rec) {
    parent::appendSqlInsert($fm, $rec);
    LookupData_Migrate::migrateByUser($fm, $rec->userId);
    //UserLoginReq_Migrate::migrate($fm, $rec->userId);
    NcUser_Migrate::migrate($fm, $rec->userId);
    //BillInfo_Migrate::migrate($fm, $rec->userId);
  }
}
class BillInfo_Migrate extends SqlRec_Migrate implements ReadOnly {
  public $userId;
  public $name;
  public $address1;
  public $address2;
  public $city;
  public $state;
  public $zip;
  public $country;
  public $cardNumber;
  public $expMonth;
  public $expYear;
  public $nextBillDate;
  public $balance;
  public $phoneNum;
  public $phoneExt;
  public $cardType;
  public $startBillDate;
  public $billCode;
  public $lastBillStatus;
  //
  public function getSqlTable() {
    return 'billinfo';
  }
  static function migrate($fm, $userId) {
    $rows = Dao_Migrate::fetchRows("SELECT * FROM billinfo WHERE user_id=$userId");
    static::appendSqlInserts($fm, static::fromRows($rows));
  }
}
class LookupData_Migrate extends SqlRec_Migrate implements ReadOnly {
  public $lookupDataId;
  public $level;
  public $levelId;
  public $lookupTableId;
  public $instance;
  public $data;
  //
  public function getSqlTable() {
    return 'lookup_data';
  }
  static function migrateByGroup($fm, $ugid) {
    $rows = Dao_Migrate::fetchRows("SELECT * FROM lookup_data WHERE level='G' AND level_id=$ugid");
    static::appendSqlInserts($fm, static::fromRows($rows));
  }
  static function migrateByUser($fm, $userId) {
    $rows = Dao_Migrate::fetchRows("SELECT * FROM lookup_data WHERE level='U' AND level_id=$userId");
    static::appendSqlInserts($fm, static::fromRows($rows));
  }
  static function fromRow($row) {
    $me = parent::fromRow($row);
    $me->clearPk();
    return $me;
  }
}
class Ipc_Migrate extends SqlRec_Migrate implements ReadOnly {
  //
  public $ipc;
  public $userGroupId;
  public $name;
  public $desc;
  public $cat;
  public $codeSystem;
  public $code;
  //
  public function getSqlTable() {
    return 'iproc_codes';
  }
  static function migrate($fm, $ugid, $pkmap) {
    $rows = Dao_Migrate::fetchRows("SELECT * FROM lookup_data WHERE level='G' AND lookup_table_id=18 AND level_id=$ugid AND instance>25");
    static::appendSqlInserts($fm, static::fromRows($rows, $pkmap));
  }
  static function fromRow($row, $pkmap) {
    $proc = jsondecode($row['DATA']);
    $me = new static();
    $me->ipc = $row['INSTANCE'];
    $me->userGroupId = $row['LEVEL_ID'];
    $me->name = $proc->name;
    $me->desc = $proc->name;
    $me->cat = '6';  // CAT_PROC
    $me->nextPk($pkmap);
    return $me;
  }
}
class UserLoginReq_Migrate extends SqlRec_Migrate implements ReadOnly {
  public $userLoginReqId;
  public $userId;
  public $loginReqId;
  public $name;
  public $active;
  public $status;
  public $dateNotified;
  public $dateRcvd;
  public $dateExpires;
  public $dateUpdated;
  public $updatedBy;
  public $comments;
  //
  public function getSqlTable() {
    return 'user_login_reqs';
  }
  static function migrate($fm, $userId) {
    $rows = Dao_Migrate::fetchRows("SELECT * FROM user_login_reqs WHERE user_id=$userId");
    static::appendSqlInserts($fm, static::fromRows($rows));
  }
}
class NcUser_Migrate extends SqlRec_Migrate implements ReadOnly {
  public $userId;
  public $userType;
  public $roleType;
  public $partnerId;
  public $nameLast;
  public $nameFirst;
  public $nameMiddle;
  public $namePrefix;
  public $nameSuffix;
  public $freeformCred;
  //
  public function getSqlTable() {
    return 'nc_users';
  }
  static function migrate($fm, $userId) {
    $rows = Dao_Migrate::fetchRows("SELECT user_id, user_type, role_type FROM nc_users WHERE user_id=$userId");
    static::appendSqlInserts($fm, static::fromRows($rows));
  }
}
class Client_Migrate extends SqlRec_Migrate implements ReadOnly {
  public $clientId;
  public $userGroupId;
  public $uid;
  public $lastName;
  public $firstName;
  public $sex;
  public $birth;
  public $img;
  public $dateCreated;
  public $active;
  public $cdata1;
  public $cdata2;
  public $cdata3;
  public $trial;
  public $livingWill;
  public $poa;
  public $gestWeeks;
  public $middleName;
  public $notes;
  public $dateUpdated;
  public $race;
  public $ethnicity;
  public $deceased;
  public $language;
  public $familyRelease;
  public $primaryPhys;
  public $releasePref;
  public $release;
  public $userRestricts;
  public $emrId;
  //
  public function getSqlTable() {
    return 'clients';
  }
  static function migrate($fm, $ugid, $pkmap) {
    $rows = Dao_Migrate::fetchRows("SELECT * FROM clients WHERE user_group_id=$ugid");
    static::appendSqlInserts($fm, static::fromRows($rows, $pkmap), $pkmap);
  }
  static function fromRow($row, $pkmap) {
    $me = parent::fromRow($row);
    $me->nextPk($pkmap);
    $me->emrId = $me->_clientId;
    return $me;
  }
  static function appendSqlInsert($fm, $rec, $pkmap) {
    parent::appendSqlInsert($fm, $rec);
    Address_Migrate::migrate($fm, $rec->_clientId, $pkmap);
    ICard_Migrate::migrate($fm, $rec->_clientId, $pkmap);
    // could do client_updates here if map type+id; probably not worth it
  }
}
class ICard_Migrate extends SqlRec_Migrate  implements ReadOnly {
  public $clientId;
  public $seq;
  public $planName;
  public $subscriberName;
  public $nameOnCard;
  public $groupNo;
  public $subscriberNo;
  public $dateEffective;
  public $active;
  //
  public function getSqlTable() {
    return 'client_icards';
  }
  static function migrate($fm, $cid, $pkmap) {
    $rows = Dao_Migrate::fetchRows("SELECT * FROM client_icards WHERE client_id=$cid");
    static::appendSqlInserts($fm, static::fromRows($rows, $pkmap));
  }
  static function fromRow($row, $pkmap) {
    $me = parent::fromRow($row);
    $me->mapFks($pkmap, 'clientId');
    return $me;
  }
}
class Address_Migrate extends SqlRec_Migrate implements ReadOnly {
  public $addressId;
  public $tableCode;
  public $tableId;
  public $type;
  public $addr1;
  public $addr2;
  public $addr3;
  public $city;
  public $state;
  public $zip;
  public $country;
  public $phone1;
  public $phone1Type;
  public $phone2;
  public $phone2Type;
  public $phone3;
  public $phone3Type;
  public $email1;
  public $email2;
  public $name;
  //
  public function getSqlTable() {
    return 'addresses';
  }
  static function migrate($fm, $cid, $pkmap) {
    $rows = Dao_Migrate::fetchRows("SELECT * FROM addresses WHERE table_code='C' AND table_id=$cid");
    static::appendSqlInserts($fm, static::fromRows($rows, $pkmap));
  }
  static function migrateUserGroup($fm, $ugid) {
    $row = Dao_Migrate::fetchRow("SELECT * FROM addresses WHERE table_code='G' AND table_id=$ugid");
    $me = parent::fromRow($row);
    $me->clearPk();
    parent::appendSqlInsert($fm, $me);
  }
  static function fromRow($row, $pkmap) {
    $me = parent::fromRow($row);
    $me->clearPk();
    $me->tableId = $pkmap->map('clientId', $me->tableId);
    return $me;
  }
}
class IpcHm_Migrate extends SqlRec_Migrate implements ReadOnly {
  //
  public $ipc;
  public $userGroupId;
  public $clientId;
  public $reportId;
  public $every;
  public $interval;
  public $active;
  //
  public function getSqlTable() {
    return 'ipc_hm';
  }
  static function migrate($fm, $ugid, $pkmap) {
    $rows = Dao_Migrate::fetchRows("SELECT * FROM data_hm WHERE user_group_id=$ugid AND session_id IS NULL AND active=1 AND cint IS NOT NULL");
    static::appendSqlInserts($fm, static::fromRows($rows, $pkmap));
  }
  static function fromRow($row, $pkmap) {
    $me = new static();
    $me->ipc = $pkmap->map('ipc', $row['PROC_ID']);
    $me->userGroupId = $row['USER_GROUP_ID'];
    $me->clientId = $pkmap->map('clientId', $row['CLIENT_ID']);
    $me->every = $row['CEVERY'];
    $me->interval = static::cintToInterval($row['CINT']);
    $me->active = '1';
    return $me;
  }
  static function cintToInterval($cint) {
    switch ($cint) {
      case 0:  // year
        return '4';
      case 1:  // month
        return '3';
      case 2:  // week
        return '2';
      case 3:  // day
        return '1';
    }
  }
}
class Session_Migrate extends SqlRec_Migrate implements ReadOnly {
  public $sessionId;
  public $userGroupId;
  public $clientId;
  public $templateId;
  public $dateCreated;
  public $dateUpdated;
  public $dateService;
  public $closed;
  public $closedBy;
  public $dateClosed;
  public $billed;
  public $schedId;
  public $data;
  public $createdBy;
  public $updatedBy;
  public $sendTo;
  public $assignedTo;
  public $html;
  public $title;
  public $standard;
  public $noteDate;
  //
  public function getSqlTable() {
    return 'sessions';
  }
  static function migrate($fm, $ugid, $pkmap) {
    $rows = Dao_Migrate::fetchRows("SELECT * FROM sessions WHERE user_group_id=$ugid");
    static::appendSqlInserts($fm, static::fromRows($rows, $pkmap));
  }
  static function fromRow($row, $pkmap) {
    $me = parent::fromRow($row);
    $me->nextPk($pkmap);
    $me->mapFks($pkmap, 'clientId');
    $me->schedId = null;
    return $me;
  }
}
class Sched_Migrate extends SqlRec_Migrate implements ReadOnly {
  public $schedId;
  public $userId;  
  public $userGroupId;
  public $clientId;
  public $date;
  public $timeStart;
  public $duration;
  public $closed;
  public $status;
  public $comment;
  public $type;
  public $timestamp;
  public $schedEventId;
  //
  public function getSqlTable() {
    return 'scheds';
  }
  static function migrate($fm, $ugid, $pkmap) {
    $rows = Dao_Migrate::fetchRows("SELECT * FROM scheds WHERE user_group_id=$ugid");
    static::appendSqlInserts($fm, static::fromRows($rows, $pkmap), $pkmap);
  }
  static function fromRow($row, $pkmap) {
    $me = parent::fromRow($row);
    $me->clearPk();
    $me->mapFks($pkmap, 'clientId');
    return $me;
  }
  static function appendSqlInsert($fm, $rec, $pkmap) {
    if (! empty($rec->schedEventId)) {
      $event = SchedEvent_Migrate::migrate($fm, $rec->schedEventId, $pkmap);
      $rec->schedEventId = $event->schedEventId;
    }
    parent::appendSqlInsert($fm, $rec);
  }
}
class SchedEvent_Migrate extends SqlRec_Migrate implements ReadOnly {
  public $schedEventId;
  public $rpType;
  public $rpEvery;
  public $rpUntil;
  public $rpOn;
  public $rpBy;
  public $comment;
  //
  public function getSqlTable() {
    return 'sched_events';
  }
  static function migrate($fm, $keid, $pkmap) {
    $row = Dao_Migrate::fetchRow("SELECT * FROM sched_events WHERE sched_event_id=$keid");
    $me = static::fromRow($row, $pkmap);
    static::appendSqlInsert($fm, $me);
    return $me;
  }
  static function fromRow($row, $pkmap) {
    $me = parent::fromRow($row);
    $me->nextPk($pkmap);
    return $me;
  }
}
class MsgThread_Migrate extends SqlRec_Migrate implements ReadOnly {
  public $threadId;
  public $userGroupId;
  public $clientId;
  public $creatorId;
  public $creator;
  public $dateCreated;
  public $dateToSend;
  public $dateClosed;
  public $type;
  public $status;
  public $priority;
  public $subject;
  //  
  public function getSqlTable() {
    return 'msg_threads';
  }
  static function migrate($fm, $ugid, $pkmap) {
    $rows = Dao_Migrate::fetchRows("SELECT * FROM msg_threads WHERE user_group_id=$ugid");
    static::appendSqlInserts($fm, static::fromRows($rows, $pkmap), $pkmap);
  }
  static function fromRow($row, $pkmap) {
    $me = parent::fromRow($row);
    $me->nextPk($pkmap);
    $me->mapFks($pkmap, 'clientId');
    return $me;
  }
  static function appendSqlInsert($fm, $rec, $pkmap) {
    parent::appendSqlInsert($fm, $rec);
    MsgPost_Migrate::migrate($fm, $rec->_threadId, $pkmap);
    MsgInbox_Migrate::migrate($fm, $rec->_threadId, $pkmap);
  }
}
class MsgPost_Migrate extends SqlRec_Migrate implements ReadOnly {
  public $postId;
  public $threadId;
  public $action;
  public $dateCreated;
  public $authorId;
  public $author;
  public $body;
  public $sendTo;
  public $data;
  //
  public function getSqlTable() {
    return 'msg_posts';
  }
  static function migrate($fm, $mtid, $pkmap) {
    $rows = Dao_Migrate::fetchRows("SELECT * FROM msg_posts WHERE thread_id=$mtid");
    static::appendSqlInserts($fm, static::fromRows($rows, $pkmap), $pkmap);
  }
  static function fromRow($row, $pkmap) {
    $me = parent::fromRow($row);
    $me->nextPk($pkmap);
    $me->mapFks($pkmap, 'threadId');
    return $me;
  }
}
class MsgInbox_Migrate extends SqlRec_Migrate implements ReadOnly {
  public $inboxId;
  public $recipient;
  public $threadId;
  public $postId;  
  public $isRead;
  //
  public function getSqlTable() {
    return 'msg_inbox';
  }
  static function migrate($fm, $mtid, $pkmap) {
    $rows = Dao_Migrate::fetchRows("SELECT * FROM msg_inbox WHERE thread_id=$mtid");
    static::appendSqlInserts($fm, static::fromRows($rows, $pkmap), $pkmap);
  }
  static function fromRow($row, $pkmap) {
    $me = parent::fromRow($row);
    $me->clearPk();
    $me->mapFks($pkmap, 'threadId', 'postId');
    return $me;
  }
}
abstract class FsDataRec_Migrate extends SqlRec_Migrate implements ReadOnly {
  static function migrate($fm, $ugid, $pkmap) {
    $rows = Dao_Migrate::fetchRows(static::migrateSql($ugid, $pkmap));
    static::appendSqlInserts($fm, static::fromRows($rows, $pkmap), $pkmap);
  }
  static function migrateSql($ugid, $pkmap) {
    $rec = new static();
    return "SELECT * FROM " . $rec->getSqlTable() . " WHERE user_group_id=$ugid";
  }
  static function fromRow($row, $pkmap) {
    $me = parent::fromRow($row);
    $me->clearPk();
    $me->mapFks($pkmap, 'clientId', 'sessionId');
    return $me;
  }
}
class DataAllergy_Migrate extends FsDataRec_Migrate implements ReadOnly {
  public $dataAllergyId;
  public $userGroupId;
  public $clientId;
  public $sessionId;
  public $date;  
	public $index;
	public $agent;
	public $reactions;
	public $active;   
	public $dateUpdated;
	public $source;
  //
	public function getSqlTable() {
    return 'data_allergies';
  }
}
class DataDiagnosis_Migrate extends FsDataRec_Migrate implements ReadOnly {
  public $dataDiagnosesId;
  public $userGroupId;
  public $clientId;
  public $sessionId;
  public $date;  
  public $parUid;
  public $text;
  public $parDesc;
  public $icd;
  public $active;
  public $dateUpdated;
  public $dateClosed;
  public $status;
  //
  public function getSqlTable() {
    return 'data_diagnoses';
  }
  static function fromRow($row, $pkmap) {
    $me = parent::fromRow($row, $pkmap);
    $me->status = '1';
    return $me;
  }
}
class DataHm_Migrate extends FsDataRec_Migrate implements ReadOnly {
  public $dataHmId;
  public $userGroupId;
  public $clientId;
  public $sessionId;
  public $type;           
  public $procId;        
  public $proc;          
  public $dateText;      
  public $dateSort;      
  public $results;       
  public $nextTimestamp; 
  public $active;
  public $dateUpdated;
  public $nextText;      
  public $cint;      
  public $cevery;         
  //
  public function getSqlTable() {
    return 'data_hm';
  }
  static function migrate($fm, $ugid, $pkmap) {
    $rows = Dao_Migrate::fetchRows("SELECT * FROM data_hm WHERE user_group_id=$ugid AND session_id=0 AND active=1");
    static::appendSqlInserts($fm, static::fromRows($rows, $pkmap), $pkmap);
  }
}
class DataImmun_Migrate extends FsDataRec_Migrate implements ReadOnly {
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
  //
  public function getSqlTable() {
    return 'data_immuns';
  }
}
class DataMed_Migrate extends FsDataRec_Migrate implements ReadOnly {
  public $dataMedId;
  public $userGroupId;
  public $clientId;
  public $sessionId;
  public $date;  
	public $quid;
	public $index;
	public $name;
	public $amt;
	public $freq;
	public $asNeeded;
	public $meals;
	public $route;
	public $length;
	public $disp;
	public $text;
	public $rx;
	public $active;   
	public $expires;
	public $dateUpdated;
	public $source;
	public $ncDrugName;
	public $ncGenericName;
	public $ncRxGuid;
	public $ncOrderGuid;
	public $ncOrigrxGuid;
	public $ncDosageNum;
	public $ncDosageForm;
	public $ncDosageNumId;
	public $ncDosageFormId;
	public $ncRouteId;
	public $ncFreqId;
  //
	public function getSqlTable() {
    return 'data_meds';
  }
}
class DataSync_Migrate extends FsDataRec_Migrate implements ReadOnly {
  //
  public $dataSyncId;
  public $userGroupId;
  public $clientId;
  public $dsyncId;
  public $dsync;
  public $dateSort;
  public $sessionId;
  public $value;
  public $active;
  public $dateUpdated;
  //
  public function getSqlTable() {
    return 'data_syncs';
  }
}
class DataVital_Migrate extends FsDataRec_Migrate implements ReadOnly {
  public $dataVitalsId;
  public $userGroupId;
  public $clientId;
  public $sessionId;
  public $date;  
  public $pulse;
  public $resp;
  public $bpSystolic;
  public $bpDiastolic;
  public $bpLoc;
  public $temp;
  public $tempRoute;
  public $wt;
  public $wtUnits;
  public $height;
  public $hc;
  public $hcUnits;
  public $wc;
  public $wcUnits;
  public $o2Sat;
  public $o2SatOn;
  public $bmi;
  public $htUnits;
  public $dateUpdated;
  public $active;
  public $wtLbs;
  public $htIn;
  //
  public function getSqlTable() {
    return 'data_vitals';
  }
}
class TrackItem_Migrate extends FsDataRec_Migrate implements ReadOnly {
  public $trackItemId;
  public $userGroupId;
  public $clientId;
  public $sessionId;
  public $key;
  public $userId;
  public $priority;
  public $trackCat;
  public $trackDesc;
  public $cptCode;
  public $status;  
  public $orderDate;
  public $orderBy;
  public $orderNotes;
  public $schedDate;
  public $schedWith;
  public $schedLoc;
  public $schedBy;
  public $schedNotes;
  public $closedDate;
  public $closedFor;
  public $closedBy;
  public $closedNotes;
  public $diagnosis;
  public $icd;
  public $freq;
  public $duration;
  //
  public function getSqlTable() {
    return 'track_items';
  }
}
class TemplatePreset_Migrate extends SqlRec_Migrate implements ReadOnly {
  public $templatePresetId;
	public $userGroupId;
	public $templateId;
	public $name;
	public $dateCreated;
	public $dateUpdated;
	public $actions;
	public $createdBy;
	public $updatedBy;
  //
  public function getSqlTable() {
    return 'template_presets';
  }
  static function migrate($fm, $ugid) {
    $rows = Dao_Migrate::fetchRows("SELECT * FROM template_presets WHERE user_group_id=$ugid");
    static::appendSqlInserts($fm, static::fromRows($rows));
  }
  static function fromRow($row) {
    $me = parent::fromRow($row);
    $me->clearPk();
    return $me;
  }
}
//
class Dao_Migrate extends Dao {
  static $DB;
  //
  static function setDb($db) {
    static::$DB = $db;
  }
  static function open() {
    parent::open(static::$DB);
  }
}
class Dao_Cert extends Dao {
  static $DB;
  //
  static function setDb($db) {
    static::$DB = $db;
  }
  static function open() {
    parent::open(static::$DB);
  }
}