<?php 
require_once "inc/noCache.php";
require_once "inc/requireLogin.php";
require_once "inc/uiFunctions.php";
require_once "php/dao/SurveyDao.php";
require_once "php/forms/SurveyForm.php";

$form = new SurveyForm();
$getId = isset($_GET["id"]) ? $_GET["id"] : null;
if (isset($getId)) {

	// Page entry from link
	if ($getId != "") {
		try {
			$survey = SurveyDao::getSurvey($getId);
			$form->setFromDatabase($survey);
		} catch (SecurityException $e) {
			die($e->getMessage());
		}
	} else {
		$form->userId = $myUserId;
	}
} else {

	// Page submitted from action
	try {
		if ($_POST["action"] == " Exit ") {
			header("Location: adminDashboard.php");
			exit;
		}
    if ($_POST["action"] == "Add New Item") {
      header("Location: adminSurveyItem.php?id=&sid=" . $_POST["id"]);
      exit;
    }
		if ($_POST["action"] == "Delete Survey") {
			SurveyDao::deleteSurvey($_POST["id"]);
			$msg = "Survey deleted.";
		} else {
			$form->setFromPost();
			$form->validate();
			$survey = $form->buildSurvey();
			if ($survey->id == "") {
				$form->id = SurveyDao::addSurvey($survey);
				$msg = "Survey added.";
			} else {
				SurveyDao::updateSurvey($survey);
				$msg = "Survey updated.";
			}
			if ($_POST["action"] == "Delete Checked") {
				$count = count($_POST["del"]);
				if ($count > 0) {
					SurveyDao::deleteItems($_POST["del"]);
				}
				$msg = "Selected items deleted: " . count($_POST["del"]) . " total.";
			}
			$survey = SurveyDao::getSurvey($form->id);
			$form->setFromDatabase($survey);
		}
	} catch (ValidationException $e) {
		$errors = $e->getErrors();
	} catch (SecurityException $e) {
		die($e->getMessage());
	}
}
$update = (! isBlank($form->id));
$form->breadcrumb = SurveyDao::buildSurveyBreadcrumb(); 
$focus = "uid";
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <!-- Copyright (c)2006 by LCD Solutions, Inc.  All rights reserved. -->
  <!-- http://www.clicktate.com -->
  <head>
    <title>clicktate : Survey</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta http-equiv="Content-Script-Type" content="text/javascript" />
    <meta http-equiv="Content-Language" content="en-us" />
    <link rel="stylesheet" type="text/css" href="css/med.css" media="screen" />
    <script language="JavaScript1.2" src="js/admin.js"></script>
  </head>
  <body onbeforeunload="return checkDirty()">
    <?php include "inc/banner.php" ?>
    <form method="post" action="adminSurvey.php">
      <input type="hidden" name="id" value="<?=$form->id ?>">
      <input type="hidden" name="userId" value="<?=$form->userId ?>">
      <?php require_once "inc/errors.php" ?>
      <div id="breadcrumb">
        <?=$form->breadcrumb ?><br>
        <h1><?=($update) ? "Survey:" : "New Survey" ?> <?=$form->name ?></h1>
      </div>
      <div class="action">
        <input type="submit" name="action" value=" Save " onclick="ignoreDirty()">
        <?php if ($update) { ?>
          <span></span>
          <input type="submit" name="action" value="Delete Survey" onclick="if (deleteCancelled()) {return false} else {ignoreDirty()}">
          <span></span>
          <input type="button" value="Preview" onclick="previewSurvey(<?=$form->id ?>,<?=$form->userId ?>,'<?=$form->password ?>')">
        <?php } ?>
        <span></span>
        <input type="submit" name="action" value=" Exit ">
      </div>
      <div class="roundBox">
        <div class="roundTop"><img src="img/tl.gif"></div>
        <div class="roundContent">
          <table border=0 cellpadding=0 cellspacing=0>
            <tr>
              <td class="label first">Name</td>
              <td width=5></td>
              <td><input type="text" size="60" name="name" value="<?=$form->name ?>" onchange="makeDirty()"></td>
              <td width=15></td>
              <td class="label">Password</td>
              <td width=5></td>
              <td><input type="text" size="30" name="password" value="<?=$form->password ?>" onchange="makeDirty()"></td>
              <td width=15></td>
              <td class="label">Active?</td>
              <td width=5></td>
              <td><input type="checkbox" name="active" value="Y" <?=checkedIf($form->active) ?> onchange="makeDirty()"></td>
            </tr>
          </table>
          <table border=0 cellpadding=0 cellspacing=0 width=95%>
            <tr>
              <td class="label first" valign="top" style="padding-top:5px">Description</td>
              <td width=5></td>
              <td><textarea style="width:100%" cols="80" rows="2" name="desc" onchange="makeDirty()"><?=$form->desc ?></textarea></td>
            </tr>
            <tr>
              <td class="label first" valign="top" style="padding-top:5px">Sample URL</td>
              <td width=5></td>
              <td><textarea style="width:100%" cols="80" rows="1" onchange="makeDirty()">https://www.clicktate.com/sec/survey.php?s=<?=$form->id ?>&p=<?=$form->password ?>&u=1</textarea></td>
            </tr>
          </table>
        </div>
        <div class="roundBottom"><img src="img/bl.gif"></div>
      </div>
      <?php if (isset($form->items)) { ?>
        <div class="roundBox">
	       <div class="roundTitle">
            Items of this Survey
          </div>
          <div class="roundContent">
            <div class="scrollTable" style="height:expression(document.body.offsetHeight - breadcrumb.offsetTop - 298)">
              <table border=0 cellpadding=0 cellspacing=0>
                <tr class="fixed">
                  <th>&nbsp;</th>
                  <th>ID</th>
                  <th>Type</th>
                  <th>Text</th>
                  <th>Options</th>
                  <th>Required?</th>
                  <th class="noDivider">Goto</th>
                </tr>
                <?php $altColor = true ?>
                <?php $page = 1 ?>
                <?php foreach ($form->items as $k => $item) { ?>
                  <?php $altColor = ! $altColor ?>
                   <tr <?php if ($altColor) { ?>class="gray"<?php } ?> <?php if ($item->type == 'pagebreak') { ?>style="background:#696969; color:white"<?php } ?>>
                    <td class="control">
                      <input type="checkbox" name="del[]" value="<?=$item->id ?>" title="Select for deletion">
                      <a href="adminSurveyItem.php?sid=<?=$survey->id ?>&id=<?=$item->id ?>" title="Edit this record"><img src="img/pencil.gif"></a>
                    </td>
                    <td><?=$item->index?>&nbsp;<?=$item->uid ?></td>
                    <td><i><?=$item->type ?></i></td>
                    <?php if ($item->type == 'pagebreak') { ?>
                      <td></td>
                    <?php } else { ?>
                      <td><?=$item->text ?></td>
                    <?php } ?>
                    <td><?=$item->getOptionList() ?></td>
                    <td><?=$item->required ? "yes" : "" ?></td>
                    <td><?=$item->goto ?></td>
                  </tr>
                <?php } ?>
              </table>
            </div>
            <div class="action nopad">
              <input type="submit" name="action" value="Add New Item">
              <span></span>
              <input type="submit" name="action" value="Delete Checked" onclick="if (deleteCancelled()) return false">
             </div>
          </div>
          <div class="roundBottom"><img src="img/bl.gif"></div>
        </div>
      <?php } ?>
    </form>
  </body>
</html>
<?php require_once "inc/focus.php" ?>
<script>
function previewSurvey(sid) {
	if (dirty) {
		alert("Warning: Unsaved changes to this page will not be visible on the preview.");
	}
	window.open("survey.php?s=" + sid + "&pv=1", "preview", 'toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,dependent=no,width=900,height=700,top=30,left=50');
}
</script>