      <div id="pop-debug" class="pop" onmousedown="event.cancelBubble = true">
        <div id="pop-debug-cap" class="pop-cap">
          <div id="pop-debug-cap-text">
            Clicktate
          </div>
          <a id="pop-debug-control" href="javascript:closeOverlayPop()" class="pop-close"></a>
        </div>
        <div class="pop-content">
          <div id="pop-debug-content" selectable="1" class="pop-debug-content" style="height:600px;width:600px;">
          </div>
          <div class="pop-cmd">
            <a href="javascript:debugCopy(0)" class="cmd none">Copy to Clipboard</a>
            <span>&nbsp;</span>
            <a href="javascript:closeOverlayPop()" class="cmd none">&nbsp;Exit&nbsp;</a>
            <button id="debug-copy-btn" style="display:none"></button>
          </div>
        </div>
      </div>
      <div id="pop-timer" class="pop" onmousedown="event.cancelBubble = true">
        <div id="pop-timer-cap" class="pop-cap">
          <div id="pop-timer-cap-text">
            Clicktate
          </div>
          <a id="pop-timer-control" href="javascript:closeOverlayPop()" class="pop-close"></a>
        </div>
        <div class="pop-content">
          <div id="pop-timer-content" selectable="1" class="pop-debug-content" style="height:500px;width:700px;">
          </div>
          <div class="pop-cmd">
            <a href="javascript:debugCopy(1)" class="cmd none">Copy to Clipboard</a>
            <span>&nbsp;</span>
            <a href="javascript:actionDebug()" class="cmd none">Debug</a>
            <span>&nbsp;</span>
            <a href="javascript:closeOverlayPop()" class="cmd none">&nbsp;Exit&nbsp;</a>
          </div>
        </div>
      </div>
<script>
function debugCopy(source) {
  var btn = setHtml("debug-copy-btn", source ? $("pop-timer-content").innerHTML : $("pop-debug-content").innerHTML);
  var r = btn.createTextRange();
  r.execCommand("copy");
  closeOverlayPop();
}
</script>