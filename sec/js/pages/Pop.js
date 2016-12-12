/**
 * Popup Controller 
 */
var Pop = {
  // 
  POS_CENTER:1,
  POS_LAST:2,
  POS_CURSOR:3,
  //
  _pop:null,
  _unders:[],
  _lastPos:null,  // last cursor pos of any pop
  _curtain:null,
  _event:null,
  _POS_RESTORE:9,
  /*
   * Show popup
   * @arg 'id'/<div> div of popup   
   * @arg 'id'/<e> foc element to receive focus (optional) 
   * @arg int pos Pop.POS_ (optional, default POS_CENTER)
   * @callback(e) (optional)
   */
  show:function(div, foc, pos, callback) {
    if (pos != Pop._POS_RESTORE && Pop._pop) {
      Pop._unders.push(Pop._pop);
      Pop._pop._focus = document.activeElement;
    }
    Pop._pop = _$(div);
    if (Pop._pop.pp == null) 
      Pop._setProps(Pop._pop);
    Pop._showCurtainAndPop(Pop._pop, function() {
      Pop._setPos(Pop._pop, pos);
      if (foc) {
        try {
          if (String.is(foc))
            _$(foc).focusable().setFocus();
          else
            _$(foc).setFocus();
        } catch (e) {}
      }
              //    if (foc)         --- still buggy. when focusing on input afterwards the taborder is screwed
              //      focus_($_(foc));
              //    else if (Pop._pop.cap && Pop._pop.cap.box)
              //      focus_(Pop._pop.cap.box);
      //if (Page.browser.isMsie()) 
      //  setTimeout(hidePageScroll, 1);
      Html.Window.scrollable(false);
      Pop._event = null;
      if (callback) 
        callback(Pop._pop);
    });
  },
  showPosCursor:function(div, focusId) {
    Pop.show(div, focusId, Pop.POS_CURSOR);
  },
  showPosLast:function(div, focusId) {
    Pop.show(div, focusId, Pop.POS_LAST);
  },
  reposition:function() {
    var p = Pop._pop;
    if (p) {
      if (p._lastPos == null || p._lastPos.pos == Pop.POS_CENTER) { 
        Pop._setPosCenter(p);
      } else if (p._lastPos.pos == Pop.POS_CURSOR) {
        Pop._event = p._lastPos.event;
        Pop._setPosCursor(p);
      } else {
        p.repos(p._lastPos);
      }
    }
  },
  /*
   * Zoom popup
   * @callback() when complete (optional)
   */
  zoom:function(div, focusId, callback) {
    Pop.show(div, focusId, Pop.POS_CURSOR);
    if (callback)
      callback();
  },
  /*
   * Close currently displayed popup
   * @arg arg (optional, to supply to onclose)
   */
  close:function(arg) {
    var p = Pop._pop;
    if (p) {
      p.style.display = 'none';
      if (p.onclose) 
        p.onclose(arg);
      Pop._pop = null;
      if (Pop._unders.length) {
        p = Pop._unders.pop();
        Pop.show(p, null, Pop._POS_RESTORE);
        if (p._focus) {
          try {p._focus.focus()} catch (e) {}
          p._focus = null;
        }
        if (p && p.onpopactivate)
          p.onpopactivate();
      } else {
        Pop._hideCurtain();
        if (Page.browser.isMsie6() == 6) 
          restoreCombos(document);
      }
    }
  },
  closeAll:function() {
    while (Pop._pop)
      Pop.close();
  },
  /*
   * @arg 'id'/<div> div of popup (optional)
   * @return bool true if div is current top pop (when div supplied)
   *              true if any pop is up (when div is omitted)  
   */
  isActive:function(div) {
    if (div) {
      var pop = _$(div);
      return (Pop._pop && pop && Pop._pop.id == pop.id);
    } else {
      return (Pop._pop != null);
    }
  },
  /*
   * @return bool true if active pop is dirty 
   */
  isDirty:function() {
    if (Pop._pop && Pop._pop.isDirty)
      return Pop._pop.isDirty();
  },
  /*
   * Re-center current pop
   */
  center:function() {
    if (Pop._pop)
      Pop._setPosCenter(Pop._pop);
  },
  /*
   * @arg 'id'/<div> div of popup
   * @arg string text (optional)
   */
  setCaption:function(div, text) {
    _$(div).innerText = (String.isBlank(text)) ? 'Clicktate' : 'Clicktate - ' + text;
  },
  //
  _closeByControlBox:function(e) {
    var p = Pop._pop;
    if (p) {
      if (p.cap && p.cap.box) 
        Html.Anchor._proto.click.call(p.cap.box, Pop.close);
      else
        Pop.close();
      Html.Window.cancelBubble(e);
    }
  },
  _shutdown:function() {  // TODO: doesn't work right because IE fires onbeforeunload from <a href='javascript:'>
    Pop.shutdown = true;
    Pop.dirty = false;
    while (Pop._pop) {
      Pop._closeByControlBox();
      if (Pop.dirty) {
        Pop.shutdown = false;
        return "There is a record with unsaved changes.";
      }
    }
  },
  _closeByEventKey:function() {
    var e = Html.Window.getEvent();
    if (e) {
      if (e.keyCode == 27) 
        Pop._closeByControlBox();
      else if (e.keyCode == 8) {
        var tag = event.srcElement && event.srcElement.tagName.toUpperCase();
        switch (tag) {
          case 'INPUT':
          case 'TEXT':
          case 'PASSWORD':
          case 'TEXTAREA':
          case null:
            break;  // you may pass
          default:
            e.keyCode = 0;
            Html.Window.cancelBubble(e);
            if (e.preventDefault)
              e.preventDefault();
        }
      }
    }
  },
  _setProps:function(p) {
    p.pp = 1;
    p.onmousedown = Html.Window.cancelBubble;
    p.ontouchend = Html.Window.cancelBubble;
    var tags = p.getTagsByClass('pop-cap', 'DIV');
    if (tags.length == 1) {
      p.cap = _$(tags[0]);
      p.cap.onmousedown = Pop._startDrag;
      p.cap.ontouchstart = Pop._startDragTouch;
      tags = Pop._pop.cap.getTagsByClass('pop-close', 'A');
      if (tags.length == 1) 
        p.cap.box = _$(tags[0]);
    }
  },
  _showCurtainAndPop:function(p, callback) {
    if (Pop._curtain == null) {
      Pop._curtain = _$('curtain');
    }
    if (Pop._curtain.style.display != 'block') {
      Pop._curtain.style.display = 'block';
      Pop._sizeCurtain();
      Html.attach(document, 'mousedown', Pop._closeByControlBox);
      Html.attach(document, 'touchend', Pop._closeByControlBox);
      Html.attach(document, 'keydown', Pop._closeByEventKey);
    }
    if (Page.browser.isMsie6()) {
      p.style.display = 'block';
      hideCombos();
      restoreCombos(p);
      Pop._setZIndex();
      callback();
    } else {
      p.style.display = 'block';
      Pop._setZIndex();
      callback();
    }
  },
  _sizeCurtain:function() {
    var c = Pop._curtain;
    if (c) {
      var wd = Html.Window.getDim();
      var vd = Html.Window.getViewportDim();
      c.setHeight(Math.max(vd.height, wd.height)).setWidth(wd.width);
    }
  },
  _hideCurtain:function() {
    if (Pop._curtain) {
      Pop._curtain.style.display = 'none';
      Html.Window.scrollable(true);
      Html.detach(document, 'mousedown', Pop._closeByControlBox);
      Html.detach(document, 'touchend', Pop._closeByControlBox);
      Html.detach(document, 'keydown', Pop._closeByEventKey);
    }
  },
  _setPos:function(p, pos) {
    if (pos != Pop._POS_RESTORE) {
      if (pos == Pop.POS_LAST && Pop._lastPos) 
        Pop._setPosLast(p);
      else if (pos == Pop.POS_CURSOR) 
        pos = Pop._setPosCursor(p);
      else
        Pop._setPosCenter(p);
      p._lastPos = Pop._getPos(p, pos);
      if (pos != Pop.POS_CENTER)
        Pop._lastPos = p._lastPos;
    } 
  },
  _setPosCenter:function(p) {
    p.center();
  },
  _setPosLast:function(p) {
    if (Pop._lastPos == null) 
      Pop._setPosCenter(p);
    else {
      p.style.left = String.px(String.toInt(Pop._lastPos.left) + 20);
      p.style.top = Pop._lastPos.top;
    }
  },
  cacheMousePos:function(ev) {
    Pop._event = null;
	
	console.log('trg = ' + ev.target + ', srcElem = ' + ev.srcElement);
	
   if (typeof(ev.srcElement) == 'undefined') {
	  var evnt = ev.target;
	}
	else {
	  var evnt = ev.srcElement;
	}
	
	
    if (evnt && evnt.clientX) {
      Pop._event = {
        'clientX':evnt.clientX,
        'clientY':evnt.clientY};
    }
  },
  _setPosCursor:function(p) {
  
   console.log('trg = ' + p.target + ', srcElem = ' + p.srcElement);
	
   if (typeof(p.srcElement) == 'undefined') {
	  var evnt = p.target;
	}
	else {
	  var evnt = p.srcElement;
	}
	
    var e = (evnt && evnt.clientX) ? evnt : Pop._event;
    if (evnt)
      Pop.cacheMousePos();
    if (e == null) { 
      Pop._setPosCenter(p);
      return Pop.POS_CENTER;
    } else {
      p.repos({'left':e.clientX - 50, 'top':e.clientY - 30});
      return Pop.POS_CURSOR;
    }
  },
  _getPos:function(p, pos) {
    return {
      'left':p.style.left,
      'top':p.style.top,
      'pos':pos,
      'event':Pop._event};
  },
  _setZIndex:function() {
    var z = 255 + Pop._unders.length * 2;
    if (Pop._curtain)
      Pop._curtain.style.zIndex = z;
    Pop._pop.style.zIndex = z + 1;
  },
  _startDrag:function(e) {
    var event = Html.Window.getEvent(e);
    var p = Pop._pop;
    if (p && event.srcElement.className != 'pop-close') {
      document.isdrag = true;
      document.mx = event.clientX;
      document.my = event.clientY;
      document.px = p.offsetLeft;
      document.py = p.offsetTop;
      document.onmousemove = Pop._drag;
      document.onmouseup = Pop._endDrag;
    }
  },
  _startDragTouch:function(e) {
    var event = Html.Window.getEvent(e);
    var p = Pop._pop;
    if (document.isdrag) 
      Pop._dragTouch(e);
    else if (p && event.srcElement.className != 'pop-close') {
      document.isdrag = true;
      document.mx = event.changedTouches[0].pageX;
      document.my = event.changedTouches[0].pageY;
      document.px = p.offsetLeft;
      document.py = p.offsetTop;
      p.cap.ontouchmove = Pop._dragTouch;
      p.cap.ontouchend = Pop._endDragTouch;
      Html.Window.cancelBubble(event);
    }
  },
  _drag:function(e) {
    var event = Html.Window.getEvent(e);
    var p = Pop._pop;
    if (p && document.isdrag) 
      p.setLeft(document.px + event.clientX - document.mx).setTop(document.py + event.clientY - document.my);
  },
  _dragTouch:function(e) {
    var event = Html.Window.getEvent(e);
    var p = Pop._pop;
    if (p && document.isdrag) 
      p.setLeft(document.px + event.changedTouches[0].pageX - document.mx).setTop(document.py + event.changedTouches[0].pageY - document.my);
    Html.Window.cancelBubble(event);
  },
  _endDrag:function() {
    document.isdrag = false;
    document.onmousemove = null;
    document.onmouseup = null;
    var p = Pop._pop;
    if (p && p.onreposition)
      p.onreposition();
  },
  _endDragTouch:function() {
    document.isdrag = false;
    var p = Pop._pop;
    if (p) {
      p.cap.ontouchmove = null;
      p.cap.ontouchend = null;
    }
  }
}
Pop.Builder = {
  _pop:null,
  _context:null,
  /*
   * Dynamically build a pop:
   *   <div id='[id]' class='pop' ctlbox=<a> content=<div> caption=<div>>
   *     <div class='pop-cap'> 
   *       <div>caption</div>
   *       <a href='[close]' class='pop-close'></a>
   *     </div>
   *     <div class='pop-content'> 
   *     </div>
   *   </div> 
   * @arg obj context pop controller
   * @arg string id of popup <div>
   * @arg string caption
   * @arg string width (optional, default '650px')
   * @arg string/fn close (optional, default 'Pop.close()')
   * @return <div> of pop  
   */
  build:function(context, id, caption, width, close) {
    close = close || 'Pop.close()';
    var pop = _$(id);
    if (pop) 
      pop.clean();
    else
      //pop = insertTag(createDiv(id, 'pop'), document.getElementById('page-includes'));
      pop = Html.Window.append(Html.Div.create('pop').setId(id));
      pop.setWidth(width);    
    //pop.style.width = width || '';
    var pcap = Html.Div.create('pop-cap').into(pop);
    pop.caption = Html.Div.create().into(pcap);
    pop.caption.innerText = caption;
    pop.ctlbox = appendInto(pcap, createAnchor(null, null, 'pop-close', null, null, close, context));
    pop.content = Html.Div.create('pop-content').into(pop);
    this._pop = pop;
    this._context = context;
    return pop;
  },
  div:function(className, id) {
    var div = Html.Div.create(className).into(this._pop.content).setId(id);
    return div;
  },
  entryUl:function() {
    return Html.Ul.create().into(this._pop.content);
  },
  entryForm:function(firstLabelClass) {
    var ul = this.entryUl();
    var form = new EntryForm(ul, firstLabelClass);
    return form;
  },
  cmdBar:function() {
    var cmdBar = new CmdBar(this._pop.content, null, this._context);
    return cmdBar;
  }
}
/*
 * Invocations
 */
Pop._Loader = {
  JS_PATH:'js/pops/',
  HTML_PATH:'js/pops/inc/',
  TILE_PATH:'js/tiles/',
  //
  get:function(name, hasHtml, fn) {
    Pop.cacheMousePos(fn);
    var inc = [Pop._Loader.JS_PATH + name + '.js'];
    if (hasHtml)
      inc.push(Pop._Loader.HTML_PATH + name + '.php');
    Includer.getWorking(inc, fn);
  }
}
//
Pop.Calendar = {
  /*
   * @arg string value (optional)
   * @callback(value) on calendar save
   */
  show:function(value, callback) {
    Pop.cacheMousePos(callback);
    Pop._Loader.get('Calendar', true, function() {
      Calendar.pop(value, callback);
    });
  },
  /*
   * @arg string id of textbox
   */
  showFromTextbox:function(id) {
    var textbox = _$(id);
    this.show(textbox.value,
      function(text) {
        if (text)
          textbox.value = text;
        focus(id);
      });
  }
}
Pop.Clock = {
  /*
   * @arg string value (optional)
   * @arg bool hourOnly (optional)
   * @callback(value) on clock save
   */
  show:function(value, hourOnly, callback) {
    Pop._Loader.get('Calendar', true, function() {
      Clock.pop(value, hourOnly, callback);
    });
  },
  /*
   * @arg string id of textbox
   * @arg bool hourOnly (optional)
   */
  showFromTextbox:function(id, hourOnly) {
    var textbox = _$(id);
    this.show(textbox.value, hourOnly,
      function(text) {
        if (text)
          textbox.value = text;
        focus(id);
      });
  }
}
Pop.Msg = {
  /*
   * @arg string html message text
   * @arg string iconClass 'information'
   * @arg string caption (optional)
   * @callback() when closed (optional)
   */
  show:function(html, iconClass, caption, callback) {
    this._callback = callback;
    this._build(html, iconClass);
    this._pop.caption.innerText = caption || 'Clicktate';
    Pop.show(this._pop, this._pop.ok);
    this._pop.style.zIndex = 600;
  },
  showInfo:function(html, callback) {
    this.show(html, 'information', null, callback);
  },
  showImportant:function(html, callback) {
    this.show(html, 'important', null, callback);
  },
  showCritical:function(html, callback) {
    this.show(html, 'critical', null, callback);
  },
  showCriticalCap:function(html, caption, callback) {
    this.show(html, 'critical', caption, callback);
  },
  //
  _pOk:function() {
    if (this._callback)
      this._callback();
    Pop.close();
  },
  _pop:null,
  _build:function(html, iconClass) {
    this._pop = Pop.Builder.build(this, 'pop-msg', 'Clicktate', 500);
    Pop.Builder.div(iconClass).innerHTML = html;
    var cmdBar = Pop.Builder.cmdBar();
    this._pop.ok = cmdBar.ok(this._pOk);
  }
}
Pop.Confirm = {
  /*
   * @arg string text 'Are you sure..'
   * @arg string yesCaption 
   * @arg string yesClass (optional, default 'ok')
   * @arg string noCaption 
   * @arg string noClass (optional)
   * @arg bool includeCancel (optional, default false)
   * @arg string iconClass (optional, default 'question')
   * @arg fn(bool) callback true=yes, false=no (no callback for 'no' when cancel excluded) 
   * @arg bool alwaysCallback true=callback(bool) for any response including cancel (optional, default false)
   */
  show:function(text, yesCaption, yesClass, noCaption, noClass, includeCancel, iconClass, callback, alwaysCallback) {
    this._callback = callback;
    this._always = alwaysCallback;
    this._build(text, yesCaption || 'Yes', yesClass, noCaption || 'No', noClass, includeCancel, iconClass);
    Pop.show(this._pop, this._pop.yes);
  },
  showImportant:function(text, yesCaption, yesClass, noCaption, noClass, includeCancel, callback) {
    this.show(text, yesCaption, yesClass, noCaption, noClass, includeCancel, 'important', callback);
  },
  showYesNo:function(text, callback, always) {
    this.show(text, null, null, null, null, false, null, callback, always);
  },
  showYesNoCancel:function(text, callback, always) {
    this.show(text, null, null, null, null, true, null, callback, always);
  },
  showDelete:function(noun, callback, always) {
    noun = noun || 'record';
    if (always)
      this.showYesNoCancel('Are you sure you want to delete this ' + noun + '?', callback, always);
    else
      this.showYesNo('Are you sure you want to delete this ' + noun + '?', callback);
  },
  showDeleteAlways:function(noun, callback) {
    this.showDelete(noun, callback, true);
  },
  showDeleteRecord:function(callback) {
    this.showDelete('record', callback);
  },
  showDeleteChecked:function(verb, callback) {
    this.showYesNoCancel('Are you sure you want to ' + verb + ' the checked selection(s)?', callback);
  },
  showDirtyExit:function(saveCallback, close) {
    close = close || Pop.close;
    var text = 'This record has unsaved changes. Do you want to save before exiting?';
    this.show(text, 'Save and Exit', 'save', "Don't Save, Just Exit", null, true, null,
      function(confirmed) {
        if (confirmed) 
          saveCallback();
        else
          close();
      });
  },
  /*
   * Close pop but confirm if entry form has unsaved changes 
   * ex. Pop.Confirm.closeCheckDirty(this, this.pSave);
   * @arg obj context pop controller
   * @arg EntryForm entryForm
   * @arg fn saveFn, e.g. this.pSave
   */
  closeCheckDirty:function(context, saveFn) {
    if (context.isDirty()) {
      if (Pop.shutdown)
        Pop.dirty = true;
      else
        this.showDirtyExit(function() {
          saveFn.call(context)
      });
    } else {
      Pop.close();
    }
  },
  //
  _pop:null,
  _callback:null,
  _close:function(confirmed) {
    Pop.close();
    this._callback(confirmed);
  },
  _closeYes:function() {
    this._close(true);
  },
  _closeNo:function() {
    if (this._pop.cancel || this._always) 
      this._close(false);
    else
      Pop.close();
  },
  _closeCancel:function() {
    if (this._always == 'null') {
      this._close(null);
    } else if (this._always)
      this._close(false);
    Pop.close();
  },
  _build:function(text, yesCap, yesCls, noCap, noCls, incCancel, iconCls) {
    iconCls = iconCls || 'question';
    yesCls = yesCls || 'ok';
    this._pop = Pop.Builder.build(this, 'pop-conf', 'Confirmation Required', 400);
    Pop.Builder.div(iconCls).innerHTML = text;
    var cmdBar = Pop.Builder.cmdBar();
    this._pop.yes = cmdBar.button(yesCap, yesCls, this._closeYes);
    this._pop.no = cmdBar.button(noCap, noCls, this._closeNo);
    this._pop.cancel = (incCancel) ? cmdBar.cancel(this._closeCancel) : null;
  }
}
Pop.Prompt = {
  /*
   * @arg string html prompt text
   * @callback(text) when OK clicked
   */
  show:function(html, callback) {
    this._callback = callback;
    this._build(html);
    Pop.show(this._pop, this._pop.textbox);
  },
  //
  _pOk:function() {
    if (this._callback)
      this._callback(this._pop.textbox.value);
  },
  _pCancel:function() {
    Pop.close();
  },
  //
  _pop:null,
  _callback:null,
  _build:function(html) {
    this._pop = Pop.Builder.build(this, 'pop-prompt', 'Clicktate');
    Pop.Builder.div('question').innerHTML = html;
    this._pop.textbox = appendInto(Pop.Builder.div('mb10'), createTextbox(null, null, 30));
    var cmdBar = Pop.Builder.cmdBar();
    cmdBar.ok(this._pOk);
    cmdBar.cancel(this._pCancel);
  }
}
Pop.Working = {
  /*
   * @arg string html text to display (optional, default 'Working')
   * @arg bool locked to hide control box (optional, default false)
   */
  show:function(html, locked) {
    html = html || 'Working';
    this._build();
    if (Pop.isActive(this._pop))
      Pop.close();
    this._bar.innerHTML = html;
    Page.visible(this._pop.ctlbox, locked);
    Pop.show(this._pop);
  },
  close:function() {
    if (Pop.isActive(this._pop))
      Pop.close();
  },
  //
  _pop:null,
  _bar:null,
  _build:function() {
    if (this._pop == null) {
      this._pop = Pop.Builder.build(this, 'pop-work', 'Clicktate', '380px');
      this._bar = Pop.Builder.div('workingbar', 'pop-workingbar');
    }
  }
}
Pop.Log = {
  show:function() {
    Pop._Loader.get('Log', false, function() {
      Log.pop();
    });
  }
}
Pop.Patient = {
  /*
   * @arg Client client
   * @callback(Client) if anything changed
   */
  showEditor:function(client, callback) {
    Pop._Loader.get('PatientEditor', true, function() {
      PatientEditor.pop(client, null, callback);
    });
  }
}
/* ui.js remnamts */
var psh = false;
function appendInto(parent, child) {
  parent.appendChild(child);
  return child;
}
function hideCombos() {
  if (Page.browser.isMsie6()) {   
    var s = document.getElementsByTagName("select");
    for (var i = 0; i < s.length; i++) { 
      var cb = s[i]; 
      if (isRendered(cb)) {
        var t = document.createElement("input");
        if (cb.selectedIndex > -1) {
          t.value = cb.options[cb.selectedIndex].text;
        }
        t.style.position = 'absolute';
        t.style.width = cb.clientWidth; 
        t.style.height = cb.clientHeight; 
        t.style.padding = "1px 15px 0 3px"; 
        t.style.margin = "0";
        t.style.fontSize = cb.currentStyle.fontSize;
        t.style.fontFamily = cb.currentStyle.fontFamily;
        cb.parentElement.insertBefore(t, cb); 
        cb.style.visibility = 'hidden';
        cb.tag = 1; 
      }
    }
  } 
} 
function restoreCombos(container) {
  if (Page.browser.isMsie6()) {   
    container = container || document; 
    var s = container.getElementsByTagName("select"); 
    for (var i = 0; i < s.length; i++) { 
      var cb = s[i]; 
      if (cb.tag) { 
        var t = cb.previousSibling; 
        cb.parentElement.removeChild(t); 
  //      cb.style.display = "";
        cb.style.visibility = 'visible';
        cb.tag = null;
      }
    }
  }
} 
function isRendered(e) {
  if (e.parentElement) {
    do {
      if (e.tagName == 'BODY') {
        return true;
      }
      e = _$(e);
      if (e.getStyle('display') == 'none' || e.getStyle('visibility') == "hidden") {
        return false;
      }
    } while (e = e.parentElement);
  }
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
