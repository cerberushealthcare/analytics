/**
 * Rec TemplateMap
 */
TemplateMap = Object.Rec.extend({
  /*
   templateId
   uid
   name
   public
   desc
   title
   userGroupId
   Sections
   _startSection
   _effective
   */
  onload:function() {
    this.Sections = MapSections.revive(this.Sections);
    var suids = {};
    var pids = {};
    this.Sections.forEach(function(section) {
      suids[section.uid] = section;
      section.Pars.forEach(function(par) {
        pids[par.parId] = par;
      })
    })
    this._suids = suids;
    this._pids = pids;
  },
  getPar:function(suid, puid) {
    return this._suids[suid]._puids[puid];
  },
  getParByPid:function(pid) {
    return this._pids[pid];
  },
  getPars:function(suid) {
    return this._suids[suid]._puids;
  },
  getSection:function(suid) {
    return this._suids[suid];
  },
  clone:function(proto) {
    proto = proto || this;
    return proto.revive(Json.decode(Json.encode(this)));
  },
  //
  ajax:function(worker) {
    return {
      fetch:function(tid, callback/*TemplateMap*/) {
        Ajax.getr_cached('Templates', 'getMap', tid, TemplateMap, worker, callback);
      }
    }
  },
  _cache:{}
})
//
MapSection = Object.Rec.extend({
  /*
   sectionId
   templateId
   uid
   name
   sortOrder
   title
   Pars
   */
  onload:function() {
    this.Pars = MapPars.revive(this.Pars);
    var puids = {};
    this.Pars.forEach(function(par) {
      puids[par.uid] = par;
    })
    this._puids = puids;
  },
  toString:function() {
    return this.name;
  }
})
MapSections = Object.RecArray.of(MapSection);
//
MapPar = Object.Rec.extend({
  /*
   parId
   sectionId
   uid
   major
   desc
   injectOnly
   dateEffective
   current
   */
  toString:function() {
    return this.desc;
  }
})
MapPars = Object.RecArray.of(MapPar, {
  testNoMajor:function() {
    for (var i = 0; i < this.length; i++) 
      if (this[i].major)
        return;
    this.forEach(function(p) {
      p.major = true;
    })
  }
})
/**
 * TemplateMap WorkingTemplateMap (for customizing)
 */
WorkingTemplateMap = TemplateMap.extend({
  from:function(map) {
    return map.clone(this);
  },
  testNoMajors:function() {
    this.Sections.forEach(function(s) {
      s.Pars.testNoMajor();
    })
  },
  buildLookupMap:function() {
    var lumap = {'startSection':this._startSection,'main':{},'auto':{}};
    this.Sections.forEach(function(s) {
      var mains = lumap.main[s.uid] = [];
      var autos = lumap.auto[s.uid] = [];
      s.Pars.forEach(function(p) {
        if (p.major) 
          mains.push(p.uid);
        if (p.auto) 
          autos.push(p.uid);
      })
    })
    return lumap;
  }
})