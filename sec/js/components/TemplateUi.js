/**
 * Template UI
 * Builds a document "mini engine"
 * Requires: NewCrop.js 
 */
TemplateUi.FORMAT_PARAGRAPH = 1;
TemplateUi.FORMAT_ENTRY_FORM = 2;
TemplateUi.FORMAT_ENTRY_FORM_WIDE = 3;
//
TemplateUi.FT_BETWEEN_SENTENCES = 1;
TemplateUi.FT_BETWEEN_LFS = 2;
TemplateUi.FT_BETWEEN_PARS = 3;
TemplateUi.FT_NONE = 9;
//
TemplateUi.FID_AS_QID = 1;
TemplateUi.FID_AS_DSYNC = 2;
//
TemplateUi.NO_CURTAIN = true;
/*
 * TemplateUi constructor
 * - div: optional (will be created if omitted) <div> for TUI document window
 *        div.tui will be set to this reference
 * - facesheet: optional, JFacesheet for client (msg level)
 * - data: optional, to default syncs
 *         {'qsyncs':{qsid:[seltext,..],..},'osyncs':{osid:1,..},'dsyncs':{dsynci:[seltext,..],..}}
 * - pid: optional, to retrieve JParInfo
 * - format: optional, default TemplateUi.FORMAT_PARAGRAPH;
 * @callback() callback when pid loaded (optional)
 * @callback(Question) onchange (optional)
 */
function TemplateUi(div, facesheet, data, pid, format, callback, onchange) {
  this.doc = (div) ? _$(div).clean()  : Html.Div.create('tui');
  this.reset();
  this.data = data;
  this.onchange = onchange;
  if (data) {
    this.osyncs = data.osyncs;
    this.qsyncs = data.qsyncs;
    this.dsyncs = data.dsyncs;
  }
  this.doc.tui = this;
  this.format = format || TemplateUi.FORMAT_PARAGRAPH;
  if (format >= TemplateUi.FORMAT_ENTRY_FORM) 
    this.ftPlacement = TemplateUi.FT_NONE;    
  else 
    this.ftPlacement = TemplateUi.FT_BETWEEN_SENTENCES;
  this.facesheet = facesheet;
  TemplateUi._cacheInstance(this);
  if (pid)
    this.getParInfo(pid, callback);
}
/*
 * TemplateUi class defs
 */
TemplateUi.prototype = {
  format:null,      // TemplateUi.FORMAT_
  doc:null,         // <div>
  ftPlacement:null, // where FT goes
  tuiSections:null, // {suid:<div>,..}
  tuiPars:null,     // {pid:<div>,..} 
  qs:null,          // {qref:q,..}
  qts:null,         // {qref:[{'q':q,'test':$,'lastResult':!},..],..}
  qcs:null,         // {qid:[{'cond':$,'action':$}],..}
  osyncs:null,      // {osid:!,..} 
  qsyncs:null,      // {qsid:[seltext$,..],..} 
  dsyncs:null,      // {dsync:[seltext$,..],..}
  facesheet:null,   // JFacesheet
  callback:null,
  called:null,
  _i:null,          // instance index
  _rec:null,
  /*
   * Reset
   */
  reset:function() {
    this.tuiSections = {};
    this.tuiPars = {};
    this.qs = {};
    this.qts = {};
    this.qcs = {};
    this.osyncs = {};
    this.qsyncs = {};
    this.dsyncs = {};
    this.data = {};
    this._rec = null;
    clearChildren(this.doc);
  },  
  /*
   * Get requested JParInfo
   */
  getParInfo:function(pid, callback, requestor) {
    par = TemplateUi._pars[pid];
    this.callback = callback;
    if (par) {
      if (par.reset)
        par.reset();
      this.addPar(par);
    } else {
      this.working(true);
      TemplateUi._requestPar(this, pid, requestor);
    }
  },
  isBlank:function() {
    for (var qref in this.qs) {
      var q = this.qs[qref];
      if (q.isBlank())
        return true;
    }
  },
  /*
   * Add paragraph + conditionless injects to the doc
   * - pis: [JParInfo,..]
   */
  addParInfos:function(pis) {
    for (var i = 0; i < pis.length; i++) {
      this.addParInfo(pis[i]);
    }
  },
  /*
   * Add a paragraph to the doc
   * - pi: JParInfo
   */
  addParInfo:function(pi) {
    this._cacheQuestions(pi.reset());
    this.working(false);
    var tuiSection = this._getTuiSection(pi.suid);
    tuiSection.appendChild(this._buildTuiPar(pi));
    this.refresh();
    if (this.callback) {
      this.callback();
      this.callback = null;
    }
  },
  addPars:function(pars, after/*=null*/) {
    for (var i = 0; i < pars.length; i++) 
      this.addPar(pars[i], after);
  },
  addPar:function(par, after) {
    this._cacheQuestions(par.reset());
    this.working(false);
    var tuiSection = this._getTuiSection(par.suid);
    var tuiPar = this._buildTuiPar(par);
    if (after == null)
      tuiSection.appendChild(tuiPar);
    else
      tuiSection.insertBefore(tuiPar, after.nextSibling);
    this.refresh();
    if (this.callback) {
      this.callback(par);
      this.callback = null;
    }
  },
  /*
   * Turn on working icon
   */
  working:function(on) {
    if (on) 
      this.doc.addClass('tuiwork');
    else 
      this.doc.removeClass('tuiwork');
  },
  /*
   * Refresh document (run thru visibility tests and reformat)
   */
  refresh:function() {
    for (qref in this.qs) {
      var q = this.qs[qref];
      this._onChange(q);
    }
  },
  /*
   * Show/hide free text tags
   */
  showFreeTags:function(show) {
    if (show) 
      this.doc.removeClass('nofree');
    else
      this.doc.addClass('nofree');
  },
  /*
   * Get updated sync values
   * - deserialized optional (omit to receive JSON)
   * Returns {
   *   'qsyncs':{qsid:[seltext,..],..},
   *   'osyncs':[osid,..],  // contains only 'on' osyncs
   *   'dsyncs':{dsynci:[seltext,..],..}
   *   } 
   */
  getSyncValues:function(deserialized) {
    var osids = [];
    for (var osid in this.osyncs) {
      if (this.osyncs[osid]) {
        osids.push(osid);
      }
    }
    var syncs = {
      'qsyncs':this.qsyncs,
      'osyncs':osids,
      'dsyncs':this.dsyncs};
    return (deserialized) ? syncs : Json.encode(syncs);
  },
  /*
   * Entry form: Builds record of question selections for each visible q 
   * @return {fid:'seltext',..}
   */
  getRecord:function(prefix) {
    var fidStart = (prefix) ? prefix.length : 0;
    var rec = this._rec || {};
    for (var qref in this.qs) {
      var q = this.qs[qref];
      var tuiq = q.tuiq;
      if (TemplateUi._isShow(tuiq)) {
        var fid = q.dsync.substr(fidStart);
        if (fid) 
          rec[fid] = (q.Opts.isBlank() && ! q.Opts.isSel()) ? null : q.Opts.getSelText();
      }      
    }
    return rec;
  },
  /*
   * Builds array of selected options for each visible q 
   * @return {qref:[opt,..],..}
   */
  getSelected:function() {
    rec = {};
    for (var qref in this.qs) {
      var q = this.qs[qref];
      var tuiq = q.tuiq;
      if (TemplateUi._isShow(tuiq)) 
        if (q.Opts.isSel()) 
          rec[qref] = q.Opts.getSels();
    }
    return rec;
  },
  /*
   * Assign question selections from record
   * - rec: {dsync:'seltext',..}
   * - prefix: optional, e.g. 'imm.' to match rec prop 'dateGiven' to dsync 'imm.dateGiven' 
   */
  setRecord:function(rec, prefix) {
    var fidStart = (prefix) ? prefix.length : 0;
    this._rec = rec;
    for (var qref in this.qs) {
      var q = this.qs[qref];
      if (q.dsync) {
        var fid = q.dsync.substr(fidStart);
        if (rec && rec[fid]) {
          q.setByValue(rec[fid]);
          this._onChange(q);  // TODO
        } else {
          q.reset();
          this._onChange(q);
        }
      }
    }     
  },
  setField:function(fid, value, prefix) {
    var fidStart = (prefix) ? prefix.length : 0;
    for (var qref in this.qs) {
      var q = this.qs[qref];
      if (q.dsync) {
        if (q.dsync.substr(fidStart) == fid) {
          q.setByValue(value);
          this._onChange(q);
        }
      }
    }     
  },
  /*
   * Produce document output 
   * Returns plain text with only <br> tags
   */
  out:function() {
    this._out = [];
    this._crawl(this.doc);
    return this._out.join('');
  },
  _crawl:function(e) {
    if (TemplateUi._isHide(e) || e.hasClass('noprt'))
      return;
    if (e.tuiqis && e.tuiqis.children.length == 0) 
      return;
    switch (e.tagName) {
      case 'H1':
      case 'H2':
      case 'H3':
      case 'B':
      case 'U':
        this._out.push('<' + e.tagName + '>');
        this._out.push(e.innerHTML);
        this._out.push('</' + e.tagName + '>');
        return;
      case 'BR':
        this._out.push('<' + e.tagName + '/>');
      case 'A':
        this._out.push(e.innerHTML);
        return;
    }
    if (e.children.length > 0) {
      for (var i = 0; i < e.children.length; i++) 
        this._crawl(_$(e.children[i]));
      if (e.className == 'tuip')
        this._out.push('<br><br>');
    } else {
      var text = e.getInnerText();
      if (text != null) 
        this._out.push(text);
    }
  },
  /*
   * Cache questions from paragraph 
   */
  _cacheQuestions:function(par) {
    for (var i = 0, j = par.Questions.length - 1; i <= j; i++) {
      var q = par.Questions[i];
      //var qt = pi.par.qts[i];
      var qt = q;
      // q.lt = qt.lt;
      this._addProps(q, qt, par, i == j);
      this._cacheTest(q, qt.Tests);
      this._cacheCommands(q, qt.Actions);
      var qref = par.suid + '.' + par.uid + '.' + q.uid;
      this._cacheQuestion(qref, q);
    }
  },
  _cacheQuestion:function(qref, q) {
    q.ref = qref;
    this.qs[qref] = q;
  },
  /*
   * Add helper props to question and options:
   *   q.blank      // true if default option is '(in paren)'
   *   q.oixs       // {ouid:ix} map
   *   q.hasOsyncs  // true if any opt sync present
   *   o.text       // doc text for option
   */
  _addProps:function(q, qt, par, last) {
    if (q.Opts) {
      q.oixs = {};  // {ouid:ix}
      for (var ix = 0; ix <= q.loix; ix++) {
        var o = q.Opts[ix];
        if (o.sync) 
          q.hasOsyncs = true;
        q.oixs[o.uid] = ix;
      }
      if (q.mix == 1 && (q.Opts[0].text.substr(0, 1) == '(' || q.Opts[0].uid == 'Select'))
        q.multiOnly = true;
    }
    if (q.type == C_Question.TYPE_ALLERGY || q.type == C_Question.TYPE_MED) 
      this._addCloningProps(q, qt, par, last);
  },
  /*
   * Add cloning props:
   *   q._ict         // instance count 
   *   q.master       // true for master question
   *   q.erx          // will handle thru NewCrop
   *   q.medNameOnly  // for legacy medpicker
   *   q.last         // indicates last med question of par (to show NewCrop anchor) 
   */
  _addCloningProps:function(q, qt, par, last) {
    q._ict = 0;
    q.master = true;
    if (q.type == C_Question.TYPE_MED) {
      if (me.isErx()) {
        q.medNameOnly = true;
        q.last = last;
        NewCrop.setQuestionErx(q, par['in'], qt.outData);
      }
      if (q.uid.substring(0, 3) == '@rf') 
        this._addRefillProps(q);
    } else if (q.type == C_Question.TYPE_ALLERGY) {
      if (me.isErx())
        NewCrop.setQuestionErx(q);
    }
  },
  /*
   * Add props for denoting med question as RX refill pop:
   *   q.refill  // true
   *   q.rx      // meds from incoming dsync
   */
  _addRefillProps:function(q) {
    if (this.facesheet) {
      var meds = this.facesheet.activeMeds;
      if (meds != null && this.data != null) {
        for (var i = 0; i < meds.length; i++) {
          var med = meds[i];
          med.checked = this._isRefill(med.name);
        }
      }
      q.refill = true;
      q.rx = {
        'docs':this.facesheet.docs,
        'client':this.facesheet.client,
        'meds':meds};
    }
  },
  _isRefill:function(medName) {
    if (this.data.refill == null)
      this._extractRefillDsyncs();
    for (var length in this.data.refill) {
      var meds = this.data.refill[length];
      for (var i = 0; i < meds.length; i++) 
        if (meds[i] == medName)
          return true;
    }
  },
  /*
   * Assigns data.refill from consolidated dsyncs 'refillLength@j' and 'refillMed+i@j'
   * data.refills = {length:[medname,..],..}
   */
  _extractRefillDsyncs:function() {
    var lengths = this._extractLengths();
    var meds = this._extractMeds();
    this.data.refill = {};
    for (var j in lengths) 
      Map.appendInto(this.data.refill, lengths[j], meds[j]);
  },
  _extractLengths:function() {  // {'0':'30 day','1':'90 day'}
    var lengths = {};
    for (var dsyncij in this.data.dsyncs) {
      var a = dsyncij.split('@');
      var j = (a.length == 2) ? a[1] : '0';
      if (a[0] == 'refillLength') 
        lengths[j] = this.data.dsyncs[dsyncij][0];
    }
    return lengths;
  },
  _extractMeds:function() {  // {'0':['medName',..],'1':['medName',..]}
    var meds = {};
    for (var dsyncij in this.data.dsyncs) {
      var a = dsyncij.split('@');
      var j = (a.length == 2) ? a[1] : '0';
      var dsync = a[0].split('+')[0];
      if (dsync == 'refillMed') 
        Map.pushInto(meds, j, this.data.dsyncs[dsyncij][0]);
    }
    return meds;
  },
  /*
   * Cache test to questions referenced in the args  
   */
  _cacheTest:function(q, tests) {
    if (tests) {
      test = this._fixTestsRef(tests);
      var qt = {
        'q':q,
        'test':test,
        'lastResult':null
        };
      var qrefs = tests.getQrefs();
      for (i = 0; i < qrefs.length; i++) {
        var qref = qrefs[i];
        if (this.qts[qref] == null) {
          this.qts[qref] = [qt];
        } else {
          this.qts[qref].push(qt);
        }
      }
    }
  },
  /*
   * Cache commands
   */
  _cacheCommands:function(q, actions) {
    if (actions) {
      for (var i = 0; i < actions.length; i++) {
        var cond = this._fixTestsRef(actions[i].Tests);
        var action = this._fixActionRef(actions[i]);
        var cmd = {
          'cond':cond,
          'action':action};
        Map.pushInto(this.qcs, q.id, cmd);
      }
    }
  },
  /*
   * Prepends method references and adds _i to args
   */
  _fixMethodRefs:function(expr) {
    if (expr) {
      expr = expr.replace(/always\(/g, "TemplateUi._always(");
      expr = expr.replace(/isMale\(/g, "TemplateUi._isMale(" + this._i);
      expr = expr.replace(/isFemale\(/g, "TemplateUi._isFemale(" + this._i);
      expr = expr.replace(/calcBmi\(/g, "TemplateUi._calcBmi('" + this._i);
      var iarg = this._i + "',";
      expr = expr.replace(/isSel\(/g, "TemplateUi._isSel('" + iarg);
      expr = expr.replace(/notSel\(/g, "TemplateUi._notSel('" + iarg);
      expr = expr.replace(/setDefault\(/g, "TemplateUi._setDefault('" + iarg);
      expr = expr.replace(/olderThan\(/g, "TemplateUi._olderThan('" + iarg);
    }
    return expr;
  },
  _fixTestsRef:function(tests) {
    if (tests) {
      var args = [];
      for (var i = 0; i < tests.length; i++) {
        args.push(this._fixTestRef(tests[i]));
        if (i < tests.length - 1)
          args.push(tests[i].op == C_Test.OP_AND ? '&&' : '||');
      }
      return args.join(' ');
    }
  },
  _fixTestRef:function(test) {
    var args = this._getArgsString(test.args);
    switch (test.id) {
      case C_Test.ALWAYS:
        return 'TemplateUi._always' + args;
      case C_Test.IS_SEL:
        return 'TemplateUi._isSel' + args;
      case C_Test.NOT_SEL:
        return 'TemplateUi._notSel' + args;
      case C_Test.IS_MALE:
        return 'TemplateUi._isMale' + args;
      case C_Test.IS_FEMALE:
        return 'TemplateUi._isFemale' + args;
      case C_Test.OLDER_THAN:
        return 'TemplateUi._olderThan' + args;
      case C_Test.YOUNGER_THAN:
        return 'TemplateUi._youngerThan' + args;
    }
  },
  _fixActionRef:function(action) {
    var args = this._getArgsString(action.args);
    switch (action.id) {
      case C_Action.SET_DEFAULT:
        return 'TemplateUi._setDefault' + args;
    }
  },
  _getArgsString:function(args) {
    if (args && args.length) 
      return '(' + this._i + ',"' + args.join('","') + '")';
    else 
      return '(' + this._i + ')';
  },
  /*
   * Update state as a result of question change 
   */
  _onChange:function(q) {
    if (q.type == C_Question.TYPE_MED) {
      var qi = this._formatMed(q);
      if (qi) {
        this._updateSyncCache(qi);
      }
    } else {
      this._formatTuiq(q);
      this._updateSyncCache(q);
      this._runTests(q);
      this._runCommands(q);
    }
  },
  /*
   * Update sync cache, if necessary 
   */
  _updateSyncCache:function(q) {
    if (! q.master) {
      this._cacheQDSyncs(q);
      if (q.hasOsyncs) {
        this._cacheOSyncs(q.Opts.getSels(), true);
        this._cacheOSyncs(q.Opts.getUnsels(), false);
      }
    }
  },
  _cacheQDSyncs:function(q) {
    if (q.sync == null && q.dsync == null) return;
    if (q.Opts == null) return;
    var sel = q.Opts.getTexts(q.Opts.getSels());
    if (q.sync) {
      if (q.deleted && this.isCloneSuffixed(q.sync)) {
        delete this.qsyncs[q.sync];        
      } else {
        this.qsyncs[q.sync] = sel;
      }
    }
    if (q.dsync) {
      if (q.deleted) {
        delete this.dsyncs[q.dsync];
      } else {
        this.dsyncs[q.dsync] = sel;        
      }
    }
  },
  _cacheOSyncs:function(opts, value) {
    for (var i = 0; i < opts.length; i++) {
      var o = opts[i];
      if (o.sync) {
        this.osyncs[o.sync] = value;
      }
    }     
  },
  /*
   * Run thru question commands
   */
  _runCommands:function(q) {
    if (TemplateUi._isShow(q.tuiq)) {
      var cmds = this.qcs[q.id];
      if (cmds) {
        for (var i = 0; i < cmds.length; i++) {
          var cmd = cmds[i];
          if (cmd.cond == null || eval(cmd.cond)) {
            eval(cmd.action);
          }
        }
      }
    }
  },
  /*
   * Run thru tests that reference this question
   */
  _runTests:function(q) {
    var qts = this.qts[q.ref];
    if (qts == null) return;
    for (var i = 0, j = qts.length; i < j; i++) {
      var t = qts[i];
      var vis = eval(t.test);
      if (vis != t.lastResult) {
        t.lastResult = vis;
        this._setTuiqVis(t.q, vis);
      }
    }
  },
  /*
   * Set question visibility and initiate onchange if necessary
   */
  _setTuiqVis:function(q, show) {
    var tuiq = q.tuiq;
    var isShow = TemplateUi._isShow(tuiq);
    if (show && ! isShow) {
      TemplateUi._show(tuiq);
      this._onChange(q);
    } else if (! show && isShow) {
      TemplateUi._hide(tuiq);
      this._onChange(q);        
    }
  },
  /*
   * Returns <div> of section if exists, else creates new one
   */
  _getTuiSection:function(suid) {
    var tuiSection = this.tuiSections[suid];
    if (tuiSection == null) {
      tuiSection = Html.Div.create('tuis');
      this.doc.appendChild(tuiSection);
    }
    return tuiSection;
  },
  /*
   * Builds par HTML
   * FORMAT_PARAGRAPH:
   *     <div class='tuip' tui=this>
   *       <span..  // see _buildTuiq
   *     </div>
   * FORMAT_ENTRY_FORM:
   *     <ul class='entry' tui=this>
   *       <li..    // see _buildTuiq
   *     </ul>
   */
  _buildTuiPar:function(par) {  
    //var par = pi.par;
    var tuip;
    if (this.format == TemplateUi.FORMAT_PARAGRAPH) {
      tuip = Html.Div.create('tuip')
    } else {
      tuip = Html.Ul.create('entry q');
    }
    for (var i = 0; i < par.Questions.length; i++) {
      //var qt = par.qts[i];
      var q = par.Questions[i];
      var qt = q;
      var tuiq = this._buildTuiq(qt, q);
      q.tuiq = tuiq;
      tuip.appendChild(tuiq);
    }
    tuip.tui = this;
    this.tuiPars[par.id] = tuip;
    return tuip;
  },
  /*
   * Build question static text and anchor(s)
   * FORMAT_PARAGRAPH:
   *   <span class='tuiq'>
   *     ..  // see specific builders below  
   *   </span> 
   * FORMAT_ENTRY_FORM:
   *   <li>
   *     ..  // see specific builders below
   *   </li>
   */
  _buildTuiq:function(qt, q) {
    var tuiq;
    if (this.format == TemplateUi.FORMAT_PARAGRAPH) 
      tuiq = Html.Span.create('tuiq');
    else 
      tuiq = Html.create('li');
    if (q.type == C_Question.TYPE_MED) 
      this._buildForMeds(tuiq, qt, q);
    else
      this._buildForQuestion(tuiq, qt, q);
    if (this.format >= TemplateUi.FORMAT_ENTRY_FORM) {
      if (tuiq.innerHTML == '') 
        tuiq.className = 'hide';
    }
    return tuiq;
  },
  /*
   * Regular question type and buttons
   * FORMAT_PARAGRAPH:
   *   <span class='tuiq [button]' q=q a=a mult=mult brk=brk>  
   *     <span>BT</span>
   *     <a q=q>Single selected</a>   // a
   *     <span ams=ams mu=mu>         // mult (only if necessary)
   *       <span>BTMS</span>
   *       <a q=q>Multi selected</a>  // ams 
   *       <span>ATMS</span>
   *       <span a=amu>               // mu (only if necessary)
   *         <span>BTMU</span> 
   *         <a q=q>Multi unsel</a>   // amu
   *         <span>ATMU</span>
   *       </span>
   *     </span>
   *     <span>AT</span>
   *     <span>break</span>           // brk (see _createBrk)
   *   </span>
   * FORMAT_ENTRY_FORM:
   *   <li q=q a=a mult=mult>  // tuiq
   *     <label class='tui'>q.desc</label>
   *     <span class='q'>         // serves as single and mult
   *       <a q=q>Selections</a>  // both a and ams
   *     </span>
   *   </li>   
   */
  _buildForQuestion:function(tuiq, qt, q) {
    if (this.format == TemplateUi.FORMAT_PARAGRAPH) {
      if (q.type == C_Question.TYPE_BUTTON) tuiq.className = 'tuiq button noprt';
      if (qt.bt) 
        tuiq.appendChild(TemplateUi.Span.create(null, TemplateUi.gf(qt.bt, this._i) + (q.Opts ? ' ' : '')));
      if (q.Opts) {
        this._appendAndRef(tuiq, this._createPopAnchor(q), 'a');
        if (q.mix != null) {
          var mult = Html.Span.create();
          if (qt.btms) 
            mult.appendChild(TemplateUi.Span.create(null, ' ' + TemplateUi.gf(qt.btms, this._i) + ' '));
          this._appendAndRef(mult, this._createPopAnchor(q), 'ams');
          if (qt.atms) 
            mult.appendChild(TemplateUi.Span.create(null, ' ' + TemplateUi.gf(qt.atms, this._i)));
          if (qt.btmu) {
            var mu = Html.Span.create();
            mu.appendChild(TemplateUi.Span.create(null, ' ' + TemplateUi.gf(qt.btmu, this._i) + ' '));
            this._appendAndRef(mu, this._createPopAnchor(q), 'amu');
            if (qt.atmu) mu.appendChild(TemplateUi.Span.create(null, ' ' + TemplateUi.gf(qt.atmu, this._i)));
            this._appendAndRef(mult, mu, 'mu');
          }
          this._appendAndRef(tuiq, mult, 'mult');
        }
      }
      if (qt.at) tuiq.appendChild(Html.Span.create(null, ' ' + TemplateUi.gf(qt.at, this._i)));
      tuiq.appendChild(this._createBreak(q, qt.brk));
    } else {
      if (q.Opts) {
        tuiq.appendChild(Html.Label.create('tui' + this.format, q.desc));
        var span = Html.Span.create('q');
        var a = this._createPopAnchor(q);
        span.appendChild(a);
        tuiq.appendChild(span);
        tuiq.a = a;
        if (q.mix != null) {
          tuiq.mult = span;
          tuiq.mult.ams = a;
        }
      }
    }
  },
  /*
   * Cloneable med pickers
   * <span class='tuiq meds' q=mq tuiqis=tuiqis>  // q=master question
   *   <span>BT</span>
   *   <br>
   *   <span>                      // tuiqis
   *     ..                        // clone instances go here; see appendMed
   *   </span>
   *   <span class='button'>
   *     <a q=mq>Select a Med</a>
   *   </span> 
   *   <span>AT</span>
   * </span>   
   */
  _buildForMeds:function(tuiq, qt, q) {
    tuiq.className = 'tuiq meds';
    if (qt.bt) tuiq.appendChild(Html.Span.create(null, qt.bt + ' '));
    tuiq.appendChild(createBr());
    this._appendAndRef(tuiq, Html.Span.create(), 'tuiqis');
    var span;
    if (q.erx == null) {
      span = Html.Span.create('button ind noprt');
      span.appendChild(this._createPopAnchor(q, (q.Opts) ? q.getValue() : 'Select a Med'));
      tuiq.appendChild(span);
      tuiq.appendChild(createBr());
    } else if (q.last) {
      span = Html.Span.create('button ind noprt');
      span.appendChild(this._createPopAnchor(q, 'Prescribe...', 'erx', TemplateUi._popNewCrop));
      tuiq.appendChild(span);
      tuiq.appendChild(createBr());
    }
    if (qt.at) tuiq.appendChild(Html.Span.create(null, ' ' + qt.at));
  },
  refreshNewCropPlan:function(fs) { 
    var audits = fs.audits;
    var meds, q, qOpposite;
    for (var key in audits) {
      meds = audits[key];
      q = NewCrop.getQuestion(key);
      qOpposite = NewCrop.getOppositeMedQuestion(key);
      this._addNewCropMeds(q, meds, qOpposite);
    }
  },
  _addNewCropMeds:function(q, meds, qOpposite) {
    this._removeMedInstances(q);
    q.ncmeds = {};
    if (meds) {
      for (var i = 0; i < meds.length; i++) {
        var m = meds[i];
        if (qOpposite && qOpposite.ncmeds && qOpposite.ncmeds[m.name]) {
          // med exists on opposite, don't duplicate here
        } else {
          q.ncmeds[m.name] = 1;
          q.med = m;
          var qi = this._formatMed(q);
          if (m.rx) {
            var fta = qi.tuiqi.brk.ft.a;
            TemplateUi._setFreetext(fta, m.rx);
          }
        }        
      }
    }
  },
  /*   
   * Appends med clone instance to tuiq.tuiqis
   * - q: master question
   *   q.med = med props (assigned by med picker)
   *   <span>                        // tuiqis (container span)
   *     <span a=a brk=brk>          // 'tuiqi' span created for this instance
   *       <span>&bull;&nbsp;</span>
   *       <a q=qi>Med</a>           // a (qi=instance of master question)
   *       <span>break</span>        // brk (see _createBreak)
   *     </span>
   *   </span>
   * Returns qi
   */
  _addMedInstance:function(q) {
    var qi = this._clone(q);
    qi.tuiq = q.tuiq;
    var tuiqi = Html.Span.create();
    tuiqi.appendChild(Html.Span.create().html('&bull;&nbsp;'));
    this._appendAndRef(tuiqi, this._createPopAnchor(qi, qi.getValue(), ''), 'a');
    this._appendAndRef(tuiqi, this._createBreak(qi, TemplateUi._BRK_LF, TemplateUi._ALWAYS_SEE_FT), 'brk');
    qi.tuiq.tuiqis.appendChild(tuiqi);
    qi.tuiqi = tuiqi;
    qi.med.id = qi.ref;
    q.med = null;
    return qi;
  },
  /*
   * Remove med clone instance
   */
  _removeMedInstance:function(qi) {
    TemplateUi._hide(qi.tuiqi);
  },
  /*
   * Remove all med clone instances from master
   */
  _removeMedInstances:function(q) {
    clearChildren(q.tuiq.tuiqis);
  },
  /*
   * Create an instance of master question
   *   qi.mq     // ref to master question
   *   qi._i     // instance index
   *   qi.ref    // suffixed with +instance
   *   qi.dsync  // suffixed with +instance
   */
  _clone:function(q) {
    q._ict++;
    var qi = Object.deepclone(q);
    qi.master = false;
    qi.mq = q;
    qi._i = q._ict;
    qi.ref = this._addCloneSuffix(q.ref, qi._i);
    if (qi.refills) {
      qi.refills = null;
      qi.meds = null;
    }
    if (q.dsync) {
      qi.dsync = this._addCloneSuffix(q.dsync, qi._i);
    }
    return qi;
  },
  _addCloneSuffix:function(id, _i) {
    return id + '+' + _i;  
  },
  _isCloneSuffixed:function(id) {
    return id.indexOf('+') > -1; 
  },
  /*
   * Format med question (returned by med picker)
   * Returns qi   
   */
  _formatMed:function(q) {
    if (q.med) 
      q.setByValue(this._formatMedName(q.med));  //qSetFormattedOption(q, this._formatMedName(q.med));
    var qi;
    if (q.tuiqi) {  // updating from a clone
      var qi = q;
      if (qi.med) {
        qi.tuiqi.a.innerText = qi.getValue(); //qSelText(qi);
      } else {
        this._removeMedInstance(qi);
        qi.deleted = true;  
      }
    } else {
      if (q.med) {  // returned from med pop (adding from master)
        qi = this._addMedInstance(q);
      } else if (q.refill && q.meds) {  // returned from rx pop (add refills)
        this._formatRefills(q);
      }
    }
    return qi;
  },
  /*
   * Format refills returned from rx pop
   * Invokes page.medsChangedCallback to reflect rx print in meds history
   */
  _formatRefills:function(q) {
    if (q.meds.length > 0) {
      for (var i = 0; i < q.meds.length; i++) {
        var med = q.meds[i];
        q.med = med;
        q.setByValue(this._formatMedName(q.med));
        var qi = this._addMedInstance(q);
        var fta = qi.tuiqi.brk.ft.a;
        TemplateUi._setFreetext(fta, med.rx);
      }
      Ajax.post(Ajax.SVR_POP, 'printMeds', q.meds, 'medsChangedCallback'); 
    } 
  },
  _formatMedName:function(med) {
    var text = med.name;
    if (med.text)
      text += ': ' + med.text;
    return text;
  },
  /*
   * Format regular question 
   */
  _formatTuiq:function(q) {
    if (q.Opts) {
      this._formatTuiqOpts(q);
    }
  },
  _formatTuiqOpts:function(q) {
    var tuiq = q.tuiq;
    var className = (! q.Opts.isSel()) ? ((q.Opts.isBlank()) ? 'blank' : 'df') : 'ud';
    if (q.mix == null) {
      tuiq.a.innerText = TemplateUi.gf(q.Opts.getSelText(), this._i);
      tuiq.a.className = className;
    } else {
      var sel = q.Opts.getSelIx();
      var unsel = (q.Opts.unsel && q.Opts.unsel.length) ? q.Opts.unsel : null;
      if (this.format >= TemplateUi.FORMAT_ENTRY_FORM) {
        tuiq.mult.ams.innerHTML = q.Opts.getSelText(1);
        tuiq.mult.ams.className = className;
      } else {
        if (q.Opts.getSel() == null) {
          TemplateUi._show(tuiq.mult);
          TemplateUi._hide(tuiq.a);
          tuiq.mult.ams.innerHTML = TemplateUi.gf(q.Opts.getSelText(q.lt), this._i);
          tuiq.mult.ams.className = className;
          if (tuiq.mult.mu) {
            if (unsel) {
              TemplateUi._show(tuiq.mult.mu);
              tuiq.mult.mu.amu.innerHTML = TemplateUi.gf(q.Opts.getUnselText(q.lt), this._i);
              tuiq.mult.mu.amu.className = className;
            } else {
              TemplateUi._hide(tuiq.mult.mu);
            }
          }
        } else {
          TemplateUi._hide(tuiq.mult);
          TemplateUi._show(tuiq.a);
          tuiq.a.innerText = TemplateUi.gf(q.Opts.getSelText(), this._i);
          tuiq.a.className = className;
        }
      }
    }
    if (this.format >= TemplateUi.FORMAT_ENTRY_FORM && q.sel.length == 0 && q.Opts.isBlank()) {
      tuiq.a.innerText = TemplateUi._UNDERLINE;
    }    
  },
  /*
   * Returns <a> for question popup
   */
  _createPopAnchor:function(q, innerText, className, onClick) {
    innerText = innerText || '___';
    className = className || 'df';
    onClick = onClick || TemplateUi._pop;
    // var a = createAnchor(null, null, className, innerText, null, onClick);
    var a = Html.Anchor.create(className, innerText);
    a.onclick = onClick.curry(a);
    a.q = q;
    q.a = a;
    return a;
  },
  /*
   * Returns
   * <span ft=ft>
   *   <span>brk chars</span>
   *   <span>       // ft (see _createFt)
   *     ..  
   *   </span>      
   *   <break tag(s)>
   * </span>
   */
  _createBreak:function(q, breakType, alwaysSeeFt) {
    var s = Html.Span.create();
    var validFor = this._appendBeforeBrk(s, breakType);
    var className = (alwaysSeeFt) ? 'ft noprt' : 'noprt';
    this._appendAndRef(s, Html.Span.create(className), 'ft');
    this._appendAfterBrk(s, breakType);
    if (alwaysSeeFt || this.ftPlacement <= validFor) {
      this._createFt(s, q);
    }
    return s;
  },
  _appendBeforeBrk:function(s, breakType) {
    var validFor;
    switch (breakType) {
      case TemplateUi._BRK_PERIOD:
        s.appendChild(Html.Span.create(null, '. '));
        validFor = TemplateUi.FT_BETWEEN_SENTENCES; 
        break;
      case TemplateUi._BRK_PERIOD_LF:
      case TemplateUi._BRK_PERIOD_PAR:
        s.appendChild(Html.Span.create(null, '. '));
        validFor = TemplateUi.FT_BETWEEN_LFS; 
        break;
      case TemplateUi._BRK_LF:
        s.appendChild(Html.Span.create(null, ' '));
        validFor = TemplateUi.FT_BETWEEN_LFS; 
        break;
      case TemplateUi._BRK_SPACE:
        s.appendChild(Html.Span.create(null, ' '));
        break;
      case TemplateUi._BRK_COLON:
        s.appendChild(Html.Span.create(null, ': '));
        break;
      case TemplateUi._BRK_SEMI:
        s.appendChild(Html.Span.create(null, '; '));
        break;
    }
    return validFor;
  },
  _appendAfterBrk:function(s, breakType) {
    switch (breakType) {
      case TemplateUi._BRK_PERIOD_LF:
      case TemplateUi._BRK_LF:
        s.appendChild(createBr());
        break;
      case TemplateUi._BRK_PERIOD_PAR:
        s.appendChild(createBr());
        s.appendChild(createBr());
        break;
    }
  },
  /*
   * Create freetext tag 
   * - brk: parent span
   * - q: question preceding freetext
   *      q.ftq will be used to store the freetext question
   * <span ft=ft>   // brk
   *   <span>       // brk.ft
   *     <a q=q brk=brk>Free text</a>  // ft.a
   *     <span>&nbsp;</span>
   *   </span>
   * </span> 
   */
  _createFt:function(brk, q) {
    var onClick = 'TemplateUi._ft(this)';
    var a = createAnchor(null, null, 'ft', null, null, onClick);
    a.q = q;
    a.brk = brk;
    brk.ft.appendChild(a);
    brk.ft.appendChild(Html.Span.create(null, ' '));
    brk.ft.a = a;
  },
  /*
   * To <parent>, appends <child> and assigns a property reference to <child> 
   */
  _appendAndRef:function(parent, child, prop) {
    parent.appendChild(child);
    parent[prop] = child;
  }
}
/*
 * TemplateUi statics
 */
TemplateUi._BRK_PERIOD = 0;
TemplateUi._BRK_SPACE = 1;
TemplateUi._BRK_COLON = 2;
TemplateUi._BRK_SEMI = 3;
TemplateUi._BRK_PERIOD_LF = 4;
TemplateUi._BRK_PERIOD_PAR = 5;
TemplateUi._BRK_LF = 6;
TemplateUi._BRK_FRAG = 7;
TemplateUi._LT_COMMA = 0;
TemplateUi._LT_REGULAR = 1;
TemplateUi._LT_BULLET = 2;
TemplateUi._LT_SPACE = 3;
TemplateUi._ALWAYS_SEE_FT = true;
TemplateUi._UNDERLINE = '_____________';
/*
 * Instance management
 */
TemplateUi._ict = 0;
TemplateUi._instances = {};
TemplateUi._cacheInstance = function(tui) {
  tui._i = TemplateUi._ict++;
  TemplateUi._instances[tui._i] = tui;
}
TemplateUi.getInstance = function(_i) {
  return TemplateUi._instances[_i];
}
TemplateUi.clearInstance = function(tui) {
  delete TemplateUi._instances[tui._i];
  tui = null;
}
/*
 * Template retrieval
 */
TemplateUi._pars = {};      // {pid:par,..}
TemplateUi._requestPar = function(tui, pid, requestor) {
  requestor = requestor || Ajax.Templates.getPar;
  requestor(pid, function(par) {
    TemplateUi._receivePar(tui, par);
  })
}
TemplateUi._receivePar = function(tui, par) {
  var pid = par.id;
  TemplateUi._pars[pid] = par;
  tui.addPar(par);
}
/*
 * UI methods
 */
TemplateUi.noCurtain = TemplateUi.NO_CURTAIN;
TemplateUi._pop = function(a, useLastPos) {
  var q = a.q;
  var tui = q.tuiq.parentElement.tui;
  if (q.Opts.soix == 2 && q.Opts.getSelIx() < 2) {
    q.Opts.toggle();
    tui._onChange(q);
    if (tui.onchange)
      tui.onchange(q);
    return;
  }
  a.setFocus();
  if (q.type == C_Question.TYPE_CALENDAR)
    q.calFmt = 2;  //CAL_FMT_ENTRY
  var qp = (tui.format == TemplateUi.FORMAT_PARAGRAPH) ? QuestionPop : QuestionPopEntry;
  var pos = (useLastPos == true) ? Pop.POS_LAST : Pop.POS_CURSOR;
  if (pos != Pop.POS_LAST)
    Pop.cacheMousePos();
  qp.pop(q, function(q) {
    tui._onChange(q);
    if (tui.onchange)
      tui.onchange(q);
  }, pos);
//  showQuestion(a.q, TemplateUi.noCurtain, null, true, function(q) {
//    var tui = q.tuiq.parentElement.tui;
//    tui._onChange(q);
//    if (tui.onchange)
//      tui.onchange(q);
//  });
}
TemplateUi._popNewCrop = function(a) {
  var q = a.q;
  var tui = q.tuiq.parentElement.tui;
  NewCrop.sendFromTui(a.q, tui, 
    function(fs) {
      switch (NewCrop.sent) {
        case NewCrop.SENT_PLAN:
          tui.refreshNewCropPlan(fs);
          break;
        case NewCrop.SENT_ALLERGY:
          tui.refreshNewCropAllergies(fs);
          break;
      }
    });
}
TemplateUi._setFreetext = function(fta, text) {  // leave text null to assign from ftq
  var ftq = TemplateUi._getFreetextQuestion(fta);
  if (text != null) 
    ftq.setByValue(text);  //qSetFormattedOption(ftq, text);
  else 
    text = ftq.getValue();  //qFormattedOptText(ftq);
  fta.innerText = text;
  if (text == '') 
    _$(ftq.a.brk.ft).addClass('noprt');    
  else  
    _$(ftq.a.brk.ft).removeClass('noprt');
}
TemplateUi._getFreetextQuestion = function(fta) {
  if (fta.q.ftq == null) {
    fta.q.ftq = QuestionFree.asDummy();  //newFreetextQuestion();
    fta.q.ftq.a = fta;
  }
  return fta.q.ftq;
}
TemplateUi._ft = function(fta) {
  var ftq = TemplateUi._getFreetextQuestion(fta);
  QuestionPop.pop(ftq, function(q) {
    fta.q.ftq = q;
    TemplateUi._setFreetext(ftq.a);
  })
  //showQuestion(ftq, TemplateUi.noCurtain, null, null, TemplateUi._ftCallback);
}
//TemplateUi._ftCallback = function(ftq) {
//  TemplateUi._setFreetext(ftq.a);
//}
TemplateUi._hide = function(e) {
  e.style.display = 'none';
  return e;
}
TemplateUi._isHide = function(e) {
  return (e.style.display == 'none');
}
TemplateUi._show = function(e) {
  e.style.display = '';
  return e;
}
TemplateUi._isShow = function(e) {
  return (e.style.display == '');
}
/* 
 * Template tests
 */
TemplateUi._isSel = function(_i, qref, ouid) {
  var tui = TemplateUi.getInstance(_i);
  var q = tui.qs[qref];
  if (q == null) return false;
  var oix = q.oixs[ouid];
  if (oix == null) return false;
  return TemplateUi._isSelected(q, oix);
},
TemplateUi._notSel = function(_i, qref, ouid) {
  return ! TemplateUi._isSel(_i, qref, ouid);
}
TemplateUi._always = function() {
  return true;
}
/* 
 * Template commands
 */
TemplateUi._setDefault = function(_i, qref, ouid) {
  var tui = TemplateUi.getInstance(_i);
  var q = tui.qs[qref];
  if (q == null) return false;
  var oix = q.oixs[ouid];
  if (oix == null) return false;
  if (q.def.length && q.def[0] == oix) return true;
  if (q._def == null) 
    q._def = q.def;  // save original default for resetting
  //q.def = [oix];
  q.setDefault(oix);
  if (q.sel.length == 0) {
    tui._onChange(q);
  }
  return true;
},
TemplateUi._olderThan = function(_i, age) {
  var tui = TemplateUi.getInstance(_i);
  if (tui.facesheet && tui.facesheet.client) 
    return tui.facesheet.client.age > age;
  else
    return null;  
},
TemplateUi._youngerThan = function(_i, age) {
  return ! TemplateUi._olderThan(_i, age);
},
TemplateUi._isMale = function(_i) {
  var tui = TemplateUi.getInstance(_i);
  if (tui.facesheet && tui.facesheet.client) 
    return tui.facesheet.client.sex == 'M';
  else
    return null;  
},
TemplateUi._isFemale = function(_i) {
  var tui = TemplateUi.getInstance(_i);
  if (tui.facesheet && tui.facesheet.client) 
    return tui.facesheet.client.sex == 'F';
  else
    return null;  
},
TemplateUi._calcBmi = function(_i) {
  return;
}
/* 
 * General template functions
 */
TemplateUi._isSelected = function(q, oix) {
  if (! this._isInserted(q)) {
    return false;
  }
  return q.isSelected(oix);
},
TemplateUi._isInserted = function(q) {
  var tuiq = q.tuiq;
  var tuip = tuiq.parentElement;
  return (TemplateUi._isShow(tuiq) && TemplateUi._isShow(tuip))
}
TemplateUi.gf = function(text, _i) {
  var isMale = TemplateUi._isMale(_i);
  if (text == null) return text;
  text = " " + text + " ";
  if (isMale) {
    text = text.replace(/Woman /g, "Man ");
    text = text.replace(/ woman /g, " man ");
    text = text.replace(/She /g, "He ");
    text = text.replace(/ she /g, " he ");
    text = text.replace(/Her /g, "His ");
    text = text.replace(/ her /g, " his ");
    text = text.replace(/ herself/g, " himself");
  } else {
    text = text.replace(/Man /g, "Woman ");
    text = text.replace(/ man /g, " woman ");
    text = text.replace(/He /g, "She ");
    text = text.replace(/ he /g, " she ");
    text = text.replace(/His /g, "Her ");
    text = text.replace(/ his /g, " her ");
    text = text.replace(/ himself/g, " herself");
    text = text.replace(/ him /g, " her ");
  }
  return text.substring(1, text.length - 1);
}
TemplateUi.Span = {
  create:function(cls, html) {
    return Html.Span.create(cls).html(html);
  } 
}