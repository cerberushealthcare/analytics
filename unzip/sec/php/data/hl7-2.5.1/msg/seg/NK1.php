<?php
require_once 'php/data/hl7-2.5.1/msg/seg/_HL7Segment.php';   
//
/**
 * Next of Kin v2.5.1
 * @author Warren Hornsby
 */
class NK1 extends HL7Segment {
  //
  public $segId = 'NK1';
  public $seq;  // 1: Set ID - NK1 (SI)
  public $name = 'XPN';  // 2: NK Name (XPN)
  public $relation = 'CE';  // 3: Relationship (CE)
  public $address = 'XAD';  // 4: Address (XAD)
  public $phone = 'XTN';  // 5: Phone Number (XTN)
  public $busPhone = 'XTN';  // 6: Business Phone Number (XTN)
  public $contactRole = 'CE';  // 7: Contact Role (CE)
  public $start;  // 8: Start Date (DT)
  public $end;  // 9: End Date (DT)
  public $jobTitle;  // 10: Next of Kin / Associated Parties Job Title (ST)
  public $jobCode;  // 11: Next of Kin / Associated Parties Job Code/Class (JCC)
  public $empNo;  // 12: Next of Kin / Associated Parties Employee Number (CX)
  public $orgName = 'XON';  // 13: Organization Name - NK1 (XON)
  public $marital = 'CE';  // 14: Marital Status (CE)
  public $sex;  // 15: Administrative Sex (IS)
  public $birth;  // 16: Date/Time of Birth (TS)
  public $dependency;  // 17: Living Dependency (IS)
  public $ambulatory;  // 18: Ambulatory Status (IS)
  public $citizen = 'CE';  // 19: Citizenship (CE)
  public $language = 'CE';  // 20: Primary Language (CE)
  public $livingArrange;  // 21: Living Arrangement (IS)
  public $publicity = 'CE';  // 22: Publicity Code (CE)
  public $protection;  // 23: Protection Indicator (ID)
  public $student;  // 24: Student Indicator (IS)
  public $religion = 'CE';  // 25: Religion (CE)
  public $mothersMaiden;  // 26: Mother's Maiden Name (XPN)
  public $nationality = 'CE';  // 27: Nationality (CE)
  public $ethnic = 'CE';  // 28: Ethnic Group (CE)
  public $contactReason = 'CE';  // 29: Contact Reason (CE)
  public $contactName = 'XPN';  // 30: Contact Person's Name (XPN)
  public $contactPhone = 'XTN';  // 31: Contact Person's Telephone Number (XTN)
  public $contactAddress = 'XAD';  // 32: Contact Person's Address (XAD)
  public $assocId;  // 33: Next of Kin/Associated Party's Identifiers (CX)
  public $jobStatus;  // 34: Job Status (IS)
  public $race = 'CE';  // 35: Race (CE)
  public $handicap;  // 36: Handicap (IS)
  public $contactSsn;  // 37: Contact Person Social Security Number (ST)
  public $birthplace;  // 38: Next of Kin Birth Place (ST)
  public $vip;  // 39: VIP Indicator (IS)
  //
  static function all(/*Facesheet*/$fs) {
    $us = array();
    $seq = 1;
    static::appendMother($fs, $us, $seq);
    static::appendFather($fs, $us, $seq);
    static::appendSpouse($fs, $us, $seq);
    return $us;
  }
  protected static function appendFather($fs, &$us, &$seq) {
    static::append($fs->Client->Address_Father, CE_Relation::asFather(), $us, $seq);
  }
  protected static function appendMother($fs, &$us, &$seq) {
    static::append($fs->Client->Address_Mother, CE_Relation::asMother(), $us, $seq);
  }
  protected static function appendSpouse($fs, &$us, &$seq) {
    static::append($fs->Client->Address_Spouse, CE_Relation::asSpouse(), $us, $seq);
  }
  protected static function append($addr, $relation, &$us, &$seq) {
    if (! $addr->isEmpty()) {
      $us[] = static::from($addr, $relation, $seq);
    }
  }
  protected static function from($addr, $relation, &$seq) {
    $me = static::asEmpty();
    $me->seq = $seq++;
    $me->name = XPN::fromAddress_asLegal($addr);
    $me->relation = $relation;
    $me->address = XAD::asLegal($addr);
    $me->phone = XTN::fromAddress($addr);
    return $me;
  }
}
