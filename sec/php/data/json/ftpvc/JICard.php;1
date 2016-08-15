<?php
require_once "php/data/json/_util.php";

class JICard {

  public $clientId;
  public $seq;
  public $planName;
  public $subscriberName;
  public $nameOnCard;
  public $groupNo;
  public $subscriberNo;
  public $dateEffective;
  public $active;

  const SEQ_PRIMARY = 1;
  const SEQ_SECONDARY = 2;
  
  const SQL_FIELDS = "client_id, seq, plan_name, subscriber_name, name_on_card, group_no, subscriber_no, date_effective, active";
  
  public function __construct($clientId, $seq, $planName, $subscriberName, $nameOnCard, $groupNo, $subscriberNo, $dateEffective, $active) {
    $this->clientId = $clientId;
    $this->seq = $seq;
    $this->planName = $planName;
    $this->subscriberName = $subscriberName;
    $this->nameOnCard = $nameOnCard;
    $this->groupNo = $groupNo;
    $this->subscriberNo = $subscriberNo;
    $this->dateEffective = $dateEffective;
    $this->active = $active;
  }
	public function out() {
    $out = "";
    $out = nqq($out, "clientId", $this->clientId);
    $out = nqq($out, "seq", $this->seq);
    $out = nqq($out, "planName", $this->planName);
    $out = nqq($out, "subscriberName", $this->subscriberName);
    $out = nqq($out, "nameOnCard", $this->nameOnCard);
    $out = nqq($out, "groupNo", $this->groupNo);
    $out = nqq($out, "subscriberNo", $this->subscriberNo);
    $out = nqq($out, "dateEffective", formatDate($this->dateEffective));
    $out = nqqo($out, "active", $this->active);
    return cb($out);
	}
}
?>