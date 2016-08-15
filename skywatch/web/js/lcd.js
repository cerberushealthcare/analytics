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
  for (var i = 0; i < sources.length; i++) 
    for (var name in sources[i])
      target[name] = sources[i][name];
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
  }
},{
  is:function(e) {
    try {  
      return /^\s*\bfunction\b/.test(e);  
    } catch (e) {
      return false;  
    }    
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
},{
  is:function(e) {
    return e != null && e.constructor == Array;
  },
  isEmpty:function(array) {
    return array == null || array.length == 0;
  }
})
/* Map */
var Map = {
  is:function(e) {
    return e && typeof e === 'object' && ! Array.is(e);
  }
}
/** User interface components */
function ui(proto) {
  return make({
    on:function(event, toOrContext, to) {
      event = 'on' + event;
      this[event] = Function.is(toOrContext) ? 
        toOrContext :
        function() {
          return toOrContext[to || event].apply(toOrContext, arguments)
        };
      return this;
    }
  }, proto);
}
function page($this, proto) {
  var me = make(ui(), {
    onshow:function() {},
    //
    show:function(trans, reverse) {
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
  $(document).on('pageshow', "#" + $this[0].id, function() {
    me.onshow();
  })
  return me;
}
function page_state(name, proto) {
  return rec_local(name, extend({
    /*string*/page:null,
    //
    restore:function(defaultPage) {
      this.go(this.page || defaultPage);
    },
    go:function(page, trans) {
      this.set('page', page);
      Pages[page].show(trans);
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
      var json;
      var data = {};
      var me = make({
        onrevive:function() {},
        //
        setr:function(fid, proto) {
          if (this[fid])
            this[fid] = proto.revive(this[fid]);
          return this;
        },
        set:function(fid, e) {
          this[fid] = e;
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
      me.onrevive.call(me);
      return me;
    }
  }
}
function recs(of, proto) {
  return {
    revive:function(/*[]*/objs) {
      var length;
      var data = {};
      var us = extend(objs || [], {
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
        data:function() {
          return data;
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
      }, proto);
      if (of) {
        each(objs, this, function(obj, i) {
          us[i] = of.revive(obj);
          us[i].data().i = i; 
        })
      }
      us.onrevive.call(us);
      return us;
    }
  }
}
function recs_map(of, fid/*key*/, proto) {
  var map = {};
  var parent = recs(of, proto);
  return extend(parent, {
    revive:function(objs) {
      var us = parent.revive.call(this, objs);
      return extend(us, {
        add:function(rec) {
          map[rec[fid]] = rec;
          return us.add.call(this, rec);
        },
        remove:function(rec) {
          delete map[rec[fid]];
          return us.remove.call(this, rec);
        },
        get:function(key) {
          return map[key];
        }
      })
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
