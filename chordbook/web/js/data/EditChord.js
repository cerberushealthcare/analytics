var EditChord = rec({
  //
  name:function(s) {
    if (s !== undef) {
      this._name = s ? s : this._list().name(this._shape);
      this._setListProps();
    }
    return this._name;
  },
  shape:function(s) {
    if (s !== undef) { 
      this._shape = s ? s : this._list().shape(this._name);
      this._setListProps();
    }
    return this._shape;
  },
  chord:function() {
    return (this.complete()) ? Chord.from(this._name + ':' + this._shape) : Chord.from(this._name);
  },
  fave:function() {
    return this._fave;
  },
  complete:function() {
    return this._name && this._shape;
  },
  saved:function() {
    return this.complete() && this._shapeIndex > -1 && this._nameIndex > -1;
  },
  unsaved:function() {
    return this.complete() && (this._shapeIndex == -1 || this._nameIndex == -1);
  },
  names:/*['prev','next']*/function() {
    return this._navs(this._names, this._nameIndex);
  },
  shapes:/*['prev','next']*/function() {
    return this._navs(this._shapes, this._shapeIndex);
  },
  empty:function() {
    return this._name == null && this._shape == null;
  },
  reset:function() {
    this._name = null;
    this._shape = null;
    this._setListProps();
  },
  save:function() {
    if (this.complete()) {
      this._list().add(this._name, this._shape);
      this._setListProps();
    }
  },
  kill:function() {
    if (this.complete()) {
      this._list().remove(this._name, this._shape);
      this._setListProps();
    }
  },
  toggleFave:function() {
    var b = ! this._fave;
    var shape = b ? this._shape : null;
    this._list().setFave(this._name, shape);
    this._setListProps();
  },
  //
  _setListProps:function() {
    this._shapes = this._list().shapes(this._name);
    this._shapeIndex = Array.find(this._shapes, this._shape);
    this._names = this._list().names(this._shape);
    this._nameIndex = Array.find(this._names, this._name);
    this._fave = this._list().isFave(this._name, this._shape);
  },
  _list:function() {
    return EditChord._list; 
  },
  _navs:function(p, i) {
    if (p) {
      var a = [];
      a.push(i > 0 ? p[i - 1] : null);
      a.push(p.length > i + 1 ? p[i + 1] : null);
      return a;
    }
  }
}).statics({
  from:function(/*Chord*/chord) {
    var me = this.make();
    if (chord == null || chord.unknown()) {
      me.reset();
    } else {
      me._name = chord.name;
      if (String.isEmpty(chord.shape)) {
        me.shape(me._list().shape(chord.name));
      } else {
        me.shape(chord.shape);
      }
    }
    return me;
  },
  _attach:function(chordlist) {
    EditChord._list = chordlist;
  }
})