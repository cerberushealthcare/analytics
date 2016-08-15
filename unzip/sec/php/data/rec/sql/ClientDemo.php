<?php
require_once 'php/data/rec/sql/_SqlRec.php';
require_once 'php/data/rec/sql/Client.php';
/**
 * Client Demographics
 * Includes: ICard
 */
class ClientDemo extends Client {
  //
  public /*Address*/ $AddressHome;
  public /*Address*/ $AddressEmergency;
  public /*Address*/ $AddressSpouse;
  public /*Address*/ $AddressFather;
  public /*Address*/ $AddressMother;
  public /*Address*/ $AddressRx;
  public /*[ICard]*/ $ICards;
  //
  /**
   * @return array(type=>Address,..)  // All types returned, even if null
   */
  public function getAddresses() {
    return array(
      Address::TYPE_SHIP => get($this, 'AddressHome'),
      Address::TYPE_EMER => get($this, 'AddressEmergency'),
      Address::TYPE_SPOUSE => get($this, 'AddressSpouse'),
      Address::TYPE_FATHER => get($this, 'AddressFather'),
      Address::TYPE_MOTHER => get($this, 'AddressMother'),
      Address::TYPE_RX => get($this, 'addressRx')
      );
  }
  //
  /**
   * Static fetchers
   */
  public static function fetch($cid) {
    $rec = Client::fetch($cid);
    ClientDemo::appendFields($rec);
    return $rec;
  }
  public static function fetchByUid($uid) {
    $rec = Client::fetchByUid($uid);
    ClientDemo::appendFields($rec);
  }
  private static function appendFields(&$rec) {
    $cid = $rec->clientId;
    $rec->AddressHome = Address::fetchByClient($cid, Address::TYPE_SHIP); 
    $rec->AddressEmergency = Address::fetchByClient($cid, Address::TYPE_EMER); 
    $rec->AddressSpouse = Address::fetchByClient($cid, Address::TYPE_SPOUSE); 
    $rec->AddressFather = Address::fetchByClient($cid, Address::TYPE_FATHER);
    $rec->AddressMother = Address::fetchByClient($cid, Address::TYPE_MOTHER);
    $rec->AddressRx = Address::fetchByClient($cid, Address::TYPE_RX);
    $rec->ICards = ICard::fetchAllByClient($cid);
  }
  //
  /**
   * TODO... should be an AdminDAO function
   * 
   * Merge client records (correct and dupe) 
   * Correct client will absorb dupe client data; dupe will be deactiated
   * @param int $cid of correct record
   * @param int $cidDupe of dupe record
   */
  public static function merge($cid, $cidDupe) {
    query("UPDATE sessions SET client_id=$cid WHERE client_id=$cidDupe");
    query("UPDATE scheds SET client_id=$cid WHERE client_id=$cidDupe");
    query("UPDATE data_allergies SET client_id=$cid WHERE client_id=$cidDupe AND source IS NULL");
    query("UPDATE data_diagnoses SET client_id=$cid WHERE client_id=$cidDupe");
    query("UPDATE data_hm SET client_id=$cid WHERE client_id=$cidDupe AND session_id=0 AND active=1");
    query("UPDATE data_immuns SET client_id=$cid WHERE client_id=$cidDupe");
    //query("UPDATE data_immun_vacs SET client_id=$cid WHERE client_id=$cidDupe");
    query("UPDATE data_meds SET client_id=$cid WHERE client_id=$cidDupe AND source IS NULL");
    query("UPDATE data_vitals SET client_id=$cid WHERE client_id=$cidDupe");
    query("UPDATE clients SET user_group_id=0 WHERE client_id=$cidDupe");
    Client::mergeAddresses($cid, $cidDupe);
  }
  private static function mergeAddresses($cidt, $cidDupe) {
    $client = ClientDemo::fetch($cid);
    $clientDupe = ClientDemo::fetch($cidDupe);
    $adds = $client->getAddresses();
    $addsDupe = $clientDupe->getAddresses();
    foreach ($adds as $type => $add) {
      $addDupe = $addsDupe[$type];
      if ($add == null && $addDupe) {
        $addDupe->tableId = $client->clientId;
        $addDupe->save();
      }
    }
    if (count($client->ICards) == 0)
      query("UPDATE client_icards SET client_id=$client->clientId WHERE client_id=$clientDupe->clientId");
  }
}
/**
 * Client Insurance Card
 */
class ICard extends SqlRec {
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
  const SEQ_PRIMARY = '1';
  const SEQ_SECONDARY = '2';
  public static $SEQS = array(
    ICard::SEQ_PRIMARY => 'Primary',
    ICard::SEQ_SECONDARY => "Secondary");
  //
  public function getSqlTable() {
    return 'client_icards';
  }
  //
  /**
   * Static fetchers
   */
  public static function fetchAllByClient($clientId) {
    $rec = new ICard($clientId);
    return SqlRec::fetchAllBy($rec);
  }
}
?>