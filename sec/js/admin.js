dirty = false;
promptDirty = true;
function checkDirty() {
	if (dirty && promptDirty) {
		return "Changes will be lost unless you click \"Save\" first.";
	}
}
function makeDirty() {
	dirty = true;
}
function ignoreDirty() {
	promptDirty = false;
}
function deleteCancelled() {
  return (! confirm("Are you sure you want to delete?"));
}
function clearCancelled() {
  return (! confirm("Are you sure you want to clear checkmarks?"));
}
function clearCacheCancelled() {
  return (! confirm("Are you sure you want to clear the cache?"));
}
function makeYesNo() {
	// TODO confirm replace
	document.forms[0].elements["optDesc[0]"].value = "yes";
	document.forms[0].elements["optDesc[1]"].value = "no";
	document.forms[0].elements["optText[0]"].focus();
}
function preview(templateId, parId) {
  if (dirty) {
    alert("Warning: Unsaved changes to this page will not be visible on the preview.");
  }
  window.open("adminPreview.php?tid=" + templateId + "&pid=" + parId, "preview", 'toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,dependent=no,width=700,height=500,top=100,left=120');
}
function showPopCopyQ(templateId, sectionId) {
	popCopyQ = window.open("popCopyQ.php?tid=" + templateId + "&sid=" + sectionId, "popCopyQ", 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,dependent=no,width=600,height=333,top=190,left=150');
}
function closePopCopyQ() {
	try {
		popCopyQ.close();
		popCopyQ = null;
	} catch (e) {}
}
function showPopAdmin(act, id) {
  //window.open("popAdmin.php?act=" + act + "&id="  + id , "popAdmin", 'toolbar=yes,location=yes,status=yes,menubar=yes,scrollbars=yes,resizable=yes,width=600,height=333,top=190,left=150');
  window.location.href = "popAdmin.php?act=" + act + "&id="  + encodeURIComponent(id);  
}
function showPopBuilder(field, method, level, delim, tid, sid, pid, qid) {
  window.open("popBuilder.php?fld=" + field + "&meth="  + method + "&l=" + level + "&delim=" + delim + "&tid=" + tid + "&sid=" + denull(sid) + "&pid=" + denull(pid) + "&qid=" + denull(qid), "popBuild", 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,dependent=no,width=600,height=333,top=190,left=150');  
  return false;
}
function denull(value) {
  return (value == null) ? "" : value + "";
}
function showPopTest(fieldId, templateId, sectionId, parId, questionId) {
	if (questionId != '') {
		popTest = window.open("popTestBuilder.php?fld=" + fieldId + "&tid=" + templateId + "&sid=" + sectionId + "&pid=" + parId + "&qid=" + questionId, "popTest", 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,dependent=no,width=600,height=333,top=190,left=150');
	} else {
		popTest = window.open("popTestBuilder.php?fld=" + fieldId + "&tid=" + templateId + "&sid=" + sectionId + "&pid=" + parId, "popTest", 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,dependent=no,width=600,height=333,top=190,left=150');
	}
	return false;
}
function showPopInject(fieldId, templateId, sectionId) {
	if (sectionId != '') {
		popTest = window.open("popInjectBuilder.php?fld=" + fieldId + "&tid=" + templateId + "&sid=" + sectionId, "popAction", 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,dependent=no,width=600,height=333,top=190,left=150');
	} else {
		popTest = window.open("popInjectBuilder.php?fld=" + fieldId + "&tid=" + templateId, "popAction", 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,dependent=no,width=600,height=333,top=190,left=150');
	}
	return false;
}
function showPopSetDefault(fieldId, templateId, sectionId, parId, questionId) {
  if (questionId != '') {
    popTest = window.open("popSetDefaultBuilder.php?fld=" + fieldId + "&tid=" + templateId + "&sid=" + sectionId + "&pid=" + parId + "&qid=" + questionId, "popTest", 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,dependent=no,width=600,height=333,top=190,left=150');
  } else {
    popTest = window.open("popSetDefaultBuilder.php?fld=" + fieldId + "&tid=" + templateId + "&sid=" + sectionId + "&pid=" + parId, "popTest", 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,dependent=no,width=600,height=333,top=190,left=150');
  }
  return false;
}
