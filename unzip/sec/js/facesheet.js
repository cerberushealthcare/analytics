function pageFocus() {
  if (fs) {
    if (isPopUp()) {
      cuPolling(true);
    } else {
      getFacesheet(cid, fs.cuTimestamp);
    }
  }
}
function pageBlur() {
  if (fs) {
    closeHourglass();
    cuPolling(false);
  }
}
function getFacesheet(id, cuTimestamp) {  // cuTimestamp optional: only returns a facesheet if updated since
  cuPolling(false);
  if (cuTimestamp != null) {
    overlayWorking(true);
  } else {
    showOverlayWorking("Retrieving patient data", null, true);  
  }
  sendRequest(4, "action=getFacesheet&id=" + id + "&cu=" + denull(cuTimestamp));
}
function getFacesheetCallback(f) {
  $("fs-refresh").style.display = "none";
  if (f) {
    fs = f;
    fs.medsHistRendered = false;
    fs.clientHistRendered = false;
    renderPage();
    renderPageBreaks();
    closeOverlayWorking();
    overlayWorking(false);
    if (popedit) {
      showDemo();
      popedit = false;
    }
  } else {  // facesheet not returned (no updates were made since cuTimestamp)
    overlayWorking(false);
  }
  cuPolling(true);
}
function renderPageBreaks() {
  clearPageBreaks();
  var divs = $$$("print", $("bodyContainer"), "DIV");
  var y = 0;
  var pgs = 1;
  var widow;
  for (var i = 0; i < divs.length; i++) {
    var div = divs[i];
    var ht = div.offsetHeight;
    y += ht;
    if ((pgs == 1 && y > 950) || (pgs > 1 && y > 850)) {
      if (pgs == 1) {
        divs[0].parentElement.insertBefore(createDiv("pgbrk", "pghead1"), divs[0]);
      }
      pgs++;
      if (widow) {
        widow.parentElement.insertBefore(createDiv("pgbrk", "pghead"), widow);
        y = widow.offsetHeight + ht;      
        widow = null;
      } else {
        div.parentElement.insertBefore(createDiv("pgbrk", "pghead"), div);
        y = ht;
      }
    } else {
      widow = (divs[i].className == "no-widow") ? divs[i] : null; 
    }
  }
  setPageBreaks(pgs);
}
function clearPageBreaks() {
  var divs = $$$("pgbrk", $("bodyContainer"), "DIV");
  for (var i = 0; i < divs.length; i++) {
    divs[i].parentElement.removeChild(divs[i]); 
  }
}
function setPageBreaks(pgs) {
  var h = "Patient: " + fs.client.name + " (" + fs.client.uid + ")<br>DOB: " + fs.client.birth + " (age " + fs.client.age + ")<br>";
  var divs = $$$("pgbrk", $("bodyContainer"), "DIV");
  for (var i = 0; i < divs.length; i++) {
    var p = "Page " + (i + 1) + " of " + pgs;
    divs[i].innerHTML = (i == 0) ? p : h + p;
  }
}
function refreshFacesheetCallback(f) {
  closeOverlayWorking();
  refreshFacesheet(f);
}
function refreshFacesheet(f) {
  fs.cuTimestamp = f.cuTimestamp;
  switch (f.contains) {
  case 2:  // meds
    fs.meds = f.meds;
    fs.activeMeds = f.activeMeds;
    fs.medsHistByMed = f.medsHistByMed;
    fs.medsHistByDate = f.medsHistByDate;
    renderMeds();
    renderMedsHist();
    //fadeScrollMed(fs.medIdsByName[f.updatedMed]);
    break;
  case 3:  // allergies
    fs.allergies = f.allergies;
    fs.allergiesHistory = f.allergiesHistory;
    renderAllergies();
    flicker("allh-head");
    break;
  case 4:  // vitals
    fs.vitals = f.vitals;
    fs.workflow.vital = f.workflow.vital;
    renderVitals();
    renderWfVital();
    break;
  case 5:  // diagnoses    
    fs.diagnoses = f.diagnoses;
    fs.diagnosesHistory = f.diagnosesHistory;
    renderDiagnoses(); 
    flicker("diah-head");
    break;
  case 6:  // client
    fs.client = f.client;
    fs.workflow.appt = f.workflow.appt;
    fs.workflow.docs = f.workflow.docs;
    renderClient();
    renderClientHistFilterTable();
    renderWfAppt(); 
    renderWfDocs(); 
    break;
  case 7:  // hm
    fs.hms = f.hms;
    fs.hmsHistory = f.hmsHistory;
    fs.hmProcs = f.hmProcs;
    renderHm();
    renderFspHmProc();
    if (fs.resetHmProc) {
      setFspHmProc();
      fs.resetHmProc = false;
    }
    flicker("fsp-hm-head");
    flicker("fsp-hma-head");
    break;
  case 8:  // medhx
    fs.medhx.procs = f.medhx.procs;
    fs.medhx.recs = f.medhx.recs;
    renderMedhx(1);
    
//    for (var hcat in f.histCats) {
//      var fh = f.histCats[hcat];
//      var fsh = fs.histCats[hcat];
//      fsh.hists = fh.hists;
//      fsh.histsHistory = fh.histsHistory;
//      if (fh.histProcs) {
//        fsh.histProcs = fh.histProcs;
//      }
//    }
//    renderHist();
    break;
  case 9:  // sochx
    fs.sochx = f.sochx;
    renderSochx();
    break;
  case 10:  // surghx
    fs.surghx.procs = f.surghx.procs;
    fs.surghx.recs = f.surghx.recs;
    renderSurghx(1);
    break;
  case 11:  // famhx
    fs.famhx.sopts = f.famhx.sopts;
    fs.famhx.puids = f.famhx.puids;
    fs.famhx.recs = f.famhx.recs;
    renderFamhx(1);
  }
}
function refreshFs() {
  getFacesheet(cid, "");
}
function cuPolling(on) {
  if (on) {
    cuPollTimestamp();
    fs.cuInterval = setInterval("cuPollTimestamp()", 15000);
  } else {
    if (fs && fs.cuInterval) {
      clearInterval(fs.cuInterval);
    }
  }
}
function cuPollTimestamp() {
  sendRequest(4, "action=checkCuTimestamp&id=" + cid);
}
function cuPollTimestampCallback(timestamp) {
  if (timestamp != denull(fs.cuTimestamp)) {
    $("fs-refresh").style.display = "block";
    cuPolling(false);
  }
}
function renderPage() {  
  $("med-tbl").style.height = "";
  $("dia-tbl").style.height = "";
  renderClient();
  renderMeds();
  renderAllergies();
  renderDiagnoses();
  renderVitals();
  renderHm();
  renderMedhx();
  renderSurghx();
  renderFamhx();
  renderSochx();
  renderWorkflow();  
}
function resizeDivs() {
  syncHeights(["med-tbl", "dia-tbl"], 100);
  syncHeights(["medhx-box", "famhx-box", "sochx-box"]);
  //$("hm-box").style.height = h + 21;
  //$("hm-div").style.height = $("hm-box").clientHeight - d;
  //var d = $("his-box").clientHeight - $("his-div").clientHeight;
  //$("his-box").style.height = $("vit-box").clientHeight;
  //$("his-div").style.height = $("his-box").clientHeight - d;
}
function renderMeds() {
  setDisabled("med-cmd-toggle", fs.activeMeds == null);
  setDisabled("med-cmd-rx", fs.activeMeds == null);
  fs.activeMedsById = {};
  fs.medIdsByName = {};
  fs.rxById = {};
  loadMedHistory(fs.meds);  // med-picker
  var t = new TableLoader("med-tbody", "off", "med-div");
  var tp = new TableLoader("fsp-med-tbody", "off", "fsp-med-div");
  if (fs.activeMeds) {
    for (var i = 0; i < fs.activeMeds.length; i++) {
      var med = fs.activeMeds[i];
      fs.medIdsByName[med.name] = med.id;
      fs.activeMedsById[med.id] = med;
      var name = (! med.expires) ? med.name : bulletJoin([med.name," <span>" + med.expireText + "</span>"]);
      var click = buildFn("showFspMed", [med.id]);
      t.createTrTd();
      t.append(createAnchor(null, null, "fs", null, name, click));
      t.append(createSpan("lpad", med.text));
      href = "javascript:showMed"  + argJoin([
          med.id,
          med.name,
          med.amt,
          med.freq,
          med.asNeeded,
          med.meals,
          med.route,
          med.length,
          med.disp]);
      tp.createTrTd("check");
      tp.append(createCheckbox("sel-med", med.id));
      tp.createTd("nowrap");
      tp.append(createAnchor("med-a-" + med.id, href, "fs", null, name));
      tp.append(createSpan("lpad", med.text));
      // tp.createTd("w60", med.text);
    }
  } else {
    var click = "showFspMed()";
    t.createTrTd();
    t.tr.className = "";
    t.append(createAnchor(null, null, "fsnone", "(None Recorded)", null, click));
    tp.createTrTd(null, null, "&nbsp;");
  }
  resizeDivs();
  //setDisabled("med-cmd-toggle", meds.length == 0);
  //setDisabled("med-cmd-rx", meds.length == 0);
  //setDisabled("med-cmd-add", false);
}
function renderMedsHist() {
  var t = new TableLoader("fsp-medh-tbody-1", "off", "fsp-medh-div-1");
  if (fs.medsHistByDate) {
    t.batchLoad(fs.medsHistByDate, renderMedsHistByDateRow);
  } else {
    // TODO
  }
  t = new TableLoader("fsp-medh-tbody-2", "off", "fsp-medh-div-2");
  t.defineFilter(medHistFilter(), medFilterCallback);
  if (fs.medsHistByMed) {
    fs.prevMed = null;
    t.batchLoad(fs.medsHistByMed, renderMedsHistByMedRow, TableLoader.EOF_CALLBACK);
  } else {
    // TODO
  } 
  fs.medsHistRendered = true;
}
function renderMedsHistByDateRow(t, med) {
  if (med.rx) {
    fs.rxById[med.id] = med;
  } 
  t.createTr(med.date, [med.date, med.sessionId]);
  t.createTd("histbreak", med.date);
  t.createTdAppend(null, createSessionAnchor(med.sessionId));
  t.createTd(null, null, med.quid + ": <b>" + med.name + "</b>");
  appendRxAnchor(t, med);  
}
function renderMedsHistByMedRow(t, med) {
  if (med) {
    var date = med.date;
    t.createTr(med.name, [med.name], medHistFilter(med));
    var c;
    //var n;
    if (med.active) {
      //if (t.offset.breakThru == -1) {
      //  t.tr.id = "med-tr-" + fs.medIdsByName[med.name];
      //}
      c = "histbreak active";
      //n = bulletJoin([med.name + "<span>", "Active</span>"]);
    } else {
      c = "histbreak inactive";
      //n = med.name;
    }
    t.createTd(c, null, med.name);
    t.createTd("nowrap", date);
    t.createTd(null, med.quid);
    t.createTd();
    t.append(createSessionAnchor(med.sessionId));
    appendRxAnchor(t, med);
    //fs.prevMed = med;
  } else {
    t.loadFilterSidebar("medh-filter-ul", TableLoader.NO_FILTER_COUNT);
    t.applyFilter(fs.filterMed);
    fs.filterMed = null;
    fs.medHistTable = t;
  }
}
function medFilterCallback(t) {
  if (t.allFilterValuesNull()) {  
    showHide('fsp-medh-div-1','fsp-medh-div-2');
  } else {
    showHide('fsp-medh-div-2','fsp-medh-div-1');
  }
}
function medHistFilter(med) {
  return {
      //"hide":(med) ? ((med.active) ? "Active" : "Inactive") : null,
      "hide":(med) ? justMedName(med.name) : null
      };
}
function appendRxAnchor(t, med) {
  t.createTd();
  if (med.rx) {
    var click = "javascript:showMedRx(" + med.id + ")";
    var a = createAnchor(null, null, "rx", null, "&nbsp;", click);
    t.append(a);
    a.title = med.rx;
  }
}
function fsRx() { 
  var checkedIds = getCheckedValues("sel-med", "fsp-med-tbody");
  for (var i = 0; i < fs.activeMeds.length; i++) {
    fs.activeMeds[i].checked = null;
  }
  for (var i = 0; i < checkedIds.length; i++) {
    fs.activeMedsById[checkedIds[i]].checked = true;
  }
  var rx = {
      "date":calToday2(),
      "me":me,
      "docs":fs.docs,
      "client":fs.client,
      "meds":fs.activeMeds,
      "showMedList":true
      };
  showRx(rx);
}
function rxCallback(meds) {
  showOverlayWorking("Updating");
  postRequest(4, "action=printMeds&obj=" + jsonUrl(meds));
}
function showFspMed(id) {
//  if (id) {
//    click("med-by-med");
//  } else {
//    click("med-by-date");
//  }
  if (id) {
    fs.filterMed = medHistFilter(fs.activeMedsById[id]);
    fs.filterMed["hide"] = null;
  } else {
    fs.filterMed = null;
  }
  setChecks("sel-med", "fsp-med-tbody", false);
  //fadeScrollMed(id);
  if (! fs.medsHistRendered) {
    zoomPop("fsp-med");
    sendRequest(4, "action=getMedHist&id=" + fs.client.id);
  } else {
    if (fs.medHistTable) {
      fs.medHistTable.applyFilter(fs.filterMed);
    }
    showOverlayPop("fsp-med");
  }
}
function getMedHistCallback(f) {
  refreshFacesheet(f);  
}
function fadeScrollMed(id) {
  if (id) {
    fade(scrollToTr("med-tr-" + id));
  }
}
function medOkCallback(m) {
  showOverlayWorking("Saving");
  m.clientId = cid;
  postRequest(4, "action=saveMed&obj=" + jsonUrl(m));
}
function medDeleteCallback(id) {
  showOverlayWorking("Removing");
  sendRequest(4, "action=deactivateMed&id=" + id);
}
function deleteMeds() {
  if (getCheckedValues("sel-med", "fsp-med-tbody").length > 0) {
    showConfirmDeleteChecked(deleteMedsConfirmed, "remove");  
  } else {
    showNoSel();
  }
}
function deleteMedsConfirmed(confirmed) {
  if (confirmed) {
    showOverlayWorking("Removing");
    postRequest(4, "action=deactivateMeds&obj=" + jsonUrl(getCheckedValues("sel-med", "fsp-med-tbody")));
  }
}
function showNoSel() {
  showCritical("Nothing was selected.");
}
function renderWorkflow() {
  renderWfVital();
  renderWfAppt();
  renderWfDocs();
}
function renderWfVital() {
  var a;
  var href;
  var div = clearChildren($("wf-vit"));
  if (fs.workflow.vital) {
    href = "javascript:editVitals(" + fs.workflow.vital.id + ")";
    a = createAnchor(null, href, "qcmd qvital", null, "Vitals <span>(" + bulletJoin(fs.workflow.vital.all) + ")</span>");
  } else {
    href = "javascript:editVitals()";
    a = createAnchor(null, href, "qicon qnew-vital", "Record Today's Vitals...");
  }
  div.appendChild(a);
  if (a.clientWidth > 400) {
    a.style.width = 400;
  }
}
function renderWfAppt() {
  var a;
  var href;
  var div = clearChildren($("wf-appt"));
  if (fs.workflow.appt) {
    var e = fs.workflow.appt;
    href = "javascript:editAppt(" + e.id + ")";
    href = "schedule.php?pe=1&pop=" + e.id;
    a = createAnchor(null, href, "qcmd qappt", null, e.time + " - " + e.type);
  } else {
    href = "javascript:newAppt(" + fs.client.id + ")";
    a = createAnchor(null, href, "qicon qnew-appt", "New Appointment...");
  }
  div.appendChild(a);
}
function renderWfDocs() {
  var a;
  var href;
  var ul = clearChildren($("wf-doc-ul"));
  if (fs.workflow.docs) {
    var li = addListItem(ul);
    for (var i = 0; i < fs.workflow.docs.length; i++) {
      var e = fs.workflow.docs[i];
      href = "javascript:editDoc(" + e.id + ")";
      a = createAnchor(null, null, "qcmd qnote", null, e.label + " <br><span>(" + e.date + ")</span>", href);
      li.appendChild(a);
    }
    //href="javascript:";
    //a = createAnchor(null, href, "qmore", "More...");
    //li.appendChild(a);
  }
  a = createAnchor(null, "javascript:createNewDoc()", "qicon qnew-note", "Create New Document...");
  addListItem(ul).appendChild(a);
  a = createAnchor(null, "javascript:createNewMsg()", "qicon qnew-msg", "Send New Message...");
  addListItem(ul).appendChild(a);
}
function renderAllergies() {
  setDisabled("all-cmd-toggle", fs.allergies == null);
  fs.activeAllergiesById = {};
  var t = new TableLoader("all-tbody", "off", "all-div");
  var tp = new TableLoader("fsp-all-tbody", "off", "fsp-all-div");
  $("all-tbl").style.border = (fs.allergies) ? "2px dashed red" : "";
  if (fs.allergies) {
    for (var i = 0; i < fs.allergies.length; i++) {
      var allergy = fs.allergies[i];
      fs.activeAllergiesById[allergy.id] = allergy;
      var click = "showFspAll()";
      var html = bulletJoin(allergy.reactions);
      t.createTrTd();
      t.append(createAnchor(null, null, "fs aller", allergy.agent, null, click), createSpan("lpad", null, null, html));
      var href = "javascript:editAllergy(" + allergy.id + ")";
      tp.createTrTd("check");
      var c = createCheckbox("sel-all", allergy.id);
      tp.append(c);
      tp.createTd();
      var a = createAnchor(null, href, "fs", allergy.agent)
      tp.append(createAnchor(null, href, "fs", allergy.agent), createSpan("lpad", null, null, html));
    }
  } else {
    var click = "showFspAll()";
    t.createTrTd();
    t.tr.className = "";
    t.append(createAnchor(null, null, "fsnone", "(None Known)", null, click));
    tp.createTrTd(null, null, "&nbsp;");
  }
  t = new TableLoader("fsp-allh-tbody", "off", "fsp-allh-div");
  if (fs.allergiesHistory) {
    for (var i = 0; i < fs.allergiesHistory.length; i++) {
      var allergy = fs.allergiesHistory[i];
      t.createTr(allergy.date, [allergy.date, allergy.sessionId]);
      t.createTd("histbreak nowrap", allergy.date);
      t.createTdAppend(null, createSessionAnchor(allergy.sessionId));
      t.createTd(null, null, allergyText(allergy));
    }
  } else {
    // TODO
  }
  // setDisabled("all-cmd-add", false);
}
function allergyText(a) {
  var text = "<b>" + a.agent + "</b>";
  if (! isEmpty(a.reactions)) {
    text += ": " + a.reactions.join(", ");
  }
  return text;
}
function showFspAll() {
  zoomPop("fsp-all");
  //showOverlayPop("fsp-all");
}
function renderDiagnoses() {
  setDisabled("dia-cmd-toggle", fs.diagnoses == null);
  fs.diagnosesById = {};
  var t = new TableLoader("dia-tbody", "off", "dia-div");
  var tp = new TableLoader("fsp-dia-tbody", "off", "fsp-dia-div");
  if (fs.diagnoses) {
    for (var i = 0; i < fs.diagnoses.length; i++) {
      var diagnosis = fs.diagnoses[i];
      var text = diagnosis.text;
      if (diagnosis.icd) {
        text += " (" + diagnosis.icd + ")";
      }
      fs.diagnosesById[diagnosis.id] = diagnosis;
      var click = "showFspDia()";
      t.createTrTd();
      t.append(createAnchor(null, null, "fs", text, null, click));
      var href = "javascript:editDiagnosis(" + diagnosis.id + ")";
      tp.createTrTd("check");
      var c = createCheckbox("sel-dia", diagnosis.id);
      tp.append(c);
      tp.createTd();
      tp.append(createAnchor(null, href, "fs", text));
    }
    if (fs.diagnosesHistory) {
      t = new TableLoader("fsp-diah-tbody", "off", "fsp-diah-div");
      t.defineFilter(diaHistFilter());
      for (var i = 0; i < fs.diagnosesHistory.length; i++) {
        var diagnosis = fs.diagnosesHistory[i];
        t.createTr(diagnosis.date + diagnosis.sessionId, [diagnosis.date, diagnosis.sessionId], diaHistFilter(diagnosis));
        t.createTd("histbreak", diagnosis.date);
        t.createTdAppend(null, createSessionAnchor(diagnosis.sessionId));
        t.createTd(null, null, diagnosis.text);
      }
      t.loadFilterSidebar("diah-filter-ul", TableLoader.NO_FILTER_COUNT);
    } else {
      // TODO
    }
  } else {
    var click = "showFspDia()";
    t.createTrTd();
    t.tr.className = "";
    t.append(createAnchor(null, null, "fsnone", "(None Recorded)", null, click));
    tp.createTrTd(null, null, "&nbsp;");
  }
  resizeDivs();
}
function diaHistFilter(d) {
  return {
      "Diagnosis":(d) ? d.text : null
      };
}
function showFspDia() {
  zoomPop("fsp-dia");
  //showOverlayPop("fsp-dia");
}
function renderHmProcs() {
  if (fs.hmProcs) {
    var tpp = new TableLoader("pp-tbody", "off", "pp-div");
    for (var pcid in fs.hmProcs) {
      var proc = fs.hmProcs[pcid];
      var href = "javascript:selectProc(" + proc.id + ")";
      tpp.createTrTd();
      tpp.append(createAnchor(null, href, "fs", proc.name));
      tpp.createTd(null, null, yesIf(proc.auto));
      tpp.createTd(null, null, nbsp(formatGender(proc)));
      tpp.createTd(null, null, nbsp(formatAgeRange(proc)));
      tpp.createTd(null, null, nbsp(formatInterval(proc)));
    }
  }  
}
function renderHm() {
  setDisabled("hm-cmd-add", false);
  renderHmProcs();
  var t = new TableLoader("hm-tbody", "off", "hm-div");
  var ta = new TableLoader("fsp-hma-tbody", "off", "fsp-hma-div");
  fs.hmqs = {};
  fs.hmFacesByProcId = {};
  if (fs.hms) {
    for (var i = 0; i < fs.hms.length; i++) {
      var hm = fs.hms[i];
      fs.hmFacesByProcId[hm.procId] = hm;
      var name = (hm.nextExpireText) ? bulletJoin([hm.proc, " <span>" + hm.nextExpireText + "</span>"]) : hm.proc; 
      var next = (hm.nextExpireText) ? "<span class='red'>" + hm.nextShort + "</span>" : hm.nextShort;
      var results = ellips(simpleBulletJoin(hm.results), 120);
      t.createTrTd("nowrap");
      t.append(createAnchor(null, buildHrefFn("showFspHm", [hm.procId]), "fs", null, name));
      t.createTd("nowrap", null, nbsp(hm.dateShort));
      t.createTd(null, null, results);
      //t.createTd();
      //t.append(createDiv(null, "hm-results", null, results));
      t.createTd("nowrap norb", null, nbsp(next));
      ta.createTrTd();
      ta.append(createAnchor(null, buildHrefFn("setFspHmProc", [hm.procId]), "fs", null, name));
      ta.createTd("nowrap", null, nbsp(hm.dateShort));
      ta.createTd(null, null, results);
      ta.createTd("nowrap", null, nbsp(next));
    }
  } else {
    var click = "showFspHm()";
    t.createTrTd();
    t.td.colSpan = 4;
    t.tr.className = "";
    t.append(createAnchor(null, null, "fsnone", "(None Recorded)", null, click));
  }
  renderHmHist();
}
function renderHmHist() {
  fs.hmTable = null;
  var t = new TableLoader("fsp-hm-tbody", "off", "fsp-hm-div");
  var tp = new TableLoader("hmprt-tbody", "off", "hmprt-div");
  t.defineFilter(hmFilter((fs.hmProc) ? fs.hmProc.name : null), hmFilterCallback);
  if (fs.hmsHistory) {
    fs.hmsById = {};
    for (var i = 0; i < fs.hmsHistory.length; i++) {
      var hm = fs.hmsHistory[i];
      fs.hmsById[hm.id] = hm;
      var results = bulletJoin(hm.results);
      t.createTr(null, null, hmFilter(hm.proc), hm.procId);
      t.tr.className = (hm.sessionId == "0") ? null : "hide";
      t.createTd("check");
      t.append(createCheckbox("sel-hm", hm.id));
      t.createTd("nowrap");
      t.append(createAnchor(null, buildHrefFn("editHm", [hm.id]), "fs", hm.proc));
      t.createTd("nowrap", null, hm.dateShort);
      t.createTd(null, null, results);
      if (hm.sessionId == "0") {
        tp.createTr();
        tp.createTd("fs", hm.proc);
        tp.createTd("w80", null, bulletJoin([hm.dateShort, results], true));
      }
//      ta.createTr(hm.procId, [hm.procId]);
//      ta.createTd("nowrap");
//      ta.append(createAnchor("hm-a-" + hm.id, buildHrefFn("setFspHmProc", [hm.procId]), "fs", hm.proc));
//      ta.createTd("nowrap", null, hm.dateShort);
//      ta.createTd(null, null, results);
    }
  }
  t.loadFilterSidebar("hm-filter-ul", TableLoader.NO_FILTER_COUNT);
  fs.hmTable = t;
  hmShowHideDelete(t);
}
function hmFilter(proc) {
  return {
      "Test/Procedures":proc
      };
}
function showFspHm(procId) {
  setFspHmProc(procId);
  zoomPop("fsp-hm", null, zoomFspHmCallback);
  //showOverlayPop("fsp-hm");
}
function zoomFspHmCallback() {
  if (fs.hms == null) {
    showProcPick();
  }  
}
function hmFilterCallback(t) {
  var value = t.getTopFilterValue();
  if (value == null) {  // all
    setFspHmProc(null, true);
  } else {
    var keys = t.getVisibleRowKeys();
    var procId = keys[0];
    setFspHmProc(procId, true);
    hmShowHideDelete(t);
  }
}
function hmShowHideDelete(t) {
  var keys = t.getVisibleRowKeys();
  showHideIf(keys.length == 1, "fsp-hm-deactivate", "fsp-hm-delete");
}
function setFspHmProc(procId, noApplyFilter) {
  if (procId) {
    if (fs.hmProc == null || fs.hmProc.id != procId) {
      setChecks("sel-hm", "fsp-hm-tbody", false);
      fs.hmProc = fs.hmProcs[procId];
      renderFspHmProc();
      //setText("hm-show-another-label", "Choose another...");
      //show("hm-show-one-td");
      toggleHmView(true);
      if (! noApplyFilter) {
        fs.hmTable.applyTopFilterValue(fs.hmProcs[procId].name);
      }
    }
  } else {
    fs.hmProc = null;
    fs.hmFace = null;
    toggleHmView(false);
    if (! noApplyFilter) {
      fs.hmTable.applyTopFilterValue(null);
    }
  }
}
function renderFspHmProc() {
  if (fs.hmProc) {
    fs.hmFace = fs.hmFacesByProcId[fs.hmProc.id];
    //setText("hm-show-one-label", fs.hmProc.name + " history");
    //setText("hm-cmd-add", "Add " + fs.hmProc.name + " Results...");
    setText("hm-one-proc", fs.hmProc.name);
    setHtml("hm-one-proc-desc", formatProcDesc(fs.hmProc));
    if (fs.hmFace) {
      show("hm-face-entry");
      attachCalendarQuestion($("hme-next-due"), "Next Due", fs.hmFace.nextText);
      setIntervalText(fs.hmFace, fs.hmProc);
      if (fs.hmFace.nextExpireText) {
        show("hme-next-info").innerText = "This test/procedure is " + fs.hmFace.nextExpireText + ".";
      } else {
        hide("hme-next-info");
      }
    } else {
      hide("hm-face-entry");
    }
  }
}
function setIntervalText(face, proc) {
  var a = $("hme-interval");
  if (face.every != null) {
    setAnchorText(a, formatInterval(face));
    a.className = "";
  } else {
    setAnchorText(a, formatInterval(proc));
    a.className = "df";
  }  
}
function getIntervalObject() {  // return face if overrides proc
  return fs.hmFace.every != null ? fs.hmFace : fs.hmProc;  
}
function toggleHmView(one) {
  if (one) {
    //fs.hmTable.applyFilter(fs.hmFilterKey, fs.hmProc.name);
    show("hm-one");
    showHide("fsp-hma-2", "fsp-hma-1");
  } else {
    //fs.hmTable.applyFilter(fs.hmFilterKey, null);
    hide("hm-one");
    showHide("fsp-hma-1", "fsp-hma-2");
  }
  flicker("fsp-hm-head");
  flicker("fsp-hma-head");
}
function showProcPick() {
  //setCheck("hm-show-another", false);
  $("pp-div").scrollTop = 0;
  showOverlayPop("pop-pp");
}
function selectProc(id) {
  closeOverlayPop();
  if (fs.hmFacesByProcId[id]) {
    setFspHmProc(id);
  } else {
    showOverlayWorking();
    fs.hmProcAdd = id;
    var h = {
        clientId:cid,
        proc:fs.hmProcs[id]};
    postRequest(4, "action=addFacesheetHm&obj=" + jsonUrl(h));  
  }
}
function addFacesheetHmCallback(f) {
  refreshFacesheetCallback(f);
  setFspHmProc(fs.hmProcAdd);
}
function formatProcDesc(proc) {
  var gender = formatGender(proc);
  var age = formatAgeRange(proc);
  var every = formatInterval(proc);
  var a = [];
  if (gender != null) {
    a.push(gender + "s");
  } 
  if (age != null) {
    a.push(age);
  }
  if (every != null) {
    a.push(every);
  }
  var desc = "Recommendations: " + ((a.length == 0) ? "None" : bulletJoin(a)); 
  return desc;
}
function formatGender(proc) {
  return (proc.gender == null) ? null : (proc.gender == "F") ? "Female" : "Male";
}
function formatAgeRange(proc) {
  if (proc.after == null && proc.until == null) {
    return null;
  }
  var s = (proc.after) ? "Age " + proc.after : "";
  if (proc.until) {
    if (s == "") {
      s = " Up to age " + proc.until;
    } else {
      s += " to " + proc.until;
    } 
  } else {
    s += " and up";
  }
  return s;
}
function formatInterval(p) {
  if (p && p.every) {
    switch(p["int"]) {
      case 0:
        return (p.every == 1) ? "Annually" : "Every " + p.every + " years";
      case 1:
        return (p.every == 1) ? "Monthly" : "Every " + p.every + " months"; 
      case 2:
        return (p.every == 1) ? "Weekly" : "Every " + p.every + " weeks"; 
      case 3:
        return (p.every == 1) ? "Daily" : "Every " + p.every + " days"; 
    }
  }
  return null;
}
function attachCalendarQuestion(a, desc, value) {
  var q = newCalendarQuestion(desc);
  q.a = a;
  a.q = q;      
  setAnchorTextByAnchor(a, value);  
}
function editHm(id, focusId) {
  //fs.hmFocus = (focusId) ? $(focusId) : null;
  if (id) {
    fs.hm = fs.hmsById[id];
  } else {
    fs.hm = {id:null, dateText:null, results:null};
  }
  var proc = fs.hmProc;
  if (proc.quid && ! fs.hmqs[proc.quid]) {
    overlayWorking(true);
    sendRequest(4, "action=getQuestionByQuid&id=" + proc.quid);
  } else {
    loadHmEntry();
  }
}
function getQuestionByQuidCallback(q) {
  overlayWorking(false);
  fs.hmqs[q.quid] = q;
  loadHmEntry();
}
function loadHmEntry() {
  var proc = fs.hmProc;
  setText("hme-proc", proc.name);
  showIf(fs.hm.id, "hme-delete-span");
  var tform = new TemplateForm($("ul-hme-fields"), "first2", fs.hmqs);
  tform.addLiAppend("Date Performed", null, "dateText", fs.hm.dateText, null, TemplateForm.Q_DEF_CALENDAR);
  tform.addLiAppend("Results", proc.quid, "results", fs.hm.results, "qr");
  showOverlayPop("pop-hme");
  fs.htform = tform;
}
function hmeSave() {
  closeOverlayPop();
  showOverlayWorking("Saving");
  postRequest(4, "action=saveHm&obj=" + jsonUrl(buildDataHm()));  
}
function hmeDelete() {
  showConfirmDelete(hmeDeleteConfirmed, "remove");
}
function hmeDeleteConfirmed(confirmed) {
  if (confirmed) {
    closeOverlayPop();
    showOverlayWorking("Removing");
    sendRequest(4, "action=deactivateHm&id=" + fs.hm.id);
  }
}
function deleteBlankHm() {
  showConfirmDelete(deleteBlankHmConfirmed, "remove");
}
function deleteBlankHmConfirmed(confirmed) {
  if (confirmed) {
    showOverlayWorking("Removing");
    var id = fs.hmFacesByProcId[fs.hmProc.id].id;
    sendRequest(4, "action=deactivateHm&id=" + id);
    fs.resetHmProc = true;
  }
}
function deleteHms() {
  if (getCheckedValues("sel-hm", "fsp-hm-tbody").length > 0) {
    showConfirmDeleteChecked(deleteHmsConfirmed, "remove");  
  } else {
    showNoSel();
  }
}
function deleteHmsConfirmed(confirmed) {
  if (confirmed) {
    showOverlayWorking("Removing");
    postRequest(4, "action=deactivateHms&obj=" + jsonUrl(getCheckedValues("sel-hm", "fsp-hm-tbody")));
  }
}
function buildDataHm() {
  var rec = fs.htform.buildRecord(TemplateForm.VALUES_ALWAYS_ARRAY, null, true);
  var h = {};
  h.id = fs.hm.id;
  h.clientId = cid;
  h.type = 1;  // todo
  h.procId = fs.hmProc.id;
  h.proc = fs.hmProc.name;
  //var q = $("hme-results").q;
  //h.results = toJSONString(qOptTextArray(q.opts, q.sel, 0));
  //h.dateText = questionFieldText($("hme-date"));
  h.results = toJSONString(rec.results);
  h.dateText = rec.dateText[0];
  h.dateSort = calFormatShortDate(calParse(h.dateText, CAL_FMT_SENTENCE));
  return h; 
}
function setAnchorTextByAnchor(a, text) {
  var q = a.q;
  if (text == null) {
    q.sel = [];
  } else {
    qSetByValue(q, text);
  }
  setAnchorTextByQuestion(q);
}
function setAnchorTextByQuestion(q, useUid) {
  if (q.sel.length == 0) {
    setAnchorText(q.a, null);
  } else {
    var text = useUid ? qSelUid(q) : qSelText(q);
    if (q.extract) {
      text = extractDate(text);
    }
    setAnchorText(q.a, text);
  } 
}
function setAnchorText(a, text) {
  if (text == null) {
    a.innerHTML = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    a.className = "df";
  } else {
    a.innerText = text;
    a.className = "";
  }
  try {
    a.focus();
  } catch (e) {}
}
function hmNextDateCallback(q) {
  setAnchorTextByQuestion(q);
  showOverlayWorking("Updating");
  fs.hmFace.nextText = questionFieldText($("hme-next-due"));
  fs.hmFace.nextSort = calFormatShortDate(calParse(fs.hmFace.nextText, CAL_FMT_SENTENCE));
  postRequest(4, "action=saveHm&obj=" + jsonUrl(fs.hmFace));  
}
function questionCallback(q) {
  setAnchorTextByQuestion(q);
  showNextQuestion(q);
}
function showCustomProc() {
  doOverlayWork("hmcpBuild()");
}
function hmcpBuild() {
  var t = new TableLoader("hmcp-tbody", "off", "hmcp-div");
  for (var pcid in fs.hmProcs) {
    var proc = fs.hmProcs[pcid];
    hmcpBuildRow(t, proc);
  }
  var empty = {
      id:null,
      name:"(New Test/Procedure)",
      active:false
      };
  for (var i = 0; i < 5; i++) {
    hmcpBuildRow(t, empty);
  }
  closeOverlayWorking();
  showOverlayPop("fsp-hmcp");
}
function hmcpBuildRow(t, proc) {
  var cb = createCheckbox("sel-hmcp", proc.id);
  t.createTrTd("wcheck");
  t.append(cb);
  cb.sibs = {};
  cb.sibs.name = createTextbox("hmcp-name-" + t.rows(), proc.name, 40);
  cb.sibs.name.cb = cb;  // to allow activate onclick 
  if (proc.id != null) {
    cb.sibs.name.readOnly = true;
  }
  cb.sibs.auto = createCheckbox("hmcp-auto", true);
  cb.sibs.gender = createSelect(null, null, {"F":"female","M":"male"}, proc.gender, "");
  cb.sibs.after = createTextbox(null, proc.after, 1);
  cb.sibs.until = createTextbox(null, proc.until, 1);
  cb.sibs.every = createTextbox(null, proc.every, 1);
  cb.sibs["int"] =  createSelect(null, null, intComboArray(), proc["int"], "");
  t.createTdAppend(null, cb.sibs.name);
  t.createTdAppend("wcheck", cb.sibs.auto);
  t.createTdAppend(null, cb.sibs.gender);
  t.createTdAppend(null, cb.sibs.after);
  t.createTdAppend(null, cb.sibs.until);
  t.createTd();
  t.append(cb.sibs.every);
  t.append(cb.sibs["int"]);
  cb.checked = proc.active;
  hmcpToggleActive(cb, true);
  cb.sibs.auto.checked = proc.auto;
  hmcpToggleColorCheck(cb.sibs.auto);
}
function intComboArray() {
  return {0:"year(s)",1:"month(s)",2:"week(s)",3:"day(s)"};
}
function hmcpClick() {
  var e = event.srcElement;
  if (e) {
    if (e.sibs) {
      hmcpToggleActive(e);
    } else if (e.cb && e.disabled) {
      e.cb.checked = true;
      hmcpToggleActive(e.cb);
    } else if (e.id == "hmcp-auto") {
      hmcpToggleColorCheck(e);
    }
  }
}
function hmcpToggleColorCheck(e) {
  e.className = (e.checked) ? "color-check" : "";
}
function hmcpToggleActive(e, noFocus) {
  var doFocus = (! noFocus && e.checked);
  for (var name in e.sibs) {
    var sib = e.sibs[name];
    setDisabledElement(sib, ! e.checked);
    if (doFocus) {
      if (! sib.readOnly) {
        focus(sib.id);
      }
      doFocus = false;
    }
  } 
}
function hmcpSave() {
  var procs = hmcpBuildDatas(getCheckboxes("sel-hmcp", "hmcp-tbody"));
  postRequest(4, "action=saveDataHmProcs&cid=" + cid + "&obj=" + jsonUrl(procs));
  closeOverlayPop();
  showOverlayWorking("Saving");
}
function hmcpBuildDatas(cbs) {
  var procs = {};
  var inst = 0;
  for (var i = 0; i < cbs.length; i++) {
    var cb = cbs[i];
    if (cb.value || cb.checked) {
      inst++;
      var proc = hmcpBuildData(cbs[i]);
      if (proc.id == null) {
        proc.id = inst;
      }      
      procs[proc.id] = proc;
    }
  }
  return procs;  
}
function hmcpBuildData(cb) {
  var d = {};
  d.id = nullify(cb.value);
  d.active = cb.checked;
  for (var name in cb.sibs) {
    var sib = cb.sibs[name];
    if (sib.type == "checkbox") {
      d[name] = sib.checked;
    } else {
      d[name] = nullify(sib.value);
    }
  }
  return d; 
}
function editHmeInterval() {
  var p = getIntervalObject();
  setValue("hmcint-every", p.every);
  setValue("hmcint-int", p["int"]);
  showOverlayPop("fsp-hmcint", "hmcint-every", true);
}
function hmcintOk() {
  closeOverlayPop();
  showOverlayWorking("Updating");
  fs.hmFace.every = value("hmcint-every");
  fs.hmFace["int"] = value("hmcint-int");
  postRequest(4, "action=saveHmInt&obj=" + jsonUrl(fs.hmFace));  
}
function hmcintClear() {
  closeOverlayPop();
  showOverlayWorking("Updating");
  fs.hmFace.every = null;
  fs.hmFace["int"] = null;
  postRequest(4, "action=saveHmInt&obj=" + jsonUrl(fs.hmFace));  
}
function renderVitals() {
  var t = new TableLoader("vit-tbody", "off", "vit-div");
  if (fs.vitals) {
    fs.vitalsById = {};
    var tp = new TableLoader("fsp-vith-tbody", "off", "fsp-vith-div");
    for (var i = 0; i < fs.vitals.length; i++) {
      var vital = fs.vitals[i];
      fs.vitalsById[vital.id] = vital;
      var click = "showFspVit(" + vital.id + ")";
      t.createTrTd();
      t.append(createAnchor(null, null, "fs vital", vital.dateText, null, click));
      //t.createTd("vitals");
      if (vital.all) {
        //t.td.innerHTML = bulletJoin(vital.all);
        var div = createDiv(null, "vit-text", null, bulletJoin(vital.all));
        t.append(div);
      }
      var href = "javascript:editVitals(" + vital.id + ")";
      tp.createTrTd("main");
      tp.append(createAnchor(null, href, "fs vital", vital.dateText));
      tp.createTd(null, vital.pulse);
      tp.createTd(null, vital.resp);
      tp.createTd("nowrap", vital.bp);
      tp.createTd(null, vital.temp);
      tp.createTd(null, vital.wt);
      tp.createTd(null, vital.height);
      tp.createTd(null, vital.bmi);
      tp.createTd(null, vital.wc);
      tp.createTd(null, vital.hc);
      tp.createTd("nowrap", vital.o2);
    }
    setDisabled("vit-cmd-add", false);
  }
  resizeDivs();
}
function showFspVit(id) {
  fs.vid = id;
  zoomPop("fsp-vit", null, zoomFspVitCallback);
  //showOverlayPop("fsp-vit");
}
function zoomFspVitCallback() {
  if (fs.vid) {
    editVitals(fs.vid);
  }
}
function renderClient() {
  var c = fs.client;
  setPageTitle(c.name);
  setCaption("fsp-med-cap-text", c.name + " - Medications");
  setCaption("fsp-all-cap-text", c.name + " - Allergies");
  setCaption("fsp-dia-cap-text", c.name + " - Diagnoses");
  setCaption("fsp-vit-cap-text", c.name + " - Vitals");
  setCaption("fsp-hm-cap-text", c.name + " - Health Maintenance");
  setCaption("fsp-his-cap-text", c.name + " - Documentation/Visits");
  setCaption("fsp-hx-cap-text", c.name + " - History");
  setCaption("pop-ve-cap-text", c.name + " - Vitals Entry");
  setCaption("pop-hme-cap-text", c.name + " - Health Maintenance Entry");
  setCaption("pop-hxe-cap-text", c.name + " - History Entry");
  setCaption("pop-de-cap-text", c.name + " - Diagnosis Entry");
  setText("h1-name", c.name);
  setText("dem-cid", c.uid);
  setText("dem-dob", c.birth);
  setText("dem-age", c.age);
  setText("dem-lbl-flags", "");
  setText("dem-flags", "");
  if (! c.age || c.age < 18) {
    var parents = [];
    if (c.fatherAddress && c.fatherAddress.name) parents.push(c.fatherAddress.name);
    if (c.motherAddress && c.motherAddress.name) parents.push(c.motherAddress.name);
    if (parents.length) {
      setText("dem-lbl-flags", "Parent(s):");
      setHtml("dem-flags", bulletJoin(parents)).className = "ro";
    }    
  } else {
    if (c.cdata5 || c.cdata6) {
      var flags = [];
      if (c.cdata5) flags.push("Living Will");
      if (c.cdata6) flags.push("Power of Attorney");
      setText("dem-lbl-flags", "On File:");
      setHtml("dem-flags", bulletJoin(flags)).className = "ro red";
    }
  }
  var pl = new ProfileLoader("dem-lbl-addr", "dem-addr");
  var a = c.shipAddress;
  pl.add("Address:", [a.addr1, a.addr2, a.csz, poFormatPhone(a.phone1, a.phone1Type)]);
  renderClientNotes();
  renderClientHistory();
}
function renderClientNotes() {
  if (fs.client.notes) {
    setClass("notepad", "full");
    hide("notepad-empty");
    show("notepad-text").innerHTML = fs.client.notes;
  } else {
    setClass("notepad", "");
    show("notepad-empty");
    hide("notepad-text");
  }
}
function renderClientHistory() {
  var t = new TableLoader("his-tbody", "off", "his-div");
  var hist = fs.clientHistory;
  if (hist && hist.all) {
    for (var i = 0; i < hist.all.length; i++) {
      var e = hist.all[i];
      t.createTr(e.date, [e.date]);
      t.createTd("bold", e.date);
      t.createTdAppend(null, createHistAnchor(e));
    }
  }
  fs.clientHistory.filteredSessions = fs.clientHistory.sessions;    
  resizeDivs();
}
function renderClientHistFilterTable() {
  var hist = fs.clientHistory;
  if (hist && hist.all) {
    var tp = new TableLoader("fsp-his-tbody", "off", "fsp-his-div", "fsp-his-head");
    var tpa = new TableLoader("fsp-hisa-tbody", "off", "fsp-hisa-div", "fsp-hisa-head");
    var tpm = new TableLoader("fsp-hism-tbody", "off", "fsp-hism-div", "fsp-hism-head");
    tp.defineFilter(histFilter(), hisFilterCallback);
    tpa.defineFilter(histaFilter());
    tpm.defineFilter(histmFilter());
    tp.tpa = tpa;
    tp.tpm = tpm;
    tp.batchLoad(hist.all, renderClientHistoryRow, TableLoader.EOF_CALLBACK);
  }
}
function renderClientHistoryRow(tp, e) {
  if (e) {
    if (e.type == 0) {
      var appt = fs.clientHistory.appts[e.id];
      if (appt) {
        tp = tp.tpa;
        tp.createTr(appt.date, [appt.date], histaFilter(appt));
        tp.createTd("bold", appt.date);
        tp.createTdAppend(null, createApptAnchor(appt.id));
      }
    } else if (e.type == 1) {
      var sess = fs.clientHistory.sessions[e.id];
      if (sess) {
        tp.createTr(sess.date, [sess.date], histFilter(sess), sess.id);
        tp.createTd("bold", sess.date);
        tp.createTdAppend(null, createSessionAnchor(sess.id, true, "preview2"));
        var line = "Last updated " + sess.updated;
        if (sess.updatedBy) line += " by " + sess.updatedBy;
        tp.append(createDiv(null, "tagline", null, line));
      }
    } else {
      var msg = fs.clientHistory.msgs[e.id];
      if (msg) {
        tp = tp.tpm;
        tp.createTr(msg.date, [msg.date], histmFilter(msg));
        tp.createTd("bold", msg.date);
        tp.createTdAppend(null, createMsgAnchor(msg.mtid));
      }      
    }
  } else {
    tp.loadFilterSidebar("his-filter-ul");
    tp.tpa.loadFilterSidebar("hisa-filter-ul");
    tp.tpm.loadFilterSidebar("hism-filter-ul");
  }
}
function histFilter(e) {
  var docType = null;
  var docStatus = null;
  if (e) {
    var sess = fs.clientHistory.sessions[e.id];
    docType = sess.title;
    docStatus = (sess.closed) ? "Closed" : "Open";
  }
  return {
      "Document Type":docType,
      "Status":docStatus    
      };
}
function histaFilter(e) {
  var type = null;
  var status = null;
  if (e) {
    var appt = fs.clientHistory.appts[e.id];
    type = appt.type;
    status = appt.status;
  }
  return {
      "Appt Type":type,
      "Status":status    
      };
}
function histmFilter(e) {
  var subject = null;
  if (e) {
    var msg = fs.clientHistory.msgs[e.mtid];
    subject = msg.subject;
  }
  return {
      "Subject":subject
      };
}
function hisFilterCallback(t) {
  var stubs = {};
  var keys = t.getVisibleRowKeys();
  for (var i = 0; i < keys.length; i++) {
    stubs[keys[i]] = fs.clientHistory.sessions[keys[i]]; 
  }
  fs.clientHistory.filteredSessions = stubs;
}
function setTextArray(id, a) {
  for (var i = 0; i < a.length; i++) {
    if (isBlank(a[i])) {
      a[i] = "&nbsp;";
    }
  }
  setHtml(id, a.join("<br/>"));
}
function createHistAnchor(h, previewFn) {
  if (h.type == 0) {  // appt
    return createApptAnchor(h.id);
  } else if (h.type == 1) {  // session
    return createSessionAnchor(h.id, true, previewFn);
  } else {
    return createMsgAnchor(h.id);
  }
}
function createMsgAnchor(id) {
  var onclick = 'previewMsg(' + id + ')';
  var msg = fs.clientHistory.msgs[id];
  var label = 'Msg: ' + msg.subject;
  var a = createAnchor(null, null, "icon edit-msg", label, null, onclick);
  return a;
}
function previewMsg(mtid) {
  Includer.getMsgPreviewer_pop(fs.client.id, mtid, true);
}
function createApptAnchor(id) {
  var click = "editAppt(" + id + ")";
  var appt = fs.clientHistory.appts[id];
  var label = appt.time + " - " + appt.type; 
  var a = createAnchor(null, null, "icon edit-appt", label, null, click);
  var span = createSpan();
  span.appendChild(a);
  var s = createSpan(null, appt.status);
  if (appt.statusColor) {
    s.style.backgroundColor = appt.statusColor;
  } 
  span.appendChild(s);
  return span;
}
function createSessionAnchor(id, asZoom, fn) {
  if (id == 0) {
    return createSpan("perfs", "Facesheet");
  } else {
    var fn = (fn) ? fn : "preview";
    var href = buildHrefFn(fn, [id, asZoom]);
    var sess = fs.clientHistory.sessions[id];
    var cls = (sess.closed) ? "icon no-edit-note" : "icon edit-note";
    return createAnchor(null, href, cls, sess.label);
  }
}
function showMedRx(id) {
  var med = fs.rxById[id];
  var text = (med.text) ? med.text + "<br/>" : "";
  var h = "<b>" + med.name + "</b><br/>" + text + med.rx;
  showMsg(h);
}
function popQuestionCallback(q) {
  if (q.callback == null) {
    questionCallback(q);
  } else {
    q.callback(q);  // invoke what's been assigned to question's callback property
  } 
}
function editAllergy(id) {
  showOverlayWorking();
  fs.allergy = id ? fs.activeAllergiesById[id] : null;
  if (fs.aq) {
    getAllergyQuestionCallback(fs.aq);
  } else {
    sendRequest(4, "action=getAllergyQuestion");
  }
}
function getAllergyQuestionCallback(q) {
  closeOverlayWorking();
  fs.aq = q;
  q.callback = allergyQuestionCallback;
  q.clone = true;
  if (fs.allergy) {
    q.cix = 1;
    qSetByValueCombo(q, fs.allergy.agent, fs.allergy.reactions);
  } else {
    q.cix = null;
    q.sel = [];
  }
  showQuestion(q);
}
function allergyQuestionCallback(q) {
  showOverlayWorking("Saving");
  postRequest(4, "action=saveAllergy&obj=" + jsonUrl(buildDataAllergy(q)));  
}
function popQuestionDeleteCallback(q) {
  showOverlayWorking("Removing");
  sendRequest(4, "action=deactivateAllergy&id=" + fs.allergy.id);
}
function deleteAllergies() {
  if (getCheckedValues("sel-all", "fsp-all-tbody").length > 0) {
    showConfirmDeleteChecked(deleteAllergiesConfirmed, "remove");  
  } else {
    showNoSel();
  }
}
function deleteAllergiesConfirmed(confirmed) {
  if (confirmed) {
    showOverlayWorking("Removing");
    postRequest(4, "action=deactivateAllergies&obj=" + jsonUrl(getCheckedValues("sel-all", "fsp-all-tbody")));
  }
}
function buildDataAllergy(q) {
  var a = {};
  a.id = (fs.allergy) ? fs.allergy.id : null;
  a.clientId = cid;
  a.index = q.sel[0];
  a.agent = qOptText(q.opts[q.sel[0]]);
  a.reactions = toJSONString(qOptTextArray(q.opts, q.sel, 1));
  return a;
}
function editVitals(id) {
  fs.vital = id ? fs.vitalsById[id] : null;
  showIf(id, "ve-delete-span");
  if (fs.vq) {
    loadVitalEntry();
  } else {
    showOverlayWorking("Retrieving");
    sendRequest(4, "action=getVitalQuestions");
  }
}
function getVitalQuestionsCallback(questions) {
  closeOverlayWorking();
  fs.vq = questions;  // {"pulse":JQuestion,"rr":JQuestion,...}
  for (var prop in fs.vq) {
    var q = fs.vq[prop];
    //q.callback = vitalQuestionCallback;
    q.prop = prop;
    var setByField = q.out.split("=$")[1];  // either "ouid" or "otext"
    if (setByField == "ouid") {
      q.showUid = true;
    }
    //q.a = $("ve-" + prop);
    //q.a.q = q;
  }
  loadVitalEntry();  
}
function loadVitalEntry() {
  setValue("ve-date", (fs.vital) ? fs.vital.dateCal : calToday());
  var tform = new TemplateForm($("ul-ve-fields"), "first", fs.vq, null, TemplateForm.NAV_NEXT_ON_LINE);
  tform.addLi();
  tform.append("Pulse", "pulse", null, fs.vital);
  tform.append("Resp", "resp", null, fs.vital);
  tform.append("Blood Pressure", "bpSystolic", null, fs.vital);
  tform.append(null, "bpDiastolic", null, fs.vital);
  tform.append(null, "bpLoc", null, fs.vital);
  tform.append("Temp", "temp", null, fs.vital);
  tform.addLi(null, "push");
  tform.append("Weight", "wt", null, fs.vital);
  tform.append(null, "wtUnits", null, fs.vital);
  tform.append("Height", "height", null, fs.vital);
  tform.append(null, "htUnits", null, fs.vital);
  tform.append("BMI", "bmi", null, fs.vital);
  tform.addLi(null, "push");
  tform.append("Waist", "wc", null, fs.vital);
  tform.append(null, "wcUnits", null, fs.vital);
  tform.append("Head", "hc", null, fs.vital);
  tform.append(null, "hcUnits", null, fs.vital);
  tform.addLi(null, "push");
  tform.append("O2 Sat", "o2Sat", null, fs.vital);
  tform.append("% on", "o2SatOn", null, fs.vital, null, null, "nopad");
  fs.vtform = tform;
  showOverlayPop("pop-ve", "ve-pulse");
}
function loadVitalEntry0() {
  if (fs.vital) {
    setValue("ve-date", fs.vital.dateCal);
  } else {
    setValue("ve-date", calToday());
  }
  for (var prop in fs.vq) {
    var q = fs.vq[prop];
    if (fs.vital) {
      qSetByValue(q, fs.vital[prop]);
    }
    setVitalQuestionText(q)
  }
  showOverlayPop("pop-ve", "ve-pulse");
}
function buildDataVital() {
  var v = fs.vtform.buildRecord(TemplateForm.VALUES_MIXED, null, true);
  v.id = (fs.vital) ? fs.vital.id : null;
  v.date = value("ve-date");
  v.clientId = cid;
  return v;
}
function buildDataVital0() {
  var v = {};
  v.id = (fs.vital) ? fs.vital.id : null;
  v.date = value("ve-date");
  v.clientId = cid;
  for (var prop in fs.vq) {
    var q = fs.vq[prop];
    v[prop] = questionFieldText(q.a);
  }
  return v;
}
function questionFieldText(a) {
  return a.className == "df" ? null : a.innerText;
}
function showQuestionField(a, useLastPos, callback, hideClearCmd) {
  var showClearCmd = (hideClearCmd) ? false : true;
  var q = a.q;
  if (! q) return;
  if (callback) {
    q.callback = callback;
  }
  if (a.next) {
    q.next = $(a.next);
  }
  showQuestion(q, null, useLastPos, showClearCmd);
}
function showNextQuestion(q) {
  if (q.sel.length > 0 && q.next && q.next.className == "df") {
    showQuestionField(q.next, true, q.callback);
  }
}
function vitalQuestionCallback(q) {
  setVitalQuestionText(q);
  showNextQuestion(q);
}
function setVitalQuestionText(q) {
  setAnchorTextByQuestion(q, q.setByField == "ouid")
  if (q.prop == "wt" || q.prop == "wtUnits" || q.prop == "height" || q.prop == "htUnits") {
    calcBmi();
  }
}
function veSave() {
  closeOverlayPop();
  showOverlayWorking("Saving");
  var rec = buildDataVital();
  postRequest(4, "action=saveVital&obj=" + jsonUrl(buildDataVital()));
}
function veDelete() {
  showConfirmDelete(veDeleteConfirmed, "remove");
}
function veDeleteConfirmed(confirmed) {
  if (confirmed) {
    closeOverlayPop();
    showOverlayWorking("Deleting");
    sendRequest(4, "action=deleteVital&id=" + fs.vital.id);
  }
}
function editDiagnosis(id) {
  fs.diagnosis = id ? fs.diagnosesById[id] : null;
  deLoad();
  showOverlayPop("pop-de", "de-desc");
}
function deLoad() { 
  if (fs.diagnosis) {
    setText("de-icd", fs.diagnosis.icd);
    setText("de-desc", fs.diagnosis.text);
    show("de-delete-span");
  } else { 
    setText("de-icd", "");
    setText("de-desc", "");
    hide("de-delete-span");
  }
}
function deIcd() {
  showIcd(null, value("de-desc"));
}
function icdCallback(code, desc) {
  if (code) {
    setText("de-icd", code);
    setText("de-desc", desc);
  }
}
function deSave() {
  var d = {
      icd:nullify(value("de-icd")),
      text:value("de-desc"),
      clientId:cid
      };
  if (fs.diagnosis) {
    d.id = fs.diagnosis.id;
  }
  postRequest(4, "action=saveDiagnosis&obj=" + jsonUrl(d));
}
function deDelete() {
  showConfirmDelete(deDeleteConfirmed, "remove");
}
function deDeleteConfirmed(confirmed) {
  if (confirmed) {
    closeOverlayPop();
    showOverlayWorking("Deleting");
    sendRequest(4, "action=deactivateDiagnosis&id=" + fs.diagnosis.id);
  }
}
function deleteDia() {
  if (getCheckedValues("sel-dia", "fsp-dia-tbody").length > 0) {
    showConfirmDeleteChecked(deleteDiaConfirmed, "remove");  
  } else {
    showNoSel();
  }
}
function deleteDiaConfirmed(confirmed) {
  if (confirmed) {
    showOverlayWorking("Removing");
    postRequest(4, "action=deactivateDiagnoses&obj=" + jsonUrl(getCheckedValues("sel-dia", "fsp-dia-tbody")));
  }
}
function createNewDoc() {
  showNewNote(fs.client.id, fs.client.name);
}
function createNewMsg() {
  window.location.href = 'message.php?cid=' + fs.client.id;
}
function newNoteCallback(s) {
  openSession(s.id);
}
function editDoc(id) {
  doHourglass("openSession(" + id + ")");
}
function editAppt(id) {
  window.location.href = "schedule.php?pe=1&pop=" + id;
}
function newAppt(id) {
  window.location.href = "schedule.php?v=1&sid=" + id;
}
function preview(sid, asZoom) {
  showDocViewer(sid, fs.clientHistory.sessions, asZoom);
}
function preview2(sid) {
  showDocViewer(sid, fs.clientHistory.filteredSessions);
}
function editNotepad() {
  if (fs.client.notes) {
    setValue("pop-cn-text", brToCrlf(fs.client.notes));
    // show("cn-delete-span");
  } else {
    setValue("pop-cn-text", "");
    // hide("cn-delete-span");
  }
  zoomPop("pop-cn", "pop-cn-text");
}
function cnSave() {
  var text = value("pop-cn-text");
  if (isBlank(text)) {
    text = null; 
  } else {
    text = esc(crlfToBr(text));
  }
  var note = {"cid":fs.client.id, "text":text};
  closeOverlayPop();
  showOverlayWorking("Saving");
  postRequest(4, "action=saveClientNotes&obj=" + jsonUrl(note));
}
function cnDelete() {
  setValue("pop-cn-text", "").select();
}
function showDemo() {
  showPatient(fs.client);
}
function showPatientCallback() {  // called only when client was changed
  getFacesheet(fs.client.id, fs.cuTimestamp);
}
function openSession(sid) {
  refreshOnFocus = true;
  openConsole(sid);
}
function calcBmi() {
  var qb = fs.vq["bmi"];
  var bmi = calculateBmi(qb, fs.vq["wt"], fs.vq["wtUnits"], fs.vq["height"], fs.vq["htUnits"]);
  if (bmi) {
    qb.a.innerText = bmi;
    qb.a.className = "";
  }  
}
function calcBmi2(w, wm, h, hm) {
  var n, d;
  if (! wm && ! hm) {  // lbs-in
    n = w * 703;
    d = h;
  } else if (wm && hm) {  // kg-cm
    n = w;
    d = h / 100;
  } else if (wm && ! hm) {  // kg-in
    n = w;
    d = h * 0.0254;
  } else {  // lbs-cm
    n = w * 703;
    d = h * 0.3937;
  }
  return Math.round(n / (d * d));
}
function printFacesheet() {
  window.open("print-facesheet.php?id=" + cid, "fsp", "top=0,left=0,resizable=1,toolbar=1,scrollbars=1,menubar=1");  
}
function tlog() {}

function dataException(e) {
  closeOverlayWorking();
  if (e.dataclass == "JDataHist") {
    showOverlayPop("pop-he");
    showErrors("pop-he-errors", e.errors, uiMapHe);
  } 
}
function showFspHist() {
  zoomPop("fsp-hist");
}
function showFspHis() {
  showTab("fsp-his", 0);
  if (! fs.clientHistRendered) {
    zoomPop("fsp-his");
    renderClientHistFilterTable();
    fs.clientHistRendered = true;
  } else {
    showOverlayPop("fsp-his");
  }
}
/*
 * Social HX
 */
function showFspSochx() {
  showFspHx(3);
}
function renderSochx() {
  var t = new TableLoader("fsp-sochx-tbody", "off", "fsp-sochx-div");
  var tp = new TableLoader("sochx-prt-tbody", "off", "sochx-prt-div");
  var topics = [];  // topics that have values
  for (var topic in fs.sochx) {
    var tpvals = [];  // ["bullet-rec-values",..]
    var rec = fs.sochx[topic];
    var dsyncCombo = null;
    if (topic == "Drug Use") dsyncCombo = "sochx.drugs";
    if (topic == "Past Occupations") dsyncCombo = "sochx.occs";
    t.createTr(topic);
    var td = t.createTd();
    t.append(createAnchor(null, buildHrefFn("editSochx", [topic, null, dsyncCombo]), "fs", null, topic));
    var i = 0;
    for (var dsyncId in rec) {
      if (i > 0) {
        t.createTr(topic);
      }
      var a;
      var values = rec[dsyncId].v;
      var dsyncCombo = rec[dsyncId].d;
      if (dsyncCombo) {
        tpvals.push(bulletJoin(values) + "<br>");
        rec[dsyncId].l = values.shift();
      } else {
        tpvals.push(bulletJoin(values));
      }
      t.createTd("right", rec[dsyncId].l);
      var v = nbsp(bulletJoin(values));
      if (dsyncCombo) {
        a = createAnchor(null, buildHrefFn("editSochx", [topic, dsyncId, dsyncCombo]), "hxa", null, v);
      } else {
        a = createAnchor(null, buildHrefFn("editSochx", [topic, dsyncId]), "hxa", null, v);
      }
      t.createTdAppend(null, a); 
      i++;
    }
    if (i == 0) {
      t.createTd();
      t.createTd();
    } else {
      td.rowSpan = i;
    }
    tpvals = bulletJoin(tpvals, true);
    if (tpvals != "") {
      topics.push(topic);
      tp.createTr();
      tp.createTd("fs", topic);
      tp.createTd("w80", null, tpvals);
    }
  }
  if (tp.rows() == 0) {
    tp.createTrTd("fs", "(None)");
  }
  if (topics.length) {
    var a = "<a class='fs0' href='javascript:showFspSochx()'>" + bulletJoin(topics) + "</a>";
    setHtml("sochx-sum", a);
    resizeDivs();
  }    
}
function editSochx(topic, focus, dsyncCombo) {
  fs.stopic = topic;
  fs.sfocus = focus;
  fs.soc = fs.sochx[topic];
  fs.dsyncCombo = dsyncCombo;
  if (fs.sqs) {
    loadSochxEntry();
  } else {
    showOverlayWorking("Retrieving");
    sendRequest(4, "action=getSochxQuestions");
  }
}
function getSochxQuestionsCallback(questions) {
  fs.sqs = questions;
  var isMale = (fs.client.sex == "M");
  for (var dsync in questions) {
    var q = questions[dsync];
    qGenderFix(q, isMale);
  }
  closeOverlayWorking();
  loadSochxEntry();  
}
function loadSochxEntry() {
  setText("she-topic", fs.stopic);
  var tform = new TemplateForm($("ul-she-fields"), "first2", fs.sqs, null, TemplateForm.NAV_NEXT_ON_FORM);
  if (fs.dsyncCombo) {
    var lbl = (fs.dsyncCombo == "sochx.drugs") ? "Substance" : "Occupation";
    for (var dsync in fs.soc) {
      var value = [fs.soc[dsync].l, fs.soc[dsync].v];
      tform.addLiAppend(lbl, fs.dsyncCombo, dsync, value);
    }    
    var ix = (dsync) ? val(dsync.split("?")[1]) : 0;
    for (var i = 1; i < 4; i++) {
      var dsync = fs.dsyncCombo + "?" + (ix + i);
      var value = [null, null];
      tform.addLiAppend(lbl, fs.dsyncCombo, dsync, value);
    }
  } else {
    for (var dsync in fs.soc) {
      tform.addLiAppend(fs.soc[dsync].l, dsync, null, fs.soc[dsync].v);
    }
  }
  showOverlayPop("pop-she");
  if (fs.sfocus) {
    tform.popup(fs.sfocus);
  }
  fs.stform = tform;
}
function sheSave() {
  closeOverlayPop();
  showOverlayWorking("Saving");
  postRequest(4, "action=saveSochx&cid=" + cid + "&obj=" + jsonUrl(fs.stform.buildRecord()));
}
function sheClose() {
  if (fs.stform && fs.stform.isDirty()) {
    showConfirmDirtyExit(sheCloseConfirm);
  } else {
    closeOverlayPop();
  }
}
function sheCloseConfirm(confirm) {
  if (confirm != null) {
    if (confirm) {
      sheSave();
    } else {
      closeOverlayPop();
    }
  }
}
/* Medical/Surgical HX
 * Added to fs:
 *   hx        // hx working on (e.g. fs.medhx)
 *   hx.pq     // JQuestion of proc list question
 *   hx.proc   // selected proc
 *   hx.rec    // rec being edited
 *   hx.focus  // field to focus on edit
 *   hx.qs     // entry form questions {dsync:JQuestion,..} 
 * UI class added as array element to field defs:
 *   hx.fields[field][label,className]
 */
function renderHxSummary() {
  var as = [];
  pushIfNotNull(as, hxSumProcsAnchor(fs.medhx.recs, 0));
  pushIfNotNull(as, hxSumProcsAnchor(fs.surghx.recs, 1));
  if (as.length > 0) {
    setHtml("fshx-sum", bulletJoin(as));
    resizeDivs();
  }
}
function hxSumProcsAnchor(recs, tab) {
  var procs = [];
  for (var proc in recs) {
    procs.push(proc);
  }
  return hxSumAnchor(procs, tab);
} 
function hxSumAnchor(a, tab) {
  if (isEmpty(a)) return null;
  return "<a class='fs0' href='javascript:showFspHx(" + tab + ")'>" + bulletJoin(a) + "</a>";  
}
function renderMedhx(refreshing) {
  var t = new TableLoader("fsp-medhx-tbody", "off", "fsp-medhx-div");
  var tp = new TableLoader("medhx-prt-tbody", "off", "medhx-prt-div");
  for (var proc in fs.medhx.recs) {
    var rec = fs.medhx.recs[proc];
    t.createTr(proc);
    t.createTd("nowrap nbb");
    t.append(createAnchor(null, buildHrefFn("editMedhx", [proc, null]), "fs", null, proc));
    var date = extractDate(bulletJoin(rec.date));
    var type = bulletJoin(rec.type);
    var rx = bulletJoin(rec.rx);
    var comment = bulletJoin(rec.comment);
    t.createTdAppend(null, createAnchor(null, buildHrefFn("editMedhx", [proc, "date"]), "hxa", null, nbsp(date)));
    //t.createTd("nowrap", null, hxout(rec.date));
    t.createTdAppend(null, createAnchor(null, buildHrefFn("editMedhx", [proc, "type"]), "hxa", null, nbsp(type)));
    t.createTdAppend(null, createAnchor(null, buildHrefFn("editMedhx", [proc, "rx"]), "hxa", null, nbsp(rx)));
    t.createTr(proc);
    t.createTd("nrbc", null, "Comment:");
    t.createTdAppend("top", createAnchor(null, buildHrefFn("editMedhx", [proc, "comment"]), "hxa", null, nbsp(comment)));
    t.td.colSpan = 3;
    tp.createTr();
    tp.createTd("fs", proc);
    tp.createTd("w80", null, bulletJoin([date, type, rx, comment], true));
  }
  if (tp.rows() == 0) {
    tp.createTrTd("fs", "(None)");
  }
  renderHxSummary();
  if (! refreshing) {
    setMedhxFieldInfo();
  }
}
function renderSurghx(refreshing) {
  var t = new TableLoader("fsp-surghx-tbody", "off", "fsp-surghx-div");
  var tp = new TableLoader("surghx-prt-tbody", "off", "surghx-prt-div");
  for (var proc in fs.surghx.recs) {
    var rec = fs.surghx.recs[proc];
    t.createTr(proc);
    t.createTd("nowrap nbb");
    var date = extractDate(bulletJoin(rec.date));
    var type = bulletJoin(rec.type);
    var comment = bulletJoin(rec.comment);
    t.append(createAnchor(null, buildHrefFn("editSurghx", [proc, null]), "fs", null, proc));
    t.createTdAppend(null, createAnchor(null, buildHrefFn("editSurghx", [proc, "date"]), "hxa", null, nbsp(date)));
    t.createTdAppend(null, createAnchor(null, buildHrefFn("editSurghx", [proc, "type"]), "hxa", null, nbsp(type)));
    t.createTr(proc);
    t.createTd("nrbc", null, "Comment:");
    t.createTdAppend("top", createAnchor(null, buildHrefFn("editSurghx", [proc, "comment"]), "hxa", null, nbsp(comment)));
    t.td.colSpan = 2;
    tp.createTr();
    tp.createTd("fs", proc);
    tp.createTd("w80", null, bulletJoin([date, type, comment], true));
  }
  if (tp.rows() == 0) {
    tp.createTrTd("fs", "(None)");
  }
  renderHxSummary();
  if (! refreshing) {
    setSurghxFieldInfo();
  }
}
function setMedhxFieldInfo() {
  append(fs.medhx.fields["date"], "qd");
  append(fs.medhx.fields["type"], "qd2");
  append(fs.medhx.fields["rx"], "qt");
  append(fs.medhx.fields["comment"], "qc2");
}
function setSurghxFieldInfo() {
  append(fs.surghx.fields["date"], "qd");
  append(fs.surghx.fields["type"], "qd2");
  append(fs.surghx.fields["comment"], "qc2");
}
function showFspHx(tab) {
  showTab("fsp-hx", tab);
  zoomPop("fsp-hx");
}
function editMedhx(proc, focus) {
  fs.hx = fs.medhx;
  editHx(proc, focus);
}
function editSurghx(proc, focus) {
  fs.hx = fs.surghx;
  editHx(proc, focus);
}
function editHx(proc, focus) {
  fs.hx.proc = proc;
  fs.hx.focus = (focus) ? makeProcDsync(focus) : null;
  fs.hx.rec = fs.hx.recs[proc];
  if (fs.hx.qs) {
    loadHxEntry();
  } else {
    overlayWorking(true);
    sendRequest(4, "action=getHxQuestions&cat=" + fs.hx.cat);
  }
}
function getHxQuestionsCallback(qs) {
  overlayWorking(false);
  fs.hx.qs = (qs) ? qs : {};
  loadHxEntry();
}
function loadHxEntry() {
  setText("hxe-proc", fs.hx.proc);
  var tform = new TemplateForm($("ul-hxe-fields"), "first2", fs.hx.qs);
  for (var field in fs.hx.rec) {
    var value = fs.hx.rec[field];
    var info = fs.hx.fields[field];
    var lbl = info[0];
    var className = info[1];
    var type = (field == "date") ? TemplateForm.Q_DEF_CALENDAR : null;
    tform.addLiAppend(lbl, makeProcDsync(field), null, value, className, type);
  }  
  showOverlayPop("pop-hxe");
  if (fs.hx.focus) {
    tform.popup(fs.hx.focus);
  }
  fs.hx.tform = tform;
}
function hxeSave() {
  closeOverlayPop();
  showOverlayWorking("Saving");
  postRequest(4, "action=saveHx&cat=" + fs.hx.cat + "&cid=" + cid + "&obj=" + jsonUrl(fs.hx.tform.buildRecord()));
}
function makeProcDsync(field, proc) {  // proc optional
  return fs.hx.cat + "." + ((proc) ? proc : fs.hx.proc) + "." + field;
}
function getProcQuestion(hx) {
  fs.hx = hx;
  if (hx.pq) {
    overlayWorkingCall("showProcQuestion()");
  } else {
    overlayWorking(true);
    sendRequest(4, "action=getProcQuestion&cat=" + hx.cat);
  }
}
function getProcQuestionCallback(q) {
  q.callback = procQuestionCallback;
  q.multiOnly = true;
  fs.hx.pq = q;
  showProcQuestion();
}
function showProcQuestion() {
  overlayWorking(false);
  qSetByValues(fs.hx.pq, fs.hx.procs); 
  showQuestion(fs.hx.pq, null, null, true);
}
function procQuestionCallback(q) {
  var procs = qSelTextArray(q);
  if (fs.hx.procs == null) fs.hx.procs = []; 
  if (isDirty(procs, fs.hx.procs)) {
    showOverlayWorking("Saving");
    postRequest(4, "action=saveHxProcs&cat=" + fs.hx.cat + "&cid=" + cid + "&obj=" + jsonUrl(fs.hx.procs));      
  }
}
function newHxRecord(proc) {
  v = {};
  for (var field in fs.hx.fields) {
    var dsync = makeProcDsync(field, proc);
    v[dsync] = "[]";
  }
  return v;
}
function hxeDelete() {
  showConfirmDelete(hxeDeleteConfirmed, "remove");
}
function hxeDeleteConfirmed(confirmed) {
  if (confirmed) {
    closeOverlayPop();
    showOverlayWorking("Removing");
    removeHxProc();
    postRequest(4, "action=saveHxProcs&cat=" + fs.hx.cat + "&cid=" + cid + "&obj=" + jsonUrl(fs.hx.procs));
  }
}
function removeHxProc() {
  for (var i = 0; i < fs.hx.procs.length; i++) {
    if (fs.hx.procs[i] == fs.hx.proc) {
      fs.hx.procs.splice(i, 1);
      return;
    }
  }
}
/* Family HX
 * Added to fs.famhx:
 *   sq     // JQuestion of suid ("famHx") question
 *   puid   // selected puid (injector+clone, e.g. "father+male")
 *   puidc  // just clone portion of selected puid ("+male")
 *   focus  // field to focus on edit
 *   rec    // rec being edited
 *   aqs    // all entry form questions, arranged by "clone" {"+male":{dsync:JQuestion,..},"+female":{dsync:JQuestion,..}}
 *   qs     // entry form questions for selected "clone" ("+male")   
 */
function showFspFamhx() {
  showFspHx(2);
}
function renderFamhx(refreshing) {
  var t = new TableLoader("fsp-famhx-tbody", "off", "fsp-famhx-div");
  for (var puid in fs.famhx.recs) {
    var rec = fs.famhx.recs[puid];
    t.createTr(puid);
    t.createTd();
    t.td.rowSpan = 3;
    t.append(createAnchor(null, buildHrefFn("editFamhx", [puid]), "fs", null, fs.famhx.puidTexts[puid]));
    t.createTd("right", "Status");
    var v = "&nbsp;";
    if (rec.status) {
      v = single(rec.status);
      if (isDeathAge(v) && rec.deathAge) {
        v += " (age " + single(rec.deathAge) + ")";
      } else if (rec.age) {
        v += " (age " + single(rec.age) + ")";
      }
    }
    t.createTdAppend(null, createAnchor(null, buildHrefFn("editFamhx", [puid]), "hxa", null, v));
    t.createTr(puid);
    t.createTd("right", "History");
    t.createTdAppend(null, createAnchor(null, buildHrefFn("editFamhx", [puid]), "hxa", null, nbspJoin(rec.history)));
    t.createTr(puid);
    t.createTd("right", "Comment");
    t.createTdAppend(null, createAnchor(null, buildHrefFn("editFamhx", [puid]), "hxa", null, nbsp(rec.comment)));
  }
  renderFamhxSummary();
  if (! refreshing) {  
    append(fs.famhx.fields["status"], "q qd2");
    append(fs.famhx.fields["age"], "q qd");
    append(fs.famhx.fields["deathAge"], "q qd");
    append(fs.famhx.fields["history"], "q qc2");
    append(fs.famhx.fields["comment"], "q qc2");
  }
}
function renderFamhxSummary() {
  setHtml("famhx-sum", hxSumAnchor(fs.famhx.sopts, 2));
  resizeDivs();
}
function editFamhx(puid, focus) {
  fs.famhx.puid = puid;
  fs.famhx.puidc = puidClone(puid);
  fs.famhx.focus = focus;
  fs.famhx.rec = fs.famhx.recs[puid];
  if (fs.famhx.aqs) {
    loadFamhxEntry();
  } else {
    overlayWorking(true);
    sendRequest(4, "action=getFamhxQuestions");    
  }
}
function puidClone(puid) {  // return clone portion of puid, e.g. "+male" from "father+male"
  return "+" + puid.split("+")[1];
}
function getFamhxQuestionsCallback(aqs) {
  overlayWorking(false);
  fs.famhx.aqs = (aqs) ? aqs : {};
  loadFamhxEntry();  
}
function loadFamhxEntry() {
  fs.famhx.qs = fs.famhx.aqs[fs.famhx.puidc];  // subset of questions just for selected clone ("+male")
  setText("fhxe-rel", fs.famhx.puidTexts[fs.famhx.puid]);
  var tform = new TemplateForm($("ul-fhxe-fields"), "first2", fs.famhx.qs, fxheOnChange);
  for (var field in fs.famhx.rec) {
    var value = fs.famhx.rec[field];
    var info = fs.famhx.fields[field];
    var lbl = info[0];
    var className = info[1];
    var type = (field == "date") ? TemplateForm.Q_DEF_CALENDAR : null;
    tform.addLi(fhxeLiId(field));
    tform.append(lbl, field, null, value, className, type);
  }
  showOverlayPop("pop-fhxe");
  if (fs.famhx.focus) {
    tform.popup(fs.famhx.focus);
  }
  fs.famhx.tform = tform;
  fxheOnChange();
}
function fhxeLiId(field) {  // field entry LI 
  return "fxhe-li-" + field;
}
function fxheOnChange() {
  var status = qSingle(fs.famhx.qs["status"]);
  if (isDeathAge(status)) {
    qClear(fs.famhx.qs["age"]);
    hide(fhxeLiId("age"));
    show(fhxeLiId("deathAge"));
  } else {
    qClear(fs.famhx.qs["deathAge"]);
    show(fhxeLiId("age"));
    hide(fhxeLiId("deathAge"));    
  } 
}
function isDeathAge(status) {
  return (status == "Deceased" || status == "Accidental Death" || status == "Suicide");
}
function fhxeSave() {
  closeOverlayPop();
  showOverlayWorking("Saving");
  var prefix = fs.famhx.suid + "." + fs.famhx.puid + ".";
  var rec = jsonUrl(fs.famhx.tform.buildRecord(TemplateForm.VALUES_ALWAYS_ARRAY, prefix));
  postRequest(4, "action=saveFamhx&cid=" + cid + "&obj=" + rec);
}
function fhxeDelete() {
  showConfirmDelete(fhxeDeleteConfirmed, "remove");
}
function fhxeDeleteConfirmed(confirmed) {
  if (confirmed) {
    closeOverlayPop();
    showOverlayWorking("Removing");
    sendRequest(4, "action=removeFamhx&cid=" + cid + "&puid=" + encodeURIComponent(fs.famhx.puid));
  }
}
function getFamQuestion() {
  if (fs.famhx.sq) {
    overlayWorkingCall("showFamQuestion()");
  } else {
    overlayWorking(true);
    sendRequest(4, "action=getSuidQuestion&suid=" + fs.famhx.suid);
  }
}
function getSuidQuestionCallback(q) {
  q.callback = suidQuestionCallback;
  q.multiOnly = true;
  fs.famhx.sq = q;
  showFamQuestion();
}
function suidQuestionCallback(q) {
  var sopts = qSelTextArray(q);
  if (fs.famhx.sopts == null) fs.famhx.sopts = [];
  if (isDirty(sopts, fs.famhx.sopts)) {
    showOverlayWorking("Saving");
    postRequest(4, "action=saveFamHxSopts&suid=" + fs.famhx.suid + "&cid=" + cid + "&obj=" + jsonUrl(fs.famhx.sopts));          
  }
}
function showFamQuestion() {
  overlayWorking(false);
  qSetByValues(fs.famhx.sq, fs.famhx.sopts); 
  showQuestion(fs.famhx.sq, null, null, true);
}
/*
 * Test if selected keys are not present in current selection. If so, return true and add new keys to current
 * Note: current must be an initialized array! [] is ok, NULL is not. This is to enforce the byRef manipulation.
 * Ex. selected: ['key1','key2']
 *     current:  ['key2','key3']
 * Returns true and sets
 *     current:  ['key1','key2','key3']   
 */
function isDirty(selected, current) {
  if (selected.length == 0) {
    return false;
  }
  var dirty = false;
  var map = makeMap(current);
  for (var i = 0; i < selected.length; i++) {
    var id = selected[i];
    if (map[id] == null) {
      current.push(id);
      dirty = true;
    }
  }
  return dirty;
}
/*
 * Return sel text array value as a single value
 * e.g. "x" for ["x"], null for null
 */
function single(a) {
  return (isEmpty(a)) ? null : a[0];
}
/*
 * Return single() from a question
 */
function qSingle(q) {
  return single(qSelTextArray(q));
}