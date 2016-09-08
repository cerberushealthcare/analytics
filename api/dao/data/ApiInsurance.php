<?php
require_once 'Api.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/analytics/sec/php/data/rec/sql/_SqlRec.php';
/**
 * Insurance
 */
class ApiInsurance extends Api {
  //
  public $plan;
  public $group;
  public $policy;
  public $subscriber;
  public $nameCard;
  public $dateEff;
  public $note;
  public $active;
  /**
   * Constructor
   * @param ['field'=>value,..] $data
   * @param(opt) string $prefix
   */
  public function __construct($data, $prefix = null) {
    $this->load($data, Api::NONE_REQUIRED, $prefix);
    $this->active = true;
  }
  /**
   * Static constructor from CLIENT_ICARDS
   * $param JICard $icard
   * @return ApiInsurance
   */
  public static function fromICard($icard) {
    if ($icard) {
      $data = array(
        'plan' => $icard->planName,
        'group' => $icard->groupNo,
        'policy' => $icard->subscriberNo,
        'subscriber' => $icard->subscriberName,
        'nameCard' => $icard->nameOnCard,
        'dateEff' => Data::ymd($icard->dateEffective),
        'note' => '',
        'active' => Data::bool($icard->active));
      return new ApiInsurance($data);
    }
  }
}
class ICard_Api extends SqlRec implements CompositePk {
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
  //
  public function getSqlTable() {
    return 'client_icards';
  }
  public function getAuditRecId() {
    return "$this->clientId,$this->seq";
  }
  public function getPkFieldCount() {
    return 2;
  }
  //
  static function from(/*ApiInsurance*/$api, $cid, $seq, $active) {
    if ($api)
      return new static(
        $cid,
        $seq,
        $api->plan,
        $api->subscriber,
        $api->nameCard,
        $api->group,
        $api->policy,
        Data::ymd($api->dateEff),
        $api->active);
  }
  static function from_asPrimary($api, $cid, $active) {
    return static::from($api, $cid, static::SEQ_PRIMARY, $active);
  }
  static function from_asSecondary($api, $cid, $active) {
    return static::from($api, $cid, static::SEQ_SECONDARY, $active);
  }
  static function fetchAllByClient($cid) {
    return array(
      static::fetchByClientSeq($cid, ICard::SEQ_PRIMARY),
      static::fetchByClientSeq($cid, ICard::SEQ_SECONDARY));
  }
  //
  private static function fetchByClientSeq($cid, $seq) {
    $c = new static($cid, $seq);
    $rec = static::fetchOneBy($c);
    if ($rec)
      return $rec;
    else
      return new static($cid, $seq);
  }
}
