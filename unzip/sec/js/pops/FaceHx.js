/**
 * Facesheet Med/Surg/Fam/Soc History
 * Global static 
 * Requires: TableLoader.js, facesheet.css
 */
FaceHx = {
  fs:null,
  changed:null,
  _scb:null,
  _POP:'fsp-hx',  
  /*
   * callback(facesheet) if anything changed (calls page.hxChangedCallback by default)
   */
  pop:function(fs, tab, zoom, callback) {
    Html.Window.working(true);
    this.fs = fs;
    this.changed = false;
    this._scb = Ajax.buildScopedCallback(callback || 'hxChangedCallback');
    var self = this;
    Includer.get(Includer.HTML_FACE_HX, function() {
      new TabBar(FaceHx._POP, 
        ['Medical History', 'Surgical History', 'Family History', 'Psycho-Social History'], 
        ['Medical', 'Surgical', 'Family', 'Psycho-Social'],
        tab);
      if (fs.client) {
        Pop.setCaption('fsp-hx-cap-text', fs.client.name + ' - History');
        Pop.setCaption("pop-hxe-cap-text", fs.client.name + " - History Entry");
      }
      if (! me.Role.Patient.history) {
        _$('medhx-act').invisible();
        _$('surghx-act').invisible();
        _$('famhx-act').invisible();
      }
      self._load();
      Html.Window.working(false);
      if (zoom) {
        Pop.zoom(FaceHx._POP);
      } else {
        Pop.show(FaceHx._POP);
      }
    });
  },
  update:function(fs) {
    this.fs = fs;
    this.changed = true;
    this._load();
  },
  fpClose:function() {
    Pop.close();
    if (this.changed) {
      Ajax.callScopedCallback(this._scb, this.fs);
    }    
  },
  fpAddMedHx:function() {
    FaceMedSurgHxEntry.showProcQuestion(this.fs.medhx);
  },
  fpLookupMedHx:function() {
    FaceMedSurgHxEntry.lookupIcd(this.fs.medhx);
  },
  fpAddSurgHx:function() {
    FaceMedSurgHxEntry.showProcQuestion(this.fs.surghx);
  },
  fpLookupSurgHx:function() {
    if (me.Role.Patient.history) {
      this.popProcEntry();
      ProcEntry.form.getField('ipc').showFilteredPop(C_Ipc.CAT_SURG);
    }
  },
  popProcEntry:function(proc) {
    proc = proc || Proc.asNew(this.fs.cid);
    var self = this;
    ProcEntry.pop_asHistory(proc).bubble(['ondelete','onsave'], function() {
      self.fs.ajax(Html.Window).refetchProcs(function() {
        self.update(self.fs);
      })
    })
  },
  fpEditMedHx:function(proc, focus) {
    if (me.Role.Patient.history)
      FaceMedSurgHxEntry.pop(this.fs.medhx, proc, focus);
  },
  fpEditSurgHx:function(proc, focus) {
    if (me.Role.Patient.history) 
      this.popProcEntry(proc);
  },
  fpAddFamHx:function() {
    if (me.Role.Patient.history)
      FaceFamHxEntry.showFamQuestion();
  },
  fpEditFamHx:function(puid, focus) {
    if (me.Role.Patient.history)
      FaceFamHxEntry.pop(puid, focus);
  },
  fpEditSocHx:function(topic, focus, dsyncCombo) {
    if (me.Role.Patient.history)
      FaceSocHxEntry.pop(topic, focus, dsyncCombo);
  },
  fpAdopted:function() {
    this.fpFamSet('saveFamHxAdopted', 'set family history as "adopted"');
  },
  fpUnknown:function() {
    this.fpFamSet('saveFamHxUnknown', 'set family history as "unknown"');
  },
  fpFamClear:function() {
    this.fpFamSet('saveFamHxClear', 'clear family history');
  },
  fpFamSet:function(action, text) {
    var self = this;
    Pop.Confirm.showYesNoCancel('This will ' + text + '. Continue?', function() {
      Html.Window.working(true);
      Ajax.get(Ajax.SVR_POP, action, self.fs.cid, function(fs) {
        Html.Window.working(false);
        self.fs.cuTimestamp = fs.cuTimestamp;
        self.fs.famhx = fs.famhx;
        self.update(self.fs);
      }) 
    })
  },
  //
  _load:function() {
    this._loadMedSurgHx();
    this._loadFamHx();
    this._loadSocHx();
    Html.Window.working(false);
  },
  /* 
   * Medical/Surgical HX
   * Added to fs:
   *   hx        // hx working on (e.g. fs.medhx)
   *   hx.pq     // JQuestion of proc list question
   *   hx.proc   // selected proc
   *   hx.rec    // rec being edited
   *   hx.focus  // field to focus on edit
   *   hx.qs     // entry form questions {dsync:JQuestion,..} 
   * UI class added as array element to field defs:
   *   hx.fields[field][label,className]
   */
  _loadMedSurgHx:function() {
    this._loadMedHx();
    this._loadSurgHx();
    this._setMedSurgHxFieldInfo();
  }, 
  _loadMedHx:function() {
    var fs = this.fs;
    var t = new TableLoader('fsp-medhx-tbody', 'off', 'fsp-medhx-div');
    for (var proc in fs.medhx.recs) {
      var rec = fs.medhx.recs[proc];
      t.createTr(proc);
      t.createTd('nowrap nbb');
      t.append(createAnchor(null, null, 'fs', null, proc, FaceHx.fpEditMedHx.bind(this, proc, null)));
      var date = DateUi.extractDate(bulletJoin(rec.date));
      var type = bulletJoin(rec.type);
      var rx = bulletJoin(rec.rx);
      var comment = bulletJoin(rec.comment);
      t.createTdAppend(null, createAnchor(null, null, 'hxa', null, String.nbsp(date), FaceHx.fpEditMedHx.bind(this, proc, 'date')));
      t.createTdAppend(null, createAnchor(null, null, 'hxa', null, String.nbsp(type), FaceHx.fpEditMedHx.bind(this, proc, 'type')));
      t.createTdAppend(null, createAnchor(null, null, 'hxa', null, String.nbsp(rx), FaceHx.fpEditMedHx.bind(this, proc, 'rx')));
      t.createTr(proc);
      t.createTd('nrbc', null, 'Comment:');
      t.createTdAppend('top', createAnchor(null, null, 'hxa', null, String.nbsp(comment), FaceHx.fpEditMedHx.bind(this, proc, 'comment')));
      t.td.colSpan = 3;
    }
  },
  _loadSurgHx:function() {
    var fs = this.fs;
    var t = new TableLoader('fsp-surghx-tbody', 'off', 'fsp-surghx-div');
    if (fs.surgs) {
      var rec, date, type, comment, cls;
      for (var i = 0; i < fs.surgs.length; i++) {
        rec = fs.surgs[i];
        t.createTr(rec._name);
        t.createTd('nowrap nbb');
        date = DateUi.extractDate(bulletJoin(rec.date));
        type = bulletJoin(rec.location);
        comment = bulletJoin(rec.comments);
        t.append(createAnchor(null, null, 'fs', null, rec._name, FaceHx.fpEditSurgHx.bind(this, rec, null)));
        cls = (date == 'unknown' ? 'hxa red' : 'hxa');
        if (date == 'unknown') 
          date = 'Unknown or Not Documented';
        t.createTdAppend(null, createAnchor(null, null, cls, null, String.nbsp(date), FaceHx.fpEditSurgHx.bind(this, rec, 'date')));
        t.createTdAppend(null, createAnchor(null, null, 'hxa', null, String.nbsp(type), FaceHx.fpEditSurgHx.bind(this, rec, 'type')));
        t.createTr(rec._name);
        t.createTd('nrbc', null, 'Comment:');
        t.createTdAppend('top', createAnchor(null, null, 'hxa', null, String.nbsp(comment), FaceHx.fpEditSurgHx.bind(this, rec, 'comment')));
        t.td.colSpan = 2;
      }
    }
  },
  _setMedSurgHxFieldInfo:function() {
    var fs = this.fs;
    if (! this._medSurgHxFieldInfoSet) {
      this._medSurgHxFieldInfoSet = true;
      fs.medhx.fields['date'].push('qd');
      fs.medhx.fields['type'].push('qd2');
      fs.medhx.fields['rx'].push('qt');
      fs.medhx.fields['comment'].push('qc2');
      fs.surghx.fields['date'].push('qd');
      fs.surghx.fields['type'].push('qd2');
      fs.surghx.fields['comment'].push('qc2');
    }
  },
  /* 
   * Family HX
   * Added to fs.famhx:
   *   sq     // JQuestion of suid ('famHx') question
   *   puid   // selected puid (injector+clone, e.g. 'father+male')
   *   puidc  // just clone portion of selected puid ('+male')
   *   focus  // field to focus on edit
   *   rec    // rec being edited
   *   aqs    // all entry form questions, arranged by 'clone' {'+male':{dsync:JQuestion,..},'+female':{dsync:JQuestion,..}}
   *   qs     // entry form questions for selected 'clone' ('+male')   
   */
  _loadFamHx:function() {
    var fs = this.fs;
    var t = new TableLoader('fsp-famhx-tbody', 'off', 'fsp-famhx-div');
    if (fs.famhx.recs) {
      for (var puid in fs.famhx.recs) {
        var rec = fs.famhx.recs[puid];
        t.createTr(puid);
        t.createTd();
        t.td.rowSpan = 3;
        t.append(createAnchor(null, null, 'fs', null, fs.famhx.puidTexts[puid], FaceHx.fpEditFamHx.bind(this, puid)));
        t.createTd('right', 'Status');
        var v = '&nbsp;';
        if (rec.status) {
          v = FaceUi.single(rec.status);
          if (FaceUi.isDeathAge(v) && rec.deathAge) {
            v += ' (age ' + FaceUi.single(rec.deathAge) + ')';
          } else if (rec.age) {
            v += ' (age ' + FaceUi.single(rec.age) + ')';
          }
        }
        t.createTdAppend(null, createAnchor(null, null, 'hxa', null, v, FaceHx.fpEditFamHx.bind(this, puid)));
        t.createTr(puid);
        t.createTd('right', 'History');
        t.createTdAppend(null, createAnchor(null, null, 'hxa', null, String.nbsp(bulletJoin(rec.history)), FaceHx.fpEditFamHx.bind(this, puid)));
        t.createTr(puid);
        t.createTd('right', 'Comment');
        t.createTdAppend(null, createAnchor(null, null, 'hxa', null, String.nbsp(rec.comment), FaceHx.fpEditFamHx.bind(this, puid)));
      }
    } else if (fs.famhx.sopts && fs.famhx.sopts.length == 1) {
      var other = fs.famhx.sopts[0];
      t.createTr();
      t.createTd();
      t.td.colSpan = 3;
      t.append(createAnchor(null, null, 'fs', null, other));
    }
  },
  _setFamHxFieldInfo:function() {
    var fs = this.fs;
    if (! this._famHxFieldInfoSet) {
      this._famHxFieldInfoSet = true;
      fs.famhx.fields['status'].push('q qd2');
      fs.famhx.fields['age'].push('q qd');
      fs.famhx.fields['deathAge'].push('q qd');
      fs.famhx.fields['history'].push('q qc2');
      fs.famhx.fields['comment'].push('q qc2');
    }
  },
  /*
   * Social HX
   */
  _loadSocHx:function() {
    var scroll = _$('fsp-sochx-div').scrollTop;
    var fs = this.fs;
    var t = new TableLoader('fsp-sochx-tbody', 'off', 'fsp-sochx-div');
    for (var topic in fs.sochx) {
      var tpvals = [];  // ['bullet-rec-values',..]
      var rec = fs.sochx[topic];
      var dsyncCombo = FaceUi.getDsyncCombo(topic);
      t.createTr(topic);
      var td = t.createTd();
      t.append(createAnchor(null, null, 'fs', null, topic, FaceHx.fpEditSocHx.bind(this, topic, null, dsyncCombo)));
      var i = 0;
      for (var dsyncId in rec) {
        if (i > 0) {
          t.createTr(topic);
        }
        var a;
        var values = rec[dsyncId].v;
        var dsyncCombo = rec[dsyncId].d;
        if (dsyncCombo) {
          if (rec[dsyncId].l == null) 
            rec[dsyncId].l = values.shift();
        }
        t.createTd('right', rec[dsyncId].l);
        var v = String.nbsp(bulletJoin(values));
        if (dsyncCombo) {
          a = createAnchor(null, null, 'hxa', null, v, FaceHx.fpEditSocHx.bind(this, topic, dsyncId, dsyncCombo));
        } else {
          a = createAnchor(null, null, 'hxa', null, v, FaceHx.fpEditSocHx.bind(this, topic, dsyncId, null));
        }
        t.createTdAppend(null, a); 
        i++;
      }
      if (i == 0) {
        t.createTd();
        t.createTd();
      } else {
        td.rowSpan = i;
      }
    }
    if (scroll)
      _$('fsp-sochx-div').scrollTop = scroll;
  }
};
/**
 * Med/Surg History Entry
 */
FaceMedSurgHxEntry = {
  parent:null,
  fs:null,
  pop:function(hx, proc, focus) {
    var fs = FaceUi.setParentage(FaceHx, this);
    fs.hx = hx;
    fs.hx.proc = proc;
    fs.hx.focus = (focus) ? this._makeProcDsync(focus) : null;
    fs.hx.rec = fs.hx.recs[proc];
    var self = this;
    if (fs.hx.qs) {
      self._load();
    } else {
      Html.Window.working(true);
      Ajax.get(Ajax.SVR_POP, 'getHxQuestions', {'cat':fs.hx.cat}, 
        function(qs) {
          Html.Window.working(false);
          fs.hx.qs = (qs) ? Question.reviveAll(qs) : {};
          self._load();
        });
    }
  },
  lookupIcd:function(hx) {
    var fs = FaceUi.setParentage(FaceHx, this);
    fs.hx = hx;
    var self = this;
    Includer.getWorking([Includer.AP_ICD_POP], function() {
      showIcd10(null, null, function(code, desc) {
        if (! String.isBlank(desc)) {
          var procs = [desc];
          fs.hx.procs = fs.hx.procs || [];
          if (FaceUi.isDirty(procs, fs.hx.procs))
            self._saveProc();
        }
      })
    });
  },
  showProcQuestion:function(hx) {
    var fs = FaceUi.setParentage(FaceHx, this);
    fs.hx = hx;
    var self = this;
    if (hx.pq) {
      async(function() {
        self._showProcQuestion();
      })
    } else {
      /*
      Html.Window.working(true);
      Ajax.get(Ajax.SVR_POP, 'getProcQuestion', {'cat':fs.hx.cat}, 
        function(q) {
          Html.Window.working(false);
          q.multiOnly = true;
          q.other = 1;
          fs.hx.pq = Question.revive(q);
          self._showProcQuestion();
        });
        */
      Ajax.Templates.getHxQuestion(fs.hx.cat, Html.Window, function(q) {
        q.multiOnly = true;
        fs.hx.pq = q;
        self._showProcQuestion();
      })
    }
  },
  update:function(fs) {
    this.fs.cuTimestamp = fs.cuTimestamp;
    if (fs.contains == FaceUi.CONTAINS_MEDHX) {
      this.fs.medhx.procs = fs.medhx.procs;
      this.fs.medhx.recs = fs.medhx.recs;
    } else {
      this.fs.procedures = Procedures.revive(fs.procedures);
      this.fs.surgs = fs.procedures.getSurgs();
      this.fs.surghx.procs = fs.surghx.procs;
      this.fs.surghx.recs = fs.surghx.recs;
    }
    this.parent.update(this.fs);
  },
  fpSave:function() {
    Page.workingCmd(true);
    var rec = this._buildRec();
    var self = this;
    Ajax.post(Ajax.SVR_POP, 'saveMedSurgHx', rec, 
      function(fs) {
        Page.workingCmd(false);
        self.update(fs);
        Pop.close();
      });
  },
  fpDelete:function() {
    var self = this;
    Pop.Confirm.showYesNo('Are you sure you want to remove this record?', function() {
      Pop.close();
      Html.Window.working(true);
      self._removeHxProc();
      self._saveProc();
    });
  },
  _showProcQuestion:function() {
    var fs = this.fs;
    var q = this.fs.hx.pq;
    //qSetByValues(fs.hx.pq, fs.hx.procs);
    q.reset();
    var self = this;
    QPopHx.pop(q, function(q) {
      var procs = q.Opts.getTexts();
      fs.hx.procs = fs.hx.procs || [];
      if (FaceUi.isDirty(procs, fs.hx.procs)) {
        self._saveProc();
      }
    })
  },
  _saveProc:function() {
    var self = this;
    Html.Window.working(true);
    rec = this._buildProcRec();
    Ajax.post(Ajax.SVR_POP, 'saveMedSurgHxProcs', rec,
      function(fs) { 
        Html.Window.working(false);
        self.update(fs);
      });
  },
  _load:function() {
    var fs = this.fs;
    _$('hxe-proc').setText(fs.hx.proc);
    var tform = new TemplateForm(_$('ul-hxe-fields'), 'first2', fs.hx.qs);
    for (var field in fs.hx.rec) {
      var value = fs.hx.rec[field];
      var info = fs.hx.fields[field];
      var lbl = info[0];
      var className = info[1];
      var type = (field == 'date') ? TemplateForm.Q_DEF_CALENDAR : null;
      tform.addLiAppend(lbl, this._makeProcDsync(field), null, value, className, type);
    }  
    Pop.show('pop-hxe');
    if (String.is(fs.hx.focus)) {
      tform.popup(fs.hx.focus);
    }
    fs.hx.tform = tform;
  },
  _makeProcDsync:function(field, proc) {  // proc optional
    var fs = this.fs;
    return fs.hx.cat + '.' + ((proc) ? proc : fs.hx.proc) + '.' + field;
  },
  _buildRec:function() {
    var fs = this.fs;
    var rec = {
      'cat':fs.hx.cat,
      'cid':fs.client.clientId,
      'rec':fs.hx.tform.buildRecord()
      };
    return rec;
  },
  _buildProcRec:function() {
    var fs = this.fs;
    return {
      'cat':fs.hx.cat,
      'cid':fs.client.clientId,
      'rec':fs.hx.procs
      };
  },
  _removeHxProc:function() {
    var fs = this.fs;
    for (var i = 0; i < fs.hx.procs.length; i++) {
      if (fs.hx.procs[i] == fs.hx.proc) {
        fs.hx.procs.splice(i, 1);
        return;
      }
    }
  }
};
/**
 * Family History Entry
 */
FaceFamHxEntry = {
  parent:null,
  fs:null,
  pop:function(puid, focus) {
    var fs = FaceUi.setParentage(FaceHx, this);
    fs.famhx.puid = puid;
    fs.famhx.puidc = this._puidClone(puid);
    fs.famhx.focus = focus;
    fs.famhx.rec = fs.famhx.recs[puid];
    var self = this;
    if (fs.famhx.aqs) {
      self._load();
    } else {
      Html.Window.working(true);
      Ajax.get(Ajax.SVR_POP, 'getFamhxQuestions', null, 
        function(aqs) {
          Html.Window.working(false);
          fs.famhx.aqs = (aqs) ? aqs : {};
          self._load();
        });
    }
  },
  showFamQuestion:function(hx) {
    var fs = FaceUi.setParentage(FaceHx, this);
    var self = this;
    if (fs.famhx.sq) {
      self._showFamQuestion();
    } else {
      Html.Window.working(true);
      Ajax.get(Ajax.SVR_POP, 'getSuidQuestion', {'suid':fs.famhx.suid}, 
        function(q) {
          Html.Window.working(false);
          q.multiOnly = true;
          q.hideOthers = true;
          //fs.famhx.sq = q;
          fs.famhx.sq = Question.revive(q);
          self._showFamQuestion();
        });
    }
  },
  update:function(fs) {
    this.fs.cuTimestamp = fs.cuTimestamp;
    this.fs.famhx.sopts = fs.famhx.sopts;
    this.fs.famhx.puids = fs.famhx.puids;
    this.fs.famhx.recs = fs.famhx.recs;
    this.parent.update(this.fs);
  },
  fpSave:function() {
    Page.workingCmd(true);
    var fs = this.fs;
    var rec = this._buildRec();
    var self = this;
    Ajax.post(Ajax.SVR_POP, 'saveFamilyHx', rec, 
      function(fs) {
        Page.workingCmd(false);
        self.update(fs);
        Pop.close();
      });
  },
  fpDelete:function() {
    var fs = this.fs;
    var self = this;
    Pop.Confirm.showYesNo('Are you sure you want to remove this record?', function() {
      Pop.close();
      Html.Window.working(true);
      Ajax.get(Ajax.SVR_POP, 'removeFamhx', {'cid':fs.client.clientId, 'puid':fs.famhx.puid}, 
        function(fs) { 
          Html.Window.working(false);
          self.update(fs);
        });
    });
  },
  _showFamQuestion:function() {
    var fs = this.fs;
    //qSetByValues(fs.famhx.sq, fs.famhx.sopts);
    fs.famhx.sq.setByValue(fs.famhx.sopts);
    var self = this; 
    QuestionPopEntry.pop(fs.famhx.sq, function(q) {
      //var sopts = qSelTextArray(q);
      var sopts = q.Opts.getTexts();
      fs.famhx.sopts = fs.famhx.sopts || [];
      if (FaceUi.isDirty(sopts, fs.famhx.sopts)) 
        self._saveFamQuestion();
    });
    /*
    showQuestion(fs.famhx.sq, null, null, true,
      function(q) {
        var sopts = qSelTextArray(q);
        fs.famhx.sopts = fs.famhx.sopts || [];
        if (FaceUi.isDirty(sopts, fs.famhx.sopts)) {
          self._saveFamQuestion();
        }
      });
    */
  },
  _saveFamQuestion:function() {
    var fs = this.fs;
    var self = this;
    Html.Window.working(true);
    rec = this._buildFamQuestionRec();
    Ajax.post(Ajax.SVR_POP, 'saveSuidQuestion', rec,
      function(fs) {
        Html.Window.working(false);
        self.update(fs);
      });
  },
  _load:function() {
    var fs = this.fs;
    fs.famhx.qs = fs.famhx.aqs[fs.famhx.puidc];  // subset of questions just for selected clone ('+male')
    _$('fhxe-rel').setText(fs.famhx.puidTexts[fs.famhx.puid]);
    var self = this;
    var tform = new TemplateForm(_$('ul-fhxe-fields'), 'first2', fs.famhx.qs, function(q){self._onChange()});
    for (var field in fs.famhx.rec) {
      var value = fs.famhx.rec[field];
      var info = fs.famhx.fields[field];
      var lbl = info[0];
      var className = info[1];
      var type = (field == 'date') ? TemplateForm.Q_DEF_CALENDAR : null;
      tform.addLi(this._fhxeLiId(field));
      tform.append(lbl, field, null, value, className, type);
    }
    Pop.show('pop-fhxe');
    if (String.is(fs.famhx.focus)) {
      tform.popup(fs.famhx.focus);
    }
    fs.famhx.tform = tform;
    this._onChange();
  },
  _onChange:function() {
    var fs = this.fs;
    var status = FaceUi.qSingle(fs.famhx.qs['status']);
    if (FaceUi.isDeathAge(status)) {
      fs.famhx.qs['age'].reset();  //qClear(fs.famhx.qs['age']);
      _$(this._fhxeLiId('age')).hide();
      _$(this._fhxeLiId('deathAge')).show();
    } else {
      fs.famhx.qs['deathAge'].reset();  //qClear(fs.famhx.qs['deathAge']);
      _$(this._fhxeLiId('age')).show();
      _$(this._fhxeLiId('deathAge')).hide();    
    } 
  },
  _fhxeLiId:function(field) {
    return 'fxhe-li-' + field;
  },
  _buildRec:function() {
    var fs = this.fs;
    var prefix = fs.famhx.suid + '.' + fs.famhx.puid + '.';
    return {
      'cid':fs.client.clientId,
      'rec':fs.famhx.tform.buildRecord(TemplateForm.VALUES_ALWAYS_ARRAY, prefix),
      'fhxprocs':this._buildFhxProcs(fs.famhx.puid, fs.famhx.qs)
      };
  },
  _buildFhxProcs:function(member, qs) {
    var fhxprocs, ipcs, self = this;
    Array.each(qs, function(q) {
      if (q.Opts) {
        ipcs = self.getIpcsFromSelected(q);
        if (fhxprocs == null)
          fhxprocs = {};
        if (fhxprocs[member])
          fhxprocs[member].append(ipcs);
        else
          fhxprocs[member] = ipcs;
      }
    })
    return fhxprocs;
  },
  getIpcsFromSelected:function(q) {
    var ipcs = [];
    Array.each(q.Opts.getSels(), function(o) {
      if (o.cpt)
        ipcs.push(o.cpt);
    })
    return ipcs;
  },
  _buildFamQuestionRec:function() {
    var fs = this.fs;
    return {
      'suid':fs.famhx.suid,
      'cid':fs.client.clientId,
      'rec':fs.famhx.sopts
      };
  },
  _puidClone:function(puid) {  // return clone portion of puid, e.g. '+male' from 'father+male'
    return '+' + puid.split('+')[1];
  }
};
/**
 * Social History Entry
 */
FaceSocHxEntry = {
  parent:null,
  fs:null,
  pop:function(topic, focus, dsyncCombo) {
    var fs = FaceUi.setParentage(FaceHx, this);
    fs.stopic = topic;
    fs.sfocus = focus;
    fs.soc = fs.sochx[topic];
    fs.dsyncCombo = dsyncCombo;
    var self = this;
    if (fs.sqs) {
      self._load();
    } else {
      Html.Window.working(true);
      Ajax.get(Ajax.SVR_POP, 'getSochxQuestions', null,
        function(questions) {
         fs.sqs = questions;
          var isMale = (fs.client.sex == 'M');
          for (var dsync in questions) {
            var q = Question.revive(questions[dsync]);            
            // qGenderFix(q, isMale);
          }
          Html.Window.working(false);
          self._load();
        } 
      );
    }
  },
  update:function(fs) {
    this.fs.cuTimestamp = fs.cuTimestamp;
    this.fs.sochx = fs.sochx;
    this.parent.update(this.fs);
  },
  fpClose:function() {
    var fs = this.fs;
    var self = this;
    if (fs.stform && fs.stform.isDirty()) {
      Pop.Confirm.showDirtyExit(function(confirm) {
        if (confirm) {
          Html.Window.working(true);
          self.fpSave();
        } else {
          Pop.close();
        }
      });
    } else {
      Pop.close();
    }
  },
  fpSave:function() {
    Page.workingCmd(true);
    var fs = this.fs;
    var self = this;
    Ajax.post(Ajax.SVR_POP, 'saveSocialHx', this._buildRec(), 
      function(fs) {
        Page.workingCmd(false);
        Html.Window.working(false);
        self.update(fs);
        Pop.close();
      });
  },
  _load:function() {
    var fs = this.fs;
    _$('she-topic').setText(fs.stopic);
    var tform = new TemplateForm(_$('ul-she-fields'), 'first2', fs.sqs, null); //, TemplateForm.NAV_NEXT_ON_FORM);
    if (fs.dsyncCombo) {
      var lbl = (fs.dsyncCombo == 'sochx.drugs') ? 'Substance' : 'Occupation';
      for (var dsync in fs.soc) {
        var value = [fs.soc[dsync].l, fs.soc[dsync].v];
        tform.addLiAppend(lbl, fs.dsyncCombo, dsync, value);
      }    
      var ix = (dsync) ? String.toInt(dsync.split('?')[1]) : 0;
      for (var i = 0; i < 3; i++) {
        var dsync = fs.dsyncCombo + '?' + (ix + i);
        var value = [null, null];
        tform.addLiAppend(lbl, fs.dsyncCombo, dsync, value);
      }
    } else {
      for (var dsync in fs.soc) {
        tform.addLiAppend(fs.soc[dsync].l, dsync, null, fs.soc[dsync].v);
      }
    }
    Pop.show('pop-she');
    if (String.is(fs.sfocus)) {
      tform.popup(fs.sfocus);
    }
    fs.stform = tform;
  },
  _buildRec:function() {
    var fs = this.fs;
    return {
      'cid':fs.client.clientId,
      'rec':fs.stform.buildRecord(),
      'shxprocs':this._buildShxProcs(fs.sqs)
      };
  },
  getIpcsFromSelected:function(q) {
    var ipcs = [];
    Array.each(q.Opts.getSels(), function(o) {
      if (o.cpt)
        ipcs.push(o.cpt);
    })
    return ipcs;
  },
  _buildShxProcs:function(qs) {
    var shxprocs = [], ipcs, self = this;
    for (var dsync in this.fs.soc) {
      var q = qs[dsync];
      if (q && q.Opts) {
        ipcs = self.getIpcsFromSelected(q);
        shxprocs.append(ipcs);
      }
    }
    if (shxprocs.length)
      return {'shx':shxprocs};
  }
};
/**
 * QPopMulti QPopHx
 */
QPopHx = {
  pop:function(q, onupdate) {
    return Html.Pop.singleton_pop.apply(this, arguments);
  },
  create:function() {
    return QPopMulti.create().extend(QPopHx, function(self, parent) {
      return {
        POP_POS:Pop.POS_CENTER,
        loadSingleTile:function() {
          self.singleTile.hide();
        },
        setMaxHeight:function(i) {
          self.multiTile.setMaxHeight(i);
        }
      }
    })
  }
}