var client;
var refreshOnFocus = false;
function testPsCr() {
  var kc = event.keyCode;
  if (kc == 13) {
    search();
  } 
}
function closePopRefresh() {
  if (refreshOnFocus && ! Pop.isActive()) {
    Pop.closeAll();
    refreshPage();
  } else {
    Pop.closeAll();
  }
}
function search() {
  Pop.closeAll();
  var pid = value("search-pid");
  var fname = value("search-first");
  var lname = value("search-last");
  if (pid == "" && fname == "" && lname == "") {
    return;
  }
  var url = "patients.php?pf1=";
  if (pid != "") {
    url += "uid&pfv1=" + pid;
  } else if (fname != "" && lname != "") {
    url += "first_name&pfv1=" + fname + "&pf2=last_name&pfv2=" + lname; 
  } else if (fname != "") {
    url += "first_name&pfv1=" + fname;
  } else {
    url += "last_name&pfv1=" + lname;
  }
  window.location.href = url;
}      
function pageFocus() {
  if (refreshOnFocus) {
    if (client != null && client.clientId != null) {
      window.location.href = curl + "&pid=" + client.clientId;
      //showClient(client.clientId);
      //refreshOnFocus = false;
    } else {
      refreshPage();
    }
  }
}
function refreshPage() {
  Pop.Working.show("Refreshing page . . .");
  window.location.href = curl;
}
function showClient(id) {
  //if (me) {  // todo pick usergroup
    window.location.href = "face.php?id=" + id;
  //} else {
  //  Pop.Working.show("Retrieving patient data . . .");
  //  sendRequest(
  //      5, "action=getClient" +
  //      "&id=" + id);
  //}
}
function showSearch() {
  showPatientSelector(2, true);
}
function resetPatientPop() {
  setText("pop-p-cap-text", "Patient > " + client.name);
  setText("pop-client-name", client.name);
  setText("pop-client-id", client.uid);
  setText("pop-client-dob", denull(client.birth));
  setText("pop-client-address", denull(address(client)));
  setText("pop-client-phone", denull(client.shipAddress.phone1));
  buildHistoryTable();
}
// TODO, consolidate
function buildHistoryTable() {
  var tbody = $("pss-tbody");
  clearAllRows(tbody);
  var offset = true;
  var lastFd = null;
  var lastTr;
  if (client.events != null) {
    for (var i = 0; i < client.events.length; i++) {
      var e = client.events[i];
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
      tr.appendChild(createTd(e.fts, tdClass));
      var div = document.createElement("div");
      div.className = "lpad";
      var td = createTdAnchor(e.ahref, e.aclass, e.name, null, null);
      div.innerText = denull(e.comment);
      td.appendChild(div);
      td.style.width = "70%";
      tr.appendChild(td);
      tbody.appendChild(tr);
      lastTr = tr;
    }
  }
  $("pss").scrollTop = 0;  
}
function go(sid) {
  refreshOnFocus = true;
  window.open("new-console.php?sid=" + sid + "&mid=default&" + Math.random(), "X" + sid, "height=" + (screen.availHeight - 80) + ",width=" + screen.availWidth + ",top=0,left=0,resizable=1,statusbar=0");
}
function address(client) {
  var a = client.shipAddress;
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
function newNotePop() {
 showNewNote(client.clientId, client.name, null, null);
}
function newNoteCallback(s) {
  go(s.id);
}
function newPatientPop() {
  //showPatient();
  PatientAdd.pop();
}
function editPatient() {
  //showEditPatient(client.clientId, client.events != null);
  showPatient(client);
}
function showPatientCallback() {
  refreshOnFocus = true;
  showClient(c.clientId);
}
function editPatientCallback(c, isNew) {
  if (c == null) {
    closePopRefresh();
  } else {
    if (isNew) {
      refreshOnFocus = false;
      window.location.href = "patients.php?pid=" + c.clientId;
    } else {
      refreshOnFocus = true;
      showClient(c.clientId);
    }
  }
}
function deletePatient() {
  Pop.Confirm.showYesNoCancel("Are you sure you want to delete this patient?", confirmDeletePatient);
}
function confirmDeletePatient(confirmed) {
  if (confirmed) {
    setWorking("working-pe", false);
    setText("working-msg-pe", "Deleting patient . . .");
    postRequest(
        5, 
        "action=deleteClient" +
        "&id=" + client.clientId +
        "&obj=");
  }
}
function closePPop() {
  client = null;
  closePopRefresh();
}
function newSessionKey() {
  return {
    "ugid":null,
    "tid":null,
    "cid":null,
    "kid":null
  };
}

// AJAX callbacks
function clientCallback(jclient) {
  client = jclient;
  resetPatientPop();
  Pop.show("pop-p");
}
function deleteClientCallback() {
  refreshPage();
}