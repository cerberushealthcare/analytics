<div id="pop-pp" class="pop" onmousedown="event.cancelBubble = true">
  <div id="pop-pp-cap" class="pop-cap">
    <div id="pop-pp-cap-text">
      Preview
    </div>
    <a href="javascript:closePpPop()" class="pop-close"></a>
  </div>
  <div class="pop-content">
    <div id="pop-pp-content">
    </div>
    <div class="pop-cmd">
      <a href="javascript:ppAdd()" class="cmd check">Insert into Document</a>
      <span>&nbsp;</span>
      <a href="javascript:closePpPop()" class="cmd none">Cancel</a>
    </div>
  </div>
</div>
<script>
var ppPids = {};
function closePpPop() {
  ppReset();
  closePop();
}
function ppReset() {
  ppPids = {};
  setHtml("pop-pp-content", "").className = "working-circle";
}
function showParPreview(tid, pid, noteDate, desc) {
  if (! isPopUp("pop-pp")) {
    zoomPop("pop-pp", null, null, true);
  }
  if (! ppPids[pid]) {
    setHtml("pop-pp-content", "").className = "working-circle";
    sendRequest(2, "action=preview&id=" + pid + "&tid=" + tid + "&nd=" + noteDate);
  }
}
function previewCallback(j) {
  var href = "javascript:ppClear(" + j.id + ")";
  var html = [];
  html.push("<h3>" + j.desc + "</h3>");
  html.push("&nbsp;&nbsp;<a href='" + href + "'>Remove</a>");
  html.push(j.html);
  ppPids[j.id] = html.join("");
  buildPpHtml();
  scrollBottom("pop-pp-content");
}
function buildPpHtml() {
  var html = []
  for (var pid in ppPids) {
    html.push(ppPids[pid]); 
  }
  if (html.length == 0) {
    closePpPop();
  }
  setHtml("pop-pp-content", html.join("")).className = "";
}
function ppClear(pid) {
  delete ppPids[pid];
  buildPpHtml();
}
function ppAdd() {
  closePop();
  for (var pid in ppPids) {
    reqParId = pid;
    doWork("getPar(" + pid + ")", "Inserting paragraph(s)", true);
  }
  ppReset();
}
</script>