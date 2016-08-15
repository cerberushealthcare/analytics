<?php
require_once "php/linkpoint/lphp.php";
//
class LinkPoint {
  //
  public $data;
  //
  public function setCard($name, $comments, $oid, $number, $expMonth, $expYear) {
		$this->data["name"] = $name;
		$this->data["comments"] = $comments;
		$this->data["oid"] = $oid;
		$this->data["cardnumber"] = $number;
		$this->data["cardexpmonth"] = $expMonth;
		$this->data["cardexpyear"] = substr($expYear, -2);
		return $this;
  }
  public function setAddress($address1, $city, $state, $zip) {
		$nums = "0123456789";
		$nonnum = strcspn($address1, $nums);
		$this->data["addrnum"] = substr($address1, $nonnum, strspn($address1, $nums, $nonnum));
		$this->data["address1"] = $address1;
		$this->data["city"] = $city;
		$this->data["state"] = $state;
		$this->data["zip"] = $zip;
  }
  public function setAmt_asPreAuth($amt) {
    return $this->setAmt($amt, true);
  }
  public function setAmt_asCharge($amt) {
    return $this->setAmt($amt, false);
  }
  public function transmit() {  /* @return Lp_Response */  
    $link = new lphp;
    $result = $link->curl_process($this->data);
    return Lp_Response::from($result, $this->isPreauth());
  }
  //
  protected function isPreauth() {
    return $this->data["ordertype"] == "PREAUTH";
  }
  protected function setAmt($amt, $asPreAuth) {
		$this->data["ordertype"] = ($asPreAuth) ? "PREAUTH" : "POSTAUTH";
		$this->data["chargetotal"] = number_format($amt, 2);
		$this->data["result"] = "LIVE";
		return $this;
  }
  protected function setLogin() {
		$this->data["host"] = "secure.linkpt.net";
		$this->data["port"] = "1129";
		$this->data["keyfile"] = "\\www\\clicktate\\sec\\1001174271.pem";
		$this->data["configfile"] = "1001174271";
		return $this;
  }
  //
  static function create() {
    $me = new static();
    $me->data = array();
    $me->setHeader();
    return $me;
  }
}
class Lp_Response {
  //
  public $time;
  public $ref;
  public $error;
  public $ordernum;
  public $message;
  public $code;
  public $tdate;
  public $authresponse;
  public $approved;
  public $avs1;
  public $avs2;
  public $avs3;
  public $_type;
  public $_asPreauth;
  //
  const AVS_OK = 1;
  const AVS_BAD_A = -10;
  const AVS_BAD_Z = -11;
  const AVS_BAD_AZ = -12;
  const AVS_NOT_FOUND = -13;
  const AVS_RETRY = -99;
  static $AVS = array(
    static::AVS_OK => 'OK',
    static::AVS_BAD_A => 'BAD ADDR',
    static::AVS_BAD_Z => 'BAD ZIP',
    static::AVS_BAD_AZ => 'BAD ADDR & ZIP',
    static::AVS_NOT_FOUND => 'NOT FOUND',
    static::AVS_RETRY => 'RETRY');
  //
  public function isGood() {
    return $this->isApproved() && $this->isAvsOk();
  }
  public function isBad() {
    return ! $this->isGood();
  }
  public function isApproved() {
    return $this->approved == 'APPROVED';
  }
  public function isAvsOk() {
    return $this->getAvsResult() == static::AVS_OK;
  }
  public function isPreauth() {
    return $this->_asPreauth;
  }
  public function getAvsResultText() {
    $result = $this->getAvsResult();
    return static::$AVS[$result];
  }
  public function getAvsResult() {
    if ($this->avs1 == 'Y' && $this->avs2 == 'Y')
      return static::AVS_OK;
    if ($this->avs1 == 'N' && $this->avs2 == 'Y')
      return static::AVS_BAD_A;
    if ($this->avs1 == 'N' && $this->avs2 == 'Y')
      return static::AVS_BAD_Z;
    if ($this->avs1 == 'N' && $this->avs2 == 'N')
      return static::AVS_BAD_AZ;
    if ($this->avs1 == 'X' && $this->avs2 == 'X' && $this->avs3 == 'R')
      return static::AVS_RETRY;
    return static::AVS_NOT_FOUND;
  } 
  //
  protected function parse($r) {
    $this->_response = $r;
    $this->time = $r['r_time'];
    $this->ref = $r['r_ref'];
    $this->error = $r['r_error'];
    $this->ordernum = $r['r_ordernum'];
    $this->message = $r['r_message'];
    $this->code = $r['r_code'];
    $this->tdate = $r['r_tdate'];
    $this->authresponse = $r['r_authresponse'];
    $this->approved = $r['r_approved'];
    $this->avs1 = substr($r['avs'], 0, 1);
    $this->avs2 = substr($r['avs'], 1, 1);
    $this->avs3 = substr($r['avs'], 2, 1);
  }
  //
  static function from($result, $asPreauth) {
    $me = new static();
    $me->parse($result);
    $me->_asPreauth = $asPreauth;
    return $me;
  }
}
