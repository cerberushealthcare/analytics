<?php
require_once "php/dao/SurveyDao.php";

$itemValues = null;
$error = null;
if (isset($_GET["s"])) {

	// First time navigation, initialize survey from query strings
	$surveyId = $_GET["s"];
	$pwd = isset($_GET["p"]) ? $_GET["p"] : "";
	$page = isset($_GET["g"]) ? $_GET["g"] : "1";
	$previewOnly = isset($_GET["pv"]) ? $_GET["pv"] : "0";
	$print = isset($_GET["prt"]) ? $_GET["prt"] : "0";
	if ($previewOnly == "1") {
		$userId = "1";
	} else {
		if (isset($_GET["u"])) {
			$userId = $_GET["u"];
	 	} else {
	 		die("Survey request is invalid.");
	 	}
	}
 	try {
		$survey = SurveyDao::readSurvey($surveyId, $pwd, $page, $previewOnly == "1", $print == "1");
 	} catch (PastEndException $e) {
 		$survey = null;
 	} catch (Exception $e) {
 		die($e->getMessage());
 	}
} else {
	if (isset($_POST["sid"])) {

		// Page submitted from action
		$surveyId = $_POST["sid"];
		$userId = $_POST["u"];
		$pwd = isset($_POST["sp"]) ? $_POST["sp"] : "";
		$page = isset($_POST["pg"]) ? $_POST["pg"] : "";
		$previewOnly = isset($_POST["pv"]) ? $_POST["pv"] : "0";
		$survey = SurveyDao::readSurvey($surveyId, $pwd, $page, $previewOnly == "1", false);
		$print = "0";	
		
		// Assign responses to survey
		if (isset($_POST["itemValues"])) {
			$itemValues = $_POST["itemValues"];
		}
		if ($itemValues != null) {
			foreach ($itemValues as $itemId => $responses) {
				foreach ($responses as $response) {
					$survey->items[$itemId]->responses[] = $response;
				}
			}
		}
		
		// Update survey responses
		try {
			$page = SurveyDao::respondSurvey($userId, $survey, $previewOnly == "1");
			header("Location: survey.php?s=" . $surveyId . "&p=" . $pwd . "&g=" . $page . "&u=" . $userId . "&pv=" . $previewOnly);
		} catch (MissingRequiredException $e) {
			$error = $e->getMessage();
		} catch (Exception $e) {
			die($e->getMessage());
		}
	} else {

		// Invalid page entry, no survey ID supplied
		die("Survey request is invalid.");
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <!-- Copyright (c)2006 by LCD Solutions, Inc.  All rights reserved. -->
  <!-- http://www.clicktate.com -->
  <head>
    <title>clicktate : survey</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta http-equiv="Content-Script-Type" content="text/javascript" />
    <meta http-equiv="Content-Language" content="en-us" />
    <meta name="keywords" content="dictate, dictation, medical note, document generation, note generation" />
    <meta name="description" content="Automated document generation." />
    <link rel="stylesheet" type="text/css" href="css/clicktate.css?3" media="screen" />
    <link rel="stylesheet" type="text/css" href="css/survey.css" media="screen" />
  </head>
  <body>
    <form id="frm" method="post" action="survey.php">
      <? if ($previewOnly == "1") { ?>
        <div id="footer"><a href="javascript:window.close()">Close Preview</a></div>
      <? } ?>
      <div id="bodyContainer">
        <table border=0 cellpadding=0 cellspacing=0>
          <tr>
            <td rowspan=2><a href="/index.php"><img src="img/lhdNoLogo.bmp" /></a></td>
            <td valign="top"><img src="img/lhdEdge.bmp" /></td>
          </tr>
          <tr>
            <td class="loginfo">
            </td>
          </tr>
        </table>
        <div class="content">
          <? if ($survey == null) { ?>
            <h1>Survey complete!</h1>
            <p>
              Thank you for taking the time to complete this survey.
              We really value your feedback.
            </p>
            <p>
              <a href="https://www.clicktate.com/sec">Log in to clicktate now</a>
            </p>
            <p>
            </p>
          <? } else { ?>
	          <input type="hidden" name="sid" value="<?=$survey->id ?>" />
	          <input type="hidden" name="sp" value="<?=$survey->password ?>" />
	          <input type="hidden" name="pg" value="<?=$survey->page ?>" />
            <input type="hidden" name="pv" value="<?=$previewOnly ?>" />
	          <input type="hidden" name="u" value="<?=$userId ?>" />
	          <h1><?=$survey->name ?></h1>
	          <? foreach ($survey->items as $item) { ?>
	            <? if ($item->type == "header") { ?>
	              <h2><?=$item->text ?></h2>
							<? } else if ($item->type == "paragraph") { ?>
	              <p><?=$item->text ?></p>
              <? } else if ($item->type == "pagebreak") { ?>
                <hr size=1 />
	            <? } else { ?>
	              <p>
	                <table border=0 cellpadding=0 cellspacing=0>
	                  <tr class="line1">
	                    <td class="index"><?=$item->index?>.</td>
	                    <td width=8></td>
	                    <td class="question"><?=$item->text ?></td>
	                  </tr>
	                  <tr class="line2">
	                    <td colspan=2 class="index2">&nbsp;</td>
	                    <td>
	                      <input type="hidden" name="itemId" value="<?=$item->id ?>" />
                        <? if ($item->missing) { ?>
                          <div class="error">>> A response is required here.</div>
                        <? } ?>
					              <? if ($item->type == "text") { ?>
					                <input name="itemValues[<?=$item->id ?>][0]" type="text" size="80" value="<?=$itemValues[$item->id][0] ?>" />
					              <? } else if ($item->type == "textarea") { ?>
	                        <textarea name="itemValues[<?=$item->id ?>][0]" cols="100" rows="3"><?=$itemValues[$item->id][0] ?></textarea>
	                      <? } else if ($item->type == "checkboxes") { ?>
	                        <? $j = 0 ?>
	                        <? foreach ($item->choices as $choice) { ?>
	                          <input type="checkbox" name="itemValues[<?=$item->id ?>][<?=$j ?>]" value="<?=$choice->id ?>"<? if (isset($itemValues[$item->id][$j])) { ?> checked<? } ?> /><?=$choice->text ?><br>
	                          <? $j++ ?>
	                        <? } ?>
	                      <? } else if ($item->type == "radiobuttons") { ?>
	                        <? foreach ($item->choices as $choice) { ?>
	                          <input type="radio" name="itemValues[<?=$item->id ?>][0]" value="<?=$choice->id ?>"<? if (isset($itemValues[$item->id][0]) && $itemValues[$item->id][0] == $choice->id) { ?> checked<? } ?> /><?=$choice->text ?><br>
	                        <? } ?>
	                      <? } else if ($item->type == "dropdown") { ?>
	                        <select name="itemValues[<?=$item->id ?>][0]">
	                        <? foreach ($item->choices as $choice) { ?>
	                          <option value="<?=$choice->id ?>"<? if ($itemValues[$item->id][0] == $choice->id) { ?> selected<? } ?>><?=$choice->text ?></option>
	                        <? } ?>
	                        </select>
	                      <? } ?>
	                    </td>
	                  </tr>
	                </table>
	              </p>
	            <? } ?>
	          <? } ?>
            <? if ($print != "1") { ?>
	          <input type="submit" value="Next Page >" />
            <? } ?>
          <? } ?>
        </div>
      </div>
    </form>
  </body>
</html>