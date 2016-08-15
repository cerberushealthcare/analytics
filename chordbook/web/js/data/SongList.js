/** Song list */
var Song = rec({
  /*
   artist
   title
   body
   tempo
   mode (last display mode)
   */
  onrevive:function() {
    this.tempo = String.denull(this.tempo);
  }
}).statics({
  from:function(/*EditSong*/edit) {
    return this.make({
      artist:edit.artist,
      title:edit.title,
      body:edit.body,
      tempo:edit.tempo,
      mode:edit.mode
    })
  }
})
var SongList = recs(Song, {
  save:function(/*EditSong*/esong) {
    var song;
    if (esong.isNew())
      song = this.add(esong);
    else
      song = this.update(esong);
    esong.i = song.index();
    return this;
  },
  remove:function(/*EditSong*/esong) {
    recs.proto.remove.call(this, this[esong.i]);
    return this;    
  },
  //
  onrevive:function() {
    this.sort();
  },
  getSortValue:function(rec) {
    return (rec.artist + '|' + rec.title).toUpperCase();
  },
  add:function(/*EditSong*/esong) {
    var song = Song.from(esong);
    recs.proto.add.call(this, song);
    this.sort();
    return song;
  },
  update:function(/*EditSong*/esong) {
    var song = Song.from(esong);
    this[esong.i] = song;
    this.sort();
    return song;
  }
}).statics({
  revive:function(list) {
    if (Array.isEmpty(list))
      list = SongList_Init;
    return SongList.__super.revive(list);
  }
})
