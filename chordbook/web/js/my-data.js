Song = rec({
  /*
   i
   artist
   title
   body
   */
  getSortValue:function() {
    return (this.artist + '|' + this.title).toUpperCase();
  },
  isNew:function() {
    return this.i == null;
  },
  isEmpty:function() {
    return String.isEmpty(this.body);
  },
  //
  asNew:function() {
    return this.revive();
  }
})
SongList = (function() {
  var parent = recs_local('song-list', Song);
  return make(parent, {
    fetch:function() {
      var us = parent.fetch.call(this);
      if (us.length == 0)
        us = this.revive([{artist:"Neil Diamond",title:"Solitary Man",body:"[Em]Melinda was [Am]mine 'til the [G]time that I [Em]found her\n[G]Holding [Am]Jim, [G]loving [Am]him\nThen Sue came along, loved me strong, that's what I thought\nMe and Sue, that died too\n[G]Don't know that I [C]will, but un-[G]til I can [D]find me\nA girl who will[C]stay and won't[G]play games be-[D]hind me\nI'll be what I [Em]am, [D]Solitary [Em]man\nI've had it to here being where love's a small word\nA part time thing, paper ring\nI know it's been done having one girl to love you\nRight or wrong, weak or strong"},{artist:"Beatles",title:"I'm Only Sleeping",body:null},{artist:"Guster",title:"Amsterdam",body:null},{artist:"Beatles",title:"Across The Universe",body:null}]);
      return us;
    },
    add:function(song) {
      parent.add.call(this, song);
      return this.save();
    },
    remove:function(song) {
      if (! song.isNew())
        parent.remove.call(this, song.i);
      return this.save();
    },
    save:function() {
      this.sort();
      parent.save.call(this);
      return this;
    },
    //
    onrevive:function() {
      this.sort();
    },
    sort:function() {
      parent.sort.call(this);
      this.reindex();
      return this;
    }
  })
})();
//
SongBody = {
  /*
   .Line[] lines
   */
  from:function(/*Song*/song) {
    SongBody.nextChordId = 0;
    var me = make(this);
    me.lines = SongBody.Line.all(song.body);
    return me;
  }
}
SongBody.Line = {
  /*
   .type
   .Bar[] bars
   .comment
   */
  TYPE_BARS:0,
  TYPE_BLANK:1,
  TYPE_SOC:2, /*start of chorus*/
  TYPE_EOC:3, /*end of chorus*/
  TYPE_COMMENT:4,
  //
  isBlank:function() {
    return this.type == this.TYPE_BLANK;
  },
  isComment:function() {
    return this.type == this.TYPE_COMMENT;
  },
  //
  all:function(body) {
    var us = [];
    if (body) {
      var texts = body.split('\n');
      each(texts, this, function(text) {
        var line = this.from(text);
        line && us.push(line);
      })
    }
    return us;
  },
  from:function(text) {
    text = this.fix(text);
    if (String.isEmpty(text))
      return this.as(this.TYPE_BLANK);
    else if (text.substr(0, 1) == '{')
      return this.asDirective(text.substr(1, text.length - 2));
    else
      return this.asBars(text);
  },
  asBars:function(text) {
    var me = make(this);
    me.type = this.TYPE_BARS;
    me.bars = SongBody.Bar.all(text);
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
    var me = make(this);
    me.type = type;
    me.comment = comment;
    return me;
  },
  fix:function(text) {
    text = text.split('@').join('[?]'); 
    return String.trim(text);
  }
}
SongBody.Bar = {
  /*
   Chord chord
   text
   */
  empty:function() {
    return this.chord == null && this.text == null;
  },
  //
  all:function(text) {
    var us = [], me;
    var chunks = text.split('[');
    each(chunks, this, function(chunk) {
      me = this.from(chunk);
      if (! me.empty())
        us.push(me);
    })
    return us;
  },
  from:function(chunk) {
    var me = make(this);
    var a = chunk.split(']');
    me.chord = a.length > 1 ? Chord.from(a[0]) : null;
    me.text = String.nullify(a[a.length - 1]);
    return me;
  }
}
//
Chord = {
  /*
   id
   name
   code
   */
  from:function(s) {
    var me = make(this);
    me.id = SongBody.nextChordId++;
    me.name = s;
    return me;
  }
}