<?php
require_once 'php/data/rec/sql/_ClientRec.php';
require_once 'php/data/rec/sql/_ICardRec.php';
require_once 'php/data/rec/sql/_AddressRec.php';
//
class Client_Import extends ClientRec implements ReadOnly, AutoEncrypt {
  //
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
  //
  static $UGID = 1; // 3022
  //
  static function fetchSsnMap() {
    $c = new static();
    $c->userGroupId = static::$UGID;
    $us = static::fetchAllBy($c);
    $map = array();
    foreach ($us as $me) {
      if ($me->cdata1) 
        $map[$me->cdata1] = $me->clientId;
    }
    return $map;
  }
}
class ScanFile_Import extends SqlRec implements ReadOnly, AutoEncrypt {
  //
  public $scanFileId;
  public $userGroupId;
  public $filename;
  public $fileseq;
  public $origFilename;
  public $height;
  public $width;
  public $rotation;
  public $mime;
  public $scanIndexId;
  public $seq;
  public $dateCreated;
  public $createdBy;
  //
  public function getSqlTable() {
    return 'scan_files';
  }
  public function toString() {
    return $this->getSqlInsert() . ';';
  }
  public function getEncryptedFids() {
    return array();
  }
  //
  static function from($csv, $siid) {
    $year = substr($csv->date_time, 2, 2);
    $rec = new static();
    $rec->userGroupId = Client_Import::$UGID;
    $rec->filename = $year . '\\' . $csv->Report_ID;
    $rec->origFilename = $csv->report_name;
    $rec->mime = 'application/pdf';
    $rec->scanIndexId = $siid;
    $rec->dateCreated = nowNoQuotes();
    return $rec;
  }
}
class ScanIndex_Import extends SqlRec implements ReadOnly, AutoEncrypt {
  //
  public $scanIndexId; 
  public $userGroupId;
  public $clientId;
  public $scanType;
  public $ipc;
  public $area1;
  public $area2;
  public $area3;
  public $providerId;
  public $addrFacility;
  public $datePerformed;
  public $dateUpdated;
  public $recipient;
  public $reviewed;
  public $tag1;
  public $tag2;
  public $tag3;
  //
  public function getSqlTable() {
    return 'scan_index';
  }
  public function toString() {
    return $this->getSqlInsert() . ';';
  }
  public function getEncryptedFids() {
    return array();
  }
  //
  static function from($csv, $cid, $siid) {
    $rec = new static();
    $rec->scanIndexId = $siid;
    $rec->userGroupId = Client_Import::$UGID;
    $rec->clientId = $cid;
    $rec->scanType = static::getScanType($csv->rep_cat);
    $rec->datePerformed = dateToString($csv->date_time);
    return $rec;
  }
  static function getScanType($cat) {
    switch ($cat) {
      case 'SWDF02':
        return 200;
      case 'SWDF08':
        return 201;
      case 'SWDF09':
        return 202;
      case 'SWDF10':
        return 203;
      case 'SWDF11':
        return 204;       
    }
  }
  static function getLastId() {
    $sql = "SELECT MAX(scan_index_id) FROM scan_index";
    return Dao::fetchValue($sql);
  }
}
