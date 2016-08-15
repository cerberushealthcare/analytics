// ICD pop
// To call: showIcd(searchFor)  // searchFor optional
// Callback: icdCallback(code, desc)  // selected code, or null if user cleared
var icdsByCode = {};  // cache
var icdSel;
var icdCallbackFn;
var icd10;
function showIcd10(code, desc, callback) {
  showIcd(code, desc, callback, true);
}
function showIcd(code, desc, callback, as10) {  // override callback or false for no callback
  if (callback) {
    icdCallbackFn = callback;
  } else {
    icdCallbackFn = (callback === false) ? null : icdCallback; 
  }
  icd10 = !! as10;
  _$('pop-icd-cap-text').setText(icd10 ? 'ICD10 Lookup' : 'ICD9 Lookup');
  Html.InputText.$('icd-search');
  icdReset();
  icdSel.codeToSet = code;
  if (callback || callback === false) {
    Pop.show("pop-icd", "icd-search", true);
  } else {
    Pop.show("pop-icd", "icd-search", true, pqNoCurtain, pqUseLastPos);    
  }
  _$('icd-search').setFocus();
  if (code) {
    Html.Input.$('icd-search').setValue(code);
    doIcdSearch();
    //sendRequest(2, "action=getIcdCodes&id=" + icdSel.codeToSet);
    //_$('icd-tree').working(true);
  } else if (desc) {
    Html.Input.$('icd-search').setValue(desc);
    doIcdSearch();
  }
}
function icdReset() {
  _$("icd-search").setValue("");
  icdSel = {
      "codeToSet":0,
      "codeSet":0,
      "desc":"",
      "history":{
        "codes":[],
        "ix":-1
        }
      }
  icdEnableNavs();
  _$("icd-tree-ul").clean();
  _$("icd-info-code").setText("");
  _$("icd-info-desc").setText("");
  _$("icd-info-syn").hide();
  _$("icd-info-exc").hide();
  _$("icd-info-inc").hide();
  _$("icd-info-note").hide();
  //setDisabled("icd-ok", true);
}
function getIcdCodesCallback(jIcdCodes) {
  icdBuildList(jIcdCodes);
  _$('icd-tree').working(false);
}
function icdBuildList(result) {
  var icds = result.icdCodes;
  var searchFor = result.expr;
  var maxAll = (result.icd3Ct < 2);
  var rootUl = _$("icd-tree-ul");
//  if (searchFor == null) {
    clearChildren(rootUl);
    rootUl.scrollTop = 0;
//  } else {
//    for (var i = 0; i < rootUl.childNodes.length; i++) {
//      var e = ul.childNodes[i];
//      e.old = true;
//    }
//  }
  if (icds == null) {
    rootUl.li('max').html("No matching records found.");
    return;
  }
  icds = icdAddToCache(icds);
  var iter = {
    "level":3,
    "ul":rootUl,
    "insertBefore":null,
    "searchSelReset":false
    };
  for (var i = 0; i < icds.length; i++) {
    var icd = icds[i];
    if (icd.level == 3) {
      //iter.insertBefore = icdGetInsertBefore(rootUl, icd.code);
      iter.insertBefore = null;
    }
    if (icd.level > iter.level) {  // start a sub-list
      icdStartSubList(iter, icd);
      iter.insertBefore = null;
    } else {
      while (icd.level < iter.level) {  // close sub-lists
        icdCloseSubList(iter, icd);
      }
    }
    var li = addListItem2(iter.ul, iter.insertBefore, icdBuildAnchorHtml(icds[i], searchFor));
    if (searchFor != null) {
      if (icdShowOrHide(li, icd.level, maxAll, icds[i]) && ! iter.searchSelReset) {
        icdSel.codeToSet = icds[i].code;
        iter.searchSelReset = true;
      }
    }
    if (icd.level == 3) {
      li.icd = icd.code;
      li.className = "i3";
    } else if (icd.level == 4) {
      li.className = "i4";
    }
  }
  while (iter.level > 3) {
    icdCloseSubList(iter, icd);
  }
  if (result.more) {
    var li = addListItem2(rootUl, null, "Maximum matches returned.");
    li.className = "max";
  }
  //selectIcd(icdSel.codeToSet);
  selectIcd(result.bestFit);
}
function icdStartSubList(iter, icd) {
  iter.level++;
  iter.ul = addChildList2(iter.ul, iter.insertBefore);
  iter.ul.desc = icdLevelDesc(icd);
  var sib = iter.ul.previousElementSibling != UNDEFINED ? iter.ul.previousElementSibling : iter.ul.previousSibling; 
  if (sib) {
    iter.ul.style.display = sib.style.display;
  }
}
function icdCloseSubList(iter, icd) {
  if (iter.ul.hid) {
    var li = addListItem2(iter.ul, null, "<a class=icd-more href='javascript:' onclick='icdShowAll(this.parentElement);return false'>[show all " + iter.ul.desc + "]</a>");
    if (icd.level == 4) {
      li.className = "i5";
    } else {
      li.className = "i4";
    }
  }
  iter.level--;
  iter.ul = iter.ul.parentElement;
}
function icdIsInList(icd) {
  return (icdsByCode[icd.code] != null);
}
function icdGetInsertBefore(ul, code) {
  for (var i = 0; i < ul.childNodes.length; i++) {
    var e = ul.childNodes[i];
    if (e.icd != null && e.icd > code) {
      return e;
    }
  }
  return null;  // indicates add to bottom
}
function icdShowOrHide(li, level, maxAll, icd) {  // returns true if search text hilited
  var h = li.innerHTML;
  var isHilited = h.indexOf("<U>") >= 0;
  var isSelected = h.indexOf("class=sel") >= 0;
  var hipaa = icd10 ? icd.hipaa == '1' : true;
  if (level > 3 && ! isHilited && ! isSelected && ! maxAll && ! hipaa) {
    li.style.display = "none";
    li.parentElement.hid = true;  // <ul> of this item
  } else {
    while (level > 3) {
      var ul = li && li.parentElement;
      if (ul) {
        ul.style.display = "";
        li = ul.previousElementSibling != UNDEFINED ? ul.previousElementSibling : ul.previousSibling;  // <li> of parent ICD 
        if (li) li.style.display = "";
      }
      level--;
    }
  }
  return isHilited;
}
function icdShowAll(li) {
  li.style.display = "none";  // hide the show all tag
  var ul = li.parentElement;
  for (var i = 0; i < ul.childNodes.length - 1; i++) {  // length-1 to skip the show all tag
    var e = ul.childNodes[i];
    e.style.display = "";
  }
}
function icdLevel(icd) {
  if (icd10) {
    return icdLevel10(icd);
  }
  switch (icd.code.length) {
  case 3:
    return 3;
  case 4:
    return 3;
  case 5:
    return 4;
  case 6:
    if (icd.code.substring(0, 1) == "E") {
      return 4;
    } else {
      return 5;
    }
  }
}
function icdLevel10(icd) {
  switch (icd.code.length) {
  case 3:
    return 3;
  default:
    return icd.code.length - 1;
  }  
}
function icdLevelDesc(icd) {
  return icd.code.substring(0, icd.code.length - 1) + "x";
}
function icdBuildAnchorHtml(icd, searchFor) {
  var code = icdHilite(icd.code, searchFor);
  var desc = icd.desc;
  if (icd.syn != null) {
    desc += " &nbsp;<i>e.g.<i> " + icd.syn;
  }
  desc = icdHilite(desc, searchFor);
  var cls = '';
  var html = "<a id=aicd" + icd.code + " href='javascript:selectIcd(\"" + icd.code + "\")'";
  if (icd.code == icdSel.codeSet) {
    cls = 'sel';
  }
  if (icd.hipaa == '0') {
    cls += ' nosl';
  }
  if (icd.level == 3) {
    cls += " i3";
  }
  if (cls != '') {
    html += ' class="' + cls + '"';
  }
  html += ">" + code + " <span";
  html += ">" + desc + "</span></a>";
  return html;
}
function icdHilite(text, searchFor) {
  if (searchFor == null) {
    return text;
  }
  // text = text.replace(/<U>|<\/U>/g, "");  // clear out prior search
  var r = new RegExp("(" + searchFor + ")", "gi");
  return text.replace(r, "<u>$1</u>");
}
function icdClass(len) {
  if (len == 5) {
    return "i4";
  } else if (len == 6) {
    return "i5";
  } else {
    return "i3";
  }
}
function doIcdSearch() {
  var icdSearchText = _$("icd-search").getValue();
  _$('icd-tree').working(true);
//  Ajax.get(Ajax.SVR_JSON, 'searchIcdCodes', icdSearchText, 
//    function(jIcdCodes) {
//      overlayWorking(false);
//      icdBuildList(jIcdCodes);
//    });
  var action = icd10? 'search10' : 'search';
  Ajax.get(Ajax.SVR_ICD, action, {text:icdSearchText}, 
    function(result) {
      _$('icd-tree').working(false); 
      icdBuildList(result);
    });
}
function icdFullDesc(icd) {
  var concat = false;
  var desc = icd.desc || '';
  if (icd.parent) {
    switch (icd.level) {
    case 4:
      if (icdShouldConcat(icd.parent.desc, icd.desc)) {
        desc = icd.parent.desc + ": " + desc;
      }
      break;
    case 5:
      if (icdShouldConcat(icd.parent.desc, icd.desc)) {
        desc = icd.parent.desc + ": " + desc;
      }
      if (icdShouldConcat(icd.parent.parent.desc, icd.desc)) {
        desc = icd.parent.parent.desc + ": " + desc;
      }
    }
  }
  return desc;
}
function icdShouldConcat(pdesc, cdesc) {
  return false;
  if (! icdBeginsWithOther(pdesc)) {
    if (icdDescIncomplete(cdesc)) {
      return true;
    } else {
      return ! icdFirstWordIn(pdesc, cdesc); 
    }
  }
}
function icdDescIncomplete(s) {
  var w = s.split(" ", 2)[0];
  if (/^(due|cause|from|by|of|in|with|without|unspecified)$/i.test(w)) {
    return true;
  }
  if (s == "Other") {
    return true;
  }
}
function icdBeginsWithOther(s) {
  return (s.split(" ", 2)[0] == "Other");
}
function icdFirstWordIn(s1, s2) {  // true if first word of s1 is in s2
  var r = new RegExp("(" + s1.split(" ", 2)[0] + ")", "i");
  return r.test(s2);
}
function selectIcd(code, noHistory) {
  if (code == null)
    return;
  if (code == icdSel.codeSet) 
    return;
  if (! noHistory) {
    icdAddHistory(code);
  }
  var icd = icdsByCode[code];
  icdSelectAnchor(icdSel.codeSet, false);
  icdSelectAnchor(code, true);
  icdSel.codeSet = code;
  icdSel.desc = icdFullDesc(icd);
  _$("icd-info-code").setText(icdSel.codeSet);
  _$("icd-info-desc").setText(icdSel.desc);
  if (icd.syn != null) {
    _$("icd-info-syn").show();
    _$("icd-info-syn").html(icd.syn);
  } else {
    _$("icd-info-syn").hide();
  }
  if (icd.hipaa == '0') {
    _$("icd-info-syn").show();
    _$("icd-info-syn").setClass('wrn').html("Warning! This ICD is not complete");
  } else if (icd.hipaa == '1') {
    _$("icd-info-syn").hide();    
  }
  if (icd.exc != null) {
    _$("icd-info-exc").show();
    _$("icd-info-exc-text").html(icd.exc);
  } else {
    _$("icd-info-exc").hide();
  }
  if (icd.inc != null) {
    _$("icd-info-inc")._$().show();
    _$("icd-info-inc-text").html(icd.inc);
  } else {
    _$("icd-info-inc").hide();
  }
  if (icd.notes != null) {
    _$("icd-info-note").show();
    var d = icd.notes;
    _$("icd-info-note-text").html(icd.notes);
  } else {
    _$("icd-info-note").hide();
  }
  //setDisabled("icd-ok", false);
}
function icdAddHistory(code) {
  var h = icdSel.history;
  if (h.ix == h.codes.length - 1) {
    h.codes.push(code);
  } else {
    h.codes.splice(h.ix + 1, h.codes.length - 1, code);
  }
  h.ix++;
  icdEnableNavs();
}
function icdEnableNavs() {
  _$("icd-nav-prev").className = (icdSel.history.ix > 0) ? "enabled" : "";
  _$("icd-nav-next").className = (icdSel.history.ix < icdSel.history.codes.length - 1) ? "enabled" : "";
}
function icdPrev() {
  if (icdSel.history.ix > 0) {
    icdSel.history.ix--;
    icdNavHistory();
  }
}
function icdNext() {
  if (icdSel.history.ix < icdSel.history.codes.length - 1) {
    icdSel.history.ix++;
    icdNavHistory();
  }
}
function icdNavHistory() {
  selectIcd(icdSel.history.codes[icdSel.history.ix], true);
  icdEnableNavs();
}
function icdSelectAnchor(id, isOn) {
  if (id != null) {
    var a = _$("aicd" + id);
    if (a != null) {
      if (isOn) {
        if (! a.className.contains('sel')) {
          a.className += ' sel';
        }  
      } else {
        a.className = a.className.replace('sel', '');
      }
    }
  }
}
function icdAddToCache(icds) {
  var icd;
  for (var i = 0; i < icds.length; i++) {
    icd = icds[i];
    if (icd.syn != null) {
      icd.syn = "<i>" + icd.syn.replace(/<br\/>/g, "; ") + "</i>";
    }
    icd.level = icdLevel(icd);
    if (icd.level == 4) {
      icd.parent = icdsByCode[icd.code.substring(0, 3)]; 
    } else if (icd.level == 5) {
      icd.parent = icdsByCode[icd.code.substring(0, 5)]; 
    }
    icdsByCode[icd.code] = icd;
  }
  return icds;
}
function icdClear() {
  Pop.close();
  if (icdCallbackFn) icdCallbackFn();  
}
function icdOk() {
  Pop.close();
  if (icdCallbackFn) icdCallbackFn(icdSel.codeSet, icdSel.desc);
}
function ifCrClick2(id) {
  if (event.keyCode == 13) {
    _$(id).onclick();
    event.cancelBubble = true;
    return false;
  }
}
function addListItem2(ul, insertBefore, html, id, className) {  // leave insertBefore null to add to bottom of list  
  var li = document.createElement("li");
  li.id = id;
  li.className = className;
  if (html) li.innerHTML = html;
  if (insertBefore) {
    ul.insertBefore(li, insertBefore);
  } else {
    ul.insertBefore(li, null);
  }
  return li;
}
function addChildList2(parentUl, insertBefore) {
  var ul = document.createElement("ul");
  if (insertBefore) {
    parentUl.insertBefore(ul, insertBefore);
  } else {
    parentUl.insertBefore(ul, null);
  }
  return ul;
}
function unselectText2() {
  try {
    document.selection.empty();
  } catch (e) {}
}
