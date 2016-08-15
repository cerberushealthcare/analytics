<?php
require_once 'php/data/rec/sql/_ClientRec.php';
require_once 'php/data/rec/sql/_ICardRec.php';
require_once 'php/data/rec/sql/_AddressRec.php';
require_once 'php/data/rec/sql/_SchedRec.php';
//
class Client_Export extends ClientRec implements ReadOnly {
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
  public $race;
  public $ethnicity;
  public $language;
  public /*Address_Export*/$Address_Home;
  public /*Address_Export*/$Address_Emer;
  public /*Address_Export*/$Address_Parent;
  public /*Address_Export*/$Address_Spouse;
  public /*Address_Export*/$Address_Rx;
  public /*ICard_Export[]*/$ICards;
  //
  static function fetchAll($ugid) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->Address_Home = Address_Export::asJoin_Home();
    $c->Address_Emer = Address_Export::asJoin_Emer();
    $c->Address_Parent = Address_Export::asJoin_Parent();
    $c->Address_Spouse = Address_Export::asJoin_Spouse();
    $c->Address_Rx = Address_Export::asJoin_Rx();
    $c->ICards = ICard_Export::asJoin();
    return static::fetchAllBy($c, null, 0);
  }
  //
  public function getICard($i) {
    $ics = get($this, 'ICards');
    if ($ics) 
      foreach ($ics as $icard) 
        if ($icard->seq == $i)
          return $icard;
  } 
}
class Sched_Export extends SchedRec implements ReadOnly {
  //
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
  public $schedEventId;
  public /*SchedEvent_Export*/$Event;
  //
  static function fetchAll($ugid) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->Event = SchedEvent_Export::asJoin();
    return static::fetchAllBy($c, null, 0);
  }
}
class SchedEvent_Export extends SchedEventRec implements ReadOnly {
  //
  public $schedEventId;
  public $rpType;
  public $rpEvery;
  public $rpUntil;
  public $rpOn;
  public $rpBy;
  public $comment;
  //
  static function asJoin() {
    $c = new static();
    return CriteriaJoin::optional($c);
  }
}
class Address_Export extends AddressRec implements ReadOnly {
  //
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
  static function asJoin_Home() {
    return static::asJoin(static::TYPE_SHIP);
  }
  static function asJoin_Emer() {
    return static::asJoin(static::TYPE_EMER);
  }
  static function asJoin_Parent() {
    return static::asJoin(CriteriaValue::in(array(static::TYPE_MOTHER, static::TYPE_FATHER)));
  }
  static function asJoin_Spouse() {
    return static::asJoin(static::TYPE_SPOUSE);
  }
  static function asJoin_Rx() {
    return static::asJoin(static::TYPE_RX);
  }
  static function asJoin($type) {
    $c = new static();
    $c->tableCode = static::TABLE_CLIENTS;
    $c->type = $type;
    return CriteriaJoin::optional($c, 'tableId');
  }
}
class ICard_Export extends ICardRec implements ReadOnly {
  //
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
  static function asJoin() {
    $c = new static();
    return CriteriaJoin::optionalAsArray($c);
  }
}
