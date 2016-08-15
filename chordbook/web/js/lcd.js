/** 
 * LCD v 1.2.001 (c)Warren Hornsby 
 **/

/** Object creation */
var undef;
function make(prototype/*, source, source..*/) {
  function Obj() {}
  Obj.prototype = prototype;
  var me = new Obj();
  var args = Array.prototype.slice.call(arguments);
  if (args.length > 1){
    args[0] = me;
    extend.apply(me, args);
  }
  me.init && me.init();
  return me;
}
function extend(target, source/*, source,..*/) {
  var sources = Array.prototype.slice.call(arguments, 1);
  for (var i = 0; i < sources.length; i++) { 
    for (var name in sources[i]) {
      if (target.hasOwnProperty(name) && isFunction(target[name])) {
        if (target.__super == undef)
          target.__super = {};
        target.__super[name] = target[name];
      }
      target[name] = sources[i][name];
    }
  }
  return target;
}
/** Looping */
function each(array, contextOrFn, fn) {
  var context;
  if (fn) {
    context = contextOrFn;
  } else {
    context = array;
    fn = contextOrFn;
  }
  if (array && array.length) 
    for (var i = 0, l = array.length; i < l; i++)
      fn.call(context, array[i], i);
  else if (Map.is(array))
    for (var i in array)
      fn.call(context, array[i], i);
}
/* loop(function(exit) {
 *   dostuff(i++);
 *   if (i > 10) exit();
 * })
 */
function loop(/*fn(exit)*/fn, /*int*/ms) {
  fn.timer = setInterval(fn.curry(function(onfinish) {
    fn.timer = clearInterval(fn.timer);
    if (onfinish)
      onfinish();
  }), ms || 1);
}
function pause(seconds, fn) {
  return setTimeout(fn, seconds * 1000);
}
function async(fn) {
  return setTimeout(fn, 1);
}
function queue(context, fn, /*fn*/state, delay/*=500ms*/) {
  var wrapper = function() {
    fn.queued = null;
    var s = state();
    if (s != fn.state) {
      fn.state = s;
      fn.call(context);
    }
  }
  wrapper.queue = function() {
    if (! fn.queued) {
      fn.queued = setTimeout(function() {
        if (fn.queued) 
          wrapper.call(context);
      }, delay || 500);
    }
  }
  return wrapper;
}
/** Constructor extends */
function extendc(constructor, prototype, statics) {
  for (var name in prototype)
    constructor.prototype[name] = prototype[name];
  return extend(constructor, statics);
}
/* Function */
extendc(Function, {
  bind:function(context) {
    var args = Array.prototype.slice.call(arguments, 1);
    var fn = this;
    return function() {
      return fn.apply(context, args.concat(Array.prototype.slice.call(arguments)));
    }
  },
  curry:function(/*arg, arg,..*/) {
    var fn = this;
    var args = Array.prototype.slice.call(arguments);
    return function() {
      return fn.apply(fn, args.concat(Array.prototype.slice.call(arguments)));
    }
  },
  extend:function(fn) {
    var fnOrig = this;
    return function() {
      return fn.apply(fn, [fnOrig].concat(Array.prototype.slice.call(arguments)));
    }
  }  
},{
  is:function(e) {
    return isFunction(e);
  },
  defer:function(context, /*string*/method/*, arg, arg,..*/) {
    var args = Array.prototype.slice.call(arguments, 2);
    return function() {
      context[method].apply(context, args);
    }
  }  
})
/* String */
extendc(String, {  
},{
  is:function(e) {
    return e !== null && typeof e == 'string';
  },
  isEmpty:function(e) {
    return e == null || String.trim(e).length == 0;
  },
  from:function(e) {
    return String.denull(e);
  },
  trim:function(e) {
    return (e != null) ? (e + '').replace(/\xa0/g, '').replace(/^\s+|\s+$/g, '') : null;
  },
  denull:function(e) {
    return (e == null) ? '' : e + '';
  },  
  nullify:function(e) {
    return (e == null) ? null : (String.trim(e + '') == '') ? null : e;
  }
})
/* Array */
extendc(Array, {
  find:function(value) {
    return Array.find(this, value);
  }
},{
  is:function(e) {
    return e != null && e.constructor == Array;
  },
  isEmpty:function(array) {
    return array == null || array.length == 0;
  },
  find:function(array, value) { 
    if (array && array.length) {
      for (var i = 0, l = array.length; i < l; i++) { 
        if (array[i] == value) 
          return i;
      }
    }
    return -1;
  }
})
/* Map */
var Map = {
  is:function(e) {
    return e && typeof e === 'object' && ! Array.is(e);
  },
  keys:/*string[]*/function(e) {
    var a = [];
    if (Map.is(e))
      for (var i in e) 
        a.push(i);
    return a;
  },
  values:/*[]*/function(e) {
    var a = [];
    if (Map.is(e))
      for (var i in e) 
        a.push(e[i]);
    return a;
  },
  from:function(e, key) {
    var m = {};
    if (Array.is(e)) {
      var isf = Function.is(key), k;
      each(e, this, function(o) {
        k = isf ? o[key]() : o[key];
        m[k] = o;
      })
    }
    return m;
  }
}
/** User interface components */
function ui($this, proto) {
  var me = make({
    $this:$this,
    on:function(event, toOrContext, to) {
      event = 'on' + event;
      this[event] = $.isFunction(toOrContext) ? 
        toOrContext :
        function() {
          return toOrContext[to || event].apply(toOrContext, arguments)
        };
      return this;
    }
  }, proto);
  $this.data('ui', me);
  return me;
}
function page($this, proto) {
  var me = make(ui($this), {
    ongo:function() {},
    onbeforeshow:function() {},
    onshow:function() {},
    //
    $body:$this.find('.body'),
    reset:function() {},
    show:function(trans, reverse) {
      this.onbeforeshow();
      if (trans == null) {
        trans = 'fade';
      } else if (trans.substr(0, 4) == 'back') {
        reverse = 1;
        trans = trans.substr(4);
      }
      $.mobile.changePage($this, {transition:trans,reverse:reverse});
      return this;
    }
  }, proto);
  return me;
}
function page_state(name, proto) {
  return rec_local(name, extend({
    /*string*/page:null,
    //
    restore:function(defaultPage) {
      this.go(this.page || defaultPage, 'none');
    },
    go:function(page, trans) {
      this.set('page', page);
      var page = Pages[page];
      page.ongo();
      page.show(trans);
    },
    set:function(fid, e) {
      this[fid] = e;
      return this.save();
    }
  }, proto));
}
/** Data */
function rec(proto) {
  return {
    revive:function(obj) {
      if (obj) 
        obj = JSON.parse(JSON.stringify(obj));
      var me = this.make(obj);
      me.onrevive.call(me);
      return me;
    },
    make:function(obj) {
      var json;
      var data = {};
      var me = make({
        onrevive:function() {},
        //
        set:function(fid, e, proto/*=null*/) {
          this[fid] = e;
          if (proto)
            this.apply(fid, proto);
          return this;
        },
        revive:function(fid, proto) {
          this[fid] = proto.revive(this[fid]);
          return this;
        },
        resetDirty:function() {
          json = JSON.stringify(this);
          for (var fid in this) 
            this[fid] && this[fid].resetDirty && this[fid].resetDirty();
          return this;
        },
        isDirty:function() {
          return json && JSON.stringify(this) != json;
        },
        data:function() {
          return data;
        },
        index:function() {
          return data.i;
        },
        indexed:function() {
          return data.i != null;
        }
      }, proto, obj);
      me.resetDirty();
      return me;
    },
    extend:function(o) {
      return rec(extend(proto, o)); 
    },
    statics:function(o) {
      return extend(this, o);
    }
  }
}
function recs(of, proto) {
  return {
    revive:function(/*[]*/objs) {
      var length;
      var data = {};
      var us = extend(objs || [], {
        data:function() {
          return data;
        }
      }, recs.proto, proto);
      if (of) {
        each(objs, this, function(obj, i) {
          us[i] = of.revive(obj);
          us[i].data().i = i; 
        })
      }
      us.onrevive.call(us);
      return us;
    },
    statics:function(o) {
      return extend(this, o);
    }
  }
}
recs.proto = {
  onrevive:function() {},
  //
  add:function(rec) {
    rec.data().i = this.length;
    this.push(rec);
    return this;
  },
  remove:function(e) {
    var i = (e.data && e.data().i) || e;
    if (i != null) {
      this.splice(i, 1);
      this.reindex();
    }
    return this;
  },
  sort:function() {
    var value = this.getSortValue;
    [].sort.call(this, function(a, b) {
      return value(a) > value(b) ? 1 : -1;
    })
    return this.reindex();
  },
  reindex:function() {
    each(this, this, function(rec, i) {
      rec.data().i = i;
    })
    return this;
  },
  getSortValue:function(rec) {
    return rec;
  },
  resetDirty:function() {
    length = this.length;
    each(this, this, function(rec) {
      rec.resetDirty && rec.resetDirty();
    })
    return this;
  },
  isDirty:function() {
    if (length != null) {
      if (length != this.length)
        return true;
      for (var i = 0; i < this.length; i++) {
        if (this[i].isDirty && this[i].isDirty())
          return true;
      }
    }
  }     
}
function recs_map(of, fid/*key*/, proto) {
  var map = {};
  return extend(recs(of, proto), {
    add:function(rec) {
      map[rec[fid]] = rec;
      return recs.proto.add.call(this, rec);
    },
    remove:function(rec) {
      delete map[rec[fid]];
      return recs.proto.remove.call(this, rec);
    },
    get:function(key) {
      return map[key];
    }    
  })
}
/* AJAX */
function ajax(page) {
  var url = '../server/srv-' + page + '.php';
  return {
    post:function(action, /*map*/args, Rec, callback/*(o)*/) {
      $.post(url, extend({'action':action}, args), function(r) {
        var o = JSON.parse(r).r;
        callback(Rec ? Rec.revive(o) : o);
      })
    },
    get:function(action, /*map*/args, Rec, callback/*(o)*/) {
      $.get(url, extend({'action':action}, args), function(r) {
        var o = JSON.parse(r).r;
        callback(Rec ? Rec.revive(o) : o);
      })
    }
  }
}
/* Local storage */
function storage(key) {
  return {
    save:function(obj) {
      localStorage.setItem(key, JSON.stringify(obj));
    },
    fetch:function() {
      var obj = JSON.parse(localStorage.getItem(key));
      return obj;
    },
    erase:function() {
      localStorage.removeItem(key);
    }
  }
}
function /*Rec*/rec_local(key, proto) {
  var store = storage(key);
  return rec(extend({
    save:function() {
      store.save(this);
      return this.resetDirty();
    }
  }, proto)).revive(store.fetch());
}
function /*Rec[]*/recs_local(key, of, proto) {
  var store = storage(key);
  return recs(of, extend({
    save:function() {
      store.save(this);
      return this.resetDirty();
    }
  }, proto)).revive(store.fetch());
}
/* Sort */
function sort(/*[]*/recs, /*fn*/valfn) {
  recs.sort(function(a, b) {
    var va = valfn(a), vb = valfn(b);
    if (va > vb) 
      return 1;
    else if (va < vb)
      return -1;
    else 
      return 0;
  })
  return recs;
}
/** Logging */
function log(o, t) {
  if (t)
    console.groupCollapsed(t);
  console.log(o);
  if (typeof o == 'object') 
    console.log(js(o));
  if (t)
    console.groupEnd();
}
function js(o) {
  return JSON.stringify(o)
}
function isFunction(e) {
  try {  
    return /^\s*\bfunction\b/.test(e);  
  } catch (e) {
    return false;  
  }    
}