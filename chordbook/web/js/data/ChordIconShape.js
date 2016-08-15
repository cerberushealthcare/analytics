var ChordIconShape = extend(rec({
  /*
   fret
   pos[0..5] //-1=closed 0=open #=finger relative to fret
   barre  
     pos
     start //string 0-5
     end   //string 0-5
  */
}),{
  from:function(/*string*/shape) {
    if (shape && shape.length == 6) {
      var s, j, a = [], hi = 0, lo = 999;
      for (var i = 0; i < 6; i++) {
        s = shape.substr(i, 1);
        if (s == 'X') {
          j = -1;
        } else {
          j = parseInt(s, 10);
          if (j) {
            if (j < lo)
              lo = j;
            if (j > hi)
              hi = j;
          }
        }
        a.push(j);
      }
      var fret = lo;
      if (fret == 2 && hi <= 4)
        fret = 1;
      if (fret > 1) {
        lo = 1;
        var off = fret - 1;
        for (var i = 0; i < 6; i++) {
          if (a[i] > 0)
            a[i] -= off;
        }
      }
      var barre = this.getBarre(a, lo, hi);
      return this.revive({
        fret:fret,
        pos:a,
        barre:barre
      });
    }
  },
  //
  getBarre:function(a, lo, hi) {
    if (lo == hi)
      return;
    var cross, barre = {
      pos:lo
    };
    for (var i = 0; i < 6; i++) {
      if (a[i] == lo) {
        if (! barre.start) {
          barre.start = i + 1;
        } else if (cross) {
          barre.end = i + 1;
        }
      } else if (a[i] > lo) {
        if (barre.start) 
          cross = true;
      } else {
        if (barre.start)
          return;
      }
    }
    if (barre.end && (barre.end - barre.start) > 2)
      return barre;
  }
})
