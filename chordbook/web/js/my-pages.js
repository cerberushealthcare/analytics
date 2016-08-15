var Pages = {
  start:function() {
    $('.body').maxHeight(43).noBounce();
    $('a').noTapDelay();
    Pages.Home = HomePage($('#home'))
      .on('new', Pages.Home_onnew)
      .on('choose', Pages.Home_onchoose);
    Pages.Song = SongPage($('#song'))
      .on('home', Pages.Song_onhome)
      .on('edit', Pages.Song_onedit)
      .on('editchord', Pages.Song_oneditchord);
    Pages.EditSong = EditSongPage($('#edit-song'))
      .on('cancel', Pages.EditSong_oncancel)
      .on('save', Pages.EditSong_onsave)
      .on('delete', Pages.EditSong_ondelete);
    Pages.Confirm = Confirm($('#pop-confirm'));
    Pages.load(SongList.fetch());
  },
  load:function(songlist) {
    Pages.songlist = songlist;
    Pages.Home.show(songlist);
  },
  //
  Home_onnew:function() {
    Pages.song = Song.asNew();
    Pages.EditSong.show(Pages.song);
  },
  Home_onchoose:function(song) {
    Pages.song = song;
    if (song.isEmpty())
      Pages.EditSong.show(song);
    else
      Pages.Song.show(song);
  },
  Song_onhome:function() {
    Pages.Home.show_backslide(Pages.songlist);
  },
  Song_onedit:function() {
    Pages.EditSong.show_flip(Pages.song);
  },
  Song_oneditchord:function(chord) {
    // TODO
  },
  EditSong_oncancel:function() {
    if (Pages.song.isNew() || Pages.song.isEmpty()) 
      Pages.Home.show_backslide(Pages.songlist);
    else
      Pages.Song.show_backflip(Pages.song);
  },
  EditSong_onsave:function(song) {
    if (song.isNew()) 
      Pages.songlist.add(song);
    else 
      Pages.songlist.save();
    if (song.isEmpty())
      Pages.Home.show_backslide(Pages.songlist);
    else
      Pages.Song.show_backflip(song);
  },
  EditSong_ondelete:function() {
    Pages.songlist.remove(Pages.song);
    Pages.Home.show_backslide(Pages.songlist);
  }
}
/** Home */
var HomePage = function($this) {
  return page($this, {
    onnew:function() {},
    onchoose:function(song) {},
    //
    init:function() {
      this.Head = Header($this.find('.head'))
        .on('clickright', this, 'onnew');
      this.List = HomePage.List($('#body-home'))  
        .on('choose', this);
    },
    //
    onshow:function(songs) {
      this.load(songs);
    },
    load:function(songs) {
      this.List.load(songs);
    }
  })
}
HomePage.List = function($container) {
  var $this = $("<ul id='song-list' class='list' data-role='listview' data-theme='c'>")
    .appendTo($container.empty())
  return ui({
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
      $this.empty();
    },
    load:function(songs) {
      this.reset();
      each(songs, this, function(song) {
        var a = $('<a>' + song.title + '</a>')
          .noTapDelay()
          .on('click', this.onchoose.curry(song));
        $this.append($('<li>').data("song", song).append(a));
      })
      $this.listview('refresh');
      return this;
    }
  })
}
/** View Song */
var SongPage = function($this) {
  return page($this, {
    onhome:function() {},
    onedit:function() {},
    oneditchord:function(chord) {},
    //
    init:function() {
      this.Head = Header($this.find('.head'))
        .on('clickleft', this, 'onhome') 
        .on('clickright', this, 'onedit');
      this.Table = SongPage.Table($('#body-song'))
        .on('edit', this, 'oneditchord');
    },
    //
    onshow:function(song) {
      this.song = song;
      this.load(song);
    },
    load:function(song) {
      this.Table.load(song);
    }
  })
}
SongPage.Table = function($container) {
  var $this = $("<div class='song'>")
    .appendTo($container.empty());
  return ui({
    onedit:function(chord) {},
    //
    load:function(song) {
      this.song = song;
      this.body = SongBody.from(song);
      this.draw();
    },
    //
    draw:function() {
      $this.empty();
      $this.append("<table class='song'><tr class='head'><td><h2>" + this.song.title + '</h2><h3>' + this.song.artist + '</h3></td></tr></table>');
      each(this.body.lines, this, this.drawTable);
    },
    drawTable:function(line) {
      $("<table class='song'>")
        .append(this.$ChordRow(line))
        .append(this.$VerseRow(line))
        .appendTo($this);
    },
    $ChordRow:function(line) {
      var $tr = $("<tr class='chords'>")
      if (line.isBlank()) {
        $tr.append("<td class='blank'></td>");
      } else if (line.isComment()) {
        $tr.append("<td><b>" + line.comment + "</b></td>");
      } else {
        each(line.bars, this, function(bar) {
          var $td = $('<td>').appendTo($tr);
          if (bar.chord)
            SongPage.ChordBox($td, bar.chord)
              .on('edit', this);
        })
      }
      return $tr;
    },
    $VerseRow:function(line) {
      var $tr = $("<tr class='verse'>");
      each(line.bars, this, function(bar) {
        $('<td>').text(bar.text).appendTo($tr);
      })
      return $tr;
    }
  })
}
SongPage.ChordBox = function($container, chord) {
  var $this = $("<div class='chordbox'>")
    .appendTo($container);
  return ui({
    onedit:function(id) {},
    //
    init:function() {
      this.$chordAnchor = $("<a href=''>").text(chord.name)
        .on('click', Function.defer(this, 'onedit', chord.id));
      $this.append(this.$chordAnchor);
    }
  })
}
var ChordIcon = function($container) {
  var h = ["<div class='chord-icon'><table class='chord-icon'>"];
  for (var r = 0; r < 4; r++) {
    h.push('<tr>');
    for (var c = 0; c < 5; c++)
      h.push('<td></td>');
    h.push('</tr>');
  }
  h.push("</table></div>");
  var $this = $(h.join('')).appendTo($container.empty());
  return ui({
    init:function() {
      this.closed(1);
      this.bullet(2, 3);
      this.bullet(3, 2);
      this.open(4);
      this.bullet(5, 1);
      this.open(6);
      this.fret(5);
    },
    //
    fret:function(text) {
      this.pos(39, 3, text, 'fret');
    },
    open:function(string) {
      var x = (string - 1) * 6 + 2;
      var y = -16;
      this.pos(x, y, '&#3866;', 'openclose');
    },
    closed:function(string) {
      var x = (string - 1) * 6 + 2;
      var y = -16;
      this.pos(x, y, '&#3869;', 'openclose');
    },
    bullet:function(string, fret) {
      var x = (string - 1) * 6 + 2;
      var y = (fret - 1) * 8 - 2;
      this.pos(x, y, '&bull;', 'bullet');
    },
    pos:function(x, y, text, cls) {
      var left = $this.position().left + x;
      var top = $this.position().top + y;
      $("<div class='" + (cls) + "'>" + text + "</div>").appendTo($this).css({top:top,left:left});
    }
  })
}
/** Edit Song */
var EditSongPage = function($this) {
  return page($this, {
    oncancel:function() {},
    onsave:function(song) {},
    ondelete:function() {},
    //
    init:function() {
      this.Head = Header($this.find('.head'))
        .on('clickleft', this, 'oncancel')
        .on('clickright', this, 'Head_onsave');
      this.Form = EditSongPage.Form($('#edit-song-form'))
        .on('delete', this);
    },
    //
    onshow:function(song) {
      this.song = song;
      this.Form.load(this.song);
    },
    Head_onsave:function() {
      if (this.Form.validate()) {
        this.Form.apply();
        this.onsave(this.song);
      }
    }
  })
}
EditSongPage.Form = function($this) {
  return ui({
    ondelete:function() {},
    //
    init:function() {
      this.$delete = $this.find('#edit-delete')
        .on('click', this.$delete_onclick.bind(this));
      this.initHeight = $('#edit-body').height();
    },
    load:function(song) {
      this.reset();
      this.song = song;
      $('#edit-artist').val(song.artist);
      $('#edit-title').val(song.title);
      $('#edit-body').val(song.body);
      if (this.song.isNew()) {
        $('#edit-body-box').appendTo('#edit-song-form');
        this.$delete.hide();
      } else {
        $('#edit-body-box').prependTo('#edit-song-form');
        this.$delete.show();
      }
    },
    apply:function() {
      this.song.artist = $('#edit-artist').val();
      this.song.title = $('#edit-title').val();
      this.song.body= $('#edit-body').val();
    },
    //
    reset:function() {
      $('#edit-body').height(this.initHeight);
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
/** Header Bar */
var Header = function($this) {
  return ui({
    onclickleft:function() {},
    onclickright:function() {},
    //
    init:function() {
      this.$left = $this.find('.l');
      this.$left.find('A')
        .on('click', Function.defer(this, 'onclickleft'));
      this.$middle = $this.find('.m');
      this.$right = $this.find('.r');
      this.$right.find('A')
        .on('click', Function.defer(this, 'onclickright'));
    },
    setTitle:function(text) {
      this.$middle.text(text);
      return this;
    }
  })
}
/** Dialog */
var Confirm = function($this) {
  return ui({
    //
    init:function() {
      this.$text = $this.find('.text');
      this.$yes = $this.find('.yes')
        .on('click', this.$yes_onclick.bind(this));
      this.$yes.button(); 
    },
    show:function(caption, button, /*fn*/onyes) {
      this.onyes = onyes;
      this.$text.text(caption);
      this.$yes.find('span.ui-btn-text').text(button);
      $.mobile.changePage($this, {role:'dialog'});
    },
    show_asDelete:function(onyes) {
      this.show('Are you sure? This operation cannot be undone.', 'Delete', onyes);
    },
    //
    $yes_onclick:function() {
      this.onyes && this.onyes();
    }
  })
}