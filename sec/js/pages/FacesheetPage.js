var SOURCE_NEWCROP = 1;
/**
 * Facesheet
 * Global static
 * Instance assigned to global variable: page
 * @author Warren Hornsby
 */
var FacesheetPage = page = {
  cid:null,
  fs:null,
  //
  working:Html.Window.working.bind(Html.Window),
  load:function(query) {
    if (! me.Role.Patient.cds)
      _$('hm').hide();
    Page.setEvents();
    var as = _$('bodyContent').getTagsByClass('A', 'fscap');
    for (var i = 0; i < as.length; i++) 
      as[i].hideFocus = 'hideFocus';
    this.cid = query.id;
    Page.sessid = query.sess;
    if (! me.active)
      this.setInact();
    DocTile.create(_$('DocTile'));
    ProcTile.create(_$('ProcTile'));
    DiagTile.create(_$('DiagTile'));
    MedTile.create(_$('MedTile'));
    CdsTile.create(_$('CdsTile'));
   // ****** crs 6/29/2016 PortalTile.create(_$('PortalTile'));
   // ****** crs 6/29/2016 BillingTile.create(_$('BillingTile'));
    if (this.cid == null)
      Page.Nav.goPatients();
    Html.Curtain.show();
    page._getFacesheet(query);
  },
  setInact:function() {
    page.pNewAppt = page.inact;
    page.pAppt = page.inact;
    page.pPopVitals = page.inact;
    page.pNewDoc = page.inact;
    page.pNewMsg = page.inact;
    AllerTile.pop = page.inact;
    MedTile.pop = page.inact;
    DemoTile.pop = page.inact;
    NotepadTile.pop = page.inact;
    DiagTile.pop = page.inact;
    ImmunTile.pop = page.inact;
    TrackTile.pop = page.inact;
    HxTile.pop = page.inact;
    CdsTile.pop = page.inact;
    PortalTile.pop = page.inact;
    BillingTile.pop = page.inact;
    DocTile.pop = page.inact;
    VitalTile.pop = page.inact;
    //Includer.getMsgPreviewer_pop = page.inact;
    Includer.getDocOpener_preview = page.inact;
  },
  inact:function() {
    showCritical('This function is only available for active accounts.');
  },
  onFocus:function() {
    var fs = this.fs;
    if (fs) {
      fs.polling().start();
      if (! Pop.isActive())  
        fs.ajax().refetchIfChanged(this._loadFacesheet.bind(this));
      else
        if (Pop._pop.id == 'pop-nn') {
          Pop.close();
          this.onFocus();
        }
    }
  },
  onBlur:function() {
    if (this.fs)
      this.fs.polling().stop();
  },
  /*
   * Refresh UI after facesheet change
   */
  refreshFacesheet:function(fs) {
    if (fs == null)
      page.fs.ajax(Html.Window).refetch(page._loadFacesheet.bind(page));
    else 
      page._loadFacesheet(fs);
  },
  /*
   * UI interactions
   */
  pRefresh:function() {
    Html.Window.working(true);
    page._getFacesheet();
  },
  pPopNotepad:function() {
    NotepadTile.pop();
  },
  pPopDemo:function() {
    DemoTile.pop();
  },
  pPopBilling:function() {
    BillingTile.pop();
  },
  pPopMeds:function() {
    MedTile.pop();
  },
  pNewCrop:function(dest, callback) {
    if (me.trial) {
      Pop.Msg.showCritical('This function is not available for trial users.');
      return;
    }
    var self = this;
    Html.Window.working(true);
    NewCrop.validate(function() {
      NewCrop.sendFromFacesheet(dest, page.acceptNewCropRefresh.curry(callback));
    })
  },
  pNewCrop_compose:function(callback) {
    this.pNewCrop(null, callback);
  },
  requestNewCropRefresh:function() {
    Pop.Working.show('Refreshing medication list...');
    NewCrop.refreshFromNewCrop(this.fs.clientId, null, true, true, function(fs) {
      Pop.Working.close();
      page.acceptNewCropRefresh(null, fs);
    })
  },
  acceptNewCropRefresh:function(callback, f) {
    page.fs.refreshNewCrop(f);
    if (callback)
      callback(f);
    else
      page.refreshFacesheet(page.fs);
  },
  pDownload:function() {
    if (this.fs && this.fs.client) {
      DownloadChoices.pop(this.fs.client.clientId);
    }
  },
  pPopHm:function() {
    CdsTile.pop();
  },
  pPopPortal:function() {
    PortalTile.pop();
  },
  pPopProcs:function() {
    ProcTile.pop();
  },
  pPopVitals:function(fvid) {
    VitalTile.pop(fvid);
  },
  pPopAllergies:function() {
    AllerTile.pop();
  },
  pPopDiagnoses:function() {
    DiagTile.pop();
  },
  pPopDocHx:function() {
    DocTile.pop();
  },
  pPopImmun:function() {
    ImmunTile.pop();
  },
  pPopTrack:function() {
    TrackTile.pop();
  },
  pPopMedSurgHx:function() {
    HxTile.pop(FaceUi.FACE_HX_TAB_MED);
  },
  pPopFamHx:function() {
    HxTile.pop(FaceUi.FACE_HX_TAB_FAM);
  },
  pPopSocHx:function() {
    HxTile.pop(FaceUi.FACE_HX_TAB_SOC);
  },
  pEditDoc:function(sid) {
    Includer.getDocOpener_open(sid);
  },
  pPreviewDoc:function(stub) {
    DocStubPreviewPop.pop(stub, page.fs.docstubs.getOpenSessions());
  },
  pNewDoc:function() {
    var c = page.fs.client;
    Includer.getDocOpener_new(c.clientId, c.name);
  },
  pNewAppt:function() {
    Page.Nav.goSchedNew(this.fs.client.clientId);
  },
  pNewTestProc:function() {
    DocHistoryPop.pop_asNewTestProc(page.fs);
  },
  pAppt:function(id) {
    Page.Nav.goSchedPop(id);
  },
  pNewMsg:function() {
    Page.Nav.goMessageNew(page.fs.client.clientId);
  },
  pNewMsgPortal:function() {
    Page.Nav.goMessageNewPortal(page.fs.client.clientId);
  },
  //
  _getFacesheet:function(query) {
    var self = this;
    Facesheet.ajax().fetch(this.cid, function(fs) {
      self._loadFacesheet(fs);
      Html.Window.working(false);
      Html.Curtain.hide();
      NewCrop.loadFromFacesheet(me.Role.erx, fs.client);
      if (query && query.pe)  // pop edit demo
        DemoTile.pop();
      else if (query && query.nc)  // NewCrop
        NewCrop.sendFromFacesheet(query.nc);
    })
  },
  _loadFacesheet:function(fs) {
    fs = (fs) ? fs : this.fs;
    fs.polling().start();
    this.fs = fs.bubble('onstale', this.fs_onstale);
    this.fs.activeLegacy = false;
    this.fs.activeErx = false;
    this._drawFacesheet();
  },
  _drawFacesheet:function() {
    var fs = this.fs;
    Page.setTitle(fs.client.name);
    _$('h1-name').setText(fs.client.name);
    DemoTile.load(fs);
    NotepadTile.load(fs);
    // crs ****** 6/29/2016 WorkflowTile.load(fs);
    AllerTile.load(fs);
    DiagTile.load(fs).bubble('ondraw', page._resizeDivs);
    MedTile.load(fs).bubble('ondraw', page._resizeDivs);
    HxTile.load(fs);
    CdsTile.load(fs).bubble('onupdate', page.refreshFacesheet);
    //  ****** crs 6/29/2016 PortalTile.load(fs).bubble('onupdate', page.refreshFacesheet);
    DocTile.load(fs).bubble('onupdate', page.refreshFacesheet);
    ProcTile.load(fs).bubble('onupdate', page.refreshFacesheet);
    VitalTile.load(fs);
    ImmunTile.load(fs);
   // crs ****** 6/29/2016 TrackTile.load(fs);
    DocStubPreviewPop.setOnUpdate(page.refreshFacesheet);
    this._resizeDivs();
    Polling.StaleFacesheet.start(fs);
    _$('fs-refresh').hide();
    //_$('legacy-sticky').showIf(me.Role.erx && fs.activeLegacy);
    if (me.Role.erx && fs.medsNcStale) {
      fs.medsNcStale = false;
      this.requestNewCropRefresh();
    }
  },
  fs_onstale:function() {
    _$('fs-refresh').show();
  },
  _resizeDivs:function() {
    syncHeights([MedTile.table, DiagTile.table]);
   // ****** crs 6/29/2016  syncHeights(['medhx-box', 'famhx-box', 'sochx-box']);
    syncHeights(['medhx-box', 'famhx-box']);
   // ****** crs 6/29/2016  syncHeights(['imm-box', 'track-box']);
   // ****** crs 6/29/2016  syncHeights(['proc-box', 'portal-box', 'bill-box']);
  }
};
/**
 * Tiles
 */
var DemoTile = {
  load:function(fs) {
    var c = fs.client;
    if (c.img) { 
      _$('empty-photo').invisible();
      _$('photo').show().src = 'face-image.php?id=' + c.img;
      _$('photo').setPosition('absolute');
      //Html.Animator.pulse(_$('photo'), 2, 0.1);
    } else {
      _$('empty-photo').visible().setClass(c.sex == 'F' ? 'bfemale' : 'bmale');
      _$('photo').hide();
    }
    _$('dem-cid').setText(c.uid);
    _$('dem-dob').setText(c.birth);
    _$('dem-age').setText(c.age + ' ' + c._sex.toLowerCase());
    _$('dem-lbl-flags').setText('');
    _$('dem-flags').setText('');
    var parents = [];
    if (c.Address_Father && c.Address_Father.name) parents.push(c.Address_Father.name);
    if (c.Address_Mother && c.Address_Mother.name) parents.push(c.Address_Mother.name);
    if (parents.length) {
      _$('dem-lbl-flags').setText('Parent(s):');
      _$('dem-flags').html(bulletJoin(parents)).setClass('ro');
    }    
    if (c.livingWill || c.poa) {
      var flags = [];
      if (c.livingWill) flags.push('Living Will');
      if (c.poa) flags.push('Power of Attorney');
      _$('dem-lbl-flags').setText('On File:');
      _$('dem-flags').html(bulletJoin(flags)).setClass('ro red');
    }
    if (c._dnr2) {
      _$('dem-lbl-dnr').setText('Code Status:');
      _$('dem-dnr').html(c._dnr2).setClass('ro red');      
    }
    //$('portrait').className = (c.sex == 'F') ? 'pf' : 'pm';
    var pl = new ProfileLoader('dem-lbl-addr', 'dem-addr');
    var a = c.Address_Home;
    pl.add('Address:', bulletJoin([a.addr1, a.addr2, a.csz], true));
    pl = Map.combine([pl, {
      phones:[],
      phoneLabel:'Phone(s):',
      addPhoneBy:function(label, addr) {
        if (addr)
          if (addr.phone1) 
            this.addPhone(addr.phone1 + ' (' + label + ')');
          if (addr.phone2) 
            this.addPhone(addr.phone2 + ' (' + label + ')');
          if (addr.phone3) 
            this.addPhone(addr.phone3 + ' (' + label + ')');
      },
      addPhone:function(text) {
        this.phones.push(text);
        if (this.phones.length > 1)
          this.writePhone();
      },
      writePhone:function() {
        if (this.phones.length) {
          this.add(this.phoneLabel, bulletJoin(this.phones, true));
          this.phones = [];
          this.phoneLabel = null;
        }
      }
    }]);
    pl.addPhone(AddressUi.formatPhone(a.phone1, a.phone1Type));
    if (a.phone2) 
      pl.addPhone(AddressUi.formatPhone(a.phone2, a.phone2Type));
    pl.addPhoneBy('Emer', c.Address_Emergency);
    pl.addPhoneBy('Father', c.Address_Father);
    pl.addPhoneBy('Mother', c.Address_Mother);
    pl.addPhoneBy('Spouse', c.Address_Spouse);
    pl.addPhoneBy('RX', c.Address_Rx);
    pl.writePhone();
  },
  _addPhone:function(pl, label, addr) {
    if (addr && addr.phone1)
      pl.add(label, AddressUi.formatPhone(addr.phone1, addr.phone1Type));
  },
  pop:function() {
    Includer.getPatientEditor_pop(page.fs.client, null, 
      function(client) {
        page.refreshFacesheet();
      });
  }
}
var NotepadTile = {
  load:function(fs) {
    if (fs.client.notes) {
      _$('notepad').setClass('full');
      _$('notepad-empty').hide();
      _$('notepad-text').show().html(fs.client.notes);
    } else {
      _$('notepad').setClass('');
      _$('notepad-empty').show();
      _$('notepad-text').hide();
    }
  },
  pop:function() {
    if (! me.Role.Patient.demo)
      return;
    var fs = page.fs;
    var input = Html.InputText.$('pop-cn-text').noSelectFocus();
    if (fs.client.notes) {
      input.setValue(String.brToCrlf(fs.client.notes));
    } else {
      input.setValue("");
    }
    Pop.show("pop-cn", "pop-cn-text");
  },
  pSave:function() {
    var fs = page.fs;
    var text = _$("pop-cn-text").getValue();
    if (String.isBlank(text)) {
      text = null; 
    } else {
      text = String.crlfToBr(text);
    }
    Pop.close();
    Html.Window.working(true);
    Ajax.Facesheet.Patients.saveNotes(fs.client.clientId, text, function(f) {
      Html.Window.working(false);
      page.refreshFacesheet(fs.refreshClient(f));
    })
  },
  pDelete:function() {
    setValue("pop-cn-text", "").select();
  }
}
var WorkflowTile = {
  load:function(fs) {
    this.renderWfVital(fs);
    this.renderWfAppt(fs);
    this.renderWfDocs(fs);
  },
  renderWfVital:function(fs) {
    var div = _$('wf-vit').clean();
    var a;
    if (fs.workflow.vital) {
      a = Html.Anchor.create('qcmd qvital').html('Vitals (' + bulletJoin(fs.workflow.vital.all) + ')').into(div);
      a.onclick = page.pPopVitals.bind(page, fs.workflow.vital.dataVitalsid);
    } else {
      a = Html.Anchor.create('qcmd qnew-vital', "Record Today's Vitals...", page.pPopVitals.bind(page)).into(div);
    }
    if (a.getWidth() > 400) 
      a.setWidth(400);
  },
  renderWfAppt:function(fs) {
    var a;
    var href;
    var div = _$('wf-appt').clean();
    if (fs.workflow.appt) {
      var e = fs.workflow.appt;
      a = Html.Anchor.create('qcmd qappt', e._date + ' @' + e.name, page.pAppt.bind(page, e.schedId)).into(div);
    } else {
      a = Html.Anchor.create('qcmd qnew-appt2', "New Appointment...", page.pNewAppt.bind(page)).into(div);
      a.id = 'qAppt';
    }
  },
  renderWfDocs:function(fs) {
    var a;
    var href;
    var ul = _$('wf-doc-ul').clean();
    var li = ul.li('pl');
    if (me.Role.Patient.history)
      li.appendChild(Html.Anchor.create('qcmd qnew-proc', 'New Test/Procedure...', page.pNewTestProc));
    if (me.Role.Message && me.Role.Message.patient) {
      li.appendChild(Html.Anchor.create('qcmd qnew-msg3', 'Office Message...', page.pNewMsg));
      li.appendChild(Html.Anchor.create('qcmd qnew-msg3', 'Portal...', page.pNewMsgPortal));
    }
    li = ul.li();
    var docs = fs.docstubs.getOpenSessions();
    if (docs) {
      for (var i = 0; i < docs.length && i < 2; i++) {
        var e = docs[i];
        a = Html.Anchor.create('qnote').html(e.name + '<br><span>(' + e.date + ')</span>');
        a.onclick = page.pPreviewDoc.bind(page, e);
        //if (me.ui.tablet) {
        //  a.href = Page.url('new-console.php', {'sid':e.id});
        //  a.target = '_blank';
        //} else {
        //  a.onclick = page.pEditDoc.bind(page, e.id);
        //}
        li.appendChild(a);
      }
    }
    if (me.Role.Artifact && me.Role.Artifact.noteCreate) {
      a = Html.Anchor.create('qnewnote').html('Create<br>New Note...');
      a.onclick = page.pNewDoc.bind(page);
      li.appendChild(a);
    }
  }
}
var AllerTile = {
  load:function(fs) {
    _$('all-tbl').style.border = (fs.activeAllers && fs.activeAllers.length) ? '2px solid red' : '';
    var t = new TableLoader('all-tbody', 'off', 'all-div');
    var self = this;
    var click = function(){self.pop()};
    if (fs.activeAllers.length) {
      for (var i = 0; i < fs.activeAllers.length; i++) {
        var allergy = fs.allergies[fs.activeAllers[i]];
        var cls = 'fs aller';
        if (me.isErx() && allergy.source != SOURCE_NEWCROP) {
          fs.activeLegacy = true;
          //cls = 'fs legacy';
        }
        if (allergy.source == SOURCE_NEWCROP) {
          fs.activeErx = true;
        }
        var html = bulletJoin(allergy.reactions);
        t.createTrTd();
        //t.append(createAnchor(null, null, cls, allergy.agent, null, click), createSpan('lpad', null, null, html));
        t.append(Html.Anchor.create(cls, allergy.agent, click), Html.Span.create('lpad').html(html));
      }
    } else {
      t.createTrTd();
      t.tr.className = '';
      //t.append(createAnchor(null, null, 'fsnone', '(None Known)', null, click));
      t.append(Html.Anchor.create('fsnone', '(None Known)', click));
    }
  },
  pop:function() {
    FaceAllergies.pop(page.fs, true, [page.refreshFacesheet, page]);
  }
}
var ImmunTile = {
  load:function(fs) {
    _$('imm-sum').html(this.sumAnchor(fs.immuns));
  },
  pop:function(tab) {
    FaceImmun.pop(page.fs, true, [page.refreshFacesheet, page], tab);
  },
  sumAnchor:function(immuns) {
    var t = [];
    var due = page.fs.immunCd && page.fs.immunCd.getDueNows();
    if (due)
      t.push("<a class='fs0 red' href='javascript:ImmunTile.pop(2)'>Due Now: " + bulletJoin(due) + "</a>");
    else if (Array.isEmpty(immuns)) 
      return null;
    var a = [];
    for (var i = 0; i < immuns.length; i++) {
      var imm = immuns[i];
      a.push(imm.name);
    }
    t.unshift("<a class='fs0' href='javascript:ImmunTile.pop()'>" + bulletJoin(a) + "</a>");
    return bulletJoin(t);
  }
}
var TrackTile = {
  load:function(fs) {
    _$('trk-sum').html(this.sumAnchor(fs.tracking));
  },
  pop:function() {
    Includer.getFaceTrack_pop(page.fs, true, [page.refreshFacesheet, page]);    
  },
  sumAnchor:function(trackItems) {
    if (Array.isEmpty(trackItems)) 
      return null;
    var stat = [];
    var rest = [];
    for (var i = 0; i < trackItems.length; i++) {
      var item = trackItems[i];
      if (item.priority == C_TrackItem.PRIORITY_STAT)
        stat.push(item.trackDesc);
      else
        rest.push(item.trackDesc);
    }
    var h = [];
    if (stat.length > 0)
      h.push("<a class='fstat' href='javascript:TrackTile.pop()'>" + bulletJoin(stat) + "</a>");
    if (rest.length > 0)
      h.push("<a class='f0' href='javascript:TrackTile.pop()'>" + bulletJoin(rest) + "</a>");
    return bulletJoin(h);
  }
}
var HxTile = {
  load:function(fs) {
    this.loadMedHx(fs);
    this.loadFamHx(fs);
    this.loadSocHx(fs);
  },
  loadMedHx:function(fs) {
    var as = [];
    as.pushIfNotNull(this.hxSumProcsAnchor(fs.medhx.recs, FaceUi.FACE_HX_TAB_MED));
    as.pushIfNotNull(this.hxSumProcsAnchor(Map.from(fs.surgs, '_name'), FaceUi.FACE_HX_TAB_SURG));
    if (as.length > 0)
      _$('fshx-sum').html(bulletJoin(as));
  },
  hxSumProcsAnchor:function(recs, tab) {
    var procs = [];
    for (var proc in recs) {
      procs.push(proc);
    }
    return this.hxSumAnchor(procs, tab);
  }, 
  hxSumAnchor:function(a, tab) {
    if (Array.isEmpty(a)) return null;
    return "<a class='fs0' href='javascript:HxTile.pop(" + tab + ")'>" + bulletJoin(a) + '</a>';  
  },
  loadFamHx:function(fs) {
    this.renderFamhxSummary(fs);
  },
  renderFamhxSummary:function(fs) {
    _$('famhx-sum').html(this.hxSumAnchor(fs.famhx.sopts, FaceUi.FACE_HX_TAB_FAM));
  },
  loadSocHx:function(fs) {
    var topics = [];  // topics that have values
    for (var topic in fs.sochx) {
      var tpvals = [];  // ['bullet-rec-values',..]
      var rec = fs.sochx[topic];
      var dsyncCombo = FaceUi.getDsyncCombo(topic);
      for (var dsyncId in rec) {
        var values = rec[dsyncId].v;
        dsyncCombo = rec[dsyncId].d;
        if (dsyncCombo) {
          tpvals.push(bulletJoin(values) + '<br>');
        } else {
          tpvals.push(bulletJoin(values));
        }
      }
      var s = bulletJoin(tpvals, true);
      if (s != '') 
        topics.push(topic);
    }
    if (topics.length) {
      var a = "<a class='fs0' href='javascript:HxTile.pop(FaceUi.FACE_HX_TAB_SOC)'>" + bulletJoin(topics) + '</a>';
      _$('sochx-sum').html(a);
    }    
  },
  pop:function(tab) {
    FaceHx.pop(page.fs, tab, true, [page.refreshFacesheet, page]);
  }
}
var VitalTile = {
  load:function(fs) {
    var self = this;
    var t = new TableLoader('vit-tbody', 'off', 'vit-div');
    if (fs.vitals) {
      for (var i = 0; i < fs.vitals.length; i++) {
        var vital = fs.vitals[i];
        var click = page.pPopVitals.curry(vital.dataVitalsId);
        t.createTrTd();
        t.append(Html.Anchor.create('fs vital', vital.date, click));
        if (vital.all) {
          t.append(Html.Div.create('vit-text').html(bulletJoin(vital.all)));
        }
      }
    }
  },
  pop:function(fvid) {
    //Includer.getFaceVitals_pop(page.fs, fvid, true, [page.refreshFacesheet, page]);
    FaceVitals.pop(page.fs, fvid, true, [page.refreshFacesheet, page]);
  }
}
/**
 * TableLoader FaceTable
 */
FaceTable = {
  create:function(container, tableCls, wrapCls) {
    return Html.TableLoader.create(container, tableCls, wrapCls || 'fstab noscroll').extend(function(self) {
      return {
        onselect:function(rec) {},
        add:function(rec, tr) {},
        //
        init:function() {
          self.noWorking();
          self.spacer = self.wrapper.append(Html.Div.create().setHeight(20));
        },
        reset:self.reset.prepend(function() {
          self.spacer.show();
        }),
        load:function(recs) {
          if (! self.prepended) 
            self.prepended = self.ondraw = self.ondraw.prepend(function() {
              self.spacer.hide();
            })
          self.reset();
          self.recs = recs;
          self.draw();
          if (Array.isEmpty(recs)) 
            self.addNone();
        },
        addNone:function() {
          self.tbody().tr().td(Html.Anchor.create('fsnone', '(None Recorded)').bubble('onclick', self.onselect));
        }
      }
    })
  }
}
/**
 * Tile DiagTile
 *   FaceTable table
 */
DiagTile = {
  create:function(container) {
    var My = this;
    return DiagTile = Html.Tile.create(container.clean()).extend(function(self) {
      return {
        ondraw:function() {},
        //
        init:function() {
          self.table = My.Table.create(self).bubble('onselect', self.table_onselect).bubble('ondraw', self);
          self.cmdtile = Html.Tile.create(self, 'cmdtile')
            .add(Html.Anchor.create('icdi', 'Lookup ICD9...', Header.icdLook))
            .add(Html.Anchor.create('ml5 icdi', 'Lookup ICD10...', Header.icdLook10))
            .hide();
        },
        load:function(fs) {
          self.recs = fs.diagnoses.actives();
          self.table.load(self.recs);
          self.cmdtile.showIf(me.Role.erx || me.trial);
          return self;
        },
        pop:function(rec) {
          FaceDiagnoses.pop(page.fs, true, [page.refreshFacesheet, page]);
        },
        //
        table_onselect:function(rec) {
          self.pop(rec);
        }
      }
    })
  },
  Table:{
    create:function(container) {
      return FaceTable.create(container, 'fsy').extend(function(self) {
        return {
          onselect:function(rec) {},
          //
          add:function(rec, tr) {
            if (rec.active) {
              var a;
              if (rec.icd)
                a = InfoButton.forDiag(rec.icd, rec.clientId);
              tr.select(Html.Anchor.create('fs', rec._name), rec, null, 'wrap')
                .td(a);
            }
          }
        }
      })
    }
  }
}
/**
 * Tile MedTile
 *   FaceTable table
 */
MedTile = {
  create:function(container) {
    var My = this;
    return MedTile = Html.Tile.create(container.clean()).extend(function(self) {
      return {
        ondraw:function() {},
        //
        init:function() {
          self.table = My.Table.create(self).noWorking().bubble('onselect', self.table_onselect).bubble('ondraw', self);
          self.cmdtile = Html.Tile.create(self, 'cmdtile').add(Html.Anchor.create('erxi', 'Prescribe...', page.pNewCrop)).hide();
        },
        load:function(fs) {
          self.recs = fs.activeMeds;
          self.table.load(self.recs);
   // ****** crs 6/29/2016       self.cmdtile.showIf(me.Role.erx || me.trial);
          return self;
        },
        pop:function(rec) {
          FaceMeds.pop(page.fs, true, [page.refreshFacesheet, page]);
        },
        //
        table_onselect:function(rec) {
          self.pop(rec);
        }
      }
    })
  },
  Table:{
    create:function(container) {
      return FaceTable.create(container, 'fsb').extend(function(self) {
        return {
          onselect:function(rec) {},
          //
          add:function(rec, tr) {
            if (rec._none) {
              tr.select(Html.Anchor.create('fs', rec._name));
            } else {
              var cls = 'fs';
              if (rec.source == SOURCE_NEWCROP) {
                page.fs.activeErx = true;
                if (rec._diet) 
                  cls = 'fs diet';
              } else if (me.isErx()) {
                page.fs.activeLegacy = true;
                //cls = 'fs legacy';
              }
              var name = rec.name;  // (! rec.expires) ? rec.name : bulletJoin([rec.name,' <span>' + rec.expireText + '</span>']);
              tr.select(Html.Anchor.create(cls, name));
              Html.Span.create('lpad', rec.text).into(tr._cell);
              tr._cell.className = '';
              var a;
              if (rec.source == SOURCE_NEWCROP) {
                a = InfoButton.forMed(rec.index, rec.clientId);
              }
              tr.td(a);
            }
          }
        }
      })
    }
  }
}
/**
 * Tile ProcTile
 */
ProcTile = {
  create:function(parent) {
    return ProcTile = Html.Tile.create(parent.clean()).extend(function(self) {
      return {
        onupdate:function(fs) {},
        //
        load:function(fs) {
          self.fs = fs;
          // TODO
          return self;
        },
        pop:function() {
          ProcsPop.pop(self.fs).bubble('onupdate', self);
        }
      }
    })
  }
}
/**
 * Tile DocTile
 *   TableLoader table   
 */
DocTile = {
  create:function(container) {
    var My = this;
    return DocTile = Html.Tile.create(container.clean()).extend(function(self) {
      return {
        onupdate:function() {},
        //
        init:function() {
          self.table = My.Table.create(self).noWorking().bubble('onselect', self.table_onselect);
        },
        load:function(fs) {
          self.fs = fs;
          self.recs = fs.docstubs;
          self.table.set('fs', fs).load(self.recs);
          return self;
        },
        pop:function(rec) {
          DocHistoryPop.pop(self.fs, rec).bubble('onupdate', self);
        },
        //
        table_onselect:function(rec) {
          self.pop(rec);
        }
      }
    })
  },
  Table:{
    create:function(container) {
      return Html.TableLoader.create(container, 'fsgr single').extend(function(self) {
        return {
          onselect:function(rec) {},
          //
          rowBreaks:function(rec) {
            return [rec.date];
          },
          add:function(rec, tr) {
            var a;
            if (rec._loinc) {
              a = InfoButton.forLab(rec._loinc, rec.clientId);
            }            
            tr.td(rec.date, 'bold').w('20%')
              .td(rec._type).w('20%')
              .select(AnchorDocStub).w('60%')
              .td(a);
          }
        }
      })
    }
  }
}
/**
 * Tile CdsTile
 *   FaceTable table   
 */
CdsTile = {
  create:function(container) {
    var My = this;
    return CdsTile = Html.Tile.create(container.clean()).extend(function(self) {
      return {
        onupdate:function() {},
        //
        init:function() {
          self.table = My.Table.create(self).noWorking().bubble('onchoose', self.table_onchoose);
        },
        load:function(fs) {
          self.fs = fs;
          self.recs = fs.hms.actives();
          self.table.load(self.recs);
          return self;
        },
        pop:function(rec) {
          CdsPop.pop(self.fs, rec).bubble('onupdate', self);
        },
        //
        table_onchoose:function(rec) {
          self.pop(rec);
        }
      }
    })
  },
  Table:{
    create:function(container) {
      return FaceTable.create(container, 'fsp single', 'fstab noscroll').extend(function(self) {
        return {
          init:function() {
            self.thead().tr('head').th('Item').w('30%').th('Frequency').w('20%').th('Last Recorded').w('50%');
          },
          onchoose:function(rec) {},         
          //
          rowBreaks:function(rec) {
            return [rec.ipc];
          },
          add:function(rec, tr) {
            tr.select(CdsAnchor.create(rec), null, Function.defer(self, 'onchoose')).td(rec.summaryEvery()).td().html(rec.uiLastResults());
          },
          addNone:function() {
            self.tbody().tr().td(Html.Anchor.create('fsnone', '(None Apply)').bubble('onclick', Function.defer(self, 'onchoose'))).colspan(3);
          }
        }
      })
    }
  }
}
/**
 * Tile PortalTile
 */
PortalTile = {
  create:function(container) {
    var My = this;
    return PortalTile = Html.Tile.create(container.clean()).extend(function(self) {
      return {
        onupdate:function() {},
        //
        init:function() {
          self.tile = Html.Tile.create(self, 'PortalTile').addClass('mt5 ml5').hide();
          Html.TableCol.create(self.tile, [ 
            self.portal = PortalAnchorTile.create(self)
              .bubble('onupdate', self.portal_onupdate),
            self.lastLogin = My.LastLogin.create(self)]);
        },
        load:function(fs) {
          self.fs = fs;
          self.portal.load(fs);
          //self.lastLogin.load(fs.portalUser);
          self.tile.show();
          return self; 
        },
        pop:function() {
          self.portal.pop();
        },
        //
        portal_onupdate:function(portalUser) {
          self.lastLogin.load(portalUser);
        }
      }
    })
  },
  LastLogin:{
    create:function(container) {
      return Html.Tile.create(container, 'ml20 mb5').extend(function(self) {
        return {
          init:function() {
            self.form = Html.UlEntry.create(self, function(ef) {
              ef.line().lblf('_status').lblf('_lastLogin', 'pl10');
            }).hide();
          },
          load:function(portalUser) {
            self.rec = portalUser;
            if (self.rec) {
              self.form.load(self.rec).show();
            }
          }
        }
      })
    }
  }
}
BillingTile = {
  create:function(container) {
    return BillingTile = Html.Tile.create(container.clean()).extend(function(self) {
      return {
        //
        pop:function() {
          BillingPop.pop(page.fs);
        }
      }
    })
  }
}
BillingPop = {
  pop:function(fs) {
    return Html.Pop.singleton_pop.apply(this, arguments);
  },
  create:function() {
    return Html.Pop.create('Billing', 700).extend(function(self) {
      return {
        init:function() {
          self.Table = Html.TableLoader.create(self.content).setHeight(400)
            .bubble('onselect', self.Table_onselect);
          self.Table.thead().trFixed().th('Bill').th('Date of Service').th('Visit').w('50%');
        },
        onshow:function(fs) {
          self.recs = fs.superbills;
          self.Table.load(self.recs, function(rec, tr) {
            var stub = fs.docstubs.get_session(rec.Session.sessionId);
            tr.select(Html.AnchorAction.asList('Encounter Form')).td(rec.Session.dateService).td(AnchorDocStub_Preview.create(stub));
          })
        },
        //
        Table_onselect:function(rec) {
          CerberusPop.pop_asSuperbill(rec.Session.sessionId);
        }
      }
    })
  }
}
/**
 * UploadPop FaceUploadPop
 */
FaceUploadPop = {
  /*
   * @arg Facesheet fs 
   */
  pop:function(fs) {
    return Html.Pop.singleton_pop.apply(this, arguments);
  },
  create:function() {
    var vars = {
      'action':'uploadFace',
      'cid':null};
    return Html.UploadPop.create('Upload Portrait', vars).extend(function(self) {
      return {
        oncomplete:function() {},
        //
        onpop:function(fs) {
          self.fs = fs;
          self.form.setValue('cid', fs.cid);
          self.cb.showDelIf(fs.client.img);
        },
        del_onclick:function() {
          self.close();
          Pop.Confirm.showYesNo('Are you sure you want to remove this photo from the facesheet?', function() {
            Html.Window.working(true);
            Ajax.Facesheet.Patients.removeImg(self.fs.cid, self.oncomplete);
          })
        }
      }
    })
  }
}
//
DownloadCustom = {
  pop:function(cid) {
    return Html.Pop.singleton_pop.apply(DownloadCustom, arguments);
  },
  create:function() {
    return Html.Pop.create('Download Clinical Document').extend(function(self) {
      return {
        init:function() {
          var w = Html.Window.getViewportDim();
          Html.Label.create(null, 'Content to Include').into(self.content);
          self.Box = DownloadCustom.Box.create(self.content)
            .setWidth(Math.min(w.width - 80, 1000))
            .setHeight(Math.min(w.height - 250, 800));
          self.Cmd = Html.CmdBar.create(self.content)
            .button('Generate Document...', self.generate_onclick, 'empty-note')
            .button('Record Patient Refused', self.refused_onclick, 'suspend')
            .cancel(self.close);
        },
        //
        onshow:function(cid) {
          self.reset();
          self.cid = cid;
          self.Box.fetch(cid);
        },
        reset:function() {
          self.Box.reset();
        },
        generate_onclick:function() {
          var value = Json.encode(self.Box.getValue());
          CcdDownloader.pop(self.cid, value);
        },
        refused_onclick:function() {
          Pop.Confirm.showYesNo('Are you sure you want to record patient refused clinical summary?', function() {
            self.close();
            Ajax.Ccd.refuse(self.cid, function() {
              self.close();
            });
          })
        }
      }
    })
  },
  Box:{
    create:function(container) {
      return Html.Div.create('scroll p5 ccdboxc').into(container).extend(function(self) {
        return {
          //
          reset:function() {
            self.cc = null;
            self.clean();
          },
          fetch:function(cid) {
            Ajax.Ccd.getSectionTexts(cid, function(sections) {
              self.cc = CustomCcda.revive(sections);
              self.draw();
            })
          },
          getValue:function() {
            self.cc.each(function(section, tid) {
              section.checked = section._ui.isChecked();
              Array.each(section.list(), function(list) {
                Array.each(list.trs(), function(tr) {
                  var td = tr.td[0];
                  if (td._ui) {
                    td.checked = td._ui.isChecked();
                  } else if (td._ui2) {
                    td.value = td._ui2.value;
                  }
                })
              })
            })
            return self.cc;
          },
          //
          draw:function() {
            self.cc.each(function(section, tid) {
              self.TitleCheck(section);
              Array.each(section.list(), function(list) {
                self.TextChecks(tid, list);
              })
            })
          },
          TitleCheck:function(section) {
            var e = Html.LabelCheck.create(section.title).addClass('h1').setChecked(1);
            section._ui = e;
            return Html.Tile.create(self).add(e);
          },
          TextChecks:function(tid, list) {
            var isInstructs = CustomSection.isInstructions(tid); 
            var tile = Html.Tile.create(self, 'ccdbox');
            if (list.caption) {
              Html.H3.create(list.caption).into(tile);
            }
            var t = Html.Table.create(tile).tbody();
            var row = t.tr();
            var ths = list.ths();
            if (ths) {
              for (var i = 0; i < ths.length; i++) {
                row.th(ths[i]);
              }
            }
            var trs = list.trs(), td, e;
            for (var i = 0; i < trs.length; i++) {
              row = t.tr();
              for (var j = 0; j < trs[i].td.length; j++) {
                td = trs[i].td[j];
                if (isInstructs) {
                  e = Html.InputText.create('w100', td._);
                  row.td(e);
                  td._ui2 = e;
                } else if (j == 0) {
                  e = Html.LabelCheck.create(td._).setChecked(1)
                  row.td(e);
                  td._ui = e;                  
                } else {
                  row.td().html(td._);
                }
              }
            }
          }
        }
      })
    }
  }
}
CustomCcda = Object.Rec.extend({
  /*
   tid:CustomSection,..
   */
  onload:function(json) {
    for (var tid in json) {
      this[tid] = CustomSection.revive(this[tid]);
    }
  },
  each:function(callback) {
    for (var tid in this) {
      if (this.hasOwnProperty(tid))
        callback(this[tid], tid);
    }
  }
  //
})
CustomSection = Object.Rec.extend({
  /*
  title
  text
  */
  //
  isInstructions:function(tid) {
    return tid == '2.16.840.1.113883.10.20.22.2.45';
  },
  list:function() {
    return CustomSectionList.reviveAll(this.text.list || [{item:this.text}]);
  }
})
CustomSectionList = Object.Rec.extend({
  /*
   item
     table
   */
  //
  ths:function() {
    if (this.item.table.thead)
      return this.item.table.thead.tr.th;
  },
  trs:function() {
    return this.item.table.tbody.tr;
  }
}) 
//
DownloadChoices = {
  pop:function(cid) {
    return Html.Pop.singleton_pop.apply(DownloadChoices, arguments);
  },
  create:function() {
    return Html.Pop.create('Select Type').extend(function(self) {
      return {
        //
        init:function() {
          Html.BigButton.create(self.content, 'btext', 'Referral Summary')
            .bubble('onclick', self.ccd_onclick);
          Html.BigButton.create(self.content, 'btext', 'Demographics Only')
            .bubble('onclick', self.demo_onclick);
          self.Visit = Html.BigButton.create(self.content, 'bclock', 'Patient Visit Summary')
            .bubble('onclick', self.visit_onclick);
        },
        onshow:function(cid) {
          self.cid = cid;
        },
        //
        ccd_onclick:function() {
          self.close();
          CcdDownloader.pop(self.cid);
        },
        demo_onclick:function() {
          self.close();
          DemoDownloader.pop(self.cid);
        },
        visit_onclick:function() {
          self.close();
          DownloadCustom.pop(self.cid);
          //CcdDownloader.pop(self.cid, true);
        }
      }
    })
  }
}
/* ui.js remnants */
function syncHeights(ids, min) {
  var h = (min) ? min : 0;
  for (var i = 0; i < ids.length; i++) {
    var hi = _$(ids[i]).getDim().height;
    if (hi > h) h = hi;
  }
  for (var i = 0; i < ids.length; i++) {
    _$(ids[i]).setHeight(h);
  }  
}
