var sched;
var popAsEdit;
function setDuration(c) {
  if (sched._durationHr == null && sched._durationMin == null) { 
    var t = lu_types[c.value];
    if (t) {
      setValue("appt-duration-hr", t.h);
      setValue("appt-duration-min", t.m);
    }
  }
}
function resetMin(forUe) {
  if (forUe) {
    setValue("ue-duration-min", "0");
  } else {
    setValue("appt-duration-min", "0");
  }
}
function setAllDay() {
  var h = $("ue-duration-hr");
  h.selectedIndex = h.options.length - 1; 
  setValue("appt-duration-min", "0");
}
function docChange() {
  var url = curl + "&u=" + selectedValue("doc");
  window.location = url;
}

// Popup: patient selector
function addSchedPop2(userId, date, time) {
  sched = buildSched(userId, date, time);
  if (nnClients && nnClients.length == 1) {  // go ahead and start schedule for a single result
//    selectClientCallback(nnClients[0]);
    editPatientCallback(nnClients[0]);
  } else {
    SchedPatientSelector.pop(editPatientCallback, unavailable);  
    //showPatientSelector(null, null, true);
  }
}
function buildSched(userId, date, time) {
  var s = newSched();
  s.userGroupId = ugid;
  s.userId = userId;
  s.date = date;
  s._formatTime = time;
  return s;
}
function schedAnother() {
  Pop.close();
  showPatientSelector(null, null, true);  
}
function newNotePop() {
  showNewNote(sched.Client.clientId, sched.Client.name, sched.schedId, null); 
}
function newNoteCallback(s) {
  go(s.id);
}

// Popup: patient editor
function editPatient() {
  //showEditPatient(sched.clientId, sched.client.events != null);
  //showPatient(sched.client);
  Ajax.Facesheet.Patients.get(sched.Client.clientId, Html.Window, function(client) {
    PatientEditor.pop(client, null, editPatientCallback);
  })
}
function showPatientCallback(c) {
  editPatientCallback(c);
}
function newPatientPop() {
  showEditPatient();
}
function editPatientCallback(c, isNew) {
  if (c != null) {
    sched.Client = c;
    sched.clientId = c.clientId;
  }
  schedPop();
}
function selectClientCallback(c) {
  overlayWorking(true);
  Ajax.get(Ajax.SVR_SCHED, 'getEventlessClient', c.clientId, 
    function(client) {
      overlayWorking(false);
      sched.Client = client;
      sched.clientId = client.clientId;
      schedPop();
    });
}

// Popup: schedule event (unavailability)
function schedEventPop() {
  resetSchedEventPop(sched);
  Pop.show("pop-ue", "ue-title");
}
function unavailable() {  // new sched event
  sched.Event = newSchedEvent();
  sched._durationHr = 4;
  sched.comment = "Unavailable";
  schedEventPop();
}
function showRepeat(s) {
  var ix =  (s.id == 'ue-rp-type2') ? '2' : '';
  var i = s.selectedIndex;
  showIf(i > 0, "ue-ul-repeat" + ix);
  switch(i) {
  case 1:
    setText("ue-rp-every-label" + ix, "day");
    hide("ue-rp-on-span" + ix);
    hide("ue-rp-by-span" + ix);
    break;
  case 2:
    setText("ue-rp-every-label" + ix, "week");
    show("ue-rp-on-span" + ix);
    hide("ue-rp-by-span" + ix);
    break;
  case 3:
    setText("ue-rp-every-label" + ix, "month");
    hide("ue-rp-on-span" + ix);
    show("ue-rp-by-span" + ix);
    break;
  case 4:
    setText("ue-rp-every-label" + ix, "year");
    hide("ue-rp-on-span" + ix);
    hide("ue-rp-by-span" + ix);
    break;
  }
}
function resetSchedEventPop(sched) {
  setValue("ue-date", sched.date);
  setValue("ue-time", sched._formatTime);
  setValue("ue-duration-hr", sched._durationHr);
  setValue("ue-duration-min", sched._durationMin);  
  setValue("ue-title", String.brToCrlf(sched.comment));   
  setValue("ue-comment", String.brToCrlf(sched.Event.comment));
  setValue("ue-rp-type", sched.Event.rpType);
  setValue("ue-rp-until", sched.Event.rpUntil);
  setValue("ue-rp-every", sched.Event.rpEvery);
  setUeDows(sched.Event.dows);
  setValue("ue-rp-by", sched.Event.rpBy);
  showRepeat($("ue-rp-type"));
}
function setUeDows(dow) {
  for (var i = 0; i < dow.length; i++) {
    idows[i].checked = dow[i];
  }
}
function setUeDows2(dow) {
  for (var i = 0; i < dow.length; i++) {
    idows2[i].checked = dow[i];
  }
}
function getDow() {
  dow = [0,0,0,0,0,0,0];
  for (var i = 0; i < idows.length; i++) {
    dow[i] = idows[i].checked ? 1 : 0;
  }
  return dow;
}
function getDow2() {
  dow = [0,0,0,0,0,0,0];
  for (var i = 0; i < idows2.length; i++) {
    dow[i] = idows2[i].checked ? 1 : 0;
  }
  return dow;
}
function saveSchedEvent() {
  sched.date = value("ue-date");
  sched._formatTime = value("ue-time");
  sched.timeStart = military(sched._formatTime);
  sched._durationHr = value("ue-duration-hr");
  sched._durationMin = value("ue-duration-min");
  sched.duration = value("ue-duration-hr") * 60 + value("ue-duration-min") * 1;
  sched.closed = 0;
  sched.comment = value("ue-title");
  sched.Event.comment = value("ue-comment");
  sched.Event.rpType = value("ue-rp-type");
  sched.Event.rpEvery = value("ue-rp-every");
  sched.Event.rpUntil = value("ue-rp-until");
  sched.Event.dows = getDow(); 
  sched.Event.rpBy = value("ue-rp-by");
  if (showValidateErrors("pop-error-ue", validateSched(), "ue-")) {
    return;
  }
  setWorking("working-ue", false);
  setText("working-msg-ue", "Saving event");
  postRequest(
      5, 
      "action=saveSchedEvent" +
      "&id=" +
      "&obj=" + jsonUrl(sched));
}

// Popup: schedule
function schedPop() {
  resetSchedPop(sched);  
  if (sched.schedId == null) {  // new schedule
    show("pop-sc-cmd-new");
    hide("pop-sc-cmd");
    hide("pop-notes");
    show("aChooseAnother");
  } else {
    hide("pop-sc-cmd-new");
    show("pop-sc-cmd");
    show("pop-notes");
    hide("aChooseAnother");
  }
  setText("pop-sc-cap-text", popTitleSched(sched) + " > " + sched.Client.name);
  if (sched.schedId == null) {
    Pop.show("pop-sc", "appt-type");
    hide("pop-sched-ro");
    hide("pop-sc-cmd-ro");
  } else {
    Pop.show("pop-sc");
    show("pop-sched-ro");
    show("pop-sc-cmd-ro");
    hide("pop-sched-edit");
  }
  if (popAsEdit) {
    schedEdit();
  }
}
function schedEdit(focusId) {
  hide("pop-sched-ro");
  hide("pop-sc-cmd-ro");
  show("pop-sched-edit");
  hide("pop-notes");
  if (focusId) {
    focus(focusId);
  }
}
function closeScPop() {
  Pop.close();
  if (colorChange) reloadWindow();
}
function cancelSaveSched() {
  if (popAsEdit) {
    closeScPop();
    return;
  }
  show("pop-sched-ro");
  show("pop-sc-cmd-ro");
  hide("pop-sched-edit");
  show("pop-notes");
}
function showAppt2(id, asEdit) {
  popAsEdit = asEdit;
  Pop.Working.show("Retrieving");
  sendRequest(5, "action=getSched&id=" + id);
}
function resetSchedPop(sched) {
  var c = sched.Client;
  setText("pop-client-name", c.name);
  setText("pop-client-id", c.uid);
  setText("pop-client-dob", denull(c.birth));
  setText("pop-client-address", denull(address(c)));
  setText("pop-client-phone", denull(c.Address_Home && c.Address_Home.phone1));
  setValue("appt-date", sched.date);
  setValue("appt-time", sched._formatTime);
  //setValue("appt-duration", sched.duration);
  setValue("appt-duration-hr", sched._durationHr);
  setValue("appt-duration-min", sched._durationMin);  
  setValue("appt-status", sched.status);
  setValue("appt-type", sched.type);
  setValue("appt-comment", String.brToCrlf(sched.comment));
  setText("pop-sched-appt-type", selectedText("appt-type"));
  setText("pop-sched-appt-repeats", sched.Event && sched.Event.rpType > '0' ? 'Yes' : 'No');
  setText("pop-sched-appt-date", sched._formatDate);
  //setText("pop-sched-appt-time", selectedText("appt-time-hr") + ":" + selectedText("appt-time-min") + " " + selectedText("appt-time-ampm"));
  setText("pop-sched-appt-time", sched._formatTime);
  setText("pop-sched-appt-duration", formatDuration());
  setText("pop-sched-appt-status", selectedText("appt-status"));
  setText("pop-sched-appt-comment", value("appt-comment"));
//  setText("pop-sched-appt-by", sched._by);
  buildHistoryTable(c);
  hide("pop-error-sc");
  hide("pop-sched-ro");
  show("pop-sched-edit");
  if (sched.Event) {
    setValue("ue-rp-type2", sched.Event.rpType);
    setValue("ue-rp-until2", sched.Event.rpUntil);
    setValue("ue-rp-every2", sched.Event.rpEvery);
    setUeDows2(sched.Event.dows);
    setValue("ue-rp-by2", sched.Event.rpBy);
  } else {
    var event = newSchedEvent();
    setValue("ue-rp-type2", "0");
    setValue("ue-rp-until2", Event.rpUntil);
    setValue("ue-rp-every2", Event.rpEvery);
    setUeDows2(event.dows);
    setValue("ue-rp-by2", Event.rpBy);
  }
  showRepeat($("ue-rp-type2"));
}
function formatDuration() {
  var h = selectedText("appt-duration-hr");
  var m = selectedText("appt-duration-min");
  if (m == "0 minutes") {
    return h;
  } else {
    if (h == "0 hours") {
      return m;
    } else {
      return h + " and " + m;
    }
  }
}
function toggleHistory() {
  buildHistoryTable(sched.Client);
}
function buildHistoryTable(client) {
//  var incNotes = $("htoggle").checked;
  var tbody = Html.Table.$('pss-table').tbody().clean();
  client.Appts && client.Appts.each(function(rec) {
    tbody.tr(rec._date == sched._date ? 'current' : '')
      .td(ApptAnchor(rec)).w('60%')
      .td(rec.comment).w('40%');
    function ApptAnchor(rec) {
      return Html.AnchorRec.create('appt2', rec._date2 + ', ' + rec._label, rec, function() {
        Page.Nav.goSchedPop(rec.schedId);
      })
    }
  })
  /*
  clearAllRows(tbody);
  var offset = true;
  var lastFd = null;
  var lastTr;
  if (client.events != null) {
    for (var i = 0; i < client.events.length; i++) {
      var e = client.events[i];
      if (! incNotes && e.type == "S") {
        continue;
      }
      var same = false;
      if (e.fd != lastFd) {
        offset = ! offset;
        lastFd = e.fd;
      } else {
        lastTr.className += " lb";
        same = true;
      }
      var tr = createTr(offset ? "offset" : "");
      var tdClass = "bold";
      var ed = e.fts.substring(0, 11);
      if (ed == sched.date) {
        tdClass += " today";
      }
      tr.appendChild(createTd(e.fts, tdClass));
      //if (same) {
        //var span = document.createElement("span");
        //span.innerText = e.fts.substring(0, 12);
        //span.className = "hide";
        //var td = document.createElement("td");
        //td.className = "bold";
        //td.appendChild(span);
        //td.appendChild(document.createTextNode(e.fts.substring(12)));
        //tr.appendChild(td);
      //} else {
      //}
      var div = document.createElement("div");
      div.className = "lpad";
      var td = createTdAnchor(e.ahref, e.aclass, e.name, null, null);
      div.innerHTML = denull(e.comment);
      td.appendChild(div);
      td.style.width = "70%";
      tr.appendChild(td);
      tbody.appendChild(tr);
      lastTr = tr;
    }
  }
  */
  $("pss").scrollTop = 0;  
}
function saveSched() {
  sched.Client = null;
  var hadEvent = sched.Event;
  var dateChanged = sched.date != value('appt-date');
  sched.date = value("appt-date");
  var before = sched._formatTime + sched._durationHr + sched._durationMin + sched.status + sched.type + sched.comment;
  sched._formatTime = value("appt-time");
  sched.timeStart = military(sched._formatTime);
  sched._durationHr = value("appt-duration-hr");
  sched._durationMin = value("appt-duration-min");
  sched.duration = value("appt-duration-hr") * 60 + value("appt-duration-min") * 1;
  sched.closed = 0;
  sched.status = value("appt-status");
  sched.type = value("appt-type");
  sched.comment = String.crlfToBr(value("appt-comment"));
  var after = sched._formatTime + sched._durationHr + sched._durationMin + sched.status + sched.type + sched.comment;
  var changed = before != after;
  if (hadEvent) 
    before = Json.encode(sched.Event);
  if (value("ue-rp-type2") > '0') {
    if (sched.Event == null)
      sched.Event = {id:null};
    sched.Event.rpType = value("ue-rp-type2");
    sched.Event.rpEvery = value("ue-rp-every2");
    sched.Event.rpUntil = value("ue-rp-until2");
    sched.Event.dows = getDow2(); 
    sched.Event.rpBy = value("ue-rp-by2");
    sched.Event.comment = '';
  } else {
    if (hadEvent) 
      sched.Event.rpType = "0";
  }
  if (showValidateErrors("pop-error-sc", validateSched(), "appt-")) 
    return;
  if (hadEvent) {
    var repeatChanged;
    if (! changed)
      repeatChanged = before != Json.encode(sched.Event);
    if (! changed && ! repeatChanged)
      postSaveSched(true);
    else if (! dateChanged && ! changed)
      postSaveSched(false);
    else if (sched.Event.rpType == '0')
      postSaveSched(false);
    else
      promptSaveSched();
  } else {
    postSaveSched(false);
  }
}
function promptSaveSched() {
  Pop.Confirm.show(
    "This is a <b>repeating</b> appointment. What do you want to save?", 
    "Update just this one", 
    'none',
    "Update this and future occurrences",
    'none',
    true,
    null,
    function(confirm) {
      if (confirm != null) {
        postSaveSched(confirm);
      }
    });
}
function postSaveSched(asSingle) {
  if (asSingle)
    sched.Event = null;
  setWorking("working-sc", false);
  setText("working-msg-sc", "Saving appointment");
  postRequest(
      5, 
      "action=saveSched" +
      "&id=" +
      "&obj=" + jsonUrl(sched));
}
function deleteSched() {
  if (sched.Event) {
    Pop.Confirm.show(
      "This is a <b>repeating</b> appointment. What do you want to delete?", 
      "Delete just this one", 
      'none',
      "Delete this and future occurrences",
      'none',
      true,
      null,
      confirmDeleteRepeat);
  } else {
    Pop.Confirm.showYesNoCancel("Are you sure you want to delete this appointment?", confirmDeleteSched);
  }
}
function confirmDeleteSched(confirmed) {
  if (confirmed) {
    setWorking("working-sc", false);
    setText("working-msg-sc", "Deleting");
    postDeleteCall("deleteSched");
  }
}
function deleteSchedEvent() {
  if (sched.Event.rpType == 0) {
    Pop.Confirm.showYesNoCancel("Are you sure you want to delete this event?", confirmDeleteSched);
  } else {
    Pop.Confirm.show(
      "This is a <b>repeating</b> event. What do you want to delete?", 
      "Delete just this one", 
      'none',
      "Delete this and future occurrences",
      'none',
      true,
      null,
      confirmDeleteRepeat);
  }
}
function postDeleteCall(action) {
  postRequest(
      5, 
      "action=" + action +
      "&id=" + sched.schedId +
      "&obj=");
}
function confirmDeleteRepeat(confirmed) {
  if (confirmed != null) {
    var action = "deleteSchedRepeats";
    if (confirmed) {
      action = "deleteSched";
    }
    setWorking("working-sc", false);
    setText("working-msg-sc", "Deleting");
    postDeleteCall(action);
  }
}
function calTitleCallback(value) {
  if (value)
    window.location = curl2 + "&d=" + value;
}
function goFs() {
  window.location.href = "face.php?id=" + sched.clientId;
}

// Sched UI
function popTitleSched(sched) {
  var title = "";
  if (sched.schedId == null) {
    title = "New Appointment";
  } else {
    title = "Appointment";
  }
  return title;
}
function schedTime() {
  var d = parseInt(sched.date.substring(5, 7), 10) + "/" + sched.date.substring(8, 10); 
  var h = Math.round(sched.timeStart / 100);
  if (h > 12) h = h - 12;
  var m = sched.timeStart.substring(sched.timeStart.length - 2, sched.timeStart.length);
  return d + " " + h + ":" + m;
}
function showValidateErrors(id, errMsgs, prefix) {
  hide(id);
  if (errMsgs.length > 0) {
    showErrors(id, errMsgs);
    focusError(errMsgs[0].id);
    return true;
  }
}
function focusError(id, prefix) {
  if (id == "sched.date") {
    focus(prefix + "date");
  } else if (id == "sched.time") {
    focus(prefix + "time");
  } else if (id == "sched.duration-hr") {
    focus(prefix + "duration-hr");
  } else if (id == "sched.duration-min") {
    focus(prefix + "duration-min");
  }
}
function testPsCr() {
  var kc = event.keyCode;
  if (kc == 13) {
    search();
  } 
}
function testPeCr() {
  var kc = event.keyCode;
  if (kc == 13) {
    // TODO
  } 
}
function testScCr() {
  var kc = event.keyCode;
  if (kc == 13) {
    // TODO
  } 
}
// AJAX callbacks
function schedCallback(o) {
  Pop.Working.close();
  sched = o;
  if (! sched.status)
    sched.status = '';
  if (! sched.comment)
    sched.comment = '';
  schedPop();
}
function eventCallback(o) {
  Pop.Working.close();
  sched = o;
  schedEventPop();
}
function saveSchedCallback(s) {
  if (s.Event && s.Event._max) {
    Pop.Msg.showImportant(
      "Warning: Maximum repeats exceeded; appointments were created up to " + s.Event._max + ".", 
      apptCallback.curry(s)); 
  } else {
    apptCallback(s);
  }
}
function apptCallback(s) {
  if (sched.schedId == null) {
    reloadWindow(s.id);
  } else {
    reloadWindow();
  }
}
function saveSchedEventCallback(s) {
  if (s.Event._max) {
    Pop.Msg.showImportant(
      "Warning: Maximum repeats exceeded; events were created up to " + s.Event._max + ".", 
      maxConfirm); 
  } else {
    reloadWindow();
  }
}
function maxConfirm() {
  reloadWindow();
}
function deleteSchedCallback() {
  reloadWindow();
}
function reloadWindow(popId) {
  var url = "reloadWindow2(" + (popId ? popId : "") + ")";
  setTimeout(url, 100);
}
function reloadWindow2(popId) {
  var url = curl3;
  if (popId) {
    url += "&pop=" + popId;
  }
  window.location = url;
}
function go(sid) {
  Pop.close();
  window.open("new-console.php?sid=" + sid + "&mid=default&" + Math.random(), "X" + sid, "height=" + (screen.availHeight - 80) + ",width=" + screen.availWidth + ",top=0,left=0, resizable=1");
}

// JSON routines
function newSchedEvent() {
  var dv = new DateValue(sched.date);
  var dow = [0,0,0,0,0,0,0];
  dow[dv.getDow()] = 1;
  return {
    "id":null,
    "rpType":null,
    "rpEvery":null,
    "rpUntil":dv.toString(DateValue.FMT_DEFAULT),
    "rpOn":null,
    "rpBy":null,
    "comment":null,
    "dows":dow
  };
}
function newSched() {
  return {
    "id":null,
    "userId":null, 
    "userGroupId":null, 
    "clientId":null,
    "date":null, 
    "timeStart":null,
    "timeStartHr":null,
    "timeStartMin":null,
    "timeStartAmPm":null,
    "duration":null,
    "_durationHr":null,
    "_durationMin":null,
    "closed":null,
    "status":null,
    "comment":null,
    "type":null,
    "client":null
    };
}
function address(client) {
  if (client.Address_Home == null)
    client.Address_Home = client.Address || client.shipAddress;
  var a = client.Address_Home;
  if (a == null)
    return '';
  var s = denull(a.addr1);
  if (! isBlank(a.addr2)) {
    s += " " + a.addr2;
  }
  if (! isBlank(a.addr3)) {
    s += " " + a.addr3;
  }
  if (s != "") s += ", ";
  s += denull(a.city);
  if (s != "") s += ", ";
  s += denull(a.state) + " " + denull(a.zip);
  return s;
}
function validateSched() {
  var errs = [];
  if (sched.date == "") {
    errs.push(errMsg("sched.date", msgReq("Date")));
  }
  if (sched._formatTime == "") {
    errs.push(errMsg("sched.time", msgReq("Time")));
  } else {
    if (military(sched._formatTime) == null) {
      errs.push(errMsg("sched.time", "Time is not in a valid format."));
    }
  }
  if (sched.duration == "") {
    errs.push(errMsg("sched.duration", msgReq("Duration")));
  }
  return errs;
}
function military(value) {  // expects "08:30 AM", AMPM optional (defaults to AM), returns null if invalid
  var a = value.split(/:| /);
  if (a.length < 2) {
    return null;
  }
  var h = parseInt(a[0], 10);
  if (h < 1 || h > 12) {
    return null;
  } else if (h == 12) {
    h = 0;
  }
  var m = parseInt(a[1], 10);
  if (m < 0 || m > 59) {
    return null;
  }
  if ((a.length == 3 && a[2] == "PM") || a.length == 2 && a[1].substr(2) == 'PM') {
    h = h + 12;
  } 
  return h * 100 + m;
}
function milToStandard(value) {
  var m = value % 100;
  var h = (value - m) / 100;
  var s = "";
  if (h > 12) {
    h -= 12;
  } else if (h == 0) {
    h = 12;
  }
  return lpad(h) + ":" + lpad(m) + ((value >= 1200) ? " PM" : " AM");
}
SchedPatientSelector = {
  //
  pop:function(onchoose/*PatientStub*/, onunavailable) {
    return Html.Pop.singleton_pop.apply(SchedPatientSelector, arguments);
  },
  create:function() {
    return PatientSelector.create().extend(function(self) {
      return {
        init:function() {
          self.Frame2 = Html.Pop.Frame.create(self.content, 'Or, mark time as unavailable').addClass('mt10');
          Html.CmdBar.create(self.Frame2).addClass('m0')
            .button('Unavailable...', self.unavail_onclick, 'none')
            .cancel(self.cancel_onclick);
        },
        onpop:self.onpop.extend(function(_onpop, onchoose, onunavailable) {
          self.onunavailable = onunavailable;
          _onpop(onchoose, true);
        }),
        choose:self.choose.extend(function(_choose, rec) {
          Ajax.Facesheet.Patients.get(rec.clientId, Html.Window, _choose);
        }),
        unavail_onclick:function() {
          self.close();
          self.onunavailable();
        }
      }
    })
  }
}