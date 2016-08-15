function lcheck(lbl, onclick) {  // label onClick for checkbox 
  var c = lbl.previousSibling;
  c.checked = ! c.checked;  // this triggers lcheckc
  if (onclick)
    onclick(c);
}
function lcheckc(c) {  // checkbox onPropertyChange
  c.nextSibling.className = (c.checked) ? "lcheck-on" : "lcheck";
}
function clearAllRows(tbody, keepHeader) {
  var i = (keepHeader) ? 1 : 0;
  while (tbody.children.length > i)
    tbody.deleteRow(i);
}
function clearChildren(p) {
  while (p.hasChildNodes()) 
    p.removeChild(p.lastChild);
  return p;
}
function createAnchor(id, href, className, innerText, innerHtml, onClick, context) {
  var a = document.createElement("a");
  if (id != null) 
    a.id = id;
  a.href = href || 'javascript:';
  if (className != null) 
    a.className = className;
  if (innerText != null) 
    a.innerText = innerText;
  if (innerHtml != null)
    a.innerHTML = innerHtml;
  if (onClick) {
    var fn;
    if (String.is(onClick)) {
      // onClick += ';return false';
      fn = new Function(onClick);
    } else {
      if (context) 
        fn = function(){onClick.call(context)};
        //fn = function(){onClick.call(context);return false};
      else
        fn = onClick;
      //fn = function(){onClick();return false};
    }
    a.onclick = fn;
  }
  return a;
}
function createA(className, innerText, onClick) {
  return createAnchor(null, null, className, innerText, null, onClick);
}
function createTable(id, className, tbodyId, withThead) {
  var t = document.createElement("TABLE");
  if (id) t.id = id;
  if (className) t.className = className;
  if (withThead) {
    var head = document.createElement("THEAD");
    t.appendChild(head);
    t.head = head;
  }
  var body = document.createElement("TBODY");
  if (tbodyId)
    body.id = tbodyId;
  t.appendChild(body);
  t.body = body;
  return t;
}
function appendTr(table, trClass) {
  var tr = createTr(trClass);
  table.lastChild.appendChild(tr);
  return tr;
}
function createTr(className, id) {
  var tr = document.createElement("tr");
  if (className) tr.className = className;
  if (id) tr.id = id;
  return tr;
}
function createTdHtml(html, className) {
  var td = document.createElement("td");
  if (className) td.className = className;
  td.innerHTML = html;
  return td;
}
function createTh(innerText, className, style) {
  var td = document.createElement("th");
  td.innerText = innerText || '';
  if (className) td.className = className;
  if (style) td.style.cssText = style;
  return td;
}
function createTd(innerText, className) {
  var td = document.createElement("td");
  td.innerText = innerText || '';
  if (className) td.className = className;
  return td;
}
function createTdAnchor(href, className, innerText, id, title, tdClassName) {
  var td = document.createElement("td");
  var a = document.createElement("a");
  a.href = href;
  a.className = className;
  a.innerText = innerText;
  a.id = id;
  a.title = title;
  a.className = className;
  td.appendChild(a);
  td.className = tdClassName;
  return td;
}
function addOpt(sel, value, text, selected) {
  var opt = document.createElement("option");
  sel.options.add(opt);
  opt.value = value || '';
  opt.text = text || '';
  opt.selected = (selected == true);
  return opt;
}
function addKvsOpts(sel, jKeyValues) {
  for (var i = 0; i < jKeyValues.length; i++) {
    addOpt(sel, jKeyValues[i].k, jKeyValues[i].v, jKeyValues[i].sel);
  }  
}
function createOpts(selectId, jKeyValues) {  // [{"v":"text","k":"key","sel":true},{"v":"text","k":"key"},...]
  var sel = _$(selectId);
  sel.clean();
  addKvsOpts(sel, jKeyValues);
  return sel;
}
function flicker(id) {  // to fix fixed TRs
  return flicker_(_$(id));
}
function flicker_(e) {
  e.style.display = 'none';
  e.style.display = '';
  return e;
}
function setValue_(e, value) {
  var v = value || '';
  if (e.tagName == "SELECT") {
    for (var i = 0; i < e.options.length; i++) {
      if (e.options[i].value == v) {
        e.options[i].selected = true;
        return e;
      }
    }
    e.options[0].selected = true;
    return e;
  }
  e.value = v;
  return e;  
}
function $$(id) {  // getElementsById 
  var a = document.all[id];
  return (a == null) ? [] : a;
}
function bulletJoin(a, skipNulls) {  // skipNulls optional
  a = Array.from(a);
  if (skipNulls)
    a = Array.filter(a);
  return (a.isEmpty()) ? "" : a.join(" <u class='bullet'>&#x2022;</u> ");
}
function findAncestorWith(e, propName, propValue) {
  if (e[propName] == propValue) {
    return e;
  }
  if (e.parentElement == null) {
    return null;
  }
  if (e.parentElement.tagName == 'BODY') {
    return null;
  }
  return findAncestorWith(e.parentElement, propName, propValue);
} 
function findEventAncestorWith(propName, propValue) {
  if (event && event.srcElement) {
    return findAncestorWith(event.srcElement, propName, propValue);
  }
}
