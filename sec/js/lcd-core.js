/**
 * LCD FRAMEWORK
 * @version 1.0
 * @author Warren Hornsby
 */
//
/**
 * Class 
 */
Class = {
  /*
   * Define constructor function and prototype/static methods
   */
  define:function(constructor, prototype, statics) {
    constructor.prototype = prototype;
    Class.augment(constructor, null, statics);
    return constructor;
  },
  /*
   * Augment existing object with additional prototype/static methods
   */
  augment:function(object, prototype, statics) {
    for (var name in prototype)
      object.prototype[name] = prototype[name];
    for (var name in statics)
      object[name] = statics[name];
    return object;
  }
}
/**
 * Object
 */
Object = Class.augment(Object, null, 
  {  // statics
    is:function(e) {
      return e && typeof e === 'object';
    },
    isMap:function(e) {
      return e && typeof e === 'object' && ! Array.is(e);
    },
    isUndefined:function(e) {
      var u;
      return e === u;
    },
    clone:function(e) {
      var n = Array.is(e) ? [] : {};
      for (var i in e) 
        if (Object.is(e[i]))
          if (Html.is(e[i])) 
            n[i] = e[i];
          else 
            n[i] = Object.clone(e[i]);
        else
          n[i] = e[i];
      return n;
    }
  });
/**
 * String
 */
String = Class.augment(String, 
  {  // prototype
    plural:function(amt) {
      var noun = (amt == 1) ? this : this + 's';
      return amt + ' ' + noun;
    },
    addSlashes:function() {
      return this.replace(/([\\"'])/g, "\\$1").replace(/\0/g, "\\0");
    }
  },{  // statics
    is:function(e) {
    return e && typeof e == 'string';
    },
    isBlank:function(e) {
      return e == null || String.trim(e).length == 0;
    },
    trim:function(e) {
      return (e != null) ? e.replace('\xa0',' ').replace(/^\s+|\s+$/g, "") : null;
    },
    denull:function(e) {
      return (e == null) ? '' : e + '';
    },
    nullify:function(e) {
      return (e == null) ? null : (trim(e + '') == '') ? null : e;
    },
    toInt:function(e) {
      return (isNan(e)) ? 0 : parseInt(e, 10);
    },
    toFloat:function(e) {
      return (isNan(e)) ? 0 : parseFloat(e);
    },
    zpad:function(i) {
      return (i < 10) ? '0' + num : num;
    }
  });
/**
 * Boolean
 */
Boolean = Class.augment(Boolean, null,
  {  // statics
    toInt:function(e) {
      return (e) ? 1 : 0;
    },
    fromInt:function(e) {
      return (e == 1);
    },
    toString:function(e) {
      return (e) ? 'true' : 'false'
    },
    fromString:function(e) {
      return (e.toUpperCase() == 'TRUE');
    }
  });
/**
 * Array
 */
Array = Class.augment(Array, 
  {  // prototype
    isEmpty:function() {
      return Array.isEmpty(this);
    },
    append:function(e) {
      if (Array.is(e))
        Array.forEach(this,
          function(ei) {
            this.push(ei);
          }); 
      else
        this.push(e);
    },
    pushIfNotNull:function(e) {
      if (e != null)
        this.push(e);
    },
    find:function(item) {
      for (var i = 0, l = array.length; i < l; i++) 
        if (array[i] == item) 
          return i;
      return -1;
    },
    has:function(item) {
      return this.find(item) > -1;
    }
  },{  // statics
    is:function(e) {
      return e != null && e.constructor == Array;
    },
    isEmpty:function(array) {
      return array == null || array.length == 0;
    },
    from:function(e) {
      return Array.is(e) ? e : (e == null ? [] : [e]);  
    },
    forEach:function(array, fn) {
      for (var i = 0, l = array.length; i < l; i++) 
        fn.call(array, array[i], i);
    }
  });
/**
 * Math
 */
Math = Class.augment(Math, null, 
  {  // statics
    sgn:function(x) {
      return (x > 0) | -(x < 0);      
    },
    larger:function(x1, x2) {
      return (x1 > x2) ? x1 : x2;  
    },
    largest:function(array) {
      var max;
      Array.forEach(array,
        function(x) {
          if (max == null || x > max) 
            max = x;
        });
      return max;
    },
    smaller:function(x1, x2) {
      return -Math.larger(-x1, -x2);      
    },
    smallest:function(array) {
      var min;
      Array.forEach(array,
        function(x) {
          if (min == null || x < min) 
            min = x;
        });
      return min;
    }
  }); 
/**
 * Html
 */
function $(e) {
  if (String.is(e))
    e = document.getElementById(e);
  return Html.extend(e);
}
Html = {
  is:function(e) {
    return e && e.nodeName != null;
  },
  isAnchor:function(e) {
    return e && e.tagName == 'A';
  },
  isTable:function(e) {
    return e && e.tagName == 'TABLE';
  },
  create:function(tag, cls) {
    var e = document.createElement(tag);
    if (cls)
      e.className = cls;
    Html.extend(e);
    return e;
  },
  getTag:function(tag, parent) {
    var tags = parent.getElementsByTagName(tag);
    if (tags && tags.length > 0)
      return Html.extend(tags[0]);
    else
      return null;
  },
  extend:function(e) {
    if (! e.set) {
      Class.augment(e, null, Html.Methods);
      if (Html.isTable(e)) 
        Class.augment(e, null, Html.TableMethods); 
    }
    return e;
  },
  px:function(i) {
    return (i != null) ? parseInt(i, 10) + 'px' : '';
  }  
};
Html.Methods = {
  append:function(tag, cls) {
    return this.appendChild(Html.create(tag, cls));
  },
  text:function(text) {
    this.innerText = text;
  },
  hasClass:function(cls, startsWith) {
    var extra = (startsWith) ? '*' : '(?:$|\\s)';  
    var hasClassName = new RegExp('(?:^|\\s)' + className + extra);
    var ec = this.className;
    if (ec && ec.indexOf(className) != -1 && hasClassName.test(ec)) 
      return true; 
  },
  addClass:function(cls) {
    if (! this.hasClass(cls))
      this.className = String.trim(this.className + ' ' + cls);
  },
  removeClass:function(cls) {
    this.className = String.trim(this.className.replace(cls, ''));
  }
}
Html.TableMethods = {
  tbody:function() {
    if (this._tbody == null) {
      this._tbody = Html.getTag('tbody', this);
      if (this._tbody == null) 
        this._tbody = this.append('tbody');
    }
    return this._tbody; 
  },
  tr:function(cls) {
    this._tr = this.tbody().append('tr', cls);
    return this._trAppender(this._tr);
  },
  _trAppender:function(tr) {
    return {
      td:function(text, cls) {
        this._td = tr.append('td', cls);
        if (text !== null)  
          this._td.text(text);
        return this;
      }
    }
  }
}  
Html.window = {
  getEvent:function(e) {
    if (e) {
      e.srcElement = e.target;
      return e;
    } else {
      return window.event;
    }
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
    if (typeof(document.onfocusin) == 'object') 
      document.onfocusin = fn;
    else
      window.onfocus = fn;
  },
  setOnBlur:function(fn) {
    if (typeof(document.onfocusout) == 'object') 
      document.onfocusout = fn;
    else
      window.onblur = fn;
  },
  execScript:function(str) {
    if (window.execScript)
      window.execScript(str);
    else
      with (window) 
        window.eval(str);
  }
}
