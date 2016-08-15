<?php 
require_once "inc/noCache.php";
require_once "inc/requireLogin.php";
require_once "inc/uiFunctions.php";
require_once "php/dao/SurveyDao.php";
require_once "php/forms/ItemForm.php";

$form = new ItemForm();
$getId = isset($_GET["id"]) ? $_GET["id"] : null;
if (isset($getId)) {

	// Page entry from link
	if ($getId != "") {
		try {
			$item = SurveyDao::getItem($getId);
			$form->setFromDatabase($item);
			$form->surveyId = $_GET["sid"];
		} catch (SecurityException $e) {
			die($e->getMessage());
		}
	} else {
		$form->surveyId = $_GET["sid"];
	}
} else {

	// Page submitted from action
	$form->surveyId = $_POST["surveyId"];
	try {
		$action = isset($_POST["action"]) ? $_POST["action"] : null;
		if ($action == " Exit ") {
			header("Location: adminSurvey.php?id=" . $form->surveyId);
			exit;
		}
		if ($action == "Delete Item") {
			SurveyDao::deleteItem($_POST["id"]);
			$msg = "Item deleted.";
		} else if ($action == "Add New") {
			$msg = "Ready to enter new item.";
		} else if ($action == "< Prev") {
			$item = SurveyDao::getPrevItem($form->surveyId, $_POST["sortOrder"]);
			if ($item == null) {
				$form->setFromPost();
			} else {
				$form->setFromDatabase($item);
				$msg = "Displaying previous item.";
			}
		} else if ($action == "Next >") {
			$item = SurveyDao::getNextItem($form->surveyId, $_POST["sortOrder"]);
			if ($item == null) {
				$form->setFromPost();
			} else {
				$form->setFromDatabase($item);
				$msg = "Displaying next item.";
			}
		} else if ($action == "Copy") {
			$form->setFromPost();
			$form->id = "";
			$form->sortOrder = null;
			if (! isBlank($form->uid)) {
				$form->uid .= "Copy";
			}
			$msg = "Item copied and ready to save.";
		} else {
			$form->setFromPost();
			$form->validate();
			$item = $form->buildItem();
			if ($item->id == "") {
				$form->id = SurveyDao::addItem($item);
				$msg = "Item added.";
			} else {
				SurveyDao::updateItem($item);
				$msg = "Item updated.";
			}
			$item = SurveyDao::getItem($form->id, true);
			$form->setFromDatabase($item);
		}
	} catch (ValidationException $e) {
		$errors = $e->getErrors();
	} catch (DuplicateInsertException $e) {
		$errors = singleError(null, "An item for this survey already exists with that ID.");
	} catch (SecurityException $e) {
		die($e->getMessage());
	}
}
$update = (! isBlank($form->id));
$form->breadcrumb = SurveyDao::buildItemBreadcrumb($form->surveyId); 
$form->sortOrders = SurveyDao::buildItemSortCombo($form->surveyId, $form->id);
$form->sortOrder = SurveyDao::denullifySortOrder($form->sortOrders, $form->sortOrder);
$form->types = $form->buildItemTypeCombo();
$focus = "type";
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <!-- Copyright (c)2006 by LCD Solutions, Inc.  All rights reserved. -->
  <!-- http://www.clicktate.com -->
  <head>
    <title>clicktate : Item</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta http-equiv="Content-Script-Type" content="text/javascript" />
    <meta http-equiv="Content-Language" content="en-us" />
    <link rel="stylesheet" type="text/css" href="css/med.css" media="screen" />
    <script language="JavaScript1.2" src="js/admin.js"></script>
  </head>
  <body onbeforeunload="return checkDirty()">
    <?php include "inc/banner.php" ?>
    <form method="post" action="adminSurveyItem.php">
      <input type="hidden" name="id" value="<?=$form->id ?>">
      <input type="hidden" name="surveyId" value="<?=$form->surveyId ?>">
      <input type="hidden" name="page" value="<?=$form->page ?>">
      <?php require_once "inc/errors.php" ?>
      <div id="breadcrumb">
        <?=$form->breadcrumb ?><br>
        <h1><?=($update) ? "Item:" : "New Item" ?> <?=$form->key() ?></h1>
      </div>
      <div class="action">
        <input type="submit" value=" Save " onclick="ignoreDirty()">
        <?php if ($update) { ?>
          <span></span>
          <input type="submit" name="action" value="Add New">
          <input type="submit" name="action" value="Copy">
          <span></span>
          <input type="submit" name="action" value="Delete Item" onclick="if (deleteCancelled()) {return false} else {ignoreDirty()}">
          <span></span>
          <input type="submit" name="action" value="< Prev">
          <input type="submit" name="action" value="Next >">
          <span></span>
          <input type="button" value="Preview" onclick="previewSurvey(<?=$form->surveyId ?>,<?=$form->page ?>)">
        <?php } ?>
        <span></span>
        <input type="submit" name="action" value=" Exit ">
      </div>
      <div class="roundBox">
        <div class="roundTop"><img src="img/tl.gif"></div>
        <div class="roundContent">
          <table border=0 cellpadding=0 cellspacing=0>
            <tr>
              <td class="label first">Type</td>
              <td width=5></td>
              <td>
                <?php renderCombo("type", $form->types, $form->type, "onchange=\"makeDirty()\"") ?>
              </td>
              <td width=15></td>
              <td class="label">ID</td>
              <td width=5></td>
              <td><input type="text" size="25" name="uid" value="<?=$form->uid ?>" onchange="makeDirty()"></td>
            </tr>
          </table>
          <table border=0 cellpadding=0 cellspacing=0>
            <tr>
              <td class="label first">Sort Position</td>
              <td width=5></td>
              <td>
                <?php renderCombo("sortOrder", $form->sortOrders, $form->sortOrder, "onchange=\"makeDirty()\"") ?>
              </td>
              <td width=15></td>
              <td class="label">Goto</td>
              <td width=5></td>
              <td><input type="text" size="10" name="goto" value="<?=$form->goto ?>" onchange="makeDirty()"></td>
              <td width=15></td>
              <td class="label">Required?</td>
              <td width=5></td>
              <td><input type="checkbox" name="required" value="Y" <?=checkedIf($form->required) ?> onchange="makeDirty()"></td>
            </tr>
          </table>
          <table border=0 cellpadding=0 cellspacing=0 width=95%>
            <tr>
              <td class="label first" valign="top" style="padding-top:5px">Text</td>
              <td width=5></td>
              <td><textarea style="width:100%" cols="80" rows="3" name="text" onchange="makeDirty()"><?=$form->text ?></textarea></td>
            </tr>
          </table>
        </div>
        <div class="roundBottom"><img src="img/bl.gif"></div>
      </div>
      <div class="roundBox">
       <div class="roundTitle">Choices</div>
        <div class="roundContent">
          <div class="scrollTable noScroll">
            <table border=0 cellpadding=0 cellspacing=0>
              <tr>
                <th class="noBorder" width=77%>Text</th>
                <th class="noBorder" width=22%>Goto</th>
              </tr>
              <?php $ix = 0 ?>
              <?php foreach ($form->choices as $k => $choiceLine) { ?>
                <tr class="blue1">
                  <td><input type="text" class="<?=isBlank($choiceLine->text) ? "expand blue" : "expand" ?>" size="15" name="chText[<?= $ix ?>]" value="<?=$choiceLine->text ?>" onchange="makeDirty()"></td>
                  <td><input type="text" class="<?=isBlank($choiceLine->text) ? "expand blue" : "expand" ?>" size="15" name="chGoto[<?= $ix++ ?>]" value="<?=$choiceLine->goto ?>" onchange="makeDirty()"></td>
                </tr>
  			      <?php } ?>
            </table>
          </div>
        </div>
        <div class="roundBottom"><img src="img/bl.gif"></div>
      </div>
      <div class="action lift">
        <input type="submit" value=" Save " onclick="ignoreDirty()">
      </div>
    </form>
  </body>
</html>
<?php require_once "inc/focus.php" ?>
<script>
function previewSurvey(sid, pg) {
  if (dirty) {
    alert("Warning: Unsaved changes to this page will not be visible on the preview.");
  }
  window.open("survey.php?s=" + sid + "&g=" + pg + "&pv=1", "preview", 'toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,dependent=no,width=900,height=700,top=30,left=50');
}
</script>