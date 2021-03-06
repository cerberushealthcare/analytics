<?php // Customize Appt Type ?>
<div id="pop-cat" class="pop" onmousedown="event.cancelBubble = true">
  <div id="pop-cat-cap" class="pop-cap">
    <div id="pop-cat-cap-text"> 
      Customize Appointment Types
    </div>
    <a href="javascript:Pop.close()" class="pop-close"></a>
  </div>
  <div class="pop-content">
    <div class="scroll" style="height:420px">
      <ul id="pop-cat-ul" class="entry" onclick="catUlClick()">
      </ul>
    </div>
    <div class="pop-cmd">
      <a href="javascript:catSave()" class="cmd save">Save Changes</a>
      <span>&nbsp;</span>
      <a href="javascript:catReset()" class="cmd none">Reset to Default</a>
      <span>&nbsp;</span>
      <a href="javascript:Pop.close()" class="cmd none">Cancel</a>
    </div>
  </div>
</div>
<?php // Customize Sched Status ?>
<div id="pop-css" class="pop" onmousedown="event.cancelBubble = true">
  <div id="pop-css-cap" class="pop-cap">
    <div id="pop-css-cap-text"> 
      Customize Schedule Status
    </div>
    <a href="javascript:Pop.close()" class="pop-close"></a>
  </div>
  <div class="pop-content">
    <div class="scroll" style="height:420px">
      <ul id="pop-css-ul" class="entry" style="padding:10px" onclick="cssUlClick()">
      </ul>
    </div>
    <div class="pop-cmd">
      <a href="javascript:cssSave()" class="cmd save">Save Changes</a>
      <span>&nbsp;</span>
      <a href="javascript:cssReset()" class="cmd none">Reset to Default</a>
      <span>&nbsp;</span>
      <a href="javascript:Pop.close()" class="cmd none">Cancel</a>
    </div>
  </div>
</div>
<?php // Color Palette ?>
<?php $colors = LookupDao::getApptColors(); ?>
<div id="pop-cc" class="pop" onmousedown="event.cancelBubble = true">
  <div id="pop-cc-cap" class="pop-cap">
    <div id="pop-cc-cap-text"> 
      Color Palette
    </div>
    <a href="javascript:Pop.close()" class="pop-close"></a>
  </div>
  <div class="pop-content">
    <div class="pop-frame">
      <div class="pop-frame-content">
        <ul class="entry" onclick="cssUlClick()">
          <li id="pop-cc-li">
            <?php for ($i = 0; $i < count($colors); $i++) { ?>
              <a class="cmd none" style="background-color:<?=$colors[$i] ?>" href="javascript:setColor('<?=$colors[$i] ?>')" >&nbsp;&nbsp;&nbsp;&nbsp;</a>
            <?php } ?>
          </li>  
        </ul>
      </div>
    </div>
    <div class="pop-cmd">
      <a href="javascript:Pop.close()" class="cmd none">Cancel</a>
    </div>
  </div>
</div>
<?php // Customize Sched Profile ?>
<div id="pop-csp" class="pop" onmousedown="event.cancelBubble = true">
  <div id="pop-csp-cap" class="pop-cap">
    <div id="pop-csp-cap-text"> 
      Customize Schedule
    </div>
    <a href="javascript:closeCspPop()" class="pop-close"></a>
  </div>
  <div class="pop-content">
    <div class="pop-frame">
      <h1>Calendar Display</h1>
      <div class="pop-frame-content">
        <ul class="entry">
          <li>
            <label class="subhead">Work Week</label>
          </li>
          <li>
            <label class="first3">Start</label>
            <select id="csp-dow">
              <option value="0">Sunday</option>
              <option value="1">Monday</option>
              <option value="2">Tuesday</option>
              <option value="3">Wednesday</option>
              <option value="4">Thursday</option>
              <option value="5">Friday</option>
              <option value="6">Saturday</option>
            </select>             
            <label>Length</label>
            <select id="csp-week">
              <option value="1">1 day</option>
              <option value="2">2 days</option>
              <option value="3">3 days</option>
              <option value="4">4 days</option>
              <option value="5">5 days</option>
              <option value="6">6 days</option>
              <option value="7">7 days</option>
            </select>
          </li>
          <li class="push">
            <label class="subhead">Time Slots</label>
          </li>
          <li>
            <label class="first3">From</label>
            <?php renderClock("csp-start", "", true) ?>
            <label>To</label>
            <?php renderClock("csp-end", "", true) ?>
            <label>Size</label>
            <select id="csp-size">
              <option value="10">10 minute</option>
              <option value="15">15 minute</option>
              <option value="30">30 minute</option>
            </select>
          </li>
          <li class="push">
            <label class="subhead">Appt Label</label>
          </li>
          <li>
            <label class="first3"></label>
            <select id="csp-label">
              <option value="0">Last, First</option>
              <option value="1">Last, F. (Phone)</option>
            </select>
          </li>
        </ul>
        <div class="pop-cmd" style="margin-top:15px">
          <a href="javascript:cspSave()" class="cmd save">Save Changes</a>
          <span>&nbsp;</span>
          <a href="javascript:cspReset()" class="cmd none">Reset to Default</a>
          <span>&nbsp;</span>
          <a href="javascript:closeCspPop()" class="cmd none">Cancel</a>
        </div>
      </div>
    </div>
    <div class="pop-frame push">
      <h1>Other</h1>
      <div class="pop-frame-content">
        <div class="pop-cmd" style="margin:0">
          <div>
            <a href="javascript:showCustomApptTypes()" class="pencil custom">Customize Appointment Types</a>
          </div>
          <div style="margin-top:5px">
            <a href="javascript:showCustomSchedStatus()" class="pencil custom">Customize Arrival Status</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
var lu_profile = <?=$form->schedProfileAsJson ?>;
var lu_types = <?=LookupDao::getApptTypesAsJson() ?>;
var lu_status = <?=LookupDao::getSchedStatusAsJson() ?>;
var lu_null_color = "<?=$colors[0] ?>";
var uid = <?=$form->userId ?>;
</script>