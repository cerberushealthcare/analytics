<?
ob_start('ob_gzhandler');
require_once "php/data/LoginSession.php";
require_once "inc/uiFunctions.php";
require_once "php/forms/DocumentsForm.php";
require_once "php/data/db/User.php";
//
LoginSession::verify_forUser()->requires($login->Role->Artifact->noteRead);
$form = new DocumentsForm("documents.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Strict//EN">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <?php renderHead("Document Manager") ?>
    <link rel="stylesheet" type="text/css" href="css/xb/_clicktate.css?<?=Version::getUrlSuffix() ?>" media="screen" />
    <link rel='stylesheet' type='text/css' href='css/xb/Pop.css?<?=Version::getUrlSuffix() ?>' />
    <link rel='stylesheet' type='text/css' href='css/xb/EntryForm.css?<?=Version::getUrlSuffix() ?>' />
    <link rel='stylesheet' type='text/css' href='css/xb/template-pops.css?<?=Version::getUrlSuffix() ?>' />
    <link rel='stylesheet' type='text/css' href='css/data-tables.css?<?=Version::getUrlSuffix() ?>' />
    <link rel='stylesheet' type='text/css' href='css/xb/documents.css?<?=Version::getUrlSuffix() ?>' />
    <?php if ($login->isPapyrus()) { ?>
    <link rel="stylesheet" type="text/css" href="css/papyrus.css?<?=Version::getUrlSuffix() ?>" />
    <?php } ?>
    <script type='text/javascript' src='js/pages/Pop.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language="JavaScript1.2" src="js/old-ajax.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/yahoo-min.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/json.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/connection-min.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/new-open.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/ui.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/documents.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/_ui/PatientSelector.js?<?=Version::getUrlSuffix() ?>"></script>
    <script type='text/javascript' src='js/components/TableLoader.js?<?=Version::getUrlSuffix() ?>'></script>
    <script type='text/javascript' src='js/libs/DateUi.js?<?=Version::getUrlSuffix() ?>'></script>
    <script type='text/javascript' src='js/libs/ClientUi.js?<?=Version::getUrlSuffix() ?>'></script>
    <script type='text/javascript' src='js/components/TableLoader.js?<?=Version::getUrlSuffix() ?>'></script>
    <script type='text/javascript' src='js/components/TabBar.js?<?=Version::getUrlSuffix() ?>'></script>
    <script type='text/javascript' src='js/components/TemplateUi.js?<?=Version::getUrlSuffix() ?>'></script>
    <script type='text/javascript' src='js/components/TemplateForm.js?<?=Version::getUrlSuffix() ?>'></script>
    <script type='text/javascript' src='js/components/CmdBar.js?<?=Version::getUrlSuffix() ?>'></script>
    <script type='text/javascript' src='js/components/EntryForm.js?<?=Version::getUrlSuffix() ?>'></script>
    <script type='text/javascript' src='js/components/DateInput.js?<?=Version::getUrlSuffix() ?>'></script>
    <script type='text/javascript' src='js/components/ProfileLoader.js?<?=Version::getUrlSuffix() ?>'></script>
    <script type='text/javascript' src='js/libs/DocUi.js?<?=Version::getUrlSuffix() ?>'></script>
  </head>
  <body onfocus="pageFocus()">
    <div id="curtain"></div>
    <form id="frm" method="post" action="documents.php">
      <div id="bodyContainer">
        <?php include "inc/header.php" ?>
        <div id='bodyContent' class="content">
          <table border="0" cellpadding="0" cellspacing="0" style="width:100%">
            <tr>
              <td>
                <table border="0" cellpadding="0" cellspacing="0">
                  <tr>
                    <td><h1>Document Manager</h1></td>
                  </tr>
                </table>
                <div id="searching">
                  Showing:
                  <?php if ($form->isNotesView()) { ?>
                    <?php if (! $form->isUnsignedView()) { ?>
                      <em>All notes for</em>
                    <?php } else { ?>
                      <em>Unsigned notes for</em>
                    <?php } ?>
                    <?php renderCombo("users", $form->users, $form->userId, "onchange='userChange()'") ?>
                    &nbsp;
                    <?php if (! $form->isUnsignedView()) { ?>
                      <a href="documents.php?pf1=closed&pfv1=0&pfe1=2&u=<?=$form->userId ?>" class="icon big view">Show <b>unsigned notes only</b></a>
                    <?php } else { ?>
                      <a href="documents.php?u=<?=$form->userId ?>" class="icon big view">Show <b>all notes</b></a>
                    <?php } ?>
                  <?php } else { ?>
                    <em>Customized templates</em>
                  <?php } ?>
                </div>
              </td>
              <td style="text-align:right; vertical-align:bottom; padding-bottom:2px">
                <?php if ($form->isNotesView()) { ?>
                  <?php if ($login->Role->Artifact->templates) { ?>
                    <a class="icon big go" href="documents.php?v=1">Manage <b>custom templates</b></a>
                  <?php } ?>
                <?php } else { ?>
                  <a class="icon big go" href="documents.php?v=0">Manage <b>notes</b></a>
                <?php } ?>
              </td>
            </tr>
          </table>
          <?php renderBoxStart("wide small-pad") ?>
            <div class="nav">
              <table cellpadding="0" cellspacing="0">
                <tr>
                  <td class="prev">
                    <?=$form->prevAnchorHtml() ?>
                  </td>
                  <td class="nav">
                    <?=$form->recordNumbers("") ?>
                  </td>
                  <td class="next">
                    <?=$form->nextAnchorHtml() ?>
                  </td>
                </tr>
              </table>
            </div>
            <div class="gridsheet">
              <?php if ($form->isNotesView()) { ?>
                <table>
                  <tr>
                    <?=$form->sortableHeader("title", "Document") ?>
                    <?=$form->sortableHeader("date_service date_updated", "DOS") ?>
                    <?=$form->sortableHeader("last_name", "Patient") ?>
                    <?=$form->sortableHeader("date_created", "Created") ?>
                    <?=$form->sortableHeader("date_updated", "Last Updated") ?>
                    <?=$form->sortableHeader("send_to_name", "Send To") ?>
                  </tr>
                  <?php foreach ($form->rows as $row) { ?>
                    <tr class="<?=$row->trClass ?>">
                      <td width="20%" class="last">
                        <?=$row->noteAnchorHtml ?>
                      </td>
                      <td width="10%" class="last"><?=$row->dos ?></td>
                      <td width="20%" class="last">
                        <?php if ($login->Role->Patient->facesheet) { ?>
                          <a href="javascript:showClient(<?=$row->stub->cid ?>)" class="icon <?=echoIf($row->stub->clientSex == Client0::MALE, "umale", "ufemale") ?>">
                            <?=$row->stub->clientName ?>
                          </a>
                        <?php } else { ?>
                          <?=$row->stub->clientName ?>
                        <?php } ?>
                      </td>
                      <td width="25%" class="last">
                        <?=$row->createdText ?>
                      </td>
                      <td width="25%" class="last">
                        <?=$row->updatedText ?>
                      </td>
                      <td width="25$" class="last">
                        <?=User0::getInits($row->stub->sendTo) ?>
                      </td>
                    </tr>
                  <?php } ?>
                </table>
              <?php } else { ?>
                <table>
                  <tr>
                    <?=$form->sortableHeader("name", "Custom Template") ?>
                    <?=$form->sortableHeader("template_name", "Based On") ?>
                    <?=$form->sortableHeader("date_created", "Created") ?>
                    <?=$form->sortableHeader("date_updated", "Last Updated") ?>
                  </tr>
                  <?php foreach ($form->rows as $row) { ?>
                    <tr class="<?=$row->trClass ?>">
                      <td width="30%" class="last">
                        <a href="javascript:goPreset(<?=$row->preset->id ?>)" class="icon edit-red">
                          <?=$row->preset->name ?>
                        </a>
                      </td>
                      <td width="20%" class="last"><?=$row->preset->templateName ?></td>
                      <td width="25%" class="last">
                        <?=$row->createdText ?>
                      </td>
                      <td width="25%" class="last">
                        <?=$row->updatedText ?>
                      </td>
                    </tr>
                  <?php } ?>
                </table>
              <?php } ?>
            </div>
            <div style="padding:10px 0 0 5px; text-align:center">
              <?php if ($form->isNotesView()) { ?>
                <?php if ($login->Role->Artifact->noteCreate) { ?>
                  <a href="javascript:newDocument()" class="cmd new">Create New Document...</a>
                <?php } ?>
              <?php } else { ?>
                <?php if ($login->Role->Artifact->templates) { ?>
                  <a href="javascript:newTemplate()" class="cmd new">Create New Custom Template</a>
                <?php } ?>
              <?php } ?>
            </div>
          <?php renderBoxEnd() ?>
        </div>
        <div id='bottom'><img src='img/brb.png' /></div>
      </div>
    <?php include "inc/ajax-pops/working-confirm.php" ?>
    <?php include "inc/ajax-pops/calendar.php" ?>
    <?php include "js/pops/inc/PatientSelector.php" ?>
    <?php include "inc/ajax-pops/new-open.php" ?>
    <?php include "inc/footer.php" ?>
    </form>
  </body>
  <?php CONSTANTS('Client') ?>
<script>
Page.setEvents();
<?php timeoutCallbackJs() ?>
var refreshOnFocus = false;
var curl = "<?=$form->getCurrentUrl() ?>";
<?php if ($form->pop != null) { ?>
newDocument();
<?php } ?>
function newDocument() {
  PatientSelector.pop(function(c) {
    showNewNote(c.clientId, c.name);
  })
}
</script>
</html>
