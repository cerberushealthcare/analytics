/**
 * Clicktate Template Engine
 * Global static
 * Requires: DocLib.js, ui.js
 */
var Engine = {
  VERSION:'2.0.0',
  // 
  session:null,
  dom:null,   // document object model
  qs:null,    // {qrefi:q,..}
  qts:null,   // {qref:[{'q':q,'test':$,'viz':!},..],..}
  //
  STATE_IDLE:0,
  STATE_FETCHING:1,    // fetching pars
  STATE_PROCESSING:2,  // processing actions
  STATE_REFRESHING:3,  // running thru template tests/commands
  _state:null,
  _processing:null,    // {'actions':[$,..],'ix':#}
  //
  _request:null,  // {'wait':#,'pars':{pid:{'cloning':!,'wait':#,'got':#},..}    
  //
  _C1_SEC_HIDDEN:'&',
  /*
   * Load the engine with session and process any session.actions
   * - session: JSession
   * - callback: returns dom 
   */
  load:function(session, callback) {
    this.session = this._addSessionProps(session);
    this._initObjects();
    this._setDom(session.template);
    var extracted = this._extractFetchActions(session.actions);
    var self = this;
    this._fetchPars(extracted.pidis, function() {
      self._processActions(extracted.actions, function() {
        self._refreshDom(function() {
          self._setState(Engine.STATE_IDLE);
          callback(self.dom);
        });
      });
    });
  },
  /*
   * Fetch requested pars and insert into DOM
   */
  fetchPars:function(pidis, callback) {
    if (pidis == null) {
      callback();
    } else {
      this._setState(Engine.STATE_FETCHING);
      var req = this._getReqArg();
      req.pidis = pidis;
      var self = this;
      Ajax.post(Ajax.SVR_ENGINE, 'fetchPars', req, 
        function(pis) {
          self._parseParInfos(pid, pis);
          callback();
       });
    } 
  },
  /*
   * Process action(s)
   * - action: ['action',..] or 'action'
   */
  processActions:function(action, callback) {
    if (action == null) {
      callback();
    } else {
      this._processing = {
        'actions':arrayify(action),
        'ix':-1,
        'callback':callback};
      var self = this;
      call(function() {
        self.processNextAction();
      });
    }
  },
  processNextAction:function() {
    this._setState(Engine.STATE_PROCESSING);
    var p = this._processing;
    p.ix++;
    if (p.ix > p.actions.length) {
      p.callback();
    } else {
      var action = p.actions[ix];
      var q = eval(action);
      if (q == null) {
        // TODO
      } else {
        var self = this;
        var injects = this._getInjectsPending($q);
        if (injects) {
          this._fetchInjects(injects, function() {
            self.processNextAction();
          });
        } else { 
          call(function() {
            self.processNextAction();
          });
        }
      }
    }
  },
  _fetchInjects:function(injects, callback) {
    var pidis = [];
    for (var i = 0; i < injects.length; i++) {
      var inj = injects[i];
      // TODO: check if in doc
      // TODO: add {'pidi':$,'injector':$} to pidis
    }
    if (pidis.length == 0) {
      callback();
    } else {
      this._setState(Engine.STATE_FETCHING);
      var req = this._getReqArg();
      req.pidis = pidis;
      var self = this;
      Ajax.post(Ajax.SVR_ENGINE, 'inject', req, 
        function(pis) {
          self._parseParInfos(pid, pis);
          callback();
       });
    }
  },
  /*
   * Returns [{qref:$,cond:$,pref:$,_pidi:$,_csuf:$},..]
   */
  _getInjectsPending:function(q) {
    
  },
  /*
   * Refresh DOM by running thru template tests/commands
   */
   refreshDom:function(callback) {
     
   },
  _extractFetchActions:function() {
    var extracted = {
      'pidis':[],     
      'actions':[]};  
    if (session.actions) {
      for (var i = 0; i < session.actions.length; i++) {
        var a = session.actions[i];
        var pidi = this._isGet(a);
        if (pidi) {
          extracted.gets.push(pidi);
        } else {
          extracted.actions.push(a);
        }
      }
    }
    return extracted;
  },
  _isGetPar:function(a) {
    if (a.substr(0, 8) == "getPar('") {
      return a.split("'")[1];
    }
    if (a.substr(0, 7) == 'getPar(') {
      return a.split("(")[1];
    }
    if (a.substr(0, 9) == 'getFsPar(') {
      return a.split("(")[1];
    }
  },
  _initObjects:function() {
    this._request = {
      'wait':0,
      'pars':{}};      
  },
  _addSessionProps:function(session) {
    session.sex = DocLib.formatSex(session.csex);
    session.age = DocLib.calculateAge(session.cbirth);
    session.birth = denull(session.cbirth);
  },
  /*
   * Document UI interactions
   * Callback returns dom
   */
  uiFetch:function(pid, puid, callback) {
    var rpar = this._cacheParRequest(pid, puid, Engine._REQ_FETCH);
    if (rpar) {
      var self = this;
      var pidi = rpar.pidi;
      this._appendRestoreCmd(Engine._RESTORE_FETCH, pidi, puid);
      this._setState(Engine.STATE_REQUESTING);
      Ajax.post(Ajax.SVR_ENGINE, 'fetchPar', this._buildReqArg([pidi]), 
        function(pis) {
          this._cacheParReturn(pid);
          self._parseParInfos(pid, pis);
       });
    }
  },
  /*
   * Load JParInfos received and update dom
   */
  _parseParInfos:function(pid, pis) {
    for (var i = 0; i < pis.length; i++) {
      this._parseParInfo(pis[i]);
    }
  },
  _parseParInfo:function(pi) {
    this._cacheQuestions(pi);
    this._addDomPar(pi);
  },
  _cacheQuestions:function(pi) {
    for (var i = 0; i < pi.questions.length; i++) {
      var q = pi.questions[i];
      var qt = pi.par.qTemplates[i];
      q.lt = qt.lt;
      this._addProps(q);
      this._cacheTest(q, qt.test);
      var qref = pi.suid + '.' + pi.par.uid + '.' + q.uid;
      this._cacheQuestion(qref, q);
    }
  },
  _cacheQuestion:function(qref, q) {
    q.ref = qref;
    this.qs[qref] = q;
  },
  /*
   * Cache test to questions referenced in the args  
   */
  _cacheTest:function(q, test) {
    if (test) {
      test = this._formatTest(test);
      var qt = {
        'q':q,
        'test':test,
        'lastResult':null};
      var qrefs = {};
      var a = test.split(')');
      for (var i = 0, j = a.length - 1; i < j; i++) {
        var b = a[i].split(/\('|','/);
        if (b.length > 0) {
          qrefs[b[2]] = b[2];
        }
      }
      for (var qref in qrefs) {
        if (this.qts[qref] == null) {
          this.qts[qref] = [qt];
        } else {
          this.qts[qref].push(qt);
        }
      }
    }
  },
  /*
   * Prepends method references and adds _i to args
   */
  _formatTest:function(test) {
    var iarg = this._i + "',";
    test = test.replace(/isSel\(/g, "Engine._isSel('" + iarg);
    return test;
  },
  /*
   * Add helper props to question and options:
   *   q.blank      // true if default option is '(in paren)'
   *   q.oixs       // {ouid:ix} map
   *   q.hasOsyncs  // true if any opt sync present
   *   q._ict       // instance count (if q is cloning type)
   *   o.text       // doc text for option
   */
  _addProps:function(q) {
    if (q.opts) {
      q.oixs = {};  // {ouid:ix}
      for (var ix = 0; ix <= q.loix; ix++) {
        var o = q.opts[ix];
        o.text = this._fixTags(qOptText(o));
        if (o.sync) q.hasOsyncs = true;
        q.oixs[o.uid] = ix;
      }
      if (q.opts[q.def[0]].text.substr(0, 1) == '(') {
        q.blank = true;
      }
    }
    if (q.cloning) {
      q._ict = 0;
      q.master = true;
    }
  },
  /*  
   * Cache paragraph request
   * Returns updated request object or null if par already in doc
   */ 
  _cacheParRequest:function(pid, puid) { 
    var cloning = this._isCloning(puid);
    var rpar = this._request.pars[pid];
    if (rpar == null) {
      var rpar = {
        'cloning':cloning,
        'wait':1,
        'got':0
        };
      this._request.pars[pid] = rpar;
    } else {
      if (! cloning) {
        return null;  // par already in doc
      }
      rpar.wait++;
    }
    rpar.pidi = (rpar.cloning) ? pid + '+' + rpar.wait : pid
    this._request.wait++;
    return rpar;
  },
  _cacheParReturn:function(pid) {
    var rpar = this._request.pars[pid];
    rpar.wait--;
    rpar.got++;
    this._request.wait--;
  },
  _buildReqArg:function(pids, injector) {
    return {
      'tid':this.template.id,
      'cid':this.session.client.id,
      'nd':this.session.noteDate};
  },
  _isCloning:function(puid) {
    return puid.substr(0, 1) == '+';
  },
  _appendRestoreCmd:function(rcid, value, comment) {
    this._restore.cmds.push([rcid, value, comment]);
  },
  /* 
   * DOM functions
   * Builds {
   *   'title':$,
   *   'secs':{
   *     sid:{
   *       'title':$,
   *       'viz':!,
   *       'pars':{
   *         pidi:{
   *           ..},  // see _addDomPar
   *         pidi:{
   *           ..},..
   *         }  
   *       },..
   *     }
   *   }
   */
  _setDom:function(template) {
    var dom = {
      'title':template.title,
      'secs':{}
      };
    dom.title = template.title;
    for (var sid in template.sections) {
      this._addDomSec(template.sections[sid]); 
    }
    if (template.autos) {
      for (var i = 0; i < template.autos.length; i++) {
        this._setDomPar(template.autos[i]);
      }
    }
    this.dom = dom;
  },
  _setDomSec:function(section) {
    this.dom.secs[section.id] = {
      'title':section.title,
      'viz':(section.uid.substr(0, 1) != Engine._C1_SEC_HIDDEN),
      'pars':{}
      };
  },
  /*
   * Adds to dom.secs[sid].pars:
   * ..'pars':{
   *     pidi:{
   *       'sort':#,
   *       'viz':!,
   *       'qs':{
   *         qidi:{
   *           ..},  // see _addDomQ
   *         qidi:{
   *           ..},..
   *       }
   *     }
   */
  _addDomPar:function(pi) {
    
  },
  /*
   * Adds to dom.secs[sid].pars[pidi].qs:
   * ..'qs':{
   *     qidi:{
   *       'viz':!,
   *       'bt':$,
   *       'pop':{
   *         'viz':!,
   *         'bt':$,
   *         'type':#, 
   *         'sel':$,
   *         'at':$
   *         },
   *       'pop2':{  // for unselected, if necessary
   *         ..},  
   *       'at':$,
   *       'ft':$,
   *       'brk':#
   *       },..
   *     }
   */
  _addDomQ:function(qt, q) {
    
  },
  /*
   * Evaluate text tags and correct gender-specific language
   */
  _fixTags:function(t) {
    t = t.replace(/\{today\}/g, today);
    return t;
  },
  _setState:function(state) {
    this._state = state;
  }
}
