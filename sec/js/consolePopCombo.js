var comboQ;  // parent element of clones
var comboA;  // existing combo anchor, null if new
var acq;  // original combo question
var comboAdd;  // add trigger, null if triggered by existing
var initcomboA;

// Initialize
function comboReset() {
  nextComboAddId = 0;  // must be reset on clear
}

function addFacesheetCombo(q, multis) {
  acq = q;
  var ix = acq.sel[0];
  var sel = acq.sel.splice(1, acq.sel.length);
  var singleOther = acq.opts[acq.mix - 1].text;
  var comboAddId = "q_" + acq.id + "o";
  addCombo(acq.id, comboAddId, ix, sel, singleOther, multis);
}

// Show combo popup
function showPopCombo() {
  event.returnValue = false;
  if (session.closed) return;
  var comboTrigger = event.srcElement;
  if (comboTrigger.getAttribute('qid')) {
    acq = questions[comboTrigger.getAttribute('qid')];    
  } else {
    acq = questions[comboTrigger.parentElement.parentElement.parentElement.getAttribute('qid')];
  }
  acq.clone = true;
  acq.type = Q_TYPE_COMBO;
  acq.csi = 0;
  acq.cbo = true;
  if (comboTrigger.className != "clone") { 

    // Popup triggered by existing combo (update)
    comboAdd = null;
    comboA = comboTrigger;
    qRestoreFromComboAnchor(acq, comboA);
  } else {
    
    // Popup triggered by "add" link (add)
    comboAdd = comboTrigger;
    comboA = null;
    acq.sel = [];
    acq.cix = null;
  }
  showQuestion(acq);
}

function qRestoreFromComboAnchor(q, a) {
  var sel = eval(a.sel);
  sel.unshift(a.ix);
  qRestore(q, sel, [], a.singleOther, a.multiOthers);
  q.cix = 1;  // dummy clone index
}

// Action buttons
function comboDoDelete() {
  deleteCombo(comboA.id);
}
function comboDoOk(q) {
  var acq = q;
  var other = qOtherTexts(acq);
  var singleOther = other.single;
  var multiOthers = other.multis;
  var singleIx = acq.sel[0];
  var sel = acq.sel.splice(1, acq.sel.length);
  if (comboA == null) {
    addCombo(acq.id, comboAdd.id, singleIx, sel, singleOther, multiOthers, true);
  } else {
    changeCombo(acq.id, comboA.id, singleIx, sel, singleOther, multiOthers, true);
  }
  Pop.close();
}

// Action methods (these are saved)
function addCombo(qid, comboAddId, ix, sel, singleOther, multiOthers, save) {
  var selJson = toJSONString(sel);
  multiOthers = asArray(multiOthers);
  acq = questions[qid];
  acq.cbo = true;
  qRestore(acq, combine(ix, sel), [], singleOther, multiOthers);
  if (save) {
    var undoText = "Add \"" + qSelText(acq) + "\""; 
    pushAction("addCombo(" + qid + ",'" + comboAddId + "'," + ix + "," + selJson + "," + toJSONString(singleOther) + "," + toJSONString(multiOthers) + ")", undoText);
  }
  var comboAdd = $(comboAddId);
  var comboK = $(comboAdd.getAttribute('kid'));
  var div = document.createElement("div");
  comboK.appendChild(div);
  var comboA = document.createElement("a");
  comboA.href = ".";
  comboA.id = comboAddId + nextComboAddId++;
  comboA.onclick = showPopCombo;
  comboA.className = "listAnchor2";
  comboA.ix = ix;
  comboA.ixText = getOptText(questions[qid].opts[ix]);
  comboA.sel = selJson;
  comboA.selText = selTextJson(questions[qid], sel);  // for saving multis to outdata
  comboA.singleOther = singleOther;
  comboA.multiOthers = multiOthers;
  comboA.innerText = qSelText(acq);
  div.appendChild(comboA);
  return comboA;
}
function changeCombo(qid, comboId, ix, sel, singleOther, multiOthers, save) {
  var selJson = toJSONString(sel);
  multiOthers = asArray(multiOthers);
  acq = questions[qid];
  acq.cbo = true;
  qRestore(acq, combine(ix, sel), [], singleOther, multiOthers);
  if (save) {
    var undoText = "Change to \"" + qSelText(acq) + "\""; 
    pushAction("changeCombo(" + qid + ",'" + comboId + "'," + ix + "," + selJson + "," + toJSONString(singleOther) + "," + toJSONString(multiOthers) + ")", undoText);
  }
  var comboA = $(comboId);
  if (! comboA) return;
  comboA.ix = ix;
  comboA.ixText = getOptText(questions[qid].opts[ix]);
  comboA.sel = selJson;
  comboA.selText = selTextJson(questions[qid], sel);  // for saving multis to outdata
  comboA.singleOther = singleOther;
  comboA.multiOthers = multiOthers;
  comboA.innerText = qSelText(acq);
  // Redisplay add combo button
  var comboAddId = "q_" + qid + "o";
  comboAdd = $(comboAddId);
  comboAdd.style.display = (ix == 0) ? "none" : "";
}
function deleteCombo(comboId) {
  var comboA = $(comboId);
  if (! comboA) return;
  var undoText = "Delete \"" + comboA.innerText + "\"";
  pushAction("deleteCombo('" + comboId + "')", undoText);
  var div = comboA.parentElement;
  var comboQ = div.parentElement;
  comboQ.removeChild(div);
  var comboAddId = "q_" + acq.id + "o";
  comboAdd = $(comboAddId);
  comboAdd.style.display = "";
}
