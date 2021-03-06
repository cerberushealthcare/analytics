<?php
require_once "php/dao/_util.php";
require_once "php/dao/_exceptions.php";
require_once "php/linkpoint/lphp.php";
require_once "php/data/db/OldBillInfo.php";

class OldBillingDao {

	// Get billing info for user
	public static function getBillInfo($userId) {
		$billrow = fetch("SELECT name, address1, address2, city, state, zip, country, card_type, card_number, exp_month, exp_year, next_bill_date, balance, phone_num, phone_ext, start_bill_date, bill_code FROM billinfo WHERE user_id = " . $userId);
		if (! $billrow) {
			$bcoderow = fetch("SELECT bill_code, upfront_charge, register_text from bill_codes WHERE new_signups = 1");
		} else {
			$bcoderow = fetch("SELECT bill_code, upfront_charge, register_text from bill_codes WHERE bill_code = " . $billrow["bill_code"]);
		}
		$billinfo = OldBillingDao::buildBillInfo($billrow, $bcoderow);
		return $billinfo;
	}

	public static function buildBillInfo($row, $coderow) {
		if (! $row) {
			$exists = "";
		} else {
			$phone = explode("-",$row["phone_num"]);
			$exists = "Y";
		}
		return new BillInfo($row["name"], $row["address1"], $row["address2"], $row["city"], $row["state"], $row["zip"], $row["country"], $row["card_type"], $row["card_number"], $row["exp_month"], $row["exp_year"], $row["next_bill_date"], $row["balance"], $row["phone_num"], $phone[0], $phone[1], $phone[2], $row["phone_ext"], $row["start_bill_date"], $coderow["bill_code"], $coderow["upfront_charge"], $coderow["register_text"], $exists);
	}

	public static function chargeUser($bill, $testing) {

		if (! $testing) 
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

		if ($testing) {
		  print_r($ckchg);
		} else {
  		$result = $link->curl_process($ckchg);
  		if ($result["r_approved"] != "APPROVED" || substr($result["r_avs"],0,1) == "N" || substr($result["r_avs"],1,1) == "N") {
  		        // Send e-mail when card charge fail
  			$to       = "info@clicktate.com";
  		        $subject  = "* CARD PREAUTH FAILURE *";
  		        $headers  = 'From: info@clicktate.com' . "\r\n";
  		        $headers .= 'Reply-To: info@clicktate.com' . "\r\n";
  		        $headers .= 'Return-Path: info@clicktate.com' . "\r\n";
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
  			mail($to, $subject, $msg, $headers);
  			if ($result["r_approved"] != "APPROVED") {
  				throw new ChargeException("We are unable to process your credit card at this time.  Please call us at 1-888-8click8.");
  				return false;
  			}
  		}
  		
  		// Pre-authorization worked.  Charge user if needed, else just return.
  		if ($bill->balance > 0) {
  			$ckchg["ordertype"] = "POSTAUTH";
  		} else {
  			return true;
  		}
  
  		$result = $link->curl_process($ckchg);
  
  		if ($result["r_approved"] != "APPROVED" || substr($result["r_avs"],0,1) == "N" || substr($result["r_avs"],1,1) == "N") {
  		        // Send e-mail when card charge fail
  			$to       = "info@clicktate.com";
  		        $subject  = "* CARD CHARGE FAILURE *";
  		        $headers  = 'From: pstewart@clicktate.com' . "\r\n";
  		        $headers .= 'Reply-To: pstewart@clicktate.com' . "\r\n";
  		        $headers .= 'Return-Path: pstewart@clicktate.com' . "\r\n";
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
  			mail($to, $subject, $msg, $headers);
  			if ($result["r_approved"] != "APPROVED") {
  				throw new ChargeException("We are unable to process your credit card at this time.  Please call us at 1-888-8click8.");
  				return false;
  			}
  			return true;
  		} else {
  			return true;
  		}
		}
	}

	public static function setBillInfo($bill) {

		if ($bill->exists == "Y") {
			$sql = "UPDATE billinfo SET ";
			$sql .= "name=" . quote(addslashes($bill->name));
			$sql .= ", " . "address1=" . quote(addslashes($bill->address1));
			$sql .= ", " . "address2=" . quote(addslashes($bill->address2));
			$sql .= ", " . "city=" . quote(addslashes($bill->city));
			$sql .= ", " . "state=" . quote(addslashes($bill->state));
			$sql .= ", " . "zip=" . quote($bill->zip);
			$sql .= ", " . "country=" . quote($bill->country);
			$sql .= ", " . "card_number=" . quote($bill->card_number);
			$sql .= ", " . "exp_month=" . quote($bill->exp_month);
			$sql .= ", " . "exp_year=" . quote($bill->exp_year);
			$sql .= ", " . "phone_num=" . quote($bill->phone_num);
			$sql .= ", " . "phone_ext=" . quote($bill->phone_ext);
			$sql .= ", " . "card_type=" . quote($bill->card_type);
			$sql .= " WHERE user_id=" . $bill->unot;
			$rows = update($sql);
			return $rows;
		} else {
			$dnow = strtotime("now");
			$dyear = date("Y", $dnow);
			$dmth = date("m", $dnow);
			$dday = date("d", $dnow);
			if ($dday < 26) {
				$bill->next_bill_date = date('Y-m-d', strtotime("+1 month"));
			} else {
				$bill->next_bill_date = date('Y-m-d', strtotime("+2 months", mktime(0,0,0,$dmth,1,$dyear)));
			}
			$sql = "INSERT INTO billinfo VALUES(" . $bill->unot;
			$sql .= ", " . quote(addslashes($bill->name));
			$sql .= ", " . quote(addslashes($bill->address1));
			$sql .= ", " . quote(addslashes($bill->address2));
			$sql .= ", " . quote(addslashes($bill->city));
			$sql .= ", " . quote(addslashes($bill->state));
			$sql .= ", " . quote($bill->zip);
			$sql .= ", " . quote($bill->country);
			$sql .= ", " . quote($bill->card_number);
			$sql .= ", " . quote($bill->exp_month);
			$sql .= ", " . quote($bill->exp_year);
			$sql .= ", " . quote($bill->next_bill_date);
			if ($bill->upfront_charge > 0) {
				$start_bal = -1 * $bill->upfront_charge;
			} else {
				$start_bal = 0;
			}
			$sql .= ", " . $start_bal;
			$sql .= ", " . quote($bill->phone_num);
			$sql .= ", " . quote($bill->phone_ext);
			$sql .= ", " . quote($bill->card_type);
			$sql .= ", " . now();
			$sql .= ", " . $bill->bill_code;
			$sql .= ")";
			$id = insert($sql);
			return $id;
		}
	}

	public static function reActivate($userId) {
		$sql = "UPDATE users SET active = 1, trial_expdt = 0";
		$sql .= " WHERE user_id=" . $userId;
		$rows = update($sql);
		return $rows;
	}
}
?>