/**
 * Facesheet Diagnoses
 * Global static 
 * Requires: TableLoader.js, facesheet.css
 */
var FaceDiagnoses = {
  fs:null,
  changed:null,
  _scb:null,
  _POP:'fsp-dia',  
  /*
   * callback(facesheet) if anything changed (calls page.diagnosesChangedCallback by default)
   */
  pop:function(fs, zoom, callback) {
    Html.Window.working(true);
    this.fs = fs;
    this.changed = false;
    this._scb = Ajax.buildScopedCallback(callback || 'diagnosesChangedCallback');
    var self = this;
    Includer.get(Includer.HTML_FACE_DIAGNOSES, function() {
      new TabBar(FaceDiagnoses._POP, ['Diagnoses List', 'Documented Diagnoses'], ['Diagnoses', 'Documented']);
      if (fs.client) {
        Pop.setCaption('fsp-dia-cap-text', fs.client.name + ' - Diagnoses');
        Pop.setCaption("pop-de-cap-text", fs.client.name + " - Diagnosis Entry");
      }
      if (! me.Role.Patient.diagnoses) {
        _$('dia-act').invisible(); 
        _$('dia-recon').invisible(); 
      }
      self._load();
      Html.Window.working(false);
      if (zoom) {
        Pop.zoom(FaceDiagnoses._POP);
      } else {
        Pop.show(FaceDiagnoses._POP);
      }
    });
  },
  update:function(fs, reload/*=false*/) {
    this.fs.cuTimestamp = fs.cuTimestamp;
    this.fs.diagnoses = fs.diagnoses;
    this.fs.diagnosesHistory = fs.diagnosesHistory;
    this.changed = true;
    this.reload = reload;
    this._load();
  },
  fpClose:function() {
    Pop.close();
    if (this.changed) {
      if (this.reload) 
        Ajax.callScopedCallback(this._scb, null); 
      else
        Ajax.callScopedCallback(this._scb, this.fs);
    }    
  },
  fpEdit:function(diagnosis) {
    if (me.Role.Patient.diagnoses) {
      if (diagnosis == null)
        FaceDiagEntry.pop(Diagnosis.asNew(this.fs.client.clientId));
      else if (diagnosis.active)
        FaceDiagEntry.pop(diagnosis);
      else
        FaceInactiveDiag.pop(diagnosis);
    }
  },
  fpNone:function() {
    if (this.fs.diagnoses) 
      Pop.Confirm.showImportant('This will deactivate all active diagnoses and replace with "None Active". Proceed?', null, null, null, null, null, this.setToNone.bind(this))
    else
      this.setToNone();
  },
  fpReconcile:function() {
    var self = this;
    DocHistoryPop.pop(self.fs, null, function(rec) {
      if (rec && rec._type == 'Electronic') {
        Ajax.Ccd.getDiags(rec.id, function(diags) {
          DocHistoryPop.close();
          DiagReconcile.pop(self.fs, diags, function(fs) {
            self.update(fs);
          })          
        })
      } else {
        DocHistoryPop.close();
      }
    })
  },
  fpDeleteChecked:function() {
    var checks = getCheckedValues('sel-dia', 'fsp-dia-tbody'); 
    var self = this;
    if (checks.length > 0) {
      Pop.Confirm.showDeleteChecked('remove', function(confirm) {
        if (confirm) {
          Html.Window.working(true);
          Ajax.post(Ajax.SVR_POP, 'deactivateDiagnoses', checks, 
            function(fs) {
              self.update(fs);
              Html.Window.working(false);
            });
        }
      });  
    } else {
      Pop.Msg.showCritical('Nothing was selected.');
    }
  },
  fpDownload:function() {
    AdtDownloader.pop(this.fs.client.clientId);
  },
  //
  setToNone:function() {
    Diagnoses.ajax().setNone(this.fs.clientId, this.update.bind(this));
  },
  _load:function() {
    var fs = this.fs;
    //setDisabled('dia-cmd-toggle', fs.diagnoses == null);
    var tp = new TableLoader('fsp-dia-tbody', 'off', 'fsp-dia-div');
    tp.filterAllLabel = 'Complete (Active and Inactive)';
    tp.defineFilterFn(
      function(diagnosis) {
        return {'Show':diagnosis && diagnosis._active}
      });
    var self = this;
    _$('dia-none').show();
    if (fs.diagnoses) {
      var i, diagnosis, cls;
      for (i = 0; i < fs.diagnoses.length; i++) {
        diagnosis = fs.diagnoses[i];
        tp.createTr(null, null, diagnosis);
        //tp.createTdAppend('check', null);//createCheckbox('sel-dia', diagnosis.id));
        cls = (diagnosis.active) ? 'fs' : 'fsi';
        if (diagnosis._none) {
          _$('dia-none').hide();
          tp.createTd('fsb', diagnosis._name);
        } else {
          tp.createTdAppend(null, createA(cls, diagnosis._name, FaceDiagnoses.fpEdit.curry(diagnosis)));
        }
        if (diagnosis.icd)
          tp.createTdAppend(null, InfoButton.forDiag(diagnosis.icd, fs.clientId));
        else
          tp.createTdAppend(null, null);
        tp.createTd(null, diagnosis._status);
        tp.createTd('nw', diagnosis.uiDateRange());
        tp.createTd('nw', diagnosis.uiRecon());
      }
      tp.loadFilterTopbar('dia-filter-ul');
      if (fs.diagnosesHistory) {
        t = new TableLoader('fsp-diah-tbody', 'off', 'fsp-diah-div');
        t.defineFilterFn(
          function(diagnosis) {
            return {'Show':diagnosis && diagnosis.text}
          });
        for (i = 0; i < fs.diagnosesHistory.length; i++) {
          diagnosis = fs.diagnosesHistory[i];
          t.createTr(diagnosis.date + diagnosis.sessionId, [diagnosis.date, diagnosis.sessionId], diagnosis);
          t.createTd('histbreak', diagnosis.date);
          t.createTdAppend(null, FaceUi.createSessionAnchor(fs, diagnosis.sessionId));
          t.createTd(null, null, diagnosis.text);
        }
        t.loadFilterTopbar('diah-filter-ul');
      }
    } else {
      tp.createTrTd(null, null, '&nbsp;');
      tp.td.colSpan = 4;
    }
    flicker("diah-head");
    tp.setFilterTopbar({'Show':'Active Only'});
  }
}
/**
 * Pop FaceInactiveDiag
 */
FaceInactiveDiag = {
  pop:function(rec) {
    return this.create().pop(rec);
  },
  create:function() {
    return FaceInactiveDiag = Html.Pop.create('Inactive Diagnosis', 600).extend(function(self) {
      return {
        init:function() {
          self.frame = Html.Pop.Frame.create(self.content);
          Html.Tile.create(self.frame).html("This entry is recorded into the patient's inactive history. Do you want to proceed with editing this entry, or copy this as a new active entry?");
          Html.CmdBar.create(self.content).button('Copy as New Active', self.copy_onclick, 'copy-note').button('Edit This Entry', self.edit_onclick, 'button-edit').cancel(self.close);
        }, 
        onshow:function(rec) {
          self.frame.setCaption(rec._status + ': ' + rec._name.toUpperCase() + ' ' + rec.uiDateRange());
          self.rec = rec;
        },
        copy_onclick:function() {
          self.close();
          FaceDiagEntry.pop(self.rec.cloneNewActive());
        },
        edit_onclick:function() {
          self.close();
          FaceDiagEntry.pop(self.rec);
        }
      }
    })
  }
}
/**
 * Diagnosis Entry
 */
FaceDiagEntry = {
  fs:null,
  form:null,
  diagnosis:null,
  //
  pop:function(diagnosis) {
    var fs = FaceUi.setParentage(FaceDiagnoses, this);
    this.diagnosis = diagnosis;
    this._load();
    var self = this;
    Includer.getWorking([Includer.AP_ICD_POP], function() {
      Pop.show('pop-de');
      self.form.focus('text');
    });
  },
  isDirty:function() {
    return (this.form && this.form.isRecordChanged());
  },
  fpSave:function() {
    Page.workingCmd(true);
    var self = this;
    Ajax.Facesheet.Diagnoses.save(this.form.getRecord(), 
      function(fs) {
        Page.workingCmd(false);
        self.parent.update(fs);
        Pop.close();
      });
  },
  fpAddMedHx:function() {
    var self = this;
    var rec = this.form.getRecord();
    Pop.Confirm.showYesNo('Copy this diagnosis to patient past medical history?', function() {
      Html.Window.working(true);
      Ajax.Facesheet.Diagnoses.copyToMedHx(rec, function(fs) {
        Html.Window.working(false);
        self.parent.update(fs, true);
        Pop.close();
      })
    })    
  },
  fpDelete:function() {
    var self = this;
    Pop.Confirm.showYesNo('Are you sure you want to remove this record?', function() {
      Html.Window.working(true);
      Ajax.Facesheet.Diagnoses.remove(self.diagnosis.dataDiagnosesId, 
        function(fs) {
          Html.Window.working(false);
          self.parent.update(fs);
          Pop.close();
        });
    });
  },
  fpClose:function() {
    Pop.Confirm.closeCheckDirty(this, this.fpSave);
  },
  fpLookupIcd:function(fid) {
    var self = this;
    showIcd(null, this.form.getValue(fid), 
      function(code, desc) {
        if (code) {
          self.form.setValue('icd', code);
          self.form.setValue('text', desc);
        }
      });
  },
  fpLookupIcd10:function(fid) {
    var self = this;
    showIcd10(null, this.form.getValue(fid), 
      function(code, desc) {
        if (code) {
          self.form.setValue('icd10', code);
          self.form.setValue('text', desc);
        }
      });
  },
  //
  _load:function() {
    var ef = new EntryForm(_$('pop-de-form'));
    ef.li('Date').datetime('date').lbl('Status').select('status', C_Diagnosis.STATUSES, null, this._statusChange.bind(this)).startSpan('spanEndDate').lbl('on', '').datetime('dateClosed').endSpan();
    ef.li('Description', 'mt15').textbox('text', 50).append(this._createIcdLookup('text')).append(this._createIcd10Lookup('text'));
    ef.li('ICD9').textbox('icd', 5).append(this._createIcdLookup('icd'))
      .lbl('ICD10').textbox('icd10', 5).append(this._createIcd10Lookup('icd10'))
      .lbl('Snomed').textbox('snomed', 12).append(this._createSnomedLookup('snomed'));
    this.form = ef;
    this.form.setRecord(this.diagnosis);
    _$('de-delete-span').showIf(this.diagnosis.dataDiagnosesId);
    this._statusChange();
  },
  _statusChange:function() {
    if (C_Diagnosis.ACTIVES[this.form.getValue('status')]) {
      _$('spanEndDate').hide();
      if (this.diagnosis.dateClosed == null)
        this.form.setValue('dateClosed', null);  
    } else {
      show('spanEndDate');
      if (this.diagnosis.active)
        this.form.setValue('dateClosed', DateUi.getToday());  
    }
  },
  _createIcdLookup:function(fid) {
    return createA('find', 'ICD9...', this.fpLookupIcd.bind(this, fid), FaceDiagEntry);
  },
  _createIcd10Lookup:function(fid) {
    return createA('find', 'ICD10...', this.fpLookupIcd10.bind(this, fid), FaceDiagEntry);
  },
  _createSnomedLookup:function() {
    var self = this;
    var a = Html.Anchor.create('find', 'Snomed...', function() {
      SnomedPop.pop(self.form.getValue('snomed'), function(rec) {
        self.form.setValue('snomed', rec.snomedCid);
        self.form.setValue('text', rec.snomedFsn);
      })
    })
    return a;
  }
};
DiagReconcile = {
  pop:function(fs, ccdDiags, callback/*(fs)*/) {
    return Html.Pop.singleton_pop.apply(DiagReconcile, arguments);
  },
  create:function() {
    var My = this;
    return Html.Pop.create('Diagnoses Check').extend(function(self) {
      return {
        init:function() {
          Html.Table2Col.create(self.content, 
            self.facetile = My.CheckTile.create('Current Active on Facesheet').bubble('oncheck', self.facetile_oncheck),
            self.ccdtile = My.CheckTile.create('Clinical Document').bubble('oncheck', self.ccdtile_oncheck)).addClass('ReconTable');
          self.recontile = My.ReconTile.create(self.content);
          self.cb = Html.CmdBar.create(self.content)
            .button('Save Reconciled List', self.record_onclick, 'approve', 'save')
            .cancel(self.close);
        },
        onpop:function(fs, ccdDiags, callback) {
          self.fs = fs;
          self.diags = fs.diagnoses;
          self.cdiags = ccdDiags;
          self.callback = callback;
          self.facetile.load(self.diags);
          self.ccdtile.load(self.cdiags);
          self.recontile.reset();
        },
        //
        record_onclick:function() {
          Pop.Confirm.showImportant('This will set facesheet active diagnoses with contents of the reconciled list. Proceed?', null, null, null, null, null, function() {
            var cid = self.fs.clientId;
            var diags = self.recontile.getRecs();
            Ajax.Facesheet.Diagnoses.reconcile(cid, diags, function(fs) {
              self.callback && self.callback(fs);
              self.close();
            })            
          })
        },
        facetile_oncheck:function(diag, on) {
          self.recontile.set(diag, on);
        },
        ccdtile_oncheck:function(diag, on) {
          self.recontile.set(diag, on);
        }
      }
    })
  },
  ReconTile:{
    create:function(container) {
      return Html.Tile.create(container, 'ReconChecklist').extend(function(self) {
        return {
          init:function() {
            var frame = Html.Pop.Frame.create(self, 'Reconciled List');
            self.list = Html.Tile.create(frame, 'Recons');
          },
          reset:function() {
            self.diags = {};
            self.draw();
          },
          set:function(diag, on) {
            if (on) {
              self.diags[diag.text] = diag;
            } else {
              if (self.diags[diag.text])
                delete self.diags[diag.text];
            }
            self.draw();
          },
          draw:function() {
            self.list.clean();
            for (var text in self.diags) {
              Html.Tile.create(self.list).setText(text);
            }
          },
          getRecs:function() {
            return Map.values(self.diags);
          }
        }
      })
    }
  },
  CheckTile:{
    create:function(caption) {
      var My = this;
      return Html.Div.create('MedChecklist').extend(function(self) {
        return {
          init:function() {
            var frame = Html.Pop.Frame.create(self, caption); //, Html.Anchor.create('icheck', 'Check All', self.checkall_onclick));
            self.list = My.CheckList.create(frame).bubble('oncheck', self);
          },
          load:function(recs) {
            self.list.load(recs);
          },
          isAllChecked:function() {
            return self.list.isAllChecked();
          },
          getCheckedNames:function() {
            return self.list.getCheckedNames();
          },
          getCheckedMeds:function() {
            return self.list.getChecked();
          },
          setChecked:function(names) {
            self.list.setChecked(names);
            self.oncheck(self.isAllChecked());
          },
          //
          checkall_onclick:function() {
            self.list.checkAll();
            self.oncheck(true);
          }
        }
      })
    },
    CheckList:{
      create:function(container) {
        var My = this;
        return Html.Tile.create(container).extend(function(self) {
          return {
            oncheck:function(diag, on) {},
            //
            reset:function() {
              self.clean();
            },
            load:function(recs) {
              self.reset();
              self.recs = recs || [];
              self.recs.each(function(rec) {
                if (rec.active)
                  rec.divcheck = Html.DivCheck.create(rec.text).setText('Date: ' + rec.date).into(self).bubble('oncheck', self.dc_oncheck.curry(rec));
              })
            },
            checkAll:function() {
              self.recs.each(function(rec) {
                rec.divcheck.setChecked(true);
              })
            },
            isAllChecked:function() {
              return self.getChecked().length == self.recs.length;
            },
            isAnyChecked:function() {
              return self.getChecked().length > 0;
            },
            getCheckedTexts:function() {
              var texts = [];
              self.getChecked().each(function(rec) {
                texts.push(rec.text);
              })
              return texts;
            },
            setChecked:function(texts) {
              self.recs.each(function(rec) {
                rec.divcheck.setChecked(texts.has(rec.text));
              })
            },
            getChecked:function() {
              var checked = [];
              self.recs.each(function(rec) {
                if (rec.divcheck.isChecked())
                  checked.push(rec);
              })
              return checked;
            },
            //
            dc_oncheck:function(rec, b) {
              self.oncheck(rec, b);
            }
          }
        })
      }
    }
  }
} 