      <div id="pop-working" class="pop" onmousedown="event.cancelBubble = true">
        <div id="pop-working-cap" class="pop-cap">
          <div id="pop-working-cap-text">
            Clicktate
          </div>
          <a id="pop-working-control" href="javascript:closeOverlayPop()" class="pop-close"></a>
        </div>
        <div class="pop-content">
          <div id="pop-workingbar" class="workingbar">
          </div>
        </div>
      </div>
      <div id="pop-confirm" class="pop" onmousedown="event.cancelBubble = true">
        <div id="pop-confirm-cap" class="pop-cap">
          <div id="pop-confirm-cap-text">
            Clicktate
          </div>
          <a id="pop-confirm-control" href="javascript:closeConfirm(null)" class="pop-close"></a>
        </div>     
        <div id="pop-confirm-content" class="pop-content question">
          <div id="pop-confirm-text">
          </div>
        </div>
        <div style="padding-bottom:20px">
          <div id="pop-confirm-yes-no-cancel" class="pop-cmd">
            <a id="pop-confirm-yes" href="javascript:closeConfirm(true)" class="cmd none">&nbsp;&nbsp;&nbsp;Yes&nbsp;&nbsp;&nbsp;</a>
            <span>&nbsp;</span>
            <a id="pop-confirm-no" href="javascript:closeConfirm(false)" class="cmd none">&nbsp;&nbsp;&nbsp;No&nbsp;&nbsp;&nbsp;</a>
            <span>&nbsp;</span>
            <a id="pop-confirm-cancel" href="javascript:closeConfirm(null)" class="cmd none">Cancel</a>
          </div>
        </div>
      </div>
      <div id="pop-prompt" class="pop" onmousedown="event.cancelBubble = true">
        <div id="pop-prompt-cap" class="pop-cap">
          <div id="pop-prompt-cap-text">
            Clicktate
          </div>
          <a href="javascript:closePrompt(false)" class="pop-close"></a>
        </div>     
        <div id="pop-prompt-content" class="pop-content question">
          <div id="pop-prompt-text">
          </div>
        </div>
        <div style="padding-bottom:10px">
          <div id="pop-prompt-div">
            <input type="text" size="30" id="pop-prompt-input" onkeypress="return ifCrClick('pop-prompt-ok')" />
          </div>
          <div id="pop-prompt-cmd" class="pop-cmd">
            <a id="pop-prompt-ok" href="javascript:" onclick="closePrompt(true); return false" class="cmd none">&nbsp;&nbsp;&nbsp;OK&nbsp;&nbsp;&nbsp;</a>
            <span>&nbsp;</span>
            <a href="javascript:closePrompt(false)" class="cmd none">&nbsp;Cancel&nbsp;</a>
          </div>
        </div>
      </div>
      <div id="pop-msg" class="pop" onmousedown="event.cancelBubble = true">
        <div id="pop-msg-cap" class="pop-cap">
          <div id="pop-msg-cap-text">
            Clicktate
          </div>
          <a href="javascript:closeConfirm(true)" class="pop-close"></a>
        </div>     
        <div id="pop-msg-content" class="pop-content information">
          <div id="pop-msg-text">
          </div>
        </div>
        <div style="padding-bottom:10px">
          <div class="pop-cmd">
            <a id="pop-msg-ok" href="javascript:closeConfirm(true)" class="cmd none">&nbsp;&nbsp;&nbsp;OK&nbsp;&nbsp;&nbsp;</a>
          </div>
        </div>
      </div>
