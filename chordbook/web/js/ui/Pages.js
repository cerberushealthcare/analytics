/**
 * Chordbook 
 * @author Warren Hornsby
 */
//
/** Persisted page state */
var Me = page_state('chordbook', {
  //
  /*string*/page:null,
  /*SongList*/songlist:null,
  /*ChordList*/chordlist:null,
  /*EditSong*/song:null,
  /*SongBody.Chord*/chord:null,
  /*Chord[]*/inuse:null,
  //
  setSong:function(/*EditSong*/song) {
    this.song = song;
    return this.save();
  },
  saveSong:function() {
    this.songlist.save(this.song);
    return this.save();
  },
  removeSong:function() {
    this.songlist.remove(this.song);
    this.song = null;
    return this.save();
  },
  setChord:function(/*SongBody.Chord*/chord, /*Chord[]*/inuse) {
    this.chord = chord;
    this.inuse = inuse;
    return this.save();
  },
  updateChord:function(/*Chord*/chord) {
    this.chord.apply(chord);
    this.song.body = this.song.songbody().toString();
    return this.saveSong();
  },
  setMode:function(mode) {
    this.song.mode = mode;
    return this.saveSong();
  },
  //
  onrevive:function() {
    this.revive('songlist', SongList);
    this.revive('chordlist', ChordList);
    this.revive('song', EditSong);
    if (this.chord) 
      this.chord = this.song.songbody().chord(this.chord.i);
    this.revive('inuse', Chords);
  }
})
/** Page controllers */
var Pages = {
  //
  pageinit:function(id) {
    var loaded = Pages.loaded;
    if (! loaded) 
      Pages.load();
    var page = Pages[id];
    page && page.pageinit && page.pageinit();
    if (! loaded) 
      Me.restore('Home');
  },
  load:function() {
    Pages.loaded = true;
    $(document).on('pageshow', function(event, ui) {
      var page = Pages[event.target.id];
      page && page.onshow && page.onshow();
    })
    $('.body').maxHeight(43).noBounce();
    $('.body-fixed').maxHeight(0);
    $('a').quick();
    Pages.Home = HomePage($('#Home'))
      .on('new', Pages.Home_onnew)
      .on('chord', Pages.Home_onchord)
      .on('choose', Pages.Home_onchoose);
    Pages.Song = SongPage($('#Song'))
      .on('home', Pages.Song_onhome)
      .on('edit', Pages.Song_onedit)
      .on('editchord', Pages.Song_oneditchord)
      .on('mode', Pages.Song_onmode);
    Pages.EditSong = EditSongPage($('#EditSong'))
      .on('cancel', Pages.EditSong_oncancel)
      .on('save', Pages.EditSong_onsave)
      .on('delete', Pages.EditSong_ondelete);
    Pages.EditChord = EditChordPage($('#EditChord'))
      .on('updatechordlist', Pages.EditChord_onupdatechordlist)
      .on('select', Pages.EditChord_onselect)
      .on('cancel', Pages.EditChord_oncancel)
      .on('exit', Pages.EditChord_onexit);
    Pages.Confirm = Confirm($('#pop-confirm'));
  },
  //
  Home_onnew:function() {
    Me.setSong(EditSong.asNew());
    Me.go('EditSong', 'slide');
  },
  Home_onchord:function() {
    Me.setSong(null);
    Me.setChord(null, []);
    Me.go('EditChord', 'backflip');
  },
  Home_onchoose:function(song) {
    Me.setSong(EditSong.from(song));
    if (Me.song.isEmpty())
      Me.go('EditSong', 'slide');
    else
      Me.go('Song', 'slide');
  },
  Song_onhome:function() {
    Pages.backslideHome();
  },
  Song_onedit:function() {
    Me.go('EditSong', 'flip');
  },
  Song_oneditchord:function(chord, inuse) {
    Me.setChord(chord, inuse);
    Me.go('EditChord', 'flip');
  },
  Song_onmode:function(mode) {
    Me.setMode(mode);
  },
  EditSong_oncancel:function() {
    if (Me.song.isNew() || Me.song.isEmpty())  
      Pages.backslideHome();
    else 
      Me.go('Song', 'backflip');
  },
  EditSong_onsave:function() {
    Pages.Home.reset();
    Pages.Song.reset();
    Me.saveSong();
    if (Me.song.isEmpty())
      Pages.backslideHome();
    else
      Me.go('Song', 'backflip');
  },
  EditSong_ondelete:function() {
    Pages.Home.reset();
    Pages.Song.reset();
    Me.removeSong();
    Me.go('Home', 'backslide');
  },
  EditChord_onupdatechordlist:function() {
    Me.save();
  },
  EditChord_onselect:function(chord) {
    Me.updateChord(chord);
    Pages.Song.update(Me.chord);
    Me.go('Song', 'backflip');
  },
  EditChord_oncancel:function() {
    if (Me.song.isNew() || Me.song.isEmpty()) 
      Pages.backslideHome();
    else
      Me.go('Song', 'backflip');    
  },
  EditChord_onexit:function() {
    Me.go('Home', 'flip');
  },
  backslideHome:function() {
    Me.setSong(null);
    Me.go('Home', 'backslide');
  }
}
/** Home */
var HomePage = function($this) {
  return page($this, {
    onnew:function() {},
    onchoose:function(song) {},
    //
    pageinit:function() {
      this.Head = Header($this.find('.head'))
        .on('clickleft', this, 'onchord')
        .on('clickright', this, 'onnew');
      this.List = HomePage.List($('#body-home'))  
        .on('choose', this);
    },
    reset:function() {
      this.List.reset();
      this.$body.addClass('working');
    },
    load:function() {
      this.List.load(Me.songlist);
      this.$body.removeClass('working');      
    },
    //
    onshow:function() {
      Pages.Song.reset();
      if (! this.List.loaded())
        this.load();
    }
  })
}
HomePage.List = function($container) {
  var $this = $("<ul id='song-list' class='list' data-role='listview' data-theme='c'>")
    .appendTo($container.empty())
  return ui($this, {
    onchoose:function(song) {},
    //
    init:function() {
      this.reset();
      $this.listview({
        autodividers:true,
        autodividersSelector:function(li) {
          return li.data("song").artist;
        }
      })
    },
    reset:function() {
      this._loaded = null;
      $this.empty();
    },
    loaded:function() {
      return this._loaded;
    },
    load:function(songs) {
      this.reset();
      each(songs, this, function(song) {
        var $a = $('<a href="#">' + song.title + '</a>')
          .on('click', this.onchoose.curry(song));
        $this.append($('<li>')
          .data("song", song)
          .append($a));
      })
      $this.listview('refresh');
      this._loaded = true;
      return this;
    }
  })
}
/** View Song */
var SongPage = function($this) {
  return page($this, {
    onhome:function() {},
    onedit:function() {},
    oneditchord:function(/*Chord*/chord, /*Chord[]*/inuse) {},
    onmode:function(mode) {},
    //
    init:function() {
      this.Head = Header($this.find('.head'))
        .on('clickleft', this, 'onhome') 
        .on('clickright', this, 'onedit');
      this.Table = SongPage.Table($('#body-song'))
        .on('edit', this, 'oneditchord')
        .on('mode', this, 'onmode');
      this.reset();
    },
    reset:function() {
      this.song = null;
      this.Table.hide();
      this.$body.addClass('working');
    },
    load:function(/*EditSong*/song) {
      this.song = song;
      this.Table.load(song).show();
      this.$body.removeClass('working');
    },
    update:function(chord) {
      if (chord.data().ChordBox)
        chord.data().ChordBox.draw();
    },
    //
    onshow:function() {
      if (this.song == null && Me.song)
        this.load(Me.song);
    }
  })
}
SongPage.Table = function($container) {
  var $this = $("<div id='songpage-table' class='song'>")
    .appendTo($container.empty());
  return ui($this, {
    onedit:function(chord/*selected*/, chords/*others in use*/) {},
    onmode:function(mode/*display mode*/) {},
    //
    load:function(/*EditSong*/song) {
      this.song = song;
      this.Boxes = [];
      this.draw();
      this.mode(song && song.mode);
      return this;
    },
    show:function() {
      $this.show();
      return this;
    },
    hide:function() {
      $this.hide();
      return this;
    },
    //
    draw:function() {
      $this.empty();
      if (this.song) {
        this.drawHead();
        this.drawLinks();
        this.drawLyricsOnlyTable();
        this.drawChordVerseTables();
      }
    },
    drawHead:function() {
      $this.append("<table class='head'><tr class='head'><td colspan='2'><h2>" + this.song.title + '</h2></tr><td><h3>' + this.song.artist + "</h3></td><td class='tempo'>" + this.song.tempo + "</td></tr></table>");      
    },
    drawLinks:function() {
      var $links = $('<div class="links">').appendTo($this);
      $('<a class="m0" href="#">Complete</a>').appendTo($links).quick()
        .on('click', this.mode.bind(this, 0));
      $('<span> | </span>').appendTo($links);
      $('<a class="m1" href="#">Chords only</a>').appendTo($links).quick()
        .on('click', this.mode.bind(this, 1));
      $('<span> | </span>').appendTo($links);
      $('<a class="m2" href="#">Lyrics only</a>').appendTo($links).quick()
        .on('click', this.mode.bind(this, 2));
      $links = $('<div class="links transpose">').appendTo($this);
      $('<span>Transpose: </span>').appendTo($links);
      $('<a href="#">Up</a>').appendTo($links).quick()
        .on('click', this.transpose.bind(this, 1));
      $('<span> | </span>').appendTo($links);
      $('<a href="#">Down</a>').appendTo($links).quick()
        .on('click', this.transpose.bind(this, -1));
      $('<span> | </span>').appendTo($links);
      $('<a href="#">Reset</a>').appendTo($links).quick()
        .on('click', this.transpose.bind(this, null));
    },
    drawLyricsOnlyTable:function() {
      var lyric;
      var $table = $("<table class='lyrics'>").appendTo($this);
      each(this.song.songbody().lines, function(line) {
        if (line.isComment()) {
          $("<tr class='chords'><td><em>" + line.comment + "</em></td></tr>").appendTo($table);
        } else if (line.isBlank()) {
          $("<tr><td class='blank'></td></tr>").appendTo($table);
        } else {
          lyric = line.lyric();
          if (lyric) 
            $("<tr class='verse'><td>" + lyric + "</td></tr>").appendTo($table);
        }       
      })
    },
    drawChordVerseTables:function() {
      var $div = $("<div class='complete'>").appendTo($this);
      var self = this;
      each(this.song.songbody().lines, this, function(line) {
        self.drawTable(line, $div);
      })      
    },
    drawTable:function(line, $into) {
      $("<table class='song'>")
        .append(this.$ChordRow(line))
        .append(this.$VerseRow(line))
        .appendTo($into);
    },
    $ChordRow:function(line) {
      var $tr = $("<tr class='chords'>")
      if (line.isBlank()) {
        $tr.append("<td class='blank'></td>");
      } else if (line.isComment()) {
        $tr.append("<td><em>" + line.comment + "</em></td>");
      } else {
        each(line.bars, this, function(bar) {
          var cls = bar.chord ? '' : 'nochord';
          var $td = $('<td class="chord ' + cls + '">').appendTo($tr);
          if (bar.chord) {
            this.Boxes.push(SongPage.ChordBox($td, bar.chord)
              .on('edit', this, 'Box_onedit'));
          }
        })
      }
      return $tr;
    },
    $VerseRow:function(line) {
      var $tr = $("<tr class='verse'>");
      each(line.bars, this, function(bar) {
        $('<td>').text(bar.text || '').appendTo($tr);
      })
      return $tr;
    },
    Box_onedit:function(chord) {
      this.onedit(chord, this.song.songbody().distinctChords());
    },
    transpose:function(i) {
      this.song.songbody().transpose(i);
      each(this.Boxes, this, function(Box) {
        Box.draw();
      })
    },
    mode:function(i) {
      if (i == 2)
        this.mode_lyricsonly();
      else if (i == 1)
        this.mode_chordsonly();
      else
        this.mode_complete();
      if (this.song) {
        if (this.song.mode != i) {
          this.song.mode = i;
          this.onmode(i);
        }
      }
      $this.find('.links A').addClass('off');
      $this.find('.links .m' + (i ? i : '0')).removeClass('off');
    },
    mode_complete:function() {
      this.toggleComplete(true);
      this.toggleLyrics(true);
    },
    mode_chordsonly:function() {
      this.toggleComplete(true);
      this.toggleLyrics(false);
    },
    mode_lyricsonly:function() {
      this.toggleComplete(false);
      this.toggleLyrics(true);
    },
    toggleComplete:function(b) {
      $this.find('.complete').toggle(b);
      $this.find('.lyrics').toggle(! b);      
    },
    toggleLyrics:function(b) {
      $this.find('tr.verse').toggle(b);
      $this.find('td.nochord').toggle(b);
      return this;
    },
    MODE_COMPLETE:0,
    MODE_CHORDSONLY:1,
    MODE_LYRICSONLY:2
  })
}
SongPage.ChordBox = extend(function($container, chord) {
  var $this = $("<div class='chordbox'>")
    .appendTo($container);
  return ui($this, {
    onedit:function(chord) {},
    //
    init:function() {
      this.Icon = ChordIcon($this)
        .on('click', Function.defer(this, 'onedit', chord));
      this.$anchor = $('<a href="#"></a>').quick()
        .on('click', this.toggle.bind(this, null))
        .appendTo($this);
      this.toggler = this._Toggler();
      this.draw();
      chord.data().ChordBox = this;
    },
    toggle:function(i) {
      if (! i) {
        this.toggler.next();
        if (this.toggler.edit()) 
          this.onedit(chord);
        else if (this.toggler.allOn()) 
          SongPage.ChordBox.toggleAllOn();
      } else {
        this.toggler.state(i);
      }
      this.draw();
      return this;
    },
    draw:function() {
      this.Icon.hide(this.toggler.off());
      this.$anchor.text(chord.name);
      this.plot();
    },
    //
    plot:function() {
      var shape = chord.shape || Me.chordlist.shape(chord.name);
      if (shape !== this._shape) {
        this._shape = chord.shape;
        this.Icon.plot(shape);
      }
      return this;
    },
    _Toggler:function() {
      if (chord.index() == 0)
        return SongPage.ChordBox.Toggler.off_allon_edit();
      else
        return SongPage.ChordBox.Toggler.off_on_edit();
    }
  })
},{
  toggleAllOn:function(i) {
    $('#songpage-table').find('.chordbox').each(function() {
      $(this).data('ui').toggle(1);
    })
  },
  Toggler:extend(rec({
    //
    state:function(state) {
      if (state === undef)
        return this.states[this.value];
      var i = Array.find(this.states, state);
      if (i > -1)
        this.value = i;
    },
    next:function() {
      if (this.value < this.states.length - 1)
        this.value++;
      var next = this.state();
      if (next < 0) 
        this.value -= next;
      return this.state();
    },
    off:function() {
      return this.state() == 0;
    },
    allOn:function() {
      return this.state() == 2;
    },
    edit:function() {
      return this.state() == 3;
    }
  }),{
    STATE_OFF:0,
    STATE_ON:1,
    STATE_ALL_ON:2,
    STATE_EDIT:3,
    //
    off_allon_edit:function() {
      return this.from([0, 2, 3]);
    },
    off_on_edit:function() {
      return this.from([0, 1, 3]);
    },
    from:function(states) {
      return SongPage.ChordBox.Toggler.make({
        states:states,
        value:0});
    }
  })
})
/** Edit Song */
var EditSongPage = function($this) {
  return page($this, {
    oncancel:function() {},
    onsave:function() {},
    ondelete:function() {},
    //
    init:function() {
      this.Head = Header($this.find('.head'))
        .on('clickleft', this, 'oncancel')
        .on('clickright', this, 'Head_onsave');
      this.Form = EditSongPage.Form($('#edit-song-form'))
        .on('delete', this);
    },
    reset:function() {
      this.Form.reset();
    },
    //
    onbeforeshow:function() {
      this.Form.load(Me.song);
    },
    Head_onsave:function() {
      if (this.Form.validate()) {
        this.Form.apply();
        this.onsave();
      }
    }
  })
}
EditSongPage.Form = function($this) {
  return ui($this, {
    ondelete:function() {},
    //
    init:function() {
      this.$delete = $this.find('#edit-delete')
        .on('click', this.$delete_onclick.bind(this));
    },
    load:function(song) {
      this.reset();
      this.song = song;
      $('#edit-artist').val(song.artist);
      $('#edit-title').val(song.title);
      $('#edit-tempo').val(song.tempo);
      $('#edit-body').val(song.body);
      if (this.song.isNew()) {
        $('#edit-body-box').appendTo('#edit-song-form');
        this.$delete.addClass('hide').removeClass('show');
      } else {
        $('#edit-body-box').prependTo('#edit-song-form');
        this.$delete.addClass('show').removeClass('hide');
      }
    },
    apply:function() {
      this.song.artist = $('#edit-artist').val();
      this.song.title = $('#edit-title').val();
      this.song.tempo = $('#edit-tempo').val();
      this.song.setBody($('#edit-body').val());
    },
    getInvalidFors:function() {
      return $this.find('.errortext').parent().map(function(){return $(this).attr('for')}).get();
    },
    //
    reset:function() {
      $('#edit-body').height(30);
      this.resetValidation();
    },
    $delete_onclick:function() {
      Pages.Confirm.show_asDelete(this.ondelete);
    },
    resetValidation:function() {
      $this.find('.errortext').remove();
    },
    validate:function() {
      var self = this;
      var error;
      this.resetValidation();
      $this.find('.required').each(function() {
        if (String.isEmpty($(this).val())) {
          error = self.addError($(this), 'A value is required.');
        }
      })
      return error == null;
    },
    addError:function($i, text) {
      return $("label[for='" + $i.attr('id') + "']").append("<div class='errortext'>" + text + "</div>");
    }
  })
}
/** Edit Chord */
var EditChordPage = function($this) {
  return page($this, {
    onupdatechordlist:function() {},
    onselect:function(chord) {},
    oncancel:function() {},
    onexit:function() {},  // when in nosong mode
    //
    init:function() {
      this.Namer = EditChordPage.Namer($('#namer'))
        .on('change', this.Namer_onchange.bind(this))
        .on('unlock', this.Namer_onunlock.bind(this));
      this.Guitar = EditChordPage.Guitar($('#guitar'))
        .on('change', this.Guitar_onchange.bind(this))
        .on('unlock', this.Guitar_onunlock.bind(this));
      this.Selector = EditChordPage.Selector($('#selector'))
        .on('choose', this.Selector_onchoose.bind(this));
      this.$top = $this.find('.top');
      this.$save = $('#chord-save').hide()
        .on('click', this.$save_onclick.bind(this));
      this.$unsave = $('#chord-unsave').hide()
        .on('click', this.$unsave_onclick.bind(this));
      this.$default = $('#chord-default').hide()
        .on('click', this.$default_onclick.bind(this));
      this.$nav = $this.find('.nav').hide()
        .on('click', this.$nav_onclick.bind(this));
      this.$navn = $this.find('.navn').hide()
        .on('click', this.$navn_onclick.bind(this));
      this.$cmdbar = $('#ec-cmdbar');
      this.$cmdbar.find('.yes')
        .on('click', this.$ok_onclick.bind(this));
      this.$cmdbar.find('.no') 
        .on('click', Function.defer(this, 'oncancel'));
      this.$cmdbar2 = $('#ec-cmdbar-ro');
      this.$cmdbar2.find('.exit')
        .on('click', Function.defer(this, 'onexit'));
    },
    load:function(/*SongBody.Chord*/chord, /*Chord[]*/inuse, song) {
      this.echord = EditChord.from(chord);
      if (chord == null) {
        this.Namer.reset();
        this.Guitar.reset();
      } else {
        this.Namer.set(this.echord.name());
        this.Guitar.set(this.echord.shape());
      }
      if (inuse) { 
        this.Selector.load(inuse);
        this.song = song;
      }
      this.draw();
    },
    //
    draw:function() {
      this.$save.toggle(!! this.echord.unsaved());
      this.$unsave.toggle(!! this.echord.saved());
      this.$default.toggle(!! this.echord.saved()).toggleClass('check', !! this.echord.fave());
      var shapes = this.echord.shapes();
      var hide = shapes == null || this.echord.unsaved();
      this.$nav.each(function(i) {
        $(this).data('shape', shapes && shapes[i]).toggle(! hide && shapes && shapes[i] != null);
      })
      var names = this.echord.names();
      hide = names == null || this.echord.unsaved();
      this.$navn.each(function(i) {
        $(this).data('name', names && names[i]).toggle(! hide && names && names[i] != null);
      })
      this.$top.toggleClass('middle', this.Guitar.fret() != '1');
      this.$cmdbar.toggle(this.song != null);
      this.$cmdbar2.toggle(this.song == null);
    },
    onbeforeshow:function() {
      this.load(Me.chord, Me.inuse, Me.song);
    },
    $save_onclick:function() {
      this.echord.save();
      this.load(this.echord.chord());
      this.onupdatechordlist();
    },
    $unsave_onclick:function() {
      this.echord.kill();
      this.draw();
      this.onupdatechordlist();
    },
    $default_onclick:function() {
      this.echord.toggleFave();
      this.draw();
      this.onupdatechordlist();
    },
    $ok_onclick:function() {
      this.onselect(this.echord.chord());
    },
    Selector_onchoose:function(chord) {
      this.load(chord);
    },
    Namer_onchange:function() {
      this.echord.name(this.Namer.get());
      if (this.Guitar.opened())
        this.suggestShape();
      else
        this.Guitar.lock();
      this.draw();
    },
    Guitar_onchange:function() {
      this.echord.shape(this.Guitar.get());
      if (this.Namer.opened())
        this.suggestName();
      else
        this.Namer.lock();
      this.draw();
    },
    Namer_onunlock:function() {
      this.suggestName();
      this.draw();
    },
    Guitar_onunlock:function() {
      this.suggestShape();
      this.draw();
    },
    suggestShape:function() {
      this.Guitar.suggest(this.echord.shape(null));
    },
    suggestName:function() {
      this.Namer.suggest(this.echord.name(null));
    },
    $nav_onclick:function(e) {
      var $e = $(e.target);
      var shape = this.echord.shape($e.data('shape'));
      this.Guitar.set(shape);
      this.draw();
    },
    $navn_onclick:function(e) {
      var $e = $(e.target);
      var name = this.echord.name($e.data('name'));
      this.Namer.set(name);
      this.draw();
    }
  })
}
EditChordPage.Selector = function($this) {
  return ui($this, {
    onchoose:function(chord) {},
    //
    load:function(chords) {
      this.reset();
      each(chords, this, function(chord) {
        this.Box(chord).on('click', this, 'onchoose');
      })
    },
    reset:function() {
      $this.find('.box').remove();
      this.Box(null).on('click', this, 'onchoose');
    },
    //
    Box:function(chord) {
      var echord = EditChord.from(chord);
      var $box = $("<div class='box'>").toggleClass('null', echord.empty()).appendTo($this);
      return ui($box, {
        onclick:function(chord) {},
        //
        init:function() {
          var self = this;
          ChordIcon($box).plot(echord.shape()).show().on('click', function() {
            self.onclick(chord);
          })
        }
      })
    }
  })
}
EditChordPage.Namer = extend(function($this) {
  return ui($this, {
    onchange:function() {},
    onunlock:function() {},
    //
    init:function() {
      this.$lock = $this.find('.lock').quick()
        .on('click', this.$lock_onclick.bind(this));
      this.$root = $('#root');
      this.$mod1 = $('#mod1');
      this.$mod2 = $('#mod2');
      this.$slash = $('#slash');
      this.$all = $this.find('.namer-boxes A')
        .on('click', this.$all_onclick.bind(this));
      this.Pop = EditChordPage.Namer.Pop($('#namer-pop'));
      this.reset();
    },
    get:/*string*/function() {
     return this.cname.name();
    },
    empty:function() {
      return this.cname.empty();
    },
    opened:function() {
      return this._state == this.STATE_OPEN;
    },
    locked:function() {
      return this._state == this.STATE_LOCKED;
    },
    reset:function() {
      this.set(null);
    },
    set:function(/*string*/name) {
      this.state(name ? this.STATE_SET : this.STATE_OPEN);
      this.suggest(name);
    },
    suggest:function(/*string*/name) {
      this.cname = ChordName.from(name);
      this.draw();
    },
    lock:function() {
      this.state(this.STATE_LOCKED);
    },
    //
    state:function(i) {
      this._state = i;
      this.$lock.toggle(this.locked());
    },
    draw:function() {
      var parts = this.cname.parts(), i;
      this.$all.text('').removeClass('on off pop').each(function(i) {
        var $e = $(this);
        $e.text(parts[i]).toggleClass('on', parts[i] != '');
      })
      this.$mod1.toggleClass('off', this.$root.text() == '');
      this.$mod2.toggleClass('off', this.$mod1.text() == '');
      this.$slash.toggleClass('off', this.$root.text() == '');
      this.$all.toggleClass('unsaved', Me.chordlist.shape(this.cname.name()) == null);
    },
    $lock_onclick:function() {
      this.state(this.STATE_OPEN);
      this.onunlock();
    },
    $all_onclick:function(e) {
      this.$all.removeClass('selected');
      var $e = $(e.target);
      var id = e.target.id;
      if (this.popping == id || $e.hasClass('off')) {
        this.Pop.close();
        this.popping = null;
      } else {
        $e.addClass('selected');
        this.popping = id;
        var current = this.cname.get(id), self = this; 
        this.Pop.pop(id, current, function(value) {
          $e.removeClass('selected');
          self.popping = null;
          if (value != current) {
            self.cname.set(id, value);
            self.state(this.STATE_SET);
            self.draw();
            self.onchange();
          }
        })
      }
    },
    STATE_OPEN:0,
    STATE_SET:1,
    STATE_LOCKED:2
  })
},{
  Pop:function($this) {
    return ui($this, {
      //
      init:function() {
        this.$curtain = $this.parent()
          .on('click', this.$curtain_onclick.bind(this));
        this.$opts = $this.find('A')
          .on('click', this.$opt_onclick.bind(this));
        this.$panels = $this.find('.panel');
      },
      pop:function(id, value, onchoose/*string*/) {
        this.id = id;
        this.value = value;
        this.onchoose = onchoose;
        this.$panel = $('#o' + id);
        this.show();
      },
      close:function() {
        this.$curtain.removeClass('up');
        $('#guitar').removeClass('blur');
        $('#cmdbars').removeClass('blur');
        $('#selector').removeClass('blur');
        $this.fadeOut('fast');
      },
      //
      show:function() {
        this.$curtain.addClass('up');
        $('#guitar').addClass('blur');
        $('#cmdbars').addClass('blur');
        $('#selector').addClass('blur');
        this.$panels.hide();
        this.$panel.show();
        this.$panel.find('A').removeClass('on').each(function() {
          // todo; turn on current selection
        })
        $this.fadeIn('fast');
      },
      $opt_onclick:function(e) {
        var $e = $(e.target);
        var text = $e.hasClass('null') ? null : $e.attr('value') || $e.text();
        this.close();
        this.onchoose && this.onchoose(text);
      },
      $curtain_onclick:function() {
        this.close();
        this.onchoose && this.onchoose(null);
      }
    })
  }
})
EditChordPage.Guitar = function($this) {
  return ui($this, {
    onchange:function() {},
    onunlock:function() {},
    //
    init:function() {
      this.$lock = $this.find('.lock')
        .on('click', this.$lock_onclick.bind(this));
      this.$pos = $this.find('.pos')
        .on('click', this.$pos_onclick.bind(this));
      this.$oc = $this.find('.ocbar A')
        .on('click', this.$oc_onclick.bind(this));
      this.$strings = $this.find('.string');
      this.$frets = $this.find('.neck TABLE TR').each(function() {
        $(this).data('spots', $(this).find('A'));
      })
      $this.find('.neck TABLE A')
        .on('click', this.$spots_onclick.bind(this));
      this.reset();
    },
    get:/*string*/function() {
      return this.cshape.shape();
    },
    empty:function() {
      return this.cshape.empty();
    },
    fret:function() {
      return this.cshape.fret;
    },
    opened:function() {
      return this._state == this.STATE_OPEN;
    },
    locked:function() {
      return this._state == this.STATE_LOCKED;
    },
    reset:function() {
      this.set(null);
    },
    set:function(/*string*/shape) {
      this.state(shape ? this.STATE_SET : this.STATE_OPEN);
      this.suggest(shape);
    },
    suggest:function(/*string*/shape) {
      this.cshape = ChordShape.from(shape);
      this.draw();
    },
    lock:function() {
      this.state(this.STATE_LOCKED);
    },
    //
    state:function(i) {
      this._state = i;
      this.$lock.toggle(this.locked());
    },
    draw:function() {
      this.$pos.text(this.cshape.fret);
      this.drawOcBar();
      this.drawFrets();
      $this.find('.spot').toggleClass('unsaved', Me.chordlist.name(this.cshape.shape()) == null);
    },
    drawOcBar:function(pos) {
      var pos = this.cshape.pos, i;
      this.$oc.each(function(i) {
        $(this).text(pos[i] == -1 ? 'x' : (pos[i] == 0 ? 'o' : ''));
      })
      this.$strings.each(function(i) {
        $(this).toggleClass('off', pos[i] == -1);
      })
    },
    drawFrets:function(pos) {
      var f, s, spots, $e, cshape = this.cshape;
      this.$frets.each(function(f) {
        spots = cshape.spots(f + 1);
        $(this).data('spots').each(function(s) {
          $(this).attr('class', spots[s] == 1 ? 'spot on' : (spots[s] == 2 ? 'spot b' : ''));
        })
      })
    },
    $lock_onclick:function() {
      this.state(this.STATE_OPEN);
      this.onunlock();
    },
    $pos_onclick:function() {
      // todo
    },
    $oc_onclick:function(e) {
      var $e = $(e.target);
      var string = $e.parent().index();
      this.cshape.toggle(string, 0);
      this.state(this.STATE_SET);
      this.draw();
      this.onchange();
    },
    $spots_onclick:function(e) {
      var $e = $(e.target);
      var string = $e.parent().index();
      var fret = $e.parent().parent().index() + 1;
      this.cshape.toggle(string, fret);
      this.state(this.STATE_SET);
      this.draw();
      this.onchange();
    },
    //
    STATE_OPEN:0,
    STATE_SET:1,
    STATE_LOCKED:2     
  })
}