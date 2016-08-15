var DataTester = {
  //
  all:function() {
    //
    console.clear();
    this.songs();
    this.chords();
  },
  songs:function() {
    log('<songs>');
    var songlist, song, edit, body, text, text2, chords, chord, chord2, line, bar, shape, names, name;
    //
    songlist = SongList.revive(SongList_Init);
    log(songlist, 'songlist');
    console.assert(songlist.length == 4);
    //
    edit = EditSong.asNew();
    edit.artist = 'Bob Mould';
    edit.title = 'Panama City Motel';
    text = '@ Don\'t you know @ I need a @ place to stay @\nIt\'s @ only fifteen @ bucks a day @';
    edit.setBody(text);
    log(edit, 'edit new');
    body = edit.songbody();
    log(body, 'body');
    text2 = body.toString();
    text2 = text2.replace(/\?/g, '');
    console.assert(text2 == text);
    console.assert(body.lines.length == 2);
    line = body.lines[0];
    console.assert(line.bars.length = 4);
    chord = line.bars[0].chord;
    console.assert(chord.toString() == '?');
    console.assert(body.chords().length == 7);
    console.assert(body.distinctChords().length == 0);
    //
    song = songlist[3];
    log(song, 'song solitary man');
    console.assert(song.title == 'Solitary Man');
    edit = EditSong.from(song);
    log(edit, 'edit');
    console.assert(edit.title == song.title);
    console.assert(! edit.isNew());
    console.assert(! edit.isEmpty());
    //
    body = edit.songbody();
    log(body, 'body');
    console.assert(body);
    chords = body.distinctChords();
    log(chords, 'distinct chords');
    console.assert(chords.length == 5);
    chords = body.chords();
    log(chords, 'chords');
    console.assert(chords.length == 18);
    chord = chords[1];
    log(chord, 'chord');
    console.assert(chord.index() == 1);
    console.assert(body.lines.length == 11);
    line = body.lines[4];
    log(line, 'line');
    console.assert(line.index() == 4);
    console.assert(line.bars.length == 4);
    bar = line.bars[1];
    log(bar, 'bar');
    console.assert(bar.parent() == line);
    chord = bar.chord;
    console.assert(chord.parent() == bar);
    console.assert(chord.name == 'C');
    console.assert(chord.toString() == 'C');
    shape = chord.shape;
    console.assert(shape == null);
    chord2 = Chord.from('Bb:688766');
    chord.apply(chord2);
    log(chord, 'chord changed to Bb:688766');
    body = body.toString();
    edit.setBody(body);
    body = edit.songbody();
    log(body, 'body');
    chords = body.chords(); 
    console.assert(chords.length == 18);
    console.assert(chords[0].name == 'Em');
    chord = body.lines[4].bars[1].chord;
    console.assert(chord.name == 'Bb');
    console.assert(chord.shape == '688766');
    console.assert(body.distinctChords().length == 6);
    //
    log('</songs>');
  },
  chords:function() {
    //
    log('<chords>');
    var chordlist, chord, shapes, names, name, cn, spots, item;
    //
    chordlist = ChordList.revive(ChordList_Init);
    log(chordlist, 'chordlist');
    console.assert(chordlist.length == 410);
    shape = chordlist.shape('C');
    log(shape, 'shape C');
    console.assert(shape == 'X32010');
    chord = ChordShape.from(shape);
    log(chord, 'chord C');
    console.assert(chord.fret == 1);
    console.assert(js(chord.pos) == '[-1,3,2,0,1,0]');
    console.assert(chord.barre == null);
    //
    shape = chordlist.shape('B');
    log(shape, 'shape B');
    console.assert(shape == 'X24442');
    shape = chordlist.shape('Am');
    log(shape, 'shape Am');
    console.assert(shape == 'X02210');
    shape = chordlist.shape('G');
    log(shape, 'shape G');
    console.assert(shape == '320003');    
    //
    shapes = chordlist.shapes('Bb');
    log(shapes, 'shapes Bb');
    console.assert(shapes.length == 3);
    shape = shapes[1];
    log(shape, 'shape Bb');
    console.assert(shape == 'X13331');
    chord = ChordShape.from(shape);
    log(chord, 'chord Bb');
    console.assert(chord.fret == 1);
    console.assert(js(chord.pos) == '[-1,1,3,3,3,1]');
    console.assert(chord.barre.pos == 1);
    console.assert(chord.barre.start == 2);
    console.assert(chord.barre.end == 6);
    console.assert(chord.shape() == 'X13331');
    //
    chord = ChordShape.from('688766');
    log(chord, 'chord Bb:688766');
    console.assert(chord.fret == 6);
    console.assert(js(chord.pos) == '[1,3,3,2,1,1]');
    console.assert(chord.barre.pos == 1);
    console.assert(chord.barre.start == 1);
    console.assert(chord.barre.end == 6);
    console.assert(chord.shape() == '688766');
    //
    chord.reset();
    log(chord, 'chord reset');
    console.assert(chord.fret == 1);
    console.assert(js(chord.pos) == '[0,0,0,0,0,0]');
    console.assert(chord.barre == null);
    console.assert(chord.shape() == '000000');
    //
    chord.setPos(0, -1);
    chord.setPos(1, 3);
    chord.setPos(2, 3);
    chord.setPos(3, 3);
    chord.setPos(4, 3);
    chord.setPos(5, 3);
    log(chord, 'chord setPos');
    console.assert(chord.fret == 1);
    console.assert(js(chord.pos) == '[-1,3,3,3,3,3]');
    console.assert(chord.barre.pos = 3);
    console.assert(chord.barre.start = 2);
    console.assert(chord.barre.end = 6);
    console.assert(chord.shape() == 'X33333');    
    //
    shape = chordlist.shape('Bm');
    log(shape, 'shape Bm');
    chord = ChordShape.from(shape);
    log(chord, 'chord Bm');
    console.assert(chord.fret == 1);
    console.assert(js(chord.pos) == '[-1,2,4,4,3,2]');
    console.assert(chord.barre.pos == 2);
    console.assert(chord.barre.start == 2);
    console.assert(chord.barre.end == 6);
    console.assert(chord.shape() == 'X24432');
    spots = [];
    for (var i = 1; i <= 4; i++)  
      spots.push(chord.spots(i));
    log(spots, 'spots Bm');
    console.assert(js(spots[0]) == '[0,0,0,0,0,0]');
    console.assert(js(spots[1]) == '[0,1,2,2,2,1]');
    console.assert(js(spots[2]) == '[0,0,0,0,1,0]');
    console.assert(js(spots[3]) == '[0,0,1,1,0,0]');
    //
    chord.setFret(2);
    log(chord, 'chord setFret');
    console.assert(chord.shape() == 'X35543');
    chord.setPos(0, -1);
    chord.setPos(1, -1);
    chord.setPos(2, 0);
    chord.setPos(3, 2);
    chord.setPos(4, 3);
    chord.setPos(5, 2);
    chord.setFret(1);
    log(chord, 'chord set D');
    console.assert(chord.barre == null);
    console.assert(chord.shape() == 'XX0232');
    //
    names = chordlist.names('XX0232');
    log(names, 'names XX0232');
    console.assert(names[0] == 'D');
    chord.setPos(5, 1);
    log(chord, 'chord set Dm');
    names = chordlist.names(chord.shape());
    console.assert(names[0] == 'Dm');
    //
    shape = 'X02222';
    chord = ChordShape.from(shape);
    log(chord, 'chord A6');
    chord.toggle(0, 2);
    console.assert(chord.shape() == '202222');
    //
    shape = chordlist.shape('Bm');
    chord = ChordShape.from(shape);
    log(chord, 'chord Bm');
    console.assert(chord.shape() == 'X24432');
    chord.toggle(3, 4);
    console.assert(chord.shape() == 'X24232');
    chord.toggle(3, 4);
    console.assert(chord.shape() == 'X24432');
    chord.toggle(5, 2);
    console.assert(chord.shape() == 'X24430');
    chord.toggle(1, 2);
    console.assert(chord.shape() == 'X04430');
    //
    cn = ChordName.from(null);
    console.assert(cn.root == null);
    console.assert(cn.minor == null);
    console.assert(cn.mod1 == null);
    console.assert(cn.mod2 == null);
    console.assert(cn.slash == null);
    //
    name = 'Dmaj9/F#';
    cn = ChordName.from(name);
    log(cn, 'chordname ' + name);
    console.assert(cn.root == 'D');
    console.assert(cn.minor == null);
    console.assert(cn.mod1 == 'maj9');
    console.assert(cn.mod2 == null);
    console.assert(cn.slash == 'F#');
    //
    name = 'D#maug#9/Gb';
    cn = ChordName.from(name);
    log(cn, 'chordname ' + name);
    console.assert(cn.root == 'D#');
    console.assert(cn.minor == 'm');
    console.assert(cn.mod1 == 'aug');
    console.assert(cn.mod2 == '#9');
    console.assert(cn.slash == 'Gb');
    console.assert(cn.name() == name);
    cn.setMod1('dim');
    log(cn, 'chordname setMod1');
    console.assert(cn.name() == 'D#mdim/Gb');
    cn.setRootMinor('C');
    cn.setSlash(null);
    log(cn, 'chordname set C');
    console.assert(cn.name() == 'C');
    //
    chord = EditChord.from(null);
    log(chord, 'EditChord null');
    console.assert(! chord.unsaved());
    chord.shape('022000');
    chord.name(null);
    console.assert(chord.name() == 'Em');
    //
    chord = EditChord.from(Chord.from('G'));
    log(chord, 'EditChord G');
    console.assert(! chord.unsaved());
    console.assert(js(chord.names()) == '[null,null]');
    console.assert(js(chord.shapes()) == '[null,"320033"]');
    //
    chord = EditChord.from(Chord.from('A#:X13331'));
    log(chord, 'EditChord A#:X13331');
    console.assert(! chord.unsaved());
    console.assert(js(chord.names()) == '[null,"Bb"]');
    console.assert(js(chord.shapes()) == '["XX0311","688766"]');    
    //
    shape = '202220';
    name = 'Am/F#';
    item = ChordListItem.from(shape, name);
    log(item, 'Item Am/F#');
    console.assert(item.shape == '202220');
    console.assert(item.ck == 'Am/FG');
    //
    chord = EditChord.from(Chord.from(name + ':' + shape));
    log(chord, 'Saving chord');
    console.assert(chordlist.length == 410);
    names = chordlist.names(name);
    console.assert(names.length == 0);
    console.assert(chord.unsaved());
    chord.save();
    console.assert(! chord.unsaved());
    console.assert(chordlist.length == 411);
    console.assert(chordlist[410].data().i == 410);
    names = chordlist.names(shape);
    console.assert(names.length == 2);
    console.assert(names[0] == 'Am/F#');
    console.assert(names[1] == 'Am/Gb');
    shapes = chordlist.shapes(name);
    console.assert(shapes.length == 1);
    console.assert(shapes[0] == '202220');
    //
    chord = EditChord.from(Chord.from('G:320003'));
    log(chord, 'Removing chord');
    console.assert(! chord.unsaved());
    console.assert(chordlist.length == 411);
    shapes = chordlist.shapes('G');
    console.assert(shapes.length == 3);
    chord.kill();
    console.assert(chord.unsaved());
    console.assert(chordlist.length == 410);
    console.assert(chordlist.shape('G') == '320033');
    shapes = chordlist.shapes('G');
    console.assert(js(shapes) == '["320033","355433"]');
    //
    chord = Chord.from('?');
    log(chord, 'Unknown chord');
    console.assert(chord.unknown());
    chord = EditChord.from(chord);
    console.assert(! chord.unsaved());
    chord = chord.chord();
    console.assert(chord.unknown());
    //
    log('</chords>');
  }
}
