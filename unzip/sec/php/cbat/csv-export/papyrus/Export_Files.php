<?php
require_once 'php/data/file/_CsvFile.php';
//
/** CSV Files */
class PatientsCsv extends CsvFile {
  //
  static $FILENAME = 'patients.csv';
  static $CSVREC_CLASS = 'PatientsCsvRec';
  static $HAS_FID_ROW = true;
  //
  static function create() {
    return new static();
  }
  //
  public function add(/*Client_Export*/$client) {
    $rec = PatientsCsvRec::from($client);
    return parent::add($rec); 
  }
}
class ApptsCsv extends CsvFile {
  //
  static $FILENAME = 'appts.csv';
  static $CSVREC_CLASS = 'ApptsCsvRec';
  static $HAS_FID_ROW = true;
  //
  static function create() {
    return new static();
  }
  //
  public function load(/*Sched_Export*/$scheds, /*ApptStatuses*/$statuses) {
    $recs = ApptsCsvRec::all($scheds, $statuses);
    return parent::load($recs);
  }
}
/** CSV Recs */
class PatientsCsvRec extends CsvRec {
  //
  public $legacyPatId;
  public $last;
  public $first;
  public $mi;
  public $suffix;
  public $gender;
  public $dob;
  public $ssn;
  public $email;
  public $emlname;
  public $emfname;
  public $emphone;
  public $address1;
  public $address2;
  public $city;
  public $state;
  public $zip;
  public $ph1;
  public $ph2;
  public $ph3;
  public $insurance1;
  public $policy1;
  public $grp1;
  public $payorid1;
  public $relationship1;
  public $insFname1;
  public $insLname1;
  public $insDob1;
  public $seq1;
  public $insSource1;
  public $insurance2;
  public $policy2;
  public $grp2;
  public $payorid2;
  public $relationship2;
  public $insFname2;
  public $insLname2;
  public $insDob2;
  public $seq2;
  public $insSource2;
  public $externalId;
  public $race;
  public $ethnicity;
  public $language;
  public $parentFname;
  public $parentLname;
  public $parentMi;
  public $parentAddress1;
  public $parentAddress2;
  public $parentCity;
  public $parentState;
  public $parentZip;
  public $parentPh1;
  public $parentPh2;
  public $spouseFname;
  public $spouseLname;
  public $spouseMi;
  public $spouseAddress1;
  public $spouseAddress2;
  public $spouseCity;
  public $spouseState;
  public $spouseZip;
  public $spousePh1;
  public $spousePh2;
  public $pharmacyName;
  public $pharmacyAddress1;
  public $pharmacyAddress2;
  public $pharmacyCity;
  public $pharmacyState;
  public $pharmacyZip;
  public $pharmacyPh1;
  public $pharmacyPh2;
  public $ins1EffDate;
  public $ins2EffDate;
  public $ins3EffDate;
  //
  static function from(/*Client_Export*/$c) {
    $home = get($c, 'Address_Home');
    $emer = get($c, 'Address_Emer');
    $parent = get($c, 'Address_Parent');
    $spouse = get($c, 'Address_Spouse');
    $rx = get($c, 'Address_Rx');
    $ic1 = $c->getICard(1);
    $ic2 = $c->getICard(2);
    $me = new static();
    $me->legacyPatId = $c->uid;
    $me->externalId = $c->clientId;
    $me->last = $c->lastName;
    $me->first = $c->firstName;
    $me->mi = substr($c->middleName, 0, 1);
    $me->gender = $c->sex;
    $me->dob = formatConsoleDate($c->birth);
    $me->ssn = $c->cdata1;
    $me->race = $c->race;
    $me->ethnicity = $c->ethnicity;
    $me->language = $c->language;
    if ($home) {
      $me->email = $home->email1;
      $me->address1 = $home->addr1;
      $me->address2 = $home->addr2;
      $me->city = $home->city;
      $me->state = $home->state;
      $me->zip = $home->zip;
      $me->ph1 = $home->phone1;
      $me->ph2 = $home->phone2;
    }
    if ($emer) {
      $me->emlname = $emer->name;
      $me->emphone = $emer->phone1;
    }
    if ($parent) {
      $me->parentLname = $parent->name;
      $me->parentAddress1 = $parent->addr1;
      $me->parentAddress2 = $parent->addr2;
      $me->parentCity = $parent->city;
      $me->parentState = $parent->state;
      $me->parentZip = $parent->zip;
      $me->parentPh1 = $parent->phone1;
      $me->parentPh2 = $parent->phone2;
    }
    if ($spouse) {
      $me->spouseLname = $spouse->name;
      $me->spouseAddress1 = $spouse->addr1;
      $me->spouseAddress2 = $spouse->addr2;
      $me->spouseCity = $spouse->city;
      $me->spouseState = $spouse->state;
      $me->spouseZip = $spouse->zip;
      $me->spousePh1 = $spouse->phone1;
      $me->spousePh2 = $spouse->phone2;
    }
    if ($rx) {
      $me->pharmacyName = $rx->name;
      $me->pharmacyAddress1 = $rx->addr1;
      $me->pharmacyAddress2 = $rx->addr2;
      $me->pharmacyCity = $rx->city;
      $me->pharmacyState = $rx->state;
      $me->pharmacyZip = $rx->zip;
      $me->pharmacyPh1 = $rx->phone1;
      $me->pharmacyPh2 = $rx->phone2;
    }
    if ($ic1) {
      $me->insurance1 = $ic1->planName;
      $me->policy1 = $ic1->subscriberNo;
      $me->grp1 = $ic1->groupNo;
      $me->insLname1 = $ic1->nameOnCard ?: $ic1->subscriberName;
      $me->seq1 = $ic1->seq;
      $me->ins1EffDate= formatConsoleDate($ic1->dateEffective);
    }
    if ($ic2) {
      $me->insurance2 = $ic2->planName;
      $me->policy2 = $ic2->subscriberNo;
      $me->grp2 = $ic2->groupNo;
      $me->insLname2 = $ic2->nameOnCard ?: $ic2->subscriberName;
      $me->seq2 = $ic2->seq;
      $me->ins2EffDate= formatConsoleDate($ic2->dateEffective);
    }
    return $me;
  }
}
class ApptsCsvRec extends CsvRec {
  //
  public $apptDate;
  public $apptStartTime;
  public $apptLength;
  public $apptProvider;
  public $apptChartNumber;
  public $apptName;
  public $apptPhone;
  public $apptIsBreak;
  public $apptStatus;
  public $apptReasonCode;
  public $apptNote;
  //
  static function all(/*Sched_Export[]*/$scheds, /*ApptStatuses*/$statuses) {
    $us = array();
    foreach ($scheds as $sched) {
      $statusCode = $statuses->getStatusCode($sched->status);
      $us[] = static::from($sched, $statusCode);
    }
    return $us;
  }
  static function from(/*Sched_Export*/$s, $statusCode) {
    $hr = substr($s->timeStart, 0, -2);
    $min = substr($s->timeStart, -2);
    $event = get($s, 'Event');
    $me = new static();
    $me->apptDate = formatConsoleDate($s->date);
    $me->apptStartTime = "$hr:$min:00";
    $me->apptLength = $s->duration;
    $me->apptProvider = $s->userId;
    $me->apptChartNumber = $s->clientId;
    $me->apptStatus = $statusCode;
    $me->apptReasonCode = $s->type;
    $me->apptNote = $s->comment;
    if ($event) {
      $me->apptIsBreak = 1;
      $me->apptName = $event->comment;
    } else {
      $me->apptIsBreak = 0;
    }
    return $me;
  }
}
