<?php 
require_once "inc/noCache.php";
require_once "php/data/LoginSession.php";
require_once "php/dao/TemplateAdminDao.php";
require_once "php/forms/SectionForm.php";
require_once "inc/uiFunctions.php";
//
LoginSession::verify_forUser()->requires($login->admin);
$form = new SectionForm();
$getId = isset($_GET["id"]) ? $_GET["id"] : null;
$focId = isset($_GET["fid"]) ? $_GET["fid"] : null;
if (isset($getId)) {

	// Page entry from link
	if ($getId != "") {
		try {
			$section = TemplateAdminDao::getSection($getId, true);
			$form->setFromDatabase($section);
		} catch (SecurityException $e) {
			die($e->getMessage());
		}
	} else {
		$form->templateId = $_GET["tid"];		
	}
} else {

	// Page submitted from action
	try {
		$action = isset($_POST["action"]) ? $_POST["action"] : null;
		if ($action == " Exit ") {
			header("Location: adminTemplate.php?id=" . $_POST["templateId"]);
			exit;
		}
    if ($action == "Add New") {
      header("Location: adminPar.php?id=&sid=" . $_POST["id"] . "&tid=" . $_POST["templateId"]);
      exit;
    }
		if ($action == "Delete Section") {
			TemplateAdminDao::deleteSection($_POST["id"]);
			$form->templateId = $_POST["templateId"];
			$msg = "Section deleted.";
		} else {
			$form->setFromPost();
			$form->validate();
			$section = $form->buildSection();
			if ($section->id == "") {
				$form->id = TemplateAdminDao::addSection($section);
				$msg = "Section added.";
			} else {
				TemplateAdminDao::updateSection($section);
				$msg = "Section updated.";
			}
      if ($action == "Save Checks") {
        $count = count($_POST["done"]);
        if ($count > 0) {
          TemplateAdminDao::checkDonePars($form->id, $_POST["done"]);
          $focId = array_pop($_POST["done"]);
        }
        $msg = "Check(s) saved.";
      } else if ($action == "Clear") {
        TemplateAdminDao::clearDonePars($form->id);
        $msg = "Check(s) cleared.";
      }
			$section = TemplateAdminDao::getSection($form->id, true);
			$form->setFromDatabase($section);
		}
	} catch (ValidationException $e) {
		$errors = $e->getErrors();
	} catch (SecurityException $e) {
		die($e->getMessage());
	}
}
$update = (! isBlank($form->id));
$form->breadcrumb = TemplateAdminDao::buildSectionBreadcrumb($form->templateId); 
$form->sortOrders = TemplateAdminDao::buildSectionSortCombo($form->templateId, $form->id);
$form->sortOrder = TemplateAdminDao::denullifySortOrder($form->sortOrders, $form->sortOrder);
$focus = "uid";
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <!-- Copyright (c)2006 by LCD Solutions, Inc.  All rights reserved. -->
  <!-- http://www.clicktate.com -->
  <head>
    <title>clicktate : Section</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta http-equiv="Content-Script-Type" content="text/javascript" />
    <meta http-equiv="Content-Language" content="en-us" />
    <link rel="stylesheet" type="text/css" href="css/med.css?1" media="screen" />
    <script language="JavaScript1.2" src="js/admin.js"></script>
    <script language="JavaScript1.2" src="js/ui.js"></script>
    <script language='JavaScript1.2' src='js/_lcd_core.js?'></script>
    <script language='JavaScript1.2' src='js/_lcd_html.js?'></script>
  </head>
  <body>
    <?php include "inc/banner.php" ?>
    <form method="post" action="adminSection.php">
      <input type="hidden" name="id" value="<?=$form->id ?>">
      <input type="hidden" name="templateId" value="<?=$form->templateId ?>">
      <?php require_once "inc/errors.php" ?>
      <div id="breadcrumb">
        <?=$form->breadcrumb ?><br>
        <h1><?=($update) ? "" : "New " ?>Section <?=$form->uid ?></h1>
      </div>
      <div class="action">
        <input type="submit" value=" Save ">
        <?php if ($update) { ?>
          <?php if (isset($form->pars) && sizeof($form->pars) == 0) { ?>
            <span></span>
            <input type="submit" name="action" value="Delete Section" onclick="if (deleteCancelled()) return false">
          <?php } ?>
        <?php } ?> 
        <span></span>
        <input type="submit" name="action" value=" Exit ">
      </div>
      <div class="roundBox">
        <div class="roundTop"><img src="img/tl.gif"></div>
        <div class="roundContent">
          <table border=0 cellpadding=0 cellspacing=0>
            <tr>
              <td class="label first">Section ID</td>
              <td width=5></td>
              <td><input type="text" size="15" name="uid" value="<?=$form->uid ?>"></td>
              <td width=15></td>
              <td class="label">Name</td>
              <td width=5></td>
              <td><input type="text" size="30" name="name" value="<?=$form->name ?>"></td>
              <td width=15></td>
              <td class="label">Sort Position</td>
              <td width=5></td>
              <td>
                <?php renderCombo("sortOrder", $form->sortOrders, $form->sortOrder) ?>
              </td>
            </tr>
          </table>
          <table border=0 cellpadding=0 cellspacing=0 width=95%>
            <tr>
              <td class="label first">Title</td>
              <td width=5></td>
              <td><input type="text" size="110" name="title" value="<?=$form->title ?>"></td>
            </tr>
            <tr>
              <td class="label first" valign="top" style="padding-top:5px">Description</td>
              <td width=5></td>
              <td><textarea style="width:100%" cols="80" rows="3" name="desc"><?=$form->desc ?></textarea></td>
          </table>
        </div>
        <div class="roundBottom"><img src="img/bl.gif"></div>
      </div>
      <?php if (isset($form->pars)) { ?>
        <div class="roundBox">
         <div class="roundTitle">
            Paragraphs of This Section
          </div>
          <div class="roundContent">
            <div id="st" class="scrollTable" style="height:expression(document.body.offsetHeight - breadcrumb.offsetTop - 313)">
              <table border=0 cellpadding=0 cellspacing=0>
                <tr class="fixed">
                  <th>&nbsp;</th>
                  <th>UID</th>
                  <th>Description</th>
                  <th>Date Effective</th>
                  <th>Major?</th>
                  <th>Hidden?</th>
                  <th class="noDivider">Sort Order</th>
                </tr>
                <?php $altColor = true ?>
                <?php $lastUid = "" ?>
                <?php foreach ($form->pars as $k => $par) { ?>
                  <?php $new = ($lastUid != $par->uid) ?>
                  <?php if ($new) {$altColor = ! $altColor; $lastUid = $par->uid;} ?>
                  <?php $cls = ($altColor) ? "class=gray" : "" ?>
                  <?php if ($par->id == $focId) {$cls = "class=yellow";} ?>
                  <tr id="td<?=$par->id ?>" <?=$cls?> <?php if (! $par->current) {?>style="color:#707070"<?} else if ($par->injectOnly) { ?>style="color:green"<?php } else if (! $par->major) { ?>style="color:blue"<?php } ?>>
                    <td class="control">
                      <?php if ($new) { ?>
                      <input type="checkbox" name="done[]" value="<?=$par->id ?>" <?php checkedIf($par->noBreak) ?> <?php echoIf($par->noBreak, "", "style='border:1px solid red'") ?> title="Done">
                      <?php } ?>
                      <a href="adminPar.php?id=<?=$par->id ?>&tid=<?=$form->templateId ?><?=rnd() ?>" title="Edit this record"><img src="img/pencil.gif"></a>
                    </td>
                    <td><?=($new) ? $par->uid : ""?></td>
                    <td><?=$par->desc ?></td>
                    <td><?=($par->effective == '0000-00-00 00:00:00' ? "" : $par->effective) ?></td>
                    <td><?=$par->major ? "Yes" : "" ?></td>
                    <td><?=$par->injectOnly ? "Yes" : "" ?></td>
                    <td><?=($par->sortOrder == 32767) ? "" : $par->sortOrder ?></td>
                  </tr>
                <?php } ?>
              </table>
            </div>
            <div class="action nopad"> 
              <input type="submit" name="action" value="Add New">
              <span></span>
              <input type="submit" name="action" value="Save Checks">
              <input type="submit" name="action" value="Clear" onclick="if (clearCancelled()) return false">
            </div>
          </div>
          <div class="roundBottom"><img src="img/bl.gif"></div>
        </div>
      <?php } ?>
    </form>
  </body>
</html>
<?php if ($focId) { ?>
<script>
scrollTo("st", "td<?=$focId ?>", 20);
</script>
<?php } ?>
<?php require_once "inc/focus.php" ?>