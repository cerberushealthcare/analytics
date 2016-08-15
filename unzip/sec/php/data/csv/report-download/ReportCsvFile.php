<?php
require_once 'php/data/csv/_CsvFile.php';
//
/**
 * Report Download
 * @author Warren Hornsby
 */
class ReportCsvFile extends CsvFile {
  //
  /**
   * @param ReportCriteria $report
   * @param SqlRec_Rep[] $recs
   * @return ReportFile 
   */
  static function from($report, $recs) {
    global $login;
    $file = new static();
    $file->setFilename($report->name);
    if ($report->isAudit()) {
      $file->recs = ReportCsvRec_Audit::from($recs);
    } else {
      if ($login->super) { 
        $file->recs = ReportCsvRec_Patient_Super::from($recs);
      } else {
        $file->recs = ReportCsvRec_Patient::from($recs);
      }
    }
    //logit_r($report, 'csv report');
    //logit_r($file->recs, 'file recs');
    return $file;
  }
  static function asAllDueNow($recs) {
    $file = new static();
    $file->setFilename('Due Now Report');
    $file->recs = ReportCsvRec_DueNow::from($recs);
    return $file;
  }
}
abstract class ReportCsvRec extends CsvRec {
  //
  /*abstract static function fromRec($rec);*/
  abstract static function asHeader($recs);
  //
  static function from($recs) {
    $lines = array(static::asHeader($recs));
    foreach ($recs as $rec) {
      static::appendRec($lines, $rec);
    }
    logit_r($lines, 'lines');
    return $lines;
  }
  //
  protected function appendRec(&$lines, $rec) {
    $lines[] = static::fromRec($rec);
  }
  protected static function getJoinFids($rec) {
    static $fids;
    if ($fids == null) {
      $fids = array();
      foreach ($rec as $fid => $value) {
        if (substr($fid, 0, 4) == 'Join') {
          $join = current($rec->$fid);
          if ($join instanceof ReportRec_CsvJoinable)
            $fids[] = $fid;
        }
      }
    }
    return $fids;
  }
  protected function addJoinHeaders($rec) {
    $fids = static::getJoinFids($rec);
    foreach ($fids as $fid) {
      if (isset($rec->$fid)) {
        $data = current($rec->$fid);
        $this->$fid = strtoupper($data->getTableName());
        if ($this->$fid == 'ADDRESS')
          $this->_Phone = 'PHONE';
      }
    }
  }
  protected function addJoinValues($rec) {
    $fids = static::getJoinFids($rec);
    foreach ($fids as $fid) 
      $this->addJoin($fid, $rec->$fid);
  }
  protected function addJoin($fid, $recs) {
    $labels = array();
    foreach ($recs as $rec) {
      if ($rec) 
        $labels[] = $rec->formatLabel();
    }
    $this->$fid = implode(' - ', $labels);
    if ($rec && $rec->getTableName() == 'Address')
      $this->_Phone = $rec->phone1;
  }
}
class ReportCsvRec_Patient extends ReportCsvRec {
  //
  public $uid;
  public $lastName;
  public $firstName;
  public $middleName;
  public $sex;
  public $birth;
  public $age;
  public $language;
  public $release;
  //
  protected function appendRec(&$lines, $rec) {
    $jfids = static::getJoinFids($rec);
    $max = 0;
    foreach ($jfids as $jfid) {
      if (count($rec->$jfid) > $max) {
        $max = count($rec->$jfid);
      }      
    }
    logit_r($max, 'max');
    if ($max == 0) {
      $lines[] = static::fromRec($rec);
    } else {
      for ($i = 0; $i < $max; $i++) {
        $lines[] = static::fromRec($rec, $i);
      }
    }
  }
  static function fromRec($rec, $i = null) {
    $me = new static();
    $me->setFrom($rec, $i);
    logit_r($me, 'fromRec');
    return $me;
  }
  static function asHeader($recs) {
    $me = new static();
    $me->addHeaders();
    $me->addJoinHeaders(current($recs));
    return $me;
  }
  static function setLanguage() {
  }
  //
  protected function setFrom($rec, $i) {
    $this->uid = $rec->uid;
    $this->lastName = $rec->lastName;
    $this->firstName = $rec->firstName;
    $this->middleName = get($rec, 'middleName');
    $this->sex = $rec->sex;
    $this->birth = $rec->birth;
    $this->language = IsoLang::getEngName(get($rec, 'language'));
    $this->setRelease($rec);
    $this->addJoinValues($rec, $i);
  }
  protected function addHeaders() {
    $this->uid = 'UID';
    $this->lastName = 'LAST_NAME';
    $this->firstName = 'FIRST_NAME';
    $this->middleName = 'MIDDLE';
    $this->sex = 'GENDER';
    $this->birth = 'BIRTH';
    $this->language = 'LANGUAGE';
    $this->release = 'RELEASE PREF';
  }
  protected static function getJoinFids($rec) {
    static $fids;
    if ($fids == null) {
      $fids = array();
      foreach ($rec as $fid => $value) {
        if (substr($fid, 0, 4) == 'Join' && ! static::isAddressJoin($fid)) {
          $join = current($rec->$fid);
          if ($join instanceof ReportRec_CsvJoinable)
            $fids[] = $fid;
        }
      }
    }
    return $fids;
  }
  protected function addJoinHeaders($rec) {
    $fids = static::getJoinFids($rec);
    foreach ($fids as $fid) {
      if (isset($rec->$fid)) {
        $data = current($rec->$fid);
        $this->$fid = strtoupper($data->getTableName());
      }
    }
    $this->_Address = 'ADDRESS';
    $this->_Phone = 'PHONE';
  }
  protected function addJoinValues($rec, $i) {
    $fids = static::getJoinFids($rec);
    foreach ($fids as $fid) {
      $jrecs = $rec->$fid;
      $this->addJoin($fid, array(geta($jrecs, $i)));
    }
    $this->addAddressJoin($rec);
  }
  protected function addAddressJoin($rec) {
    $fid = $this->getAddressJoin($rec);
    if (isset($rec->$fid)) {
      $this->addJoin('_Address', $rec->$fid);
    } else {
      $this->_Address = null;
      $this->_Phone = null;
    }
  }
  protected function getAddressJoin($rec) {
    foreach ($rec as $fid => $value) 
      if (static::isAddressJoin($fid))
        return $fid;
  }
  protected function isAddressJoin($fid) {
    return (substr($fid, 0, 4) == 'Join' && substr($fid, -8) == '_tableId');
  }
  protected function setRelease($rec) {
    $pref = get($rec, 'releasePref');
    if ($pref) {
      $release = $rec::$RELEASE_PREFS[$pref];
      if ($rec->release) 
        $release .= ': ' . implode(' ', split_crlf($rec->release));
      $this->release = $release;
    } else {
      $this->release = 'No Preference';
    }
  }
}
class ReportCsvRec_Patient_Super extends ReportCsvRec_Patient {
  //
  public $practice;
  public $uid;
  public $lastName;
  public $firstName;
  public $middleName;
  public $sex;
  public $birth;
  public $age;
  public $language;
  public $release;
  //
  static function fromRec($rec) {
    $me = new static();
    $me->practice = getr($rec, 'UserGroup.name');
    $me->setFrom($rec);
    return $me;
  }
  static function asHeader($recs) {
    $me = new static();
    $me->addHeaders();
    $me->addJoinHeaders(current($recs));
    return $me;
  }
  //
  protected function addHeaders() {
    $this->practice = 'PRACTICE';
    parent::addHeaders();
  }
}
class ReportCsvRec_DueNow extends ReportCsvRec_Patient {
  // 
  //public $_ipc;
  public $uid;
  public $lastName;
  public $firstName;
  public $middleName;
  public $sex;
  public $birth;
  public $age;
  public $language;
  public $release;
  //
  protected function addJoinHeaders($rec) {
    //$this->_ipc = 'TEST/PROCEDURE';
    $this->_Address = 'ADDRESS';
    $this->_Phone = 'PHONE';
  }
  protected function addJoinValues($rec) {
    //$this->_ipc = $rec->ipc_;
    $this->addAddressJoin($rec);
  }
}
class ReportCsvRec_Audit extends ReportCsvRec {
  //
  public $action;
  public $record;
  public $date;
  public $user;
  public $patient;
  //
  static function fromRec($rec) {
    $me = new static();
    $me->action = $rec->formatAction();
    $me->record = $rec->formatNameLabel();
    $me->date = formatDateTime($rec->date);
    $me->user = $rec->User->name;
    $me->patient = $rec->Client->getFullName();
    $me->addJoinValues($rec);
    return $me;
  }
  static function asHeader($recs) {
    $me = new static();
    $me->action = 'Action';
    $me->record = 'Record';
    $me->date = 'Date';
    $me->user = 'User';
    $me->patient = 'Patient';
    $me->addJoinHeaders(current($recs));
    return $me;
  }
  //
  protected function setRelease($rec) {
    if ($rec->releasePref) {
      $release = $rec::$RELEASE_PREFS[$rec->releasePref];
      if ($rec->release) 
        $release .= ': ' . implode(' ', split_crlf($rec->release));
      $this->release = $release;
    } else {
      $this->release = 'No Preference';
    }
  }
}
