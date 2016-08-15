Immun = Object.Rec.extend({
  /*
   dataImmunId
   userGroupId
   clientId
   sessionId
   dateGiven
   name
   tradeName
   manufac
   lot
   dateExp
   dateVis
   dateVis2
   dateVis3
   dateVis4
   dose
   route
   site
   adminBy
   comment
   dateUpdated
   formVis
   formVis2
   formVis3
   formVis4
   */
  uiDate:function() {
    var s = this.dateGiven;
    switch (this.status) {
    case 'Refused':
      s += ' (REFUSED)';
      break;
    case 'Not Given Due to Prior Reaction':
      s += ' (NOT GIVEN)';
      break;
    }
    return s;
  },
  uiDateCls:function(cls) {
    switch (this.status) {
    case 'Refused':
    case 'Not Given Due to Prior Reaction':
      cls += ' red';
      break;
    }
    return cls;    
  }
})
Immuns = Object.RecArray.of(Immun, {
  //
  get:function(id) {
    if (this._map == null)
      this._map = Map.from(this, 'dataImmunId');
    return this._map[id];
  }
})
ImmunCd = Object.Rec.extend({
  /*
   IC_Sched[] ics
   _html
   */
  onload:function() {
    this.setr('ics', IC_Sched);
  },
  getDueNows:function() { /*[vcat,..]*/
    var a = [];
    this.ics && this.ics.each(function(sched) {
      if (sched.due)
        a.push(sched.vcat);
    })  
    if (a.length)
      return a;
  },
  getImmsByVcat:function() { /*{vcat:[Immun_C,..],..}*/
    var map = {};
    this.ics && this.ics.each(function(sched) {
      var a = [];
      sched.rec.imms && sched.rec.imms.each(function(imm) {
        a.unshift(imm);
      })
      map[sched.vcat] = a;
    })
    return map;
  }
})
IC_Sched = Object.Rec.extend({
  /*
   vcat
   IS_Record rec
   completed
   count
   Dose dose
   due
   left
   */
  onload:function() {
    this.setr('rec', IS_Record);
    this.setr('dose', Dose);
  },
  each:function(callback/*Immun_C*/) {
    this.rec.imms && this.rec.imms.each(callback);
  },
  uiCell:function() {
    return this.due ? 'Due Now' : 'Due: ' + this.dose.uiDate();
  }
})
Dose = Object.Rec.extend({
  /*
   ages
   index
   _date
   */
  uiDate:function() {
    return this._date;
    var a = this._date.split('-');
    return a[1] + ' ' + a[2].substr(2);
  }
})
IS_Record = Object.Rec.extend({
  /*
   Immun_C imms 
   */
  onload:function() {
    this.setr('imms', Immun_C);
  }
})
Immun_C = Object.Rec.extend({
  /*
   dataImmunId
   userGroupId
   clientId
   dateGiven
   name
   tradeName
   lot
   dose
   comment
   cage
   */
  uiCell:function() {
    return this.uiDate() + ' (' + this.uiCage() + ')';
  },
  uiCage:function() {
    var c = this.cage._cage;
    if (c.y)
      return c.y >= 2 ? c.y + 'y' : (c.y * 12 + c.m) + 'm';
    else 
      return c.m + 'm';
  },
  uiDate:function() {
    return this.dateGiven;
    var a = this.dateGiven.split('-');
    return a[1] + ' ' + a[2].substr(2);
  }
})

