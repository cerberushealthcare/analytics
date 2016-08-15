/**
 * Doc Opener
 * Global static 
 */
var DocOpener = {
  cid:null,
  sid:null,
  stubs:null,    // JSessionStubs
  nav:null,
  session:null,  // curently viewed JSessionStub
  _zoom:null,
  _ptCustoms:null,
  _previewing:null,
  /*
   * Doc preview pop
   * - cid: client ID
   * - sid: session ID, optional; if null, first session will be shown
   */
  popPreview:function(cid, sid, zoom) {
    Html.Window.working(true);
    this.sid = sid;
    this._zoom = zoom;
    if (this.cid == cid && this.stubs) {
      this._preview(this.stubs);      
    } else {
      this.cid = cid;
      var self = this;
      Includer.get([Includer.HTML_DOC_OPENER, Includer.HTML_DOC_DOWNLOAD_FORM], function() {
        Lookup.getPrintTemplateCustoms(
          function(ptCustoms) {
            self._ptCustoms = ptCustoms;
            Ajax.get(Ajax.SVR_MSG, 'getSessionStubs', self.cid, [self._preview, self]);
          });
      });
    }
  },
  _preview:function(stubs) {
    this._setStubs(stubs);
    this._buildDoc(this.sid);
    Html.Window.working(false);
    if (this._zoom) {
      Pop.zoom('pop-dv');
    } else {
      Pop.show('pop-dv');
    }
    this._previewing = true;
  },
  pvClose:function() {
    this._previewing = false;
    Pop.close();
  },
  pvPrint:function() {
    DocDownloader.toWord(this.session, _$("pop-dv-body").children, this.session.updatedBy, this._ptCustoms);
  },
  pvPrev:function() {
    this._buildDoc(this.nav.prev);
  },
  pvNext:function() {
    this._buildDoc(this.nav.next);
  },
  pvEdit:function() {
    Pop.close();
    this.openConsole(this.sid);
  },
  pvReplicate:function() {
    DocOpener.popReplicate(this.stubs[this.nav.on]);
  },
  openConsole:function(sid) {
    Page.popConsole(sid);
  },
  _setStubs:function(stubs) {
    this.stubs = stubs;
    if (stubs) {
      var last = null;
      for (var id in stubs) {
        if (this.sid == null) {
          this.sid = id;
        }
        var s = stubs[id];
        if (last) {
          last.prev = s.id;
          s.next = last.id;
        } else {
          s.next = null;
        }
        last = s;
      }
      last.prev = null;
    }
  },
  _buildDoc:function(sid) {
    this.sid = sid;
    this.session = this.stubs[sid];
    this._buildNav(sid);
    _$('pop-dv-body').html('').className = 'working-circle';
    Ajax.get(Ajax.SVR_POP, 'getDocView', {'sid':sid}, 
      function(dv) {
        if (dv.html) {
          _$('pop-dv-body').html(dv.html).className = '';
        } else {
          _$('pop-dv-body', "We're sorry).html(but a preview of this document is not available.").className = 'no-preview';
        }
      });
  },
  _buildNav:function(sid) {
    var stub = this.stubs[sid];
    this.nav = {
      'on':sid, 
      'prev':stub.prev, 
      'next':stub.next};
    _$('dv-nav-on-div').html(this._buildNavHtml(sid));
    if (this.nav.prev) {
      _$('dv-nav-prev').style.visibility = '';
      _$('dv-nav-prev-div').html(this._buildNavHtml(this.nav.prev));
    } else {
      _$('dv-nav-prev').style.visibility = 'hidden';
    }  
    if (this.nav.next) {
      _$('dv-nav-next').style.visibility = '';
      _$('dv-nav-next-div').html(this._buildNavHtml(this.nav.next));
    } else {
      _$('dv-nav-next').style.visibility = 'hidden';
    }
  },
  _buildNavHtml:function(sid) {
    var stub = this.stubs[sid];
    return stub.label + '<br/><span>(' + stub.date + ')</span>'; 
  },
  _rnStub:null,  
  _rnScb:null,
  _ovfs:null,
  /*
   * Replicate pop
   * - s: JSession or JSessionStub
   * - callback: optional callback(JSession)
  */
  popReplicate:function(s, callback) {
    Html.Window.working(true);
    this._rnStub = s;
    this._rnScb = Ajax.buildScopedCallback(callback);
    var self = this;
    Includer.get([Includer.HTML_DOC_OPENER], function() {
      Ajax.get(Ajax.SVR_POP, 'getReplicateDefs', null, 
        function(defs) {
          createOpts('pop-rn-sendtos', defs.st);
          Html.InputText.$('rn-dos').setValue(DateUi.getToday());
          Html.InputCheck.$('rn-ovfs').setCheck(defs.ovfs);
          self._ovfs = isChecked('rn-ovfs'); 
          Html.Window.working(false);
          Pop.show('pop-rn');
        });
    })
  },
  rnDefaultSendTo:function() {
    Lookup.saveDefaultSendTo(Html.Select.$('pop-rn-sendtos').getValue());
    Pop.Msg.showInfo("Default set.");
  },
  rnCreate:function() {
    //Page.workingCmd(true);
    overlayWorking(true);
    var ovfs = isChecked('rn-ovfs');
    if (ovfs != this._ovfs) {
      Lookup.saveReplicateOverrideFs(ovfs);
    }
    var sk = {
      'tid':this._rnStub.tid, 
      'cid':this._rnStub.cid, 
      'kid':null, 
      'dos':Html.InputText.$('rn-dos').getValue(), 
      'tpid':null, 
      'st':Html.Select.$('pop-rn-sendtos').getValue(),
      'sid':this._rnStub.id, 
      'ovfs':ovfs};
    Ajax.post(Ajax.SVR_SCHED, 'newSession', sk, this);
  },
  newSessionCallback:function(session) {
    overlayWorking(false);
    Pop.close();
    DocOpener.openConsole(session.id);
    return;
    if (this._previewing) {
      Pop.close();   
    }
    if (this._rnScb) {
      Ajax.callScopedCallback(this._rnScb, session);      
    } else {
      DocOpener.openConsole(session.id);
    }
  },
  nnCid:null,
  nnKid:null,
  nnStandards:null,
  nnTid:null,
  /*
   * New doc pop
   * - clientId, clientName: required
   * - schedId: optional
   * - dos: optional (default today)
  */
  popNew:function(clientId, clientName, schedId, dos) {
    Html.Window.working(true);
    var self = this;
    Includer.get([Includer.HTML_DOC_OPENER], function() {
      _$("nn-client-name").setText(clientName);
      Html.InputText.$("nn-dos").setValue(dos ? dos : DateUi.getToday());
      Ajax.get(Ajax.SVR_POP, 'getNewNotePopInfo', {'cid':clientId}, 
        function(pop) {
          createOpts("pop-nn-templates", pop.templates);
          createOpts("pop-nn-sendtos", pop.sendTos);
          var sel = createOpts("pop-nn-presets", pop.presets);
          var a = _$("pop-nn-start-custom");
          if (sel.options.length == 0) {
            a.style.visibility = "hidden";
          } else {
            a.style.visibility = "";
          }
          self.nnCid = clientId;
          self.nnKid = schedId;
          self.nnStandards = pop.standards;
          self.nnTid = null;
          self.onTemplateChange();
          Html.Window.working(false);
          Pop.show("pop-nn");
        });
    });
  },
  nnSetTemplateDefault:function() {
    Ajax.post(Ajax.SVR_LOOKUP, 'saveDefaultTemplate', {'id':'','value':Html.Select.$('pop-nn-templates').getValue()}, function() {
      Pop.Msg.showInfo('Default template set.');
    });
  },
  nnSetSendToDefault:function(id) {
    Ajax.post(Ajax.SVR_LOOKUP, 'saveDefaultSendTo', {'id':'','value':Html.Select.$(id).getValue()}, function() {
      Pop.Msg.showInfo('Default send to set.');
    });
  },
  nnCreateEmptySession:function() {
    this._createNewSession(Html.Select.$("pop-nn-templates").getValue(), null, null);
  },
  nnCreateStandardSession:function() {
    this._createNewSession(Html.Select.$("pop-nn-templates").getValue(), null, this.nnStandardSid);
  },
  nnCreatePrefilledSession:function() {
    this._createNewSession(null, Html.Select.$("pop-nn-presets").getValue(), null);
  },
  onTemplateChange:function() {
    if (Html.Select.$("pop-nn-templates").getValue() == this.nnTid) return;
    this.nnTid = _$("pop-nn-templates").getValue();
    var tname = _$("pop-nn-templates").getText();
    _$("pop-nn-start-empty").setText("Blank " + tname);
    var a = _$("pop-nn-replicate");
    var std = this.nnStandards[this.nnTid];
    if (std == null || (me.Role.Artifact && ! me.Role.Artifact.noteReplicate)) {
      a.style.visibility = "hidden";
      this.nnStandardSid = null;
    } else {
      a.style.visibility = "";
      _$("pop-nn-replicate-span").setText(std.label + " DOS: " + std.dos);
      this.nnStandardSid = std.id;
    }
    if (me.tab) {
      a = _$('pop-nn-start-empty');
      a.href = Page.url('new-console.php', this._sessionCreateParms(this.nnTid, null, null));
      a.onclick = null;
      a.target = '_blank';
      a = _$('pop-nn-replicate');
      a.href = Page.url('new-console.php', this._sessionCreateParms(this.nnTid, null, this.nnStandardSid));
      a.onclick = null;
      a.target = '_blank';
      a = _$('pop-nn-start-custom');
      a.href = Page.url('new-console.php', this._sessionCreateParms(null, Html.Select.$("pop-nn-presets").getValue()));
      a.onclick = null;
      a.target = '_blank';
    }
  },
  onPresetChange:function() {
    if (me.tab) {
      var a = _$('pop-nn-start-custom');
      a.href = Page.url('new-console.php', this._sessionCreateParms(null, Html.Select.$("pop-nn-presets").getValue()));
      a.onclick = null;
      a.target = '_blank';
      //alert(Html.Select.$("pop-nn-presets").getValue());
    }
  },
  _sessionCreateParms:function(tid, tpid, sid) {
    return {
      'create':1,
      'tid':tid, 
      'cid':this.nnCid, 
      'kid':this.nnKid, 
      'dos':Html.InputText.$('nn-dos').getValue(), 
      'tpid':tpid, 
      'st':Html.Select.$('pop-nn-sendtos').getValue(),
      'sid':sid, 
      'ovfs':false};
  },
  _createNewSession:function(tid, tpid, sid) {
    Pop.close();
    Pop.Working.show("Creating Document");
    var sk = this._sessionCreateParms(tid, tpid, sid);
    Ajax.post(Ajax.SVR_SCHED, 'newSession', sk, 
      function(session) {
        Pop.Working.close();
        DocOpener.openConsole(session.id);
      });
  }
};
/* ui.js remnants */
