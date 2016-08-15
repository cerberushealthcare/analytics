<?php
require_once "php/dao/_util.php";
require_once "php/dao/_exceptions.php";
require_once "php/dao/_exceptions.php";
require_once "php/data/db/BillInfo.php";
require_once "php/data/db/BillCode.php";
require_once 'php/data/LoginSession.php';
require_once 'php/data/rec/cryptastic.php';

/*
 * Billing data access
 */
class BillingDao {

  public function getBillInfo($userId, $withBillCode = true) {
    //LoginDao::authenticateUserId($userId);
    $billInfo = BillingDao::buildBillInfo(fetch(
        "SELECT user_id, name, address1, address2, city, state, zip, country, card_number, exp_month, exp_year, next_bill_date, balance, phone_num, phone_ext, card_type, start_bill_date, bill_code, last_bill_status" .
        " FROM billinfo" .
        " WHERE user_id=" . $userId));
    
    if ($billInfo != null) {
      $billInfo->cardNumber = MyCrypt_Auto::decrypt($billInfo->cardNumber);
      if ($withBillCode) {
        $billInfo->billCode = BillingDao::getBillCode($billInfo->billCodeId);
      }
    }
    return $billInfo;
  }

  public function getBillInfosForNewSignups() {
  }

  public function getBillCode($billCode) {
    return BillingDao::buildBillCode(fetch(
        "SELECT bill_code, new_signups, upfront_charge, monthly_charge, min_charge, max_charge, register_text, create_date, discount_code, first_bill " .
        " FROM bill_codes" .
        " WHERE bill_code=" . $billCode));
  }

  public static function chargeUser($bill) {

    $link = new lphp;

    $ckchg["host"] = "secure.linkpt.net";
    $ckchg["port"] = "1129";
    $ckchg["keyfile"] = "\\www\\clicktate\\sec\\1001174271.pem";
    $ckchg["configfile"] = "1001174271";

    $ckchg["name"] = $bill->name;
    $ckchg["comments"] = "For user: " . $bill->unot;
    $ckchg["oid"] = "U-" . $bill->unot . "-D-" . date("Y-m-d-H:i:s",strtotime("now"));
    $ckchg["cardnumber"] = $bill->card_number;
    $ckchg["cardexpmonth"] = $bill->exp_month;
    $ckchg["cardexpyear"] = substr($bill->exp_year,2,2);

    // Street number and zip code are needed for AVS (address verification)
    $nums = "0123456789";
    $nonnum = strcspn($bill->address1, $nums);
    $ckchg["addrnum"] = substr($bill->address1, $nonnum, strspn($bill->address1, $nums, $nonnum));
    $ckchg["address1"] = $bill->address1;
    $ckchg["city"] = $bill->city;
    $ckchg["state"] = $bill->state;
    $ckchg["zip"] = $bill->zip;

    // See if pre-authorization works
    $ckchg["ordertype"] = "PREAUTH";
    if ($bill->balance > 0) {
      $ckchg["chargetotal"] = number_format($bill->balance, 2);
    } else {
      $ckchg["chargetotal"] = "39.00";
    }
    $ckchg["result"] = "LIVE";

    $result = BillingDao::callLinkpoint($link, $ckchg);
    if ($result["r_approved"] != "APPROVED" || substr($result["r_avs"],0,1) == "N" || substr($result["r_avs"],1,1) == "N") {
      // Send e-mail when card charge fail
      $to       = "pstewart@clicktatemail.com";
      $subject  = "* CARD PREAUTH FAILURE *";
      $headers  = 'From: info@clicktatemail.info' . "\r\n";
      $headers .= 'Reply-To: info@clicktatemail.info' . "\r\n";
      $headers .= 'Return-Path: info@clicktatemail.info' . "\r\n";
      $headers .= 'MIME-Version: 1.0' . "\r\n";
      $headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";
      $msg = "<html><body>Credit card pre-authorization failed.  See details . . . <br><br>";
      $msg .= "User: " . $bill->unot . " <br><br>";
      $msg .= "Name: " . $bill->name . " <br><br>";
      $msg .= "Err : " . $result["r_error"] . " <br><br>";
      $msg .= "Msg : " . $result["r_message"] . " <br><br>";
      $msg .= "Appr: " . $result["r_approved"] . " <br><br>";
      $msg .= "StNm: " . substr($result["r_avs"],0,1) . " <br><br>";
      $msg .= "Zip : " . substr($result["r_avs"],1,1) . " <br><br>";
      $msg .= "</body></html>";
      if (LoginSession::isProdEnv()) {
        mail($to, $subject, $msg, $headers);
      }
      if ($result["r_approved"] != "APPROVED") {
        throw new ChargeException("We are unable to gain approval for the card information provided. Please make sure the information provided is correct. If you continue to have problems, you may contact us at 1-888-8click8.");
        return false;
      }
    }

    // Pre-authorization worked.  Charge user if needed, else just return.
    if ($bill->balance > 0) {
      $ckchg["ordertype"] = "POSTAUTH";
    } else {
      return true;
    }

    $result = BillingDao::callLinkpoint($link, $ckchg);

    if ($result["r_approved"] != "APPROVED" || substr($result["r_avs"],0,1) == "N" || substr($result["r_avs"],1,1) == "N") {
      // Send e-mail when card charge fail
      $to       = "pstewart@clicktatemail.com";
      $subject  = "* CARD CHARGE FAILURE *";
      $headers  = 'From: info@clicktatemail.info' . "\r\n";
      $headers .= 'Reply-To: info@clicktatemail.info' . "\r\n";
      $headers .= 'Return-Path: info@clicktatemail.info' . "\r\n";
      $headers .= 'MIME-Version: 1.0' . "\r\n";
      $headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";
      $msg = "<html><body>Credit card charge failed.  See details . . . <br><br>";
      $msg .= "User: " . $bill->unot . " <br><br>";
      $msg .= "Name: " . $bill->name . " <br><br>";
      $msg .= "Err : " . $result["r_error"] . " <br><br>";
      $msg .= "Msg : " . $result["r_message"] . " <br><br>";
      $msg .= "Appr: " . $result["r_approved"] . " <br><br>";
      $msg .= "StNm: " . substr($result["r_avs"],0,1) . " <br><br>";
      $msg .= "Zip : " . substr($result["r_avs"],1,1) . " <br><br>";
      $msg .= "</body></html>";
      if (LoginSession::isProdEnv()) {
        mail($to, $subject, $msg, $headers);
      }
      if ($result["r_approved"] != "APPROVED") {
        throw new ChargeException("We are unable to process your credit card at this time.  Please call us at 1-888-8click8.");
        return false;
      }
      return true;
    } else {
      return true;
    }
  }
  
  private static function callLinkpoint($link, $ckchg) {
    if (LoginSession::isProdEnv()) {
      return $link->curl_process($ckchg);
    } else {
      return BillingDao::testCharge();
    }
  }
  
  // FOR TESTING ONLY
  // Simulates the return of linkpoint call
  private static function testCharge() {

    /* Comment out one or the other */
    return BillingDao::testChargeSuccess();
    //return BillingDao::testChargeFail();
  }

  private static function testChargeSuccess() { 
    $result = array();
    $result["r_approved"] = "APPROVED";
    $result["r_code"] = "Success";
    $result["r_avs"] = "YY";
    return $result;
  }
  
  private static function testChargeFail() { 
    $result = array();
    $result["r_approved"] = "FAIL";
    $result["r_error"] = "Charge failed";
    $result["r_message"] = "Card declined";
    $result["r_avs"] = "NN";
    return $result;
  }
  
  // Update from BillInfoForm
  public static function setBillInfo($bill) {
    if ($bill->mode == BillInfoForm::MODE_ACTIVATING) {
      $dnow = strtotime("now");
      $dyear = date("Y", $dnow);
      $dmth = date("m", $dnow);
      $dday = date("d", $dnow);
      if ($dday < 26) {
        $bill->next_bill_date = date('Y-m-d', strtotime("+1 month"));
      } else {
        $bill->next_bill_date = date('Y-m-d', strtotime("+2 months", mktime(0,0,0,$dmth,1,$dyear)));
      }
      $sql = "DELETE FROM billinfo WHERE user_id=" . $bill->unot;
      queryNoDie($sql);
      $sql = "INSERT INTO billinfo VALUES(" . $bill->unot;
      $sql .= ", " . quote(addslashes($bill->name));
      $sql .= ", " . quote(addslashes($bill->address1));
      $sql .= ", " . quote(addslashes($bill->address2));
      $sql .= ", " . quote(addslashes($bill->city));
      $sql .= ", " . quote(addslashes($bill->state));
      $sql .= ", " . quote($bill->zip);
      $sql .= ", NULL "; // country
      $sql .= ", " . quote(MyCrypt_Auto::encrypt($bill->card_number));
      $sql .= ", " . quote($bill->exp_month);
      $sql .= ", " . quote($bill->exp_year);
      $sql .= ", " . quote($bill->next_bill_date);
      //if ($bill->upfront_charge > 0) {
      //  $start_bal = -1 * $bill->upfront_charge;
      //} else {
        $start_bal = 0;
      //}
      $sql .= ", " . $start_bal;
      $sql .= ", " . quote($bill->phone);
      $sql .= ", NULL "; // phone_ext
      $sql .= ", " . quote($bill->card_type);
      $sql .= ", " . now();
      $sql .= ", " . $bill->bill_code;
      $sql .= ", 0";  // LAST_BILL_STATUS
      $sql .= ")";
      insert($sql);
      /*
      $sql = "INSERT INTO billcodechanges VALUES(NULL"
      . ", " . $myLogin->userId
      . ", NULL" // old bill code
      . ", " . $bill->bill_code
      . ", NULL)";
      insert($sql);
      */
    } else {
      $sql = "UPDATE billinfo SET ";
      $sql .= "name=" . quote(addslashes($bill->name));
      $sql .= ", " . "address1=" . quote(addslashes($bill->address1));
      $sql .= ", " . "address2=" . quote(addslashes($bill->address2));
      $sql .= ", " . "city=" . quote(addslashes($bill->city));
      $sql .= ", " . "state=" . quote(addslashes($bill->state));
      $sql .= ", " . "zip=" . quote($bill->zip);
      //$sql .= ", " . "country=" . quote($bill->country);
      $sql .= ", " . "card_number=" . quote(MyCrypt_Auto::encrypt($bill->card_number));
      $sql .= ", " . "exp_month=" . quote($bill->exp_month);
      $sql .= ", " . "exp_year=" . quote($bill->exp_year);
      $sql .= ", " . "phone_num=" . quote($bill->phone);
      $sql .= ", " . "card_type=" . quote($bill->card_type);
      $sql .= ", " . "bill_code=" . quote($bill->bill_code);
      $sql .= " WHERE user_id=" . $bill->unot;
      update($sql);
      /*
      if ($bill->isBillCodeChanged()) {
        $sql = "INSERT INTO billcodechanges VALUES(NULL"
        . ", " . $myLogin->userId
        . ", " . quote($bill->billcodeOnFile)
        . ", " . $bill->bill_code
        . ", NULL)";
        insert($sql);
      }
      */
    }
  }

  public static function reActivate($userId) {
    $sql = "UPDATE users SET subscription=1, active = 1, trial_expdt = 0, expire_reason=null";
    $sql .= " WHERE user_id=" . $userId;
    update($sql);
    $sql = "UPDATE billinfo SET last_bill_status=0 WHERE user_id=" . $userId;
    update($sql);
  }

  // Data builders
  private static function buildBillInfo($row) {
    if (! $row) return null;
    return new BillInfo(
    $row["user_id"],
    $row["name"],
    $row["address1"],
    $row["address2"],
    $row["city"],
    $row["state"],
    $row["zip"],
    $row["country"],
    $row["card_type"],
    $row["card_number"],
    $row["exp_month"],
    $row["exp_year"],
    $row["next_bill_date"],
    $row["balance"],
    $row["phone_num"],
    $row["phone_ext"],
    $row["start_bill_date"],
    $row["bill_code"],
    $row["last_bill_status"]
    );
  }
  private static function buildBillCode($row) {
    if (! $row) return null;
    return new BillCode(
    $row["bill_code"],
    $row["new_signups"],
    $row["upfront_charge"],
    $row["monthly_charge"],
    $row["min_charge"],
    $row["max_charge"],
    $row["register_text"],
    $row["create_date"],
    $row["discount_code"],
    $row["first_bill"]
    );
  }
}
