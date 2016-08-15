var Flight = rec({
  /*
   id
   feed
   lat
   long
   dir
   alt
   speed
   projected
   datatype
   craft
   reg
   timestamp
   origin
   dest
   code
   mph
   dist
   bearing
   xt
   at
   ema
   string[] captions
   Point Pos
   VPt[] VPts
   */
  onrevive:function() {
    this.setr('VPts', VPts);
  },
  willCross:function(angle) {
    return this.VPts.willCross(angle);
  },
  crossing:function(angle) {
    return this.VPts[0].crosses(angle);
  }
})
var Flights = extend(recs_of(Flight), {
  ajax:ajax('flight'),
  fetch:function(pos, callback/*Flight[]*/) {
    this.ajax.get('fetch', {'a':pos.airport, 't':pos.lat, 'g':pos.long}, this, callback);
  }
})  
var VPt = rec({
  /*
   dir
   angle
   */
  crosses:function(angle) {
    return this.angle >= angle;
  },
  move:function(iangle, idir, mult) {
    this.angle += iangle * mult;
    this.dir += idir * mult;
    return this;
  } 
})
var VPts = recs(VPt, {
  willCross:function(angle) {
    for (var i = 1; i < this.length; i++) {
      if (this[i].angle >= angle)
        return i;
    }
  }
})