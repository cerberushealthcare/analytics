<?php
require_once "php/dao/_util.php";

/*
 * Usage stats
 */
class UsageDao {

	public static function createUsageDetail($sessionId, $usageType, $cid, $cname) {
		global $myUserId;
		try {
			$billable = false;
			if ($usageType > 1) {
				$alreadyBilled = UsageDao::isSessionBilled($sessionId);
				$billable = ! $alreadyBilled;
			}
			insert("INSERT INTO usage_details VALUES(" . $myUserId . "," . $sessionId . "," . $usageType . "," . now() . "," . quote($cid) . "," . quote($cname) . ", 0, " . toBoolInt($billable) . ")");
			if ($billable) {
				UsageDao::setBilled($sessionId, true);
			}
		} catch (DuplicateInsertException $e) {
			// Already recorded, ignore
			return;
		} catch (Exception $e) {
			// TODO3 PAT I would like to kick off an email to us to let us know an exception is preventing recording billing info.
			return;
		}	
	}
	
	public static function isSessionBilled($sessionId) {
		$row = fetch("SELECT billed FROM sessions WHERE session_id=" . $sessionId);
		if (! $row) return null;
		return ($row["billed"] == 1);
	}
	
	public static function setBilled($sessionId, $billed) {
		query("UPDATE sessions SET billed=" . toBoolInt($billed) . " WHERE session_id=" . $sessionId);
	}
}
?>
