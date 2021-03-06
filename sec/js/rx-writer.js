// Prescription writer
// To call: showRx(rx)
// rx
//   date
//   JClient client
//   JUser me
//   JDataMed[] meds
//     checked  // optional boolean to default check
//   showMedList  // optional boolean to show print med list button (default false) 
// Callback: rxCallback(meds)
//   JDataMed[] meds  // just the selected ones printed
//     rx             // new field: freetext, e.g. (RX 11/1/2009 Disp: 1200, Refills: None)
var rx;
var rxcb;
var rq;
/*
 * Alternate show method, using question
 * Pass rx arg as prop of q (q.rx)
 * Callback: callback(q)  // q.meds contains return value (see callback description above)
 */
function showRxByQuestion(q, callback) {
  rq = q;
  showRx(q.rx, callback, true);
}
function showRx(r, callback, fromQ) {
  if (! fromQ) {
    rq = null;
  }
  rx = r;
  if (callback) {
    rxcb = callback;
  } else {
    rxcb = rxCallback;
  }
  if (me.isErx()) {
    hideForErx();
  }
  loadRxDocs();
  setRxHead();
  //checkAllCol1(Html.InputCheck.$('rx-med-tbl-ck').setCheck(false));
  _$('rx-refills').selectedIndex = 0;
  _$('rx-dnss').checked = false;
  _$('rx-daws').checked = false;
  _$('med-cmd-print-list').showIf(r.showMedList);
  Pop.showPosCursor('pop-rx');
  setRxMeds();
  //setDisps(setValue('rx-disps', '30 days'));
  if (! me.isErx())
    setDisps(Html.InputText.$('rx-disps').setValue(''));
}
function hideForErx() {
  _$('rx-med-th-disp').hide();
  _$('rx-med-th-refill').hide();
  _$('rx-med-th-dns').hide();
  _$('rx-med-th-daw').hide();
  _$('rx-med-th-disp0').hide();
  _$('rx-med-th-refill0').hide();
  _$('rx-med-th-dns0').hide();
  _$('rx-med-th-daw0').hide();
  _$('rx-med-cmd-rx').hide();
}
function loadRxDocs() {
  if (! rx.docs) {
    rx.docs = [me];
  }
  if (rx.me == null) {
    rx.me = me;
  }
  var sel = (rx.docid) ? rx.docid : rx.me.userId;
  Html.Select.$('rx-docs').load(Map.from(rx.docs, null, 'name')).setValue(sel);
  //createOptsFromObjectArray('rx-docs', rx.docs, 'name', sel);
  setRxDoc();
}
function setRxDoc() {
  var doc = rx.docs[value('rx-docs')];
  _$('rx-head-lic').setText(doc.licLine);
  Html.InputText.$('rx-doc-name').setValue(doc.name);
  Html.InputText.$('rx-doc-lic').setValue(doc.licLine);
}
function setRxHead() {
  if (rx.date == null) {
    rx.date = DateUi.getToday(1);
  }
  Html.InputText.$('rx-submit-date').setValue(rx.date);
  Html.InputText.$('rx-submit-client').setValue(rx.client.name);
  Html.InputText.$('rx-submit-dob').setValue(rx.client.cbirth);
  Html.InputText.$('rx-prac-name').setValue(rx.me.User.UserGroup.name);
  Html.InputText.$('rx-prac-addr').setValue(rx.me.User.UserGroup.Address.addr1);
  var phones = rxFormatPhones(rx.me.User.UserGroup.Address);
  Html.InputText.$('rx-prac-phone').setValue(phones);  
  _$('rx-head-prac').setText(rx.me.User.UserGroup.name);
  _$('rx-head-prac-addr').setText(rx.me.User.UserGroup.Address.addr1);
  _$('rx-head-prac-phone').html(phones);
  //setText('rx-head-date', rx.date);
  //setText('rx-head-client', rx.client.name);
  //setText('rx-head-client-dob', 'DOB: ' + rx.client.cbirth);
}
function rxFormatPhones(addr) {
  var a = [addr.phone1];
  if (addr.phone2All != null) a.push(addr.phone2All);
  if (addr.phone3All != null) a.push(addr.phone3All);
  return a.join(' &#x2022; ');
}
function setRxMeds() {
  var t = new TableLoader('rx-med-tbody', 'off', 'rx-med-div');
  var checkAll = true;
  for (var i = 0; i < rx.meds.length; i++) {
    if (rx.meds[i].checked) {
      checkAll = false;
      break;
    }
  }
  for (var i = 0; i < rx.meds.length; i++) {
    var med = rx.meds[i];
    med.length = med.length || '';
    med.autoCalcDisp = (med.length == '' || med.length == '30 days' || med.length == '90 days');
    if (String.denull(med.disp) == '') {
      med.disp = calcMedDisp(med.amt, med.freq, med.length);
    }
    med.olength = med.length;
    med.odisp = med.disp;
    // med.sig = calcRxSig(med);
    //med.text = calcRxSig(med);
    if (me.isErx()) 
      med.sig = (med.text) ? med.text : calcRxSig(med);
    else
      med.sig = calcRxSig(med);
    t.createTrTd('check');
    t.tr.id = 'rx-tr-' + i;
    t.tr.className = '';
    var click = 'clickRxMed(this)';
    var c = createCheckbox('sel-rx-med', i, null, click);
    t.append(c);
    if (med.checked || checkAll) c.checked = true;
    t.createTd('medname', med.name);
    t.td.id = 'rx-td-name-' + i;
    t.td.onclick = new Function('clickRxName(this)');
    t.append(createRxHidden('rx-name', i, med.name, 'name'));
    t.createTd();
    t.append(createRxHidden('rx-sig', i, med.sig, 'sig'));
    t.append(createSpan(null, med.sig, 'rx-span-sig-' + i));
    if (me.isErx()) t.td.style.width = '100%';
    t.createTd();
    t.append(createRxInput('rx-disp', i, med.disp, 'disp'));
    if (me.isErx()) t.td.style.display = 'none';
    t.createTd();
    t.append(createRefillSelect(i));
    if (me.isErx()) t.td.style.display = 'none';
    t.createTd('check noborder');
    t.append(createRxCheckbox('rx-dns', i, 'dns'));
    if (me.isErx()) t.td.style.display = 'none';
    t.createTd('check noborder');
    t.append(createRxCheckbox('rx-daw', i, 'daw'));
    if (me.isErx()) t.td.style.display = 'none';
    clickRxMed(c);
  }
}
function checkAllDns(c) {
  rxCheckAll('rx-dns', c.checked); 
}
function checkAllDaw(c) {
  rxCheckAll('rx-daw', c.checked); 
}
function rxCheckAll(idPrefix, value) {
  for (var i = 0; i < rx.meds.length; i++) {
    Html.InputCheck.$(idPrefix + '-' + i).setCheck(value);
  }
}
function setRefills(c) {
  var v = c.options[c.selectedIndex].text;
  for (var i = 0; i < rx.meds.length; i++) {
    Html.InputText.$('rx-refill-' + i).setValue(v);
  }
}
function setDisps(c) {
  var length = c.value;
  for (var i = 0; i < rx.meds.length; i++) {
    var med = rx.meds[i];
    if (med.autoCalcDisp) {
      if (c.value == '') {
        med.disp = med.odisp;
        med.length = med.olength;
      } else {
        med.disp = calcMedDisp(med.amt, med.freq, length);
        if (! isBlank(med.length)) {
          med.length = length;
        }
      }
      med.sig = calcRxSig(med);
      Html.InputText.$('rx-disp-' + i).setValue(med.disp);
      Html.InputText.$('rx-sig-' + i).setValue(med.sig);
      _$('rx-span-sig-' + i).setText(med.sig);
    }
  }
}
function calcRxSig(med) {
  var mt = medBuildText(med.amt, med.freq, med.route, med.length, med.asNeeded, med.meals);
  mt = String.trim(mt.replace(/for long-term/, ''));
  mt = String.trim(mt.replace(/MWF/, 'on Mon, Wed, Fri'));
  return mt;
}
function createRefillSelect(ix) {
  opts = [
      {'k':'0','v':'None'},
      {'k':'1','v':'1'},
      {'k':'2','v':'2'},
      {'k':'3','v':'3'},
      {'k':'4','v':'4'},
      {'k':'5','v':'5'},
      {'k':'6','v':'6'},
      {'k':'7','v':'7'},
      {'k':'8','v':'8'},
      {'k':'9','v':'9'},
      {'k':'10','v':'10'},
      {'k':'11','v':'11'},
      {'k':'12','v':'12'}];
  var s = createSelectByKvs('rx-refill-' + ix, null, opts);
  s.name = cna('refill', ix); 
  return s;
}
function createRxInput(idPrefix, ix, value, name) {
  var i = createInput(idPrefix + '-' + ix, 'text', value, 'w100');
  i.name = cna(name, ix);
  return i;
}
function createRxHidden(idPrefix, ix, value, name) {
  var h = createInput(idPrefix + '-' + ix, 'hidden', value);
  h.name = cna(name, ix);
  return h;
}
function createRxCheckbox(idPrefix, ix, name) {
  var c = createCheckbox(idPrefix + '-' + ix, '1');
  c.name = cna(name, ix);
  return c;
}
function cna(name, ix) {
  return name + '[' + ix + ']';
}
function clickRxMed(c) {  
  setDisabledOnly('rx-name-' + c.value, ! c.checked);
  setDisabledOnly('rx-sig-' + c.value, ! c.checked);
  setDisabledInput('rx-disp-' + c.value, ! c.checked);
  setDisabledInput('rx-refill-' + c.value, ! c.checked);
  setDisabledOnly('rx-dns-' + c.value, ! c.checked);
  setDisabledOnly('rx-daw-' + c.value, ! c.checked);
  setDisabled('rx-sig-' + c.value, ! c.checked);
  _$('rx-td-name-' + c.value).className = (c.checked) ? 'medname' : 'medname unselname';
  _$('rx-tr-' + c.value).className = (c.checked) ? 'off' : '';
  disableRxButton();
  if (! c.checked) _$('rx-med-tbl-ck').checked = false;
}
function clickRxName(td) {
  var c = td.previousSibling.firstChild;
  c.checked = ! c.checked;
  clickRxMed(c);
}
function disableRxButton() {
  var sel = getCheckedValues('sel-rx-med', 'rx-med-tbody');
  if (sel.length == rx.meds.length) _$('rx-med-tbl-ck').checked = true;
  setDisabled('med-cmd-print-rx4', sel.length == 0);  
  setDisabled('med-cmd-print-rx1', sel.length == 0);  
}
function printRx(docType, pageLayoutIndex) {
  Html.InputText.$('rx-doc-type').setValue(docType);
  Html.InputText.$('rx-pp').setValue(pageLayoutIndex);
  window.open('', 'rxw', 'top=0,left=0,resizable=1,toolbar=1,scrollbars=1,menubar=1');
  window.setTimeout(buildFn('printRxSubmit', [docType]), 10);
}
function printRxSubmit(docType) {
  _$('frm-rx').submit();
  if (docType != 1) {
    rxSaveCallback();
  }
  Pop.close();
}
function rxSaveCallback() {
  var cbMeds = [];
  var sel = getCheckedValues('sel-rx-med', 'rx-med-tbody');
  for (var j = 0; j < sel.length; j++) {
    var i = sel[j];
    var med = rx.meds[i];
    med.disp = value('rx-disp-' + i);
    med.refills = value('rx-refill-' + i);
    med.dns = isChecked('rx-dns-' + i);
    med.daw = isChecked('rx-dns-' + i);
    med.rx = rxFreetext(med);
    cbMeds.push(med);
  }
  if (rq) {
    rq.meds = cbMeds;
    rxcb(rq);    
  } else {
    rxcb(cbMeds);
  }
}
function rxFreetext(med) {
  return '(RX ' + rx.date + ' Disp: ' + med.disp + ', Refills: ' + med.refills + ')'; 
}
