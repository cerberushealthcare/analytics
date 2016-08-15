<?php
require_once "php/dao/_util.php";
require_once "php/dao/_exceptions.php";
require_once "php/data/db/AcctData.php";

Class AcctDataDao {

	// Get specific account info
	public static function getAcctInfo($userId) {
		$acctrow = fetch("SELECT u.uid, u.pw, u.name, u.email, u.company, u.udata1, u.udata2, u.udata3, u.phone_num, u.phone_ext FROM users u where u.user_id = " .$userId);
		$acctinfo = AcctDataDao::buildAcctInfo($acctrow);
		return $acctinfo;
	}

	public static function buildAcctInfo($row) {
		if (! $row) return null;
		return new AcctData($row["uid"], $row["pw"], $row["name"], $row["email"], $row["company"], $row["udata1"], $row["udata2"], $row["udata3"], $row["phone_num"], $row["phone_ext"] );
	}


	public static function setAcctInfo($acct) {

		$sql = "UPDATE users SET ";
		switch ($_SESSION["cField"]) {
			case "pw":
				$sql .= "pw=" . quote($acct->npw1);
				break;
			case "name":
				$sql .= "name=" . quote($acct->name);
				break;
			case "email":
				$sql .= "email=" . quote($acct->email);
				break;
			case "company":
				$sql .= "company=" . quote($acct->company);
				break;
			case "state":
				$sql .= "udata1=" . quote($acct->state);
				break;
			case "license":
				$sql .= "udata2=" . quote($acct->license);
				break;
			case "dea":
			  $sql .= "udata3=" . quote($acct->dea);
			  break;
			case "phone":
				$sql .= "phone_num=" . quote($acct->phone_num);
				$sql .= ", " . "phone_ext=" . quote($acct->phone_ext);
				break;
			default:
				return;
				break;
		}
		$sql .= " WHERE user_id=" . $acct->userId;
		$rows = update($sql);
		return $rows;
	}


}