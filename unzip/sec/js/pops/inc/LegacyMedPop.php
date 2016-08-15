<?
// Medicine picker
// Before showing: loadMedHistory(meds)  // JDataMed[] 
// For updates: showMed(id, name, amt, freq, asNeed, meals, route, length, disp) 
// For adds: showMed()
// Callbacks: medOkCallback(med)
//            medDeleteCallback(medId)
?>
<div id="popMedLegacy" class="pop" style="width:488px">
  <div id="popMed-cap" class="pop-cap" unselectable="on">  
    <div id="medCap" unselectable="on">
      Med Selector
    </div>
    <a href="javascript:Pop.close()" class="pop-close"></a>
  </div>
  <div class="pop-content p5">
    <div id="popM" class="med">
      <table border=0 cellpadding=0 cellspacing=0>
        <tr>
          <th>Name / Strength</th>
          <th></th>
        </tr>
        <tr>
          <td><input id="medName" type="text" size="55" onkeyup="QPopLegacyMed.$.name_onkeypress()" onfocus="QPopLegacyMed.$.medShow('medName', 0)"></td>
          <td><input id="medSearch" class="medSearch" type="button" value="Search..." onclick="QPopLegacyMed.$.search_onclick()" /></td>
        </tr>
      </table>
      <div id="popM2">
        <table border=0 cellpadding=0 cellspacing=0>
          <tr style="padding-top:4px">
            <th>Amount</th>
            <th></th>
            <th>Freq</th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
          </tr>
          <tr>
            <td><input id="medAmt" type="text" size="12" onfocus="QPopLegacyMed.$.medShow('medAmt', 1)"></td>
            <td></td>
            <td><input id="medFreq" type="text" size="15" onfocus="QPopLegacyMed.$.medShow('medFreq', 2)"></td>
            <td></td>
            <td><? renderLabelCheck2('medAsNeed', 'As needed') ?></td>
            <td></td>
            <td><? renderLabelCheck2('medMeals', 'With meals') ?></td>
          </tr>
        </table>
        <table border=0 cellpadding=0 cellspacing=0>
          <tr style="padding-top:4px">
            <th>Route</th>
            <th></th>
            <th>Length</th>
            <th></th>
            <th></th>
            <th></th>
          </tr>
          <tr>
            <td><input id="medRoute" type="text" size="18" onfocus="QPopLegacyMed.$.medShow('medRoute', 3)"></td>
            <td></td>
            <td><input id="medLength" type="text" size="15" onfocus="QPopLegacyMed.$.medShow('medLength', 4)"></td>
            <td></td>
            <td><input style='display:none' id="medDisp" type="text" size="15" onkeydown="if(event.keyCode==9){_$('medName').setFocus()}" onfocus="QPopLegacyMed.$.disp_onfocus()"></td>
          </tr>
        </table>
      </div>
      <div id="m0" style="display:block">
        <div id="medOptTitle">Name Search Results</div>
        <div id="medList">
          <div id="medListTitle"></div>
          <div id="medListNone"></div>
          <ul id="medListUl"></ul>
          <div id="medListFoot"></div>
        </div>
      </div>
      <div id="m1" style="display:none">
        <div id="medOptTitle">Amount Options</div>
        <div class="medOptions">
          <ul class="medbreak">
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">1/4</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">1/3</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">1/2</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">1/2 - 1</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">1</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">1 1/2</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">2</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">3</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">4</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">5</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">6</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">7</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">8</a></li>
          </ul>
          <ul class="medbreak">
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">1/4 tsp</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">1/3 tsp</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">1/2 tsp</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">3/4 tsp</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">1 tsp</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">1 1/4 tsp</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">1 1/3 tsp</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">1 1/2 tsp</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">1 3/4 tsp</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">2 tsp</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">3 tsp</a></li>
          </ul>
          <ul class="medbreak">
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">0.4 ml</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">0.5 ml</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">0.8 ml</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">1 ml</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">1.2 ml</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">1 1/2 ml</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">1.6 ml</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">2 ml</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">2 1/2 ml</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">3 ml</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">3 1/2 ml</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">4 ml</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">4 1/2 ml</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">5 ml</a></li>
          </ul>
          <ul class="medbreak">
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">1 drop</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">2 drops</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">3 drops</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">4 drops</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">5 drops</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">10 drops</a></li>
            <li><a style="visibility:hidden" href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">none</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">1 puff</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">2 puffs</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">4 puffs</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">8 puffs</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">16 puffs</a></li>
            <li><a style="visibility:hidden" href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">none</a></li>
            <li><a style="visibility:hidden" href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">none</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">1 spray</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">2 sprays</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">3 sprays</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">4 sprays</a></li>
            <li><a style="visibility:hidden" href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">none</a></li>
            <li><a style="visibility:hidden" href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">none</a></li>
            <li><a style="visibility:hidden" href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">none</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">1/2 capful</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">1 capful</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">17 gms</a></li>
          </ul>
          <ul class="medbreak">
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedAmt(this); return false">as directed</a></li>
          </ul>
        </div>
      </div>
      <div id="m2" style="display:none">
        <div id="medOptTitle">Frequency Options</div>
        <div class="medOptions">
          <ul class="medbreak">
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedFreq(this); return false">every hour</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedFreq(this); return false">every 2 hours</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedFreq(this); return false">every 3 hours</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedFreq(this); return false">every 4 hours</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedFreq(this); return false">every 6 hours</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedFreq(this); return false">every 8 hours</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedFreq(this); return false">every 12 hours</a></li>
          </ul>
          <ul class="medbreak">
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedFreq(this); return false">daily<br>&nbsp;</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedFreq(this); return false">BID<br>&nbsp;</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedFreq(this); return false">TID<br>&nbsp;</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedFreq(this); return false">QID<br>&nbsp;</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedFreq(this); return false">five times daily</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedFreq(this); return false">QAM<br>&nbsp;</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedFreq(this); return false">QHS<br>&nbsp;</a></li>
          </ul>
          <ul class="medbreak">
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedFreq(this); return false">every 2 days</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedFreq(this); return false">every 3 days</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedFreq(this); return false">MWF<br>&nbsp;</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedFreq(this); return false">Mon/Thur<br>&nbsp;</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedFreq(this); return false">once weekly</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedFreq(this); return false">twice weekly</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedFreq(this); return false">3 times weekly</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedFreq(this); return false">once monthly</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedFreq(this); return false">every 2 weeks</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedFreq(this); return false">every 10 days</a></li>
            <li><a href="javascript:" onclick="QPopLegacyMed.$.upMedFreq(this); return false">every 5 minutes</a></li>
          </ul>
          <ul class="medbreak">
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedFreq(this); return false">as directed</a></li>
          </ul>
        </div>
      </div>
      <div id="m3" style="display:none">
        <div id="medOptTitle">Route Options</div>
        <div class="medOptions">
          <ul class="medbreak">
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedRoute(this); return false">orally</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedRoute(this); return false">rectally</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedRoute(this); return false">intravaginally</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedRoute(this); return false">inhaled</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedRoute(this); return false">subcutaneously</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedRoute(this); return false">transdermally</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedRoute(this); return false">IV</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedRoute(this); return false">on the nails</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedRoute(this); return false">on the skin</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedRoute(this); return false">in the left eye</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedRoute(this); return false">in the right eye</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedRoute(this); return false">in both eyes</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedRoute(this); return false">in the left ear</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedRoute(this); return false">in the right ear</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedRoute(this); return false">in both ears</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedRoute(this); return false">in the left nostril</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedRoute(this); return false">in the right nostril</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedRoute(this); return false">in both nostrils</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedRoute(this); return false">in alternating nostrils</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedRoute(this); return false">IM</a></li>
          </ul>
        </div>
      </div>
      <div id="m4" style="display:none">
        <div id="medOptTitle">Length Options</div>
        <div class="medOptions">
          <ul class="medbreak">
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedLength(this); return false">1 day</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedLength(this); return false">2 days</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedLength(this); return false">3 days</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedLength(this); return false">4 days</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedLength(this); return false">5 days</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedLength(this); return false">6 days</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedLength(this); return false">7 days</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedLength(this); return false">10 days</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedLength(this); return false">12 days</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedLength(this); return false">14 days</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedLength(this); return false">21 days</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedLength(this); return false">28 days</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedLength(this); return false">30 days</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedLength(this); return false">60 days</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedLength(this); return false">90 days</a></li>
            <li><a class="big" href="javascript:" onclick="QPopLegacyMed.$.upMedLength(this); return false">long-term</a></li>
          </ul>
        </div>
      </div>
      <div class="pop-cmd">
        <a id="medOK" class="cmd save" href="javascript:" onclick='QPopLegacyMed.$.medOk(); return false'>Save Changes</a>
        <span id="med-delete-span" style='display:none'>
          <span>&nbsp;</span>
          <a id="medDelete" class="cmd delete-red" href="javascript:" onclick='QPopLegacyMed.$.medDelete(); return false'>Delete</a>
        </span>
        <span>&nbsp;</span>
        <a id="medCancel" class="cmd none" href="javascript:" onclick='QPopLegacyMed.$.close(); return false'>Cancel</a>
      </div>
    </div>
  </div>
</div>
<?php 
function renderLabelCheck2($id, $caption, $checked = false, $style = null, $onclick = null, $lblId = null) {  // delim strings in $onclick by unescaped apostrophes only, e.g. alert('hi') 
  $sty = ($style) ? "style='" . $style . "'" : "";
  $onc = ($onclick) ? "onclick=\"" . $onclick . "\"" : "";
  $ond = ";" . $onclick;
  $chk = ($checked) ? "checked" : "";
  $cls = ($checked) ? "lcheck-on" : "lcheck";
  $lid = ($lblId) ? "id='" . $lblId . "'" : "";
  echo <<<eos
<input id='$id' type='checkbox' $chk class='lcheck' onPropertyChange='lcheckc(this)' $onc ondblclick="this.checked=!this.checked;$ond"><label unselectable='on' $lid class='$cls' onclick="lcheck(this)$ond" ondblclick="lcheck(this)$ond">$caption</label>
eos;
}
function renderLabelRadio2($id, $name, $caption, $checked = false, $style = null, $onclick = null, $lblId = null) {  // delim strings in $onclick by unescaped apostrophes only, e.g. alert('hi') 
  $sty = ($style) ? "style='" . $style . "'" : "";
  $onc = ($onclick) ? "onclick=\"" . $onclick . "\"" : "";
  $ond = ";" . $onclick;
  $chk = ($checked) ? "checked" : "";
  $cls = ($checked) ? "lcheck-on" : "lcheck";
  $lid = ($lblId) ? "id='" . $lblId . "'" : "";
  echo "<input id='$id' name='$name' type='radio' $chk class='lcheck' onpropertychange='lcheckc(this)' $onc ondblclick=\"$ond\"><label unselectable='on' $lid class='$cls' onclick=\"lrcheck(this)$ond\" ondblclick=\"lrcheck(this)$ond\">$caption</label>";
}
?>