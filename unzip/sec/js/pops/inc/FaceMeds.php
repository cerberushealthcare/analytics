<?
set_include_path('../../../');
require_once "inc/uiFunctions.php";
/**
 * Facesheet Meds
 * Controller: FaceMeds.js
 */
?>
<div id="fsp-med" class="pop" onmousedown="event.cancelBubble = true" style='width:750px'>
  <div id="fsp-med-cap" class="pop-cap">
    <div id="fsp-med-cap-text">
      Medications
    </div>
    <a href="javascript:FaceMeds.fpClose()" class="pop-close"></a>
  </div>
  <div class="pop-content" style='padding:0'>
    <div class='tabbar'></div>
    <div class='tabpanels' style='padding-top:0'>
      <div class='tabpanel' style='padding-top:7px'>
        <div class='reviewed' style='display:none'>
          <table>
            <tr>
              <td><label>Last reviewed:</label></td>
              <td><span class='ro' id='reviewed_span'></span></td>
              <td style='padding-left:10px'><a class='iclipboard' href='javascript:FaceMeds.fpReconcile()'>Reconcile With...</a>
            </tr>
          </table>
        </div>
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td style='padding-bottom:3px'>
              <ul id="med-filter-ul" class="topfilter"></ul>
            </td>
          </tr>
        </table>
        <div id="fsp-med-div" class="fstab" style="height:334px">
          <table id="fsp-med-tbl" class="fsb single">
            <thead>
              <tr id="fsp-med-head" class="fixed head">
                <th></th>
                <th style='width:85%'>Medication</th>
                <th style='width:16'></th>
                <th style='width:15%'>Status</th>
              </tr>
            </thead>
            <tbody id="fsp-med-tbody">
              <tr>
                <td>&nbsp;</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div id="med-cmd-erx" class="pop-cmd cmd-right" style="display:none">
          <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
              <td nowrap="nowrap">
                <a id='med-recon' href="javascript:" onclick="FaceMeds.fpReconcile()" class="cmd clipboard">Mark Reconciled...</a>
                <a id='med-dleg' style='display:none' href='javascript:FaceMeds.fpDeleteLegacy()' class='cmd delete-red'>Remove <i style='color:red;'>[Imported]</i></a>
              </td>
              <td style="width:100%">
                <span id='med-acts'>
                <a href="javascript:FaceMeds.fpPrint()" class="cmd rx">Print...</a>
                <span>&nbsp;</span>
                <a href='javascript:FaceMeds.fpNewCrop()' class="cmd erx">Update/Prescribe...</a>
                <span>&nbsp;</span>
                <span id='med-none'>
                  <a href="javascript:" onclick="FaceMeds.fpNone()" class="cmd none">"None Active"</a>
                  <span>&nbsp;</span>
                </span>
                </span>
                <a href="javascript:FaceMeds.fpClose()" class="cmd none">&nbsp;&nbsp;&nbsp;Exit&nbsp;&nbsp;&nbsp;</a>
              </td>
            </tr>
          </table>
        </div>
        <table id="med-cmd" border="0" cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td nowrap="nowrap">
              <div class="pop-cmd">
                <label>
                  With checked:
                </label>
                <a id="med-cmd-toggle" href='javascript:' onclick="FaceMeds.fpDeleteMeds()" class="cmd delete-red">Remove from List</a>
              </div>
            </td>
            <td style="width:100%">
              <div class="pop-cmd cmd-right">
                <a id="med-cmd-rx" href="javascript:FaceMeds.fpPrint()" class="cmd rx">Print...</a>
                <span>&nbsp;</span>
                <a id="med-cmd-add" href="javascript:FaceMeds.fpAddMed()" class="cmd new">Add a Med...</a>
                <span>&nbsp;</span>
                <a href="javascript:FaceMeds.fpClose()" class="cmd none">&nbsp;&nbsp;&nbsp;Exit&nbsp;&nbsp;&nbsp;</a>
              </div>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>
