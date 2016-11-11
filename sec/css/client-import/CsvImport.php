<?php
set_include_path('../');
require_once "php/dao/_util.php";

/**
 * CSV Import
 * Assumes first row contains field names
 * Builds array of records associated by field name 
 */
class CsvImport {
  
  public $rows;  // CSV data rows [[label=>value,..],..]
  public $ugid;
  public $clients;
  
  /*
   * Constructor
   */
  public function __construct($filename, $ugid) {
    $this->ugid = $ugid;
    $this->rows = array();
    $this->clients = array();
    if (($handle = fopen($filename, 'r')) !== false) {
      $labels = fgetcsv($handle, 1000, ',');
      while (($values = fgetcsv($handle, 1000, ',')) !== false) {
        $this->rows[] = array_combine($labels, $values); 
      }
      fclose($handle);
    }
  }
  public function addClient($uid, $lastName, $firstName, $sex, $birth, $comment, $addr1, $addr2, $city, $state, $zip, $homePhone, $workPhone, $cellPhone, $email) {
    if ($uid == null) {
      echo "WARNING: uid is null for $lastName<br>";
      $uid = $lastName;
    }
    $this->clients[] = array(
        'uid' => $uid,
        'lastName' => $lastName,
        'firstName' => $firstName,
        'sex' => $sex,
        'birth' => $birth,
        'comment' => $comment,
        'addr1' => $addr1,
        'addr2' => $addr2,
        'city' => $city,
        'state' => $state,
        'zip' => $zip,
        'homePhone' => $homePhone,
        'workPhone' => $workPhone,
        'cellPhone' => $cellPhone,
        'email' => $email);
  }
  /*
   * Returns UID of last client inserted
   */
  public function writeClients($startAfterUid = '', $max = 100) {   
    $found = ($startAfterUid == '');
    $ct = 0;
    foreach ($this->clients as $c) {
      if ($found) {
        $values = CsvImport::sqlValues(array(
            $this->ugid,
            $c['uid'],
            $c['lastName'],
            $c['firstName'],
            $c['sex'],
            $c['birth'],
            1,
            $c['comment']));
        $sql = <<<eos
INSERT INTO clients (USER_GROUP_ID,UID,LAST_NAME,FIRST_NAME,SEX,BIRTH,ACTIVE,CDATA9) 
VALUES($values)
eos;
        $cid = insert($sql);
        echo "<br>Added $c[uid] $c[lastName] CLIENT ";
        $values = CsvImport::sqlValues(array(
            'C',
            $cid,
            0,
            $c['addr1'],
            $c['addr2'],
            $c['city'],
            $c['state'],
            $c['zip'],
            $c['homePhone'],
            0,
            $c['workPhone'],
            1,
            $c['cellPhone'],
            2,
            $c['email'])); 
        $sql = <<<eos
INSERT INTO addresses (TABLE_CODE,TABLE_ID,TYPE,ADDR1,ADDR2,CITY,STATE,ZIP,PHONE1,PHONE1_TYPE,PHONE2,PHONE2_TYPE,PHONE3,PHONE3_TYPE,EMAIL1)
VALUES($values)
eos;
        insert($sql);      
        echo "ADDRESS ";
        insert("INSERT INTO client_updates VALUES($cid,NULL,0,NULL,NULL)");
        echo "CLIENT_UPDATES";
        $ct++;
        if ($ct >= $max) {
          return $c['uid'];
        }
      } else {
        $found = ($c['uid'] == $startAfterUid);
      }
    }
  }
  private static function sqlValues($row) {
    $v = array();
    foreach ($row as $value) {
      $v[] = CsvImport::sqlValue($value);  
    }
    return implode($v, ',');
  }
  private static function sqlValue($value, $addSlashes = true) {
    if (is_null($value)) {
      return "null";
    }
    if ($addSlashes) {
      $value = addSlashes($value);
    }
    return "'$value'";
  }
}
?>