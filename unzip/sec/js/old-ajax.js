var wrk = 0;  // workingOn() counter
var wtag = document.getElementById("working");  // working tag pointer
var wstyle = true;  // false=toggle style.visibility, true=toggle style.display
var wfn = null;
var urs = ["Defaults","Doc","Json","Lookup","Pop","Sched","Session","Msg"];  // serverXXX.php urls
//var jcache = {}; // json responses: jcache[url]=json

// Assign a working element (e.g. image) to show/hide during AJAX call
// If toggleVisibility true, will toggle style.visibility
// If toggleVisibility false, will toggle style.display 
function setWorking(id, toggleVisibility) {
  wtag = document.getElementById(id);
  wstyle = toggleVisibility;
}
// Assign a working callback function
// Will call fn(true) when AJAX call initiated
// Will call fn(false) when AJAX call complete 
function setWorkingCallback(fn) {
  wfn = fn;
}
function workingOn() {
  wrk++;
  //clog("workingOn, wrk=" + wrk, true);
  if (wrk == 1) {
    if (wtag != null) {
      if (wstyle) {
        wtag.style.visibility = "visible";
      } else {
        wtag.style.display = "block";
      }
    }
    if (wfn != null) {
      wfn(true);
    } 
  }
}
function workingOff() {
  wrk--;
  //clog("workingOff, wrk=" + wrk, true);
  if (wrk <= 0) {
    if (wtag != null) {
      if (wstyle) {
        wtag.style.visibility = "hidden";
      } else {
        wtag.style.display = "none";
      }
    }
    if (wfn != null) {
      wfn(false);
    }
  }
}
var sur = [".php?",".htm?",".gif?","server","client"];
function sendRequest(uri, params) {
  //clog("Ajax.sendRequest(" + uri + "," + params + ")", true);
  workingOn();
//  if (useCache) {
//    var json = jcache[url];
//    if (json != null) {
//      processResponse(json);
//      return;
//    }
  //}
//  if (useCache) {
//    YAHOO.util.Connect.asyncRequest("GET", qurl, {success:successHandler, failure:failureHandler, argument:url});
//  } else {
    YAHOO.util.Connect.asyncRequest("GET", fixur2(uri, params), {success:successHandler, failure:failureHandler});
//  }
}
function sendRequest2(uri) {
  YAHOO.util.Connect.asyncRequest("GET", uri + "&" + Math.random(), {success:successHandler, failure:failureHandler});
}
function fixur(uri) {
  return sur[3] + urs[uri] + sur[0] + Math.random(); 
}
function fixur2(uri, params) {
  return sur[3] + urs[uri] + sur[0] + params + "&" + Math.random();
}
function postRequest(uri, params) {
  var url = fixur(uri);
  //clog("Ajax.postRequest(" + url + "," + params + ")", true);
  workingOn();
  var request = YAHOO.util.Connect.asyncRequest("POST", url, {success:successHandler, failure:failureHandler}, params);
}
function postRequest2(uri, params) {
  workingOn();
  var request = YAHOO.util.Connect.asyncRequest("POST", uri + "?" + Math.random(), {success:successHandler, failure:failureHandler}, params);  
}
function postLookupSave(action, obj, id) {  // id optional
  postRequest(3, "action=" + action + "&id=" + denull(id) + "&value=" + jsonUrl(obj));
}
function successHandler(o) {
  //alert("successHandler(o)");
  var r = o.responseText;
  if (r == null || r.length == 0) {
    //clog("o.responseText null: " + r);
    workingOff();
    return;
  }
//  var url = o.argument;
//  if (url != null) {
//    jcache[url] = r;
//  }
  processResponse(r);
}
function processResponse(r) {
  //alert("Ajax.processResponse(" + r.substring(0, 80) + ")");
  var o;
  try {
    o = eval("(" + r + ")");
  } catch (e) {
    alert("Error 1. The server response was not recognized:" + r.substring(0, 600));
    workingOff();
    return;
  }
  switch (o.id) {
    case "pars":
      parseParInfos(o.obj);
      break;
    case "injects":
      parseParInfosPool(o.obj);
      break;
    case "meds":
      parseMeds(o.obj);
      break;
    case "plan":
      parsePlan(o.obj);
      break;
    case "template":
      parseTemplate(o.obj);
      break;
    case "templates":
      parseTemplates(o.obj);
      break;
    case "par":
      parseParInfo(o.obj);
      break;
    case "parTemplates":
      parseParTemplates(o.obj);
      break;
    case "questions":
      parseQuestions(o.obj);
      break;
    case "saveSession":
      saveSessionCallback(o.obj);  // returns JSession with updated info
      break;
    case "autosaveSession":
      autosaveSessionCallback(o.obj);  // returns timestamp (string)
      break;
    case "getSession":
      getSessionCallback(o.obj);  // returns JSession
      break;
    case "savePreset":
      savePresetCallback(o.obj);  // returns JTemplatePreset with updated info
      break;
    case "preview":
      previewCallback(o.obj);  // returns HTML (string)
      break;
    case "sign":
      signCallback();
      break;
    case "addendum":
      addendumCallback();
      break;
    case "deleteSession":
      deleteCallback();
      break;
    case "timeout":
      timeoutCallback();
      break;
    case "save-timeout":
      timeoutCallback();
      break;
    case "clients":
      clientsCallback(o.obj.clients);
      break;
    case "getDocView":
      getDocViewCallback(o.obj);  // returns JDocView
      break;
    case "addClient":
      addClientCallback(o.obj);  // returns Client with correct clientId
      break;
    case "updateClient":
      updateClientCallback(); 
      break;
    case "addClientUidExists":
      addClientUidExistsCallback(o.obj);  // returns JClient of dupe
      break;
    case "sched":
      schedCallback(o.obj);  // returns JSched
      break;
    case "event":
      eventCallback(o.obj);  // returns JSched
      break;
    case "client":
      clientCallback(o.obj);  // returns JClient
      break;
    case "eventlessClient":
      eventlessClientCallback(o.obj);  // returns JClient
      break;
    case "saveSched":
      saveSchedCallback(o.obj);  // returns Sched with correct schedId
      break;
    case "saveSchedEvent":
      saveSchedEventCallback(o.obj);  // returns Sched with correct schedId
      break;
    case "deleteSched":
      deleteSchedCallback();
      break;
    case "deleteClient":
      deleteClientCallback();
      break;
    case "saveClient":
      saveClientCallback(o.obj);  // returns JClient
      break;
    case "addSession":
      addSessionCallback(o.obj);  // returns JSession
      break;
    case "getFacesheet":
      getFacesheetCallback(o.obj);  // returns JFacesheet
      break;
    case "closeSession":
      closeSessionCallback();
      break;
    case "getMyInbox":
      getMyInboxCallback(o.obj);
      break;
    case "getMyInboxCt":
      getMyInboxCtCallback(o.obj);
      break;
    case "getThread":
      page.getThreadCallback(o.obj);
      break;
    case "reply":
      page.postCallback(o.obj);
      break;
    case "send":
      sendCallback(o.obj);
      break;
    case "getPresets":
      getPresetsCallback(o.obj);
      break;
    case "getPreset":
      getPresetCallback(o.obj);  // returns JTemplatePreset
      break;
    case "newPreset":
      newPresetCallback(o.obj);  // returns JTemplatePreset
      break;
    case "addPreset":
      addPresetCallback(o.obj);  // returns JTemplatePreset
      break;
    case "addPresetExists":
      addPresetExistsCallback(o.obj);  // returns id of collision
      break;
    case "getTemplateCombo":  // returns JHtmlCombo
      getTemplateComboCallback(o.obj);
      break;
    case "getNewNotePopInfo":  // returns JNewNotePop
      getNewNotePopInfoCallback(o.obj);
      break;
    case "getSendTos":  // returns JHtmlCombo
      getSendTosCallback(o.obj);
      break;
    case "getEditHeaderPopInfo":  // returns JNewNotePop
      getEditHeaderPopInfoCallback(o.obj);
      break;
    case "updateNoteHeader":  // returns JSession
      updateNoteHeaderCallback(o.obj);
      break;
    case "clearSendTo":  
      clearSendToCallback();
      break;
    case "getMyUser":  // returns JUser
      getMyUserCallback(o.obj);
      break;
    case "getSupportUser":  // returns JUser
      getSupportUserCallback(o.obj);
      break;
    case "getMyUserGroup":  // returns JUserGroup
      getMyUserGroupCallback(o.obj);
      break;
    case "updateMyUser":
      updateMyUserCallback(o.obj);  // returns null if OK, otherwise error message 
      break;
    case "updateMyUserGroup":
      updateMyUserGroupCallback(); 
      break;
    case "updateSupportUser":
      updateSupportUserCallback(o.obj);  // returns null if OK, true if dupe insert 
      break;
    case "getIcdCodes":
      getIcdCodesCallback(o.obj);
      break;
    case "searchIcdCodes":
      searchIcdCodesCallback(o.obj);
      break;
    case "getClientSearchCustom":
      getClientSearchCustomCallback(o.obj);
      break;
    case "removeMyClientSearchCustom":
      removeMyClientSearchCustomCallback(o.obj);
      break;
    case "checkCuTimestamp":  // returns string
      cuPollTimestampCallback(o.obj);
      break;
    case "schedProfile":
      schedProfileCallback();
      break;
    case "saveApptTypes":
      saveApptTypesCallback();
      break;
    case "removeApptTypes":
      removeApptTypesCallback(o.obj);
      break;
    case "saveSchedStatus":
      saveSchedStatusCallback();
      break;
    case "removeSchedStatus":
      removeSchedStatusCallback(o.obj);
      break;
    case "refreshFacesheet":  // returns JFacesheet  
      refreshFacesheetCallback(o.obj);
      break;
    case "addFacesheetHm":  // returns JFacesheet  
      addFacesheetHmCallback(o.obj);
      break;
    case "getProcQuestion":  // returns JQuestion
      getProcQuestionCallback(o.obj);
      break;
    case "getSuidQuestion":  // returns JQuestion
      getSuidQuestionCallback(o.obj);
      break;
    case "getMedHist":  // returns JFacesheet  
      getMedHistCallback(o.obj);
      break;
    case "getQuestionsForHproc":  // returns {prop:JQuestion}
      getQuestionsForHprocCallback(o.obj);
      break;
    case "getVitalQuestions":  // returns {prop:JQuestion}
      getVitalQuestionsCallback(o.obj);
      break;
    case "getSochxQuestions":  // returns {dsync:JQuestion}
      getSochxQuestionsCallback(o.obj);
      break;
    case "getHxQuestions":  // returns {dsync:JQuestion}
      getHxQuestionsCallback(o.obj);
      break;
    case "getFamhxQuestions":  // returns {puid:{dsync:JQuestion}}
      getFamhxQuestionsCallback(o.obj);
      break;
    case "getAllergyQuestion":  // returns JQuestion
      getAllergyQuestionCallback(o.obj);
      break;
    case "getQuestionByQuid":  // returns JQuestion
      getQuestionByQuidCallback(o.obj);
      break;
    case "tSearch":  
      tSearchCallback(o.obj);
      break;
    case "getPars":
      getParsCallback(o.obj);
      break;
    case "null":  // intentional no-callback
      break;
    case "dataException":
      dataException(o.obj);
      break;
    case "getLookupDataForTable":
      getLookupDataForTableCallback(o.obj);
      break;
    case "saveLookupData":
      saveLookupDataCallback(o.obj);
      break;
    case "getTemplatePar":
      TemplateUi._requestParCallback(o.obj);
      break;
    default:
      alert("Error 2. The server response was not recognized.\n\n" + r);
  }
  //clog("calling workingOff()");
  workingOff();
}
function failureHandler(o) {
  alert("Call to server failed.\n\n" + o.status + " " + o.statusText);
  workingOff();
}