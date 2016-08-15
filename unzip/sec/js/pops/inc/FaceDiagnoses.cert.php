<?
/**
 * Facesheet Diagnoses
 * Controller: FaceDiagnoses.js
 */
?>
<div id="fsp-dia" class="pop" style='width:820px'>
  <div id="fsp-dia-cap" class="pop-cap">
    <div id="fsp-dia-cap-text">
      Diagnoses
    </div>
    <a href="javascript:FaceDiagnoses.fpClose()" class="pop-close"></a>
  </div>
  <div class="pop-content" style='padding:0'>
    <div class='tabbar'></div>
    <div class='tabpanels' style='padding-top:0'>
      <div class='tabpanel' style='padding-top:7px'>
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td style='padding-bottom:3px'>
              <ul id="dia-filter-ul" class="topfilter"></ul>
            </td>
          </tr>
          <tr>
            <td>
              <div id="fsp-dia-div" class="fstab" style="height:350px">
                <table id="fsp-dia-tbl" class="fsy single">
                  <thead>
                    <tr id="fsp-dia-head" class="fixed head">
                      <th style='width:60%'>Diagnosis</th>
                      <th style='width:15%'>Status</th>
                      <th style='width:25%'>Effective</th>
                    </tr>
                  </thead>
                  <tbody id="fsp-dia-tbody">
                  </tbody>
                </table>
              </div>
              <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:5px">
                <tr>
                  <td nowrap="nowrap" style="padding-left:10px">
                    <div class="pop-cmd">
                      <a href="javascript:" onclick="FaceDiagnoses.fpDownload()" class="cmd download2">Download</a>
                      <label style='display:none'>
                        With checked:
                      </label>
                      <a style='display:none' id="dia-cmd-toggle" href="javascript:" onclick="FaceDiagnoses.fpDeleteChecked()" class="cmd delete-red">Remove from List</a>
                    </div>
                  </td>
                  <td style="width:100%">
                    <div class="pop-cmd cmd-right">
                      <span id='dia-act'>
                      <a id="dia-cmd-add" href="javascript:" onclick="FaceDiagnoses.fpEdit()" class="cmd new">Add a Diagnosis...</a>
                      <span>&nbsp;</span>
                      <span id='dia-none'>
                        <a href="javascript:" onclick="FaceDiagnoses.fpNone()" class="cmd none">"None Active"</a>
                        <span>&nbsp;</span>
                      </span>
                      </span>
                      <a href="javascript:FaceDiagnoses.fpClose()" class="cmd none">&nbsp;&nbsp;Exit&nbsp;&nbsp;</a>
                    </div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </div>
      <div class='tabpanel' style='padding-top:7px'>
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td style='padding-bottom:3px'>
              <ul id="diah-filter-ul" class="topfilter"></ul>
            </td>
          </tr>
          <tr>
            <td>
              <div id="fsp-diah-div" class="fstab" style="height:350px">
                <table id="fsp-diah-tbl" class="fsy single">
                  <thead>
                    <tr id="diah-head" class="fixed head">
                      <th>Date</th>
                      <th>Document</th>
                      <th>Diagnosis</th>
                    </tr>
                  </thead>
                  <tbody id="fsp-diah-tbody">
                  </tbody>
                </table>
              </div>
            </td>
          </tr>
        </table>
        <div class="pop-cmd cmd-right">
          <a href="javascript:FaceDiagnoses.fpClose()" class="cmd none">&nbsp;&nbsp;Exit&nbsp;&nbsp;</a>
        </div>
      </div>
    </div>
  </div>
</div>
<?
/**
 * Diagnoses Entry
 */
?>
<div id="pop-de" class="pop" style="width:710px">
  <div id="pop-de-cap" class="pop-cap">
    <div id="pop-de-cap-text">
      Clicktate - Diagnosis Entry
    </div>
    <a href="javascript:FaceDiagEntry.fpClose()" class="pop-close"></a>
  </div>
  <div class="pop-content">
    <div class="pop-frame">
      <div class="pop-frame-content">
        <ul id="pop-de-form">
        </ul>
      </div>
    </div>
    <div class="pop-cmd">
      <a href="javascript:" onclick="FaceDiagEntry.fpSave()" class="cmd save">Save Changes</a>
      <span id="de-delete-span">
        <span>&nbsp;</span>
        <a href="javascript:" onclick="FaceDiagEntry.fpAddMedHx()" class="cmd new">Copy to Med History</a>
        <span>&nbsp;</span>
        <a href="javascript:" onclick="FaceDiagEntry.fpDelete()" class="cmd delete">Delete</a>
      </span>
      <span>&nbsp;</span>
      <a href="javascript:" onclick="FaceDiagEntry.fpClose()" class="cmd none">Cancel</a>
    </div>
  </div>
</div>
