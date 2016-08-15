<?
/**
 * Facesheet Immunizations
 * Controller: FaceImmun.js
 */
?>
<div id="fsp-imm" class="pop" onmousedown="event.cancelBubble = true" style='width:870px'>
  <div id="fsp-imm-cap" class="pop-cap">
    <div id="fsp-imm-cap-text">
      Immunizations
    </div>
    <a href="javascript:FaceImmun.fpClose()" class="pop-close"></a>
  </div>
  <div class="pop-content" style='padding:0'>
    <div class='tabbar'></div>
    <div class='tabpanels' style='padding-top:0'>
      <div class='tabpanel' style='padding-top:7px'>
        <div id="fsp-immh-div" class="fstab" style="height:300px">
          <table id="fsp-immh-tbl" class="fsg single grid bigpad">
            <thead>
              <tr class="fixed head">
                <th>Date Given</th>
                <th>Vaccine</th>
                <th>Trade Name</th>
                <th>Manufac &bull; Lot</th>
                <th>Dose &bull; Route</th>
              </tr>
            </thead>
            <tbody id="fsp-immh-tbody">
              <tr>
                <td colspan="5">&nbsp;</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="pop-cmd cmd-right">
          <table class='h'>
            <tr>
              <th>
                <a href="javascript:" onclick="FaceImmun.fpChart()" class="cmd rx">Print Certificate...</a>
                <span>&nbsp;</span>
                <a href="javascript:" onclick="FaceImmun.fpDownload()" class="cmd download2">Download</a>
              </th>
              <td>
                <span id='imm-act'>
                <a href="javascript:" onclick="FaceImmun.fpEdit()" class="cmd new">Add Immunization...</a>
                <span>&nbsp;</span>
                </span>
                <a href="javascript:FaceImmun.fpClose()" class="cmd none">&nbsp;&nbsp;Exit&nbsp;&nbsp;</a>
              </td>
            </tr>
          </table>
        </div>
      </div>
      <div class='tabpanel' style='padding-top:7px'>
        <div id="fsp-immh2-div" class="fstab" style="height:300px">
          <table id="fsp-immh2-tbl" class="fsg single grid bigpad">
            <thead>
              <tr class="fixed head">
                <th></th>
                <th>Date Given</th>
                <th>Vaccine</th>
                <th>Trade Name</th>
                <th>Manufac &bull; Lot</th>
                <th>Dose &bull; Route</th>
              </tr>
            </thead>
            <tbody id="fsp-immh2-tbody">
              <tr>
                <td colspan="5">&nbsp;</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="pop-cmd cmd-right">
          <table class='h'>
            <tr>
              <th>
                <a href="javascript:" onclick="FaceImmun.fpChart()" class="cmd rx">Print Certificate...</a>
                <span>&nbsp;</span>
                <a href="javascript:" onclick="FaceImmun.fpDownload()" class="cmd download2">Download</a>
              </th>
              <td>
                <span id='imm-act2'>
                <a href="javascript:" onclick="FaceImmun.fpEdit()" class="cmd new">Add Immunization...</a>
                <span>&nbsp;</span>
                </span>
                <a href="javascript:FaceImmun.fpClose()" class="cmd none">&nbsp;&nbsp;Exit&nbsp;&nbsp;</a>
              </td>
            </tr>
          </table>
        </div>
      </div>
      <div class='tabpanel' style='padding-top:7px'>
        <div id="fsp-imms-div">
        </div>
      </div>
    </div>
  </div>
</div>
<?
/**
 * Immunization Entry 
 */
?>
<div id="pop-imme" class="pop" onmousedown="event.cancelBubble = true" style='width:700px'>
  <div id="pop-imme-cap" class="pop-cap">
    <div id="pop-imme-cap-text">
      Immunization Entry
    </div>
    <a href="javascript:FaceImmunEntry.fpClose()" class="pop-close"></a>
  </div>
  <div class="pop-content">
    <div class="pop-frame-content">
      <ul id="ul-imme-date" class='entry mb10'>
      </ul>
      <div id="imme-tui">
      </div>
      <div id='imme-audit' class='mt5 rj'>
        Note: This immunization has been updated. <a href="javascript:FaceImmunEntry.fpAudit()">Click to review audit log</a>
      </div>
    </div>
    <div class="pop-cmd">
      <a href="javascript:" onclick="FaceImmunEntry.fpSave()" class="cmd save">Save Changes</a>
      <span id="imme-delete-span">
        <span>&nbsp;</span>
        <a href="javascript:" onclick="FaceImmunEntry.fpDelete()" class="cmd delete-red">Delete</a>
      </span>
      <span>&nbsp;</span>
      <a href="javascript:Pop.close()" class="cmd none">Cancel</a>
      <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
      <a id="imme-print" href="javascript:FaceImmunEntry.fpPrint()" class="cmd rx">Print...</a>
      </div>
  </div>
</div>
<?
/**
 * Immunization Chart 
 */
?>
<div id="pop-immc" class="pop" onmousedown="event.cancelBubble = true" style='width:660px'>
  <div id="pop-immc-cap" class="pop-cap">
    <div id="pop-immc-cap-text">
      Immunization Chart
    </div>
    <a href="javascript:Pop.close()" class="pop-close"></a>
  </div>
  <div class="pop-content">
    <div id="immc-div" class="fstab" style="height:400px">
      <table id="immc-tbl" class="fsgr single grid" style='border-bottom:none'>
        <tbody id="immc-tbody">
        </tbody>
      </table>
    </div>
    <div class="pop-cmd">
      <a href="javascript:FaceImmunChart.fpPrint()" class="cmd rx">Print Certificate...</a>
      <span>&nbsp;</span>
      <a href="javascript:Pop.close()" class="cmd none">&nbsp;&nbsp;Exit&nbsp;&nbsp;</a>
    </div>
  </div>
</div>
<div id='immc-prt' class='fstab noscroll' style='display:none'>
  <table id="immc-tbl-prt" class="fsgr single grid">
    <tbody id="immc-tbody-prt">
    </tbody>
  </table>
</div>
<?
/**
 * vIS Picker 
 */
?>
<div id="pop-vis" class="pop" onmousedown="event.cancelBubble = true" style='width:460px'>
  <div id="pop-vis-cap" class="pop-cap">
    <div id="pop-vis-cap-text">
      VIS Selector
    </div>
    <a href="javascript:Pop.close()" class="pop-close"></a>
  </div>
  <div class="pop-content">
    <div class="fstab" style="height:500px">
      <table class='fsgr'>
        <tr class='head fixed'>
          <th>Name</th>
          <th>Date</th>
          <th>&nbsp;</th>
        </tr>
        <tr>
          <td><a href='javascript:' onclick='FaceImmunEntry.fpVisSel(this)' class='fsnone'>Multiple Vaccines</a></td>
          <td>11/5/15</td>
          <td><a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/multi.pdf' target="_blank" class='action tpdf'>VIS</a>
        </tr>
        <tr>
          <td><a href='javascript:' onclick='FaceImmunEntry.fpVisSel(this)' class='fsnone'>Adenovirus</a></td>
          <td>6/11/14</td>
          <td><a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/adenovirus.pdf' target="_blank" class='action tpdf'>VIS</a>
        </tr>
        <tr>
          <td><a href='javascript:' onclick='FaceImmunEntry.fpVisSel(this)' class='fsnone'>Anthrax
          </a></td>
          <td>3/10/10</td>
          <td><a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/anthrax.pdf' target="_blank" class='action tpdf'>VIS</a>
        </tr>
        <tr>
          <td><a href='javascript:' onclick='FaceImmunEntry.fpVisSel(this)' class='fsnone'>Diphtheria/Tetanus/Pertussis (DTaP)
          </a></td>
          <td>5/17/07</td>
          <td><a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/dtap.pdf' target="_blank" class='action tpdf'>VIS</a>
        </tr>
        <tr>
          <td><a href='javascript:' onclick='FaceImmunEntry.fpVisSel(this)' class='fsnone'>Hepatitis A
          </a></td>
          <td>10/25/11
          </td>
          <td><a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/hep-a.pdf' target="_blank" class='action tpdf'>VIS</a>
        </tr>
        <tr>
          <td><a href='javascript:' onclick='FaceImmunEntry.fpVisSel(this)' class='fsnone'>Hepatitis B 
          </a></td>
          <td>2/2/12
          </td>
          <td><a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/hep-b.pdf' target="_blank" class='action tpdf'>VIS</a>
        </tr>
        <tr>
          <td><a href='javascript:' onclick='FaceImmunEntry.fpVisSel(this)' class='fsnone'>Hib (Haemophilus Influenzae type b) 
          </a></td>
          <td>4/2/15
          </td>
          <td><a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/hib.pdf' target="_blank" class='action tpdf'>VIS</a>
        </tr>
        <tr>
          <td><a href='javascript:' onclick='FaceImmunEntry.fpVisSel(this)' class='fsnone'>HPV (Human Papillomavirus Vaccine)    
          </a></td>
          <td>5/17/13<br>4/15/15<br>5/3/11<b
          </td>
          <td><a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/hpv-gardasil.pdf' target="_blank" class='action tpdf'>VIS&nbsp;Gardasil</a><br>
          <a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/hpv-gardasil-9.pdf' target="_blank" class='action tpdf'>VIS&nbsp;Gardasil 9</a><br>
          <a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/hpv-cervarix.pdf' target="_blank" class='action tpdf'>VIS&nbsp;Cervarix</a>
        </tr>
        <tr>
          <td><a href='javascript:' onclick='FaceImmunEntry.fpVisSel(this)' class='fsnone'>Influenza Vaccine - Live, Intranasal  
          </a></td>
          <td>8/7/15
          </td>
          <td><a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/flulive.pdf' target="_blank" class='action tpdf'>VIS</a>
        </tr>
        <tr>
          <td><a href='javascript:' onclick='FaceImmunEntry.fpVisSel(this)' class='fsnone'>Influenza Vaccine - Inactivated   
          </a></td>
          <td>8/7/15
          </td>
          <td><a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/flu.pdf' target="_blank" class='action tpdf'>VIS<br>
          <a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/flu-largetype.pdf' target="_blank" class='action tpdf'>VIS&nbsp;Large&nbsp;Type
          </a>
        </tr>
        <tr>
          <td><a href='javascript:' onclick='FaceImmunEntry.fpVisSel(this)' class='fsnone'>Japanese Encephalitis 
          </a></td>
          <td>1/24/14
          </td>
          <td><a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/je-ixiaro.pdf' target="_blank" class='action tpdf'>VIS</a>
        </tr>
        <tr>
          <td><a href='javascript:' onclick='FaceImmunEntry.fpVisSel(this)' class='fsnone'>Measles/Mumps/Rubella (MMR)  
          </a></td>
          <td>4/20/12
          </td>
          <td><a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/mmr.pdf' target="_blank" class='action tpdf'>VIS</a>
        </tr>
        <tr>
          <td><a href='javascript:' onclick='FaceImmunEntry.fpVisSel(this)' class='fsnone'>Measles/Mumps/Rubella & Varicella (MMRV)    
          </a></td>
          <td>5/21/10
          </td>
          <td><a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/mmrv.pdf' target="_blank" class='action tpdf'>VIS</a>
        </tr>
        <tr>
          <td><a href='javascript:' onclick='FaceImmunEntry.fpVisSel(this)' class='fsnone'>Meningococcal     
          </a></td>
          <td>10/14/11
          </td>
          <td><a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/mening.pdf' target="_blank" class='action tpdf'>VIS</a>
        </tr>
        <tr>
          <td><a href='javascript:' onclick='FaceImmunEntry.fpVisSel(this)' class='fsnone'>Serogroup B Meningococcal (MenB)     
          </a></td>
          <td>8/14/15
          </td>
          <td><a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/mening-serogroup.pdf' target="_blank" class='action tpdf'>VIS</a>
        </tr>
        <tr>
          <td><a href='javascript:' onclick='FaceImmunEntry.fpVisSel(this)' class='fsnone'>Pneumococcal Conjugate (PCV13)    
          </a></td>
          <td>11/5/15
          </td>
          <td><a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/pcv13.pdf' target="_blank" class='action tpdf'>VIS</a>
        </tr>
        <tr>
          <td><a href='javascript:' onclick='FaceImmunEntry.fpVisSel(this)' class='fsnone'>Pneumococcal Polysaccharide (PPSV23)   
          </a></td>
          <td>4/24/15
          </td>
          <td><a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/ppv.pdf' target="_blank" class='action tpdf'>VIS</a>
        </tr>
        <tr>
          <td><a href='javascript:' onclick='FaceImmunEntry.fpVisSel(this)' class='fsnone'>Polio     
          </a></td>
          <td>11/8/11
          </td>
          <td><a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/ipv.pdf' target="_blank" class='action tpdf'>VIS</a>
        </tr>
        <tr>
          <td><a href='javascript:' onclick='FaceImmunEntry.fpVisSel(this)' class='fsnone'>Rabies    
          </a></td>
          <td>10/6/09
          </td>
          <td><a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/rabies.pdf' target="_blank" class='action tpdf'>VIS</a>
        </tr>
        <tr>
          <td><a href='javascript:' onclick='FaceImmunEntry.fpVisSel(this)' class='fsnone'>Rotavirus  
          </a></td>
          <td>4/15/15
          </td>
          <td><a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/rotavirus.pdf' target="_blank" class='action tpdf'>VIS</a>
        </tr>
        <tr>
          <td><a href='javascript:' onclick='FaceImmunEntry.fpVisSel(this)' class='fsnone'>Shingles (Herpes Zoster)    
          </a></td>
          <td>10/6/09
          </td>
          <td><a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/shingles.pdf' target="_blank" class='action tpdf'>VIS</a>
        </tr>
        <tr>
          <td><a href='javascript:' onclick='FaceImmunEntry.fpVisSel(this)' class='fsnone'>Smallpox (Vaccinia)   
          </a></td>
          <td>8/31/07
          </td>
          <td><a href="http://www.bt.cdc.gov/agent/smallpox/vaccination/pdf/ACAM2000MedicationGuide-31Aug2007.pdf" target="_blank" class='action tpdf'>Med&nbsp;Guide</a>
        </tr>
        <tr>
          <td><a href='javascript:' onclick='FaceImmunEntry.fpVisSel(this)' class='fsnone'>Td (Tetanus, Diphtheria)    
          </a></td>
          <td>2/24/15
          </td>
          <td><a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/td.pdf' target="_blank" class='action tpdf'>VIS</a>
        </tr>
        <tr>
          <td><a href='javascript:' onclick='FaceImmunEntry.fpVisSel(this)' class='fsnone'>Tdap (Tetanus, Diphtheria, Pertussis)    
          </a></td>
          <td>2/24/15
          </td>
          <td><a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/tdap.pdf' target="_blank" class='action tpdf'>VIS</a>
        </tr>
        <tr>
          <td><a href='javascript:' onclick='FaceImmunEntry.fpVisSel(this)' class='fsnone'>Typhoid   
          </a></td>
          <td>5/29/12
          </td>
          <td><a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/typhoid.pdf' target="_blank" class='action tpdf'>VIS</a>
        </tr>
        <tr>
          <td><a href='javascript:' onclick='FaceImmunEntry.fpVisSel(this)' class='fsnone'>Varicella (Chickenpox)    
          </a></td>
          <td>3/13/08
          </td>
          <td><a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/varicella.pdf' target="_blank" class='action tpdf'>VIS</a>
        </tr>
        <tr>
          <td><a href='javascript:' onclick='FaceImmunEntry.fpVisSel(this)' class='fsnone'>Yellow Fever    
          </a></td>
          <td>3/30/11
          </td>
          <td><a href='http://www.cdc.gov/vaccines/hcp/vis/vis-statements/yf.pdf' target="_blank" class='action tpdf'>VIS</a>
        </tr>
      </table>
    </div>
    <div class="pop-cmd">
      <a href="javascript:Pop.close()" class="cmd none">Cancel</a>
    </div>
  </div>
</div>
<?php 
function renderCal($id, $value = "", $readOnly = true) {
  echo "<input id='$id' type='text' size='10' value='$value' ";
  if ($readOnly) {
    echo "readonly='readonly' style='cursor:hand' onclick=\"Pop.Calendar.showFromTextbox('$id')\" ";
  }
  echo "/>&nbsp;";
  echo "<a href='#' onclick=\"Pop.Calendar.showFromTextbox('$id');return false\" class='cal'></a>";
}
?>