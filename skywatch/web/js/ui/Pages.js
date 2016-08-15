/**   
 * Skywatch UI
 * @author Warren Hornsby
 */
var Me = rec_local('app-skywatch', {
  //
  page:null,
  data:null,
  pos:null,
  north:null,
  locked:null,
  /*Flights*/flights:null,
  //
  onrevive:function() {
    this.setr('flights', Flights);
  },
  setPosition:function(pos) {
    return this.set('pos', pos);
  },
  setFlights:function(flights) {
    return this.set('flights', flights);
  },
  setNorth:function(north) {
    return this.set('north', north);
  },
  setLocked:function(locked) {
    return this.set('locked', locked);
  },
  setData:function(fid, e) {
    this.data.set(fid, e);
    return this.save();
  },
  go:function(page, trans, restoring/*=false*/) {
    if (! restoring)
      this.resetData();
    this.set('page', page);
    Pages[page].show(trans);
  },
  restore:function(defaultPage) {
    this.go(this.page || defaultPage);
  },
  //
  resetData:function() {
    this.data = rec();
  },
  set:function(fid, e) {
    this[fid] = e;
    return this.save();
  }
})
var Pages = {
  start:function() {
    if (window.location.hash)
      return window.location.replace(Pages.getBaseUrl());
    $('.body').maxHeight(43).noBounce();
    $('a').noTapDelay();
    Pages.Home = HomePage($('#home'))
      .on('save', Pages.HomePage_onsave);
    Pages.Radar = RadarPage($('#radar'))
      .on('home', Pages.RadarPage_onhome)
      .on('setlocked', Pages.RadarPage_onsetlocked);
    Me.restore('Home');
    if (window.DeviceOrientationEvent)
      window.addEventListener('deviceorientation', Pages.onorient);
  },
  //
  HomePage_onsave:function(pos) {
    Me.setFlights(null);
    Me.setPosition(pos);
    Me.go('Radar', 'flip');
  },
  RadarPage_onhome:function() {
    Pages.Home.reset();
    Me.go('Home', 'backflip');
  },
  RadarPage_onsetlocked:function(locked) {
    Me.setLocked(locked);
  },
  onorient:function(e) {
    if (e && Me.page == 'Radar' && ! Me.locked) {
      var deg = e.webkitCompassHeading || 0; 
      var rad = (deg * Math.PI / 180);
      Me.setNorth(-rad);
    }
  },
  getBaseUrl:function() {
    var a = window.location.pathname.split('/web/');
    return a[0] + '/web/';
  }
}
/** Home */
var HomePage = function($this) {
  return page($this, {
    onsave:function(pos) {},
    //
    init:function() {
      this.Form = HomePage.Form($this.find('FORM'))
        .on('save', this, 'onsave');
    },
    reset:function() {
      this.Form.reset();
    }
  })
}
HomePage.Form = function($this) {
  return ui({
    onsave:function(pos) {},
    //
    init:function() {
      this.$save = $this.find('#edit-save')
        .on('click', this.$save_onclick.bind(this));
      pause(0.5, this.reset);
    },
    reset:function() {
      navigator.geolocation.getCurrentPosition(function(pos) {
        $('#edit-airport').val('SDF');
        $('#edit-lat').val(pos.coords.latitude);
        $('#edit-long').val(pos.coords.longitude);
      })
    },
    //
    $save_onclick:function() {
      this.onsave({
        'airport':$('#edit-airport').val(),
        'lat':$('#edit-lat').val(),
        'long':$('#edit-long').val()});
    }
  })  
}
/** Radar */
var RadarPage = function($this) {
  return page($this, {
    onhome:function() {},
    onsetlocked:function(locked) {},
    //
    init:function() {
      Header($this.find('.head'))
        .on('clickleft', this, 'onhome');
      this.Canvas = RadarPage.Canvas($this.find('canvas'));
      this.Lock = RadarPage.Lock($this.find('.lock'))
        .on('set', this, 'onsetlocked');
      this.Config = RadarPage.Config($this.find('.config'));
    },
    onshow:function() {
      this.Canvas.reset();
      this.load();
      this.step();
      this.reorient();
    },
    //
    load:function() {
      if (Me.page != 'Radar')
        return;
      var self = this;
      Flights.fetch(Me.pos, function(flights) {
        Me.setFlights(flights);
        self.Canvas.reload();
        pause(10, self.load.bind(self));
      })
    },
    step:function() {
      if (Me.page != 'Radar')
        return;
      this.Canvas.step();
      pause(0.2, this.step.bind(this));
    },
    reorient:function() {
      if (Me.page != 'Radar')
        return;
      this.Canvas.reorient();
      pause(0.1, this.reorient.bind(this));
    }
  })
}
RadarPage.Lock = function($this) {
  return ui({
    onset:function(b) {},
    //
    init:function() {
      var top = $(window).height() - 42;
      var left = 10;
      $this.css({left:left, top:top})
        .noTapDelay()
        .on('click', this.toggle.bind(this));
      this.set(Me.locked);
    },
    set:function(b) {
      this.value = b;
      if (b)
        $this.addClass('locked');
      else
        $this.removeClass('locked');
    },
    toggle:function() {
      var b = ! this.value;
      this.set(b);
      this.onset(b);
    }
  })
}
RadarPage.Config = function($this) {
  return ui({
    init:function() {
      var top = $(window).height() - 42;
      var left = $(window).width() - 42;
      $this.css({left:left, top:top});
    }
  })
}
RadarPage.Canvas = function($this) {
  var ctx = $this[0].getContext('2d');
  var cw = Math.min($(window).width(), $(window).height() - 150);
  $this.attr('width', cw);
  $this.attr('height', cw);
  var center = cw / 2;
  var radius = center - 10;
  var north = Me.north || 0;
  var blips = Blips.revive();
  return ui({
    init:function() {
      this.drawCompass();
      this.drawLines();
    },
    reorient:function() {
      var n = Me.north || 0;
      if (north != n) {
        north = n;
        this.clear();
        this.drawCompass();
        this.drawLines();
        this.reload();
      }
    },
    reset:function() {
      blips.reset();
    },
    reload:function() {
      var self = this;
      $('.lbull').remove();
      blips.reload(Me.flights, function(blip) {
        if (blip.label()) {
          //self.label(self.Pt_fromV(blip.to))
          return blip.label();
        //} else if (blip.f.crossing(5) && blip.f.willCross(15)) {
        } else if (blip.f.willCross(15)) {
          //self.label(self.Pt_fromV(blip.to))
          return self.label(self.Pt_fromV(blip.at), blip.f).on('click', '*', function() {
            alert(blip.f.id);
          })
        }
      })
    },
    step:function() {
      var self = this;
      blips.step(function(blip, label) {
        if (blip.at.angle < 5)
          label.remove();
        else
          return label.move(self.Pt_fromV(blip.at), blip.f, blip);
      })
    },
    drawFlights:function() {
      $('.label').remove();
      each(Me.flights, this, function(f) {
        if (f.crossing(10) || f.willCross(10)) {
          var cls = f._special ? 'lred' : (f.alt >= 20000 ? 'lblue' : 'lgreen');
          var pt = this.Pt(radius * (1 - f.viewangle/90), f.rad);
          this.label(pt, f.captions, cls);
          each(f.VPts, this, function(v) {
            var pt = this.Pt_fromV(v);
            this.label(pt);
          })
        }
      })
    },
    drawCompass:function() {
      this.circle(null, radius, 'black');
      var step = radius / 6;
      for (var r = radius; r >= step; r -= step * 2) 
        this.circle(null, r, null, '#252525');
      this.circle(null, 2, '#252525');
    },
    drawLines:function() {
      this.line(this.Pt(radius, 0), this.Pt(radius, Math.PI), '#252525');
      this.line(this.Pt(radius, Math.PI / 2), this.Pt(radius, -Math.PI / 2), '#252525');
      this.line(this.Pt(radius + 4, -0.02), this.Pt(radius + 10, 0), 'white');
      this.line(this.Pt(radius + 10, 0), this.Pt(radius + 4, 0.02), 'white');
      this.line(this.Pt(radius + 4, 0.02), this.Pt(radius + 4, -0.02), 'white');
    },
    //
    clear:function() {
      ctx.clearRect(0, 0, cw, cw);
    },
    circle:function(/*Pt*/c, r, fill, borderColor, borderWidth) {
      ctx.beginPath();
      var x = c ? c.x : center;
      var y = c ? c.y : center;
      ctx.arc(x, y, r, 0, 2 * Math.PI, false);
      this.fill(fill);
      this.stroke(borderColor, borderWidth);
    },
    line:function(/*Pt*/from, /*Pt*/to, color, width) {
      ctx.beginPath();
      ctx.moveTo(from.x, from.y);
      ctx.lineTo(to.x, to.y);
      this.stroke(color, width);
    },
    label:function(/*Pt*/pt, /*Flight*/f) {
      var $label = $("<div class='label'>")
        .appendTo($this.parent());
      $label.extend({
        move:function(pt, f, blip) {
          this.empty();
          if (f)
            this.append("<div class='bullet'>&bull;</div>");
          else
            this.append("<div class='bullet lbull'>&bull;</div>");
          var left = $this.position().left + pt.x - 1;
          var top = $this.position().top + pt.y - 5;
          each(f && f.captions, this, function(caption, i) {
            var cls = f.alt >= 20000 ? 'lblue' : 'lgreen';
            if (f._special && i == 1)
              cls = 'lred';
            this.append($("<span class='" + cls + "'>" + caption + "</span><br>"));
          })
          /*
          if (blip) {
            this.append($("<span class='lred'>" + (f.VPts[0].dir + '').substr(0, 7) + ',' + (f.VPts[1].dir + '').substr(0, 7) + ':' + (blip.idir + '').substr(0, 7) + "</span>"));
          }
          */
          this.css({top:top, left:left - this.width() / 2});
          return this;
        }
      })
      return $label.move(pt, f);
    },
    //
    stroke:function(color, width) {
      if (color) {
        ctx.strokeStyle = color;
        ctx.lineWidth = width || 1;
        ctx.stroke();
      }
    },
    fill:function(style) {
      if (style) {
        ctx.fillStyle = style;
        ctx.fill();
      }
    },
    Pt:function(r, t) {
      var y = r * Math.cos(t + north);
      var x = r * Math.sin(t + north);
      return {
        r:r,
        t:t,
        x:center + x,
        y:center - y
      };
    },
    Pt_fromV:function(v) {
      return this.Pt(radius * (1 - v.angle / 90), v.dir);
    }
  })
}
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
var Blip = extend(rec({
  /*
   id
   Flight f
   VPt at
   VPt to
   iangle
   idir
   */
  //
  update:function(f, now) {
    this.f = f;
    if (! this.at) 
      this.setAt(VPt.revive(this.f.VPts[0]));
    this.setTo(VPt.revive(this.f.VPts[1]));
    this.data().now = now;
  },
  wasUpdated:function(now) {
    return this.data().now == now;
  },
  setLabel:function($label) {
    this.data().$label = $label;
  },
  clearLabel:function() {
    var $label = this.data().$label;
    if ($label) {
      $label.remove();
      this.data().$label = null;
    }
  },
  label:function() {
    return this.data().$label;
  },
  setAt:function(vpt) {
    this.at = vpt;
    this.data().stepped = $.now();
  },
  setTo:function(vpt) {
    this.to = vpt;
    var dangle = this.to.angle - this.at.angle;
    var ddir = this.to.dir - this.at.dir;
    if (ddir > Math.PI)
      ddir -= Math.PI * 2;
    else if (ddir < -Math.PI)
      ddir += Math.PI * 2;
    this.iangle = dangle / 60;
    this.idir = ddir / 60;
  },
  step:function() {
    var secs = this.stepped();
    this.at.move(this.iangle, this.idir, secs);
    this.data().stepped = $.now();
  },
  stepped:function() {
    if (this.data().stepped)
      return ($.now() - this.data().stepped) / 1000; 
  }
}),{
  from:function(/*Flight*/f) {
    return this.revive({id:f.id});
  }
})
var Blips = recs_map(Blip, 'id', {
  reload:function(flights, /*fn(Blip):$label*/labelf) {
    var now = $.now();
    this.data().reloaded = now;
    each(flights, this, function(f) {
      var blip = this.get(f.id);
      if (! blip) {
        blip = Blip.from(f);
        this.add(blip);
      }
      blip.update(f, now);
      blip.setLabel(labelf(blip));
    })
    this.removeOuts(now);
  },
  step:function(/*fn(Blip,label):$label*/labelf) {
    each(this, this, function(blip) {
      blip.step();
      if (blip.label())
        blip.setLabel(labelf(blip, blip.label()));
    })
  },
  reset:function() {
    this.removeOuts(-1);
  },
  //
  removeOuts:function(now) {
    var i = 0;
    while (i < this.length) {
      if (this[i].wasUpdated(now)) {
        i++;
      } else {
        this[i].clearLabel();
        this.remove(this[i]);
      }
    }
  }
})
