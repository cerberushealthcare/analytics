/**
 * LCD FRAMEWORK: HTML
 * @version 1.1
 * @author Warren Hornsby
 */
function _$(e) {
  if (String.is(e))
    e = document.getElementById(e);
  return Html.decorate(e);
}
var Html = {
  is:function(e) {
    return e && e.nodeName != null;
  },
  create:function(tag, cls) {
    var e = document.createElement(tag);
    if (cls)
      e.className = cls;
    Html.decorate(e);
    return e;
  },
  getTag:function(tag, container, createIfNull) {
    var tags = container.getElementsByTagName(tag);
    if (tags && tags.length > 0)
      return Html.decorate(tags[0]);
    else
      return (createIfNull) ? container.append(tag) : null;
  },
  decorate:function(e) {
    if (e && ! e._decorated) {
      Class.augment(e, null, Html._proto);
      e._decorated = Html;
      switch (e.tagName) {
        case 'TABLE':
          Class.augment(e, null, Html.Table._proto);
          break;
        case 'TBODY':
        case 'THEAD':
          Class.augment(e, null, Html.Table._protoBody);
          break;
        case 'UL':
          Class.augment(e, null, Html.Ul._proto);
      }
    }
    return e;
  },
  /*
   * @arg <e> e
   * @arg fn(self) protof
   */
  extend:function(e, protof) {
    e._protof = protof;
    return e.aug(protof(e));
  },
  /*
   * @arg <e>|id e
   * @arg object proto
   */
  augment:function(e, proto) {
    e = _$(e);
    return (e._augmented) ? e : e.aug(proto).set('_augmented', 1);
  },
  attach:function(o, event, fn) {
    Html.Window.attachEvent(event, fn, o);
  },
  detach:function(o, event, fn) {
    Html.Window.detachEvent(event, fn || this, o);
  },
  _proto:{
    /*
     * @arg object proto
     */
    aug:function(proto) {
      if (proto) {
        var self = this;
        if (Array.is(proto)) {
          proto.forEach(this.aug.bind(this));
        } else {
          Class.augment(this, null, proto);
          if (proto.init)
            proto.init.call(this);
        }
      }
      return this;
    },
    /*
     * @arg fn(self) protof
     */
    extend:function(proto, protof) {
      if (protof == null)
        return Html.extend(this, proto);
      proto._protof = protof;
      return this.aug(protof(this, (function(parent) {
        return parent._protof(this);
      }).bind(this)));
    },
    /*
     * @arg string fid
     * @arg mixed value
     */
    set:function(fid, value) {
      if (fid)
        this[fid] = value;
      return this;
    },
    /*
     * @arg string event
     * @arg object|fn to context|function
     * @arg string toEvent (optional, default same as event if context supplied)
     * e.g. bubble('onselectopt', self)
     *      bubble('onclick', self.toggle)
     *      bubble('onclick', self, 'onabstract')
     * Recognize that the second form is no good for events intended to be inherited, as the function reference is evaluated only once at init and will always point to the empty @abstract
     */
    bubble:function(event, to, toEvent) {
      if (to == null && toEvent == null)
        throw new Error(null, 'No receiver for ' + event);
      if (Function.is(to))
        this[event] = to;
      else
        this[event] = function(){to[toEvent || event].apply(this, arguments)};
      return this;
    },
    append:function(e, fid) {
      if (String.is(e))
        e = Html.create(e);
      this.appendChild(_$(e));
      if (fid)
        this.set(fid, e);
      return e;
    },
    into:function(e) {
      if (e)
        e.appendChild(this);
      else
        Html.Window.append(this);
      return this;
    },
    before:function(e) {
      if (e && e.parentElement)
        e.parentElement.insertBefore(this, e);
      return this;
    },
    after:function(e) {
      if (e && e.parentElement)
        e.parentElement.insertBefore(this, e.nextSibling);
      return this;
    },
    add:function(e, fid) {
      if (e)
        this.append(e, fid);
      return this;
    },
    remove:function() {
      this.parentElement.removeChild(this);
    },
    clean:function() {
      while (this.hasChildNodes())
        this.removeChild(this.lastChild);
      return this;
    },
    tile:function(protof, cls) {
      return Html.Tile.create(this, cls).extend(protof);
    },
    setId:function(id) {
      if (id)
        this.id = id;
      return this;
    },
    setText:function(s) {
      if (typeof this.textContent !== "undefined") {
        this.textContent = String.denull(s);
      } else {
        this.innerText = String.denull(s);
      }
      return this;
    },
    getInnerText:function() {
      return this.textContent || this.innerText;
    },
    hide:function() {
      this.style.display = 'none';
      this.showing = null;
      return this;
    },
    show:function() {
      this.style.display = '';
      this.showing = this;
      return this;
    },
    isHidden:function() {
      return this.style.display == 'none';
    },
    showIf:function(test) {
      if (test)
        this.show();
      else
        this.hide();
      return this;
    },
    hideIf:function(test) {
      return this.showIf(! test);
    },
    visible:function() {
      this.style.visibility = '';
      return this;
    },
    invisible:function() {
      this.style.visibility = 'hidden';
      return this;
    },
    visibleIf:function(test) {
      return (test) ? this.visible() : this.invisible();
    },
    isShowing:function() {
      var e = this;
      if (e.parentElement) {
        do {
          if (e.tagName == 'BODY')
            return true;
          e = _$(e);
          if (e.getStyle('display') == 'none' || e.getStyle('visibility') == "hidden")
            return false;
        } while (e = e.parentElement);
      }
    },
    isChildOf:function(parent) {  // returns true if this == parent
      var e = this;
      if (e.parentElement) {
        do {
          if (e == parent)
            return true;
          if (e.tagName == 'BODY')
            return false;
        } while (e = e.parentElement);
      }
    },
    getStyle:function(style) {
      if (this.currentStyle)
        return this.currentStyle[style];
      else
        return document.defaultView.getComputedStyle(this, null)[style];
    },
    cursor:function(s) {
      this.style.cursor = s;
      return this;
    },
    float:function(value) {
      this.style.cssFloat = this.style.styleFloat = value;
      return this;
    },
    getPosDim:function() {
      var pos = this.getPos();
      var dim = this.getDim();
      return Map.combine([pos, dim]);
    },
    getPos:function() {
      if (this.getBoundingClientRect) {
        var br = this.getBoundingClientRect();
        var st = Html.Window.getScrollTop();
        return {'left':br.left,'top':br.top + st};
      }
      var l = 0, t = 0, e = this;
      if (e.offsetParent) {
        do {
          l += e.offsetLeft;
          t += e.offsetTop;
          if (e.className == 'pop')
            break;
        } while (e = e.offsetParent);
      }
      return {'left':l,'top':t};
    },
    /*
    getPosWithin:function(p) {
      var l = 0, t = 0, e = this;
      if (e.parentElement) {
        do {
          if (e == p)
            break;
          l += e.offsetLeft;
          t += e.offsetTop;
        } while (e = e.parentElement);
      }
      return {'left':l,'top':t};
    },
    */
    setLeft:function(i) {
      this.style.left = String.px(i);
      return this;
    },
    setTop:function(i) {
      this.style.top = String.px(i);
      return this;
    },
    getDim:function() {
      var dis = this.style.display;
      if (this.getStyle('display') == 'none')
        this.style.display = 'block';
      var h = this.offsetHeight;
      var w = this.offsetWidth;
      this.style.display = dis;
      return {'height':h,'width':w};
    },
    getHeight:function() {
      return this.getDim().height;
    },
    getWidth:function() {
      return this.getDim().width;
    },
    setDim:function(h, w) {
      return this.setHeight(h).setWidth(w);
    },
    setHeight:function(i, min) {
      min = min || 0;
      if (i != null && i < min)
        i = min;
      this.style.height = (i == null) ? 'auto' : String.px(i);
      return this;
    },
    setWidth:function(i, min) {
      min = min || 0;
      if (i != null && i < min)
        i = min;
      this.style.width = (i == null) ? 'auto' : String.px(i);
      return this;
    },
    setHeightToMax:function(pad) {
      pad = pad || 0;
      this.setHeight(Html.Window.getViewportDim().height - pad);
      return this;
    },
    setSizeWithin:function(h, w, maxh, maxw) {
      h = String.toInt(h);
      w = String.toInt(w);
      var r = h / w;
      if ((maxh && h > maxh) || (maxw && w > maxw)) {
        if (r > 1 && maxh && h > maxh) {
          r = maxh / h;
          h = maxh;
          w = w * r;
        } else {
          r = maxw / w;
          w = maxw;
          h = h * r;
        }
      }
      this.setHeight(h);
      this.setWidth(w);
      return this;
    },
    repos:function(pos, scroller) {  // set left/top while ensuring within viewport
      var st = Html.Window.getScrollTop();
      var ac = {
        'x':String.toInt(pos.left),
        'y':String.toInt(pos.top + st)};
      var dd = Html.Window.getViewportDim();
      var cw = dd.width;
      var ch = dd.height + st;
      if ((ac.x + this.clientWidth) > cw)
        ac.x = cw - this.clientWidth - 30;
      if ((ac.y + this.clientHeight) > ch)
        ac.y = ch - this.clientHeight - 30;
      if (ac.x < 0)
        ac.x = 0;
      if (ac.y < st)
        ac.y = st;
      this.setLeft(ac.x).setTop(ac.y);
    },
    setPosition:function(s) {
      this.style.position = s;
      return this;
    },
    getScrollHeight:function() {
      return this.scrollHeight;
    },
    scrollToBottom:function() {
      this.scrollTop = this.scrollHeight;
    },
    scrollToTop:function() {
      this.scrollTop = 0;
    },
    center:function() {
      var dim = Html.Window.getViewportDim();
      var left = dim.width / 2 - this.clientWidth / 2;
      var top = dim.height / 2 - this.clientHeight / 2;
      if (left < 0)
        left = 0;
      if (top < 0)
        top = 0;
      left += Html.Window.getScrollLeft();
      top += Html.Window.getScrollTop();
      return this.setLeft(left).setTop(top);
    },
    centerWithin:function(e) {
      var pp = _$(e).getPos();
      var pd = e.getDim();
      var d = this.getDim();
      if (pd.height == 0 || pd.width == 0)
        return this.center();
      else
        return this.setTop(pp.top + pd.height/2 - d.height/2).setLeft(pp.left + pd.width/2 - d.width/2);
    },
    html:function(h) {
      this.innerHTML = String.denull(h);
      return this;
    },
    nbsp:function() {
      this.innerHTML = '&nbsp;';
      return this;
    },
    working:function(e) {
      if (e) {
        Html.Window.registerWorking(this);
        this._isworking = true;
        if (this._working == null)
          this._working = Html.Window.append(Html.Div.create(this.workingClass()));
        this._working.style.display = 'block';
        this._working.centerWithin(this);
        this._working.style.visibility = 'visible';
        if (Function.is(e))
          async(e);
      } else if (this._isworking) {
        this._isworking = false;
        if (this._working)
          this._working.style.display = 'none';
      }
      return this;
    },
    workingClass:function() {
      return 'working-float';
    },
    withWorkingSmall:function() {
      this.workingClass = function() {return 'working-float-small'};
      return this;
    },
    withWorkingLarge:function() {
      this.workingClass = function() {return 'working-float-large'};
      return this;
    },
    work:function(fn) {
      work(this, fn);
    },
    getTagsByClass:function(cls, tag) {
      return Html.Window.getTagsByClass(cls, tag, this);
    },
    hasClass:function(cls, startsWith) {
      return Html.Window.hasClass(this, cls, startsWith);
    },
    setClass:function(cls) {
      this.className = cls || '';
      return this;
    },
    addClass:function(cls) {
      if (! this.hasClass(cls))
        this.className = String.trim(this.className + ' ' + cls);
      return this;
    },
    removeClass:function(cls) {
      this.className = String.trim(this.className.replace(cls, ''));
      return this;
    },
    addClassIf:function(cls, test) {
      if (test)
        this.addClass(cls);
      else
        this.removeClass(cls);
      return this;
    },
    setUnselectable:function() {
      this.unselectable = 'on';
      this.addClass('unselectable');
      Array.forEach(this.children, function(e) {
        if (e._decorated)
          e.setUnselectable();
      })
      return this;
    },
    focusable:function(e) {
      (e || this).aug(Html._focus);
      if (e)
        this.aug({
          hasFocus:e.hasFocus.bind(e),
          setFocus:e.setFocus.bind(e)
        })
      return this;
    }
  },
  _focus:{
    hasFocus:function() {
      return document.activeElement == this;
    },
    setFocus:function() {
      try {
        this.focus();
      } catch (ex) {
      }
      return this;
    }
  }
}
Html.Window = {
  isIe:function() {
    return navigator.appName == 'Microsoft Internet Explorer';
  },
  getEvent:function(e) {
    if (e) {
      if (! e.srcElement)
        e.srcElement = e.target;
    } else {
      e = window.event;
    }
    return e;
  },
  cancelBubble:function(e) {
    var e = Html.Window.getEvent(e);
    if (e.stopPropagation)
      e.stopPropagation();
    else
      e.cancelBubble = true;
    return e;
  },
  getEventTo:function(e) {
    e = Html.Window.getEvent(e);
    return e.relatedTarget || e.toElement;
  },
  getEventFrom:function(e) {
    e = Html.Window.getEvent(e);
    return e.relatedTarget || e.fromElement;
  },
  getScrollTop:function() {
    if (typeof pageYOffset != 'undefined')
      return pageYOffset;
    else
      return document.documentElement.scrollTop;
  },
  getScrollLeft:function() {
    if (typeof pageXOffset != 'undefined')
      return pageXOffset;
    else
      return document.documentElement.scrollLeft;
  },
  getDim:function() {
    return {'height':document.body.clientHeight,'width':document.body.clientWidth};
  },
  getTagsByClass:function(cls, tag, container, startsWith) {
    container = container || document.body;
    var all = container.getElementsByTagName(tag);
    var r = [], e;
    for (var i = 0; (e = all[i]) != null; i++)
      if (this.hasClass(e, cls, startsWith))
        r.push(_$(e));
    return r;
  },
  hasClass:function(e, cls, startsWith) {
    var extra = (startsWith) ? '*' : '(?:$|\\s)';
    var hasClassName = new RegExp('(?:^|\\s)' + cls + extra);
    var ec = e.className;
    if (ec && ec.indexOf(cls) != -1 && hasClassName.test(ec))
      return true;
  },
  getViewportDim:function() {
    var h, w;
    if (typeof window.innerWidth != 'undefined') {
      w = window.innerWidth;
      h = window.innerHeight;
    } else {
      w = document.documentElement.offsetWidth;
      h = document.documentElement.offsetHeight
    }
    return {'height':h,'width':w};
  },
  attachEvent:function(event, fn, o) {
    o = o || window;
    if (window.addEventListener)
      o.addEventListener(event, fn, false);
    else
      o.attachEvent('on' + event, fn);
  },
  detachEvent:function(event, fn, o) {
    o = o || window;
    if (window.removeEventListener)
      o.removeEventListener(event, fn, false);
    else
      o.detachEvent('on' + event, fn);
  },
  setOnFocus:function(fn) {
    if (! this.isTablet()) {
      if ("onfocusin" in document)
        document.onfocusin = fn; 
      else 
        window.onfocus = fn;
    } else {
      var last = new Date().getTime();  // temp? fix for iPad Safari until window.onfocus supported for tab focus
      var looping = window.onfocusin2; 
      window.onfocusin2 = fn;
      if (! looping) {
        (function loop() {
          var now = new Date().getTime();
          if (now - last > 2000) 
            window.onfocusin2 && window.onfocusin2();
          last = now;
          Html.Window.reqAnimFrame(loop);
        })();
      }
    }
  },
  getOnFocus:function() {
    if (! this.isTablet()) {
      if ("onfocusin" in document)
        return document.onfocusin;
      else
        return window.onfocus;
    } else {
      return window.onfocusin2;
    }
  },
  setOnBlur:function(fn) {
    if ("onfocusout" in document)
      document.onfocusout = fn;
    else
      window.onblur = fn;
  },
  reqAnimFrame:function(fn) {
    if (! window.requestAnimationFrame)
      Html.Window.setReqAnimFrame();
    window.requestAnimationFrame(fn);
  },
  setReqAnimFrame:function() {
    var lastTime = 0;
    var vendors = ['ms', 'moz', 'webkit', 'o'];
    for (var x = 0; x < vendors.length && ! window.requestAnimationFrame; ++x) {
      window.requestAnimationFrame = window[vendors[x] + 'RequestAnimationFrame'];
      window.cancelAnimationFrame = window[vendors[x] + 'CancelAnimationFrame'] || window[vendors[x] + 'CancelRequestAnimationFrame'];
    }
    if (! window.requestAnimationFrame)
      window.requestAnimationFrame = function(callback, element) {
        var currTime = new Date().getTime();
        var timeToCall = Math.max(0, 16 - (currTime - lastTime));
        var id = window.setTimeout(function() {
          callback(currTime + timeToCall)
        }, timeToCall);
        lastTime = currTime + timeToCall;
        return id;
      }
    if (! window.cancelAnimationFrame)
      window.cancelAnimationFrame = function(id) {
        clearTimeout(id);
      }
  },
  execScript:function(str) {
    if (window.execScript)
      window.execScript(str);
    else
      with (window)
        window.eval(str);
  },
  append:function(e) {
    document.body.appendChild(_$(e));
    return e;
  },
  scrollable:function(b) {
    if (b) {
      document.documentElement.style.overflow = '';
      document.documentElement.style.paddingRight = '';
    } else {
      var scroll = (document.body.scrollHeight > Html.Window.getViewportDim().height);
      document.documentElement.style.overflow = 'hidden';
      if (scroll)
        document.documentElement.style.paddingRight = '16px';
    }
  },
  registerFixedRow:function(tr) {
    if (Html.Window._trs == null)
      Html.Window._trs = [];
    Html.Window._trs.push(tr);
  },
  flickerFixedRows:function() {
    Array.forEach(Html.Window._trs, function(tr) {
      tr.style.display = 'none';
      tr.style.display = '';
    });
  },
  registerWorking:function(e) {
    if (Html.Window._wks == null)
      Html.Window._wks = [];
    Html.Window._wks.push(e);
  },
  clearWorking:function() {
    if (Html.Window._wks) {
      Html.Window._wks.forEach(function(e) {
        if (e) {}
          e.working(false);
      })
    }
    Html.Window._wks = null;
  },
  working:function(e) {
    var self = Html.Window;
    if (e) {
      Html.Window.registerWorking(self);
      self._isworking = true;
      if (self._working == null) {
        self._working = Html.Window.append(Html.Div.create('working-float'));
      }
      self._working.style.display = 'block';
      self._working.center();
      self._working.style.visibility = 'visible';
      if (Function.is(e))
        async(e);
    } else if (self._isworking) {
      self._isworking = false;
      if (self._working)
        self._working.style.display = 'none';
    }
    return self;
  },
  isTablet:function() {
    return false;
    return ! Object.isUndefined(document.ontouchstart);
  },
  isiPhone:function() {
    return (
      (navigator.platform.indexOf("iPhone") != -1) ||
      (navigator.platform.indexOf("iPod") != -1)
    );
  }
}
Html.Curtain = {
  //
  show:function(working) {
    var c = Html.Curtain.get();
    var wd = Html.Window.getDim();
    var vd = Html.Window.getViewportDim();
    c.style.display = 'block';
    c.setHeight(Math.max(vd.height, wd.height)).setWidth(wd.width);
    if (working)
      working();
  },
  hide:function() {
    Html.Curtain.get().style.display = 'none';
  },
  get:function() {
    if (Html.Curtain._c == null)
      Html.Curtain._c = _$('curtain');
    return Html.Curtain._c;
  }
}
Html.Array = function() {
  return Object.augment(Array.prototype.slice.call(arguments), {
    into:function(e) {
      this.each(function(i) {
        i && i.into(e);
      })
    }
  })
}
Events = {};
Events.onkeypresscr = {
  onkeypresscr:function() {},
  //
  onkeypress:function() {
    if (event.keyCode == 13)
      this.onkeypresscr();
  }
}
Events.ignoredblclick = {
  ondblclick:function() {
    if (Html.Window.isIe())
      this.onclick();
  }
}
Events.onscrollbottom = {
  onscrollbottom:function() {},
  //
  onscroll:function() {
    if (this.scrollLocked) {
      if (this.scrollTop != this._slpos)
        this.scrollTop = this._slpos;
    } else {
      if (this.scrollTop + this.clientHeight >= this.scrollHeight - 20) {
        if (! this._osb) {
          this._osb = this;
          this.onscrollbottom();
        }
      } else {
        if (this._osb)
          this._osb = null;
      }
    }
  },
  lockScroll:function(b) {
    this.scrollLocked = b;
    if (b)
      this._slpos = this.scrollTop;
  }
}
Events.onhoverclass = {  // for anchors, use CSS A:hover
  onmouseover:function() {
    if (! this.className.endsWith('hover'))
      this.className += 'hover';
  },
  onmouseout:function() {
    if (this.className.endsWith('hover'))
      this.className = this.className.substr(0, this.className.length - 5);
  }
}
Html.Animator = {
  /*
   * Set background yellow
   * @arg <e> e
   */
  highlight:function(e) {
    e.rgb0 = hexToNumbers(_$(e).getStyle('backgroundColor'));
    e.rgb1 = [255,255,128];
    e.style.backgroundColor = rgbString(e.rgb1);
  },
  /*
   * Set background yellow, fade to transparent
   * @arg <e> e
   * @arg fn() onfinish (optional)
   */
  fade:function(e, onfinish) {
    if (e == null)
      return;
    if (e.fading) {
      e.fadecancel = function(){Html.Animator.fade(e, onfinish)};
      return;
    }
    e.fading = true;
    if (e.rgb0 == null)
      Html.Animator.highlight(e);
    var rgb0 = e.rgb0;
    var rgb1 = e.rgb1;
    e.rgb = rgb1;
    e.rgbOff = [rgb0[0] - rgb1[0], rgb0[1] - rgb1[1], rgb0[2] - rgb1[2]];
    e.style.backgroundColor = Html.Animator.rgbstring(rgb1);
    var fdix = 0;
    var fdmax = 40;
    pause(0.5, function() {
      loop(function(exit) {
        fdix++;
        var m = fdix / fdmax;
        var rgb = [e.rgb[0] + e.rgbOff[0] * m, e.rgb[1] + e.rgbOff[1] * m, e.rgb[2] + e.rgbOff[2] * m];
        e.style.backgroundColor = Html.Animator.rgbstring(rgb);
        if (fdix == fdmax || e.fadecancel) {
          e.style.backgroundColor = '';
          e.rgb = null;
          e.rgb0 = null;
          e.fading = null;
          if (e.fadecancel) {
            var fadecancel = e.fadecancel;
            e.fadecancel = null;
            fadecancel();
          }
          exit(onfinish);
        }
      })
    })
  },
  deflate:function(e, onfinish) {
    if (e == null)
      return;
    var inc = 0.005;
    var limit = 0.01;
    var zoom = 1;
    pause(0.2, function() {
      loop(function(exit) {
        zoom -= inc; 
        if (zoom < limit) {
          e.style.display = 'none';
          exit(onfinish);
        }
        e.style.zoom = zoom - inc;
        inc = inc * 1.1;
      })
    })
  },
  /*
   * Scroll to element within a scrollable div
   * @arg <e>}'id' div
   * @arg <e>|'id' to (optional, omit to scroll to top)
   * @arg int padding (optional; e.g. to accommodate height of a fixed header row)
   */
  scrollTo:function(div, to, padding, callback) {
    div = _$(div);
    if (div == null)
      return;
    var e = to && _$(to);
    // to = (e) ? e.offsetTop - div.offsetTop : 0;
    to = (e) ? e.getPos().top - div.getPos().top : 0;
    to = Math.max(0, to - (padding || 0));
    var sce = {
      div:div,
      to:to,
      inc:Math.sgn(to - div.scrollTop),
      speed:1.3};
    // TODO: add scrollingcancel
    pause(0.1, function() {
      loop(function(exit) {
        var top = sce.div.scrollTop + sce.inc;
        if ((sce.inc < 0 && top < sce.to) || (sce.inc > 0 && top > sce.to) || sce.inc == 0) {
          top = sce.to;
          sce.div.scrollTop = top;
          exit();
        } else {
          sce.div.scrollTop = top;
          sce.inc = sce.inc * sce.speed;
        }
      })
    })
  },
  /*
   * Pulse (swell) element
   * @arg int to (default 4 times size)
   * @arg int inc (default 0.2)
   * @arg fn() onfinish (optional)
   */
  pulse:function(e, to, inc, onfinish) {
    if (e == null || e.swell)
      return;
    var hide = e.isHidden();
    if (hide)
      e.style.display = 'block';
    var s = {
      to:to || 4,
      inc:inc || 0.2,
      dir:1,
      zoom:1,
      sp:e.style.position,
      hide:hide,
      pos:e.getPos()};
    pause(0.1, function() {
      loop(function(exit) {
        var limit = (s.dir == 1) ? s.to : 1;
        var zoom = s.zoom + s.inc * s.dir;
        if ((s.dir * (zoom - limit)) > 0) {
          zoom = limit;
          if (s.dir == -1)
            s.done = true;
          else
            s.dir = -1;
        }
        e.style.zoom = zoom;
        e.setLeft(s.pos.left / zoom - (zoom - 1) * 2);
        e.setTop(s.pos.top / zoom - (zoom - 1) * 2);
        if (s.done) {
          if (e.hide)
            e.style.display = 'none';
          exit(onfinish);
        } else {
          s.zoom = zoom;
        }
      })
    })
  },
  //
  rgbstring:function(rgb) {
    return 'rgb(' + rgb.join(',') + ')';
  }
}
/**
 * STANDARD TAGS
 */
Html.Anchor = {
  is:function(e) {
    return Html.is(e) && e.tagName == 'A';
  },
  create:function(cls, text, onclick, augs) {
    var e = Html.create('a', cls);
    e.href = 'javascript:';
    e.setText(text);
    if (Function.is(onclick)) {
      e.onclick = function() {  // wrapping to prevent unintentional event arg supply
        onclick();
      }
    }
    return e.aug(Html.Anchor._proto).focusable().aug(augs);
  },
  _proto:{
    tooltip:function(text) {
      this.title = String.denull(text);
      return this;
    },
    noFocus:function() {
      this.hideFocus = 'hideFocus';
      return this;
    },
    working:function(value) {
      if (value) {
        Html.Window.registerWorking(this);
        this._isworking = true;
        this._text = this.getText();
        this.nbsp();
        this.addClass('working');
        if (Function.is(value))
          async(value);
      } else if (this._isworking) {
        this._isworking = false;
        this.innerText = String.denull(this._text);
        this.removeClass('working');
        this._working = null;
      }
      return this;
    },
    setText:function(text) {
      this.innerText = text;
      return this;
    },
    getText:function() {
      return this.innerText || this.textContent;
    },
    click:function(fnDefault) {
      if (this.onclick)
        this.onclick();
      else if (this.href && this.href.substr(0, 11) == 'javascript:')
        eval(this.href.substr(11));
      else if (fnDefault)
        fnDefault();
    }
  }
}
Html.AnchorNoFocus = {
  create:function(cls, text, onclick) {
    return Html.Anchor.create(cls, text, onclick).noFocus();
  }
}
Html.Br = {
  create:function() {
    return Html.create('br');
  }
}
Html.Div = {
  is:function(e) {
    return Html.is(e) && e.tagName == 'DIV';
  },
  create:function(cls, augs) {
    var self = Html.create('div', cls);
    return self.aug({
      spin:function(e) {
        if (e) {
          self.clean();
          self.addClass('working-circle');
          if (Function.is(e))
            async(e);
        } else {
          self.removeClass('working-circle');
        }
      },
      scrollable:function(height) {
        if (height)
          self.setHeight(height);
        self.style.overflowY = 'scroll';
        return self;
      }
    }).aug(augs);
  }
}
Html.CenteredDiv = {
  create:function(cls) {
    var self = Html.Div.create(cls).setPosition('relative');
    return self.aug({
      within:function(parent) {
        parent.append(self).setTop(40).setLeft(40);
      }
    })
  }
}
Html.HoverableDiv = {
  create:function(cls) {
    return Html.Div.create(cls).aug({
      init:function() {
        this.addClass('hoverable');
      },
      onmouseover:function() {
        this.addClass('hovering');
      },
      onmouseout:function() {
        this.removeClass('hovering');
      }
    })
  }
}
Html.Form = {
  create:function(action, method) {
    var e = Html.create('form');
    e.action = action;
    e.method = method || 'POST';
    e._submit = (e.submit.bind) ? e.submit.bind(e) : e.submit;
    return e.aug(this._proto);
  },
  _proto:{
    onsubmit:function() {},
    submit:function() {
      if (! this._submitting) {
        this._submitting = true;  // no need to clear later on if page will be refreshed
        this.onsubmit();
        this._submit();
      }
    }
  }
}
Html.RecForm = {
  create:function(url, fields) {
    return Html.Form.create(url).into().extend(function(self) {
      return {
        init:function() {
          self.load(fields);
        },
        load:function(fields) {
          for (var name in fields) {
            var value = fields[name];
            if (String.is(value))
              self.append(Html.InputHidden.create(value, name));
          }
          return self;
        }
      }
    })
  }
}
Html.ServerForm = {
  create:function(server, action, obj) {
    if (! Object.is(obj))
      obj = {'id':obj};
    return Html.RecForm.create('server' + server + '.php', {'action':action,'obj':Json.encode(obj)}).aug({
      oncomplete:function() {}  // TODO: way to fire
    })
  },
  submit:function(server, action, obj, oncomplete) {
    var me = Html.ServerForm.create(server, action, obj);
    if (oncomplete)
      me.bubble('oncomplete', oncomplete);
    me.submit();
  }
}
Html.IFrame = {
  create:function(cls, src, height, width) {
    var self = Html.create('iframe', cls);
    self.src = String.denull(src);
    self.setHeight(height);
    self.setWidth(width);
    return self.aug({
      fullsize:function(vmargin, hmargin, maxh, maxw) {
        var w = Html.Window.getViewportDim();
        return self
          .setWidth(Math.min(w.width - (hmargin || 40), (maxw || 1200)))
          .setHeight(Math.min(w.height - (vmargin || 40), (maxh || 800)));
      },
      nav:function(url) {
        self.src = url;
        var container = self.parentElement;
        container.withWorkingSmall().working(function() {
          pause(1, function() {
            container.working(false);
          })
        })
        return self;
      },
      clean:function() {
        self.src = 'about:blank';
      }
    })
  }
}
Html.UploadForm = {
  /*
   * @arg serverUrl 'upload.php';
   * @arg serverVars {'name':'value',..}
   */
  create:function(container, serverUrl, serverVars, fileCount, maxFileSize) {
    fileCount = fileCount || 1;
    return Html.Form.create(serverUrl).into(container).extend(function(self) {
      return {
        oncomplete:function(data) {},
        //
        init:function() {
          self.enctype = 'multipart/form-data';
          self.encoding = self.enctype;
          for (var i = 0; i < fileCount; i++)
            Html.InputFile.create('file', 'uploadfile[]').into(Html.Tile.create(self, 'mt5'));
          self.inputs = {};
          for (var name in serverVars)
            self.setValue(name, serverVars[name]);
          if (maxFileSize)
            self.inputFileSize = Html.InputHidden.create(null, 'MAX_FILE_SIZE').into(self);
          self.iframe = Html.IFrame.create().set('name', 'uploader').into(self).hide();
          self.target = self.iframe.name;
        },
        setValue:function(name, value) {
          serverVars[name] = value;
          if (self.inputs[name] == null)
            self.inputs[name] = Html.InputHidden.create(null, name).into(self);
        },
        onsubmit:function() {
          for (var name in self.inputs)
            self.inputs[name].setValue(serverVars[name]);
          if (maxFileSize)
            self.inputFileSize.setValue(maxFileSize);
          Html.UploadForm._instance = self;
        }
      }
    })
  },
  callback:function(data) { /*called from inline javascript of server, e.g. serverUpload.php*/
    if (Html.UploadForm._instance) {
      Html.UploadForm._instance.oncomplete(data);
      Html.UploadForm._instance._submitting = null;
      Html.UploadForm._instance = null;
    }
  }
}
Html.Image = {
  create:function(cls, src, height, width, alt) {
    var e = Html.create('img', cls);
    e.src = String.denull(src);
    e.setHeight(height);
    e.setWidth(width);
    e.alt = String.denull(alt);
    return e;
  }
}
Html.H1 = {
  create:function(text) {
    var e = Html.create('h1');
    e.setText(text);
    return e;
  }
}
Html.H2 = {
  create:function(text, cls) {
    var e = Html.create('h2', cls);
    e.setText(text);
    return e;
  }
}
Html.H3 = {
  create:function(text, cls) {
    var e = Html.create('h3', cls);
    e.setText(text);
    return e;
  }
}
Html.Input = {
  create:function(type, cls, value, name) {
    var e = Html.create('input', cls).focusable();
    e.type = type;
    e.value = String.denull(value);
    e.name = String.denull(name);
    return e.aug(this._proto);
  },
  $:function(e) {
    return Html.augment(e, this._proto).focusable();
  },
  _proto:{
    clean:function() {
      this.value = '';
      return this;
    },
    getValue:function() {
      return String.trim(this.value);
    },
    setValue:function(value) {
      this.value = String.denull(value);
      return this;
    }
  },
  _dirtyProto:{
    ondirty:function() {},
    //
    clean:function() {
      this._dirty = false;
      this.value = '';
      return this;
    },
    onchange:function() {
      this._dirty = true;
      this.ondirty();
    },
    isDirty:function() {
      return this._dirty;
    }
  }
}
Html.InputCheck = {
  create:function(cls, value, name, augs) {
    var e = Html.Input.create('checkbox', cls, value, name);
    return this.augment(e);
  },
  $:function(e) {
    return (e._decorated) ? e : this.augment(Html.Input.$(e));
  },
  augment:function(e) {
    return e.aug(Events.ignoredblclick).aug(this._proto);
  },
  _proto:{
    setCheck:function(value) {
      this.checked = (value == true);
      return this;
    },
    isChecked:function() {
      return this.checked;
    }
  }
}
Html.InputText = {
  create:function(cls, value, name, type) {
    var e = Html.Input.create(type || 'text', cls, value, name);
    return e.aug(Events.onkeypresscr).aug(this._proto);
  },
  $:function(id) {
    var e = String.is(id) ? document.getElementById(id) : id;
    var onfocus = e.onfocus;
    e = Html.augment(e, [Html.Input._proto, Events.onkeypresscr, this._proto]);
    if (onfocus)
      e.onfocus = onfocus;
    return e;
  },
  _proto:{
    onkeypresscr:function() {},
    onfocus:function() {
      this.select();
    },
    onblur:function() {  // fixes IE bug
      var temp = this.value;
      this.value = '';
      this.value = temp;
    },
    setFocus:function() {
      var self = this;
      async(function() {
        try {
          if (self.onfocus)
            self.select();
          self.focus();
        } catch (ex) {}
      })
      return this;
    },
    noSelectFocus:function() {
      this.onfocus = null;
      return this;
    },
    setSize:function(i) {
      if (i)
        this.size = i;
      return this;
    },
    setMaxLength:function(i) {
      if (i)
        this.maxLength = i;
      return this;
    },
    isBlank:function() {
      return String.isBlank(this.value);
    }
  }
}
Html.InputTextBlank = {
  create:function(blankText, size, value) {
    return Html.InputText.create('InputTextBlank').setSize(size).aug({
      init:function() {
        this.reset();
        if (value)
          this.setValue(value);
      },
      reset:function() {
        this.addClass('Blank');
        this.value = blankText;
      },
      setValue:function(value) {
        this.value = value;
        if (this.isBlank())
          this.reset();
      },
      isBlank:function() {
        return String.isBlank(this.getValue());
      },
      getValue:function() {
        var value = Html.Input._proto.getValue.call(this);
        return (value == blankText) ? null : value;
      },
      selectAll:function() {
        if (Html.Window.isTablet())
          this.setSelectionRange(0, 999);
        else
          this.select();
      },
      onfocus:function() {
        this.setClass('InputTextBlank');
        this.selectAll();
      },
      onclick:function() {
        if (this.isBlank()) {
          var self = this;
          async(function() {
            self.selectAll();
          })
        }
      },
      onblur:function() {
        Html.InputText._proto.onblur.call(this);
        if (this.isBlank())
          this.reset();
      }
    })
  }
}
/**
 * Autocomplete Input Box
 * self.input = Html.InputAutoComplete.create().into(self).aug({
 *   fetch:function(value, callback) {
 *     Ipcs.ajax().fetchMatches(value, callback);
 *   },
 *   Anchor:IpcAnchor
 * })
 */
Html.InputAutoComplete = {
  create:function() {
    var My = this;
    return Html.InputText.create().extend(function(self) {
      return {
        oncomplete:function(rec, text) {},
        onclear:function() {},
        oncustom:null,  // e.g. function(text){self.new_onclick({'last':text})}
        fetch:null,  // e.g. function(value, callback){Ipcs.ajax().fetchMatches(value, callback)}
        Anchor:AnchorSelect,
        LIMIT:10,
        //
        init:function() {
          self.picker = My.Picker.create(self)
            .bubble('oncomplete', self.picker_oncomplete)
            .bubble('oncustom', self.picker_oncustom)
            .bubble('onblur', self.picker_onblur)
            .reset();
        },
        setValue:function(value) {
          self.value = self._value = String.denull(value);
          return self;
        },
        reset:function() {
          self.clean();
          self.picker.reset();  // close and clear cache
          return self;
        },
        setCustomText:function(text) {  // default 'Add...'
          self.picker.setCustomText(text);
          return self;
        },
        //
        onkeydown:function() {
          switch (event.keyCode) {
            case 13:  // cr
              if (self.picker.showing)
                self.picker.selectFirst();
              return;
            case 38:  // up
            case 40:  // down
              if (self.picker.showing)
                self.picker.setFocus();
              return;
            case 27:  // esc
              if (self.picker.showing) {
                self.picker.close();
                self.cancelBubble();
              }
              return;
          }
        },
        onkeyup:function() {
          self.search();
        },
        onblur:function() {
          if (self.value != self._value) {  // changed value but never got autocompleted
            self.clean();
            self.onclear();
          }
          pause(0.2, function() {
            if (! self.picker.focused)
              self.picker.close();
          })
        },
        search:function() {
          var value = String.trim(self.value);
          if (value != self.searching) {
            self.searching = value;
            self.picker.set(value);
          }
        },
        clean:function() {
          self.setValue(null);
        },
        picker_oncomplete:function(rec, text) {
          self.setValue(text);
          self.searching = text;
          self.closePicker();
          self.oncomplete(rec, text);
        },
        picker_oncustom:function() {
          var text = self.value;
          self.clean();
          self.searching = null;
          self.picker.reset();
          self.closePicker();
          self.oncustom(text);
        },
        picker_onblur:function() {
          pause(0.2, function() {
            if (! self.hasFocus())
              self.picker.close();
          })
        },
        closePicker:function() {
          self.picker.close();
          self.blur();
          self.setFocus();
        },
        cancelBubble:function() {
          Html.Window.cancelBubble();
        }
      }
    })
  },
  Picker:{
    create:function(parent) {
      var self = Html.Window.append(Html.Div.create('AutoComplete')).hide();
      return self.aug({
        oncomplete:function(rec, text) {},
        oncustom:function() {},
        onblur:function() {},
        //
        _customtext:'Add...',
        //
        set:function(value) {
          if (parent.Anchor && parent.Anchor.create) {
            if (value == '')
              self.reset();
            else
              self.search(value);
          }
        },
        setFocus:function() {
          self.firstChild && self.firstChild.setFocus();
        },
        selectFirst:function() {
          self.firstChild && self.firstChild.click();
        },
        setCustomText:function(text) {
          self._customtext = text;
        },
        //
        onmousedown:function() {
          Html.Window.cancelBubble();
        },
        onkeydown:function() {
          switch (event.keyCode) {
            case 8:  // backspace
            case 9:  // tab
            case 27:  // esc
              self.reset();
              parent.cancelBubble();
              parent.setFocus();
              return false;
            case 38:  // up
              parent.setFocus();
              return false;
          }
        },
        search:function(value) {
          if (parent.fetch) {
            self.value = value;
            if (self.cache[value]) {
              async(function() {
                self.load(self.cache[value]);
              })
            } else {
              self.work();
              parent.fetch(value, function(recs) {
                self.cache[value] = recs;
                if (self.value == value) {  // ensure value hasn't been replaced by another request
                  self.load(recs);
                }
              })
            }
          }
        },
        load:function(recs) {
          if (Array.isEmpty(recs) && parent.oncustom == null) {
            self.reset();
          } else {
            self.clean();
            self.loadAnchors(recs);
            self.loadCustomAnchor();
            if (self.value && ! self.showing)
              self.show();
            else
              self.position();
          }
        },
        work:function() {
          self.clean();
          Html.Tile.create(self, 'acwork').setText('Loading...');
          //Html.Span.create(null, 'Loading...').into(self);
          self.position();
          self.working = true;
          return Html._proto.show.apply(self);
        },
        show:function() {
          self.position();
          self.showing = self;
          return Html._proto.show.apply(self);
        },
        close:function() {
          self.hide();
          self.showing = null;
          self.working = null;
          self.focused = null;
          self.value = null;
        },
        reset:function() {
          self.close();
          self.cache = {};
          return self;
        },
        //
        loadAnchors:function(recs) {
          if (recs && recs.length) {
            for (var i = 0; i < recs.length && i < parent.LIMIT; i++) {
              var rec = recs[i];
              var an = parent.Anchor.create(rec);
              an.set('onclick', self.oncomplete.curry(rec, an.innerText))
                .aug(self.anchorProto)
                .into(self);
            }
          }
        },
        loadCustomAnchor:function() {
          if (parent.oncustom)
            Html.AnchorAction.asNew(self._customtext, self.oncustom)
              .aug(self.anchorProto)
              .into(self);
        },
        anchorProto:{
          onfocus:function() {
            self.focused = self;
            this.style.backgroundColor = '#FFFF80';
          },
          onblur:function() {
            self.focused = null;
            this.style.backgroundColor = '';
            pause(0.2, function() {
              if (! self.focused)
                self.onblur();
            })
          },
          onkeydown:function() {
            switch (event.keyCode) {
              case 40:
                if (this.nextSibling) {
                  this.nextSibling.setFocus();
                  parent.cancelBubble();
                }
                break;
              case 38:
                if (this.previousSibling) {
                  this.previousSibling.setFocus();
                  parent.cancelBubble();
                }
                break;
            }
          }
        },
        position:function() {
          var pos = parent.getPosDim();
          var space = Html.Window.getViewportDim().height + Html.Window.getScrollTop() - pos.top;
          if (space < 200) // position above
            self.setTop(pos.top - self.getHeight());
          else
            self.setTop(pos.top + pos.height);
          self.setLeft(pos.left);
          self.setWidth(pos.width);
          self.positioned = self;
        }
      })
    }
  }
}
Html.InputPassword = {
  create:function(cls, value, name) {
    return Html.InputText.create(cls, value, name, 'password');
  }
}
Html.InputRadio = {
  create:function(cls, value, name, augs) {
    var e = Html.Input.create('radio', cls, value, name);
    return e.aug(Events.ignoredblclick).aug({
      setCheck:function(value) {
        e.checked = (value == true);
        return e;
      }
    }).aug(augs);
  }
}
Html.InputButton = {
  create:function(cls, value) {
    return Html.Input.create('button', cls, value);
  }
}
Html.InputHidden = {
  create:function(value, name) {
    return Html.Input.create('hidden', null, value, name);
  }
}
Html.InputFile = {
  create:function(cls, name) {
    var e = Html.Input.create('file', cls, null, name);
    e.style.backgroundColor = '#D4D0C8';
    return e.aug({
      onkeydown:function() {
        this.blur();
      },
      onContextMenu:function() {
        return false;
      }
    })
  }
}
Html.Select = {
  /*
   * @arg {value:text,..} map (optional, to load with options on create)
   * @arg string blank (optional, text for creating first blank value)
   * @arg string cls (optional)
   */
  create:function(map, blank, cls) {
    var e = Html.create('select', cls).focusable().aug(this._proto);
    e.load(map, blank);
    return e;
  },
  $:function(e) {
    return (e._decorated) ? e : _$(e).aug(this._proto);
  },
  _proto:{
    /*
     * @event when setValue invoked; use onchange for user interaction
     */
    onset:function() {},
    //
    load:function(map, blank) {
      this.clean();
      if (blank != null)
        this.addOption('', blank);
      var text;
      for (var value in map)
        this.addOption(value, map[value]);
      return this;
    },
    /*
     * Sort options by text
     */
    sort:function() {
      var a = [];
      for (var i = 0, l = this.options.length; i < l; i++)
        a.push([this.options[i].text, this.options[i].value]);
      a.sort();
      this.clean();
      for (var i = 0, l = a.length; i < l; i++)
        this.addOption(a[i][1], a[i][0]);
      return this;
    },
    /*
     * @arg string value
     * @arg string text (optional, default to value)
     */
    addOption:function(value, text) {
      var o = Html.create('option');
      this.options.add(o);
      o.value = String.denull(value);
      o.text = String.denull(text || o.value);
    },
    setValue:function(value) {
      var cur = this.value;
      for (var i = 0, l = this.options.length; i < l; i++)
        if (this.options[i].value == value)
          break;
      var opt = this.options[(i < l) ? i : 0];
      if (opt.value != this.value) {
        opt.selected = true;
        this.onset();
      }
      return this;
    },
    setIndex:function(i) {
      this.selectedIndex = i;
    },
    getValue:function() {
      return this.value;
    },
    getText:function() {
      return this.options[this.selectedIndex].text;
    },
    setFocus:function() {
      var self = this;
      async(function() {
        self.focus();
      })
    }
  }
}
Html.TextArea = {
  create:function(cls, value, name, augs) {
    var e = Html.create('textarea', cls).focusable();
    e.value = String.denull(value);
    e.name = String.denull(name);
    return e.aug(Html.Input._proto)
      .aug(Events.onkeypresscr)
      .aug({
        onkeypresscr:function() {},
        //
        setRows:function(i) {
          e.rows = i;
          return e;
        }})
      .aug(augs);
  }
}
Html.AutoSizeTextArea = {
  create:function(cls, value, name, augs) {
    var e = Html.TextArea.create(cls, value, name);
    return e.aug({
      onkeydown:autosize,
      onkeyup:autosize,
      onkeypress:autosize,
      autosize:function() {
        //self.setHeight(self.getScrollHeight());  // this doesn't work in chrome, use jquery expanding textarea
      }
    }).aug(augs);
  }
}
Html.DirtyTextArea = {
  create:function(cls, value, name) {
    return Html.TextArea.create(cls, value, name).aug(Html.Input._dirtyProto);
  }
}
Html.TinyMce = { /*RichTextBox*/
  //
  create:function(container, rows/*=4*/, id/*=null*/) {
    id = id || 'rtb__' + this._nextId++;
    var self = Html.TextArea.create('rtb').setId(id).setRows(rows || 4).invisible().into(container);
    return self.aug({
      initMce:function(opts/*=null*/) {
        opts = opts || this.DefaultOpts; 
        var o = opts(id);
        o.setup = function(ed) {
          var ta = ed.getElement();
          ta._mce = ed;
          ta.mce_onsetup(ed);
        }
        tinyMCE.init(o);
        tinyMCE.onAddEditor.add(function(mgr, ed) {
          var ta = ed.getElement();
          ta.mce_onload(ed);
          loop(function(exit) {
            if (ed.isHidden() == false) {
              ta._visible = 1;
              ta.mce_onshow(ed);
              exit(); 
            }
          })
        })
      },
      setHtml:function(html) {
        html = self.elimlfs(html);
        if (self._visible)
          self._mce.setContent(html);
        else
          self.value = html;
        return self;
      },
      getHtml:function() {
        if (self._mce)
          return self.elimlfs(self._mce.getContent());
        else
          return self.value;
      },
      setFocus:function() {
        self.getVisibleMce(function(mce) {
          mce.focus();
        })
        return self;
      },
      //
      mce_onsetup:function(mce) {}, /*prior to rendering, e.g. addButton*/
      addButton:function(buttonId, tooltip, imgSrc, onclick) {
        self._mce.addButton(buttonId, {
          title:tooltip,
          image:imgSrc,
          onclick:onclick
        })
        return self;
      },
      mce_onload:function(mce) {}, /*when editor added*/
      mce_onshow:function(mce) {}, /*when editor rendered, e.g. setTooltip*/
      setTooltip:function(buttonId/*e.g.'bold'*/, text) {
        document.getElementById(self._mce.controlManager.get(buttonId).id).title = text;
        return self;
      },
      //
      getVisibleMce:function(/*fn(mce)*/callback) { 
        if (self._mce && self._visible)
          callback(self._mce);
        else
          pause(0.1, self.getVisibleMce.curry(callback));
      },
      elimlfs:function(s) {
        return (s) ? s.replace(/(\r\n|\n|\r)/gm,"") : '';    
      }
    })
  },
  create_asMin:function(container, rows, id) {
    return this.create(container, rows, id, this.MinOpts); 
  },
  //
  MinOpts:function(id) {
    return {
      /*General options*/
      mode : "exact",
      theme : "advanced",
      plugins : "autolink,lists,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
      elements : id,
      /*Theme options*/
      theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,outdent,indent,blockquote,|,forecolor,backcolor,|,cut,copy,paste,pastetext,pasteword,|,search,replace,|,spellchecker,|,undo,redo",
      theme_advanced_buttons2 : "",
      theme_advanced_buttons3 : "",
      theme_advanced_buttons4 : "",
      theme_advanced_toolbar_location : "top",
      theme_advanced_toolbar_align : "left",
      //theme_advanced_statusbar_location : "bottom",
      //theme_advanced_resizing : true,
      /*Skin options*/
      skin : "o2k7",
      skin_variant : "silver"
    }
  },
  DefaultOpts:function(id) {
    var opts = Html.TinyMce.MinOpts(id);
    opts.theme_advanced_buttons1 += ",|,fullscreen";
    opts.theme_advanced_buttons2 = "tablecontrols,|,link,unlink,anchor,image,hr,charmap,emotions,media,sub,sup,|,cleanup,help,code";
    return opts;
  },
  _nextId:1
}
Html.Label = {
  create:function(cls, text, augs) {
    var e = Html.create('label', cls);
    e.setText(text);
    return e.aug(augs);
  }
}
Html.Span = {
  create:function(cls, text, augs) {
    var e = Html.create('span', cls);
    e.setText(text);
    return e.aug(augs);
  }
}
Html.Table = {
  is:function(e) {
    return Html.is(e) && e.tagName == 'TABLE';
  },
  create:function(container, cls) {
    return Html.create('table', cls).into(container);
  },
  $:function(e) {
    return Html.augment(e, this._proto);
  },
  _proto:{
    tbody:function() {
      if (this._tbody == null)
        this._tbody = Html.getTag('tbody', this, true);
      return this._tbody;
    },
    thead:function() {
      if (this._thead == null)
        this._thead = Html.getTag('thead', this, true);
      return this._thead;
    }
  },
  _protoBody:{
    setClass:function(cls) {
      this.parentElement.setClass(cls);
    },
    tr:function(cls) {
      this._tr = Html.create('tr', cls).into(this);
      return Html.Table._protoBody._trAppender(this._tr);
    },
    trOff:function() {
      this._trOff = ! this._trOff;
      return this.tr(this._trOff ? 'off' : '');
    },
    trFixed:function() {
      var appender = this.tr('fixed head');
      Html.Window.registerFixedRow(this._tr);
      return appender;
    },
    trToggle:function(keep) {
      this._trToggle = (keep) ? this._trToggle : ! this._trToggle;
      return this.tr(this._trToggle ? 'row1' : 'row2');
    },
    _trAppender:function(tr) {
      return {
        td:function(e, cls) {
          this._cell = Html.create('td', cls).into(tr);
          this._appendOrText(e);
          return this;
        },
        th:function(e, cls) {
          this._cell = Html.create('th', cls).into(tr);
          this._appendOrText(e);
          return this;
        },
        html:function(s) {
          this._cell.html(s);
          return this;
        },
        w:function(i) {
          this._cell.setWidth(i);
          return this;
        },
        rowspan:function(i) {
          this._cell.rowSpan = i;
          return this;
        },
        colspan:function(i) {
          this._cell.colSpan = i;
          return this;
        },
        _tr:function() {
          return tr;
        },
        _appendOrText:function(e) {
          if (e)
            if (Html.is(e))
              this._cell.append(e);
            else
              this._cell.setText(e);
        }
      }
    }
  }
}
Html.Tr = {
  create:function(cls) {
    return Html.create('tr', cls);
  }
}
Html.Td = {
  create:function(cls) {
    return Html.create('td', cls);
  }
}
Html.Ul = {
  create:function(cls) {
    return Html.create('ul', cls).aug(Html.Ul._proto);
  },
  _proto:{
    li:function(cls) {
      return this._li = Html.create('li', cls).into(this);
    },
    add:function(e, cls) {
      this.li(cls).add(e);
      return this;
    }
  }
}
Html.Page = {
  extend:function() {
    var self = Html.decorate(window);
    self.aug({
      onpageload:function() {},
      //
      init:function() {
        self.body = Html.decorate(document.body);
      },
      /*
       * @arg string page 'page-name.php'
       * @arg map|string args e.g. {name:value,..} or single string value to supply to 'id'
       */
      go:function(page, args) {
        setTimeout(function(){window.location.href = String.url(page, args)},1);
      },
      getScrollTop:function() {
        if (typeof pageYOffset != 'undefined')
          return pageYOffset;
        else
          return document.documentElement.scrollTop;
      },
      getScrollLeft:function() {
        if (typeof pageXOffset != 'undefined')
          return pageXOffset;
        else
          return document.documentElement.scrollLeft;
      },
      vscroll:function(i) {
        self.scroll(0, i || 0);
      }
    }).extend.apply(self, arguments);
    self.onload = function() {
      self.onpageload();
    }
    self.onresize = function() {
      self.onpageresize();
    }
    return self;
  }
}
/**
 * CLICKTATE TAGS
 */
Html.InputDate = {
  create:function(text) {
    return Html.Span.create().extend(function(self) {
      var di;
      return {
        onset:function(value) {},
        //
        init:function() {
          di = new DateInput(text, self, function(value) {
            self.onset(value)
          })
          this.focusable(di.textbox);
        },
        setValue:function(text) {
          di.setText(text);
        },
        getValue:function() {
          return di.getText();
        },
        setFocus:function() {
          di.focus();
        }
      }
    })
  }
}
Html.InputDateTime = {
  create:function(text) {
    return Html.Span.create().extend(function(self) {
      var di;
      return {
        onset:function(value) {},
        //
        init:function() {
          di = new DateTimeInput(text, self, function(value) {
            self.onset(value)
          })
          this.focusable(di.textbox);
        },
        setValue:function(text) {
          di.setText(text);
        },
        getValue:function() {
          return di.getText();
        },
        setFocus:function() {
          di.focus();
        }
      }
    })
  }    
}
Html.AnchorDate = {
  create:function(text) {
    return Html.Span.create('AnchorDate').extend(function(self) {
      return {
        onset:function(value) {},
        //
        init:function() {
          self.Date = Html.Anchor.create('action calend', null, self.pop).into(self);
          self.focusable(self.Date);
          self.setText(text);
        },
        getText:function() {  // '20-Nov-2012'
          return String.nullify(self.dv.toString());
        },
        setText:function(text) {
          self.dv = self.getDv(text);
          self.Date.setText(self.dv.toString());
        },
        //
        getDv:function(text) {
          var dv = (text) ? new DateValue(text) : DateValue.now();
          dv.toString_full = function() {
            var text = dv.toString_verbose();
            return dv.getDowName() + ', ' + text.substr(0, text.length - 6);
          }
          return dv;
        },
        //
        pop:function() {
          Pop.Calendar.show(self.dv.toString(), function(text) {
            if (text) {
              self.setText(text);
              self.onset(self.getText());
            }
          })
        }
      }
    })
  }
}
/**
 * Anchor AnchorAction
 */
Html.AnchorAction = {
  create:function(cls, text, onclick) {
    return Html.Anchor.create('action ' + cls, text, onclick).aug({
      setClass:function(cls) {
        this.className = 'action ' + cls;
        return this;
      }
    });
  },
  asEdit:function(text, onclick) {
    return this.create('edit2', text, onclick);
  },
  asSelect:function(text, onclick) {
    return this.create('choice', text, onclick);
  },
  asSelect2:function(text, onclick) {
    return this.create('choice2', text, onclick);
  },
  asSelectGray:function(text, onclick) {
    return this.create('choice0', text, onclick);
  },
  asSelectGreen:function(text, onclick) {
    return this.create('editgreen', text, onclick);
  },
  asOpen:function(text, onclick) {
    return this.create('open', text, onclick);
  },
  asNew:function(text, onclick) {
    return this.create('add', text, onclick);
  },
  asPractice:function(text, onclick) {
    return this.create('house', text, onclick);
  },
  asPrint:function(text, onclick) {
    return this.create('print', text, onclick);
  },
  asView:function(text, onclick) {
    return this.create('view2', text, onclick);
  },
  asWarning:function(text, onclick) {
    return this.create('warning', text, onclick);
  },
  asUpdate:function(text, onclick) {
    return this.create('update', text, onclick);
  },
  asDelete:function(text, onclick) {
    return this.create('dele', text, onclick);
  },
  asGrid:function(text, onclick) {
    return this.create('grid', text, onclick);
  },
  asCustom:function(text, onclick) {
    return this.create('configure', text, onclick);
  },
  asNote:function(text, onclick) {
    return this.create('page', text, onclick);
  },
  asAppt:function(text, onclick) {
    return this.create('appt', text, onclick);
  },
  asOrder:function(text, onclick) {
    return this.create('track', text, onclick);
  },
  asMsg:function(text, onclick) {
    return this.create('msg', text, onclick);
  },
  asAttach:function(text, onclick) {
    return this.create('attachment3', text, onclick);
  },
  asImage:function(text, onclick) {
    return this.create('image', text, onclick);
  },
  asGraph:function(text, onclick) {
    return this.create('graph', text, onclick);
  },
  asXml:function(text, onclick) {
    return this.create('xmlsm', text, onclick);
  },
  asList:function(text, onclick) {
    return this.create('list', text, onclick);
  },
  asKey:function(text, onclick) {
    return this.create('key2', text, onclick);
  }
}
/**
 * AnchorAction AnchorRec
 */
Html.AnchorRec = {
  /*
   * @arg string cls
   * @arg string text
   * @arg Rec rec
   * @arg fn(Rec) onclick
   */
  create:function(cls, text, rec, onclick) {
    return this.from(Html.AnchorAction.create(cls, text), rec, onclick);
  },
  /*
   * @arg AnchorACtion a
   * @arg Rec rec
   * @arg fn(Rec) onclick
   */
  from:function(a, rec, onclick) {
    if (onclick)
      a.bubble('onclick', onclick.curry(rec));
    a.rec = rec;
    return a;
  },
  //
  asSelect:function(text, rec, onclick) {
    return this.create('choice', text, rec, onclick);
  },
  asEdit:function(text, rec, onclick) {
    return this.create('edit2', text, rec, onclick);
  }
}
AnchorAppt = {
  create:function(rec, onclick) {
    if (rec.schedEventId)
      return Html.AnchorRec.create('disab', rec._label, rec, onclick);
    else
      return Html.AnchorRec.create('appt', rec._label, rec, onclick);
  }
}
AnchorSelect = {
  create:function(rec, onclick) {
    return Html.AnchorRec.asSelect(rec.name, rec, onclick);
  }
}
AnchorClient = {
  create:function(rec, onclick) {
    var cls = AnchorClient.getClass(rec);
    return Html.AnchorRec.create(cls, rec.name, rec, onclick);
  },
  getClass:function(rec) {
    return rec.sex == 'M' ? 'umale' : 'ufemale';
  }
}
AnchorClient_Facesheet = {
  create:function(rec) {
    return AnchorClient.create(rec).set('href', Page.url(Page.PAGE_FACESHEET, rec.clientId));
  }
}
AnchorClient_FacesheetPop = {
  create:function(rec, callback) {
    return AnchorClient.create(rec, function(rec) {
      Page.popFace(rec.clientId, callback);
    })
  }
}
AnchorPortalUser = {
  create:function(rec, withClientName) {
    return Html.AnchorAction.create().extend(function(self) {
      return {
        init:function() {
          if (rec)
            self.load(rec);
        },
        load:function(rec) {  // PortalUser
          self.setText((withClientName) ? rec.uid + ' (' + rec.Client.name + ')' : rec.uid);
          if (rec.isSuspended())
            self.setClass('susp');
          else if (! rec.isActivated())
            self.setClass('keyg');
          else if (rec.isPremium())
            self.setClass('key2');
          else
            self.setClass('keyg3');
        }
      }
    })
  },
  create_withClientName:function(rec) {
    return AnchorPortalUser.create(rec, true);
  }
}
AnchorUser = {
  create:function(rec, onclick) {
    var cls = AnchorUser.getClass(rec);
    return Html.AnchorRec.create(cls, rec.name, rec, onclick);
  },
  getClass:function(rec) {
    switch (rec.roleType) {
      case C_UserRole.TYPE_TRIAL:
      case C_UserRole.TYPE_PROVIDER_PRIMARY:
      case C_UserRole.TYPE_PROVIDER:
        return 'acct1';
      case C_UserRole.TYPE_CLINICAL:
        return 'acct2';
      default:
        return 'acct3';
    }
  }
}
AnchorDocStub = {
  create:function(rec, onclick) {
    var m = 'as' + [null, 'Note','Msg','Appt','Order','Image','Graph','Xml','Note','List','Note'][rec.type];
    var a = Html.AnchorRec.from(Html.AnchorAction[m](rec.name), rec, onclick);
    if (rec.Unreviewed)
      a.addClass('red');
    return a;
  }
}
AnchorTrackItem = {
  create:function(rec, onclick) {
    var self = Html.AnchorRec.from(Html.AnchorAction.asOrder(rec.trackDesc), rec, onclick);
    if (rec.priority == C_TrackItem.PRIORITY_STAT)
      self.addClass('red');
    return self;
  }
}
AnchorProc = {
  create:function(rec, onclick) {
    return Html.AnchorRec.from(Html.AnchorAction.asGraph(rec.Ipc.name), rec, onclick);
  }
}
/**
 * Anchor AnchorSubmit
 */
Html.AnchorSubmit = {
  create:function(cls, text, onclick) {
    var e = Html.Anchor.create(cls, text, onclick);
    return e.aug(this._proto);
  },
  $:function(e) {
    return Html.augment(e, this._proto);
  },
  _proto:{
    attach:function(frm) {
      var self = this;
      self.onclick = function() {
        self.submit(frm);
        return false;
      }
      frm.onsubmit = function() {
        self.working();
      }
    },
    //
    submit:function(frm) {
      if (! this.isWorking()) {
        this.working();
        frm.submit();
      }
    },
    isWorking:function() {
      return this._working;
    },
    working:function() {
      this._working = true;
      this.innerText = '';
      this.style.borderColor = '#c0c0c0';
      this.style.color = '#f8f8f8';
      this.style.backgroundColor = '#f8f8f8';
      this.style.backgroundImage = 'url(img/icons/working6.gif)';
      this.style.backgroundPositionX = 'center';
      this.style.backgroundPositionY = 'center';
      this.style.backgroundRepeat = 'no-repeat';
      return this;
    }
  }
}
/**
 * Span LabelCheck
 *   Input check
 *   Label label
 */
Html.LabelCheck = {
  /*
   * @arg string text
   * @arg mixed value (optional, default '1')
   */
  create:function(text, value) {
    var self = Html.Span.create('LabelCheck').setUnselectable();
    return self.aug({
      /*
       * @events
       */
      onclick_check:function(lcheck) {},
      ondraw:function(checked) {},
      //
      init:function() {
        self.check = Html.LabelCheck.Check.create(self, value || '1').aug({
          onclick:function(e) {
            self.lc_onclick();
            Html.Window.cancelBubble(e);
            self.draw();
          }
        })
        self.focusable(self.check);
        self.label = Html.LabelCheck.Label.create(self, text).aug({
          onclick:function(e) {
            self.setChecked(! self.isChecked());
            if (self.check)
              self.lc_onclick();
            Html.Window.cancelBubble(e);
            self.draw();
          }
        })
        self.draw();
      },
      draw:function() {
        if (self.label)
          self.label.setClass((self.check.checked) ? 'lcheck-on' : 'lcheck');
        self.ondraw(self.check.checked);
      },
      isChecked:function() {
        return self.check.checked;
      },
      setChecked:function(b) {
        self.check.checked = b;
        self.draw();
        return self;
      },
      getValue:function() {
        return value;
      },
      getText:function() {
        return text;
      },
      //
      lc_onclick:function() {
        self.onclick_check(self);
        if (self.onchange)
          self.onchange();
      }
    });
  },
  Check:{
    create:function(container, value) {
      return Html.InputCheck.create('lci', value).into(container);
    }
  },
  Label:{
    create:function(container, text) {
      return Html.Label.create('lcheck', text).aug(Events.ignoredblclick).setUnselectable().into(container);
    }
  }
}
Html.DivCheck = {
  create:function(text, value) {
    var My = this;
    return Html.Div.create('DivCheck').extend(function(self) {
      return {
        oncheck:function(checked) {},
        //
        init:function() {
          self.lcheck = My.LabelCheck.create(text, value).into(self).bubble('onclick_check', self.lcheck_onclick);
          self.content = Html.Tile.create(self, 'DivCheckContent').hide();
          self.focusable(self.lcheck);
        },
        add:function(e) {
          return self.lcheck.add(e);
        },
        setText:function(s) {
          self.lcheck.setText(s);
          return self;
        },
        setText2:function(s) {
          self.lcheck.setText2(s);
          return self;
        },
        setContent:function(html) {
          self.content.show().html(html);
        },
        isChecked:function() {
          return self.lcheck.isChecked();
        },
        setChecked:function(b) {
          self.lcheck.setChecked(b);
          self.setCheckedClass();
        },
        getValue:function() {
          return self.lcheck.getValue();
        },
        getText:function() {
          return self.lcheck.getText();
        },
        //
        lcheck_onclick:function() {
          self.setCheckedClass();
          self.oncheck(self.lcheck.isChecked());
        },
        onclick:function() {
          self.setChecked(! self.isChecked());
          self.lcheck_onclick();
        },
        setCheckedClass:function() {
          self.addClassIf('DivChecked', self.lcheck.isChecked());
        }
      }
    })
  },
  LabelCheck:{
    create:function(text, value) {
      return Html.LabelCheck.create(text, value).extend(function(self) {
        return {
          init:function() {
            var t = Html.Table.create(self).tbody();
            t.tr().th(self.check).w(17).td(self.label).w('100%');
            t.tr().th().w(17).td(self.content = Html.Div.create('LabelCheckContent')).w('100%');
            t.tr().th().w(17).td(self.content2 = Html.Div.create('LabelCheckContent')).w('100%');
          },
          add:function(e) {
            return self.content.add(e);
          },
          setText:function(s) {
            self.content.setText(s);
            return self;
          },
          setText2:function(s) {
            self.content2.setText(s);
            return self;
          }
        }
      })
    }
  }
}
/**
 * Span LabelChecks
 *   LabelCheck[] lchecks
 */
Html.LabelChecks = {
  /*
   * @arg {value:text,..} map
   * @arg int cols (optional, to spread into columns; default 1)
   */
  create:function(map, cols) {
    var self = Html.Span.create().setUnselectable();
    return self.aug({
      /*
       * @events
       */
      onclick_check:function(lcheck) {},
      //
      init:function() {
        self.lchecks = [];
        Map.eachByValue(map, function(value, key) {
          self.lchecks.push(Html.LabelCheck.create(value, key).bubble('onclick_check', self));
        })
        if (cols)
          Html.TableVertical.create(self).vertload(cols, self.lchecks);
        else
          Html.TableCol.create(self, cols, self.lchecks);
        if (self.lchecks.length)
          self.focusable(self.lchecks[0]);
      },
      /*
       * @return [LabelCheck,..]
       */
      getChecked:function() {
        var checked = [];
        Array.forEach(self.lchecks, function(lcheck) {
          if (lcheck.isChecked())
            checked.push(lcheck);
        });
        return checked;
      },
      /*
       * @arg [value,..] values
       */
      setChecked:function(values) {
        self._origValues = values;
        values = Array.from(values);
        Array.forEach(self.lchecks, function(lcheck) {
          lcheck.setChecked(values.has(lcheck.getValue()));
        });
      },
      isDirty:function() {
        if (self._origValues) {
          for (var i = 0, j = self.lchecks.length; i < j; i++) {
            var lcheck = self.lchecks[i];
            if (self._origValues.has(lcheck.getValue())) {
              if (! lcheck.isChecked())
                return true;
            } else {
              if (lcheck.isChecked())
                return true;
            }
          }
          return false;
        }
      },
      /*
       * @return [value,..]
       */
      getCheckedValues:function() {
        var checked = [];
        Array.forEach(self.getChecked(), function(lcheck){checked.push(lcheck.getValue())});
        return checked;
      },
      /*
       * @return [text,..]
       */
      getCheckedTexts:function() {
        var checked = [];
        Array.forEach(self.getChecked(), function(lcheck){checked.push(lcheck.getText())});
        return checked;
      }
    });
  }
}
/**
 * Span LabelRadios
 *   {value:LabelRadio,..} lradios
 */
Html.LabelRadios = {
  /*
   * @arg {value:text,..} map
   * @arg int cols (optional, to spread into columns; default map length)
   */
  create:function(map, cols) {
    var self = Html.Span.create('LabelRadios').setUnselectable();
    var name = 'LR' + String.rnd();
    return self.aug({
      onselect:function(value) {},
      //
      init:function() {
        self.lradios = {};
        self.value = null;
        self.map = map;
        var value, len = 0;
        for (value in map) {
          self.lradios[value] = Html.LabelRadios.LabelRadio.create(name, map[value], value)
            .bubble('onclick_radio', self.radio_onclick);
          if (self.value == null) {
            self._default = value;
            self.value = self._default; 
          }
          len++;
        }
        self.array = Array.from(self.lradios);
        Html.TableCol.create(self, cols || len, self.array);
        self.setValue(self.value);
        if (self.lradios.length)
          self.focusable(self.lradios[0]);
      },
      setValue:function(value) {
        self.value = value || self._default;
        self.lradios[self.value].setChecked(true);
        self.setColors();
      },
      getValue:function(value) {
        return self.value;
      },
      //
      radio_onclick:function(lradio) {
        self.setColors();
        self.value = lradio.getValue();
        self.onselect(self.value);
      },
      setColors:function() {
        self.array.each(function(lr) {
          lr.radio.setColor();
        })
      }
    });
  },
  LabelRadio:{
    create:function(name, text, value) {
      var _proto = this;
      var self = Html.Span.create().setUnselectable();
      return self.aug({
        onclick_radio:function(lradio) {},
        //
        init:function() {
          self.radio = _proto.Radio.create(self, value, name).aug({
            setColor:function() {
              if (self.label)
                self.label.setClass((self.radio.checked) ? 'lcheck-on' : 'lcheck');
            },
            onclick:function() {
              self.onclick_radio(self);
            }
          })
          self.label = _proto.Label.create(self, text).aug({
            onclick:function() {
              self.setChecked(true);
              if (self.radio)
                self.onclick_radio(self);
            }
          })
          self.radio.setColor();
          self.focusable(self.radio);
        },
        isChecked:function() {
          return self.radio.checked;
        },
        setChecked:function(b) {
          self.radio.checked = b;
          self.radio.setColor();
        },
        getValue:function() {
          return value;
        },
        getText:function() {
          return text;
        }
      });
    },
    Radio:{
      create:function(container, value, name) {
        var self = Html.InputRadio.create(null, value, name).into(container);
        return self;
      }
    },
    Label:{
      create:function(container, text) {
        return Html.Label.create('lcheck', text).aug(Events.ignoredblclick).setUnselectable().into(container);
      }
    }
  }
}
/**
 * EntryForm Wrapper
 */
Html.EntryForm = {
  create:function(container, firstLabelCls, augs) {
    var ul = Html.Ul.create().into(container);
    ul.ef = new EntryForm(ul, firstLabelCls);
    return Object.augment(ul.ef, augs);
  }
}
/**
 * Ul Entry
 **/
Html.UlEntry = {
  /*
   * self.form = Html.UlEntry.create(self, function(ef) {
   *   ef.line().lbl('Label1').textbox('field1');
   *   ef.line('mt5').lbl('Label2').ro('field2');
   * })
   *  - or -
   * self.form = Html.UlEntry.create(self).extend(function(self) {
   *   return {
   *     init:function() {
   *       self.line().lbl( etc.
   *     }
   *   }
   * }
   */
  create:function(container, builder) {
    return Html.Ul.create().into(container).extend(function(self) {
      return {
        onbeforeload:function(rec) {},  // may modify rec or return new one
        onapply:function() {},  // to modify self.rec
        onload:function() {},
        onchange:function() {},
        withOnCr:function(oncr) {
          self.aug(Events.onkeypresscr);
          if (oncr)
            self.onkeypresscr = oncr;
          return self;
        },
        //
        init:function() {
          self.ef = Object.augment(new EntryForm(self));
          self.ef.setOnChangeAny(self.ef_onchange);
          if (builder)
            builder(self.ef);
        },
        build:function(cls) {
          if (cls)
            self.ef.setFirstLabelClass(cls);
          return self.ef.appender;
        },
        line:function(cls) {
          return self.ef.line(cls);
        },
        $:function(id) {
          return self.ef.$(id);
        },
        load:function(rec) {
          self.ef.clearRecordChanged();
          var changed = self.onbeforeload.apply(self, arguments);
          if (changed)
            rec = changed;
          self.rec = rec;
          self.ef.setRecord(rec);
          if (self.draw)
            self.draw();
          self.onload();
          return self;
        },
        isDirty:function() {
          return self.ef.isRecordChanged();
        },
        getRecord:function() {
          if (self.rec)
            return self.applyTo();
          else
            return self.ef.getRecord();
        },
        getField:function(fid) {
          return self.ef.getField(fid);
        },
        getValue:function(fid) {
          return self.ef.getValue(fid);
        },
        setValue:function(fid, value) {
          if (self.ef.setValue(fid, value) && self.draw)  // returns true if changed
            self.draw();
        },
        applyTo:function() {
          self.onapply();
          return self.ef.applyTo(self.rec);
        },
        focus:function(fid) {
          self.ef.focus(fid);
        },
        reset:function() {
          self.rec = null;
          self.ef.reset();
          return self;
        },
        //
        ef_onchange:function() {
          if (self.ef.isRecordChanged())
            self.onchange();
        }
      }
    })
  },
  create_asClickable:function(container, builder) {
    var self = this.create(container).addClass('clickform');
    self.aug({
      onformclick:function(fid) {},
      init:function() {
        self._es = [];
      },
      lock:function() {
        self._locked = true;
        self._es.each(function(e) {
          e.lock();
        })
      }
    })
    self.ef.appender.ro = function(fid, cls) {
      self.ef.appendReadOnly(fid, cls);
      self.aug({
        onclick:function() {
          if (! self._locked)
            self.onformclick();
        }
      })
      self.ef._e.aug({
        onmouseover:function() {
          this.style.border = '1px solid blue';
        },
        onmouseout:function() {
          this.style.border = '';
        },
        onclick:function() {
          self.onformclick(fid);
          Html.Window.cancelBubble();
        },
        lock:function() {
          this.onmouseover = null;
          this.onmouseout = null;
          this.onclick = null;
        }
      })
      self._es.push(self.ef._e);
      return self.ef.appender;
    }
    if (builder)
      builder(self.ef);
    return self;
  }
}
Html.UlEntries = function(array) {
  return array.delegate('load', 'applyTo', 'getRecord', 'isDirty', '$', 'getValue').aug({
    focus:function(fid) {
      if (fid)
        array.walk('focus', fid);
      else
        array.length && array[0].focus();
    }
  })
}
/**
 * Ul Filter
 */
Html.UlFilter = {
  create:function() {
    var My = this;
    return Html.Ul.create('filter').extend(My, function(self) {
      return {
        onselect:function(key) {},
        //
        /*
         * @arg {'key':'text',..} items
         * @arg string selectedkey (optional)
         */
        load:function(items, selectedkey) {
          self.reset();
          Map.eachByValue(items, function(value, key) {
            if (selectedkey == null)
              selectedkey = key;
            self.add(key, value);
          })
          self.select(selectedkey);
        },
        reset:function() {
          self.clean();
          self.selected = null;
          self.items = {};
        },
        add:function(key, text) {
          var a = Html.Anchor.create();
          a.setText(text).set('key', key).bubble('onclick', self.item_onclick.curry(a));
          self.li().add(self.items[key] = a);
          return a;
        },
        select:function(key) {
          if (self.selected) {
            if (self.selected.key == key)
              return;
            self.selected.removeClass('fsel');
          }
          self.selected = self.items[key].addClass('fsel');
          self.onselect(key);
          return self.selected;
        },
        //
        item_onclick:function(a) {
          self.select(a.key);
        }
      }
    })
  }
}
/**
 * Tile NavBar
 *   LinkBox prevbox
 *   Tile onbox
 *   LinkBox nextbox
 */
Html.NavBar = {
  create:function(container) {
    var My = this;
    return Html.Tile.create(container, 'NavBar').extend(function(self) {
      return {
        onselect:function(rec) {},
        ondraw_load:function(rec, header, content) {
          // @abstract
        },
        //
        init:function() {
          self.prevbox = My.LinkBox.asPrev().bubble('onnav', self.draw);
          self.onbox = My.OnBox.create();
          self.nextbox = My.LinkBox.asNext().bubble('onnav', self.draw);
          Html.Table.create(self, 'w100').tbody().tr().td(self.prevbox).w('15%').td(self.onbox).w('70%').td(self.nextbox).w('15%');
        },
        /*
         * @arg Rec[] recs
         * @arg Rec rec
         * @arg proto anchor e.g. AnchorDocStub
         * @arg bool descending (optional)
         */
        load:function(recs, rec, anchor, descending) {
          self.navs = Array.navify(recs, descending);
          self.anchor = anchor;
          self.draw(rec);
        },
        //
        draw:function(rec) {
          self.prevbox.load(rec._prev, self.anchor);
          self.nextbox.load(rec._next, self.anchor);
          self.draw_onbox(rec);
          self.onselect(rec);
        },
        draw_onbox:function(rec) {
          self.onbox.header.clean();
          self.onbox.content.clean();
          self.ondraw_load(rec, self.onbox.header, self.onbox.content);
        }
      }
    })
  },
  OnBox:{
    create:function() {
      return Html.Div.create('onbox').extend(function(self) {
        return {
          init:function() {
            self.header = Html.H2.create().into(self);
            self.content = Html.Tile.create(self, 'content');
          }
        }
      })
    }
  },
  LinkBox:{
    create:function(cls) {
      return Html.Div.create(cls).aug(Events.ignoredblclick).extend(function(self) {
        return {
          onnav:function(rec) {},
          //
          load:function(rec, anchor) {
            self.clean();
            self.rec = rec;
            self.anchor = (rec) ? anchor.create(self.rec, self.onnav).addClass('linkbox').noFocus().into(self) : null;
            self.addClassIf('empty', rec == null);
          },
          //
          onclick:function() {
            if (self.rec)
              self.onnav(self.rec);
          },
          onmouseover:function() {
            if (self.anchor)
              self.addClass('hover');
          },
          onmouseout:function() {
            self.removeClass('hover');
          }
        }
      })
    },
    asPrev:function() {
      return this.create('linkbox prevbox');
    },
    asNext:function() {
      return this.create('linkbox nextbox');
    }
  }
}
/**
 * Div TemplateUi
 *   TemplateUi tui
 */
Html.TemplateUi = {
  create_asEntry:function(container, fs, fidPrefix) {
    return Html.TemplateUi.create(container, fs, null, fidPrefix);
  },
  create_asParagraph:function(container, fs) {
    QPopCalendar.setFormatSentence();
    var tuic = Html.Tile.create(container, 'tuic');
    return Html.TemplateUi.create(tuic, fs, TemplateUi.FORMAT_PARAGRAPH);
  },
  /* @arg <e> container
   * @arg Facesheet fs
   * @arg int format
   * @arg string fidPrefix (optional, e.g. 'imm.' to math rec prop 'dateGiven' to dsync 'imm.dateGiven') */
  create:function(container, fs, format, fidPrefix) {
    format = format || TemplateUi.FORMAT_ENTRY_FORM_WIDE;
    var cls = format == TemplateUi.FORMAT_ENTRY_FORM_WIDE ? '' : 'tui';
    var self = Html.Div.create(cls).into(container);
    return self.aug({
      onload:function() {},
      onchange:function(q) {},
      //
      init:function() {
        self.tui = new TemplateUi(self, fs, null, null, format, null, self.tui_onchange);
        self.tui.working = function(){};
        container.withWorkingSmall();
      },
      fetch:function(/*int/fn(pid)*/pid, /*opt fn()*/onload) {
        if (self.isLoaded())
          self.reset();
        if (onload)
          self.onload = onload;
        self.working(function() {
          if (Function.is(pid))
            pid(self._getPar);
          else
            self._getPar(pid);
        })
        return self;
      },
      working:function(e) {
        container.working(e);
      },
      scrollable:function(h) {
        container.scrollable(h);
        return self;
      },
      load:function(/*Par/fn*/par) {
        if (Function.is(par))
          par(self._loadPar);
        else
          self._loadPar(par);
      },
      isLoaded:function() {
        return self.par;
      },
      reset:function() {
        self.par = null;
        self._dirty = null;
        self.clean();
        self.tui.reset();
        return self;
      },
      setRecord:function(rec) {
        self.tui.setRecord(rec, fidPrefix);
        return self;
      },
      setField:function(fid, value) {
        self.tui.setField(fid, value, fidPrefix);
        return self;
      },
      isDirty:function() {
        return self._dirty;
      },
      getRecord:function() { /*{fid:'seltext',..}*/
        return self.tui.getRecord(fidPrefix);
      },
      getQuestion:function(quid) { /*Question*/
        return self.tui.qs[quid];
      },
      getHtml:function() { /*string*/
        return self.tui.out();
      },
      pop:function(/*Question*/q) {
        TemplateUi._pop(q.a, true);
      },
      //
      _getPar:function(pid) {
        self.pid = pid;
        self.tui.getParInfo(pid, self.tui_onload);
      },
      _loadPar:function(par) {
        TemplateUi._receivePar(self.tui, par);
        self.tui_onload(par);
      },
      tui_onload:function(par) {
        self.par = par;
        container.working(false);
        if (self.onload)
          self.onload();
      },
      tui_onchange:function(q) {
        self._dirty = true;
        self.onchange(q);
      }
    })
  }
}
/**
 * Div Tile
 */
Html.Tile = {
  create:function(container, cls) {
    return Html.Div.create(cls).into(container);
  }
}
/**
 * Table2Col SplitTile
 *   TH left
 *   TD right
 */
Html.SplitTile = {
  create:function(container) {
    return Html.Table2Col.create(container).setClass('split').extend(function(self) {
      return {
        init:function() {
          self.left.addClass('split');
          self.right.addClass('split');
        },
        showBoth:function() {
          self.left.show();
          self.right.show();
        },
        showLeft:function() {
          self.left.show();
          self.right.hide();
        },
        showRight:function() {
          self.right.show();
          self.left.hide();
        }
      }
    })
  }
}
/**
 * Div ScrollDiv
 */
Html.ScrollDiv = {
  create:function(container, cls) {
    var self = Html.Div.create(cls || 'fstab').into(container);
    return self.aug({
      clean:function() {
        Html._proto.clean.call(self);
        self.scrollTop = 0;
        return self;
      }
    })
  }
}
Html.TableRow = {
  create:function(container, cls) {
    var self = Html.Table.create(container, cls);
    return self.aug({
      init:function() {
        self.tr = self.tbody().tr();
      },
      tds:function(/*e,..*/) {
        var es = Array.prototype.slice.call(arguments);
        es.each(function(e) {
          self.tr.td(e);
        })
        return self;
      }
    })
  }
}
/**
 * Table Table2Col
 *   Th left
 *   Tr right
 */
Html.Table2Col = {
  /*
   * @arg col1, col2 (optional contents of table)
   */
  create:function(container, col1, col2) {
    var self = Html.Table.create(container, 't2c');
    return self.aug({
      init:function() {
        self.tr = self.tbody().tr();
        self.left = self.tr.th(col1)._cell;
        self.right = self.tr.td(col2)._cell;
      }
    });
  }
}
/**
 * Table Table2Col
 *   Th left
 *   Tr right
 */
Html.Table2ColHead = {
  /*
   * @arg col1, col2 (optional contents of table)
   */
  create:function(container, col1, col2) {
    var self = Html.Table.create(container, 'h');
    return self.aug({
      init:function() {
        self.tr = self.tbody().tr();
        self.left = self.tr.th(col1)._cell;
        self.right = self.tr.td(col2)._cell;
      }
    });
  }
}
/**
 * Table TableCol
 */
Html.TableCol = {
  /*
   * @arg <e> container
   * @arg int|obj[] cols number of columns|items to populate table at create (optional)
   * @ex create(self, [tile1, tile2])
   * @ex create(self, 3, checks)
   */
  create:function(container, cols, items) {
    if (cols == null) {
      cols = 1;
    } else if (Array.is(cols)) {
      items = cols;
      cols = items.length;
    }
    var table = Html.Table.create(container);
    var tbody = table.tbody();
    tbody.aug({
      init:function() {
        tbody.reset(cols);
      },
      reset:function(cols) {
        tbody.clean();
        tbody.ct = 0;
        tbody.cols = cols;
      },
      add:function(e) {
        if (tbody.ct % tbody.cols == 0)
          tbody.trapp = tbody.tr();
        tbody.ct++;
        tbody.trapp.td(e);
      }
    });
    if (items)
      Array.forEach(items, function(item) {
        tbody.add(item);
      });
    return tbody;
  }
}
Html.TableVertical = {
  create:function(container, cls) {
    var self = Html.Table.create(container, cls);
    var tbody = self.tbody();
    return self.aug({
      vertload:function(cols, items) {
        tbody.clean();
        var pct = Math.ceil(100 / cols) + '%';
        var len = items.length;
        var rows = Math.ceil(len / cols);
        var c, i, trapp;
        for (var r = 0; r < rows; r++) {
          trapp = tbody.tr();
          for (c = 0; c < cols; c++) {
            i = r + c * rows;
            if (i < len)
              trapp.td(items[i]).w(pct);
            else
              trapp.td().w(pct);
          }
        }
      }
    })
  }
}
/**
 * TableCol Table1Row
 */
Html.Table1Row = {
  /*
   * @arg <e> container
   * @arg obj[] items
   */
  create:function(container, items) {
    cols = items.length;
    return Html.TableCol.create(container, cols, items);
  }
}
/**
 * Table ScrollTable
 *   Div wrapper
 */
Html.ScrollTable = {
  create:function(container, tableCls, wrapperCls) {
    var My = this;
    var div = Html.Div.create(wrapperCls || 'fstab').withWorkingSmall().into(container);
    return Html.Table.create(div, tableCls || 'fsy').extend(My, function(self) {
      return {
        init:function() {
          self.wrapper = div;
        },
        withWorkingLarge:function() {
          self.wrapper.withWorkingLarge();
          return self;
        },
        withMore:function() {
          div.aug(Events.onscrollbottom).bubble('onscrollbottom', self);
          return self.aug({
            onmore:function() {},
            //
            setMore:function(b) {
              if (b) 
                self.More = self.More || Html.Tile.create(div, 'More').withWorkingSmall();
              else
                self.More = self.More && self.More.remove();
            },
            onscrollbottom:function() {
              if (self.More)
                async(self.onmore);
            }
          })
        },
        addHeader:function() {
          self.header = My.Header.create(div);
          self.aug({
            syncHeaderWidth:function() {
              self.header.setWidth(self.getWidth());
              var _tr = self.tbody()._tr;
              if (self._tr == null || _tr)
                self._tr = _tr;
              if (self._tr)
                self.header.setColWidths_byTr(self._tr);
            }
          })
          return self.header.tr();
        },
        //
        working:function(e) {
          self.wrapper.working(e);
        },
        setHeight:function(i) {
          if (self.header)
            i -= self.header.getHeight();
          if (div.hasClass('noscroll'))
            self.style.height = (i == null) ? 'auto' : String.px(i);
          else
            div.setHeight(i);
          if (self.header)
            self.syncHeaderWidth();
          return self;
        },
        onresize:function() {
          if (self.header)
            self.syncHeaderWidth();
        },
        scrollTo:function(e, padding) {
          Html.Animator.scrollTo(self.wrapper, e, padding);
        },
        hide:function() {
          self.wrapper.hide();
          return self;
        },
        show:function() {
          self.wrapper.show();
          return self;
        },
        visible:function() {
          self.wrapper.visible();
          return self;
        },
        invisible:function() {
          self.wrapper.invisible();
          return self;
        }
      }
    })
  },
  Header:{
    create:function(div) {
      var container = Html.Div.create('fixedhead').before(div);
      return Html.Table.create(container, 'fixed').extend(function(self) {
        return {
          tr:function() {
            return self.tbody().tr();
          },
          ths:function() {
            return self.tbody()._tr.children;
          },
          setColWidths:function(widths) {
            var ths = self.ths();
            for (var i = 0; i < widths.length; i++) 
              ths[i].setWidth(widths[i] - 10);
          },
          setColWidths_byTr:function(tr) {
            var widths = [];
            Array.each(tr.children, function(td) {
              widths.push(td.offsetWidth);
            })
            self.setColWidths(widths);
          }
        }
      })
    }
  }
}
/**
 * CmdBarAppender CmdBar
 **/
Html.CmdBar = {
  create:function(container, context) {
    var cb = new CmdBar(container, null, context);
    var wrapper = _$(cb.div);
    return Object.augment(cb.appender()).aug({
      wrapper:wrapper,
      into:function(e) {
        e.appendChild(wrapper);
        return this;
      },
      hide:function() {
        wrapper.hide();
        return this;
      },
      visible:function() {
        wrapper.visible();
        return this;
      },
      invisible:function() {
        wrapper.invisible();
        return this;
      },
      show:function() {
        wrapper.show();
        return this;
      },
      showIf:function(e) {
        wrapper.showIf(e);
        return this;
      },
      addClass:function(cls) {
        wrapper.addClass(cls);
        return this;
      }
    })
  },
  asOkCancel:function(/*e*/container, /*fn*/onok, /*fn*/oncancel) {
    return Html.CmdBar.create(container).ok(onok).cancel(oncancel);
  },
  asSaveCancel:function(container, context, saveCaption) {
    return Html.CmdBar.create(container).save(context.save_onclick, saveCaption).cancel(context.cancel_onclick);
  },
  asSaveDelCancel:function(container, context, saveCaption) {
    return Html.CmdBar.create(container).save(context.save_onclick, saveCaption).del(context.del_onclick).cancel(context.cancel_onclick);
  }
}
/**
 * Tiles CmdBars
 * @example
 *   Html.CmdBars.create(self.content, [
 *     self.cb1 = Html.CmdBar.create(self.content)
 *       .button('Reset Account', self.reset_onclick, 'button-edit')
 *       .button('Suspend Account', self.suspend_onclick, 'delete')
 *       .exit(self.close),
 *     self.cb2 = Html.CmdBar.create(self.content)
 *       .button('Print Login Card', self.print_onclick, 'print-note')
 *       .button('Edit Activation Fields', self.edit_onclick, 'button-edit')
 *       .exit(self.close)]);
 */
Html.CmdBars = {
  create:function(container, cbs) {
    return Html.Tiles.create(container, cbs);
  }
}
/**
 * Table2Col SplitCmdBar
 * @example
 *   self.cb = Html.SplitCmdBar.create(self)
 *     .ok(self.ok_onclick)
 *     .split()
 *     .ok(self.ok2_onclick)
 *     .end();
 */
Html.SplitCmdBar = {
  create:function(container, noAlign) {
    var table = Html.Table2Col.create(container).setClass('splitcmd');
    var cbLeft = Html.CmdBar.create(table.left);
    var cbRight = Html.CmdBar.create(table.right);
    cbRight.aug({
      end:function() {
        return table.aug({
          left:cbLeft,
          right:cbRight
        })
      },
      left:cbLeft,
      right:cbRight,
      table:table
    })
    return cbLeft.aug({
      split:function() {
        return cbRight;
      },
      left:cbLeft,
      right:cbRight,
      table:table
    });
  }
}
/**
 * ScrollTable TableLoader
 *   TableLoader loader()
 */
Html.TableLoader = {
  /*
   * var self = Html.TableLoader.create(container);
   * return self.aug({
   *  init:function() {
   *    self.setHeight(500);
   *    self.thead().trFixed().th('Date').w('10%').th('Type').w('10%').th('Name').w('30%').th('').w('50%');
   *    self.setTopFilter();
   *  },
   *  filter:function(rec) {
   *    return {'Type':rec._type};
   *  },
   *  rowBreaks:function(rec) {
   *    return [rec.date];
   *  },
   *  rowOffset:function(rec) {
   *    return rec.date;
   *  },
   *  add:function(rec, tr) {
   *    tr.td(rec.date, 'bold nw').td(rec._type).select(AnchorDocStub).td(rec.desc);
   *  }
   * });
   */
  create:function(container, tableCls, wrapperCls, offCls) {
    var My = this;
    var tile = Html.Tile.create(container);
    var table = Html.ScrollTable.create(tile, tableCls, wrapperCls);
    if (offCls == null)
      offCls = 'off';
    table.tl = new TableLoader(table.tbody(), offCls, table.wrapper);
    return table.extend(My, function(self) {
      return {
        onload:function(recs) {},
        ondraw:function() {},
        ondrawrow:function(rec) {},
        onselect:function(rec, label) {},  // label optional, to identify specific field clicked of record
        onfilterset:function(value) {},
        //
        init:function() {
          self.thead();
        },
        /*
         * @abstract (optional)
         * @return string e.g. return rec.ipc
         */
        rowKey:function(rec) {},
        /*
         * @abstract (optional)
         * @return string[] e.g. return [rec.date, rec.sessionId]
         */
        rowBreaks:function(rec) {},
        /*
         * @abstract (optional)
         * @return string e.g. return rec.cat
         */
        rowOffset:function(rec) {
          var s = self.rowBreaks(rec);
          if (s)
            return s.join();
        },
        /*
         * @abstract (optional)
         * @arg Rec rec filter, will be empty {} on reset
         * @return object e.g. return {'Category':C_Ipc.CATS[rec.cat]}
         */
        filter:function(rec) {},
        /*
         * @abstract (must override if using argless load)
         * @arg fn(Rec[]) callback_recs
         */
        fetch:function(callback_recs) {
          callback_recs(self.recs);
        },
        /*
         * @abstract (required if using load)
         * @arg Rec rec
         * @arg TrAppender tr to build record row e.g. tr.select(rec, rec.name).td(rec.desc)
         */
        add:function(rec, tr) {},
        //
        /*
         * @arg Rec[] recs (optional; if null, must implement fetch)
         * @arg fn(rec, tr) add (optional)
         */
        load:function(recs, add) {
          if (add)
            self.add = add;
          self.reset();
          if (recs)
            self.working(function() {
              self._load(recs);
            })
          else
            self.working(function() {
              self.fetch(self._load);
            })
        },
        _load:function(recs) {
          self.reset();
          self.recs = recs;
          self.onload(recs);
          self.draw();
          self.loaded = true;
        },
        draw:function() {
          self.working(function() {
            if (self.recs)
              for (var i = 0; i < self.recs.length; i++)
                self.drawRow(self.recs[i]);
            if (self.header) 
              self.syncHeaderWidth();
            self.ondraw();
            if (self.filterBar)
              self.filterBar.set();
            self.working(false);
          })
        },
        drawRow:function(rec) {
          self.ondrawrow(rec);
          self.add(rec, self.tbody().tr(rec));
          if (self.onadd)
            self.onadd(rec, _$(self._tr));
        },
        /*
         * @return bool
         */
        isLoaded:function() {
          return self.loaded;
        },
        /*
         * Refresh table after record add/update/delete
         * @arg Rec/int e (Rec if add/update, int ID if delete)
         * @requires fetch() and rowKey()
         */
        update:function(e) {
          var rec = (Object.is(e)) ? e : null;
          var key = rec ? self.rowKey(rec) : e;
          var tr = self.tl.getRowByKey(key);
          if (tr) {
            Html.Animator.highlight(tr);
            self.working(function() {
              self.recs = null;
              self.fetch(function(recs) {
                self.recs = recs;
                if (self.tl.getRowByKey(key)) {
                  if (rec) {
                    self.working(false);
                    self.drawRow(rec);
                    self.tl.reapply();
                    Html.Animator.fade(tr);
                  } else {
                    self.working(false);
                    Html.Animator.fade(tr, function() {
                      self.tl.removeTrs([key]);
                    });
                  }
                }
              });
            });
          } else {
            self.load();
          }
        },
        /*
         * Get array of visible recs (as result of filter)
         * @return Rec[]
         */
        getVisibleRecs:function() {
          var recs = [];
          var trs = self.tl.trs();
          if (trs.length)
            Array.forEach(trs, function(tr) {
              if (! tr.isHidden())
                recs.push(tr.rec);
            })
          return recs;
        },
        /*
         * @arg string key
         * @return Rec
         */
        findKey:function(key) {
          for (var i = 0; i < self.recs.length; i++)
            if (self.rowKey(self.recs[i]) == key)
              return self.recs[i];
        },
        thead:function() {
          var thead = Html.Table._proto.thead.call(self);
          return thead.aug({
            /*
             * @return TrAppender of header
             */
            tr:function(cls) {
              var tr = Html.Table._protoBody.tr.call(thead, cls);
              self.tl.setTrHead(tr._tr());
              return tr;
            }
          });
        },
        tbody:function() {
          var tbody = Html.Table._proto.tbody.call(self);
          if (! tbody._auged) {
            tbody._auged = tbody.aug({
              /*
               * @arg Rec rec (optional, if supplied remaining args pulled from abstract row methods at top)
               * @args optional (see TableLoader) and may be specified independently below (e.g. breaks())
               * @return TrAppender of body
               */
              tr:function(rec, offset, breaks, filter, key, index) {
                if (rec) {
                  offset = self.rowOffset(rec);
                  breaks = self.rowBreaks(rec);
                  filter = self.filter(rec);
                  key = self.rowKey(rec);
                }
                self.tl.createTr(offset, breaks, filter, key, index);
                self._tr = self.tl.tr;
                if (rec)
                  self._tr.rec = rec;
                return this._trAppender(self._tr);
              },
              _trAppender:function(tr) {
                return {
                  /*
                   * Create a <td>
                   * @arg <e>|string e contents of cell
                   * @arg string cls (optional)
                   * @return TrAppender
                   */
                  td:function(e, cls) {
                    if (String.is(e))
                      self.tl.createTd(cls, e);
                    else
                      self.tl.createTdAppend(cls, e);
                    this._cell = self.tl.td;
                    return this;
                  },
                  colspan:function(i) {
                    this._cell.colSpan = i;
                    return this;
                  },
                  w:function(i) {
                    Html._proto.setWidth.call(this._cell, i);
                    return this;
                  },
                  html:function(s) {
                    if (s)
                      this._cell.innerHTML = s;
                    return this;
                  },
                  /*
                   * Create a checkbox cell
                   * @arg string|Rec value (optional; by default check.value set to row key and check.rec set to row rec)
                   * @arg bool checked (optional)
                   * @return TrAppender
                   */
                  check:function(value, checked, onclick) {
                    var rec;
                    if (Object.is(value)) {
                      rec = value;
                      value = '';
                    } else {
                      rec = self._tr.rec;
                      value = value || self.tl.tr.key;
                    }
                    var e = Html.InputCheck.create().setValue(value);
                    self.tl.createTdAppend('check', e);
                    self.tl.tr.check = e;
                    e.aug({
                      rec:rec,
                      tr:self.tl.tr,
                      onclick:function() {
                        e.tr.style.backgroundColor = (e.checked) ? '#FFFF40' : '';
                        if (onclick)
                          onclick(e.checked);
                      }
                    });
                    if (checked)
                      e.click();
                    return this;
                  },
                  /*
                   * Create a selector
                   * @arg <a>|proto|string e e.g. AnchorTrackItem
                   * @arg Rec|string rec (or ID) to supply to onselect event (optional, uses rec assigned to row if supplied)
                   * @arg fn(Rec) onclick (optional, self.onselect by default)
                   * @arg string cls (optional, for TD)
                   * @arg string label (optional, to supply as second arg to onselect)
                   * @return TrAppender
                   */
                  select:function(e, rec, onclick, cls, label) {
                    rec = rec || self._tr.rec;
                    //cls = cls || 'nw';
                    onclick = onclick || self.onselect;
                    if (String.is(e))
                      e = Html.AnchorRec.asSelect(e, rec, onclick);
                    else if (Html.Anchor.is(e))
                      e.bubble('onclick', onclick.curry(rec, label));
                    else
                      e = e.create(rec, onclick);
                    this.td(e, cls);
                    if (! self._tr.selector)
                      self._tr.selector = e;
                    return this;
                  },
                  /*
                   * Create a selector as edit icon; still fires onselect
                   */
                  edit:function(text) {
                    return this.select(Html.AnchorRec.asEdit(text, self._tr.rec));
                  },
                  /*
                   * Create a selector as simple anchor; still fires onselect
                   * @arg string text
                   * @arg string label (optional; to id field clicked onselect; if not supplied, will be derived from corresponding THEAD cell text)
                   * @arg string cls (optional)
                   */
                  a:function(text, label, cls) {
                    if (! label) {
                      var th = self.tl.getTrHead().children[self._tr.children.length];
                      label = th && th.innerText;
                    }
                    return this.select(Html.Anchor.create(cls).html(text), null, null, null, label);
                  }
                }
              }
            });
          }
          return tbody;
        },
        reset:function() {
          self.recs = null;
          self.tl.reset();
          self.tl.defineFilter(self.filter({}));
          if (self.filterBar)
            self.filterBar.reset();
          self.loaded = false;
        },
        /*
         * @return [value,..] of checked rows
         */
        getCheckValues:function() {
          var values = [];
          Array.forEach(self.tl.trs(), function(tr) {
            if (tr.check && tr.check.checked)
              values.push(tr.check.value);
          });
          return values;
        },
        /*
         * @return [value,..] of checked rows
         */
        getCheckRecs:function() {
          var values = [];
          Array.forEach(self.tl.trs(), function(tr) {
            if (tr.check && tr.check.checked)
              values.push(tr.check.rec);
          });
          return values;
        },
        /*
         * @arg int[] values of rows to check
         */
        setChecks:function(values) {
          self.resetChecks();
          Array.forEach(self.tl.trs(), function(tr) {
            if (tr.check && values.has(tr.check.value))
              tr.check.click();
          });
        },
        resetChecks:function() {
          Array.forEach(self.tl.trs(), function(tr) {
            if (tr.check && tr.check.checked)
              tr.check.click();
          });
        },
        /*
         * Assign rec filter values to a top filter bar (created upon first use)
         * @arg Rec rec (optional, leave null to reset)
         */
        setTopFilter:function(rec) {
          if (self.filterBar == null)
            self.addTopFilter();
          self.filterBar.set(rec);
          return self.filterBar;
        },
        setSideFilter:function(rec) {
          if (self.filterBar == null)
            self.addSideFilter();
          self.filterBar.set(rec);
          return self.filterBar;
        },
        /*
         * Add a top filter
         * @example
         * init:function() {
         *   self.setTopFilter().setAllLabel('Show All');
         * }
         */
        addTopFilter:function() {
          self.filterBar = Html.TableLoader.FilterBar.create_asTop(self).bubble('onset', self, 'onfilterset');
          return self.filterBar;
        },
        addSideFilter:function() {
          self.filterBar = Html.TableLoader.FilterBar.create_asSide(self).bubble('onset', self, 'onfilterset');
          return self.filterBar;
        },
        /*
         * @return string 'Document'
         */
        getFilterValue:function() {
          return self.filterBar.getValue();
        },
        //
        noWorking:function() {
          self.working = function(e) {
            if (Function.is(e))
              async(e);
          }
          return self;
        },
        hide:function() {
          tile.hide();
          return self;
        },
        show:function() {
          tile.show();
          return self;
        },
        showIf:function(e) {
          tile.showIf(e);
          return self;
        },
        /*
         * @return TableLoader
         */
        loader:function() {
          return self.tl;
        },
        into:function(e) {
          tile.into(e);
          return self;
        }
      }
    })
  },
  FilterBar:{
    create:function(table, cls) {
      var div = Html.Div.create(cls).before(table.wrapper);
      var self = Html.Ul.create(cls).into(div);
      return self.aug({
        onset:function(value) {},
        onload:function() {},
        //
        init:function() {
          table.tl.filterOnset = self.filter_onset;
          self.wrapper = div;
        },
        reset:function() {
          self.currentValue = null;
          self.onset(null);
          self.clean();
        },
        hideAllLabel:function() {
          //table.tl.filterHideAll = true;  this doesn't work
          return self;
        },
        /*
         * @arg string text
         */
        setAllLabel:function(text) {
          table.tl.filterAllLabel = text;
          return self;
        },
        /*
         * @return string 'Document'
         */
        getValue:function() {
          return table.tl.getTopFilterValue();
        },
        getFilterRecord:function() {
          return table.tl.getFilter();
        },
        getTopFilterCount:function() {
          return table.tl.getTopFilterCount();
        },
        getHeight:function() {
          return self.wrapper.getHeight();
        },
        showIf:function(b) {
          return div.showIf(b);
        },
        //
        filter_onset:function(filter) {
          self.currentValue = table.tl.getTopFilterValue();
          self.onset(self.currentValue);
        }
      })
    },
    create_asTop:function(table) {
      return this.create(table, 'topfilter').extend(function(self) {
        return {
          set:function(rec) {
            table.tl.loadFilterTopbar(self, table.filter(rec || {}));
          }
        }
      })
    },
    create_asSide:function(table) {
      return this.create(table, 'filter').extend(function(self) {
        return {
          set:function(rec) {
            table.tl.loadFilterSidebar(self, table.filter(rec || {}));
          }
        }
      })
    }
  },
  //
  create_asBlue:function(container) {
    return this.create(container, 'fsb');
  }
}
/**
 * Tile Panels
 *   Panel[] panels
 */
Html.Panels = {
  /*
   * @arg {'name':contentProto,..}
   */
  create:function(container, panels) {
    var My = this;
    return Html.Tile.create(container, 'Panels').extend(this, function(self) {
      return {
        onselect:function(panel) {},
        //
        init:function() {
          var panel, proto;
          self.Panels = {};
          for (var name in panels) {
            proto = panels[name];
            panel = My.Panel.create(self, name, proto).bubble('onselect', self.panel_onselect);
            self.Panels[name] = panel;
          }
        },
        /*
         * @arg fn(My.Panel) callback
         */
        forEach:function(callback) {
          for (var name in self.Panels)
            callback(self.Panels[name]);
        },
        reset:function() {
          if (self.selected)
            self.selected.reset();
        },
        setMaxHeight:function(i) {
          self.forEach(function(p) {
            p.setMaxHeight(i);
          })
        },
        //
        panel_onselect:function(panel) {
          if (panel.name != self.selname) {
            self.selname = panel.name;
            self.selected = panel.content;
            self.forEach(function(p) {
              p.hideIf(p.name != panel.name);
            })
          }
        }
      }
    })
  },
  Panel:{
    create:function(container, name, contentProto) {
      return Html.Tile.create(container, 'Panel').hide().extend(function(self) {
        return {
          onselect:function(panel) {},
          //
          init:function() {
            self.name = name;
          },
          select:function() {
            self.show();
            if (self.content == null)
              self.createContent();
            self.onselect(self);
            return self.content;
          },
          createContent:function() {
            self.content = contentProto.create(self);
            self.setContentMaxHeight();
          },
          setMaxHeight:function(i) {
            if (self.maxHeight != i) {
              self.maxHeight = i;
              self.setContentMaxHeight();
            }
          },
          setContentMaxHeight:function() {
            if (self.maxHeight)
              if (self.content && self.content.setMaxHeight)
                self.content.setMaxHeight(self.maxHeight);
          }
        }
      })
    }
  }
}
/**
 * Tile Tiles
 */
Html.Tiles = {
  /*
   * self.tiles = Html.Tiles.create(self, [
   *   self.recips = My.RecipsForm.create(self, recips);
   *   self.portal = My.PortalTile.create(self)]);
   * self.recips.select();
   *
   * Tile events:
   *   ontileselect
   */
  create:function(container, tiles) {
    var self = Html.Tile.create(container, 'Tiles');
    return self.aug({
      init:function() {
        self._count = 0;
        self.tiles = [];
        if (tiles)
          tiles.forEach(self.add);
      },
      reset:function() {
        if (self.tiles.length)
          self.select(self.tiles[0]);
      },
      add:function(tile) {
        self.tiles.append(tile.into(self).showIf(self._count == 0).aug({
          _tileIndex:self._count++,
          select:function() {
            return self.select(tile);
          },
          isSelected:function() {
            return tile._tileIndex == self._selIndex;
          }
        }))
        return tile;
      },
      select:function(tile) {
        self.tiles.forEach(function(t, i) {
          t.showIf(i == tile._tileIndex);
        })
        if (tile.ontileselect)
          tile.ontileselect();
        self._selIndex = tile._tileIndex;
        return self._selected = tile;
      },
      selectByIndex:function(i) {
        return self.select(self.tiles[i]);
      },
      selected:function() {
        return self._selected;
      },
      setMaxHeight:function(i) {
        self.tiles.forEach(function(t) {
          if (t.setMaxHeight) {
            t.show();
            t.setMaxHeight(i);
            t.hide();
          }
        })
        if (self._selected)
          self.selected().show();
      }
    })
  }
}
/**
 * Div TabPanels
 *   Div bar
 *     TabBar tb
 *   Div panels
 */
Html.TabPanels = {
  /*
   * @arg string[] panelTitles ['Documentation History',..]
   * @arg string[] tabCaptions ['Documents',..] (optional, will use titles if omitted)
   */
  create:function(container, panelTitles, tabCaptions) {
    var self = Html.Div.create().into(container.clean());
    return self.aug({
      /*
       * @events
       */
      panel_onselect:function(panel) {},
      //
      init:function() {
        self.bar = Html.TabPanels.Bar.create(self, {
          onselect:function(panel) {
            self.panel_onselect(panel);
          }
        });
        self.panels = Html.TabPanels.Panels.create(self, panelTitles.length);
        self.bar.load(panelTitles, tabCaptions);
      },
      /*
       * @arg int index
       */
      select:function(index) {
        self.bar.select(index);
      },
      /*
       * @return Panel
       */
      selected:function() {
        return self.panels.get(self.bar.tb.getSelIndex());
      },
      /*
       * @return int
       */
      getSelIndex:function() {
        return self.bar.tb.getSelIndex();
      }
    });
  },
  Bar:{
    create:function(container, augs) {
      var self = Html.Tile.create(container, 'tabbar');
      return self.aug({
        onselect:function(panel) {},
        //
        load:function(titles, captions) {
          self.tb = new TabBar(container, titles, captions);
          self.tb._onSelectCallback = function(index, panel) {
            Html.Window.flickerFixedRows();
            self.onselect(panel);
          };
        },
        select:function(index) {
          self.tb.select(index);
        }
      }).aug(augs);
    }
  },
  Panels:{
    create:function(container, count) {
      var self = Html.Tile.create(container, 'tabpanels');
      self.panels = [];
      for (var i = 0; i < count; i++)
        self.panels.push(Html.TabPanels.Panel.create(self, i));
      return self.aug({
        get:function(index) {
          return self.panels[index];
        },
        getAll:function() {
          return self.panels;
        },
        /*
         * @arg fn(panel) callback
         */
        forEach:function(callback) {
          Array.forEach(self.panels, callback);
        }
      });
    }
  },
  Panel:{
    create:function(container, index) {
      var self = Html.Tile.create(container, 'tabpanel');
      return self.aug({
        index:index
      });
    }
  }
}
/**
 * Div Pop
 *   Div cap
 *     Div caption
 *     Anchor ctlbox
 *   Div content
 */
Html.Pop = {
  /*
   * AbcPop = {
   *   pop:function(x, y) {
   *     return Html.Pop.singleton_pop.apply(AbcPop, arguments);
   *   },
   *   create:function() {
   *     var self = Html.Pop.create('Caption');
   *     return self.aug({
   *       init:function() {
   *         //..
   *       },
   *       pop:function(x, y) {
   *         //..
   *         self.show(z);
   *         return self;
   *       }
   *     })
   *   }
   * }
   * AbcPop.pop('x', 'y').aug({
   *   onshow:function(z) {
   *     //..
   *   }
   * })
   */
  create:function(caption, width) {
    var id = 'pop' + String.rnd();
    var pop = Html.Div.create('pop', {
      init:function() {
        this.cap = Html.Pop.Cap.create(this, caption);
        this.content = Html.Pop.Content.create(this);
      }
    })
    Html.Window.append(pop.setWidth(width));
    return pop.extend(Html.Pop.Proto);
  },
  /*
   * From existing <html> source
   */
  from:function(id) {
    var pop = _$(id);
    if (pop.cap == null) {
      pop.cap = pop.firstElementChild || pop.firstChild;
      pop.content = pop.lastElementChild || pop.lastChild;
      pop.extend(Html.Pop.Proto);
    }
    return pop;
  },
  /*
   * To create a pop method directly off object, e.g.
   *   pop:function(cid) {
   *     return Html.Pop.singleton_pop.apply(this, arguments);
   *   }
   */
  singleton_pop:function() {
    return Html.Pop.singleton.call(this).pop.apply(this._singleton, arguments);
  },
  singleton:function() {
    if (this._singleton == null)
      this._singleton = this.create.apply(this, arguments);
    return this._singleton;
  },
  //_augment:function(self) {
  //  return self.aug(this._proto);
  //},
  Proto:function(self) {
    return {
      //
      POP_POS:1,  // Pop.POS_CENTER
      //
      onpop:function() {},   // prior to show; passes all pop() args
      onshow:function() {},  // after show; passes all pop() args
      onclose:function() {},
      onreposition:function() {},
      onpopactivate:function() {},  // when receives "focus" after closing overlay pop
      //
      setMaxHeight:function(i) {},
      /*
       * @return self
       */
      pop:function() {
        self.reset();
        self.onpop.apply(self, arguments);
        self.show.apply(self, arguments);
        self.setMaxHeight(Html.Window.getViewportDim().height - 80);
        return self;
      },
      reset:function() {},
      /*
       * @abstract
       * @arg CmdBar cb
       */
      buildCmd:function(cb) {},
      //
      /*
       * @return int viewport height
       */
      fullscreen:function(maxw, maxh) {
        var w = Html.Window.getViewportDim();
        self.setWidth(Math.min(w.width - 40, maxw || 1000));
        return Math.min(w.height - 150, maxh || 1000);
      },
      show:function() {
        Pop.show(self, null, self.POP_POS);
        self.onshow.apply(self, arguments);
        if (self.onkeydown)
          Html.attach(document, 'keydown', self.onkeydown);
        return self;
      },
      showPosCursor:function() {
        Pop.showPosCursor(self);
        self.onshow.apply(self, arguments);
        if (self.onkeydown)
          Html.attach(document, 'keydown', self.onkeydown);
        return self;
      },
      close:function() {
        if (Pop._pop == self) {
          Pop.close();
          if (self.onkeydown)
            Html.detach(document, 'keydown', self.onkeydown);
        }
      },
      reposition:function() {
        Pop.reposition();
        return self;
      },
      setCaption:function(text) {
        self.cap.caption.setText(text);
      },
      bubble:function(events, to, toEvent) {  // "one-time" bubbles for global pops
        events = Array.from(events);
        for (var i = 0; i < events.length; i++) {
          var event = events[i];
          var was = self[event];
          Html._proto.bubble.call(self, event, to, toEvent);
          var to = self[event];
          self[event] = function() {
            //if (was)  // self is a bad idea for global pops! will execute last assigned event handler if never invoked on a prior pop
            //  was.apply(self, arguments);
            to.apply(self, arguments);
            self[event] = was;
          }
        }
        return self;
      },
      bubble_keep:function(event, to, toEvent) {
        return Html._proto.bubble.call(self, event, to, toEvent);
      },
      withErrorBox:function() {
        self.aug({
          errorbox:Html.Pop.ErrorBox.create(self),
          onerror:function(e) {
            self.working(false);
            self.errorbox.showException(e);
          },
          pop:self.pop.bind(self).append(function() {
            self.errorbox.hide();
          })
        })
        return self;
      },
      withFrame:function(caption, anchor) {
        self.frame = Html.Pop.Frame.create(self.content, caption, anchor);
        return self;
      },
      withFrameExit:function(caption, anchor) {
        self.withFrame(caption, anchor);
        Html.CmdBar.create(self.content).exit(self.close);
        return self;
      },
      setOnkeypresscr:function(onkeypresscr) {
        self.aug(Events.onkeypresscr);
        self.onkeypresscr = onkeypresscr;
      }
    }
  },
  Cap:{
    create:function(container, text, onClose) {
      var cap = Html.Div.create('pop-cap').into(container);
      cap.caption = Html.Div.create().into(cap).setText(text);
      cap.ctlbox = Html.Anchor.create('pop-close', null, function() {
        container.close();
      }).into(cap);
      return cap;
    }
  },
  Content:{
    create:function(container) {
      return Html.Div.create('pop-content').into(container);
    }
  },
  Frame:{
    create:function(container, caption, anchor) {
      var div = Html.Div.create('pop-frame').into(container);
      var head;
      var cap = Html.H1.create().into(div).hide();
      if (anchor) {
        head = Html.Div.create('pop-frame-head').into(div);
        head.add(cap).add(anchor);
      }
      var self = Html.Div.create('pop-frame-content').into(div);
      self = self.aug({
        frame:div,
        head:head,
        cap:cap,
        setCaption:function(text) {
          self.cap.show().setText(text);
        },
        setCaptionHtml:function(html) {
          self.cap.show().html(html);
        },
        addClass:function(cls) {
          div.addClass(cls);
          return self;
        }
      })
      if (caption)
        self.setCaption(caption);
      return self;
    }
  },
  CmdBar:{
    create:function(pop, container) {
      container = container || pop.content;
      var cb = Html.CmdBar.create(container, pop);
      return cb.aug({
        build:function() {
          if (! cb._built) {
            pop.buildCmd(cb);
            cb._built = cb;
          }
          return cb;
        },
        reset:function() {
          cb.container().clean();
          cb._built = null;
        },
        saveCancel:function(cap, id) {
          cb.save(pop.save_onclick, cap, id).cancel(pop.close);
          return cb;
        },
        saveDelCancel:function(cap, id, delcap) {
          cb.save(pop.save_onclick, cap, id).del(pop.del_onclick, delcap).cancel(pop.close);
          return cb;
        },
        okCancel:function() {
          cb.ok(pop.ok_onclick).cancel(pop.close);
        }
      });
    }
  },
  ErrorBox:{
    create:function(pop) {
      return Html.Div.create('pop-error mpe').after(pop.content).aug({
        showException:function(e) {
          this.show(e.message);
        },
        show:function(html) {
          this.html(html);
          this.style.display = '';
          Html.Window.flickerFixedRows();
        }
      });
    }
  }
}
Html.SingletonPop = {
  /*
   * SomePop = Html.SingletonPop.aug({
   *   create:function() {
   *     return Html.Pop.create('Caption').extend(function(self) {
   *       ..
   *     })
   *   }
   * })
   */
  aug:function(augs) {
    return Object.create(this.Proto, augs);
  },
  Proto:{
    pop:function() {
      return Html.Pop.singleton_pop.apply(this, arguments);
    }
  }
}
/**
 * Pop IncludedSourcePop
 * 
 */
Html.IncludedSourcePop = {
  /*
   * QPopLegacyMed = {
   *   pop:function(q, onupdate, pos) {
   *     Html.IncludedSourcePop.pop.apply(QPopLegacyMed, arguments);
   *   }
   *   create:function(callback) {
   *     Html.IncludedSourcePop.create_asSingleton(callback, 'LegacyMedPop', 'popMedLegacy', function(self) {
   *       return {
   *         init:function() {..},
   *         onshow:function(q, onupdate,pos) {..}
   *       }
   *     })
   *   }
   * }
   */
  create_asSingleton:function(oninclude, name, id, protof) {
    this.singleton(name, id, protof, oninclude);
  },
  pop:function() {
    var args = Array.prototype.slice.call(arguments);
    this.create(function(self) {
      self.pop.apply(self, args);
    })
  },
  //
  singleton:function(name, id, protof, oninclude) {
    var My = this;
    Pop.cacheMousePos();
    if (My._singleton == null)
      this.create(name, id, function(self) {
        My._singleton = self.extend(protof);
        oninclude(My._singleton);
      })
    else
      oninclude(My._singleton);
  },
  create:function(name, id, oninclude) {
    Pop.cacheMousePos();
    var src = this.getSrc(name);
    Includer.getWorking(src, function() {
      var self = Html.Pop.from(id);
      oninclude(self);
    })
  },
  getSrc:function(name) {
    return 'js/pops/inc/' + name + '.php';
  }
}
/**
 * Pop UploadPop
 */
Html.UploadPop = {
  /*
   * @arg mixed serverVars either 'action' or {'name':'value',..}
   */
  create:function(caption, serverVars, fileCount, maxFileSize) {
    var My = this;
    return Html.Pop.create(caption).extend(function(self) {
      return {
        oncomplete:function(data) {},
        //
        init:function() {
          self.form = My.UploadForm.create(self.content, serverVars, fileCount, maxFileSize)
            .bubble('oncomplete', self.form_oncomplete);
          self.cb = Html.CmdBar.create(self.content)
            .button('Upload Now', self.upload_onclick, 'upload')
            .del(Function.defer(self, 'del_onclick')).showDelIf(false)
            .cancel(self.cancel_onclick);
        },
        reset:function() {
          self.form.reset();
          self.cb.showDelIf(false);
          self.visible();
        },
        //
        upload_onclick:function() {
          self.working(true);
          self.form.submit();
          self.invisible();
        },
        form_oncomplete:function(msg) {
          self.visible();
          self.working(false);
          if (msg.id == 'error') {
            self.form_oncomplete_error(msg.obj);
          } else {
            self.form_oncomplete_ok(msg);
            self.oncomplete(msg.obj);
          }
        },
        form_oncomplete_ok:function(msg) {
          self.reset();
          self.close();
        },
        form_oncomplete_error:function(e) {
          Page.showAjaxError(e);
        },
        cancel_onclick:function() {
          self.close();
        },
        working:function(b) {
          Html.Window.working(b);
        }
      }
    })
  },
  UploadForm:{
    create:function(container, serverVars, fileCount, maxFileSize) {
      var url = 'serverUpload.php';
      if (String.is(serverVars))
        serverVars = {'action':serverVars};
      return Html.UploadForm.create(container, url, serverVars, fileCount, maxFileSize);
    }
  }
}
//
Html.BrowserPop = {
  create:function(cap, staticUrl/*=null*/) {
    return Html.Pop.create(cap || 'Browser').extend(function(self) {
      return {
        init:function() {
          self.content.addClass('p5');
          self.IFrame = Html.IFrame.create().setHeight(self.content.getHeight()).into(self.content);
          self.fullsize();
        },
        onshow:function(url) {
          self.nav(url || staticUrl);
        },
        withExit:function() {
          if (self.Cb == null) {
            self.Cb = Html.CmdBar.create(self.content).exit(self.close);
            self.Cb.addClass('mt0');
          }
          return self;
        },
        nav:function(url) {
          if (url)
            self.IFrame.nav(url);
        },
        reset:function() {
          self.IFrame.clean();
        },
        fullsize:function(maxw, maxh) {
          var vmar = 40 + self.getHeight() - self.IFrame.getHeight();
          var hmar = 40;
          self.IFrame.fullsize(vmar, hmar, maxh, maxw);
          return self.reposition();
        },
        close:function() {
          self.reset();
          Pop.close(true);
        }
      }
    })
  }
}
CerberusPop = {
  //
  pop:function(url, caption) {
    return Html.Pop.singleton_pop.apply(this, arguments);
  },
  pop_asSuperbill:function(sid) {
    return this.pop(this.url('superbill', sid), 'Superbill').fullsize();
  },
  pop_asListSuperbills:function(cid) {
    return this.pop(this.url('list', cid)).fullsize();
  },
  pop_asUnsigned:function() {
    return this.pop(this.url('unsigned'), 'Unsigned Superbills').fullsize();
  },
  pop_asInsurance:function(cid) {
    return this.pop(this.url('insurance', cid), 'Edit Insurance').fullsize(900);
  },
  create:function() {
    return Html.BrowserPop.create('Billing Window').withExit().extend(function(self) {
      return {
        onshow:function(url, caption) {
          caption && self.setCaption(caption);
          self.nav(url);
        }
      }
    })
  },
  //
  url:function(action, id) {
    return Page.url('cerberus-body.php', {'action':action,'id':id});
  }
}
/**
 * Pop DirtyPop
 */
Html.DirtyPop = {
  create:function(caption, width) {
    return Html.Pop.create(caption, width).extend(Html.DirtyPop.Proto);
  },
  Proto:function(self) {
    return {
      ondirty:function() {},
      //
      save:function() {},  // @abstract, should finish with self.close_asSaved
      //
      reset:function() {
        self.setDirty(false);
      },
      setDirty:function(value) {
        var orig = self._dirty;
        self._dirty = value;
        if (! orig && value)
          self.ondirty();
      },
      isDirty:function() {
        return self._dirty;
      },
      close:function(saved) {
        if (saved)
          Pop.close(true);
        else
          Pop.Confirm.closeCheckDirty(self, self.save);
        if (self.onclose)
          self.onclose();
      },
      close_asSaved:function(/*any*/) {
        self.close(true);
        if (self.onsave)
          self.onsave.apply(self, arguments);
      }
    }
  }
}
Html.DirtyEntryPop = {
  create:function(caption, width) {
    return Html.DirtyPop.create(caption, width).extend(Html.DirtyEntryPop.Proto);
  },
  Proto:function(self) {
    return {
      onbeforeload:function(rec) {},  // may modify rec or return new one
      onsave:function(rec) {},
      ondelete:function(id) {},
      onerror:function(e) {},
      //
      onpop:function() {
        self.load.apply(self, arguments);
      },
      onshow:function() {
        self.form && self.form.focus();
      },
      onpopactivate:function() {
        self.form && self.form.focus();
      },
      reset:function() {
        self.rec = null;
        self.form && self.form.reset();
      },
      load:function(rec) {
        var changed = self.onbeforeload.apply(self, arguments);
        if (changed)
          rec = changed;
        self.rec = rec;
        self.form && self.form.load(self.rec);
      },
      isDirty:function() {
        return self.form && self.form.isDirty();
      },
      close:function(mode, arg) {
        if (mode > 0)
          Pop.close(true);
        else
          Pop.Confirm.closeCheckDirty(self, self.save);
        if (self.onclose)
          self.onclose();
        if (mode == 1 && self.onsave)
          self.onsave(arg);
        if (mode == 2 && self.ondelete)
          self.ondelete(arg);
      },
      close_asSaved:function(rec) {
        self.close(1, rec);
      },
      close_asDeleted:function(id) {
        self.close(2, id);
      },
      save_onclick:function() {
        self.save();
      },
      del_onclick:function() {
        Pop.Confirm.showYesNo('Are you sure you want to remove this record?', self.remove);
      },
      save:function() {
        self.form.getRecord().ajax(self).save(self.close_asSaved, self.onerror);
      },
      remove:function() {
        self.rec.ajax(self).remove(self.close_asDeleted);
      },
      cancel_onclick:function() {
        self.close();
      }
    }
  }
}
/**
 * DirtyPop RecordEntryPop
 *   PopFrame frame
 *   EntryForm form
 *   CmdBar cmd
 */
Html.RecordEntryPop = {
  /*
   * AbcEntryPop = {
   *   pop:function(rec) {
   *     AbcEntryPop = this.create().pop(rec);
   *   },
   *   create:function() {
   *     var self = Html.RecordEntryPop.create('Caption');
   *     return self.aug({
   *       //..
   *     });
   *   }
   * }
   * AbcEntryPop.pop(rec).aug({
   *   onsave:function(rec) {..}
   *   ondelete:function(id) {..}
   * });
   */
  create:function(caption, width, frameCaption) {
    var My = this;
    return Html.DirtyPop.create(caption, width).extend(My, function(self) {
      return {
        //
        onshow:function(rec, focusId) {  // assumes focusId passed to pop, but this is optional
          self.form.focus(focusId);
        },
        onload:function(rec) {},  // supplies all pop arguments (rec, focusId, ..)
        onsave:function(rec) {},
        onerror:function(e) {
          self.working(false);
          self.errorbox.show(e.message);
          Html.Window.flickerFixedRows();
        },
        close:function(saved) {
          if (saved)
            Pop.close(true);
          else
            Pop.Confirm.closeCheckDirty(self, self.save_onclick);
          if (self.onclose)
            self.onclose();
        },
        /*
         * @abstract
         * @arg EntryForm ef
         * @arg Rec rec
         */
        buildForm:function(ef, rec) {},
        /*
         * @abstract
         * @arg CmdBar cb
         */
        buildCmd:function(cb) {
          cb.save(self.save_onclick).cancel(self.cancel_onclick);
        },
        /*
         * @abstract
         * @arg Rec rec
         * @arg fn(Rec) onsuccess
         * @arg fn(Rec) onerror
         */
        save:function(rec, onsuccess, onerror) {
        },
        /*
         * @abstract
         * @return bool
         */
        isDeletable:function(rec) {
          return rec != null;
        },
        //
        init:function() {
          self.frame = Html.Pop.Frame.create(self.content, frameCaption);
          self.form = My.Form.create(self);
          self.cmd = Html.Pop.CmdBar.create(self);
          self.errorbox = Html.Pop.ErrorBox.create(self);
        },
        /*
         * @arg Rec rec
         */
        pop:function(rec) {
          self.rec = rec;
          self.setDirty(false);
          self.onpop.apply(self, arguments);
          self.form.build().setRecord(self.rec);
          if (self.cmd.build)
            self.cmd.build();
          self.errorbox.hide();
          self.onload.apply(self, arguments);
          self.show.apply(self, arguments);
          self.cmd.showDelIf(self.isDeletable(self.rec))
          return self;
        },
        isDirty:function() {
          return self.form.isRecordChanged();
        },
        //
        close_asSaved:function(rec) {
          self.close(true);
          self.onsave(rec);
        },
        getRecord:function() {
          return self.form.applyTo(self.rec);
        },
        save_onclick:function() {
          var rec = self.getRecord();
          self.working(function() {
            self.errorbox.hide();
            self.save(rec,
              function(rec) {
                self.working(false);
                self.close_asSaved(rec);
              },
              [self.onerror, self]);
          });
        },
        cancel_onclick:function() {
          self.close();
        }
      }
    })
  },
  Form:{
    create:function(pop) {
      var ef = Html.EntryForm.create(pop.frame);
      return ef.aug({
        build:function() {
          if (! ef._built) {
            pop.buildForm(ef);
            ef._built = ef;
          }
          return ef;
        }
      });
    }
  }
}
/**
 * RecordEntryPop RecordEntryDeletePop
 */
Html.RecordEntryDeletePop = {
  create:function(caption, width, frameCaption) {
    var self = Html.RecordEntryPop.create(caption, width);
    return self.aug({
      /*
       * @events
       */
      onsave:function(rec) {},
      ondelete:function(id) {},  // @arg int id of Rec deleted
      /*
       * @abstract
       * @arg CmdBar cb
       */
      buildCmd:function(cb) {
        cb.save(self.save_onclick).del(self.del_onclick).cancel(self.cancel_onclick);
      },
      /*
       * @abstract
       * @arg Rec rec
       * @arg fn(id) onsuccess
       */
      remove:function(rec, onsuccess) {
      },
      /*
       * @abstract
       * @return string
       */
      getDeleteNoun:function() {
        return 'record';
      },
      //
      del_onclick:function() {
        var rec = self.form.getRecord();
        Pop.Confirm.showYesNo('Are you sure you want to remove this ' + this.getDeleteNoun() + '?', function() {
          self.working(true);
          self.errorbox.hide();
          self.remove(rec,
            function(id) {
              self.working(false);
              self.close();
              self.ondelete(id);
            },
            [self.onerror, self]);
        });
      }
    });
  }
}
/**
 * RecEntryCallbackPop.pop(rec, callback)
 * callback supplies either Rec of add/update or [int] ID of delete
 */
Html.RecEntryPop = {
  create:function(caption, width, frameCaption) {
    var self = Html.RecordEntryDeletePop.create(caption, width);
    return self.aug({
      onshow:function() {},
      buildForm:function(ef) {},
      save:function(rec, callback_rec) {},
      remove:function(rec, callback_id) {},
      onload:function(rec, callback) {
        self.setCallback(callback);
      },
      //
      setCallback:function(callback) {
        if (callback) {
          self.onsave = callback;
          self.ondelete = function(id) {
            callback([id]);
          }
        }
      }
    })
  }
}
/**
 * Span TextAnchor
 *   InputText input
 *   Anchor anchor
 */
Html.TextAnchor = {
  create:function(anchorCls, inputSize) {
    var self = Html.Span.create();
    return self.aug({
      onclick_anchor:function(text) {},
      //
      init:function() {
        self.input = Html.InputText.create().setSize(inputSize).into(self);
        self.anchor = Html.Anchor.create(anchorCls, null, self.anchor_onclick).into(self);
      },
      setText:function(text) {
        self.input.value = String.denull(text);
      },
      getText:function() {
        return self.input.value;
      },
      setFocus:function() {
        self.input.setFocus();
      },
      //
      anchor_onclick:function() {
        self.onclick_anchor(self.getText());
      }
    });
  }
}
/**
 * TextAnchor SearchTextAnchor
 */
Html.SearchTextAnchor = {
  create:function(anchorCls, inputSize, input/*=null*/) {
    var self = Html.Span.create();
    return self.aug({
      onclick_anchor:function(text) {},
      //
      init:function() {
        self.input = (input || Html.InputAutoComplete).create().setSize(inputSize).into(self);
        self.anchor = Html.Anchor.create(anchorCls || 'mglass', null, self.anchor_onclick).into(self);
        self.focusable(self.input);
      },
      setText:function(text) {
        self.input.setValue(text);
        return self;
      },
      getText:function() {
        return self.input.value;
      },
      withOnCr:function(oncr) {
        if (oncr == null)
          oncr = self.anchor_onclick
        self.input.aug(Events.onkeypresscr).aug({
          onkeypresscr:function() {
            oncr();
          }
        })
        return self;
      },
      setWidth:function(w) {
        self.input.setWidth(w - 18/*width of mglass*/);
        return self;
      },
      getWidth:function() {
        return self.input.getWidth() + 18/*width of mglass*/;
      },
      //
      anchor_onclick:function() {
        self.onclick_anchor(self.getText());
      }
    });
  }
}
/**
 * AnchorTab AnchorTab
 */
Html.AnchorTab = {
  create:function(text, cls) {
    var at = new AnchorTab(text || '(Select)', cls || 'atedit');
    return Class.augment(at, null, {
      checks:function(recs, cols, isRequired) {
        at.loadChecks(recs, null, null, null, null, isRequired, cols);
        return at;
      },
      radios:function(recs, cols, isRequired) {
        at.loadRadios(recs, null, null, null, isRequired, cols);
        return at;
      },
      okCancel:function(onok) {
        at.appendCmd(null, onok);
        return at;
      },
      cancelOnly:function() {
        at.appendCmd(AnchorTab.BUTTONS_CANCEL_ONLY);
        return at;
      }
    })
  }
}
/**
 * Anchor AnchorTabSelector
 */
Html.AnchorTabSelector = {
  create:function(cls, cols, text) {
    cols = cols || 3;
    var at = new AnchorTab(text || '(Select)', cls || 'atedit');
    var self = at.anchor;
    return self.aug({
      onchange:function(value) {},
      //
      /*
       * @arg Rec[] recs
       * @arg string valueFid (optional)
       * @arg string textFid (optional)
       */
      radios:function(recs, valueFid, textFid) {
        at.loadRadios(recs, valueFid, textFid, null, null, cols);
        at.appendCmd(AnchorTab.BUTTONS_CANCEL_ONLY);
        at.setOnchange(self.at_onchange);
        return self;
      },
      setValue:function(value) {
        at.setValue(value);
      },
      getValue:function() {
        return at.getValue();
      },
      getText:function() {
        return self.innerText;
      },
      //
      at_onchange:function() {
        self.onchange(at.getValue());
      }
    })
  }
}
/**
 * SearchTextAnchor Picker
 */
Html.Picker = {
  create:function(inputSize, pop, input/*=null*/) {
    return Html.SearchTextAnchor.create(null, inputSize, input).extend(this, function(self) {
      return {
        init:function() {
          self.pop = pop;
          self.input
            .bubble('oncomplete', self, 'input_oncomplete')
            .bubble('onclear', self, 'input_onclear');
        },
        getValue:function() {
          return self.value;
        },
        set:function(value, text) {
          self.value = value;
          self.setText(text);
        },
        //
        onclick_anchor:function(text) {
          self.pop.show(self.value, self.getText());
        },
        input_oncomplete:function(rec, text) {
          self.set(rec, text);
        },
        input_onclear:function() {
          self.set(null, null);
        }
      }
    })
  }
}
/**
 * Picker RecPicker
 */
Html.RecPicker = {
  //
  create:function(/*int*/inputSize, /*PickerPop*/pop, /*Input*/input/*=null*/) {
    return Html.Picker.create(inputSize, pop, input).extend(function(self) {
      return {
        onset:function(rec) {},
        //
        getValueFrom:/*string*/function(/*Rec*/rec) {
        },
        getTextFrom:/*string*/function(/*Rec*/rec) {
        },
        set:function(/*Rec*/rec) {
          if (rec)
            self.setValueText(self.getValueFrom(rec), self.getTextFrom(rec));
          else
            self.setValueText(null, '');
          self._dirty = self._dirty || self.rec != rec;
          self.rec = rec;
          return self;
        },
        reset:function(rec/*optional, to establish initial 'undirty' value*/) {
          self.set(rec);
          self._dirty = false;
          return self;
        },
        isDirty:function() {
          return self._dirty;
        },
        getValue:/*Rec*/function() {
          return self.value;
        },
        getText:/*string*/function() {
          return self.input.value;
        },
        //
        setValueText:function(value, text) {
          self.value = value;
          self.setText(text);
        },
        showPop:function(value, text) {
          self.pop.pop(value, text)
            .bubble('onselect', self.pop_onselect)
            .bubble('onclose', self.pop_onclose);
        },
        onclick_anchor:function(text) {
          self.showPop(self.value, self.getText());
        },
        pop_onselect:function(rec) {
          self.set(rec);
          self.onset(rec);
        },
        pop_onclose:function() {
          self.input.setFocus();
        },
        input_oncomplete:function(rec, text) {
          self.set(rec);
          self.onset(rec);
        },
        input_onclear:function() {
          self.set(null);
          self.onset(null);
        }
      }
    })
  }
}
/**
 * ScrollTable RecordTable
 */
Html.RecordTable = {
  create:function(container, cls) {
    var self = Html.ScrollTable.create(container, cls);
    return self.aug({
      /*
       * @events
       */
      onselect:function(rec) {},
      onload:function(recs) {},
      /*
       * @abstract (must override if using argless load)
       * @arg fn(Rec[]) callback_recs
       */
      fetch:function(callback_recs) {
        callback_recs(self.recs);
      },
      /*
       * @abstract
       * @arg Rec rec
       * @arg TrAppender tr to build record row e.g. tr.select(rec, rec.name).td(rec.desc)
       */
      add:function(rec, tr) {},
      //
      /*
       * @arg Rec[] recs (optional; if null, must implement fetch)
       */
      load:function(recs) {
        self.recs = recs;
        self.working(true);
        self.fetch(function(recs) {
          self.working(false);
          self.recs = recs;
          self.draw();
          self.onload(recs);
        });
      },
      //
      tbody:function() {
        var tbody = Html.Table._proto.tbody.call(self);
        if (! tbody._auged) {
          tbody.aug({
            tr:function(cls) {
              if (cls == null)
                cls = Math.isEven(self.ct++) ? '' : 'off';
              var appender = Html.Table._protoBody.tr.call(tbody, cls);
              return Class.augment(appender, null, {
                /*
                 * @arg Rec rec
                 * @arg proto|<a>|string e e.g. AnchorTrackItem
                 * @return TrAppender
                 */
                select:function(rec, e) {
                  if (String.is(e))
                    e = Html.AnchorRec.asSelect(e, rec, self.onselect);
                  else if (Html.Anchor.is(e))
                    e.bubble('onclick', self.onselect.curry(rec));
                  else
                    e = e.create(rec, self.onselect);
                  return this.td(e);
                }
              });
            }
          });
          tbody._auged = self;
        }
        return tbody;
      },
      clean:function() {
        self.tbody().clean();
        self.ct = 0;
        return self;
      },
      draw:function() {
        self.clean();
        self.working(function() {
          Array.forEach(self.recs, function(rec) {
            self.add(rec, self.tbody().tr());
          });
          self.working(false);
        });
      }
    })
  }
}
/**
 * Div SearchRecordTable
 *   Div searcher
 *     SearchTextAnchor input
 *     Span filterbox
 *   RecordTable table
 */
Html.SearchRecordTable = {
  create:function(container) {
    var self = Html.Div.create().into(container);
    return self.aug({
      /*
       * @events
       */
      onload:function(recs) {},
      onselect:function(rec) {},
      /*
       * @abstract
       * @arg fn(Rec[]) callback_recs
       */
      fetch:function(callback_recs) {},
      /*
       * @abstract
       * @arg Rec rec
       * @arg RegExp search
       * @return bool true if rec should be displayed based upon search
       */
      applies:function(rec, search) {},
      /*
       * @abstract
       * @arg Rec rec
       * @arg TrAppender tr to build record row e.g. tr.select(rec, rec.name).td(rec.desc)
       */
      add:function(rec, tr) {},
      //
      init:function() {
        self.searcher = Html.SearchRecordTable.Searcher.create(self).aug({
          onclick_search:function(text) {
            self.load(text);
          }
        })
        self.table = Html.SearchRecordTable.Table.create(self).aug({
          fetch:function(callback_recs) {
            self.fetch(callback_recs);
          },
          add:function(rec, tr) {
            self.add(rec, tr);
          },
          onselect:function(rec) {
            self.onselect(rec);
          },
          onload:function(recs) {
            self.onload(recs);
          }
        })
      },
      load:function(text) {
        self.text = text;
        self.searcher.setSearchText(text);
        if (self.loaded) {
          self.table.draw();
        } else {
          self.loaded = self;
          self.table.load();
        }
      },
      reload:function() {
        self.loaded = false;
        self.load(self.text);
      },
      getSearchText:function() {
        return self.searcher.getSearchText();
      },
      setFocus:function() {
        self.searcher.setFocus();
      },
      thead:function() {
        return self.table.thead();
      },
      //
      tbody:function() {
        return self.table.tbody();
      },
      clean:function() {
        self.table.clean();
        self.loaded = null;
        return self;
      }
    });
  },
  Table:{
    create:function(container) {
      var self = Html.RecordTable.create(container);
      return self.aug({
        draw:function() {
          self.clean();
          if (self.recs) {
            self.working(function() {
              var search = container.searcher.getSearchRegExp();
              var unapplies = [];
              for (var i = 0; i < self.recs.length; i++) {
                var rec = self.recs[i];
                if (container.applies(rec, search))
                  container.add(rec, self.tbody().tr(''));
                else
                  unapplies.push(rec);
              }
              for (var i = 0; i < unapplies.length; i++)
                container.add(unapplies[i], self.tbody().tr('off'));
              self.working(false);
            })
          }
        }
      })
    }
  },
  Searcher:{
    create:function(container) {
      var self = Html.Div.create('mb5').into(container);
      return self.aug({
        onclick_search:function(text) {},
        //
        init:function() {
          self.input = Html.SearchTextAnchor.create().withOnCr().into(self).aug({
            onclick_anchor:function(text) {
              self.onclick_search(text);
            },
            onselect:function(e) {
              Html.Window.cancelBubble(e);  // to keep from firing SearchRecordTable.onselect custom event
            }
          });
          self.filterbox = Html.Span.create().into(self);
        },
        getSearchText:function() {
          return self.input.getText();
        },
        getSearchRegExp:function() {
          var text = String.nullify(self.input.getText());
          return (text) ? new RegExp(text, 'i') : null;
        },
        setSearchText:function(text) {
          self.input.setText(text);
        },
        setFocus:function() {
          self.input.setFocus();
        }
      });
    }
  }
}
/**
 * @deprecated use RecPicker instead
 * Picker RecordPicker
 *   PickerPop pop
 */
Html.RecordPicker = {
  create:function(popCaption, inputSize, popWidth) {
    var My = this;
    var pop = Html.PickerPop.create(popCaption, popWidth);
    return Html.Picker.create(inputSize, pop).extend(My, function(self) {
      return {
        /*
         * @events
         */
        onset:function(rec) {},
        //
        init:function() {
          self.pop = pop.aug({
            onselect:function(rec) {
              self.set(rec);
              self.onset(rec);
              self.input.setFocus();
            },
            onclose:function() {
              self.setFocus();
            },
            table_fetch:function(callback_recs) {
              self.fetch(callback_recs);
            },
            table_applies:function(rec, search) {
              return self.applies(rec, search);
            },
            table_add:function(rec, tr) {
              self.add(rec, tr);
            },
            cmdbar_buttons:function(cb) {
              self.buttons(cb);
            }
          })
        },
        /*
         * @arg Rec rec
         */
        set:function(rec) {
          self.rec = rec;
          if (rec)
            self.setValueText(self.getValueFrom(rec), self.getTextFrom(rec));
          else
            self.setValueText(null, '');
        },
        /*
         * @arg string value
         * @arg string text
         */
        setValueText:function(value, text) {
          self.value = value;
          self.setText(text);
        },
        /*
         * @return string
         */
        getValue:function() {
          return self.value;
        },
        /*
         * @return string
         */
        getText:function() {
          return self.input.value;
        },
        //
        input_oncomplete:function(rec, text) {
          self.set(rec);
          self.onset(rec);
        },
        /*
         * @abstract
         * @arg Rec rec
         * @return string
         */
        getValueFrom:function(rec) {},
        /*
         * @abstract
         * @arg Rec rec
         * @return string
         */
        getTextFrom:function(rec) {},
        /*
         * @abstract
         * @arg fn(Rec[]) callback_recs
         */
        fetch:function(callback_recs) {},
        /*
         * @abstract
         * @arg Rec rec
         * @arg RegExp search
         * @return bool true if rec should be displayed based upon search
         */
        applies:function(rec, search) {},
        /*
         * @abstract
         * @arg Rec rec
         * @arg TrAppender tr to build record row e.g. tr.select(rec, rec.name).td(rec.desc)
         */
        add:function(rec, tr) {},
        thead:function() {
          return self.pop.table.thead();
        },
        /*
         * Override to create add'l buttons
         */
        buttons:function(cmd) {
          cmd.cancel(self.pop.close);
        }
      }
    })
  }
}
/**
 * AnchorAction AnchorPicker
 *   PickerPop pop
 */
Html.AnchorPicker = {
  /*
   * @arg string cls
   * @arg string defaultText (optional)
   * @arg string popCaption (optional)
   * @arg int popWidth (optional)
   */
  create:function(cls, defaultText, popCaption, popWidth) {
    return Html.AnchorAction.create(cls, defaultText || '(Select)').extend(function(self) {
      return {
        onset:function(rec) {},
        //
        fetch:function(fn_recs) {},
        applies:function(rec, search) {},
        add:function(rec, tr) {},
        getValueFrom:function(rec) {},
        getTextFrom:function(rec) {},
        //
        init:function() {
          self.pop = Html.PickerPop.create(popCaption, popWidth);
          self.pop.bubble('onselect', self.pop_onselect).bubble('table_fetch', self, 'fetch').bubble('table_applies', self, 'applies').bubble('table_add', self, 'add').bubble('cmdbar_buttons', self, 'buttons');
        },
        set:function(rec) {
          if (rec)
            self.setValueText(self.getValueFrom(rec), self.getTextFrom(rec));
          else
            self.setValueText(null, defaultText);
        },
        thead:function() {
          return self.pop.table.thead();
        },
        onclick:function() {
          self.pop.show(self.value, self.getText());
        },
        //
        setValueText:function(value, text) {
          self.value = value;
          self.setText(text);
        },
        getValue:function() {
          return self.value;
        },
        getText:function() {
          return (self.value) ? self.innerText : '';
        },
        pop_onselect:function(rec) {
          self.set(rec);
          self.onset(rec);
        },
        buttons:function(cmd) {}
      }
    })
  }
}
/**
 * Pop PickerPop
 *   SearchRecordTable table
 */
Html.PickerPop = {
  create:function(caption, width) {
    var My = this;
    return Html.Pop.create(caption || 'Selector', width || 600).extend(My, function(self) {
      return {
        POP_POS:Pop.POS_CURSOR,
        onbeforeshow:function(/* pop() args */) {},
        onselect:function(rec) {},
        table_fetch:function(callback_recs) {},
        table_applies:function(rec, search) {},
        table_add:function(rec, tr) {},
        cmdbar_buttons:function(cb) {
          cb.cancel(self.close)
        },
        //
        init:function() {
          self.table = Html.SearchRecordTable.create(self.content).aug({
            onselect:function(rec) {
              self.select(rec);
            },
            fetch:function(callback) {
              self.table_fetch(callback);
            },
            applies:function(rec, search) {
              return self.table_applies(rec, search);
            },
            add:function(rec, tr) {
              self.table_add(rec, tr);
            }
          })
          self.cmd = My.CmdBar.create(self);
        },
        clean:function() {
          self.table.clean();
          return self;
        },
        getSearchText:function() {
          return self.table.getSearchText();
        },
        show:function(value, text) {
          self.onbeforeshow.apply(this, arguments);
          self.cmd.load();
          Pop.show(self, null, self.POP_POS);
          self.table.load(text);
          self.table.setFocus();
          self.onshow.apply(this, arguments);
          return self;
        },
        reload:function() {
          self.table.reload()
        },
        select:function(rec) {
          self.close();
          self.onselect(rec);
        }
      }
    })
  },
  CmdBar:{
    create:function(pop) {
      var self = Html.CmdBar.create(pop.content);
      return Class.augment(self, null, {
        load:function() {
          if (! self.loaded) {
            pop.cmdbar_buttons(self);
            self.loaded = self;
          }
        }
      })
    }
  }
}
/**
 * Div Box
 */
Html.Box = {
  create:function(container, cls) {
    var self = Html.Div.create('box-content');
    var tbody = Html.Table.create(container, 'box ' + cls).tbody();
    tbody.tr('box-tb').td(null, 'tl').td(null, 't').td(null, 'tr');
    tbody.tr().td(null, 'l').td(self, 'content').td(null, 'r');
    tbody.tr('box-tb').td(null, 'bl').td(null, 'b').td(null, 'br');
    return self;
  },
  create_asWideThin:function(container) {
    return Html.Box.create(container, 'wide min-pad');
  }
}
/**
 * A BigButton
 */
Html.BigButton = {
  create:function(container, cls, text, onclick) {
    return Html.Anchor.create('cmd bigb ' + cls, text, onclick).into(Html.Tile.create(container, 'cmd-fixed'));
  }
}