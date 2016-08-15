// Template engine
// (c)2009 LCD Solutions Inc.
var X_OFFSET = -50;
var Y_OFFSET = 8;
var fromPreview;
var doc;
var template;
var session;
var map;
var reqPids;       // {pid:count}  for pars requested (not injected) count for non-clonable always 1 
var pidByRef;      // {pref:pid}  pref="suid.puid"
var pByRefi;       // {prefi:<p>}  prefi="plan.+pulmPlan@2370"  
var clonedParCt;   // {pid:instanceCt}
//var usedQueue;     // [{"pid":pid,"ct":ct}]  pars to addToUsed while loading
var qByRef;        // {qref:q}  qref="suid.puid.quid"
var jCache;        // {pid:pi]  for caching getParInfos requests, critical for removePar processing
var questions;     // {qid:q}
var tq;            // {qid:qidi}  temporary, for pointing old-style qidi @refs to new
var qSyncs;        // {qsync:[qid]}
var oSyncs;        // {osync:[{"qid":qid,"ix":ix}]}
var qTestsByQref;  // {qref:[{"q":q,"test":t,"visible":v}]}
var qTestsByInj;   // {pref:[{"q":q,"test":t,"visible":v}]}
var qActionsByQid; // {qid:[{"qid":qid,"cond":c,"action":a,"runOnce":r}]}
var qActionsPend;  // [{"qid":qid,"cond":c,...}]  qActions that failed to execute
var injParsByQref; // {qref:[pref]}
var injTests;      // {pref:cond}  cond=concatenated conditions
var qidsOfInjPar;  // {pid:[qid]}
var terse;         // hide section, no linefeeds in pars
var allFrags;      // [{"qids":[qid],"brk":b,"lt":l,"sels":[oix],"a":a}]
var qOutData;      // [{"qid":qid,"pid",pid,"puid":puid,"suid":suid,"out":qt.out}]
var qDsyncs;       // {dsync:qid}
var pendingOut;    // {dtid:{"records":{pk:{"fields":{dcid:value}}}}}
var dosQids;       // [qid]  questions containing date-of-service {dos} tag
var actions;       // {"stack":[action],...}  see initObjects()
var changed;       // {qid:q}  questions to process in onChangeLoop
var injectPool;    // [[pref,injectorPidi],..]  pooled injection requests
var trackOpts;     // {key:!,..}  options that generate order sheet 
var ipcs;
var parsing;      
var dirty = false;
var now = new Date();
// var today = (now.getMonth() + 1) + "/" + now.getDate() + "/" + now.getYear();
var reqParId;
var parId;  // preview pid
var lastPopQid;  
var popWin;
var showFT = false;
var pbv = 0;
var sessionAge;
var sessionSex;
var sessionBirth;
var VISIBLE = "v";
var VISIBLE2 = "v noprt";
var HIDDEN = "h";
var HIDDEN2 = "h2";  // for paragraphs
var NOTEXT = "notext";  // for questions
var PENDING_SYNC_ON = "PSO";
var tableInserted = false;
var allergyInsertedQid = null;  // qid of allergy question, if present
var medRefillId = null;  // qid of med refill question, if present
var docEx;  // last document exception
var headerId;  // ID of @header DIV
var showingLoading = false;
function actionDebug() {
  stop.now();  // bogus code to invoke debugger
}
//var defaults;
var waitingOnPar = 0;
//var pids; // array of par ids in document to elim need for building dupe injects  
//var mapParsByPid;

// User customization fields
var twoColumnsOnly = false;
var logofile = "";  // custom logo, first page only  
var logofile2 = "";  // custom logo, page 2+
var sigfile = "";
var showIntro = false;
var showOutro = false;
var ignoreActionErrors = false;

// Start from preview 
function init(templateJson, userId, pid) {
  tlog("init", true);
  fromPreview = true;
  template = templateJson;
  if (pid != null) {  // from preview
    preset = null;
    session = fakeSession();
    parId = pid;
    initObjects(false);
  }
}
// Start from console
// Pass s for session
// Pass tpid for custom template
// Pass tid + tname for new custom template
function initConsole(s, tpid, tid, tname) {
  tlog("initConsole", true);  
  doc = $("doc");
  if (UsedParList.create) { 
    UsedParList = UsedParList.create(_$('usedli')).bubble('ondraw', syncUsedClass).bubble('onclickremove', askRemovePar).bubble('onremove', removeUsed);
  }
  if (s) {
    setNewSession(s);
  } else if (tpid) {
    openTemplate(tpid);
  } else {
    newCustomTemplate(tid, tname);
  }
}
// Create a new session after console init
function setNewSession(s) {
  tlog("setNewSession", true);
  var sid;
  var closed;
  if (session && session.locked) {
    sid = session.id;
    closed = session.closed;
  } 
  session = s;
  if (session) {
    session.real = true;
    template = s.template;
    setMap(s.map);
    NewCrop.loadFromSession(me.isErx(), session);
  }
  if (sid) {
    closeSession(sid, closed);
    return;
  }
  resetUi();
}
// Create or apply template preset 
function setTemplatePreset(p, isApply) {
  if (isApply) {
    if (p.actions != null && p.actions.length > 0) {
      actions.ix = actions.stack.length;
      for (var i = 0; i < p.actions.length; i++) {
        var action = p.actions[i];
        if (! isCloneAction(action)) {
          actions.stack.push(action);
        }
      }
      actions.loading = true;
      finishActions();
      enable("actionUndo");
      setDirty();
    }
  } else {
    preset = p;
    template = p.template;
    setMap(p.map);
    setNewSession(null);
  }  
}
function openCustomTemplateCallback(p, caption) {
  if (caption == "Apply Custom Template") {
    if (p.actions != null && p.actions.length > 0) {
      actions.ix = actions.stack.length;
      for (var i = 0; i < p.actions.length; i++) {
        actions.stack.push(p.actions[i]);
      }
      actions.loading = true;
      finishActions();
      enable("actionUndo");
      setDirty();
    }
  } else {
    preset = p;
    template = p.template;
    setMap(p.map);
    setNewSession(null);
  }
}
function setMap(m) {
  map = TemplateMap.revive(m);
  if (ParPreviewTrigger.create)
    ParPreviewTrigger.create();
  ParPreviewTrigger.load(map);
  UsedParList.loadMap(map);
}
// Reset UI after a template, map, session/preset load
function resetUi() {
  tlog("resetUi", true);
  ignoreActionErrors = (me.userGroupId == 24);
  if (session) {
    session.ro = ! me.Role.Artifact.noteCreate;
    if (session.real && ! session.ro && session.lockedBy != null) {
      Pop.Confirm.show(
        "This note is currently being edited by <b>" + session.lockedBy + "</b>.<br/>While a note is locked, <b>it is recommended that you open it as read-only</b> to prevent editing conflicts.",
        "Open note as read-only",
        'none',
        "Ignore lock and edit note anyway",
        'none',
        true,
        null,
        handleLock, 
        'null');
      return;
    }
  }
  resetUiFinish();
}
function resetUiFinish() {
  tlog("resetUiFinish", true);
  loadSessionVars();
  initObjects(false);
  renderTemplateMap();  
  resetMenus();
  lockSession();
  setTemplateCustoms();
  startSessionActions()
}
function setTemplateCustoms() {
  logofile = lu_custom.logofile;
  logofile2 = lu_custom.logofile2;
  sigfile = lu_custom.sigfile;
  twoColumnsOnly = false;
  var c = template.custom;
  if (c != null) {
    if (c.logofile) logofile = c.logofile;
    if (c.logofile2) logofile2 = c.logofile2;
    if (c.sigfile) sigfile = c.sigfile;
    if (c.twoColOnly) twoColumnsOnly = true;    
  }
}
function handleLock(confirmed) {
  if (confirmed == null) {
    window.close(); 
  } else {
    if (confirmed) {
      session.ro = true;
    }
    doWork("resetUiFinish()", "Building note");
  }
}
function loadSessionVars() {
  if (session == null) {
    session = fakeSession();
  }
  sessionAge = session.cbirth != null ? age() : "";
  sessionSex = session.csex ? "male" : "female";
  sessionBirth = session.cbirth != null ? session.cbirth : "";
}
function fakeSession() { // to allow session references to work for preview / custom template edit
  return {
    "real":false,
    "cbirth":"01/01/1960",
    "dos":"01/01/2009",
    "cid":"1001",
    "csex":false,
    "cname":"Custom Template Patient",
    "uname":"Custom Template Doctor",
    "ucompany":"Custom Template Facility",
    "closed":0,
    "signature":"",
    "cdata1":"",
    "cdata2":"",
    "cdata3":"",
    "actions":[],
    "noteDate":(preset != null) ? preset.noteDate : null
  };  
}
function lockSession() {
  if (session.real && ! session.ro && ! session.closed) {
    session.locked = true;
    sendRequest(2, "action=lock&id=" + session.id);
  }
}
/*
 * Initial thread end-point
 * Kicks off session actions evaluation
 */
function startSessionActions() {
  if (session.real) {
    if (session.closed) {
      $("doc").innerHTML = (session.closed == 3) ? session.html : session.actions;
      //showMsg(""); closePop();  // for some reason, this fixes the chopped missing lines bug
      Pop.Working.close();
      return;
    }
    if (session.noteDate < "2010-06-15 00:00:00") {
      session.oldstyle = 1;
    }
    if (session.actions != null) {
      actions.stack = session.actions;
      if (actions.stack.length > 0) {
        setTimeout("startActions('Building note')", 5);
        return;  
      } 
    } else {
      prependMapAutoActions();
      if (actions.stack.length > 0) {
        setTimeout(function() {
          Pop.Working.show('Initializing auto-apply paragraphs');
          actions.ix = 0;
          actions.autoapplying = true;
          startGetPars();
        }, 5);
        return;
      }
    }
  } else {
    if (preset != null && preset.actions != null) {
      actions.stack = preset.actions;
      setTimeout("startActions('Building note presets')", 5);
      return;  
    }
  }
  Pop.Working.close();  // no new thread spawned, we're done
}
/*
 * Prepend actions.stack with any user-customized autopars
 */
function prependMapAutoActions() {
  for (var i = 0; i < map.Sections.length; i++) {
    var s = map.Sections[i];
    for (var j = 0; j < s.Pars.length; j++) {
      var p = s.Pars[j];
      if (p.auto) {
        actions.stack.unshift("getPar(" + p.parId + ")");
      }
    }
  }
}
function popNonFsActions(stack) {
  var s = [];
  for (var i = 0; i < stack.length; i++) {
    var a = stack[i];
    if (isFacesheetAction(a)) {
      s.push(a);
    } else {
      break;
    }
  }
  return s;
}
function initObjects(keepActions) {
  //usedQueue = [];
  reqPids = {};
  terse = false;
  qByRef = {};
  questions = {};
  tq = {};
  qSyncs = {};
  oSyncs = {};
  qTestsByQref = {};
  qTestsByInj = {};
  qActionsByQid = {};
  qOutData = [];
  qDsyncs = {};
  allFrags = [];
  dosQids = [];
  injParsByQref = {};
  injTests = {};
  qidsOfInjPar = {};
  qActionsPend = [];
  defaults = {};
  reqParId = -1;
  tableInserted = false;
  allergyInsertedQid = null;
  medReset();
  allerReset();
  comboReset();
  medRefillId = null;
  changed = {};
  pByRefi = {};
  injectPool = [];
  trackOpts = {};
  ips = {};
  if (! keepActions) {
    clonedParCt = {};
    jCache = {};
    pidByRef = {};
    parseTemplate(template);
    actions = {
        stack:[],       // array of actions
        bad:[],         // actions that couldn't be run
        loading:false,  // in the process of loading actions
        undos:[],       // undoable action descriptions
        undoStack:null, // stack before removePar() action 
        ix:0,           // currently executing index
        restart:0,      // count of restarts (to check for exceed threshhold)
        error:null      // last error encountered when eval'ing action
        };
  }
  if (parId != null) {
    getPar(parId);  // used by adminPreview
  }
  dirty = false;
  showFT = !! lu_console.showFreeTags;
}
function resetToolUndo() {
  var tooltip = "";
  if (actions.undos.length == 0) {
    disable("actionUndo");
  } else {
    tooltip = "Undo: " + actions.undos[actions.undos.length - 1];
  }
  if (typeof(toolUndo) != UNDEFINED) toolUndo.title = tooltip;
}

// AJAX callbacks
function timeoutCallback() {
  alert("We're sorry, but it appears your session has timed out.");
}
function parseTemplate(t) {
  template = t;
  template.hiddenSectionsShown = false;
  docInit();
}
function nextCloneIndex(pid, value) {  // incremented if no value supplied
  if (value) {
    clonedParCt[pid] = value;
  } else {
    if (clonedParCt[pid] == null) {
      clonedParCt[pid] = 1;
    } else {
      clonedParCt[pid]++;
    }
  }
  return clonedParCt[pid];
}
// Attach clone suffix to children
function setCloneIds(col, cloneSuffix) {
  for (var i = 0, i2 = col.length; i < i2; i++) {
    var o = col[i];
    if (o.origId == null) {
      o.origId = o.id;
    }
    o.id = o.origId + cloneSuffix;  
  }
}
// Attach clone suffix to single ref
function appendCloneSuffix(ref, csuf) {
  if (csuf != null) {
    return ref + csuf;
  } else {
    return ref;
  }
}
/*
 * Fix question ref if it references a cloneable par
 * If qref refs self, append clone suffix; otherwise, append pidi (the injector of the clone) 
 */  
function fixCloneRef(qref, pidi, csuf) {
  if (pidi == null || ! isCloneRef(qref)) {
    return qref;
  }
  var pref = qref.substring(0, qref.lastIndexOf("."));
  var qpid = pidByRef[pref];
  var p = parsePidi(pidi);
  if (qpid == p.pid && csuf != null) {
    return qref + csuf;
  }
  return qref + "@" + pidi;
}
function isCloneRef(ref) {
  return (ref.indexOf("+") > -1);
}
// Parse injection pool return
function parseParInfosPool(pool) {
  for (var i = 0; i < pool.length; i++) {
    parseParInfos(pool[i]);
  }
}
function parseParInfos(pis) {
  var pids = [];
  for (var i = 0; i < pis.length; i++) {
    pids.push(pis[i].par.id)
  }
  cloga("parseParInfos", pids);  
  var injectRef = null;
  if (actions.getting) {  // This is the initial collection of requested pars + conditionless injects... no special processing required, just get everything
    for (var i = 0; i < pis.length; i++) {
      if ((me.userGroupId == 3011 || me.userGroupId == 110) && i > 0 && pis[i].suid == 'rto') {
        //don't include (customization)
      } else {
        parseParInfo(pis[i], injectRef);
      }
    }
    waitingOnPar--;
    clog("waitingOnPar--=" + waitingOnPar);
    actions.skipGetPars = true;
    actions.getting = false;
    finishActions();
    return;
  }
  // Determine if paragraph returned is a result of conditioned injection
  // If so, assign condition to any other pars in the package
  var ref = pis[0].suid + "." + pis[0].par.uid;
  // If result of user request (map click), null out injTests to force par to always show
  if (reqPids[pis[0].par.id]) {
    injTests[ref] = null;
  } else if (injTests[ref] != null) {
    injectRef = ref;
  }
  var alreadyInDoc = parseParInfo(pis[0], injectRef);
  if (! alreadyInDoc) {  // if initial par was not already in doc, OK to get the remaining conditionless injects
    for (var i = 1; i < pis.length; i++) {
      if ((me.userGroupId == 3011 || me.userGroupId == 110) && pis[i].suid == 'rto') {
        //don't include (customization)
      } else {
        parseParInfo(pis[i], injectRef);
      }
    }
  }
  waitingOnPar--;
  clog("waitingOnPar--=" + waitingOnPar);
  if (waitingOnPar == 0) {
    if (actions.loading && actions.waitingOnPar) {
      finishActions(1);
      return;
    } else if (waitingOnPar == 0 && ! actions.loading && showingLoading) {
      closeLoading();  // happens during load when all actions executed but waiting on last injection
      return;
    } else if (waitingOnPar == 0 && actions.autoapplying) {
      closeLoading();
      return;
    }
  }
  // If just requested, run any inActions associated with pars in the group
  if (actions.inActionsAdded) {
    actions.loading = true;
    autosave(true);
    finishActions();
  }
  if (! actions.loading) { 
    applyCustomShows();
  }
}
function parseParInfosFromCache(pid, injector) {
  cloga("parseParInfosFromCache", [pid, injector]);
  var pidi = parsePidi(pid);
  var pi = clone(jCache[pidi.pid]);
  var csuf = "";
  if (pidi.cloneix) {
    csuf += "+" + pidi.cloneix;
  }
  if (injector) {
    csuf += "@" + injector;
  }
  pi.csuf = (csuf == "") ? null : csuf;
  var alreadyInDoc = parseParInfo(pi);
  if (! alreadyInDoc && pi.autoInj) {
    for (var i = 0; i < pi.autoInj.length; i++) {
      var inj = (isCloneRef(pi.autoInj[i])) ? pidi.pid + csuf : null;
      parseParInfosFromCache(pidByRef[pi.autoInj[i]], inj);
    }
  }  
}
// Returns true if not parsed because par already in doc 
var clogit; 
function parseParInfo(pi, injectRef) {  // injectRef (puid) is passed when this par comes from a conditioned inject by the injectRef par
  var ref = pi.suid + "." + pi.par.uid;
  pi.ref = ref;
  pi.refi = ref;  // initialize with static ref
  cloga("parseParInfo", [val(pi.par.id), injectRef], ref);
  clogit = (ref == "pe.heent");
  if (pi.csuf) {  // cloneable
    clog("cloneable, pi.csuf=" + pi.csuf);
    if (pi.par.origId == null) {
      pi.par.origId = pi.par.id;
      pidByRef[ref] = pi.par.id;   
      jCache[pi.par.id] = clone(pi);  // orig ID used for caching cloneables (i.e., single cache instance) 
    }
    // Create instance IDs
    pi.par.id = pi.par.origId + pi.csuf;
    pi.refi += pi.csuf;
    clog("new id=" + pi.par.id);
    // Make the suffix for attaching to questions 
    clog("clone suffix=" + pi.csuf);
    setCloneIds(pi.questions, pi.csuf);
    setCloneIds(pi.par.qts, pi.csuf);
    // Make "+" sync suffix (start of injection chain)
    if (pi.csuf.substring(0, 1) == "+") {
      pi.csyncsuf = "@" + pi.par.id;
    } else {
      var a = pi.csuf.split("@");
      pi.csyncsuf = "@" + a.pop();  
    }
  } else {
    pidByRef[ref] = pi.par.id;
    jCache[pi.par.id] = clone(pi); 
    pi.csyncsuf = "@" + pi.par.id;
  }  
  // Create pidi arg to append to all test/actions
  var pid = pi.par.id;
  pi.pidiArgs = "'" + pid + "'";
  if (pi.csuf) {
    pi.pidiArgs += ",'" + pi.csuf + "'";
  }
  if (! template.hiddenSectionsShown && pi.suid == "hpi") {
    showHiddenSections();
    template.hiddenSectionsShown = true;
  }
  // If injectRef supplied, associate this paragraph with the same inject condition
  if (injectRef != null) {
    copyInjParsForPid(injectRef, pi.refi, pid);
    if (isIncluded(pid)) {
      checkInjTest(pi.refi);
      return true;
    }
    addQidsOfInjPar(pi);
  }
  // If par already in document, ensure it's visible and return
  var par = _$(pidify(pid));
  if (par != null) {
    setParVisibility(par, true);
    if (reqParId == pi.par.id || reqParId == pi.par.origId) {
      Html.Animator.fade(par);
      par.scrollIntoView(false);
      //if (selSuid != null) {
      //  tlog("1 calling renderPars");
      //  renderPars(selSuid);
      //}      
      Pop.Working.close();
    }
    clog("Par " + ref + " already present");
    return true;
  }
  // Cache filtered questions 
  if (clogit) clog("cache filtered questions");
  for (var i = 0, i2 = pi.questions.length; i < i2; i++) {
    var q = pi.questions[i];
    q.sel = [];  // to force fresh selection in case question reused from jCache
    var sync = (q.sync != null);
    if (q.opts != null) {
      for (var j = 0; j <= q.loix; j++) {
        var o = q.opts[j];
        o.desc = o.uid;
        var text = getOptText(o);
        if (text == "{dos}") {
          dosQids.push(q.id);
        }
        o.text = filter(text, true);
        if (o.text.substring(0, 1) == "(") {
          o.blank = true;
        }
        if (o.sync != null) {
          sync = true;
        }
      }
    }
    // No syncing rules (to prevent syncing into certain sections)
    // TODO just set if the section's noSyncIn is set.
    if (sync && (pi.suid == "hpi" || pi.suid == "meds")) {
      q.sid = pi.sid;
      q.noSyncIn = true;
    }  
    q.char1 = q.uid.substring(0, 1);
    //q.cloning = (q.char1 == "@" || q.char1 == "!" || q.char1 == "?");
    q.oByRef = {};
    q.ref = ref + "." + q.uid;
    q.pid = pid;
    questions[q.id] = q;
    if (session.oldstyle && q.origId) {
      tq[q.origId] = q;
    }
    var qref = q.ref;
    if (pi.csuf) {
      qref += pi.csuf;  // make unique qref for clone instance  
    }
    qByRef[qref] = q;
  }
  // Add paragraph to document, display selected options, and sync
  if (clogit) clog("call docAddPar(pi)");
  par = _$(docAddPar(pi));
  if (par.className == VISIBLE && reqParId > -1 && pi.suid == selSuid)
    Html.Animator.fade(par);
  if (reqParId == pi.par.id || reqParId == pi.par.origId) {
    par.scrollIntoView(false);
    Pop.Working.close();
  }
  if (clogit) clog("format questions/sync loop");
  var q;
  var onChanges = (actions && ! actions.loading) ? {} : null;  // do onChanges now for par requested after initial load
  if (onChanges) clog("onChanges init to:{}");
  for (var i = 0; i < pi.questions.length; i++) {
    q = pi.questions[i];
    if (onChanges) onChanges[q.id] = q.id; 
    // q.ptag = par; can't be done here... too late
    if (q.sync) {
      if (isCloneRef(q.sync)) {
        if (q.origSync == null) {
          q.origSync = q.sync;
        }
        q.sync = q.origSync + pi.csyncsuf;
      }
      addQSync(q);
    }
    if (q.opts && ! q.cloning) {
      formatQuestion(q);
      for (var j = 0; j <= q.loix; j++) {
        var o = q.opts[j];
        if (o.sync != null) {
          q.osyncs = true;
          // For single options, only cache the first ("yes") value index.
          if (q.mix == null || j < q.mix) {
            if (j == 0) {
              addOSync(q, o, j);
            }
          } else {
            addOSync(q, o, j);
          }
        }
      }
    }
  }
  if (onChanges) onChangesLoop(onChanges);
  onChanges = null;
  // Format list fragments
  for (var i = 0; i < allFrags.length; i++) {
    stringFragText(allFrags[i]);
  }
  allFrags = [];
  // Cache <p> reference by prefi (instance pref)
  pByRefi[pi.refi] = par;
  // Check if injected par allows pending actions to now run; also see if any conditions for injection may now be tested
  if (clogit) clog("call runQActionsPend"); 
  runQActionsPend();
  runQTestsForInj(ref);
  if (clogit) clog("call checkInjTest"); 
  checkInjTest(pi.refi);
  // If just requested, run any inActions associated 
  if (pi.inActions && actions && ! actions.loading && ! showingLoading) {
    var a = eval(pi.inActions);
    if (! actions.inActionsAdded) {  // don't reset pointer on second pass here
      actions.ix = actions.stack.length;        
    }
    actions.stack = actions.stack.concat(a);
    actions.inActionsAdded = true;
    pi.inActions = null;
  }
  if (clogit) clog("done parseParInfo");
}
function oldstyleQid(qid) {
  var a = qid.split("@");
  if (a.length == 1) return null;
  return a[0] + "@" + a.pop();
}
function findGetParActionIndex(pid, ct) {  // ct=0 for non-cloneable, instance index for cloneable
  var a = "getPar('" + pid;
  if (ct) {
    a += "+";
  } else {
    ct = 0;
    a += "'";
  }
  var l = a.length;
  for (var i = 0; i < actions.stack.length; i++) {
    if (actions.stack[i].substr(0, l) == a) {
      if (ct <= 1) {      
        return i;
      }
      ct--;
    }
  }
  return -1;
}
function redoActions() {  // rebuild doc after removing an action
  docInit();
  initObjects(true);
  actions.loading = true;
  var pids = extractGetPars();
  UsedParList.reset().load(Pidi.from(pids));
  for (var i = 0; i < pids.length; i++) {
    parseParInfosFromCache(pids[i]);
  }
  actions.ix = 0;
  setTimeout("finishRedoActions()", 1);
}
function finishRedoActions() {
  while (actions.loading) {
    var action = actions.stack[actions.ix];
    try {
      tlog("action=" + action);
      if (! isGetPar(action)) {
        eval(action);
      }
      actions.ix++;
    } catch (e) {
      tlog("removePar e=" + e);
      actions.stack.splice(actions.ix, 1);
    }
    actions.loading = (actions.ix < actions.stack.length);    
  }  
  onChangesLoop(questions);
  applyCustomShows();
  autosave(true);
  Pop.Working.close();
}
function removePar(pidi, ct, wasRemoved, desc) {  // ct=0 for non-cloneable, instance index for cloneable, wasRemoved=true action already removed
  var pid = pidi.pidi;
  tlog("removePar(" + pid + ")", true);
  if (! wasRemoved) {
    var j = findGetParActionIndex(pid, ct);
    actions.undoStack = clone(actions.stack);
    actions.stack.splice(j, 1);
    actions.undos = ['Remove "' + desc + '"'];
    resetToolUndo();
  }
  redoActions();
  UsedParList.remove(pidi);
  //removeUsed(pid, ct);
}
function age() {
  var now = new Date();
  var born;
  try {
    born = new Date(session.cbirth);
  } catch (e) {
    born = new Date(1960, 1, 1);
  }
  return Math.floor((now.getTime() - born.getTime()) / (365.25 * 24 * 60 * 60 * 1000));
}
function docInit() {
  cloga("docInit");
  var h = [];
  if (session.closed == 1) {  // legacy signing style
    h.push("<div id='sig'>" + session.signature + "</div>");
  }
  if (template.title) {
    if (template.id == 16 && me.userGroupId == 2)
      template.title = '';
    h.push("<p id=title>" + template.title + "</p>");
  }
  h.push("<div id=dSections style='height:100%;'>");
  for (var sid in template.sections) {
    var s = template.sections[sid];
//    if (lu_console.hideAllerMedVitals && (s.uid == "aller" || s.uid == "meds")) {  // no one is using this yet
//      s.hide = true;
//    }
    if (s.uid.substring(0, 1) == "&") {
      s.hide = true;
    }
    h.push("<div id=" + sidify(s.id) + " uid=" + s.uid);
    if (s.hide) h.push(" h=true"); 
    h.push(" class=h>");
    if (s.title) {
      h.push("<p id='stitle-" + s.uid +  "' class=pTitle>" + s.title + "</p>");
    } else {
      h.push("<p class=h></p>");
    }
    h.push("</div>");
  }
  h.push("</div>");
  doc.innerHTML = h.join("");
  if (template.autos != null) {
    for (var i = 0; i < template.autos.length; i++) {
      var pi = template.autos[i];
      parseParInfo(pi, null);
    }
  }
}
// Build maps for syncing questions and options
function addQSync(q) {
  var a = qSyncs[q.sync];
  if (a == null) {
    a = [q.id];
  } else {
    a.push(q.id);
    // Sync up this new question to one already in the map
    syncQuestion(questions[a[0]]);
  }
  qSyncs[q.sync] = a;
}
function addOSync(q, o, i) {
  var opt = {"qid":q.id, "ix":i};
  var a = oSyncs[o.sync];
  if (a == null) {
    a = [opt];
  } else {
    var os = a[0];
    if (os.qid == PENDING_SYNC_ON) {
      // Pending sync in map, replace with real entry (and make sure option is on)
      a = [opt];
      if (! isSel2(q, i)) {
        setChecked2(q, i, true);
        formatQuestionAndFrags(q);
      }
    } else {
      // Add to map and sync this new one to one already in the map
      a.push(opt);
      syncOptions(questions[os.qid]);
    }
  }
  oSyncs[o.sync] = a;
}
function showHiddenSections() {  // show hidden sections once a par chosen
  for (var sid in template.sections) {
    var section = template.sections[sid];
    var s = $(sidify(section.id));
    if (s.getAttribute('uid').substring(0, 1) == "&") {
      s.hide = "";
      s.className = VISIBLE;
    }
  }
}
// Add paragraph HTML to appropriate section
function docAddPar(pi) {
  if (clogit) cloga("docAddPar", [pi.par.id]);
  var par = pi.par;
//  if (lu_console.hideAllerMedVitals && par.uid == "vitals") {  // no one is using
//    par.hide = true;
//  }
  var s = $(sidify(pi.sid));
  var p = document.createElement("div");
  p.sort = par.sort;
  // if clone par injected into same section, use same sort order as injector
  if (pi.csuf) {
    if (pi.csuf.substr(0, 1) == "@") {
      p.injector = $(pidify(pi.csuf.substr(1)));
    }
    if (p.injector && p.injector.suid == pi.suid) {
      p.sort = p.injector.sort;
    }
    // Make instance version of dsync
    if (p.injector) { 
      pi.dsyncsuf = p.injector.ref;
    }
  }
  if (s.getAttribute('h') != "true" && par.uid.substr(0, 1) != "&") {
    s.className = VISIBLE;
  }
  p.id = pidify(par.id);
  p.uid = par.uid;
  p.suid = pi.suid;
  p.className = visibleIf(pi.suid.substr(0, 1) != "@");
  p.ref = pi.ref;
  if (pi.suid == "@header") {
    headerId = p.id;
    if (isMenuChecked($("actionViewHeader"))) {
      p.className = "doc-header";
    }
  }
  var h = [];
  h.push("<p");
  if (par.hide) {
    h.push(" class=h");
  }
  h.push(">");
  //if (perm.yk) h.push("<span id='adminp' prt='n' class='noprt'><b>" + p.uid + "</b> " + pi.par.id + "</span>");
  if (par.qts.length == 1) {
    var q = par.qts[0];
    if (q.bt == null && q.at == null && pi.questions[0].opts == null) {
      h.push(docAddQuestion(q, p.id, false, pi));
    } else {
      h.push(docAddQuestion(q, p.id, true, pi));
    }
  } else {
    var frags = [];
    var fragging = false;
    var q;
    for (var i = 0, j = par.qts.length, k = j - 1; i < j; i++) {
      q = par.qts[i];
      if (q.brk == 7) {  // list fragment
        frags.push(q.id);
        fragging = true;
      } else if (fragging) {
        frags.push(q.id);
        addFragToQuestions(frags, q.brk, q.lt);
        fragging = false;
        frags = [];
      }
      h.push(docAddQuestion(q, p.id, (i == k), pi));
    }
  }
  h.push("</p>");
  if (clogit) clog("built html");
  p.innerHTML = h.join("");
  if (clogit) clog("assigned to innerHTML");
  var parSort = parseInt(p.sort);
  if (parSort != 32767) {
    var inserted = false;
    for (var i = 0; i < s.children.length; i++) {
      var p2 = s.children[i];
      if (p2.sort != null) {
        var p2Sort = parseInt(p2.sort);
        if (p2Sort > parSort) {
          s.insertBefore(p, p2);
          inserted = true;
          break;
        }
      }
    }
    if (! inserted) s.appendChild(p);
  } else {
    s.appendChild(p);
  }    
  if (clogit) clog("appended to section");
  // Default allergy popup if inserted
  if (allergyInsertedQid != null) {
    initAller(allergyInsertedQid);
    allergyInsertedQid = null;
  }
  // Run thru tests and assign initial visibility
  for (var i = 0; i < par.qts.length; i++) {
    var qt = par.qts[i];
    var q = questions[qt.id];
    q.qt = qt;
    q.tag = $(qidify(qt.id));
    q.ptag = p; 
    if (qt.btmu != null) q.tristate = 1;  // tristate checks only apply if question has btmu
    if (qt.test != null) {  // restored
      var test = appendPidiArgs(qt.test, pi);
      q.tag.className = visibleIf(eval(test)); 
    }  // restored
  }
  if (clogit) clog("ran thru tests and assigned initial vis");
  return p;
}
function addFragToQuestions(frags, brk, lt) {
  for (var i = 0; i < frags.length; i++) {
    questions[frags[i]].frag = {
        qids:frags,
        brk:brk,
        lt:lt,
        sels:null,
        a:null};  
  }
  allFrags.push(questions[frags[0]].frag);
}
// Return formatted HTML for question
function docAddQuestion(qt, pid, last, pi) {
  var par = pi.par;
  var q = questions[qt.id];
  var deletable = ! (pi.suid == "meds" || pi.suid == "med mgr" || pi.suid == "aller");
  if (pi.suid == 'med mgr' && q.uid == '@rfMed') 
    last = true;
  if (session.tid == 16 && pi.suid == 'plan' && q.uid == '@rfMed')
    last = true;
  var notext = (q.opts == null && qt.bt == null && qt.at == null); // || q.type == Q_TYPE_HIDDEN;
  q.lt = qt.lt;
  var clazz = VISIBLE;
  var h = [];
  if (q.type == Q_TYPE_HIDDEN) 
    h.push('<span class=h>');
  if (notext) {
    clazz = NOTEXT;
  } else if (qt.test) {
    clazz = HIDDEN;  // restored
    addQTest(q, qt.test, pi);
  }
  if (qt.actions) {
    for (var i = 0; i < qt.actions.length; i++) {
      addQAction(qt.id, qt.actions[i].cond, qt.actions[i].action, pi);
    }
  }
  if (qt.out) {
    qOutData.push({"qid":qt.id,"pid":par.id,"puid":par.uid,"suid":pi.suid,"out":qt.out});
  }
  if (q.dsync) {
    if (isCloneRef(q.dsync)) {
      if (q.origDsync == null) {
        q.origDsync = q.dsync;
      }
      q.dsync = denull(pi.dsyncsuf) + q.origDsync;
    }
    qDsyncs[q.dsync] = qt.id;
  }
  if (deletable) h.push("<span id=del_" + qt.id + " class=dunsel dq=" + qt.id + ">");  // del tag
  //if (perm.yk) h.push("<span id='adminq' prt='n' class='noprt'><b>" + q.uid + "</b> " + qt.id + "</span>");
  if (q.type == Q_TYPE_DATA_HM) {
    h.push("<span class=noprt prt=n style='display:block'><a href='javascript:' onclick='showPopHm(" + qt.id + ")'>Select Database History</a></span>");
    h.push("<span id=" + qidify(qt.id) + "></span>");  
  } else if (q.cloning) {
    if (q.char1 == "@") {  
      if (me.isErx()) { 
        NewCrop.setQuestionErx(q, par['in'], qt.out);
      }
      // Med pop
      var addText = (q.opts) ? q.opts[0].text : "Select Med";
      var rx = false;
      if (! q.erx) {
        if (pi.suid != "meds" && (q.uid == "@addMed" || q.uid == "@addMeds")) {  // add med in plan
          rx = true;
        } else if (pi.suid == "plan" && par.uid == "plan" && q.uid == "@rfMed") {  // ligogaster "continue"
          rx = true;
        }
      }
      var attribs = [
          "rxFt=" + bool(q.uid != '@dcMed' && (pi.suid == "med mgr" || pi.suid == "plan" || pi.suid == "treatment")),  // has rx freetext
          //"rx=" + bool(rx),  // has rx button
          "er=" + bool(q.erx),
          "disp=" + bool(pi.suid == "med mgr" && q.uid == "@rfMed")];
      var fn = "showPopMed()";
      if (q.erx) {
        fn = "showNewCrop(" + q.id + ")";
      } else {
        if (q.uid == "@rfMed" && ! rx) {
          fn = "showPopRx()";
          medRefillId = qidify(qt.id) + "o";
        }
      }
      h.push(docAddClonePop(qt, clazz, fn, addText, last, par.id, attribs, q.erx));
    } else if (q.char1 == "!") {  
    
      // Allergy pop
      allergyInsertedQid = qt.id;
      var fn = 'showPopAllergy()';
      if (me.isErx()) {
        NewCrop.setQuestionErx(q);
        fn = "showNewCrop(" + q.id + ")";
      }
      h.push(docAddClonePop(qt, clazz, fn, "Add Allergy", last, par.id, null, q.erx));
    } else {
      
      // Combo pop
      h.push(docAddClonePop(qt, clazz, "showPopCombo()", "Add " + q.desc, last, par.id));
    }
  } else {
  
    // Regular popup  
    if (q.char1 == "*") {
      h.push("<span class=noprt prt=n>");
    }
    h.push("<span id=" + qidify(qt.id) + " class=" + clazz + ">"); //1

    if (qt.bt != null) {
      h.push(filter(qt.bt, false, true));
    }
    if (q.opts != null) {
      if (qt.bt != null && q.lt != 2) {  // No spaces for bullets
        h.push(" ");  
      }
      if (q.mix != null) {
        h.push("<span id=" + qidify(qt.id + "sing") + " class=v>"); //2 
      }
      h.push("<a href='javascript:' id=" + qidify(qt.id + "o") + " class=df onclick=\"showPopQuestion('" + qt.id + "'); return false\">");
      h.push("___");
      h.push("</a> ");
      if (q.mix != null) {
        h.push("</span>");  //1
        h.push("<span id=" + qidify(qt.id + "mult") + " class=h>");  //2
        if (qt.btms != null) {
          h.push(filter(qt.btms) + " ");
        }
        h.push("<a href='javascript:' id=" + qidify(qt.id + "s") + " class=df onclick=\"showPopQuestion('" + qt.id + "'); return false\">");
        h.push("___");
        h.push("</a>");
        if (qt.lt != 2) {
          h.push(" ");
        }
        if (qt.atms != null) {
          h.push(" " + filter(qt.atms));
        }
        if (qt.btmu != null) {
          h.push("<span id=" + qidify(qt.id + "mu") + " class=h>");  //3
          h.push(" " + filter(qt.btmu) + " ");
          h.push("<a href='javascript:' id=" + qidify(qt.id + "u") + " class=df onclick=\"showPopQuestion('" + qt.id + "'); return false\">");
          h.push("___");
          h.push("</a>");
          if (qt.atmu != null) {
            h.push("&nbsp;" + filter(qt.atmu));
          }
          h.push("</span>");  //2
        }
        h.push("</span>");  //1
      }
    }
    if (qt.at != null) {
      h.push("&nbsp;" + filter(qt.at));
    }
//    if (q.icd) {
//      h.push("&nbsp;<a class=icd id=" + qidify(qt.id + "icd") + " href='javascript:' onclick='showPopIcd(" + par.id + "," + qt.id + ")'>ICD</a>");
//    }
    if (q.lt != 2 && ! q.frag) {  // bullets
      h.push(breakTypeBefore(qt.brk));
    }      
    if (q.icd || qt.brk == 0 || qt.brk == 4 || qt.brk == 5) {
      if (pi.suid == "impr") {
        h.push("&nbsp;<a class=icd id=" + qidify(qt.id + "icd") + " href='javascript:' onclick='showPopIcd(\"" + par.id + "\",\"" + qt.id + "\")'>ICD9</a>");
        h.push("&nbsp;<a class=icd id=" + qidify(qt.id + "icd10") + " href='javascript:' onclick='showPopIcd10(\"" + par.id + "\",\"" + qt.id + "\")'>ICD10</a>");
      }
      h.push(freeTextAnchor(qt.id));
    }
    h.push(breakTypeAfter(qt.brk));
    h.push("</span>");
    if (q.char1 == "*") {
      h.push("</span>");
    }
    if (clazz != VISIBLE && last) {
      h.push(freeTextAnchor(pid));
    }
  }
  if (deletable) h.push("</span>");  // close del tag      
  if (q.type == Q_TYPE_HIDDEN) 
    h.push('</span>');
  return h.join("");
}
function docAddClonePop(qt, clazz, addFn, addText, last, pid, attribs, erx) {  // optional extra attribs[] for question span
  var attr = (attribs) ? attribs.join(" ") : "";
  var qtid = qidify(qt.id);
  var h = [];
  h.push("<span name=clonePop " + attr + " qid=" + qt.id + " id=" + qtid + " pid=" + pid + " class=" + clazz + "><span class='clonebt'>");
  if (qt.bt != null) {
    h.push(filter(qt.bt) + " ");
  }
  h.push("</span><span id=" + qtid + "c name=clonekids></span>");
  var cls = (erx) ? 'h' : 'clone';
  h.push("<a class=" + cls + " href='javascript:' onclick='" + addFn + "' qid=" + qt.id + " id=" + qtid + "o kid=" + qtid + "c>" + addText + "</a>"); 
  // h.push("<br>");
  h.push("<span>");
  if (qt.at != null) {
    h.push("&nbsp;" + filter(qt.at));
    h.push(breakTypeBefore(qt.brk));
    h.push(breakTypeAfter(qt.brk));
  }
  h.push("</span></span><span class='cloneat'></span>");
  if (last) {
    h.push("<span>&nbsp;</span>" + freeTextAnchor(pidify(pid)));
    if (erx) {
      h.push("<a href='javascript:" + addFn + "' class='cmd erx'>Update/Prescribe...</a>");      
    }
  }
  h = h.join("");
  return h;
}

// Change DOS thruout document after change
function resetDosSpans(dos) {  
  var a = $$("spandos");
  for (var i = 0; i < a.length; i++) {  
    a[i].innerText = dos;
  } 
  for (var i = 0; i < dosQids.length; i++) {
    setCalValue(dosQids[i], dos);
  }
}
// Returns true if popup saves formatted value in q.opts[0] (e.g. calculator, calendar) 
function isFormattedOptionPopup(q) {
  return (q.char1 == "#" || q.char1 == "%"); 
}
// Save formatted value in q.opts[0] (e.g. calc, cal)
function setFormattedOption(qid, value) {
  var q = questions[qid];
  q.opts[0].text = value;
  q.opts[0].blank = null;
  q.sel = [0];  // no effect except to change option color to non-default
  formatQuestionAndFrags(q);
  if (q.sync != null) {
    syncQuestion(q);
  }
  return q;  
}
function firstSyncQid(sync) {
  if (sync == null) return null;
  var a = oSyncs[sync];
  if (a == null) return null;
  for (var i = 0; i < a.length; i++) {
    if (a[i].qid == PENDING_SYNC_ON) {
      return PENDING_SYNC_ON;
    }
    if (isInserted2(a[i].qid)) {
      return a[i].qid;
    }
  }
  return null;

}
// Resets all rendered questions to their q.def settings
function resetDoc() {
  for (var qid in questions) {
    var q = questions[qid];
    if (q.sel.length != 0) {
      if (! q.cloning) {
        qReset(q);
        formatQuestion(q); 
      }
    }
  }
}
// Render formatted option text for a question
//lon = false;
function formatQuestion(q) {
  var hqid = qidify(q.id);
  var clazz = "";
  var a;
  var blank = false;
  var sel = q.sel;
  if (sel.length == 0) {
    sel = q.def;
    clazz = "df";
  }
  if (q.icd) {
    if (! (sel[0] > q.mix)) {
      var o = q.opts[sel[0]];
      var icds = ['icd', 'icd10'];
      for (var i = 0; i < icds.length; i++) {
        var icda = $(hqid + icds[i]);
        var oicd = o[icds[i]];
        var oicdDesc = o[icds[i] + 'Desc'];
        var oicdIncomplete = o[icds[i] + 'Incomplete'];
        if (icda.className != 'icdset') {
          if (oicd) {
            icda.innerText = "(" + oicd + ")";
            icda.icd = oicd;
            if (oicdIncomplete) {
              icda.title = 'INCOMPLETE: ' + oicdDesc;
              icda.className = 'blank';
              blank = true;
            } else {
              icda.title = oicdDesc;
              icda.className = 'dficd';
            }
          } else {
            icda.innerText = (i == 0) ? "ICD9" : 'ICD10';
            icda.icd = null;
            icda.title = '';
            icda.className = 'icd';
          }
        }
      }
    }
  }
  if (q.mix == null) {
    // Single option question
    a = $(hqid + "o");
    var o = q.opts[sel[0]];
    a.innerText = o.text;
    if (o.blank) {
      blank = true;
      a.className = "blank";
    } else {
      a.className = clazz;
    }
    if (q.frag) {
      q.frag.sels = [a.innerText];
      q.frag.fromSel = true;
    }
    if ((template.id == 14 || template.id == 26 || template.id == 32) && q.uid.contains('title'))
      a.className += ' bu';
  } else {
    // Multi option questions, filter out the syncs that have already been displayed
    // TODO this may have to use the always-show-syncs flag.
    var fsel = filterSync(q, sel);
    var funsel = filterSync(q, q.unsel);
    if (fsel.length == 0 && funsel.length == 0 & q.mix > 0) {  // NOTE: added q.mix > 0 to force show of first NVD symptoms question. this can be taken out if an always-show-sync flag is added
      // All sels and unsels filtered out, hide question
      $(hqid).className = HIDDEN;
    } else {
      if (fsel.length == 0) {
        // All sels filtered out, reset sel to single option if exists, otherwise we can't filter--restore sel
        if (q.mix == 0) {
          fsel = sel;
        } else {
          fsel = [0];
        }
      }
      if (! isMultiSelected(q, fsel)) {
        $(hqid + "sing").className = VISIBLE;
        $(hqid + "mult").className = HIDDEN;
        a = $(hqid + "o");
        var o = q.opts[fsel[0]];
        var text;
        if (o.text.indexOf("{all}") >= 0) {
          // All unsels filtered out, hide question
          if (funsel.length == 0) {
            $(hqid).className = HIDDEN;
          } else {
            text = o.text.replace(/\{all\}/, stringOptText(q, funsel, false));
          }
        } else {
          text = stringOptText(q, fsel, true);
        }
        a.innerHTML = text;
        if (o.blank) {
          blank = true;
          a.className = "blank";
        } else {
          a.className = clazz;
        }
      } else {
        $(hqid + "sing").className = HIDDEN;
        $(hqid + "mult").className = VISIBLE;
        a = $(hqid + "s");
        a.innerHTML = stringOptText(q, fsel, true);
        a.className = clazz;
        var span = $(hqid + "mu");
        if (span != null) {
          if (funsel != null && funsel.length > 0) {
            span.className = VISIBLE;
            a = $(hqid + "u");
            a.innerHTML = stringOptText(q, funsel, false);
            a.className = clazz;
          } else {
            span.className = HIDDEN;
          }
        }
      }
    }
  }
  if (q.frag) {
    q.frag.a = a;
  } 
  q.blank = blank;
  if (actions && (! actions.loading || actions.inActionsLoading)) {
    onChange(q.id);
  }
}
// Do above plus string fragments
function formatQuestionAndFrags(q) {
  formatQuestion(q);
  if (q.frag) {
    stringFragText(q.frag);
  }
}
function stringFragText(frag) {
  tlog("stringFragText([" + frag.qids[0] + ",...])", true);
  if (frag.lt != 0) {
    return;  // if simple list or bullet list, nothing to do here
  }
  var frags = [];
  var left = 0;
  var vf;
  for (var i = frag.qids.length - 1; i >= 0; i--) {
    if (isInserted2(frag.qids[i])) {
      vf = questions[frag.qids[i]].frag;
      if (vf.sels == null) return;
      vf.vis = true;
      vf.left = left;
      left += vf.sels.length;
    }
  }
  if (vf == null) return;
  vf.first = true;
  for (var i = 0; i < frag.qids.length; i++) {
    var f = questions[frag.qids[i]].frag;
    if (f.vis) {
      if (f.left == 0) {
        var s = joinOptTextList(f.sels, f.fromSel);
        if (! f.first && f.sels.length == 1) {  
          s = trim(andOr(f.fromSel)) + " " + s;
        }
        f.a.innerHTML = s + breakTypeBefore(f.brk);
        break;
      } else {
        var s = f.sels.join(", ");
        if (f.left > 1) {
          s += ",";
        }
        f.a.innerText = s;
      }
    }
  }
}

// Append pidi arg to all test/actions
function appendPidiArgs(fn, pi) {
  fn = fn.replace(/null,null/g, pi.pidiArgs);
  return fn;
}

// Add question test to qTestsByQref map
function addQTest(q, test, pi) {
  test = appendPidiArgs(test, pi);
  var qTest = {"q":q, "test":test, "visible":null};  // restored
  // var qTest = {"q":q, "test":test, "visible":true};
  if (test.substr(0, 10) == 'isInjected') {
    var a = test.split("'");
    var pref = a[1];
    pushInto(qTestsByInj, pref, qTest);
  } else {
    var refs = {};
    var tests = test.split(")");
    for (var t = 0; t < tests.length - 1; t++) {
      var a = tests[t].split(/\('|',/);
      if (a.length > 0) {
        refs[a[1]] = a[1];
      }
    }
    for (var ref in refs) {
      pushInto(qTestsByQref, ref, qTest);
    }
  }
}

// Add action to qActionsByQid map
function addQAction(qid, cond, action, pi) {
  if (cond) {
    cond = appendPidiArgs(cond, pi);
  }
  action = appendPidiArgs(action, pi);
  var qAction = {"qid":qid, "cond":cond, "action":action};
  qAction.runOnce = isRunOnce(cond, action);
  var qActions = qActionsByQid[qid];
  if (qActions == null) {
    qActionsByQid[qid] = [qAction];
  } else {
    qActionsByQid[qid].push(qAction);
  }
}

// If condition-less or an injection, flag to prevent from running again for efficiency
function isRunOnce(cond, action) {
  var word = action.split("(")[0];
  if (word == "inject" || word == "hideTitle") return true;  // injects only run once  
  //if (word == "setDefault") return true;       // setDefaults only run once  
  if (cond != null) return false;              // result of test condition may change
  if (word == "setTextFromSel") return false;  // source selection may change
  return true;                                 // no condition, action will run once
}  

// Add injection test to injParsByQref map
function addInjPar(qref, test, prefi) {
  //var qid = qByRef[qref].id;
  var cond = "isInserted('" + qref + "')";
  if (test != null) cond += " && " + test; 
  addInjParByQref(qref, prefi, cond); 
  // Associate injection with other question referenced in test
  if (test != null) {
    var a = test.split("'");
    if (a.length > 1) {
      var qref2 = a[1];
      if (qref2 != qref) {
        addInjParByQref(qref2, prefi, cond);
      }
    }
  }
}
function addInjParByQref(qref, prefi, cond) {
  var refs = injParsByQref[qref];
  if (refs == null) {
    injParsByQref[qref] = [prefi];
  } else {
    injParsByQref[qref].push(prefi);
  }
  var conds = injTests[prefi];
  if (conds == null) {
    injTests[prefi] = "(" + cond + ")";
  } else {
    injTests[prefi] += " || (" + cond + ")";
  }
}
// Copy any test conditions established for ref1 to ref2
function copyInjParsForPid(ref1, ref2, pid2) {
  cloga("copyInjParsForPid", [ref1, ref2, pid2]);
  if (ref1 == ref2) return;
  
  // If ref2 already in document without condition, don't
  // establish a condition here.
  if (isIncluded(pid2) && injTests[ref2] == null) {
    return;
  }
  for (var qref in injParsByQref) {
    var refs = injParsByQref[qref];
    for (var i = 0; i < refs.length; i++) {
      if (refs[i] == ref1) {
        addInjParByQref(qref, ref2, injTests[ref1]);
      }
    }
  }
}
// Associate all questions to injected par for easy reference
function addQidsOfInjPar(pi) {
  var qids = [];
  for (var i = 0; i < pi.questions.length; i++) {
    qids.push(pi.questions[i].id);
  }
  qidsOfInjPar[pi.par.id] = qids;
}
/*
 * Calls onChange for each qid supplied and accumulates affected qids
 * Loops recursively until all affected qids are onChanged
 * qids: {qid:qid}
 */
function onChangesLoop(qids) {
  clog("onChangesLoop", true);
  var onChanges = {};
  var i = 0;
  for (var qid in qids) {
    onChanges = onChange(qid, onChanges);
    i++;
  }
  cloga("onChangesLoop", [i]);
  for (var qid in onChanges) {
    onChangesLoop(onChanges);
    return;
  }
}
/*
 * Initiates question text/action processing after status change (visibility/selection)
 * onChanges: if supplied, returns qids whose status changed and require onChange call; otherwise, these will be cascaded
 */
function onChange(qid, onChanges) {
  var cascade = (onChanges == null);
  if (cascade) {
    onChanges = {};
  }
  var qref = questions[qid].ref;
  onChanges = runQTestsForQref(qref, onChanges);
  runQActions(qid);
  onChanges = checkInjPars(qref, onChanges);
  if (cascade) {
    for (var qid in onChanges) {
      onChange(qid);
    }
  }
  return onChanges; 
}
/*
 * After question change, run thru other tests that reference this question
 * Modifies onChanges {qid:qid} with qids whose status changed and require onChange call  
 */
function runQTestsForQref(qref, onChanges) {
  //if (lon) tlog("runQTests(" + qref + ")", true);
  var qTests = qTestsByQref[qref];
  return runQTests(qTests, onChanges);
}
function runQTestsForInj(pref, onChanges) {
  var qTests = qTestsByInj[pref];
  return runQTests(qTests, onChanges);
}
function runQTests(qTests, onChanges) {
  if (qTests == null) return onChanges;
  var t;
  var vis;
  for (var i = 0, j = qTests.length; i < j; i++) {
    t = qTests[i];
    //if (lon) tlog("qref=" + qref+ ", i=" + i + ", len=" + j + ", eval " + t.test);
    vis = eval(t.test);
    //if (lon) tlog("eval done, vis=" + vis + ", t.visible=" + t.visible);
    if (vis != t.visible) {
      t.visible = vis;
      t.q.tag.className = visibleIf(vis);
      if (t.q.frag) {  // may need to gather these first to elim dupes if it's a problem
        stringFragText(t.q.frag);    
      }
      if (t.q.osyncs) 
        formatQuestion(t.q);
      //if (lon) tlog("runQTest, checkInjPars(" + t.q.ref + ")");
      checkInjPars(t.q.ref);
      //if (lon) tlog("runQTest, onChange(" + t.q.ref + ")");
      // if (! actions.loading) cloga('runQTests onChange', [t.q.id]);
      if (onChanges)
        onChanges[t.q.id] = t.q.id;
    }
  }
  return onChanges;
  //if (lon) tlog("runQTest, done");
}
/* 
 * After question change, run thru actions dependent upon this question 
 */
function runQActions(qid) {
  var qActions = qActionsByQid[qid];
  if (qActions == null || qActions.length == 0) return;
  var a;
  for (var i = 0; i < qActions.length; i++) {
    a = qActions[i];
    if (a != null) {
      if (isInserted2(a.qid)) {
        if (a.cond == null || eval(a.cond)) {
          clog(a.action, 2);
          if (! eval(a.action)) {
            qActionsPend.push(a.action);
          }
          // If condition-less or an injection, null out action to prevent from running again for efficiency
          if (a.runOnce) {
            qActions[i] = null;
          }
        }
      }
    }
  }
  if (injectPool.length) {
    sendInjectPool();
  }
}
/*
 * Run thru pending actions
 */
function runQActionsPend() {
  for (var i = 0, j = qActionsPend.length; i < j; i++) {
    var a = qActionsPend[i];
    if (a != null) {
      //tlog("runQActionsPend: i=" + i + ", len=" + j + " evaluating " + a);
      if (eval(a)) {
        qActionsPend[i] = null;
        //tlog("runQActionsPend------>nulled out");
//      } else {
//        tlog("runQActionsPend------>eval = false");
      }
    }
  }
}
/* 
 * After question change, run thru injected pars dependent upon this question to recheck visibility
 * Modifies onChanges {qid:qid} with qids whose status changed and require onChange call  
 */
function checkInjPars(qref, onChanges) {
  var refs = injParsByQref[qref];
  if (refs == null) return onChanges;
  for (var i = 0; i < refs.length; i++) {
    onChanges = checkInjTest(refs[i], onChanges);
  }
  return onChanges;
}
/*
 * Check if par has injection test; if so, recheck visibility
 * onChanges: if supplied, returns qids whose status changed and require onChange call
 */
function checkInjTest(refi, onChanges) {
  tlog("checkInjTest(" + refi + ")", true);
  var cond = injTests[refi];
  if (cond != null) {
    // Note that p may be null at this point, as the referenced injected par may not have had time to be 
    // received to the console.  However, this is remedied by checkInjTest, which is invoked as
    // pars are received in parseParInfo.
    var p = pByRefi[refi];
    if (p) {
      var test = eval(cond);
      onChanges = setParVisibility(p, test, onChanges);
    }  
  }
  return onChanges;
}
/*
 * Paragraph visibility setter
 * onChanges: if supplied, returns qids whose status changed and require onChange call (rather than cascade the changes)
 */
function setParVisibility(p, test, onChanges) {
  cloga("setParVisibility", [p.id, test]);
  if (p != null) {
    var vis = (p.className == VISIBLE);
    if (test != vis) {
      clog("test=" + test + ", setting p.vis=" + vis);
      p.className = (test) ? VISIBLE : HIDDEN2;
      // Call onChange for par's questions to initiate any inject tests tied to its questions
      var qids = qidsOfInjPar[p.id];
      if (qids != null) {
        for (var i = 0; i < qids.length; i++) {
          if (onChanges) {
          //if (! actions.loading) cloga('setParVis onChange', qids[i]);
            onChanges[qids[i]] = qids[i];            
          } else {
            onChange(qids[i]);
          }
        }
      }
    }
  }
  return onChanges;
}
function syncQuestion(q) {
  if (q.sync == null) return;
  if (q.opts == null) return;
  var a = qSyncs[q.sync];
  if (a == null) return;
  var calc = null;
  if (isFormattedOptionPopup(q)) {
    calc = q.opts[0].text;
  }
  for (var i = 0; i < a.length; i++) {
    var sq = questions[a[i]];
    if (sq.id != q.id && sq.opts) {
      if (sq.noSyncIn != null && sq.sid != q.sid) {
        // Don't sync
      } else {
        sq.sel = q.sel;
        sq.unsel = q.unsel;
        sq.def = q.def;
        // Sync text of text-driven popup option
        if (calc != null && isFormattedOptionPopup(sq)) {
          sq.opts[0].text = calc;
        }
        // Sync "other" text (the last checked options of multis)
        if (q.loix == sq.loix) {
          if (q.opts.length > sq.opts.length) {
            qAppendOtherOpts(sq, q.opts.length);
          }
          for (var k = q.loix + 1; k < q.opts.length; k++) {        
            sq.opts[k].text = q.opts[k].text;
          }
        }
        formatQuestionAndFrags(sq);
      }
    }
  }
}
function syncOptions(q) {
  var unsel = q.unsel;
  if (unsel == null) unsel = [];
  var sel = getSel(q);
  // Adjust if syncing question is single-option YES/NO type
  if (q.mix == null && q.opts.length > 1) {
    if (isSel2(q, 0)) {
      unsel = [];
    } else {
      sel = [];
    }    
  }
  // For this question, iterate thru unchecked options and sync off other questions
  for (var i = 0; i < unsel.length; i++) {
    var o = q.opts[unsel[i]];
    if (o != null && o.sync != null) {
      var a = oSyncs[o.sync];
      if (a != null) {
        for (var j = 0; j < a.length; j++) {
          if (a[j].qid != PENDING_SYNC_ON) {
            if (a[j].qid != q.id) {
              var q2 = questions[a[j].qid];
              if (q2.noSyncIn != null && q2.sid != q.sid) {
                // Don't sync
              } else {
                if (q2.mix == null && q2.opts.length > 1) {
                  // Question to sync is single-option YES/NO type
                  // Sync option if YES (index 0) was selected
                  if (a[j].ix == 0) {
                    if (isSel2(q2, 0, true)) {
                      q2.sel = [1];
                      formatQuestionAndFrags(q2);
                    }
                  }
                } else {
                  if (isSel2(q2, a[j].ix, true)) {
                    setUnchecked2(q2, a[j].ix, true);
                    formatQuestionAndFrags(q2);
                  }
                }
              }
            }
          }
        }
      }
    }
  }
  // For this question, iterate thru checked options and sync on other questions
  // We do this after the sync off above to handle questions that duplicate the option sync ID (used in a single option with and one of the multi-checkboxes)
  for (var i = 0; i < sel.length; i++) {
    var o = q.opts[sel[i]];
    if (o != null && o.sync != null) {
      var a = oSyncs[o.sync];
      if (a != null) {
        for (var j = 0; j < a.length; j++) {
          if (a[j].qid != PENDING_SYNC_ON) {
            if (a[j].qid != q.id) {
              q2 = questions[a[j].qid];
              if (q2.noSyncIn != null && q2.sid != q.sid) {
                // Don't sync
              } else {
                if (q2.mix == null && q2.opts.length > 1) {
                  // Question to sync is single-option YES/NO type
                  // Sync option if NO (index 1) was selected
                  if (a[j].ix == 0) {
                    if (isSel2(q2, 1, true)) {
                      q2.sel = [0];
                      formatQuestionAndFrags(q2);
                    }
                  }
                } else {
                  if (! isSel2(q2, a[j].ix, true)) {
                    setChecked2(q2, a[j].ix, true);
                    formatQuestionAndFrags(q2);
                  }
                }
              }
            }
          }
        }
      }
    }
  }
}

// Return option ix given its ref
function ixFromRef(q, oref) {
  if (q == null || q.opts == null) return null;
  var ix = q.oByRef[oref];
  if (ix != null) return ix;
  for (ix = 0; ix < q.opts.length; ix++) {
    if (q.opts[ix].uid == oref) {
      q.oByRef[oref] = ix;
      return ix;
    }
  }
}

// Template tests
//var isct = 0;
function isSel(qref, oref, _pidi, _csuf) {
  //isct++;
  // if (clogit) cloga("isSel", [qref, oref, _pidi, _csuf]);
  qref = appendCloneSuffix(qref, _csuf);
  var q = qByRef[qref];
  var ix = ixFromRef(q, oref);
  if (ix == null) {
    return false;
  } else {
    return isSel2(q, ix);
  }
}
function onSel(qref, oref) {
  var q = qByRef[qref];
  if (lastPopQid == q.id) {
    var ix = ixFromRef(q, oref);
    if (ix == null)
      return false;
    if (isSel2(q, ix)) {
      lastPopQid = null;
      return true;
    }
  }
}
function isSel2(q, ix, noCheckInserted) {  // internal version
  if (q == null) return false;
  if (noCheckInserted == null) {
    if (! isInserted2(q.id)) return false;
  }
  var sel = getSel(q);
  for (var i = 0; i < sel.length; i++) {
    if (sel[i] == ix) return true;
  }
  return false;
}
function ipc(ipc) {
  return ipcs[ipc];
}
function notSel(ref, ix, _pidi, _csuf) {
  return ! isSel(ref, ix, _pidi, _csuf);
}
function isInjected(pref) {
  return ! (pidByRef[pref] == null);
}
function isMale() {
  return session.csex;
}
function isFemale() {
  return ! session.csex;
}
function currentAge(years) {
  return (sessionAge == years);
}
function olderThan(years) {
  return (sessionAge > years); 
}
// Really youngerOrEqualTo
function youngerThan(years) {
  return ! olderThan(years);
}
function isBirthdateSet() {
  return (session.cbirth != null);
}
function always() {  // to always execute an action on question change (conditionless actions are removed once executed)
  return true;
}
function isDel(q, ix) {
  if (q == null) return false;
  if (q.del == null || q.del.length == 0) return false;
  for (var i = 0; i < q.del.length; i++) {
    if (q.del[i] == ix) return true;
  }
}
function isFirst() {
  
}

// Begin evaluation actions stack
function startActions(msg) {
  cloga("startActions");
  doc.style.visibility = "hidden";
  tlog("startActions", true);
  actions.ix = 0;
  actions.loading = true;
  showingLoading = true;
  if (msg != null) { 
    // showWorking(msg, false, true);
    Pop.Working.show(msg, true);
  }
  startGetPars();
}
function startGetPars() {  // evaluate all get pars in a group
  var pids = extractGetPars();
  if (pids.length == 0) {
    actions.getting = false;
    finishActions();
    return;
  }    
  UsedParList.load(Pidi.from(pids));
//  for (var i = 0; i < pids.length; i++) {
//    var pidi = parsePidi(pids[i]);
//    if (pidi.cloneix) {
//      nextCloneIndex(pidi.pid, pidi.cloneix);
//    }
//    addToUsed(pidi.pid, setReqPids(pidi.pid));
//  }
  actions.getting = true;
  sendGetParsRequest(pids);
}
function setReqPids(pid) {  // increments and returns pid's requested count 
  if (reqPids[pid]) {
    reqPids[pid]++;
  } else {
    reqPids[pid] = 1;
  }
  return reqPids[pid];
}
function extractGetPars() {
  var pids = [];
  for (var i = 0; i < actions.stack.length; i++) {
    var pid = fixGetPar(i);
    //var pid = isGetPar(actions.stack[i]);
    if (pid && pid != 'NaN') {
      pids.push(pid);
    }
  }
  return pids;
}
function sendGetParsRequest(pids) {
  cloga("sendGetParsRequest", pids);
  waitingOnPar++;
  clog("waitingOnPar++=" + waitingOnPar);
  var o = {
      "tid":template.id,
      "cid":session.clientId,
      "nd":session.noteDate,
      "pids":pids
      };
  var j = toJSONString(o);
  clog('act=getParInfos&obj=' + j, 4);
  postRequest(6, "act=getParInfos&obj=" + encodeURIComponent(j));
}
function fixGetPar(i) {
  if (actions.stack[i].substr(0, 7) == "getPar(" && actions.stack[i].substr(7, 1) != "'") {
    var pid = parseInt(actions.stack[i].substr(7));
    if (pid == 3717)
      pid = 3720;
    actions.stack[i] = "getPar('" + pid + "')";
    return pid;
  }
  var a = actions.stack[i];
  var pid = isGetPar(actions.stack[i]); 
  if (pid == 3717)
    pid = 3720;
  if (a.indexOf('Family History') > -1) {
    actions.famhx = pid;
    return null;
  }
  if (a.indexOf('PsychoSocial History') > -1) {
    actions.sochx = pid;
    return null;
  }
  return pid;
}
function isGetPar(a) {  // returns pid if so
  if (a.substr(0, 8) == "getPar('") {
    return a.substr(8).split("'")[0];
  }
  if (a.substr(0, 9) == "getFsPar(") {
    return parseInt(a.substr(9));
  }
}

// Evaluate the actions stack to restore a saved document
function finishActions(restart) {
  cloga("finishActions", [restart]);
  if (restart == null) {
    actions.restart = 0;
    if (actions.inActionsAdded)
      actions.inActionsLoading = true;
    actions.inActionsAdded = false;
    if (session.ro) {
      $("doc").style.visibility = "hidden";
    }
  } else if (restart == 1) {  // waiting on par
    actions.waitingOnPar = false;
    actions.restart = 0;
  }
  var action = getAction();
  while (actions.loading) {
    try {
      //setWorkingText("Action #" + actions.ix);
      clog("[" + actions.ix + "]: " + action, 3);
      eval(action);
      actions.error = null;
      actions.restart = 0;
      action = getAction(1);  // get next action
    } catch (e) {
      actions.error = e;
      clog("[" + actions.ix + "]: ERROR: " + e, 2);
      if (waitingOnPar > 0) {
        actions.waitingOnPar = true;
        return;
      }
      // if (waitingOnPar > 0 && actions.restart < 10) {  // try action again in a second
      if (actions.restart < 1) {
        actions.restart++;
        onChangesLoop(questions);
        setTimeout("finishActions(2)", 1);
        return;
      } else {
        actions.bad.push(actions.stack.splice(actions.ix, 1));  // can't execute action, move to bad array 
        action = getAction();
      }
    }
    actions.restart = 0;
  }
  actions.skipGetPars = false;
  actions.inActionsLoading = false;
  closeLoading();
  if (actions.famhx) {
    var pid = actions.famhx;
    actions.famhx = null;
    getPar(pid, 'Family History');
  }
  if (actions.sochx) {
    var pid = actions.sochx;
    actions.sochx = null;
    getPar(pid, 'PsychoSocial History');
  }
}
// Returns current non-getPar action
function getAction(increment) {  // if true, increment first
  var len = actions.stack.length;
  var action;
  if (increment) 
    actions.ix++;
  do {
    actions.loading = (actions.ix < len);
    if (actions.loading) {
      action = actions.stack[actions.ix];
      if (actions.skipGetPars && isGetPar(action)) {
        actions.ix++;
        action = null;
      }
    }
  } while (action == null && actions.loading);
  return action;
}
/*
 * Closes loading working if all pars received 
 */
function closeLoading() {
  cloga("closeLoading", null, "waitingOnPar=" + waitingOnPar);
  if (waitingOnPar == 0) {
    if (session.ro) {
      doc.innerHTML = copyDoc(true);
    } else {
      onChangesLoop(questions);
    }
    clog('the big one');
    doc.style.visibility = "";
    applyCustomShows();
    Pop.Working.close();
    //cloga("total isSel count=" + isct);
    if (actions.bad.length > 0 && ! ignoreActionErrors) {
      setHtml("pop-aerr-errors", actions.bad.join("<br>"));
      Pop.show("pop-aerr");
    }
    showingLoading = false;
    actions.autoapplying = false;
    //sendGetParRequest('2631');
  }
  //alert((new Date() - timer) / 1000);
  //setTimeout("renderTemplateMap()", 1);
}
/*
 * Apply custom shows (header, freetags) after document load
 */
function applyCustomShows() {
  if (! fromPreview) {
    cloga('showHideTags');
//    if (perm.yk) {
//      showHideTags(!! lu_console.showAdminTags);
//    }
    cloga('showHideFreeTags');
    if (showFT) {
      showHideFreeTags();        
    }
    cloga('showHideHeader');
    showHideHeader(!! lu_console.showHeader);
  }  
}
function isCloneAction(action) {
  var a = action.split("(");
  switch (a[0]) {
    case "addAllergy":
    case "changeAllergy":
    case "deleteAllergy":
    case "addAllergyByData":
    case "addMed":
    case "changeMed":
    case "deleteMed":
    case "addMedByQid":
      return true;
  }
  return false;
}

// Add user-initiated action to the stack
function pushAction(action, undoText, noAutosave) {
  tlog("pushAction(" + action + "," + undoText + "," + noAutosave + ")", true);
  if (actions.loading) return;
  if (session.closed) return;
  actions.stack.push(action);
  if (undoText == null) {
    disable("actionUndo");
    actions.undos = [];
  } else {
    actions.undos.push(undoText);
    enable("actionUndo");
    if (typeof(toolUndo) != UNDEFINED) toolUndo.title = "Undo: " + undoText;
  }
  setDirty(noAutosave);
}
function setDirty(noAutosave) {
  dirty = true;
  if (! noAutosave) {
    autosave();
  }
}

// End of template tests

// Template instructions (actions)
// All return false if action refers to a question that does not exist in document
function inject(qref, cond, pref, _pidi, _csuf) {
  cloga("inject", [pref, qref, cond, _pidi, _csuf]);
  qref = appendCloneSuffix(qref, _csuf);
  var injectPid = pidByRef[pref];
  if (injectPid != null && clonedParCt[injectPid] == null) {  // par already in document and not cloneable
    var p = $(pidify(injectPid));
    if (p != null) {
      if (p.className == HIDDEN2) {  // make sure it's visible
        addInjPar(qref, cond, pref);
        setParVisibility(p, true);
      }
      return true;
    }
  }
  addInjPar(qref, cond, fixCloneRef(pref, _pidi, _csuf));
  poolInjectRequest(pref, _pidi);
  //sendGetParRequest(pref, _pidi);
  return true;
}
function hideTitle(suid) { 
  terseDoc();
  return true;
}
function terseDoc() {
  if (terse) 
    return;
  for (var sid in template.sections) {
    var section = template.sections[sid];
    var stitle = $('stitle-' + section.uid);
    if (stitle) 
      stitle.className = HIDDEN;
  }
  terse = true;
}
function poolInjectRequest(pref, injectorPid) {
  injectPool.push([pref, injectorPid]);
}
function sendInjectPool() {
  waitingOnPar = waitingOnPar + injectPool.length;
  clog("waitingOnPar++=" + waitingOnPar);
  var o = {
    'tid':template.id,
    'cid':session.clientId,
    'nd':session.noteDate,
    'pool':injectPool
    };
  var j = toJSONString(o);
  clog('act=getInjects&obj=' + j, 4);
  postRequest(6, "act=getInjects&obj=" + encodeURIComponent(j));
  injectPool = [];
}
function sendGetParRequest(pid) {
  cloga("sendGetParRequest", [pid]);
  if (jCache[pid] != null) {
    parseParInfosFromCache(pid);
    return;
  }
  sendGetParsRequest([pid]);
//  var o = {
//      "tid":template.id,
//      "cid":session.clientId,
//      "nd":session.noteDate,
//      "pids":[pid]
//      };
//  var j = toJSONString(o);
//  clog('act=getParInfos&obj=' + j, 4);   
//  postRequest(6, "act=getParInfos&obj=" + encodeURIComponent(j));
}
function syncOn(sync) {
  var a = oSyncs[sync];
  if (a == null) {  
    oSyncs[sync] = [{"qid":PENDING_SYNC_ON, "ix":0}];
    return true;
  }
  if (a[0].qid == PENDING_SYNC_ON) return;
  return setChecked2(questions[a[0].qid], a[0].ix);
}
function syncOff(sync) {
  var a = oSyncs[sync];
  if (a == null) {
    // TODO add pending_sync_off
    return true;
  }
  return setUnchecked2(questions[a[0].qid], a[0].ix);
}
function setFreetext(qref, text, _pidi, _csuf) {  // converted from setText() action
  qref = fixCloneRef(qref, _pidi, _csuf);
  var q = qByRef[qref];
  if (q == null) return false;
  setFormattedOption(q.id, text);
  return true;
}
function setTextFromSel(qref, qrefSource, _pidi, _csuf) {
  qref = fixCloneRef(qref, _pidi, _csuf);
  qrefSource = appendCloneSuffix(qrefSource, _csuf);  
  var q = qByRef[qref];
  var qSource = qByRef[qrefSource];
  if (q == null || qSource == null) return false;
  var text = (qSource.opts) ? qSelText(qSource) : textFromTag(qSource.tag); 
  setFormattedOption(q.id, text);
  return true;
}
function textFromTag(e) {
  var text = trim(e.innerText);
  if (text.substring(text.length - 1) == ".") {
    return text.substring(0, text.length - 1);
  } else {
    return text;
  }
}
function pop(qref, oref) {
  if (actions && ! actions.loading && ! showingLoading) {
    if (! Pop.isActive()) {
      var q = qByRef[qref];
      showPopQuestion(q.id);  
    }
    return true;
  }
}
function setDefault(qref, oref, _pidi, _csuf) {
  cloga('setDefault', [qref, oref]);
  qref = fixCloneRef(qref, _pidi, _csuf);
  var q = qByRef[qref];
  var ix = ixFromRef(q, oref); 
  if (ix == null) return false;
  if (q.def.join() == [ix].join()) {
    return true;
  }
  q.def = [ix];
  if (ix >= q.mix && q.sel.length == 0) {
    refreshUnsel(q);
  }
  syncQuestion(q);
  syncOptions(q);
  formatQuestionAndFrags(q);
  return true;
}
function calcBmi(qBmi, q1, q2, q3, q4) {  // refs of bmi, ht, ht-units, wt, wt-units
  var qb = qByRef[qBmi];
  var bmi = calculateBmi(qb, qByRef[q1], qByRef[q2], qByRef[q3], qByRef[q4])
  if (bmi == null || bmi == session.lastBmi) return;
  session.lastBmi = bmi;
  qSetByValue(qb, bmi);
  doChgCalls(qb);
}
function setChecked(qref, oref, _pidi, _csuf) {
  //clog("setChecked(" + qref + "," + oref + "," + pid + "," + injector + ")", 1);
  qref = fixCloneRef(qref, _pidi, _csuf);
  //clog("qref=" + qref);
  var q = qByRef[qref];
  var ix = ixFromRef(q, oref); 
  if (ix == null) return false;
  return setChecked2(q, ix);
}
function setChecked2(q, ix, ns) {  // internal version. optional ns = no sync
  if (q == null) {  
    return false;
  }
  var sel = (q.sel.length > 0) ? q.sel : q.def;
  for (var i = 0; i < sel.length; i++) {
    if (sel[i] == ix) {
      return true;
    }
  }
  // If only thing selected is single option, replace with the new selection; otherwise, append new selection
  var mix = q.mix;
  if (mix == null) mix = 999; 
  if (sel.length == 1 && sel[0] < mix) {
    sel = [ix];
  } else {
    sel.push(ix);
  }
  q.sel = sel;
  refreshUnsel(q);
  formatQuestionAndFrags(q);
  if (ns == null || ! ns) {
    syncQuestion(q);
    syncOptions(q);
  }
  return true;
}
function setUnchecked(qref, oref, _pidi, _csuf) {
  qref = fixCloneRef(qref, _pidi, _csuf);
  var q = qByRef[qref];
  var ix = ixFromRef(q, oref); 
  if (ix == null) return false;
  return setUnchecked2(q, ix);  
}
function setUnchecked2(q, ix, ns) {  // internal version. optional ns = no sync
  if (q == null) {  
    return false;
  }
  if (q.sel.length == 0) {
    return true;
  }
  for (var i = 0; i < q.sel.length; i++) {
    if (q.sel[i] == ix) {
      q.sel.splice(i, 1);
      refreshUnsel(q);
      formatQuestionAndFrags(q);
      if (ns == null || ! ns) {
        syncQuestion(q);
        syncOptions(q);
      }
      return true;
    }
  }
  return true;
}

// End of template instructions

function refreshUnsel(q) {
  if (q.mix != null) {
    var aSel = {};
    var unsel = [];
    var sel = getSel(q);
    for (var i = 0; i < sel.length; i++) {
      aSel[sel[i]] = sel[i];
    }
    for (var i = q.mix; i < q.loix; i++) {
      if (aSel[i] == null) {
        unsel.push(i);
      }
    }
  }
  q.unsel = unsel;
}

function isIncluded(pid) {  // par exists in doc, visible or not
  return ($(pidify(pid)) != null);
}
function isVisible(pid) {  // par is visible in doc
  var p = $(pidify(pid));
  if (p == null) return false;
  return (p.className == VISIBLE);
}
function isInserted(qref) {
  var q = qByRef[qref];
  if (q == null) return false;
  var qt = q.tag;
  if (qt == null) return false;
  var p = q.ptag;
  if (p == null) return false;
  return ((qt.className == VISIBLE || qt.className == VISIBLE2 || qt.className == NOTEXT) && p.className == VISIBLE);
}
function isInserted2(qid) {  // question and its par are visible
  //tlog("isInserted2(" + qid + ")", true);
  var q = questions[qid];
  var qt = q.tag;
  if (qt == null) return false;
  var p = q.ptag;
  if (p == null) return false;
  return ((qt.className == VISIBLE || qt.className == VISIBLE2 || qt.className == NOTEXT) && p.className == VISIBLE);
}
function sidify(id) {
  return "s_" + id;
}
function pidify(id) {
  return "p_" + id;
}
function qidify(id) {
  return "q_" + id;
}
function unqidify(id) {
  return id.substring(2);
}
function isMultiSelected(q, sel) {
  if (q.mix != null) {
    for (var i = 0; i < sel.length; i++) {
      if (sel[i] >= q.mix) {
        return true;
      }
    }
  }
  return false;
}
function visibleIf(visible) {
  return (visible) ? VISIBLE : HIDDEN;
}
function getOptText(o) {
  if (o.text != null) return o.text;
  return o.desc;
}
function stringOptText(q, ixlist, fromSel) {
  var v = buildOptTextList(q, ixlist);
  if (q.frag) {
    q.frag.sels = v;
    q.frag.fromSel = fromSel;
    return v.join(q.lt == 0 ? " " : "<br/>");
  }
  if (q.lt == 0) {
    return joinOptTextList(v, fromSel);
  } else if (q.lt == 3) {
    return v.join(" ");     
  } else {
    return v.join("<br/>");
  }
}
function buildOptTextList(q, ixlist) {
  var v = [];
  var dupeCheck = {};
  var bull1 = (q.lt == 2) ? "&bull; " : "";
  for (var i = 0; i < ixlist.length; i++) {
    var text = bull1 + getOptText(q.opts[ixlist[i]]);
    if (! dupeCheck[text]) {
      v.push(text);
      dupeCheck[text] = text;
    }  
  }
  if (q.lt == 2) {
    v[v.length - 1] += "<br/>";
  }
  return v;
}
function joinOptTextList(v, fromSel) {  
  switch (v.length) {
    case 1: return v[0];
    case 2: return v.join(andOr(fromSel));
  }
  last = v.pop();
  return v.join(", ") + andOr(fromSel) + last;
}
function andOr(useAnd) {
  return useAnd ? " and " : " or ";
}

// Given array of selections, extract out the ones that have already been displayed in document 
function filterSync(q, sel) {
  if (sel == null) return null;
  fsel = [];
  for (var i = 0; i < sel.length; i++) {
    var o = q.opts[sel[i]];
    if (o != null) {
      var sqid = firstSyncQid(o.sync);
      if (sqid == null || sqid == q.id || sqid == PENDING_SYNC_ON) {
        fsel.push(sel[i]); 
      }
    }
  }
  return fsel;
}
function sigName() {
  if (session.assignedToId != null) {
    return session.assignedTo;
  } else {
    return session.createdBy;
  }  
}
// Filter special keywords and gender-specifics from text
function filter(t, noHtml, allowFormatting) {  // noHtml = don't add HTML tags 
  if (t == null) return null;
  if (t.indexOf('{') >= 0) {
    t = t.replace(/\{cname\}/g, session.cname);
    t = t.replace(/\{cdata1\}/g, sessionBirth);
    t = t.replace(/\{cid\}/g, session.cid);
    t = t.replace(/\{dos\}/g, noHtml ? session.dos : "<span id='spandos'>" + session.dos + "</span>");    
    t = t.replace(/\{dos\}/g, session.dos);    
    t = t.replace(/\{uname\}/g, sigName());
    t = t.replace(/\{uphone\}/g, session.uphone);
    t = t.replace(/\{uaddress\}/g, session.uaddressonly);
    t = t.replace(/\{ucitystatezip\}/g, session.ucitystatezip);
    t = t.replace(/\{caddress\}/g, session.caddress);
    t = t.replace(/\{ccitystatezip\}/g, session.ccitystatezip);
    t = t.replace(/\{ucompany\}/g, session.ucompany);
    t = t.replace(/\{cage\}/g, sessionAge);
    t = t.replace(/\{cgender\}/g, sessionSex);
    t = t.replace(/\{custom1\}/g, denull(session.cdata1));
    t = t.replace(/\{custom2\}/g, denull(session.cdata2));
    t = t.replace(/\{custom3\}/g, denull(session.cdata3));
    t = t.replace(/\{today\}/g, today);
    t = t.replace(/\{page\}/g, "{\\chpgn} ");
    t = t.replace(/\{total\}/g, "{\\field{\\*\\fldinst NUMPAGES}}");
  }
  if (! allowFormatting && t.indexOf('<') >= 0) {
    t = t.replace(/<b>/g, "");
    t = t.replace(/<\/b>/g, "");
  }
  if (session == null) {
    return t;
  } else {
    return genderFix(t, session.csex);
  }
}
// Formats the break type up to any line feed character
function breakTypeBefore(bt) {
  switch (bt) {
    case 1:
      return "&nbsp;";
      break;
    case 2:
      return ": ";
      break;
    case 3:
      return "; ";
      break;
    case 4:
      return ".&nbsp;";
      break;
    case 5:
      return ".&nbsp;";
      break;
    case 6:
      return "";
      break;
    case 7:
      return "";
      break;
    default:
      return ".&nbsp; ";
  }
}
// Completes the break type with any line feed(s)
function breakTypeAfter(bt) {
  switch (bt) {
    case 1:
      return "";
      break;
    case 2:
      return "";
      break;
    case 3:
      return "";
      break;
    case 4:
      return "<br>";
      break;
    case 5:
      return "<br><br>";
      break;
    case 6:
      return "<br>";
      break;
    default:
      return "";
  }
}
function freeTextAnchor(id) {
  return "<span class=v id=ft>" + freeTextAnchorHtml(id) + "</span>";
}
function freeTextAnchorHtml(id) {
  return "<a class=ftd id=\"" + id + "ft\" href='javascript:' onclick=\"showFreePop('" + id + "'); return false\"></a>&nbsp;" 
}
function copy() {
  if (! Html.Window.isIe()) {
    CopyPop.pop(DocFormatter.consoleToHtml());
    return;
  }
  //us3();
  // var docTitle = $("title").innerText;
  // var d = "<p><center><b>" + docTitle + "</b></center></p><p>";
  var d;
  if (session.closed >= 2) {
    d = $("doc").innerHTML;
  } else {
    d = copyDoc();
  }

  // Copy to clipboard
  but.innerHTML = d;
  var r = but.createTextRange();
  r.execCommand("copy");
}

CopyPop = {
  pop:function() {
    return Html.Pop.singleton_pop.apply(CopyPop, arguments);
  },
  create:function() {
    return Html.Pop.create('Copy to Clipboard', 400).extend(function(self) {
      return {
        init:function() {
          self.Frame = Html.Pop.Frame.create(self.content, 'Press CTRL+C to copy (or right-click selection)');
          self.Doc = Html.Tile.create(self.Frame, 'DocCopy');
          Html.CmdBar.create(self.content).exit(self.close);
        },
        onshow:function(html) {
          self.Doc.html(html);
          //self.Doc.title = 'R';
          document.onkeyup = function() {
            if (event.keyCode == 67 && event.ctrlKey)
              self.close();
          }
          window.getSelection().selectAllChildren(self.Doc);
        },
        onclose:function() {
          document.onkeyup = null;
        }   
      }
    })
  }
}

function hasOpenQuestions() {
  if (! session.closed) {
    for (var qid in questions) {
      if (isInserted2(qid)) {
        if (questions[qid].blank) {
          var d = getMouseOverDel($(qidify(qid)));
          if (d == null || d.className != "del") {
            return true;
          }
        }
      }
    }
  }
}
/*
 * Returns { 
 *   'cid':cid,
 *   'sid':sid,
 *   'dos':dos,
 *   'dsyncs':{dsync:[seltext,..],..}
 *   'fhxprocs':{dsync:[ipc,..],..}
 *   'shxprocs':{dsync:[ipc,..],..}
 *   }
 */
function buildDataSyncsOut() {
  ssDsyncs = {};
  var dsyncs, fhxprocs, shxprocs;
  for (var dsync in qDsyncs) {
    var qid = qDsyncs[dsync];
    var q = questions[qid];
    if (isInserted2(q.id)) {
      if (dsyncs == null) {
         dsyncs = {};
      }
      if (q.cloning) {  // cloned question, need to make cloned instances of dsyncs ?0 ?1..
        var anchors = $$$$("listAnchor", $(qidify(q.id)), "A", true);
        for (var j = 0; j < anchors.length; j++) {
          qRestoreFromComboAnchor(q, anchors[j]);
          di = dsync + "?" + j;  // datasync instance ID
          dsyncs[di] = buildDataSync(q);
          ssDsyncs[di] = {'text':qSelText(q),'code':q.opts[getSel(q)[0]].cpt};
        }
      } else {
        dsyncs[dsync] = buildDataSync(q);
        ssDsyncs[dsync] = {'text':qSelText(q),'code':q.opts[getSel(q)[0]].cpt};
      }
      if (q.ptag.suid == 'famHx') {
        var ipcs = getIpcsFromSelected(q);
        if (ipcs.length) {
          var member = dsync.split('.')[1]; // e.g. 'relSister1+female'
          if (fhxprocs == null)
            fhxprocs = {};
          if (fhxprocs[member])
            fhxprocs[member].append(ipcs);
          else
            fhxprocs[member] = ipcs;
        }
      } else if (q.ptag.suid == 'socHx') {
        var ipcs = getIpcsFromSelected(q);
        if (ipcs.length) {
          var member = 'shx';
          if (shxprocs == null)
            shxprocs = {};
          if (shxprocs[member])
            shxprocs[member].append(ipcs);
          else
            shxprocs[member] = ipcs;
        }
      }
    }
  }
  if (dsyncs) {
    return {
      'cid':session.clientId,
      'sid':session.id,
      'dos':session.dossql,
      'dsyncs':dsyncs,
      'fhxprocs':fhxprocs,
      'shxprocs':shxprocs
      };
  }  
} 
function getIpcsFromSelected(q) {
  var ipcs = [];
  Array.each(qSelOpts(q), function(o) {
    if (o.cpt)
      ipcs.push(o.cpt);
  })
  return ipcs;
}
function buildDataSync(q) {
  return toJSONString(qSelTextArray(q));
}
function buildPendingOut(withDiags) {
  pendingOut = null;
  pendingDiagnoses = [];
  for (var i = 0; i < qOutData.length; i++) {
    var outData = qOutData[i];
    var q = questions[qOutData[i].qid];
    outData.q = q;
    if (isInserted2(q.id)) {
      if (q.cloning) {
        var anchors = $$$$("listAnchor", $(qidify(q.id)), "A", true);
        for (var j = 0; j < anchors.length; j++) {
          addQuestionToPendingOut(qOutData[i], anchors[j]);
        }
      } else {
        addQuestionToPendingOut(qOutData[i], null);
      }
    }
  }
  if (withDiags && pendingOut.diagnoses) {
    for (var key in pendingOut.diagnoses.records) {
      var d = pendingOut.diagnoses.records[key];
      pendingDiagnoses.push(d.fields);
    }
  }
}
function addQuestionToPendingOut(outData, a) {  // outdata.q=ref to parent q, a=anchor instance if cloning
  for (var dtid in outData.out) {
    var pks = buildDataPks(outData, dtid, lu_dtabs[dtid].pk, a);
    for (var i = 0; i < pks.length; i++) {
      var pk = pks[i];
      var fields = getPendingFields(dtid, pk); 
      var cols = outData.out[dtid].cols;
      for (var dcid in cols) {
        var value = cols[dcid];
        if (value)
          fields[dcid] = parseDataField(outData, value, a);
        if (dcid == 'icd') {
          fields['icd10'] = parseDataField(outData, '$icd10', a);
        }
      }         
    }
  }
}
function getSel(q) {
  return (q.sel.length == 0) ? q.def : q.sel; 
}
function getPendingFields(dtid, pk) {
  if (pendingOut == null) {
    pendingOut = {};
  }
  if (pendingOut[dtid] == null) {
    pendingOut[dtid] = {"records":{}};
  }
  if (pendingOut[dtid].records[pk] == null) {
    pendingOut[dtid].records[pk] = {"fields":{}};
  }
  return pendingOut[dtid].records[pk].fields;
}
function buildDataPks(outData, dtid, pks, a) {
  var p = [];
  var mv;  // multi value 
  for (var i = 0; i < pks.length; i++) {
    var v = parseDataField(outData, pks[i], a, dtid);
    if (isArray(v)) {
      mv = {ix:i,v:v};
      p.push(null);
    } else {
      p.push(v);
    }  
  }
  if (mv == null) {
    return [p.join("|")];
  }
  var pk = [];
  for (var i = 0; i < mv.v.length; i++) {
    p[mv.ix] = mv.v[i];
    pk.push(p.join("|"));
  }
  return pk;
}
/*
 * Return par text without end punctuation 
 * - removeIcd: true to remove (ICD)  
 */
function getParText(pid, removeIcd) {
  var text = parCopyText($(pidify(pid)), removeIcd);
  if (text.substring(text.length - 1) == ".") 
    text = text.substring(0, text.length - 1);
  return text;
}
function parseDataField(outData, value, a, dtid) {  // a=anchor instance if cloning pop, dtid only req for pk fields
  if (value == "$ugid") {  // user group ID
    return me.userGroupId;
  }
  if (value == "$sid") {  // session ID
    return session.id;
  }
  if (value == "$dos") {  // date of service in SQL format
    return session.dossql;  // TODO : needs to change with dos change
  }
  if (value == "$dosfull") { 
    return calFormatFullDate(calParse(session.dos));
  }
  if (value == "$cid") {  // client ID
    return session.clientId;
  }
  if (value == "$tid") {  // template ID
    return template.id;
  }
  if (value == "$ptext") {  // par text without punc
    var text = getParText(outData.pid, (outData.suid == 'impr')) || '';
    return text.replaceBull();
  }
  if (value == "$icd") {  
    var a = $(qidify(outData.qid) + 'icd');
    if (a && a.icd) {
      return a.icd;
    } else {
      return null;
    }
  }
  if (value == "$icd10") {  
    var a = $(qidify(outData.qid) + 'icd10');
    if (a && a.icd) {
      return a.icd;
    } else {
      return null;
    }
  }
  if (value == "$ptextpunc") {  // par text with punc
    return parCopyText($(pidify(outData.pid)));
  }
  if (value == "$pdesc") {  // par desc
    var a = $("apl_" + outData.pid);
    if (! a) return "";
    var text = a.innerText;
    //text = text.replace(/&#149;/g, "");  // doesn't work... trying to get rid of bullets
    return trim(text).replaceBull();
  }
  if (value == "$puid") {  // par UID
    if (outData.puid.substr(0, 1) == '+') {
      return outData.pid;
    } else {
      return outData.puid;
    }
  }
  if (value == "$qcix") {  // cloned question index
    return qcix(a);
  }
  if (value == "$qctext") {  // cloned question text
    return a.innerText;
  }
  if (value.substring(0, 1) == "@") {  // @ fields (pull value from property of cloned pop anchor)
    return a.getAttribute(value.substring(1));
  }
  if (value == "$medText") {
    return a.innerText.replace(a.getAttribute('medName'), "").substring(3);
  }
  // Remaining data fields reference the question
  var q = outData.q;
  if (value.substr(0, 1) == "?") {  // pk fields supplied in out data
    var ix = parseInt(value.substr(1)) - 1;
    var v = outData.out[dtid].pk[ix];
    if (v == "$moix") {
      var a = [];
      var sel = getSel(q);
      for (i = 0; i < sel.length; i++) {
        if (sel[i] >= q.mix) {
          a.push(sel[i] - q.mix + 1);
        }
      }
      return a;
    } else {
      return v;
    }
  }
  if (value == "$medRx") {  // RX free text associated with med, e.g. contents of medq_####o#ft anchor
    var rxft = $("medq_" + q.id + "o" + qcix(a) + "ft");
    return (rxft) ? trim(rxft.innerText) : "";
  }
  if (value == "$ouid") {  // selected option UID
    var sel = getSel(q);
    return q.opts[sel[0]].uid;
  }
  if (value == "$oix") {  // selected option index
    var sel = getSel(q);
    return sel[0];
  }
  if (value == "$otext") {  // selected option text, e.g. "Nausea"
    var sel = getSel(q);
    return getOptText(q.opts[sel[0]]);
  }
  if (value == "$otexta") {  // selected options text array, e.g. ["Nausea","Vomiting"]
    return toJSONString(qSelTextArray(q));
  }
  if (value == "$otextAsDate" || value == "$oTextAsDate") {  // selected option text formatted as SQL date
    var sel = getSel(q);
    var text = getOptText(q.opts[sel[0]]);
    return calFormatSqlDate(calParse(text, CAL_FMT_SENTENCE));
  }
  if (value == "$qtext") {  // question text, e.g. "Nausea and Vomiting"
    return null;  // todo
  }
  if (value == "$quid") {  // question UID
    return outData.suid + "." + outData.puid + "." + q.uid;
  }
  // No match found, just return unparsed value
  return value; 
}
function qcix(a) {  // cloned question index
  return a.id.split("o")[1];
}

// Question UI
function showPopQuestion(id) {
  if (session.closed) return;
  selectDels();
  var q = questions[id];
  if (q.type == Q_TYPE_BUTTON && q.desc == 'Attachment') {
    selectStub(id);
  } else {
    if (q.type == Q_TYPE_CALENDAR && isUndefined(q.calFmt)) {
      q.calFmt = (q.uid.substring(q.uid.length - 1) == "%" ? 0 : 1);
    }
    showQuestion(q);
  }
  if (event)
    event.cancelBubble = true;
}
function selectStub(qid) {
  if (session.fs == null) {
    Pop.Working.show('Retrieving patient history');
    Ajax.Facesheet.get(session.clientId, null, function(fs) {
      session.fs = fs;
      Pop.Working.close();
      showHistoryPop(qid);
    })
  } else {
    showHistoryPop(qid);
  }
}
function showHistoryPop(qid) {
  DocHistoryPop.pop(session.fs, null, function(stub) {
    AttachPreviewPop.pop_forAttach(stub, function() {
      DocHistoryPop.close();
      addStub(qid, stub.type, stub.id, stub.name);
    })
  })
}
function popQuestionCallback(q) {
  lastPopQid = q.id;
  if (q.type == Q_TYPE_ALLERGY) {
    allerDoOk(q);
    return;
  }
  if (q.type == Q_TYPE_COMBO) {
    comboDoOk(q);
    return;
  }
  if (q.type == Q_TYPE_FREE) {
    if (qFormattedOptText(q) == "") {
      qSetFormattedOption(q, "(Insert)");
      q.opts[0].blank = true;
    }
  } 
  var action = "qs('" + q.pid + "','" + q.id + "'," + qRestoreArgs(q) + ",'" + q.ref + "')";  // qref for self-documentation; not needed for restore
  pushAction(action, qUndoText(q));
  doChgCalls(q);
}
function addTrackOpts(q) {
  var hasFriendly;
  var qimpr = getQImpr(q);
  for (var i = 0; i < q.opts.length; i++) {
    var opt = q.opts[i];
    opt.qimpr = qimpr;
    if (opt.friendly)
      hasFriendly = true;
    if (opt.tcat == null) 
      opt.tcat = '99';  // TCAT_OTHER
    if (opt.tkey == null) {
      opt.tkey = OrderItem.buildKey(q.id, i);
      opt.index = i;
      opt.q = q;
      if (opt.desc == null || opt.desc == 'other') 
        opt.friendly = hasFriendly;
      else if (opt.coords)
        opt.friendly = hasFriendly = true;
      trackOpts[opt.tkey] = opt;
    }
    opt.sel = false;
  }
  for (var i = 0; i < q.sel.length; i++) {
    var opt = q.opts[q.sel[i]];
    if (opt.tcat) {
      opt.sel = true;
    }
  }
}
function getQImpr(q) {
  var impr = q.pid.split('@')[1];
  if (impr && jCache[impr]) {
    var qimpr = jCache[impr].questions[0];
    if (qimpr)
      return questions[qimpr.id];
  }
}
function getIcdFromImpr(q, as10) {
  if (q) {
    var icde = $icd(q.id, as10);
    //var icde = $(qidify(q.id) + 'icd');
    return icde && icde.icd;
  }
}
function getDiagFromImpr(q) {
  if (q)
    return q.opts[getSel(q)[0]].text;
}
function buildFriendlyPlan() {
  var a = [];
  for (var key in trackOpts) {
    var opt = trackOpts[key];
    if (opt.friendly)  
      if (opt.q.sel.has(opt.index))   // is selected
        a.push(opt.coords || opt.text);
  }
  return a.length ? a : null;
}
function buildOrderItems() {
  var items = [];
  for (var key in trackOpts) {
    var opt = trackOpts[key];
    if (opt.sel) {
      var tdesc = (opt.desc && opt.desc != 'other') ? opt.desc : opt.text;
      var diag = getDiagFromImpr(opt.qimpr);
      var icd = getIcdFromImpr(opt.qimpr);
      var icd10 = getIcdFromImpr(opt.qimpr, 1);
      items.push(new OrderItem(
        session.clientId, session.id, key, opt.tcat, tdesc, opt.cpt, icd, diag, icd10));
    }
  }
  return items;
}
function qUndoText(q) {
  var pi = jCache[q.pid];
 var s = (pi) ? q.desc + " (" + pi.suid.toUpperCase() + "/" + pi.par.desc + ")" : q.desc;
  return "Set " + s + " to \"" + qSelText(q) + "\"";
}
function popQuestionDeleteCallback(q) {
  if (q.type == Q_TYPE_ALLERGY) {
    allerDoDelete();
  } else {  
    comboDoDelete();
  }
}
function docDel(start, end, undo) {  // start, end: ids of <span> range; undo: optional boolean
  if (session.id == 289969)
    return;
  if (! undo) {
    pushAction("docDel('" + start + "','" + end +"')", "Delete Text");
  }
  var e = $(start);
  var p = e.parentElement;
  p.className = "";
  var deleting = false;
  for (var i = 0; i < p.children.length; i++) {
    var del = p.children[i];
    if (del.dq || del.getAttribute('dq')) {
      if (del.id == start) deleting = true;
      if (deleting) {
        del.className = (! undo) ? "del" : "dunsel";
        del.prt = (! undo) ? "n" : "";
      }
      if (del.id == end) deleting = false;
    }
  }
  if (undo) 
    Pop.Working.close();
}
/*
 * Returns true if an "IN_DATA" action (auto generated by DataDao)
 */
function isFacesheetAction($action) {
  $a = $action.split("(");
  $a = $a[0];
  return (
      $a == "getFsPar" ||
      $a == "setByValue" || 
      $a == "setByValues" || 
      $a == "setByIndex" || 
      $a == "setByQuid" ||
      $a == "addHmHist" ||
      $a == "addMedByQid" ||
      $a == "addComboByValues" ||
      $a == "addAllergyByData");  
}
/*
 * value: may be string or serialized array
 * injector: optional pref of injector to resolve cloned par question reference 
 */
function setByValue(qid, value, injector) {  
  if (value.substr(0, 1) == "[" && value.substr(value.length - 1) == "]") {
    setByValues(qid, value, injector);
    return;
  }
  var q = questions[qid];
  if (q.mix) {
    setByValues(qid, "['" + value + "']", injector);
    return;
  }
  qSetByValue(q, value);
  doChgCalls(q);
}
/*
 * values: serialized array "['1','2']"
 * injector: optional pref of injector to resolve cloned par question reference 
 */
function setByValues(qid, values, injector) {
  if (injector) {
    qid += "@" + pidByRef[injector];
  }  
  var q = questions[qid];
  if (actions && actions.loading) {
    if (actions.famhx || actions.sochx) {
      if (q == null) {
        actions.stack.splice(actions.ix, 1);
        actions.ix--;
        return;
      }
    }
  }
  var a = eval(values); 
  qSetByValues(q, a);
  doChgCalls(q);
}
function setByIndex(qid, ix) {
  var q = questions[qid];
  qSet(q, ix);
  doChgCalls(q);
}
function setByQuid(quid, ix) {
  var q = qByRef[quid];
  qSet(q, ix);
  doChgCalls(q);
}
function addHmHist(qid, hmid, proc, date, results, ipc) {
  var q = questions[qid];
  if (q.hms == null) {
    q.hms = {};
  }
  var hm = {
    id:hmid,
    ipc:ipc,
    proc:proc,
    date:extractDate(date),
    results:results};
  q.hms[hmid] = hm;
}
function addMedByQid(qid, name, amt, freq, route, length, asNeeded, withMeals, disp, text) {
  addMed("q_" + qid + "o", name, amt, freq, route, length, asNeeded, withMeals, disp, text);
}
function addAllergyByData(qid, agent, reactions) {
  var q = questions[qid];
  var r = null;
  if (reactions)
    if (reactions.substr(0, 1) == '[')
      r = eval(reactions);
    else
      r = [reactions];
  qSetByValueCombo(q, agent, r);
  addFacesheetAllergy(q, r);
}
function addComboByValues(qid, values) {
  var q = questions[qid];
  var m = eval(values);
  var s = m.shift(); 
  qSetByValueCombo(q, s, m);
  addFacesheetCombo(q, m);
}
function getFsPar(pid) {
  getPar(pid);
}
function requestPar(pid) {
  reqParId = pid;
  doWork("getPar(" + reqParId + ")", "Inserting paragraph", true);  
}
// Restore actions
function getPar(pid, desc) {  // for clones, pid + instance e.g. 501+1
  if (pid == 'NaN') return;
  if (session.closed >= 1) return;
  //var pidi = parsePidi(pid);
  //if (reqPids[pidi.pid] && pidi.cloneix == null) return;
  var pidi = (fromPreview) ? parsePidi(pid) : UsedParList.add(pid);
  if (pidi == null) 
    return;
  pushAction("getPar('" + pidi.pidi + "','" + desc + "')", "Get \"" + desc + "\"");
  //addToUsed(pidi.pid, setReqPids(pidi.pid));
  setUsed(pid);
  sendGetParRequest(pidi.pidi, null);
}
function qs(pid, qid, sel, del, sotext, motexts) {
  if (qid == 32063)
    qid = 32075;
  if (pid == '3717')
    pid = '3720';
  if (qid == '32063')
    qid = '32075';
  if (String.is(pid))
    pid = pid.replace('@3717', '@3720');
  if (String.is(qid))
    qid = qid.replace('@3717', '@3720');
  var q = questions[qid];
  if (q == null && session.oldstyle) {
    var a = qid.split("@");
    if (a.length > 1) {
      q = tq[a[0]];
    }
  }
  if (sel == null || sel.length == 0)
    sel = q.def;
  if (q.type == Q_TYPE_BUTTON) {
    motexts = [];
    var j = q.opts.length - 1;
    do {
      if (sel[sel.length - 1] <= j) {
        break;
      }
      sel.pop();
    } while (sel.length > 0);
  }
  qRestore(q, sel, del, sotext, motexts);
  doChgCalls(q);
}
//function getClonePar(pid, desc) {
//  getPar(pid + "+" + nextCloneIndex(pid), desc);
//}
function selectHms(qid, hmids) {
  var q = questions[qid];
  pushAction("selectHms('" + qid + "'," + toJSONString(hmids) + ")");
  for (var hmid in q.hms) {
    q.hms[hmid].checked = false;
  }
  ipcs = {};
  var span = q.tag;
  span.innerHTML = "";
  var h = [];
  var last;
  for (var i = 0; i < hmids.length; i++) {
    var hm = q.hms[hmids[i]];
    hm.checked = true;
    if (hm.ipc)
      ipcs[hm.ipc] = true;
    if (hm.proc != last) {
      if (i > 0) h.push("");
      h.push("<u>" + hm.proc + ' - ' + hm.date + '</u>');
      last = hm.proc;
    }
    //var results = hm.results ? ':<br>' + hm.results : ''; 
    h.push(hm.results);
  }
  span.innerHTML = h.join("<br>");
  autosave(true);
}
function addStub(qid, stubType, stubId, stubName) {
  var q = questions[qid];
  var stub = {id:stubId, type:stubType, name:stubName};
  session.stub = stub;
  pushAction("addStub(" + qid + ',' + stubType + ',' + stubId + ",'" + stubName + "')");
  var span = q.tag;
  if (q.a == null) {
    q.a = span.firstElementChild || span.firstChild;
  }
  q.a.style.display = 'none';
  q.stub = DocStub.revive(stub);
  q.astub = AnchorStubAttach.create(q.stub).into(span).bubble('ondetach', removeStub.curry(qid));
}
function removeStub(qid) {
  var q = questions[qid];
  session.stub = null;
  pushAction("removeStub(" + qid + ")");
  var span = q.tag;
  if (q.a) { 
    q.a.style.display = '';
    q.stub = null;
    span.removeChild(q.astub);
  }
}
/*
 * Split args from par instance ref "pid", "pid@injector" or "pid+cloneix"
 */
function parsePidi(pidi) {
  return Pidi.from(pidi);
}

// Legacy restore actions
function set(qid, ix) {
  var q = questions[qid];
  qSet(q, ix);
  doChgCalls(q);
} 
function setSels(qid, sel, unsel, del) {
  var q = questions[qid];
  if (isUndefined(del)) del = [];
  qSetMulti(q, sel, del);
  doChgCalls(q);
}
function setOptText(qid, ix, text) {
  qChangeOptText(questions[qid], ix, text);
}
function setFormattedOpt(qid, text) {
  var q = questions[qid];
  qSetFormattedOptions(q, text);
  q.opts[0].blank = null;  // reset unanswered status
} 
function toggle(qid) {
  var q = questions[qid];
  qSet(q, qIsSel(q, 0) ? 1 : 0);
  doChgCalls(q);  
}
function setCalValue(qid, value) {
  var q = setFormattedOption(qid, value);
  pushAction("setCalValue(" + qid + ",'" + value + "')", qUndoText(q));
}
function setCalcValue(qid, value) {
  var q = questions[qid];
  setFormattedOption(qid, value);
}
function doChgCalls(q) {
  syncOptions(q);
  formatQuestionAndFrags(q);
  if (q.sync) 
    syncQuestion(q);
  if (q.track) 
    addTrackOpts(q);
  //if (actions.loading) {
  //  runQActions(q.id);
  //} else {
    onChange(q.id);
  //}
}
function showNewCrop(qid) {
  var q = questions[qid];
  NewCrop.sendFromConsole(q,
    function(fs) {
      updateSessionMeds(fs);
      switch (NewCrop.sent) {
        case NewCrop.SENT_CURRENT:
          refreshNewCropCurrent(fs);
          break;
        case NewCrop.SENT_PLAN:
          refreshNewCropPlan(fs);
          break;
        case NewCrop.SENT_ALLERGY:
          refreshNewCropAllergies(fs);
          break;
      }
    });
}
function refreshNewCropPlan(fs) {
  var audits = fs.audits;
  var meds, q, qOpposite, requireDupe;
  for (var key in audits) {
    meds = audits[key];
    q = NewCrop.getQuestion(key);
    if (q) {
      qOpposite = NewCrop.getOppositeMedQuestion(key);
      requireDupe = key == NewCrop.NC_DC;
      addNewCropMeds(q, meds, qOpposite, requireDupe);
    }
  }
  autosave(true);
}
function refreshNewCropCurrent(fs) {
  var key = NewCrop.NC_CURRENT;
  var q = NewCrop.getQuestion(key);
  var qOpposite = NewCrop.getOppositeMedQuestion(key);
  addNewCropMeds(q, fs.activeMeds, qOpposite, false);
  autosave(true);
}
function refreshNewCropAllergies(fs) {
  var q = NewCrop.getQuestion(NewCrop.NC_ALLERGY);
  addNewCropAllergies(q, fs.allergies);
  autosave(true);
}
function updateSessionMeds(fs) {
  if (fs.activeMeds) {
    if (session.meds == null || (Array.is(session.meds) && session.meds.length == 0))
      session.meds = {};
    for (var i = 0; i < fs.activeMeds.length; i++) {
      var med = fs.activeMeds[i];
      session.meds[med.name] = med;
    }
  }
}