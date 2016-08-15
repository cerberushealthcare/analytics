var EditSong = rec({
  /*
   artist
   title
   body
   tempo
   mode
   i
   */  
  isNew:function() {
    return this.i == -1;
  },
  isEmpty:function() {
    return String.isEmpty(this.body);
  },
  setBody:function(body) {
    this.body = body;
    this.data().songbody = SongBody.from(this);
    return this;
  },
  songbody:/*SongBody*/function() {
    return this.data().songbody;
  },
  //
  onrevive:function() {
    this.setBody(this.body);
  }
}).statics({
  from:function(/*Song*/song) {
    return EditSong.revive(song)
      .set('i', song.index());
  },
  asNew:function() {
    return EditSong.revive()
      .set('i', -1);
  }
})
/** Song body */
var SongBody = rec({
  /*
   Line[] lines
   */
  toString:function() {
    var a = [];
    each(this.lines, this, function(line) {
      a.push(String.trim(line.toString()));
    })
    a = a.join('\n');
    return String.trim(a);
  },
  chords:/*SongBody.Chord[]*/function() {
    return this.data().chords;
  },
  chord:/*SongBody.Chord*/function(index) {
    return this.data().chords[index];
  },
  distinctChords:/*Chord[]*/function() {
    var map = Map.from(this.chords(), 'name');
    delete map['?'];
    return Map.values(map);
  },
  transpose:function(i) {
    each(this.chords(), this, function(chord) {
      chord.transpose(i);
    })
  }
}).statics({
  from:function(/*EditSong*/song) {
    var me = this.make({
      lines:SongBody.Line.all(song && song.body)
    })
    me.data().chords = this._chords(me);
    return me;
  },
  //
  _chords:function(body) {
    var chords = [];
    each(body.lines, this, function(line) {
      var c = line.chords();
      if (c.length)
        chords = chords.concat(c);
    })
    var ci = 0;
    each(chords, this, function(chord) {
      chord._setIndex(ci++);
    })
    return chords;
  }
})
SongBody.Line = rec({
  /*
   type
   Bar[] bars
   comment
   source (e.g. '/rc')
   */
  isBlank:function() {
    return this.type == SongBody.Line.TYPE_BLANK;
  },
  isComment:function() {
    return this.type == SongBody.Line.TYPE_COMMENT;
  },
  isBars:function() {
    return this.type == SongBody.Line.TYPE_BARS;
  },
  chords:/*Chord[]*/function() {
    var chords = [];
    each(this.bars, this, function(bar) {
      if (bar.chord)
        chords.push(bar.chord);
    })
    return chords;
  },
  lyric:/*string*/function() {
    return SongBody.Bar.lyric(this.bars);
  },
  toString:function() {
    if (this.isBars())
      return SongBody.Bar.toString(this.bars);
    else
      return this.source; 
  },
  index:function() {
    return this.data().i;
  },
}).statics({
  TYPE_BARS:0,
  TYPE_BLANK:1,
  TYPE_SOC:2, /*start of chorus*/
  TYPE_EOC:3, /*end of chorus*/
  TYPE_COMMENT:4,
  //
  all:function(/*string*/body) {
    var us = [], i;
    if (body) {
      var texts = body.split('\n');
      each(texts, this, function(text, i) {
        var line = this.from(text, i);
        line && us.push(line);
      })
    }
    return us;
  },
  from:function(/*string*/source, index) {
    var me, text = this.fix(source);
    if (String.isEmpty(text))
      me = this.as(this.TYPE_BLANK);
    else if (text.substr(0, 1) == '{')
      me = this.asDirective(text.substr(1, text.length - 2));
    else
      me = this.asBars(text);
    me.source = source;
    me.data().i = index;
    return me;
  },
  asBars:function(text) {
    var me = this.make();
    me.type = SongBody.Line.TYPE_BARS;
    me.bars = SongBody.Bar.all(text, me);
    return me;
  },
  asDirective:function(text) {
    var dir = text.toLowerCase();
    switch (dir) {
    case 'soc':
    case 'start_of_chorus':
      return this.as(this.TYPE_SOC);
    case 'eoc':
    case 'end_of_chorus':
      return this.as(this.TYPE_EOC);
    }
    if (dir.substr(0, 8) == 'comment:') 
      return this.as(this.TYPE_COMMENT, text.split(':')[1]);
  },
  as:function(type, comment) {
    return this.make({
      type:type,
      comment:comment
    })
  },
  fix:function(text) {
    text = this.fixChords(text);
    text = this.fixComments(text, 'v', 'Verse');
    text = this.fixComments(text, 'c', 'Chorus');
    text = this.fixComments(text, 'b', 'Bridge');
    text = this.fixComments(text, 'i', 'Intro');
    text = this.fixComments(text, 'o', 'Outro');
    text = this.fixComments(text, 'n', 'Interlude');
    return String.trim(text);
  },
  fixComments:function(text, from, to, repeat) {
    for (var i = 1; i < 4; i++) 
      text = this.fixComment(text, '/' + from + i, to + ' ' + i);
    text = this.fixComment(text, '/' + from, to);
    if (repeat)
      return text;
    else 
      return this.fixComments(text, 'r' + from, 'Repeat ' + to, 1);
  },
  fixComment:function(text, from, to) {
    return text.split(from).join('{comment:' + to + '}');
  },
  fixChords:function(text) {
    var a = text.split('@');
    for (var i = 1; i < a.length; i++) {
      var b = a[i].split(' ');
      if (b[0] == '')
        b[0] = '?';
      b[0] = '[' + b[0] + ']';
      a[i] = b.join(' ');
    }
    a = a.join('');
    a = a.replace(/] /g, ']');
    return a;
  }
})
SongBody.Bar = rec({
  /*
   Chord chord
   text
   */
  isEmpty:function() {
    return this.chord == null && this.text == null;
  },
  toString:function() {
    var a = [];
    if (this.chord) 
      a.push('@' + this.chord.toString() + ' ');
    a.push(this.text);
    return a.join('');
  },
  parent:function() {
    return this.data().parent;
  },
}).statics({
  all:function(text, parent) {
    var us = [], me;
    var chunks = text.split('[');
    each(chunks, this, function(chunk) {
      me = this.from(chunk, parent);
      if (! me.isEmpty())
        us.push(me);
    })
    return us;
  },
  from:function(chunk, parent) {
    var me = this.make();
    var a = chunk.split(']');
    if (a.length > 1)
      me.chord = SongBody.Chord.from(a[0], me);
    me.text = String.nullify(a[a.length - 1]);
    me.data().parent = parent;
    return me;
  },
  toString:function(bars) {
    var a = [];
    each(bars, this, function(bar) {
      a.push(bar.toString());
    })
    return a.join('');
  },
  lyric:function(bars) {
    var a = [], text;
    each(bars, this, function(bar) {
      text = String.trim(bar.text);
      if (! String.isEmpty(text))
        a.push(text);
    })
    return String.nullify(a.join(' '));    
  }
})
SongBody.Chord = Chord.extend({
  /*
   name
   shape
   i
   */
  index:function() {
    return this.i;
  },
  parent:function() {
    return this.data().parent;
  },
  apply:function(chord) {
    this.name = chord.name;
    this.shape = chord.shape;
    return this;
  },
  transpose:function(i) {
    var cn = ChordName.from(this.name);
    var p = this.data().pretranspose;
    if (p == null) {
      p = {
        rootCk:cn.rootCk(),
        name:this.name,
        shape:this.shape
      }
      this.data().pretranspose = p;
    }
    if (i === null) {
      this.name = p.name;
      this.shape = p.shape;
    } else {
      cn.transpose(i);
      if (cn.rootCk() == p.rootCk) {
        this.name = p.name;
        this.shape = p.shape;
      } else {
        this.name = cn.name();
        this.shape = null;
      }
    }
  },
  //
  _setIndex:function(i) {
    this.i = i;
    return this;
  }
}).statics({
  from:function(s, parent) {
    var me = this.make().set(s);
    me.data().parent = parent;
    return me;
  }
})