/** Small chord anchor */
var ChordIcon = extend(function($container) {
  var h = ["<div class='chord-icon'><table class='chord-icon'>"];
  for (var r = 0; r < 4; r++) {
    h.push('<tr>');
    for (var c = 0; c < 5; c++)
      h.push('<td></td>');
    h.push('</tr>');
  }
  h.push("</table></div>");
  var $this = $(h.join('')).appendTo($container.empty()).hide();
  return ui($this, {
    onclick:function() {},
    //
    init:function() {
      $this.on('click', Function.defer(this, 'onclick'));
    },
    hide:function(b) {
      if (b) {
        $this.hide('slow');
      } else {
        $this.show('transfer');
      }
      return this;
    },
    show:function() {
      $this.show();
      return this;
    },
    plot:function(shape) {
      $this.find('.plot').remove();
      var cshape = ChordShape.from(shape);
      if (cshape) {
        for (var i = 0; i < 6; i++) {
          var j = cshape.pos[i];
          if (j == -1) {
            if (cshape.barre)
              this.closed(i + 1);
          } else if (j == 0) { 
            this.open(i + 1);
          } else {
            if (cshape.barre && j == cshape.barre.pos && i > cshape.barre.start - 1 && i < cshape.barre.end - 1) {
              //
            } else {
              this.bullet(i + 1, j);
            }
          }
        }
        if (cshape.barre)
          this.barre(cshape.barre.start, cshape.barre.end, cshape.barre.pos);
        if (cshape.fret > 1)
          this.fret(cshape.fret);
      }
      return this;
    },
    //
    fret:function(text) {
      this.pos(39, 6, text, 'fret');
    },
    open:function(string) {
      this.pos(this.x(string), -14, '&#3866;', 'openclose');
    },
    closed:function(string) {
      this.pos(this.x(string), -14, '&#3869;', 'openclose');
    },
    bullet:function(string, fret) {
      this.pos(this.x(string), this.y(fret), '&bull;', 'bullet');
    },
    barre:function(string1, string2, fret) {
      var x = this.x(string1);
      var y = this.y(fret);
      var left = x + 4;
      var top = y + 11;
      var width = this.x(string2) - x;
      $("<div class='plot barre'></div>")
        .appendTo($this)
        .css({top:top,left:left,width:width});
    },
    x:function(string) {
      return (string - 1) * 6;
    },
    y:function(fret) {
      return (fret - 1) * 8;
    },
    pos:function(x, y, text, cls) {
      $("<div class='plot " + (cls) + "'>" + text + "</div>")
        .appendTo($this)
        .css({top:y,left:x});
    }
  })
},{
  clearAll:function() {
    $('.plot').remove();
  }
})
