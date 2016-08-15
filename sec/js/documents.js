function pageFocus() {
  if (refreshOnFocus) {
    refreshPage();
  }
}
function refreshPage() {
  Pop.Working.show("Refreshing page . . .");
  window.location.href = curl;
}
function userChange() {
  var url = curl + "&u=" + selectedValue("users");
  window.location.href = url;  
}
function go(sid) {
  refreshOnFocus = true;
  window.open("new-console.php?sid=" + sid + "&mid=default&" + Math.random(), "X" + sid, "height=" + (screen.availHeight - 80) + ",width=" + screen.availWidth + ",top=0,left=0,resizable=1,statusbar=0");
}
function goPreset(tpid) {
  refreshOnFocus = true;
  window.open("new-console.php?tpid=" + tpid + "&mid=default&" + Math.random(), "TP" + tpid, "height=" + (screen.availHeight - 80) + ",width=" + screen.availWidth + ",top=0,left=0,resizable=1,statusbar=0");
}
function showClient(cid) {
  //showOpenNote(cid);
  window.location.href = "face.php?id=" + cid;
}
function newNoteCallback(s) {
  go(s.id);
}
function newTemplate() {
  showNewCustomTemplate();
}
function newCustomTemplateCallback(p) {
  refreshOnFocus = true;
  window.open("new-console.php?tid=" + p.templateId + "&tname=" + p.templateName + "&mid=default&" + Math.random(), "TPNew", "height=" + (screen.availHeight - 80) + ",width=" + screen.availWidth + ",top=0,left=0,resizable=1,statusbar=0");
}
