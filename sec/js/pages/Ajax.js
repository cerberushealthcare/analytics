/**
 * Ajax 
 * Requires: yahoo-min.js, connection-min.js
 * @author Warren Hornsby
 */
Ajax = {
  // 
  SVR_ICD:'Icd',
  SVR_JSON:'Json',
  SVR_LOOKUP:'Lookup',
  SVR_MSG:'Message',
  SVR_POP:'Pop',
  SVR_SCHED:'Sched',
  SVR_SESSION:'Session',
  SVR_TIANI:'Tiani',
  //
  NO_CALLBACK:false,
  //
  ERR_GET:'Ajax.get',
  ERR_POST:'Ajax.post',
  ERR_CALLBACK:'Ajax.callback',
  //
  txs:null,
  callback:null,
  _sur:['.php?', '.htm?', '.gif?', 'server', 'client'],
  _cache:{},
  //
  setSessionId:function(sessionId) {
    Ajax.sessionId = sessionId;
  },
  /*
   * Send 'GET' request
   * - server: Ajax.SVR_x
   * - action: string to provide server
   * - arg: optional, in form:
   *    {field:value,..}  // field names and string values
   *    'value'           // single value to pass as field 'id' 
   * - success: optional callback, in form:
   *    null: calls page.actionCallback(data) by default
   *    false (Ajax.NO_CALLBACK): no callback
   *    function: calls function(data)
   *    object (i.e. 'this'): calls object.actionCallback(data)
   *    [function]: calls page.function(data)
   *    [scope, function]: calls scope.function(data)
   * - error: optional callback; if null, Page.showAjaxError called
   * - failure: optional callback when AJAX fails or times out
   * - timeout: optional in seconds
   * - reviver: optional JSON reviver prototype/fn
   * - worker: optional UI element for working()
   * - usecache: cache result for use on subsequent requests
   */
  get:function(server, action, arg, success, error, failure, timeout, reviver, worker, usecache) {
    var url = this._fixGetUrl(server, action, arg), cache;
    if (usecache) {
      var fid = server + action;
      if (Ajax._cache[fid] == null)
        Ajax._cache[fid] = {};
      cache = Ajax._cache[fid];
      var cachekey = Json.encode(arg);
      if (cache[cachekey]) {
        async(function() {
          success(cache[cachekey]);
        })
        return;
      }
    }
    if (worker) 
      worker.working(true);
    try {
      var scb = this.buildScopedCallback(success, action);
      var scbErr = this._buildScopedErrorCallback(action, success, error);
      var scbFail = this.buildScopedCallback(failure);
      this._async('GET', url, this._yuiCallbacks(scb, scbErr, scbFail, url, timeout, reviver, worker, cache, cachekey));
    } catch (e) {
      throw Page.error(Ajax.ERR_GET, 'Ajax.get(' + server + ', ' + action + ')', e);
    }
  },
  /* 
   * Alternate get/reviver form  
   */
  getr:function(server, action, arg, reviver, worker, onsuccess, onerror, usecache) {
    this.get(server, action, arg, onsuccess, onerror, null, null, reviver, worker, usecache);
  },
  getr_cached:function(server, action, arg, reviver, worker, onsuccess, onerror) {
    Ajax.getr(server, action, arg, reviver, worker, onsuccess, onerror, true);
  },
  /*
   * Send 'POST' request
   * - see get function for arg description
   */
  post:function(server, action, arg, success, error, failure, timeout, reviver, worker) {
    var url = this._fixPostUrl(server);
    var params = this._buildPostParams(action, arg);
    if (worker)
      worker.working(true);
    try {
      var scb = this.buildScopedCallback(success, action);
      var scbErr = this._buildScopedErrorCallback(action, success, error);
      var scbFail = this.buildScopedCallback(failure);
      this._async('POST', url, this._yuiCallbacks(scb, scbErr, scbFail, url, timeout, reviver, worker), params);
    } catch (e) {
      throw Page.error(Ajax.ERR_POST, 'Ajax.post(' + server + ', ' + action + ')', e);
    }
  },
  /*
   * Alternate post/reviver form
   */
  postr:function(server, action, arg, reviver, worker, onsuccess, onerror, cache) {
    this.post(server, action, arg, onsuccess, onerror, null, null, reviver, worker, cache);
  },
  /*
   * Fetch HTML include
   * - url: location of HTML or javascript
   * - container: <e> to put include contents if HTML; if null, javascript will be attached to global 
   * - callback: optional; if not supplied, no callback (see get/post for forms) 
   */
  include:function(url, container, callback) {
    var yuic = {
      success:Ajax._yuiIncludeSuccess, 
      failure:Ajax._yuiIncludeFailure, 
      scope:Ajax, 
      argument:{
        'url':url,
        'container':container,
        'scb':this.buildScopedCallback(callback)}};
    url += '?' + Math.random();
    this._async('GET', url, yuic);
  },
  /*
   * callback(bool) true=call(s) in progress, false=idle  
   */
  setWorkingCallback:function(callback) {
    this.callback = callback;
  },
  /*
   * Build scoped callback object
   * - callback: optional, in form:
   *     'function': for calling page.function(data)
   *     function: for calling function(data)
   *     [function]: for calling page.function(data)
   *     [function, scope]: for calling scope.function(data)
   *     ['function', scope]: for calling scope.function(data)
   * - defaultAction: optional, defaults callback if null (see Ajax.defaultCallback)
   * Returns {  
   *  'scope':scope, 
   *  'fn':function
   *   }  // or null if callback was null
   */
  buildScopedCallback:function(callback, defaultAction) {
    if (defaultAction) {
      callback = this.defaultCallback(callback, defaultAction);
    }
    var scb = null;
    if (callback) {
      scb = {};
      if (String.is(callback)) {
        scb.scope = page;
        scb.fn = callback;
      } else if (Array.is(callback)) {
        scb.scope = (callback.length == 2) ? callback[1] : page;
        scb.fn = callback[0];
      } else {
        scb.scope = null;
        scb.fn = callback;
      }
      if (String.is(scb.fn)) {
        var method = scb.fn
        scb.fn = scb.scope[method];
        if (scb.fn == null) {
          throw Page.error(Ajax.ERR_CALLBACK, 'Ajax.buildScopedCallback: Undefined callback "' + method + '"');
        }
      }
      if (Object.isUndefined(scb.fn)) {
        throw Page.error(AJAX.ERR_CALLBACK, 'Ajax.buildScopedCallback: Undefined callback');
      }
    }
    return scb;
  },
  /*
   * - suffix: optional, default 'Callback'
   * Returns
   *    if callback=false (Ajax.NO_CALLBACK): null
   *    if callback=null:                     'actionCallback'
   *    if callback=object (i.e. "this"):     ['actionCallback',object]
   *    otherwise:                            callback (unchanged)  
   */
  defaultCallback:function(callback, action, suffix) {
    var suffix = suffix || 'Callback';
    if (callback === Ajax.NO_CALLBACK) {
      callback = null;
    } else if (callback == null) {
      callback = action + suffix;
    } else if (Object.is(callback) && ! Function.is(callback)) {
      callback = [action + suffix, callback];
    }
    return callback;
  },
  /*
   * Send return data to caller
   */
  callScopedCallback:function(scb, data) {
    if (scb) {
      if (scb.scope) 
        scb.fn.call(scb.scope, data);
      else 
        scb.fn(data);
    }
  },
  /*
   * Abort all active transactions
   * Automatically done on page unload @see _async()
   */
  abortAll:function() {
    try {
      if (Ajax.txs) {
        var txs = Ajax.txs.getValues();
        for (var i = 0, j = txs.length; i < j; i++) 
          YAHOO.util.Connect.abort(txs[i]);
      }
    } catch (e) {
    }
  },
  //
  _async:function(method, url, callback, post) {
    var tx = YAHOO.util.Connect.asyncRequest(method, url, callback, post);
    this._addTx(tx);
  },
  _buildScopedErrorCallback:function(action, success, error) {
    if (error == null) {
      if (success == Ajax.NO_CALLBACK) 
        error = Ajax.NO_CALLBACK;
      else 
        error = Page.showAjaxError;
    }
    return this.buildScopedCallback(this.defaultCallback(error, action, 'Error'));
  },
  _yuiCallbacks:function(scb, scbErr, scbFail, url, timeout, reviver, worker, cache, cachekey) {
    timeout = timeout || 29;
    if (reviver) 
      reviver = Function.is(reviver) ? reviver : reviver.revive.bind(reviver);
    return {
      success:Ajax._yuiSuccess, 
      failure:Ajax._yuiFailure, 
      scope:Ajax, 
      timeout:timeout * 1000,
      argument:{
        'scb':scb, 
        'scbErr':scbErr,
        'scbFail':scbFail,
        'url':url,
        'reviver':reviver,
        'worker':worker,
        'cache':cache,
        'cachekey':cachekey}
      };
  },
  _addTx:function(tx) {
    if (this.txs == null) {
      this.txs = Object.create(Object.Collection);
      Page.attachEvent('unload', Ajax.abortAll);
    }
    var wasEmpty = this.txs.isEmpty();
    this.txs.add(tx, tx.tId);
    if (this.callback && wasEmpty) 
      this.callback(true);
  },
  _removeTx:function(tId) {
    if (this.txs) {
      this.txs.remove(tId);
      if (this.callback && this.txs.isEmpty()) 
        this.callback(false);
    }
  },
  _yuiSuccess:function(yuiResponse) {
    this._removeTx(yuiResponse.tId);
    var response = String.trim(yuiResponse.responseText);
    var arg = yuiResponse.argument;
    var scbSuccess = arg.scb;
    var scbError = arg.scbErr;
    var url = arg.url;
    var reviver = arg.reviver;
    if (arg.worker)
      arg.worker.working(false);
    if (scbSuccess && response && response.length > 0) {
      var ajaxMsg = '(' + response  + ')';
      try {
        ajaxMsg = eval(ajaxMsg);
      } catch (e) {
        this._badResponse(1, response, url);
        return;
      }
      if (ajaxMsg.id != null) {
        if (ajaxMsg.id == 'save-timeout') {
          Page.sessionTimeout();
          return;
        }
        if (ajaxMsg.id == 'error') {
          this.callScopedCallback(scbError, ajaxMsg.obj);
        } else {
          var data = (reviver && ajaxMsg.obj) ? reviver(ajaxMsg.obj) : ajaxMsg.obj; 
          if (arg.cache)
            arg.cache[arg.cachekey] = data;
          this.callScopedCallback(scbSuccess, data);
        }
      } else {
        this._badResponse(2, response, url);
      }
    }
  },
  _yuiFailure:function(yuiResponse) {
    this._removeTx(yuiResponse.tId);
    var response = String.trim(yuiResponse.responseText || yuiResponse.statusText);
    if (response) {
      var arg = yuiResponse.argument;
      if (arg.worker)
        arg.worker.working(false);
      var scbFail = arg.scbFail;
      if (scbFail)
        this.callScopedCallback(scbFail, response);
    }
  },
  _yuiIncludeSuccess:function(yuiResponse) {
    this._removeTx(yuiResponse.tId);
    var arg = yuiResponse.argument;
    if (arg.worker)
      arg.worker.working(false);
    var container = arg.container;
    var scb = arg.scb;
    if (container) 
      container.innerHTML = yuiResponse.responseText;
    else 
      Html.Window.execScript(yuiResponse.responseText);  
    if (scb) 
      this.callScopedCallback(scb, arg.url);
  },
  _yuiIncludeFailure:function(yuiResponse) {
    var arg = yuiResponse.argument;
    if (arg.worker)
      arg.worker.working(false);
    this._removeTx(yuiResponse.tId);
    var msg = 'Include failed.\n\n' + yuiResponse.argument.url + '\n' + yuiResponse.status + ' - ' + yuiResponse.statusText;
    alert(msg);
  },  
  _badResponse:function(code, response, url) {
    var msg = 'Error ' + code + ': Server response not recognized.'; 
    //if (me.admin)
      msg += '\n' + url + '\n\n' + response.substr(0, 1200);
    alert(msg);
  },
  _fixPostUrl:function(server) {
    return this._sur[3] + server + this._sur[0] + Math.random();
  },
  _buildPostParams:function(action, arg) {
    var a = ['action=' + action];
    if (Ajax.sessionId)
      a.push('sess=' + Ajax.sessionId);
    a.push('obj=' + Json.uriEncode(arg));
    return a.join('&');
  },
  _fixGetUrl:function(server, action, arg) {
    return this._sur[3] + server + this._sur[0] + this._buildGetParams(action, arg);
  },
  _buildGetParams:function(action, arg) {
    var a = ['action=' + action];
    if (arg) {
      if (Object.is(arg)) {
        for (var fid in arg) 
          a.push(fid + '=' + encodeURIComponent(arg[fid]));
      } else {
        a.push('id=' + encodeURIComponent(arg));
      }
    }
    if (Ajax.sessionId)
      a.push('sess=' + Ajax.sessionId);
    a.push(Math.random());
    return a.join('&');
  },
  _isJavascript:function(url) {
    return (url.split('.').pop()) == 'js';
  }
};
/**
 * Server Invocations
 */
Ajax.JTemplates = {
  _SVR:'JTemplates',
  /*
   * @callback([ParInfo,..]) 
   */
  getParInfos:function(pid, callback) {
    Ajax.getr(Ajax.JTemplates._SVR, 'getParInfos', pid, ParInfos, null, callback); 
  },
  /*
   * @callback(ParInfo) 
   */
  getParInfo:function(pid, callback) {
    Ajax.getr(Ajax.JTemplates._SVR, 'getParInfos', pid, ParInfo.reviveOne.bind(ParInfo), null, callback);
  },
  /*
   * @callback(ParInfo) 
   */
  getParInfoByRef:function(ref, tid, callback) {
    Ajax.postr(Ajax.JTemplates._SVR, 'getParInfosByRef', {'ref':ref,'tid':tid}, ParInfo.reviveOne.bind(ParInfo), null, callback);
  },
  /*
   * @callback({id:#,desc:$,html:$})
   */
  preview:function(pid, map, callback) {
    Ajax.get(Ajax.JTemplates._SVR, 'preview', {'id':pid,'tid':map.templateId,'nd':map._effective}, callback);
  },
  /*
   * @callback Cinfo
   */
  cinfo:function(id, callback) {
    Ajax.get(Ajax.JTemplates._SVR, 'cinfo', id, callback);
  }
}
Ajax.Patients = {
  _SVR:'Patients',
  //
  getMru:function(/*PatientSearch*/rec, worker, callback/*PatientStub[]*/) {
    Ajax.getr(Ajax.Patients._SVR, 'getMru', {'active':rec.active ? 1 : 0}, PatientStubs, worker, callback);
  },
  search:function(/*PatientSearch*/rec, worker, callback/*PatientStub[]*/) {
    Ajax.postr(Ajax.Patients._SVR, 'search', rec, PatientStubs, worker, callback);
  },
  getNextUid:function(worker, callback) {
    Ajax.getr(Ajax.Patients._SVR, 'getNextUid', null, null, worker, callback);
  },
  saveNew:function(/*PatientAdd*/rec, worker, onsave, onerror) {
    Ajax.postr(Ajax.Patients._SVR, 'add', rec, null, worker, onsave, onerror);
  },
  saveDupe:function(/*PatientAdd*/rec, worker, onsave, onerror) {
    Ajax.postr(Ajax.Patients._SVR, 'addDupeOk', rec, null, worker, onsave, onerror);
  },
  getLangs:function(callback/*Lang[]*/) {
    Ajax.getr(Ajax.Patients._SVR, 'getLangs', null, Langs, null, callback);
  }
}
Ajax.Templates = {
  _SVR:'Templates',
  //
  getPar:function(pid, callback/*Par*/) {
    Ajax.getr(Ajax.Templates._SVR, 'getPar', pid, Par, null, callback);
  },
  getIols:function(worker, callback/*Iol[]*/) {
    Ajax.getr(Ajax.Templates._SVR, 'getIols', null, null, worker, callback);
  },
  getIolEntry:function(pid, worker, callback/*IolEntry*/) {
    Ajax.getr(Ajax.Templates._SVR, 'getIolEntry', pid, IolEntry, worker, callback);
  },
  getImmunEntry:function(pid, callback/*ImmunEntry*/) {
    Ajax.getr(Ajax.Templates._SVR, 'getImmunEntry', pid, ImmunEntry, null, callback);
  },
  getHxQuestion:function(cat, worker, callback/*Question*/) {
    var method = (cat == 'pmhx') ? 'getPmhxQuestion' : 'getPshxQuestion'; 
    Ajax.getr(Ajax.Templates._SVR, method, null, Question, worker, callback);
  },
  saveCustomOthers:function(/*Question*/q, /*string*/values) {
    if (q.Par) 
      Ajax.post(Ajax.Templates._SVR, 'saveCustomOthers', {'tid':q.Par.tid,'sid':q.Par.sid,'puid':q.Par.uid,'quid':q.uid,'values':values}, Ajax.NO_CALLBACK);
  }
}
Ajax.Tracking = {
  _SVR:'Tracking',
  /*
   * @arg [OrderItem,..] orderItems
   * @callback(tracksheet)
   */
  order:function(orderItems, callback) {
    Ajax.post(Ajax.Tracking._SVR, 'order', orderItems, callback);
  },
  saveOrder:function(/*TrackItem[]*/trackItems, callback) {
    Ajax.postr(Ajax.Tracking._SVR, 'saveOrder', trackItems, TrackItems, null, callback);
  },
  saveOrderByIp:function(/*TrackItem*/trackItem, callback) {
    Ajax.post(Ajax.Tracking._SVR, 'saveOrderByIp', trackItem, callback);
  },
  /*
   * @arg TrackItem rec
   * @callback()
   */
  update:function(rec, callback) {
    Ajax.postr(Ajax.Tracking._SVR, 'update', rec, TrackItem, null, callback);
  },
  /*
   * @callback(pid)
   */
  getPid:function(callback) {
    Ajax.post(Ajax.Tracking._SVR, 'getPid', null, callback);
  },
  /*
   * @arg int type 0=open by cat, 1=unsched by date, 2=closed
   * @callback([TrackItemList,..])
   */
  getTrackItems:function(type, criteria, worker, callback) {
    Ajax.postr(Ajax.Tracking._SVR, Ajax.Tracking._requestFromType(type), criteria, TrackItems, worker, callback);
  },
  /*
   * @arg int id
   * @callback(TrackItem)
   */
  get:function(id, callback) {
    Ajax.getr(Ajax.Tracking._SVR, 'get', id, TrackItem, null, callback);
  },
  //
  _requestFromType:function(type) {
    switch (type) {
      case 0:
        return 'getOpen';
      case 1:
        return 'getUnsched';
      case 2:
        return 'getSched';
      case 3:
        return 'getClosed';
    } 
  }
}
Ajax.Polling = {
  _SVR:'Polling',
  /*
   * @arg int cid
   * @callback(timestamp) in SQL format
   */
  pollCuTimestamp:function(cid, callback) {
    Ajax.get(this._SVR, 'pollCuTimestamp', cid, callback);
  },
  /*
   * @callback(int) unread message count
   */
  getMyInboxCt:function(callback) {
    Ajax.get(this._SVR, 'getMyInboxCt', null, callback);
  },
  /*
   * @callback(int) unreviewed stub message count
   */
  getMyUnreviewedCt:function(callback) {
    Ajax.get(this._SVR, 'getMyUnreviewedCt', null, callback);
  },
  /*
   * @callback(int) unreviewed stub message count
   */
  getMyLabCt:function(callback) {
    Ajax.get(this._SVR, 'getMyLabCt', null, callback);
  },
  /*
   * @callback(ErxStatusCount) @see NewCrop::pullAcctStatus
   */
  getStatusCount:function(callback) {
    timeout = 60;
    Ajax.get(this._SVR, 'getStatusCount', null, callback, null, null, timeout);
  }  
}
Ajax.Erx = {
  _SVR:'Erx',
  /*
   * @arg int cid
   * @callback(['required-field',..]) 
   */
  validate:function(cid, callback) {
    Ajax.get(this._SVR, 'validate', cid, callback);
  },
  /*
   * @arg int cid
   * @arg string since 'yyyy-mm-dd hh:mm:ss' (optional)
   * @arg bool auditless (default false)
   * callback(obj) non-revived facesheet
   */
  refresh:function(cid, since, auditless, callback) {
    args = {'id':cid, 'since':since, 'auditless':auditless};
    Ajax.getr(this._SVR, 'refresh', args, null, null, callback);
  },
  /*
   * @callback([ErxStatus,..])
   */
  getStatusDetails:function(callback) {
    Ajax.get(this._SVR, 'getStatusDetail', null, callback);
  },
  /*
   * @callback([ErxPharm,..]) for logged-in LP
   */
  getPharmReqs:function(callback) {
    Ajax.get(this._SVR, 'getPharmReqs', null, callback);
  },
  /*
   * @callback([ErxPharm,..]) for all LPs
   */
  getAllPharmReqs:function(callback) {
    Ajax.get(this._SVR, 'getAllPharmReqs', null, callback);
  },
  /*
   * @arg ErxPharm req
   * @callback([Client,..])
   */
  matchClients:function(req, callback) {
    Ajax.post(this._SVR, 'matchClients', req, callback);
  }
}
Ajax.Facesheet = {
  _SVR:'Facesheet',
  /*
   * @arg int cid
   * @callback(Facesheet)
   */
  get:function(cid, worker, callback) {
    Ajax.getr(Ajax.Facesheet._SVR, 'get', cid, Facesheet, worker, callback);
  },
  /*
   * @arg string timestamp SQL format 
   * @callback(Facesheet) if facesheet changed since timestamp
   */
  getIfUpdated:function(cid, timestamp, worker, callback) {
    var args = {'id':cid, 'cu':String.nullify(timestamp)};
    Ajax.getr(Ajax.Facesheet._SVR, 'getIfUpdated', args, Facesheet, worker, callback);
  },
  //
  Patients:{
    save:function(client, callback, callbackError) {
      Ajax.post(Ajax.Facesheet._SVR, 'savePatient', client, callback, callbackError);
    },
    /*
    saveDupe:function(client, callback, callbackError) {
      Ajax.post(Ajax.Facesheet._SVR, 'savePatientDupe', client, callback, callbackError);
    },
    getNextUid:function(callback) {
      Ajax.get(Ajax.Facesheet._SVR, 'getNextPatientUid', null, callback);
    },
    */
    /*
     * @arg {'id':cid,'address':Address} object
     * @callback(Client) 
     */
    saveAddress:function(object, callback) {
      Ajax.post(Ajax.Facesheet._SVR, 'savePatientAddress', object, callback);
    },
    /*
     * @arg {'id':cid,'icard':ICard} object
     * @callback(Client) 
     */
    saveICard:function(object, callback) {
      Ajax.post(Ajax.Facesheet._SVR, 'savePatientICard', object, callback);
    },
    /*
     * @arg int cid
     * @arg string text
     * @callback(Facesheet)
     */
    saveNotes:function(cid, text, callback) {
      Ajax.postr(Ajax.Facesheet._SVR, 'savePatientNotes', {'cid':cid,'text':text}, Facesheet, null, callback);
    },
    /*
     * @arg int cid
     * @callback()
     */
    breakGlass:function(cid, callback) {
      Ajax.get(Ajax.Facesheet._SVR, 'breakGlass', cid, callback);
    },
    /*
     * @arg int cid
     * @callback(Client)
     */
    removeImg:function(cid, callback) {
      Ajax.get(Ajax.Facesheet._SVR, 'removeImg', cid, callback);
    },
    /*
     * @arg int cid
     * @callback(Client)
     */
    get:function(cid, worker, callback) {
      Ajax.getr(Ajax.Facesheet._SVR, 'getClient', cid, null, worker, callback);
    }
  },
  Meds:{
    reconcile:function(cid, callback) {
      Ajax.getr(Ajax.Facesheet._SVR, 'reconcileMeds', cid, Facesheet, null, callback);
    },
    reconcile2:function(cid, callback) {
      Ajax.getr(Ajax.Facesheet._SVR, 'reconcileMeds2', cid, Facesheet, null, callback);
    },
    /*
     * @arg int cid
     * @callback(Facesheet)
     */
    getHist:function(cid, callback) {
      Ajax.get(Ajax.Facesheet._SVR, 'getMedHist', cid, callback);
    },
    /*
     * @arg [int,..] ids
     * @callback(Facesheet)
     */
    deactivateMany:function(ids, callback) {
      Ajax.post(Ajax.Facesheet._SVR, 'deactivateMeds', ids, callback);
    },
    /*
     * @arg Med med
     * @callback(Facesheet)
     */
    save:function(med, callback) {
      Ajax.post(Ajax.Facesheet._SVR, 'saveMed', med, callback);
    },
    /*
     * @arg int cid
     * @callback(Facesheet)
     */
    deleteLegacy:function(cid, callback) {
      Ajax.getr(Ajax.Facesheet._SVR, 'deleteLegacyMeds', cid, Facesheet, null, callback);
    },
    /*
     * @arg [Med,..] meds
     * @callback(Facesheet)
     */
    printRx:function(meds, callback) {
      Ajax.post(Ajax.Facesheet._SVR, 'printRxMeds', meds, callback);
    },
    /*
     * @arg int cid
     * @callback(Facesheet) 
     */
    setNone:function(cid, callback) {
      Ajax.getr(Ajax.Facesheet._SVR, 'setMedsNone', cid, Facesheet, null, callback);
    },
    /*
     * @arg int cid
     * @arg Med[] meds 
     * @callback(Facesheet) 
     */
    saveReviewed:function(cid, meds, callback) {
      Ajax.postr(Ajax.Facesheet._SVR, 'saveReviewed', {'cid':cid,'meds':meds}, Facesheet, null, callback);
    }
  },
  //
  Allergies:{
    reconcile:function(cid, allers, callback) {
      Ajax.postr(Ajax.Facesheet._SVR, 'reconcileAllergies', {'cid':cid,'allergies':allers}, Facesheet, Html.Window, callback);
    },
    /*
     * @callback(JQuestion)
     */
    getQuestion:function(callback) {
      Ajax.get(Ajax.Facesheet._SVR, 'getAllergyQuestion', null, callback);
    },
    /*
     * @arg Allergy allergy
     * @callback(Facesheet)
     */
    save:function(allergy, callback) {
      Ajax.post(Ajax.Facesheet._SVR, 'saveAllergy', allergy, callback);
    },
    /*
     * @arg int id
     * @callback(Facesheet)
     */
    deactivate:function(id, callback) {
      Ajax.get(Ajax.Facesheet._SVR, 'deactivateAllergy', id, callback);  
    },
    /*
     * @arg [int,..] ids
     * @callback(Facesheet)
     */
    deactivateMany:function(ids, callback) {
      Ajax.post(Ajax.Facesheet._SVR, 'deactivateAllergies', ids, callback);
    },
    /*
     * @arg int cid
     * @callback(Facesheet)
     */
    deleteLegacy:function(cid, callback) {
      Ajax.getr(Ajax.Facesheet._SVR, 'deleteLegacyAllergies', cid, Facesheet, null, callback);
    }
  },
  //
  Vitals:{
    /*
     * @callback({'prop'=>JQuestion,..})
     */
    getQuestions:function(callback) {
      Ajax.get(Ajax.Facesheet._SVR, 'getVitalQuestions', null, callback);
    },
    /*
     * @arg Vital vital
     * @callback(Facesheet)
     */
    save:function(vital, callback) {
      Ajax.post(Ajax.Facesheet._SVR, 'saveVital', vital, callback);
    },
    /*
     * @arg int id
     * @callback(Facesheet)
     */
    deactivate:function(id, callback) {
      Ajax.get(Ajax.Facesheet._SVR, 'deactivateVital', id, callback);
    },
    /*
     * @arg Client client
     * @callback([{'id':$,'title':$},..])
     */
    getCharts:function(client, callback) {
      var args = {'age':client.ageYears,'sex':client.sex};
      Ajax.get(Ajax.Facesheet._SVR, 'getCharts', args, callback);
    }
  },
  //
  Immuns:{
    /*
     * @arg Immun immun
     * @callback(Facesheet)
     */
    save:function(immun, callback) {
      Ajax.postr(Ajax.Facesheet._SVR, 'saveImmun', immun, Facesheet, null, callback);
    },
    /*
     * @arg int id
     * @callback(Facesheet)
     */
    remove:function(id, callback) {
      Ajax.getr(Ajax.Facesheet._SVR, 'deleteImmun', id, Facesheet, null, callback);
    }
  },
  //
  Diagnoses:{
    //
    reconcile:function(cid, diags, callback) {
      Ajax.postr(Ajax.Facesheet._SVR, 'reconcileDiagnoses', {'cid':cid,'diags':diags}, Facesheet, Html.Window, callback);
    },
    /*
     * @arg Diagnosis diagnosis
     * @callback(Facesheet)
     */
    save:function(diagnosis, callback) {
      Ajax.postr(Ajax.Facesheet._SVR, 'saveDiagnosis', diagnosis, Facesheet, null, callback);
    },
    /*
     * @arg int id
     * @callback(Facesheet)
     */
    remove:function(id, callback) {
      Ajax.getr(Ajax.Facesheet._SVR, 'deleteDiagnosis', id, Facesheet, null, callback);
    },
    /*
     * @arg int cid
     * @callback(Facesheet) 
     */
    setNone:function(cid, worker, callback) {
      Ajax.getr(Ajax.Facesheet._SVR, 'setDiagnosisNone', cid, Facesheet, worker, callback);
    },
    /*
     * @arg Diagnosis diag
     * @callback(Facesheet) 
     */
    copyToMedHx:function(diag, callback) {
      Ajax.postr(Ajax.Facesheet._SVR, 'copyToMedHx', diag, Facesheet, null, callback);
    }
  },
  //
  Documentation:{
    /*
     * @arg int cid
     * @arg fn callback(DocStubs)
     */
    getAll:function(cid, callback) {
      Ajax.getr(Ajax.Facesheet._SVR, 'getDocStubs', cid, DocStubs, null, callback);
    },
    /*
     * @arg DocStub rec
     * @arg fn callback(DocStub)
     */
    refetch:function(rec, worker, callback) {
      Ajax.postr(Ajax.Facesheet._SVR, 'refetchStub', rec, DocStub, worker, callback);
    },
    /*
     * @arg fn callback(DocStubs)
     */
    getUnreviewed:function(worker, callback) {
      Ajax.getr(Ajax.Facesheet._SVR, 'getUnreviewed', null, DocStubs, worker, callback);
    },
    /*
     * @arg DocStub rec
     * @arg fn callback(DocStubPreview)  
     */
    preview:function(rec, worker, callback) {
      Ajax.postr(Ajax.Facesheet._SVR, 'preview', rec, DocStubPreview, worker, callback);
    },
    /* 
     * @arg MsgThread_Stub rec
     * @arg fn callback(DocStubPreview)
     */
    reviewed:function(rec, worker, callback) {
      Ajax.getr(Ajax.Facesheet._SVR, 'reviewed', rec.threadId, DocStubPreview, worker, callback);
    },
    //
    forward:function(stubType, stubId, userId, worker, callback/*DocStubPreview*/) {
      Ajax.getr(Ajax.Facesheet._SVR, 'forward', {stubType:stubType,stubId:stubId,userId:userId}, DocStubPreview, worker, callback);
    }
  }
}
Ajax.Audit = {
  _SVR:'Audit',
  /*
   * @arg int cid
   */
  printFacesheet:function(client) {
    Ajax.get(this._SVR, 'printFacesheet', {'cid':client.clientId, 'name':client.name}, Ajax.NO_CALLBACK);
  },
  /*
   * @arg int cid
   */
  printPop:function(cid, tableId, title) {
    Ajax.get(this._SVR, 'printPop', {'cid':cid, 'tableId':tableId, 'title':title}, Ajax.NO_CALLBACK);
  },
  /*
   * @arg int cid
   * @arg string chartId
   */
  printVitalsChart:function(cid, chart) {
    Ajax.get(this._SVR, 'printVitalsChart', {'cid':cid,'chartId':chart.id,'title':chart.title}, Ajax.NO_CALLBACK);
  }
}
Ajax.Profile = {
  _SVR:'Profile',
  get:function(callback) {
    Ajax.getr(this._SVR, 'get', null, Profile, Html.Window, callback);
  },
  saveUser:function(rec, callback) {
    Ajax.postr(this._SVR, 'saveUser', rec, Profile, Html.Window, callback);
  },
  saveGroup:function(rec, callback) {
    Ajax.postr(this._SVR, 'saveGroup', rec, Profile, Html.Window, callback);
  },
  saveBilling:function(rec, callback) {
    Ajax.postr(this._SVR, 'saveBilling', rec, Profile, Html.Window, callback);
  },
  saveTimeout:function(min, callback) {
    Ajax.getr(this._SVR, 'saveTimeout', min, Profile, Html.Window, callback);
  },
  changePassword:function(id, cpw, pw, onsuccess, onerror) {
    Ajax.post(this._SVR, 'changePassword', {'id':id,'cpw':cpw,'pw':pw}, onsuccess, onerror);
  },
  setPassword:function(id, pw, tablet, onsuccess, onerror) {
    Ajax.post(this._SVR, 'setPassword', {'id':id,'pw':pw,'tablet':tablet}, onsuccess, onerror);
  },
  verifyPassword:function(pw, callback) {
    Ajax.postr(this._SVR, 'verifyPassword', {'pw':pw}, null, Html.Window, callback);
  }
}
Ajax.Users = {
  _SVR:'Users',
  /*
   * @arg fn(Users) callback
   */
  get:function(worker, callback) {
    Ajax.getr(this._SVR, 'get', null, Users, worker, callback);
  },
  /*
   * @arg User rec
   * @arg fn(User) callback
   */
  save:function(worker, rec, callback) {
    Ajax.postr(this._SVR, 'save', rec, User, worker, callback);
  },
  /*
   * @arg int userId
   * @arg fn(User) callback
   */
  deactivate:function(worker, userId, callback) {
    Ajax.getr(this._SVR, 'deactivate', userId, User, worker, callback);
  },
  activate:function(worker, userId, callback) {
    Ajax.getr(this._SVR, 'activate', userId, User, worker, callback);
  },
  /*
   * @arg NcUser rec
   * @arg fn(User) callback
   */
  saveErx:function(worker, rec, callback) {
    Ajax.postr(this._SVR, 'saveErx', rec, NcUser, worker, callback);
  },
  /*
   * @arg int userId
   * @arg fn(userId) callback
   */
  removeErx:function(worker, userId, callback) {
    Ajax.getr(this._SVR, 'removeErx', userId, null, worker, callback);
  }
}
Ajax.Adt = {
  _SVR:'Adt',
  /*
   * @arg int cid
   * @callback(AdtFile)
   */
  get:function(sessdata, callback) {
    Ajax.post(this._SVR, 'get', sessdata, callback);
  },
  /*
   * @arg int cid
   * @arg string pass
   * @callback (AdtFile)
   */
  encrypt:function(cid, pass, callback) {
    Ajax.get(this._SVR, 'get', {'id':cid, 'pw':pass}, callback);
  }
}
Ajax.Vxu = {
  _SVR:'Vxu',
  /*
   * @arg int cid
   * @callback(VxuFile)
   */
  get:function(cid, callback) {
    Ajax.get(this._SVR, 'get', cid, callback);
  },
  /*
   * @arg int cid
   * @arg string pass
   * @callback (VxuFile)
   */
  encrypt:function(cid, pass, callback) {
    Ajax.get(this._SVR, 'get', {'id':cid, 'pw':pass}, callback);
  }
}
Ajax.Ccd = {
  _SVR:'Ccd',
  //
  get:function(cid, custmap, callback) {
    Ajax.post(this._SVR, 'get', {id:cid, custmap:custmap || 0}, callback);
  },
  /*
   * @arg int cid
   * @arg string pass
   * @callback (CcdFile)
   */
  encrypt:function(cid, pass, callback) {
    Ajax.get(this._SVR, 'get', {'id':cid, 'pw':pass}, callback);
  },
  print:function(filename, cid, demoOnly, visit) {
    Html.ServerForm.submit(this._SVR, 'print', {'filename':filename, 'cid':cid, 'demoOnly':demoOnly, 'visit':visit});
  },
  clearBatch:function(callback) {
    Ajax.getr(this._SVR, 'clearBatch', null, null, Html.Window, callback);
  },
  batchSync:function(callback) {
    Ajax.getr(this._SVR, 'batchSync', null, null, Html.Window, callback);
  },
  getSectionTexts:function(cid, callback) {
    Ajax.getr(this._SVR, 'getSectionTexts', cid, null, Html.Window, callback);
  },
  getMeds:function(scanIndexId, /*Med_Ci[]*/callback) {
    Ajax.getr(this._SVR, 'getMeds', scanIndexId, null, Html.Window, callback);
  },
  getAllergies:function(scanIndexId, /*Allergy_Ci[]*/callback) {
    Ajax.getr(this._SVR, 'getAllergies', scanIndexId, null, Html.Window, callback);
  },
  getDiags:function(scanIndexId, /*Diagnosis_Ci[]*/callback) {
    Ajax.getr(this._SVR, 'getDiags', scanIndexId, null, Html.Window, callback);
  },
  refuse:function(cid, callback) {
    Ajax.getr(this._SVR, 'refuse', cid, null, Html.Window, callback);
  }
}
Ajax.Snomed = {
  _SVR:'Snomed',
  //
  search:function(text, callback) {
    Ajax.getr(Ajax.Snomed._SVR, 'search', text, null, null, callback);
  }
}
Ajax.Ipc = {
  _SVR:'Ipc',
  //
  getAll:function(callback/*Ipc[]*/) {
    Ajax.getr(Ajax.Ipc._SVR, 'getAll', null, Ipcs, null, callback);
  },
  getAll_forCat:function(/*int*/cat, callback/*Ipc[]*/) {
    Ajax.getr(Ajax.Ipc._SVR, 'getAll', cat, Ipcs, null, callback);
  },
  getIpcHmsFor:function(cid, worker, callback/*IpcHm[]*/) {
    Ajax.getr(Ajax.Ipc._SVR, 'getIpcHmsFor', cid, IpcHms, worker, callback);
  },
  saveIpcHm:function(/*IpcHm*/rec, worker, callback/*IpcHm*/) {
    Ajax.postr(Ajax.Ipc._SVR, 'saveIpcHm', rec, IpcHm, worker, callback);
  },
  delIpcHm:function(/*IpcHm*/rec, worker, callback/*IpcHm(non-custom replacement)*/) {
    Ajax.postr(Ajax.Ipc._SVR, 'delIpcHm', rec, IpcHm, worker, callback);
  },
  allHmIpc:function(worker, callback/*Ipc[]*/) {
    Ajax.getr(Ajax.Ipc._SVR, 'allHmIpc', null, null, worker, callback);
  },
  allDueNow:function(worker, ipc, callback/*Client_Rep[]*/) {
    Ajax.getr(Ajax.Ipc._SVR, 'allDueNow', ipc, null, worker, callback); 
  },
  recordReminders:function(/*int[]*/cids, name, callback) {
    Ajax.post(Ajax.Ipc._SVR, 'recordReminders', {'cids':cids,'name':name}, callback);
  },
  downloadDueNow:function(worker, /*int*/ipc) {
    Html.ServerForm.create('Ipc', 'downloadAllDueNow', {'ipc':ipc}).submit();
  }
}
Ajax.Providers = {
  _SVR:'Providers',
  /*
   * @callback([Ipc,..]) 
   */
  getAll:function(callback) {
    Ajax.getr(Ajax.Providers._SVR, 'getAll', null, Providers, null, callback);
  },
  getAllActive:function(callback) {
    Ajax.getr(Ajax.Providers._SVR, 'getAllActive', null, Providers, null, callback);
  },
  /*
   * @arg Provider rec
   * @callback(Provider)
   */
  save:function(rec, callback) {
    Ajax.postr(Ajax.Providers._SVR, 'save', rec, Provider, null, callback);
  },
  /*
   * @arg int id
   * @callback(id)
   */
  remove:function(id, callback) {
    Ajax.get(Ajax.Providers._SVR, 'delete', id, callback);
  },
  //
  Facilities:{
    /*
     * @callback([Ipc,..]) 
     */
    getAll:function(callback) {
      Ajax.getr(Ajax.Providers._SVR, 'getFacilities', null, Facilities, null, callback);
    },
    /*
     * @arg Provider rec
     * @callback(Provider)
     */
    save:function(rec, callback) {
      Ajax.postr(Ajax.Providers._SVR, 'saveFacility', rec, Facility, null, callback);
    },
    /*
     * @arg int id
     * @callback(id)
     */
    remove:function(id, callback) {
      Ajax.get(Ajax.Providers._SVR, 'deleteFacility', id, callback);
    }    
  }
}
Ajax.Scanning = {
  _SVR:'Scanning',
  /*
   * @callback([ScanFile,..]) 
   */
  getUnindexed:function(userId, worker, callback) {
    Ajax.getr(this._SVR, 'getUnindexed', userId, ScanFiles, worker, callback);
  },
  /*
   * @callback([ScanIndex,..]) 
   */
  getIndexedToday:function(worker, callback) {
    Ajax.getr(this._SVR, 'getIndexedToday', null, ScanFiles, worker, callback);
  },
  /*
   * @arg int sfid
   * @callback(ScanIndex)
   */
  getIndex:function(sfid, worker, callback) {
    Ajax.getr(this._SVR, 'getIndex', sfid, ScanIndex, worker, callback);
  },
  /*
   * @arg int sfid
   * @arg fn callback(ScanIndex)
   */
  reviewed:function(sfid, worker, callback) {
    Ajax.getr(this._SVR, 'reviewed', sfid, ScanIndex, worker, callback);
  },
  /*
   * @arg ScanIndex rec
   * @arg int[] sfids
   * @callback(ScanIndex) 
   */
  saveIndex:function(rec, sfids, callback) {
    Ajax.post(this._SVR, 'saveIndex', {'rec':rec,'sfids':sfids}, callback);
  },
  /*
   * @arg int id
   * @callback(id)
   */
  removeIndex:function(id, callback) {
    Ajax.get(this._SVR, 'deleteIndex', id, callback);
  },
  /*
   * @arg int id
   * @callback(id)
   */
  deleteFile:function(id, callback) {
    Ajax.get(this._SVR, 'deleteFile', id, callback);
  },
  /*
   * @arg string filename
   * @callback()
   */
  splitBatch:function(filename, callback) {
    Ajax.get(this._SVR, 'splitBatch', filename, callback);
  },
  /*
   * @arg int id
   * @callback(ScanFile)
   */
  rotate:function(id, worker, callback) {
    Ajax.getr(this._SVR, 'rotate', id, ScanFile, worker, callback);
  },
  /*
   * @arg int sxid
   */
  download:function(id) {
    Html.ServerForm.submit(this._SVR, 'download', {'id':id});
  }
}
Ajax.Reporting = {
  _SVR:'Reporting',
  //
  fetchImmunAudits:function(cid, immunId, callback/*Audit_Rep[]*/) {
    Ajax.postr(this._SVR, 'fetchImmunAudits', {cid:cid,immunId:immunId}, null, Html.Window, function(recs) {
      recs = RepRecs.revive(recs, AuditRec);
      callback(recs);
    });
  },
  /*
   * @arg int type 
   * @arg fn(ReportCriteria) onsuccess
   */
  newReport:function(type, worker, onsuccess) {
    Ajax.getr(this._SVR, 'newReport', type, ReportCriteria, worker, onsuccess);
  },
  /*
   * @arg int id
   * @arg fn(ReportCriteria) onsuccess 
   */
  getReport:function(id, worker, onsuccess) {
    Ajax.getr(this._SVR, 'getReport', id, ReportCriteria, worker, onsuccess);
  },
  /*
   * @arg int id
   * @arg fn(id) onsuccess 
   */
  deleteReport:function(id, worker, onsuccess) {
    Ajax.getr(this._SVR, 'deleteReport', id, null, worker, onsuccess);
  },
  /*
   * @arg fn(ReportCriteria[]) onsuccess 
   */
  getStubs:function(worker, onsuccess) {
    Ajax.getr(this._SVR, 'getStubs', null, ReportStubs, worker, onsuccess);
  },
  /*
   * @arg int id
   * @arg bool num 
   */
  download:function(report, num, noncomps, duenow) {
    var comment = report.comment;
    var refs = report.refs;
    report.comment = null;
    report.refs = null;
    Html.RecForm.create('serverReporting.php', {
      'action':'download',
      'obj':Json.encode(report, true),
      'num':String.from(Boolean.toInt(num)),
      'nc':String.from(Boolean.toInt(noncomps)),
      'duenow':String.from(Boolean.toInt(duenow))}).submit();
    report.comment = comment;
    report.refs = refs;
  },
  /*
   * @arg int id
   * @arg fn(ReportCriteria) onsuccess
   */
  save:function(report, worker, onsuccess) {
    Ajax.postr(this._SVR, 'save', report, ReportCriteria, worker, onsuccess);
  },
  /*
   * @arg ReportCriteria report
   * @arg fn(ReportCriteria) onsuccess
   */
  generate:function(report, worker, onsuccess) {
    Ajax.postr(this._SVR, 'generate', report, ReportCriteria, worker, onsuccess);
  },
  /*
   * @arg string table
   * @arg fn(RepCritJoin) onsuccess
   */
  getJoin:function(table, worker, onsuccess) {
    Ajax.getr(this._SVR, 'getJoin', table, null, worker, onsuccess);
  }
}
Ajax.PortalAccounts = {   
  _SVR:'PortalAccounts',
  //
  refuse:function(cid, callback) {
    Ajax.getr(this._SVR, 'refuse', cid, null, Html.Window, callback);
  },
  /*
   * @arg fn(PortalUsers) onsuccess
   */
  getPortalUsers:function(onsuccess) {
    Ajax.getr(this._SVR, 'getPortalUsers', null, PortalUsers, null, onsuccess);
  },
  /*
   * @arg int cid
   * @arg fn(PortalUser) onsuccess
   */
  createPortalUser:function(rec, worker, onsuccess) {
    Ajax.postr(this._SVR, 'createPortalUser', rec, PortalUser, worker, onsuccess);
  },
  /*
   * @arg int cid
   * @arg fn(PortalUser) onsuccess
   */
  savePortalUser:function(rec, worker, onsuccess) {
    Ajax.postr(this._SVR, 'savePortalUser', rec, PortalUser, worker, onsuccess);
  },
  /*
   * @arg int cid
   * @arg fn(PortalUser) onsuccess
   */
  editPortalUserFor:function(cid, worker, onsuccess) {
    Ajax.getr(this._SVR, 'editPortalUserFor', cid, PortalUser, worker, onsuccess);
  },
  /*
   * @arg int cid
   * @arg fn(PortalUser) onsuccess
   */
  resetPortalUser:function(cid, worker, onsuccess) {
    Ajax.getr(this._SVR, 'resetPortalUser', cid, PortalUser, worker, onsuccess);
  },
  /*
   * @arg int cid
   * @arg fn(PortalUser) onsuccess
   */
  suspendPortalUser:function(cid, worker, onsuccess) {
    Ajax.getr(this._SVR, 'suspendPortalUser', cid, PortalUser, worker, onsuccess);
  },
  /*
   * @arg int userId (optional)
   * @arg fn onsuccess(PortalMsgTypes)
   */
  getPortalMsgTypes:function(userId, worker, onsuccess) {
    Ajax.getr(this._SVR, 'getPortalMsgTypes', userId, PortalMsgTypes, worker, onsuccess);
  },
  /*
   * @arg PortalMsgTypes[] recs
   * @arg fn onsuccess(PortalMsgTypes)
   */
  savePortalMsgTypes:function(recs, userId, worker, onsuccess) {
    Ajax.postr(this._SVR, 'savePortalMsgTypes', {'recs':recs, 'id':userId}, PortalMsgTypes, worker, onsuccess);
  }
}
Ajax.Dashboard = {
  _SVR:'Dashboard',
  /*
   * @arg fn(Dashboard) callback
   */
  get:function(worker, callback) {
    Ajax.postr(this._SVR, 'get', null, Dashboard, worker, callback); 
  },
  /*
   * @arg string date 'yyyy-mm-dd'
   * @arg int provider (optional)
   * @arg fn(Dashboard) callback
   */
  getAppts:function(date, provider, worker, callback) {
    Ajax.postr(this._SVR, 'getAppts', {'date':date, 'provider':provider}, Dashboard, worker, callback);
  },
  /*
   * @arg int userId (optional)
   * @arg fn(Dashboard) callback
   */
  getMessages:function(userId, worker, callback) {
    Ajax.getr(this._SVR, 'getMessages', userId, Dashboard, worker, callback);
  },
  /*
   * @arg fn(Login_Dash[]) callback
   */
  getLoginHist:function(worker, callback) {
    Ajax.getr(this._SVR, 'getLoginHist', null, null, worker, callback);
  }
}
Ajax.InfoButton = {
  _SVR:'InfoButton',
  /*
   * 
   */
  fetch:function(type, id, onsuccess) {
    Ajax.getr(this._SVR, type, id, InfoButtonResponse, Html.Window, onsuccess);
  },
  fetch_diag:function(icd, onsuccess) {
    this.fetch('diag', icd, onsuccess);
  }
}
Ajax.Labs = {
  _SVR:'Labs',
  /*
   * @arg fn(int) onsuccess
   */
  getInboxCt:function(onsuccess) {
    Ajax.get(this._SVR, 'getInboxCt', null, onsuccess);
  },
  /*
   * @arg fn(Hl7Inbox[]) onsuccess
   */
  getInboxes:function(worker, onsuccess) {
    Ajax.getr(this._SVR, 'getInboxes', null, Hl7Inboxes, worker, onsuccess);
  },
  /*
   * @arg int id hl7InboxId
   * @arg fn(OruMessage) onsuccess
   */
  getInboxMessage:function(id, onsuccess) {
    Ajax.getr(this._SVR, 'getInboxMessage', id, OruMessage, Html.Window, onsuccess);
  },
  /*
   * @arg fn() onsuccess
   */
  removeInbox:function(id, worker, onsuccess) {
    Ajax.getr(this._SVR, 'removeInbox', id, null, worker, onsuccess);
  },
  /*
   * @arg int cid
   * @arg int id hl7InboxId
   * @arg fn(ClientRecon) onsuccess
   */
  setClient:function(cid, id, worker, onsuccess) {
    Ajax.getr(this._SVR, 'setClient', {'cid':cid, 'id':id}, LabRecon, worker, onsuccess);
  },
  /*
   * @arg int id hl7InboxId
   * @arg fn(LabRecon) onsuccess
   */
  getRecon:function(id, worker, onsuccess) {
    Ajax.getr(this._SVR, 'getRecon', id, LabRecon, worker, onsuccess);
  },
  /*
   * @arg int id hl7InboxId
   * @arg ORU_Lab msg
   * @arg fn(LabRecon) onsuccess
   */
  saveRecon:function(id, msg, worker, onsuccess) {
    Ajax.postr(this._SVR, 'saveRecon', {'id':id,'msg':msg}, LabRecon, worker, onsuccess);
  },
  /*
   * @arg LabRecon recon
   * @arg Proc[] recs
   * @arg int[] checked [trackItemId,..]
   * @arg fn() onsuccess
   */
  saveRecon2:function(recon, recs, checked, worker, onsuccess) {
    var id = recon.Inbox.hl7InboxId;
    var cid = recon.Client.clientId;
    Ajax.postr(this._SVR, 'saveRecon', {'id':id,'cid':cid,'procs':recs,'checked':checked}, null, worker, onsuccess);
  }
}
Ajax.Procedures = {
  _SVR:'Procedures',
  /*
   * @arg int cid
   * @callback(Proc[])
   */
  getAll:function(cid, worker, callback) {
    Ajax.getr(this._SVR, 'getAll', cid, Procedures, worker, callback);
  },
  /*
   * @arg int procId
   * @callback(Proc)
   */
  get:function(procId, callback) {
    Ajax.getr(this._SVR, 'get', procId, Proc, null, callback);
  },
  /*
   * @arg Proc rec
   * @callback(Proc)
   */
  saveProc:function(rec, worker, callback, onerror) {
    Ajax.postr(this._SVR, 'saveProc', rec, Proc, worker, callback, onerror);
  },
  /*
   * @arg IolProc rec
   * @callback()
   */
  savePanel:function(rec, worker, callback, onerror) {
    Ajax.postr(this._SVR, 'savePanel', rec, null, worker, callback, onerror);
  },
  /*
   * @arg int procId
   * @arg ProcResult result
   * @callback(ProcResult)
   */
  saveResult:function(procId, result, worker, callback, onerror) {
    Ajax.postr(this._SVR, 'saveResult', {'procId':procId,'result':result}, ProcResult, worker, callback, onerror);
  },
  /*
   * @arg int id
   * @callback(int) id
   */
  deleteProc:function(id, worker, callback) {
    Ajax.getr(this._SVR, 'delete', id, null, worker, callback);
  },
  /*
   * @arg int id
   * @callback(int) id
   */
  deleteResult:function(id, callback) {
    Ajax.get(this._SVR, 'deleteResult', id, callback);
  },
  /*
   * @arg ProcResult result
   * @callback(ResultHists)
   */
  getResultHistory:function(result, callback) {
    Ajax.postr(this._SVR, 'getResultHistory', result, ResultHists, null, callback);
  },
  /*
   * @arg int cid
   * @arg int ipc
   */
  record:function(cid, ipc) {
    Ajax.get(this._SVR, 'record', {'cid':cid, 'ipc':ipc}, Ajax.NO_CALLBACK);
  },
  /*
   * @arg int procId
   */
  download:function(id) {
    Html.ServerForm.submit(this._SVR, 'download', {'id':id});
  }
}