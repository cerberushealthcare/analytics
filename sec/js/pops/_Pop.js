/**
 * Popup Controller 
 */
var Pop = {
  // 
  POS_CENTER:1,
  POS_LAST:2,
  POS_MOUSE:3,
  POS_ZOOM:4,
  //
  _pop:null,
  _unders:[],
  _lastPos:null,
  _curtain:null,
  _event:null,
  _POS_RESTORE:9,
  /*
   * Show popup
   * @arg string id of popup <div> 
   * @arg int pos Pop.POS_
   * @arg string focusId (optional) 
   */
  show:function(id, pos, focusId) {
    if (pos != Pop._POS_RESTORE && Pop._pop) 
      Pop._unders.push(Pop._pop);
    Pop._pop = $(id);
    if (Pop._pop.pp == null) 
      Pop._setProps(Pop._pop);
    Pop._showCurtainAndPop(Pop._pop);
    Pop._setPos(Pop._pop, pos);
    if (focusId)
      focus(focusId);
    hidePageScroll();
    Pop._event = null;
  },
  /*
   * Close currently displayed popup
   */
  close:function() {
    var p = Pop._pop;
    if (p) {
      p.style.display = 'none';
      Pop._pop = null;
      if (Pop._unders.length) {
        p = Pop._unders.pop();
        Pop.show(p.id, Pop._POS_RESTORE);
      } else {
        Pop._hideCurtain();
        restoreCombos(document);
      }
    }
  },
  closeConfirmDirty:function(context, entryForm, saveFn) {
    if (entryForm && entryForm.isRecordChanged()) {
      Pop.Confirm.showDirtyExit(null, 
        function(confirmed) {
          if (confirmed) 
            saveFn.call(context);
          else 
            Pop.close();
        });
    } else {
      Pop.close();
    }
  },
  //
  _closeByControlBox:function() {
    var p = Pop._pop;
    if (p) {
      if (p.cap && p.cap.box) {
        p.cap.box.click();
      } else {
        Pop.close();
      }
    }
  },
  _closeByEventKey:function() {
    if (event && event.keyCode == 27) {
      Pop._closeByControlBox();
    }
  },
  _setProps:function(p) {
    p.pp = 1;
    p.onmousedown = Pop._cancelBubble;
    var tags = $$$$('pop-cap', p, 'DIV');
    if (tags.length == 1) {
      p.cap = tags[0];
      p.cap.onmousedown = Pop._startDrag;
      tags = $$$$('pop-close', Pop._pop.cap, 'A');
      if (tags.length == 1) 
        p.cap.box = tags[0];
    }
  },
  _showCurtainAndPop:function(p) {
    if (Pop._curtain == null) 
      Pop._curtain = $('curtain');
    var c = Pop._curtain;
    if (c.style.display != 'block') {
      var h = document.body.offsetHeight;
      var w = document.body.offsetWidth;
      if (document.documentElement.clientHeight > h) 
        h = document.documentElement.clientHeight;
      if (document.documentElement.clientWidth > w) 
        w = document.documentElement.clientWidth;
      c.style.height = h;
      c.style.width = w;
      c.style.display = 'block';
    }
    p.style.display = 'block';
    Pop._setZIndex();
    hideCombos();
    restoreCombos(p);
  },
  _hideCurtain:function() {
    if (Pop._curtain) {
      Pop._curtain.style.display = 'none';
      showPageScroll();
    }
  },
  _setPos:function(p, pos) {
    if (pos != Pop._POS_RESTORE) {
      if (pos == Pop.POS_LAST && Pop._lastPos) 
        Pop._setPosLast(p);
      else if (pos == Pop.POS_MOUSE) 
        Pop._setPosMouse(p);
      else
        Pop._setPosCenter(p);
    } 
    Pop._lastPos = Pop._getPos(p);
  },
  _setPosCenter:function(p) {
    var top = document.documentElement.clientHeight / 2 - p.clientHeight / 2;
    var left = document.documentElement.clientWidth / 2 - p.clientWidth / 2;
    var width = p.style.width;
    if (top < 0) 
      top = 0;
    if (left < 0) 
      left = 0;
    p.style.width = p.clientWidth;
    p.style.top = top + document.documentElement.scrollTop;
    p.style.left = left + document.documentElement.scrollLeft;
    if (width == "") 
      p.style.width = "";
  },
  _setPosLast:function(p) {
    if (Pop._lastPos == null) 
      Pop._setPosCenter(p);
    else {
      p.style.left = Pop._lastPos.left;
      p.style.top = Pop._lastPos.top;
    }
  },
  _cacheMousePos:function() {
    Pop._event = null;
    if (event) {
      Pop._event = {
        'clientX':event.clientX,
        'clientY':event.clientY};
    }
  },
  _setPosMouse:function(p) {
    var e = (event) ? event : Pop._event;
    if (e == null) 
      Pop._setPosCenter(p);
    else {
      var ac = {
        'x':e.clientX - 50, 
        'y':e.clientY - 30}; 
      var cw = document.body.offsetWidth;
      var ch = document.documentElement.clientHeight;
      if ((ac.x + p.clientWidth) > cw) 
        ac.x = cw - p.clientWidth - 30;
      if ((ac.y + p.clientHeight) > ch) 
        ac.y = ch - p.clientHeight - 5;
      if (ac.x < 0) 
        ac.x = 0;
      if (ac.y < 0) 
        ac.y = 0;
      p.style.top = ac.y + document.documentElement.scrollTop;
      p.style.left = ac.x;
    }
  },
  _getPos:function(p) {
    return {
      'left':p.style.left,
      'top':p.style.top
      };
  },
  _setZIndex:function() {
    var z = 255 + Pop._unders.length * 2;
    if (Pop._curtain)
      Pop._curtain.style.zIndex = z;
    Pop._pop.style.zIndex = z + 1;
  },
  _cancelBubble:function() {
    event.cancelBubble = true;
  },
  _startDrag:function() {
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
  _drag:function() {
    var p = Pop._pop;
    if (p && document.isdrag) {
      p.style.left = document.px + event.clientX - document.mx;
      p.style.top = document.py + event.clientY - document.my;
    }
  },
  _endDrag:function() {
    document.isdrag = false;
    document.onmousemove = null;
    document.onmouseup = null;
  }
}
Pop.Builder = {
  _pop:null,
  _context:null,
  /*
   * Dynamically build a pop:
   *   <div id='[id]' class='pop' content=<div>>
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
    width = denull(width, '650px');
    close = denull(close, 'Pop.close()');
    var pop = insertTag(createDiv(id, 'pop'));
    pop.style.width = width;
    var pcap = createDivIn('pop-cap', pop);
    createDivIn(null, pcap).innerText = caption;
    pcap.appendChild(createAnchor(null, null, 'pop-close', null, null, close, context));
    pop.content = createDivIn('pop-content', pop);
    this._pop = pop;
    this._context = context;
    return pop;
  },
  div:function(className) {
    var div = createDivIn(className, this._pop.content);
    return div;
  },
  entryUl:function() {
    var ul = createList(this._pop.content);
    return ul;
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
    Pop._cacheMousePos();
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
    Pop._Loader.get('Calendar', true, function() {
      Calendar.pop(value, callback);
    });
  }
}
Pop.Confirm = {
  /*
   * @arg string noun (optional, default 'entry')
   * @callback(confirmed) where confirmed true=yes, false=no
   */
  showDirtyExit:function(noun, callback) {
    Pop._Loader.get('Confirm', false, function() {
      Confirm.popDirtyExit(noun, callback);
    });
  }
}
Pop.Log = {
  show:function() {
    Pop._Loader.get('Log', false, function() {
      Log.pop();
    });
  }
}
Pop.Face = {
  zoom:null,
  //
  showAllergies:function(fs, callback) {
  },
  showDiagnoses:function(fs, callback) {
  },
  showMeds:function(fs, callback) {
  },
  showVitals:function(fs, callback) {
  }
}
Pop.Patient = {
  showSelector:function(callback) {
  },
  showEditor:function(callback) {
  }
}
Pop.Preview = {
  /*
   * @arg int cid
   * @arg int type: artifact type
   * @arg int id: artifact ID
   */
  show:function(cid, type, id) {
  }  
}