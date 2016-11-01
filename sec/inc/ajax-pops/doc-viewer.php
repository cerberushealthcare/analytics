<?
// Document Viewer
// showDocViewer(sid, stubs{sid:}) 
?>
<div id="pop-dv" class="pop" onmousedown="event.cancelBubble = true">
  <div id="pop-dv-cap" class="pop-cap">
    <div id="pop-dv-cap-text"> 
      Clicktate - Document Viewer
    </div>
    <a href="javascript:closeOverlayPop()" class="pop-close"></a>
  </div>
  <div id="pop-dv-content" class="pop-content">
    <div id="pop-dv-nav">
      <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td id="dv-nav-1">
            <a id="dv-nav-prev" href="javascript:dvGoPrev()">
              <div id="dv-nav-prev-div"></div>
            </a>
          </td>
          <td id="dv-nav-2">
            <div id="dv-nav-on-div"></div>
          </td>
          <td id="dv-nav-3">
            <a id="dv-nav-next" href="javascript:dvGoNext()">
              <div id="dv-nav-next-div"></div>
            </a>
          </td>
        </tr> 
      </table>
    </div>
    <div id="pop-dv-body">
    </div>
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
      <tr>
        <td nowrap="nowrap">
          <div class="pop-cmd">
            <label>
              This document:
            </label>
            <a id="dv-edit" href="javascript:dvEdit()" class="cmd note">Open in Console Editor</a>
            <span>&nbsp;</span>
            <a id="dv-copy" href="javascript:dvCopy()" class="cmd copy-note">Replicate...</a>
            <span>&nbsp;</span>
            <a id="dv-copy" href="javascript:dvPrint()" class="cmd print-note">Print...</a>
          </div>
        </td>
        <td style="width:100%">
          <div class="pop-cmd cmd-right">
            <a href="javascript:closeOverlayPop()" class="cmd none">&nbsp;&nbsp;&nbsp;Exit&nbsp;&nbsp;&nbsp;</a>
            <span>&nbsp;</span>
          </div>
        </td>
      </tr>
    </table>
  </div>
</div>
<?php if ($lu_print->quick) { ?>
<OBJECT ID="Helper" CLASSID="CLSID:7E9FB91B-9FB9-426A-895C-9FB914536CB5" CODEBASE="Clicktate.CAB#version=1,0,0,7"></OBJECT>
<?php } ?>
<script type="text/javascript">
var dvStubs;
var dvNav;
var session;
var template;
function showDocViewer(sid, stubs, asZoom) {
  if (stubs) {
    dvSetStubs(stubs);
  }
  dvBuildDoc(sid);
  if (asZoom) {
    zoomPop("pop-dv");
  } else {
    showOverlayPop("pop-dv");
  }
}
function dvSetStubs(stubs) {
  dvStubs = stubs;
  var last;
  for (var id in stubs) {
    var s = stubs[id];
    if (last) {
      last.prev = s.id;
      s.next = last.id;
    } else {
      s.next = null;
    }
    last = s;
  }
  last.prev = null;
}
function dvBuildDoc(sid) {
  session = dvStubs[sid];
  dvBuildNav(sid);
  setHtml("pop-dv-body", "").className = "working-circle";
  sendRequest(4, "action=getDocView&sid=" + sid);  
}
function dvPrint() {
  <?php if ($lu_print->quick) { ?>
    download($("pop-dv-body").children, session.updatedBy, true);
    VBPrint();
  <?php } else { ?>
    download($("pop-dv-body").children, session.updatedBy);
  <?php } ?>
}
function getDocViewCallback(dv) {
  if (dv.html) {
    setHtml("pop-dv-body", dv.html).className = "";
  } else {
    setHtml("pop-dv-body", "We're sorry, but a preview of this document is not available.").className = "no-preview";
  }
}
function dvBuildNav(sid) {
  var stub = dvStubs[sid];
  dvNav = {on:sid, prev:stub.prev, next:stub.next};
  setHtml("dv-nav-on-div", dvNavHtml(sid));
  if (dvNav.prev) {
    $("dv-nav-prev").style.visibility = "";
    setHtml("dv-nav-prev-div", dvNavHtml(dvNav.prev));
  } else {
    $("dv-nav-prev").style.visibility = "hidden";
  }  
  if (dvNav.next) {
    $("dv-nav-next").style.visibility = "";
    setHtml("dv-nav-next-div", dvNavHtml(dvNav.next));
  } else {
    $("dv-nav-next").style.visibility = "hidden";
  }
}
function dvNavHtml(sid) {
  var stub = dvStubs[sid];
  return stub.label + "<br/><span>(" + stub.date + ")</span>"; 
}
function dvGoPrev() {
  dvBuildDoc(dvNav.prev);
}
function dvGoNext() {
  dvBuildDoc(dvNav.next);
}
function dvEdit() {
  closeOverlayPop();
  editDoc(dvNav.on);
}
function dvCopy() {
  var stub = dvStubs[dvNav.on];
  showReplicate(stub.cid, stub.id, stub.tid);
}
</script>
<?php if ($lu_print->quick) { ?>
<script language="VBScript">
Function VBPrint()
  Dim xml, word, wdoc
  Dim sUrl, sLocal, sRtf, sForm
  sLocal = Document.getElementById("docName").value & "_.doc"
  sForm = "docDos=" & Document.getElementById("docDos").value _
        & "&cn=" & Document.getElementById("docName").value _
        & "&head=" & Document.getElementById("docHead").value _
        & "&fmt=" & Document.getElementById("docFmt").value _
        & "&img=" & Document.getElementById("docImg").value _
        & "&img2=" & Document.getElementById("docImg2").value _
        & "&sigimg=" & Document.getElementById("docSigImg").value _
        & "&signame=" & Document.getElementById("docSigName").value _
        & "&noTag=" & Document.getElementById("docNoTag").value _
        & "&lefthead=" & Document.getElementById("docLeftHead").value _
        & "&doc=" & Document.getElementById("docText").value
  Set xml = CreateObject("msxml2.xmlhttp")
  xml.Open "POST", "serverDoc.php", False
  xml.SetRequestHeader "Content-Type", "application/x-www-form-urlencoded"  
  xml.Send sForm
  sRtf = xml.ResponseText
  Helper.PrintRtf sRtf, sLocal
End Function
</script>
<?php } ?>