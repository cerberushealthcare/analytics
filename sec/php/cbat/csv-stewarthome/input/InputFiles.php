<?php
require_once 'php/data/_BasicRec.php';
require_once 'php/data/file/_CsvFile.php';
//
class CsvRec_SH extends CsvRec {
  //
  public function set($fid, $value) {
    if ($value == 'NULL')
      $value = null;
    parent::set($fid, $value);
  } 
}
class ActivePatient extends CsvRec_SH {
  //
  static function asClients($us, $ugid) {
    $recs = array();
    foreach ($us as $me) 
      $recs[] = $me->asClient($ugid);
    return $recs; 
  }
  static function all() {
    $us = ActivePatientsFile::fetchMap();
    $gendemos = GenDemoFile::fetchMap();
    $uddemos = UserDefDemoFile::fetchMap();
    $idemos = InsuranceDemoFile::fetchMap();
    foreach ($us as $pid => $me) {
      $me->Gen = geta($gendemos, $pid);
      $me->UserDef = geta($uddemos, $pid);
      $me->Insurance = geta($idemos, $pid);
    }    
    return $us;
  }
  //
  public $ID;
  public $Patient_ID;
  public $warning;
  public $name;
  public $chartnum;
  public $Doctor;
  public $DoctorName;
  public $v_date;
  public $status;
  public $InUse;
  public $InUseBy;
  public $InUseStation;
  public $InUseDateTime;
  public $LastModified;
  //
  public /*GenDemo*/$Gen;
  public /*UserDefDemo*/$UserDef;
  public /*InsuranceDemo*/$Insurance;
  //
  public function asClient($ugid) {
    $gen = $this->Gen;
    $ud = $this->UserDef;
    $notes = $ud ? $ud->asNotes() : null;
    $client = Client_Import::from($this,
      $ugid,
      null,
      $gen->last_name,
      $gen->first_name,
      $gen->mid_init,
      $gen->sex,
      dateToString($gen->birthdate),
      $gen->SocialSec,
      $this->chartnum,
      $this->DoctorName,
      $notes);
    $client->Address_Home = Address_Import::from(
      $gen->street,
      str_replace('~', ' ', $gen->street2),
      null,
      $gen->city,
      substr($gen->state, 0, 2),
      $gen->zip,
      $gen->home_phone,
      $gen->work_phone,
      $gen->email);
    if (! empty($this->Insurance)) {
      foreach ($this->Insurance as $i => $ins) {
        $fid = 'ICard' . ($i + 1);
        $client->$fid = ICard_Import::from(
          $ins->Seq_Num,
          $ins->Insur_Comp_Name,
          $ins->Name_Insur_Under,
          $ins->Insur_Contact_Per,
          $ins->Policy_Number,
          null);  // missing Insur_Comp_Phone, Expiration_Date, Comments
      }
    }
    return $client;
  }
}
class GenDemo extends CsvRec_SH {
  //
  public $patient_id;
  public $last_name;
  public $first_name;
  public $mid_init;
  public $title;
  public $suffix;
  public $street;
  public $street2;
  public $city;
  public $state;
  public $zip;
  public $home_phone;
  public $work_phone;
  public $email;
  public $sex;
  public $birthdate;
  public $SocialSec;
  public $pri_physician;
  public $DateDeactivated;
  public $IsActive; 
}
class UserDefDemo extends CsvRec_SH {
  //
  public $Patient_ID;
  public $Custom1;
  public $Custom2;
  public $Custom3;
  public $Custom4;
  public $Custom5;
  public $Custom6;
  public $Custom7;
  public $Custom8;
  public $Custom9;
  public $Custom10;
  public $Custom11;
  public $Custom12;
  public $Custom13;
  //
  public function asNotes() {
    $this->Custom13 = str_replace('~', '<br/>', $this->Custom13);
    $a = array();
    for ($i = 1; $i <= 13; $i++) {
      $fid = "Custom$i";
      if (! empty($this->$fid)) 
        $a[] = $this->$fid;
    }
    return substr(implode('<br/>', $a), 0, 400);
  }
}
class InsuranceDemo extends CsvRec_SH {
  //
  public $Patient_ID;
  public $Seq_Num;
  public $Policy_Number;
  public $Insur_Comp_Name;
  public $Insur_Contact_Per;
  public $Insur_Comp_Phone;
  public $Expiration_Date;
  public $Name_Insur_Under;
  public $Comments;
}
/** Files */
class CsvFile_SH extends CsvFile {
  //
  static $HAS_FID_ROW = true;
  //
  static function fetchMap($fid, $singles = true) {
    $me = static::fetch();
    $map = array();
    foreach ($me->recs as $rec) {
      if ($singles) {
        $map[$rec->$fid] = $rec;
      } else {
        if (! isset($map[$rec->$fid]))
          $map[$rec->$fid] = array();
        $map[$rec->$fid][] = $rec;
      }
    }
    return $map;
  }
}
class ActivePatientsFile extends CsvFile_SH {
  //
  static $FILENAME = 'active_patients.csv';
  static $CSVREC_CLASS = 'ActivePatient';
  //
  static function fetchMap() {
    return parent::fetchMap('Patient_ID');
  }
}
class GenDemoFile extends CsvFile_SH {
  //
  static $FILENAME = 'gen_demo.csv';
  static $CSVREC_CLASS = 'GenDemo';
  //
  static function fetchMap() {
    return parent::fetchMap('patient_id');
  }
}
class UserDefDemoFile extends CsvFile_SH {
  //
  static $FILENAME = 'user_def_demo.csv';
  static $CSVREC_CLASS = 'UserDefDemo';
  //
  static function fetchMap() {
    return parent::fetchMap('Patient_ID');
  }
}
class InsuranceDemoFile extends CsvFile_SH {
  //
  static $FILENAME = 'insurance_demo.csv';
  static $CSVREC_CLASS = 'InsuranceDemo';
  //
  static function fetchMap() {
    return parent::fetchMap('Patient_ID', false);
  }
}
