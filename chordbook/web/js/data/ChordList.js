/** Chord list */
var ChordListItem = recs(null, {
  onrevive:function() {
    this.shape = this[0]; /*'X13331'*/
    this.ck = this[1];    /*'AB' (for Bb)*/
    this.favorite = this[2];
  },
  setFave:function(b) {
    if (b) {
      if (this.length == 2)
        this.push(true);
    } else {
      this.splice(2, 1);
    }
    this.favorite = b;
  }
}).statics({
  from:function(shape, name) {
    var ck = ChordName.nameToCk(name);
    return ChordListItem.revive([shape, ck]);
  }
})
var ChordList = recs(ChordListItem, {
  find:function(name, shape) {
    if (name && shape) {
      var ck = ChordName.nameToCk(name), item;
      for (var i = 0; i < this.length; i++) {
        item = this[i];
        if (item.shape == shape && item.ck == ck)
          return item;
      }
    }
  },
  shapes:function(name) {
    if (name && name != '?') {
      var ck = ChordName.nameToCk(name);
      var shapes = this.data().cache[ck];
      if (shapes === undef) {
        shapes = [];
        each(this, this, function(item) {
          if (item.ck == ck) {
            shapes.push(item.shape);
            if (item.favorite)
              shapes.favorite = item.shape;
          }
        })
        this.data().cache[ck] = sort(shapes, this._calcShapeValue);
      }
      return shapes;
    }
  },
  shape:function(name) {
    var s = this.shapes(name);
    if (s) 
      return s.favorite || s[0];
  },
  setFave:function(name, shape) {
    var ck = ChordName.nameToCk(name);
    if (ck) {
      each(this, this, function(item) {
        if (item.ck == ck) 
          item.setFave(item.shape == shape);
      })
      this._clearShapeCache(ck);
    }
  },
  isFave:function(name, shape) {
    var item = this.find(name, shape);
    return item && item.favorite;
  },
  names:function(shape) {
    var names = this.data().cache[shape];
    if (names === undef) {
      names = [];
      each(this, this, function(item) {
        if (item.shape == shape) {
          each(ChordName.ckToNames(item.ck), this, function(name) {
            names.push(name);
          })
        }
      })
      this.data().cache[shape] = names.sort();
    }
    return names;
  },
  name:function(shape) {
    var n = this.names(shape);
    return n && n[0];
  },
  add:function(name, shape) {
    var item = ChordListItem.from(shape, name);
    this.__super.add.call(this, item);
    this._clearCache(item);
  }, 
  remove:function(name, shape) {
    var e = this.find(name, shape);
    if (e) {
      this.__super.remove.call(this, e);
      this._clearCache(e);
    }
  },
  //
  onrevive:function() {
    this.data().cache = {};
    EditChord._attach(this);
  },
  _calcShapeValue:function(shape) {
    var j, v = 0, lo = 999, hi = 0, x = 0, f;
    for (var i = 0; i < shape.length; i++) {
      j = parseInt(shape.substr(i, 1), 10);
      if (isNaN(j)) {
        x++;
      } else {
        v += j;
        if (j && j < lo)
          lo = j;
        if (j && j > hi)
          hi = j;
      }
    }
    if (x < 3)
      x = 0;
    f = lo;
    if (hi <= 4)
      f = 1;
    return f * 1000 + x * 100 + v;
  },
  _clearCache:function(item) {
    this._clearNameCache(item.shape);
    this._clearShapeCache(item.ck);
  },
  _clearNameCache:function(shape) {
    delete this.data().cache[shape];
  },
  _clearShapeCache:function(ck) {
    delete this.data().cache[ck];
  }
}).statics({
  revive:function(list) {
    if (Array.isEmpty(list))
      list = ChordList_Init;
    return ChordList.__super.revive.call(this, list);
  },
  _attach:function(shapedefaults) {
    ChordList._defaults = shapedefaults;
  }
})
/** Chord */
var Chord = rec({
  /*
   name
   shape
   */
  toString:function() {
    if (this.shape)
      return this.name + ':' + this.shape;
    else
      return this.name;
  },
  set:function(s/*e.g. 'Em', 'Bb:688766', '?'*/) {
    var a = s.split(':');
    this.name = a[0];
    this.shape = a.length ? a[1] : null;
    return this;
  },
  unknown:function() {
    return this.name == '?';
  }
}).statics({
  from:function(s) {
    var me = this.make();
    me.set(s || '?');
    return me;
  }
})
var Chords = recs(Chord, {
})
/** Chord name */
var ChordName = rec({
  /*
   root   'D#'
   minor  'm'
   mod1   'aug'
   mod2   '#9'
   slash  'G#'
   */
  empty:function() {
    return this.root == null && this.minor == null && this.mod1 == null && this.mod2 == null && this.slash == null;
  },
  name:function() {
    return this.parts().join('');
  },
  parts:function() {
    var a = [];
    a.push(String.denull(this.rootMinor()));
    a.push(String.denull(this.mod1));
    a.push(String.denull(this.mod2));
    a.push(this.slash ? '/' + this.slash : '');
    return a;
  },
  transpose:function(/*int*/steps) {
    this.root = ChordName.transpose(this.root, steps);
    this.slash = ChordName.transpose(this.slash, steps);
  },
  rootCk:function() {
    return ChordName.nameToCk(this.root);
  },
  rootMinor:function() {
    if (this.root)
      return this.minor ? this.root + this.minor : this.root;
  },
  get:function(fid) {
    switch (fid) {
      case 'root':
        return this.rootMinor();
      default:
        return this[fid];
    }
  },
  set:function(fid, s) {
    s = String.nullify(s);
    switch (fid) {
      case 'root':
        this.setRootMinor(s);
        break;
      case 'mod1':
        this.setMod1(s);
        break;
      case 'mod2':
        this.setMod2(s);
        break;
      case 'slash':
        this.setSlash(s);
        break;
    }
  },
  setRootMinor:function(s) {
    var a = s.split('m');
    var root = a[0];
    var minor = a.length > 1 ? 'm' : null;
    if (this.root != root || this.minor != minor) {
      this.root = root;
      this.minor = minor;
      this.mod1 = null;
      this.mod2 = null;
    }
  },
  setMod1:function(mod1) {
    if (this.mod1 != mod1) {
      this.mod1 = mod1;
      this.mod2 = null;
    }
  },
  setMod2:function(mod2) {
    this.mod2 = mod2;
  },
  setSlash:function(slash) {
    this.slash = slash;
  },
  //
  _extractRoot:function(name) {
    this.root = this._extract(ChordName._NOTES, name);
  },
  _extractMinor:function() {
    if (this.data().from.substr(0, 3) != 'maj')
      this.minor = this._extract(ChordName._MINOR);
  },
  _extractMod1:function() {
    this.mod1 = this._extract(ChordName._MOD1);
  },
  _extractMod2:function() {
    this.mod2 = this._extract(ChordName._MOD2);
  },
  _extractSlash:function() {
    if (this._extract(ChordName._SLASH))
      this.slash = this._extract(ChordName._NOTES);
  },
  _extract:function(values, from/*=null*/) {
    if (from)
      this.data().from = from;
    for (var i = 0, v; i < values.length; i++) {
      v = values[i];
      if (this.data().from.substr(0, v.length) == v) {
        this.data().from = this.data().from.substr(v.length);
        return v;
      }      
    }
  }
}).statics({
  //
  from:function(/*string*/name) {
    var me = this.make();
    if (name) {
      me._extractRoot(name);
      me._extractMinor();
      me._extractMod1();
      me._extractMod2();
      me._extractSlash();
    }
    return me;
  },
  transpose:function(name/*'A#'*/, steps/*half (int)*/) {
    if (name) {
      var ck = this.nameToCk(name);
      var i = this._CKS.find(ck) + steps;
      if (i < 0)
        i += 12;
      else if (i > 11)
        i -= 12;
      ck = this._CKS[i];
      var n = ck.length == 2 && name.substr(1) == 'b' ? 1 : 0;
      return this._makeName(this._CKS[i], n);
    }
  },
  nameToCk:function(name) {
    return name && name
      .replace(/A#/g, 'AB')
      .replace(/Bb/g, 'AB')
      .replace(/C#/g, 'CD')
      .replace(/Db/g, 'CD')
      .replace(/D#/g, 'DE')
      .replace(/Eb/g, 'DE')
      .replace(/F#/g, 'FG')
      .replace(/Gb/g, 'FG')
      .replace(/G#/g, 'GA')
      .replace(/Ab/g, 'GA');      
  },
  ckToNames:function(ck) {
    var n1 = this._makeName(ck, 0);
    var n2 = this._makeName(ck, 1);
    if (n2)
      return [n1, n2];
    else
      return [n1];
  },
  _makeName:function(ck, i) {
    if (i == 0) {
      return ck
        .replace(/AB/g, 'A#')
        .replace(/CD/g, 'C#')
        .replace(/DE/g, 'D#')
        .replace(/FG/g, 'F#')
        .replace(/GA/g, 'G#');      
    } else {
      var name = ck
        .replace(/AB/g, 'Bb')
        .replace(/CD/g, 'Db')
        .replace(/DE/g, 'Eb')
        .replace(/FG/g, 'Gb')
        .replace(/GA/g, 'Ab');
      if (name != ck)
        return name;
    }    
  },  
  //
  _MINOR:['m'],
  _MOD1:['aug','dim','sus4','sus2','5','6','7','maj7','9','maj9','add9','11','13','maj13'],
  _MOD2:['#11#9','#11','#9sus4','#9','7#9','7','/9','9','b5','b9','sus2','sus4'],
  _SLASH:['/'],
  _NOTES:['A#','Bb','C#','Db','D#','Eb','F#','Gb','G#','Ab','A','B','C','D','E','F','G'],
  _CKMAP:{'A#':'AB','Bb':'AB','C#':'CD','Db':'CD','D#':'DE','Eb':'DE','F#':'FG','Gb':'FG','G#':'GA','Ab':'GA','A':'A','B':'B','C':'C','D':'D','E':'E','F':'F','G':'G'},
  _CKS:['C','CD','D','DE','E','F','FG','G','GA','A','AB','B']
})
var ChordShape = rec({
  /*
   fret
   pos[0..5] //-1=closed 0=open #=finger relative to fret
   barre  
     pos
     start //string 1-6
     end   //string 1-6
   */
  shape:/*string*/function() {
    var s = '';
    var off = this.fret - 1;
    for (var i = 0, j; i < 6; i++) {
      j = this.pos[i];
      if (j == -1)
        s += 'X';
      else if (j == 0)
        s += '0';
      else
        s += (j + off);
    }
    return s;
  },
  empty:function() {
    return this.shape() == '000000';
  },
  setPos:function(string/*0-5*/, pos/*relative to fret*/) {
    this.pos[string] = pos;
    this._setBarre();
  },
  toggle:function(string, pos) { 
    var current =  this.pos[string];
    if (pos == 0) {
      pos = (current == 0) ? -1 : 0;
    } else {
      if (current == pos) {
        if (this.barre && string >= this.barre.start && string < this.barre.end - 1)
          pos = this.barre.pos;
        else
          pos = 0;
      }
    }
    this.setPos(string, pos);
  },
  setFret:function(fret) {
    this.fret = fret;
  },
  reset:function() {
    this._setShape('000000');
  },
  spots:/*int[6]*/function(pos/*relative to fret*/) { /*e.g [1,2,2,2,1,0] 0=none, 1=finger, 2=bar*/
    var a = [], i;
    if (this.barre && this.barre.pos == pos) {
      for (i = 1; i <= 6; i++) {
        if (i == this.barre.start || i == this.barre.end)
          a.push(1);
        else if (i > this.barre.start && i < this.barre.end)
          a.push(2);
        else
          a.push(0);
      }
    } else {
      for (i = 0; i <= 5; i++) {
        if (this.pos[i] == pos)
          a.push(1);
        else
          a.push(0);
      }
    }
    return a;
  },
  //
  _setShape:function(shape) {
    var pos = [], s;
    for (var i = 0; i < 6; i++) {
      s = shape.substr(i, 1);
      pos.push(s == 'X' ? -1 : parseInt(s, 10));
    }
    this._hilo(pos);
    var fret = pos.lo;
    if (pos.hi <= 4)
      fret = 1;
    if (fret > 1) {
      var off = fret - 1;
      for (var i = 0; i < 6; i++) {
        if (pos[i] > 0)
          pos[i] -= off;
      }
    }
    this.pos = pos;
    this.fret = fret;
    this._setBarre();
  },
  _setBarre:function() {
    this.barre = null;
    var pos = this.pos;
    this._hilo(pos);
    var lo = pos.lo, hi = pos.hi;
    if (hi) {
      var barre = {};
      barre.pos = lo;
      for (var i = 0; i < 6; i++) {
        if (pos[i] == lo) {
          if (! barre.start) 
            barre.start = i + 1;
          else  
            barre.end = i + 1;
        } else if (pos[i] < lo) {
          if (barre.start)
            break;
        }
      }
      if (barre.end == 6) { // if (barre.end)
        barre.len = barre.end - barre.start + 1;
        if (barre.len > 3) 
          this.barre = barre;
      }
    }
  },
  _hilo:function(pos) {
    pos.hi = 0;
    pos.lo = 999;
    for (var i = 0; i < 6; i++) {
      if (pos[i] > 0) {
        if (pos[i] < pos.lo)
          pos.lo = pos[i];
        if (pos[i] > pos.hi)
          pos.hi = pos[i];
      }
    }
    if (pos.lo == 999)
      pos.lo = 0;
  }
}).statics({
  from:function(/*string*/shape) {
    var me = this.make();
    if (shape)
      me._setShape(shape);
    else
      me.reset();
    return me;
  }
})