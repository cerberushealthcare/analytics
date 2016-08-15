<?php
require_once "php/dao/_util.php";
require_once "php/data/db/Survey.php";
require_once "php/data/db/Item.php";
require_once "php/data/db/Choice.php";
require_once "php/data/ui/CrumbTrail.php";

class SurveyDao {

	public static function getSurveys($userId) {
		$res = query("SELECT survey_id, name, `desc`, password, active, user_id FROM cs_surveys WHERE user_id=" . $userId . " ORDER BY survey_id DESC");
		$dtos = array();
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			$dto = SurveyDao::buildSurvey($row);
			$dtos[] = $dto;
		}
		return $dtos;
	}
	public static function getSurvey($surveyId) {
		$survey = SurveyDao::buildSurvey(fetch("SELECT survey_id, name, `desc`, password, active, user_id FROM cs_surveys WHERE survey_id=" . $surveyId));
		if ($survey != null) {
			//authenticateUserId($surveyId->userId);
			$survey->items = SurveyDao::getItems($surveyId);
		}
		return $survey;
	}
	public static function getItems($surveyId) {
		$res = query("SELECT item_id, survey_id, uid, type, required, text, goto, sort_order FROM cs_items WHERE survey_id=" . $surveyId . " ORDER BY sort_order");
		$items = array();
		$index = 1;
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			$item = SurveyDao::buildItem($row);
			if ($item->isQuestion()) {
				$item->index = $index++;
			}
			$item->choices = SurveyDao::getChoices($item->id);
			$items[] = $item;
		}
		return $items;
	}
	public static function getItem($itemId) {
		$item = SurveyDao::buildItem(fetch("SELECT item_id, survey_id, uid, type, required, text, goto, sort_order FROM cs_items WHERE item_id=" . $itemId));
		if ($item != null) {
			$item->choices = SurveyDao::getChoices($itemId);
			$item->page = SurveyDao::getPage($item->surveyId, $item->sortOrder);
		}
		return $item;
	}
	public static function getChoices($itemId) {
		$res = query("SELECT choice_id, item_id, text, goto, sort_order FROM cs_choices WHERE item_id=" . $itemId . " ORDER BY sort_order");
		$choices = array();
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			$choice = SurveyDao::buildChoice($row);
			$choices[] = $choice;
		}
		return $choices;
	}
	
	public static function addSurvey($survey) {
		//authenticateUserId($survey->userId);
		$sql = "INSERT INTO cs_surveys VALUE(NULL";
		$sql .= ", " . quote($survey->name);
		$sql .= ", " . quote($survey->desc);
		$sql .= ", " . quote($survey->password);
		$sql .= ", " . toBoolInt($survey->active);
		$sql .= ", " . $survey->userId;
		$sql .= ")";
		return insert($sql);
	}
	public static function addItem($item, $escape = false) {
		$nextSortOrder = SurveyDao::nextItemSortOrder($item->surveyId);
		if (isNull($item->sortOrder)) {
			$item->sortOrder = $nextSortOrder;
		} else if ($item->sortOrder != $nextSortOrder) {
			SurveyDao::shiftItemSorts($item->surveyId, $item->sortOrder);
		}
		$sql = "INSERT INTO cs_items VALUE(NULL";
		$sql .= ", " . $item->surveyId;
		$sql .= ", " . quote($item->uid);
		$sql .= ", " . quote($item->type);
		$sql .= ", " . toBoolInt($item->required);
		$sql .= ", " . quote($item->text, $escape);
		$sql .= ", " . quote($item->goto);
		$sql .= ", " . $item->sortOrder;
		$sql .= ")";
		$id = insert($sql);
		$item->id = $id;
		SurveyDao::addChoicesforItem($item, $escape);
		return $id;
	}
	public static function addChoicesForItem($item, $escape = false) {
		$sortOrder = 1;
		foreach ($item->choices as $k => $choice) {
			$choice->itemId = $item->id;
			$choice->sortOrder = $sortOrder++;
			SurveyDao::addChoice($choice, $escape);
		}
	}
	public static function addChoice($choice, $escape = false) {
		$sql = "INSERT INTO cs_choices VALUE(NULL";
		$sql .= ", " . $choice->itemId;
		$sql .= ", " . quote($choice->text, $escape);
		$sql .= ", " . quote($choice->goto);
		$sql .= ", " . $choice->sortOrder;
		$sql .= ")";
		insert($sql);
	}
	public static function updateSurvey($survey) {
		$sql = "UPDATE cs_surveys SET ";
		$sql .= "name=" . quote($survey->name);
		$sql .= ", `desc`=" . quote($survey->desc);
		$sql .= ", password=" . quote($survey->password);
		$sql .= ", active=" . toBoolInt($survey->active);
		$sql .= " WHERE survey_id=" . $survey->id;
		return query($sql);
	}
	public static function updateItem($item) {
		$row = fetch("SELECT survey_id, sort_order FROM cs_items WHERE item_id=" . $item->id);
		if ($row["sort_order"] != $item->sortOrder) {
			SurveyDao::shiftItemSorts($row["survey_id"], $item->sortOrder);
		}
		$sql = "UPDATE cs_items SET ";
		$sql .= "uid=" . quote($item->uid);
		$sql .= ", type=" . quote($item->type);
		$sql .= ", required=" . toBoolInt($item->required);
		$sql .= ", text=" . quote($item->text, true);
		$sql .= ", goto=" . quote($item->goto);
		$sql .= ", sort_order=" . $item->sortOrder;
		$sql .= " WHERE item_id=" . $item->id;
		query($sql);
		SurveyDao::deleteChoicesForItem($item->id);
		SurveyDao::addChoicesForItem($item);	
	}
	public static function deleteSurvey($surveyId) {
		return query("DELETE FROM cs_surveys WHERE survey_id=" . $surveyId);
	}
	public static function deleteItem($itemId) {
		return query("DELETE FROM cs_items WHERE item_id=" . $itemId);
	}
	public static function deleteItems($itemIds) {
		foreach ($itemIds as $k => $itemId) {	
			SurveyDao::deleteItem($itemId);
		}
	}
	public static function deleteChoicesForItem($itemId) {
		return query("DELETE FROM cs_choices WHERE item_id=" . $itemId);
	}
	public static function nextItemSortOrder($surveyId) {
		$row = fetch("SELECT MAX(sort_order) AS max FROM cs_items WHERE survey_id=" . $surveyId);
		return $row["max"] + 1;
	}
	public static function shiftItemSorts($surveyId, $sortOrder) {
		query("UPDATE cs_items SET sort_order=sort_order+1 WHERE survey_id=" . $surveyId . " AND sort_order>=" . $sortOrder);
	}
	public static function getNextItem($surveyId, $sortOrder) {
		$row = fetch("SELECT item_id, survey_id, uid, type, required, text, goto, sort_order FROM cs_items WHERE survey_id=" . $surveyId . " AND sort_order>" . $sortOrder . " ORDER BY sort_order");
		$item = SurveyDao::buildItem($row);
		if ($item != null) {
			$item->choices = SurveyDao::getChoices($item->id);
			$item->page = SurveyDao::getPage($item->surveyId, $item->sortOrder);
		}
		return $item;
	}
	public static function getPrevItem($surveyId, $sortOrder) {
		$row = fetch("SELECT item_id, survey_id, uid, type, required, text, goto, sort_order FROM cs_items WHERE survey_id=" . $surveyId . " AND sort_order<" . $sortOrder . " ORDER BY sort_order DESC");
		$item = SurveyDao::buildItem($row);
		if ($item != null) {
			$item->choices = SurveyDao::getChoices($item->id);
			$item->page = SurveyDao::getPage($item->surveyId, $item->sortOrder);
		}
		return $item;
	}
	public static function getPage($surveyId, $sortOrder) {
		$row = fetch("SELECT COUNT(*)+1 AS page FROM cs_items WHERE survey_id=" . $surveyId . " AND type='pagebreak' AND sort_order<" . $sortOrder);
		return $row["page"];
	}
	public static function getPageNumberFromId($surveyId, $id) {
		$row = fetch("SELECT COUNT(*)+1 AS page FROM cs_items WHERE survey_id=" . $surveyId . " AND type='pagebreak' AND sort_order<=(SELECT sort_order FROM cs_items WHERE UID=" . quote($id) . ")");
		return $row["page"];
	}
	public static function readSurvey($surveyId, $password, $page, $previewOnly, $print) {
		$survey = SurveyDao::buildSurvey(fetch("SELECT survey_id, name, `desc`, password, active, user_id FROM cs_surveys WHERE survey_id=" . $surveyId));
		if (! $previewOnly && ! isNull($survey->password)) {
			if ($password != $survey->password) {
				throw new SecurityException("You are not authorized to access this survey");
			}
		}
		$survey->items = SurveyDao::readItems($surveyId, $page, $print);
		$survey->page = $page;
		return $survey;
	}
	public static function readItems($surveyId, $page, $print) {
		$res = query("SELECT item_id, survey_id, uid, type, required, text, goto, sort_order FROM cs_items WHERE survey_id=" . $surveyId . " ORDER BY sort_order");
		$items = array();
		$p = 1;
		$index = 1;
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			$item = SurveyDao::buildItem($row);
			if ($item->isQuestion()) {
				$item->index = $index++;
			}
			if ($print || ($page == $p && $item->type != "pagebreak")) {
				$item->choices = SurveyDao::readChoices($item->id);
				$item->responses = array();
				$item->missing = false;
				$items[$item->id] = $item;
			}
			if (! $print && $item->type == "pagebreak") {
				$p++;
				if ($p > $page) {
					return $items;
				}
			}
		}
		if ($p < $page) {
			throw new PastEndException();
		}
		return $items;
	}
	public static function readChoices($itemId) {
		$res = query("SELECT choice_id, item_id, text, goto, sort_order FROM cs_choices WHERE item_id=" . $itemId . " ORDER BY sort_order");
		$choices = array();
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			$choice = SurveyDao::buildChoice($row);
			$choices[$choice->id] = $choice;
		}
		return $choices;
	}
	public static function respondSurvey($userId, $survey, $previewOnly) {

		// Iterate thru responses and ensure requireds are supplied and capture first applicable goto
		$goto = null;  // to hold first Choice goto
		$itemGoto = null; // to hold first Item goto
		$anyMissing = false;
		foreach ($survey->items as $itemId => $item) {
			if ($itemGoto == null & ! isNull($item->goto)) {
				$itemGoto = $item->goto;
			}
			if ($item->isQuestion()) {
				if ($item->hasChoices()) {
					$count = count($item->responses);
					if ($item->type == "dropdown") {
						if ($item->choices[$item->responses[0]]->sortOrder == 1) {
							$count = 0;
						}
					}
					if ($count == 0) {
						if ($item->required) {
							$anyMissing = true;
							$item->missing = true;
						}
					} else {
						foreach ($item->responses as $selIndex) {
							$choice = $item->choices[$selIndex];
							if ($goto == null & ! isNull($choice->goto)) {
								$goto = $choice->goto;
							}
						}
					}		
				} else {
					if ($item->required && isNull($item->responses[0])) {
						$anyMissing = true;
						$item->missing = true;
					}
				}
			}
		}
		if ($anyMissing) {
			throw new MissingRequiredException("Please supply all required responses (indicated below in red).");
		}

		// Commit responses to database
		if (! $previewOnly) { 
			$res = query("DELETE FROM cs_responses WHERE user_id=" . $userId . " AND survey_id=" . $survey->id . " AND page>=" . $survey->page);
			foreach ($survey->items as $itemId => $item) {
				if ($item->isQuestion()) {
					$responseText = "";
					$choiceIds = "";
					if ($item->hasChoices()) {
						foreach ($item->responses as $selIndex) {
							$choice = $item->choices[$selIndex];
							if (! isNull($responseText)) {
								$responseText .= " / ";
								$choiceIds .= ",";
							}
							$responseText .= $choice->text;
							$choiceIds .= $selIndex;
						}
					} else {
						$responseText = $item->responses[0];
					}
					$sql = "INSERT INTO cs_responses VALUES(" . $userId;
					$sql .= "," . $itemId;
					$sql .= "," . $survey->id;
					$sql .= "," . $survey->page;
					$sql .= "," . quote($choiceIds);
					$sql .= "," . quote($responseText, true);
					$sql .= ", null";
					$sql .= ")"; 
					query($sql);
				}
			}
		}

		// If no goto assigned from above, assign it to any itemGoto; if still null, just go to the next page
		if ($goto == null) {
			$goto = $itemGoto;
		}
		if ($goto != null) {
			$goto = SurveyDao::getPageNumberFromId($survey->id, $goto);
		} else {
			$goto = $survey->page + 1; 
		}
		return $goto;
	}
		
	public static function buildSurveyBreadcrumb() {
		$trail = new CrumbTrail();
		SurveyDao::addDashboardCrumb($trail);
		return $trail->html();
	}
	public static function buildItemBreadcrumb($surveyId) {
		$trail = new CrumbTrail();	
		SurveyDao::addSurveyCrumb($trail, $surveyId);
		SurveyDao::addDashboardCrumb($trail);
		return $trail->html();
	}
	public static function addSurveyCrumb($trail, $surveyId) {
		$row = fetch("SELECT name FROM cs_surveys WHERE survey_id=" . $surveyId);
		$trail->push("adminSurvey.php?id=" . $surveyId . rnd(), "S:" . $row["name"]);
	}
	public static function buildItemSortCombo($surveyId, $itemId) {
		$sql = "SELECT item_id, type, uid, sort_order FROM cs_items WHERE survey_id=" . $surveyId . " ORDER BY sort_order";
		return SurveyDao::buildSortCombo($sql, $itemId, "item_id");
	}
	// If sortOrder is null, return the last entry
	public static function denullifySortOrder($sortOrders, $sortOrder) {
	  if (is_null($sortOrder)) {
    	$keys = array_keys($sortOrders);
    	return $keys[count($sortOrders) - 1];
  	} else {
	    return $sortOrder;
	  }
	}
	private static function buildSortCombo($sql, $id, $idFieldName) {
		$res = query($sql);
		$combos = array();
		$last = "";
		$lastId = "";
		$page = 1;
		for ($i = 0; $row = mysql_fetch_array($res, MYSQL_ASSOC); $i++) {
			if ($row["type"] == "pagebreak") {
				$page++;
			}
			$last = $row["sort_order"];
			if ($i == 0) {
				$combos[$last] = "[At the beginning]";
			} else {	
				if ($lastId != $id) {
					$combos[$last] = "After [" . $lastUid . "]";
				}
			}
			$lastId = $row[$idFieldName];
			$lastUid = $row["type"];
			if (! isBlank($row["uid"])) $lastUid .= " \"" . $row["uid"] . "\"";
			if ($row["type"] == "pagebreak") $lastUid .= " - start of page " . $page;
		}
		if (! is_null($id) && $id == $lastId) {
			$combos[$last] = "[At the end]";
		} else {
			$combos[$last + 1] =  "[At the end]";
		}
		return $combos;
	}
	public static function addDashboardCrumb($trail) {
		$trail->push("adminDashboard.php", "Dashboard");
	}
	
	private static function buildSurvey($row) {
		if (! $row) return null;
		return new Survey($row["survey_id"], $row["name"], $row["desc"], $row["password"], $row["active"], $row["user_id"]);
	}
	private static function buildItem($row) {
		if (! $row) return null;
		return new Item($row["item_id"], $row["survey_id"], $row["uid"], $row["type"], $row["required"], $row["text"], $row["goto"], $row["sort_order"]);
	}
	private static function buildChoice($row) {
		if (! $row) return null;
		return new Choice($row["choice_id"], $row["item_id"], $row["text"], $row["goto"], $row["sort_order"]);
	}
}
?>