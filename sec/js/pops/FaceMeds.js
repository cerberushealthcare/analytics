/**
 * Facesheet Meds
 * Global static 
 * Requires: TableLoader.js, facesheet.css
 */
FaceMeds = {
  fs:null,
  changed:null,
  //
  _q:null,
  _scb:null,
  _filter:null,
  _zoom:null,  
  /*
   * callback(facesheet) if anything changed (calls page.medsChangedCallback by default)
   */
  pop:function(fs, zoom, callback) {
    Html.Window.working(true);
    this.fs = fs;
    this._zoom = zoom;
    this.changed = false;
    this._scb = Ajax.buildScopedCallback(callback || 'medsChangedCallback');
    var self = this;
    Includer.get(Includer.HTML_FACE_MEDS, function() {
      //self._q = newMedQuestion(); 
      self.tabbar = new TabBar('fsp-med', ['Current Medications'], ['Current']);
      if (fs.client) 
        Pop.setCaption('fsp-med-cap-text', fs.client.name + ' - Medications');
      if (! me.Role.Patient.diagnoses) {
        _$('med-recon').invisible();
      }
      if (fs.medsHistByDate) {
        self._pop();
      } else {
        Ajax.Facesheet.Meds.getHist(fs.client.clientId, 
          function(f) {
            fs.medsHistByDate = f.medsHistByDate;
            fs.clientHistory = f.clientHistory;
            self._pop();
          });
      }
    });
  },
  _pop:function() {
    if (! me.Role.erx) {
      _$('med-dleg').invisible();
      _$('med-acts').invisible();
    }
    this._load();
    Pop.show('fsp-med');
  },
  update:function(fs) {
    this.fs.cuTimestamp = fs.cuTimestamp;
    this.fs.meds = fs.meds;
    this.fs.activeMeds = fs.activeMeds;
    this.fs.medsHistByMed = fs.medsHistByMed;
    this.fs.medsHistByDate = fs.medsHistByDate;
    this.fs.medsLastReview = fs.medsLastReview;
    if (me.Role.erx) {
      this.fs.allergies = fs.allergies;
      this.fs.allergiesHistory = fs.allergiesHistory;
      this.fs.activeAllers = fs.activeAllers;
    }
    this.changed = true;
    this._load();
  },
  fpReconcile:function() {
    var fs = this.fs;
    var cap = 'Record these medications as reconciled?';
    var self = this;
    Pop.Confirm.showYesNo(cap, function() {
      Html.Window.working(true);
      Ajax.Facesheet.Meds.reconcile2(fs.client.clientId,
        function(fs) {
          self.update(fs);
        });
    });
  },
  fpReconcile_old2:function() {
    var self = this;
    DocHistoryPop.pop(self.fs, null, function(rec) {
      if (rec && rec._type == 'Electronic') {
        Ajax.Ccd.getMeds(rec.id, function(meds) {
          DocHistoryPop.close();
          MedReconcile.pop(self.fs, meds, function(fs) {
            self.update(fs);
          })          
        })
      } else {
        DocHistoryPop.close();
      }
    })    
  },
  fpReconcile_old:function() {
    var self = this;
    FaceMedsReconcile.pop(this.fs, function(fs) {
      self.tabbar.select(0);
      self.update(fs);
    })
  },
  fpClose:function() {
    this.tabbar.select(0);
    Pop.close();
    if (this.changed) 
      Ajax.callScopedCallback(this._scb, this.fs);
  },
  fpNewCrop:function() {
    if (me.trial) {
      Pop.Msg.showCritical('This function is not available for trial users.');
      return;
    }
    page.pNewCrop_compose(this.update.bind(this));
  },
  fpDeleteLegacy:function() {
    var fs = this.fs;
    var cap = 'Remove imported medications from active list?';
    if (! fs.activeErx)
      cap += '<br><br>Warning: These have not yet been transferred to NewCrop as current medications.';
    var self = this;
    Pop.Confirm.showYesNo(cap, function() {
      Html.Window.working(true);
      Ajax.Facesheet.Meds.deleteLegacy(fs.client.clientId,
        function(fs) {
          hide('med-dleg');
          self.update(fs);
        });
    });
  },
  fpDeleteMeds:function() {
    if (getCheckedValues('sel-med', 'fsp-med-tbody').length > 0) {
      var self = this;
      Pop.Confirm.showDeleteChecked('remove', function(confirm) {
        if (confirm)
          self._deactivateMedChecks();
      });  
    } else {
      Pop.Msg.showCritical('Nothing was selected.');
    }
  },
  fpNone:function() {
    if (this.fs.activeMeds) 
      Pop.Confirm.showImportant('This will deactivate all active meds and replace with "None Active". Proceed?', null, null, null, null, null, this.setToNone.bind(this))
    else
      this.setToNone();
  },
  setToNone:function() {
    var self = this;
    Html.Window.working(true);
    Ajax.Facesheet.Meds.setNone(this.fs.clientId, function(fs) {
      self.update(fs);
      Html.Window.working(false);
    });
  },
  fpPrint:function() {
    var fs = this.fs;
    var checkedIds = getCheckedValues('sel-med', 'fsp-med-tbody');
    for (var i = 0; i < fs.activeMeds.length; i++) 
      fs.activeMeds[i].checked = null;
    for (var i = 0; i < checkedIds.length; i++) 
      fs.activeMedsById[checkedIds[i]].checked = true;
    var rx = {
      'date':DateUi.getToday(1),
      'me':me,
      'docs':fs.docs,
      'client':fs.client,
      'meds':fs.activeMeds,
      'showMedList':true};
    var self = this;
    showRx(rx, function(meds){self._rxCallback(meds)});
  },
  fpAddMed:function() {
    this._q = newMedQuestion();
    this._q.id = null;
    this._q.med = null;
    this._showMedQuestion();
  },
  fpShowMed:function(id) {
    var m = this.fs.activeMedsById[id];
    var med = {
      'id':m.dataMedId,
      'name':m.name,
      'amt':m.amt,
      'freq':m.freq,
      'asNeeded':m.asNeeded,
      'meals':m.meals,
      'route':m.route,
      'length':m.length,
      'disp':m.disp};
    this._q = newMedQuestion();
    this._q.id = id;
    this._q.med = med;
    this._showMedQuestion();
  },
  fpShowMedRx:function(id) {
    var med = this.fs.rxById[id];
    var text = (med.text) ? med.text + '<br/>' : '';
    var h = '<b>' + med.name + '</b><br/>' + text + med.rx;
    Pop.Msg.showInfo(h);
  },
  deactivateMeds:function(ids) {
    Html.Window.working(true);
    Ajax.Facesheet.Meds.deactivateMany(ids, [this.update, this]);
  },
  saveMed:function(med) {
    Html.Window.working(true);
    med.clientId = this.fs.client.clientId;
    Ajax.Facesheet.Meds.save(med, [this.update, this]);
  },
  //
  _load:function() {
    this._loadCurrent(me.isErx());
//    this._loadHist();
    this._loadLastReview();
    if (me.isErx()) {
      _$('med-cmd-erx').show();
      _$('med-cmd').hide();
    }
    Html.Window.working(false);
  },
  _deactivateMedChecks:function() {
    this.deactivateMeds(getCheckedValues('sel-med', 'fsp-med-tbody'));
  },
  _showMedQuestion:function() {
    var self = this;
    showMedByQuestion(this._q, function(q){self._showMedQuestionCallback(q)});
  },
  _showMedQuestionCallback:function(q) {
    if (q.med)
      this.saveMed(q.med);
    else if (q.id)
      this.deactivateMeds([q.id]);
  },
  _rxCallback:function(meds) {
    Html.Window.working(true);
    Ajax.Facesheet.Meds.printRx(meds, [this.update, this]);
  },
  _loadLastReview:function() {
    var span = _$('reviewed_span');
    var rec = Map.first(this.fs.medsLastReview);
    if (rec) {
      var s = rec.dateUpdated + ' by ' + rec.User.name;
      span.setText(s);
    } else {
      span.setText('[Not recorded]');
    }
  },
  _loadCurrent:function(erx) {
    _$('med-none').show();
    var fs = this.fs;
    var tp = new TableLoader('fsp-med-tbody', 'off', 'fsp-med-div');
    fs.activeMedsById = {};
    fs.rxById = {};
    if (fs.meds) {
      tp.filterAllLabel = 'Complete (Active and Inactive)';
      tp.defineFilterFn(
        function(med) {
          return {'Show':med && med._active}
        });
      var self = this;
      for (var i = 0; i < fs.meds.length; i++) {
        var med = fs.meds[i];
        if (med.active)
          fs.activeMedsById[med.dataMedId] = med;
        tp.createTr(null, null, med);
        var name = (! med.expires) ? med.name : bulletJoin([med.name,' <span>' + med.expireText + '</span>']);
        if (med._none) {
          _$('med-none').hide();
          tp.createTd();
          tp.createTd('fsb', med._name);
          tp.createTd();
        } else {
          if (med.active) {
            if (erx) {
              _$('med-none').hide();
              if (med.source != SOURCE_NEWCROP) {
                tp.createTd();
                tp.createTd('fs legacy', null, '[IMPORTED] ' + name);
                show('med-dleg');
              } else {
                tp.createTd();
                if (med._diet)
                  tp.createTd('diet');
                else
                  tp.createTd();
                tp.append(Html.Span.create('sfs', name));
              }
              tp.append(Html.Span.create('lpad2', med.text));
              if (med.index)
                tp.createTdAppend(null, InfoButton.forMed(med.index, fs.clientId));
              else
                tp.createTdAppend(null, null);              
              tp.createTd(null, med._status);
            } else {
              tp.createTd('check');
              tp.append(createCheckbox('sel-med', med.dataMedId));
              var href = 'javascript:FaceMeds.fpShowMed' + argJoin([med.dataMedId]);
              tp.createTd('nowrap');
              tp.append(createAnchor('med-a-' + med.dataMedId, href, 'fs', null, name));
              tp.append(Html.Span.create('lpad', med.text));
              tp.createTd(null, med._status);
              tp.createTdAppend(null, null);              
            }
          } else {
            if (erx) {
              tp.createTd();
              tp.createTd('fsiw', name);
              tp.append(Html.Span.create('lpad3', med.text));
              tp.createTd(null, med._status);
            }
          }
        }
      }
      tp.loadFilterTopbar('med-filter-ul');
      tp.setFilterTopbar({'Show':'Active Only'});
    }
  },
  _medHistFilter:function(med) {
    return {
      'Show':(med) ? QPopLegacyMed.justMedName(med.name) : null};
  },
  _loadHist:function() {
    var fs = this.fs;
    var self = this;
    var t = new TableLoader('fsp-medh-tbody-1', 'off', 'fsp-medh-div-1');
    if (fs.medsHistByDate) 
      t.batchLoad(fs.medsHistByDate, function(tl, med){self._renderMedsHistByDateRow(tl, med)}, true);
    t.defineFilter(this._medHistFilter());
    return;
    t = new TableLoader('fsp-medh-tbody-2', 'off', 'fsp-medh-div-2', 'fsp-medh-head-2');
    fs.tl_hist = t;
    t.defineFilter(this._medHistFilter(), 
      function(t) {
        if (t.allFilterValuesNull()) 
          showHide('fsp-medh-div-1','fsp-medh-div-2');
        else
          showHide('fsp-medh-div-2','fsp-medh-div-1');
      });
    if (fs.medsHistByMed)
      t.batchLoad(fs.medsHistByMed, function(tl, med){self._renderMedsHistByMedRow(tl, med)}, true);
  },
  _renderMedsHistByDateRow:function(t, med) {
    var fs = this.fs;
    if (med) {
      var brk = med.date + med.quid + med.name + med.rx + med.sessionId;
      if (brk != fs.medHistByDateBrk) {
        if (med.rx) 
          fs.rxById[med.dataMedId] = med;
        t.createTr(med.date, [med.date, med.quid + med.name, med.rx], this._medHistFilter(med));
        t.createTd('histbreak', med.date);
        t.createTd(null, null, med.quid + ': <b>' + med.name + '</b>');
        this._appendRxAnchor(t, med);  
        t.createTdAppend(null, FaceUi.createSessionAnchor(fs, med.sessionId, false, med.source));
      }
      fs.medHistByDateBrk = brk;
    } else {
      fs.medHistByDateBrk = null;
      t.loadFilterTopbar('medh-filter-ul');
      t.applyFilter(null);
      t.flicker();
    }
  },
  _renderMedsHistByMedRow:function(t, med) {
    var fs = this.fs;
    if (med) {
      var brk = med.name + med.date + med.quid + med.rx + med.sessionId;
      if (brk != fs.medHistByMedBrk) {
        if (med.rx) 
          fs.rxById[med.dataMedId] = med;
        var date = med.date;
        t.createTr(med.name, [med.name, med.date, med.quid, med.rx], this._medHistFilter(med));
        var c = (med.active) ? 'histbreak active' : 'histbreak inactive';
        t.createTd(c, null, med.name);
        t.createTd('nowrap', date);
        t.createTd(null, med.quid);
        this._appendRxAnchor(t, med);
        t.createTd();
        t.append(FaceUi.createSessionAnchor(fs, med.sessionId, false, med.source));
      }
      fs.medHistByMedBrk = brk;
    } else {
      //t.loadFilterSidebar('medh-filter-ul', TableLoader.NO_FILTER_COUNT);
      t.loadFilterTopbar('medh-filter-ul');
      t.applyFilter(null);
      t.flicker();
      fs.medHistByMedBrk = null;
    }
  },
  _appendRxAnchor:function(t, med) {
    t.createTd();
    if (med.rx) {
      var click = 'FaceMeds.fpShowMedRx(' + med.dataMedId + ')';
      var text = '&nbsp;';
      var s = med.rx.split('Disp:');
      if (s.length > 1)
        text = 'Disp:' + s[1];
      if (text.substr(text.length - 1) == ')')
        text = text.substr(0, text.length - 1);
      var a = createAnchor(null, null, 'rx', null, text, click);
      t.append(a);
      a.title = med.rx;
    }
  }
};
//
MedReconcile = {
  pop:function(fs, ccdMeds, callback/*(fs)*/) {
    return Html.Pop.singleton_pop.apply(MedReconcile, arguments);
  },
  create:function() {
    var My = this;
    return Html.Pop.create('Meds Check').extend(function(self) {
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
        onpop:function(fs, ccdMeds, callback) {
          self.fs = fs;
          self.meds = fs.meds;
          self.cmeds = ccdMeds;
          self.callback = callback;
          self.facetile.load(self.meds);
          self.ccdtile.load(self.cmeds);
          self.recontile.reset();
        },
        //
        record_onclick:function() {
          Pop.Confirm.showImportant('This will set facesheet active medications with contents of the reconciled list. Proceed?', null, null, null, null, null, function() {
            var cid = self.fs.clientId;
            var meds = self.recontile.getRecs();
            Ajax.Facesheet.Meds.reconcile(cid, meds, function(fs) {
              self.callback && self.callback(fs);
              self.close();
            })            
          })
        },
        facetile_oncheck:function(med, on) {
          self.recontile.set(med, on);
        },
        ccdtile_oncheck:function(med, on) {
          self.recontile.set(med, on);
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
            self.meds = {};
            self.draw();
          },
          set:function(med, on) {
            if (on) {
              self.meds[med.name] = med;
            } else {
              if (self.meds[med.name])
                delete self.meds[med.name];
            }
            self.draw();
          },
          draw:function() {
            self.list.clean();
            for (var text in self.meds) {
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
            oncheck:function(rec, on) {},
            //
            reset:function() {
              self.clean();
            },
            load:function(recs) {
              self.reset();
              self.recs = recs || [];
              self.recs.each(function(rec) {
                if (rec.active)
                  rec.divcheck = Html.DivCheck.create(rec.name).setText('Date: ' + rec.date).into(self).bubble('oncheck', self.dc_oncheck.curry(rec));
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
            setChecked:function(names) {
              self.recs.each(function(rec) {
                rec.divcheck.setChecked(names.has(rec.name));
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
FaceMedsReconcile_Old = {
  /*
   * @arg Facesheet fs
   * @arg fn(fs) onupdate
   */
  pop:function(fs, onupdate) {
    return Html.Pop.singleton_pop.apply(FaceMedsReconcile, arguments);
  },
  create:function(container) {
    var My = this;
    return Html.Pop.create('Medication Check', 750).extend(function(self) {
      return {
        init:function() {
          self.content.addClass('MedReconPop');
          Html.Table2Col.create(self.content, 
            self.checktile = My.CheckTile.create().bubble('oncheck', self.checktile_oncheck),
            self.recontile = My.ReconTile.create());
          self.cb = Html.SplitCmdBar.create(self.content)
            .spacer()
            .button('Update List...', self.erx_onclick, 'erx')
            .split()
            .button('Record as Reviewed/Reconciled', self.record_onclick, 'approve', 'save')
            .cancel(self.close).spacer();
        },
        reset:function() {
          self.updated = false;
        },
        onshow:function(fs, onupdate) {
          self.reset();
          self.callback = onupdate;
          self.load(fs);
        },
        load:function(fs) {
          self.fs = fs;
          Array.each(fs.activeMeds, function(med) {
            if (fs.medsLastReview[med.name])
              med._reviewed = fs.medsLastReview[med.name];
          })
          self.checktile.load(fs.activeMeds);
          self.recontile.load(fs);
          self.cb.disable('save');
        },
        record_onclick:function() {
          Ajax.Facesheet.Meds.saveReviewed(self.fs.client.clientId, self.checktile.getCheckedMeds(), function(fs) {
            self.fs = fs;
            self.updated = true;
            self.close();
          })
        },
        erx_onclick:function() {
          var names = self.checktile.getCheckedNames();
          page.pNewCrop_compose(function(fs) {
            self.work(function() {
              self.updated = true;
              self.load(fs);
              self.checktile.setChecked(names);
            })
          })
        },
        checktile_oncheck:function(isAllChecked) {
          self.cb.disable('save', ! isAllChecked);
        },
        onclose:function() {
          if (self.updated) {
            self.callback.async(self.fs);
          }
        }
      }
    })
  },
  CheckTile:{
    create:function() {
      var My = this;
      return Html.Div.create('MedChecklist').extend(function(self) {
        return {
          oncheck:function(isAllChecked) {},
          //
          init:function() {
            var frame = Html.Pop.Frame.create(self, 'Current Medication(s)', Html.Anchor.create('icheck', 'Check All', self.checkall_onclick));
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
            oncheck:function(isAllChecked) {},
            //
            reset:function() {
              self.clean();
            },
            load:function(recs) {
              self.reset();
              self.recs = recs || [];
              self.recs.each(function(rec) {
                rec.divcheck = Html.DivCheck.create(rec.name).setText(rec.text).into(self).bubble('oncheck', self.dc_oncheck);
                if (rec._reviewed) 
                  rec.divcheck.setContent('Last Reviewed: ' + rec._reviewed.dateUpdated + ' by ' + rec._reviewed.User.name);
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
            getCheckedNames:function() {
              var names = [];
              self.getChecked().each(function(rec) {
                names.push(rec.name);
              })
              return names;
            },
            setChecked:function(names) {
              self.recs.each(function(rec) {
                rec.divcheck.setChecked(names.has(rec.name));
              })
            },
            getChecked:function() {  // returns [Med,..]
              var checked = [];
              self.recs.each(function(rec) {
                if (rec.divcheck.isChecked())
                  checked.push(rec);
              })
              return checked;
            },
            //
            dc_oncheck:function(b) {
              if (b)
                self.oncheck(self.isAllChecked());
              else
                self.oncheck(false);
            }
          }
        })
      }
    }
  },
  ReconTile:{
    create:function() {
      return Html.Div.create('MedRecon').extend(function(self) {
        return {
          init:function() {
            self.frame = Html.Pop.Frame.create(self, '', Html.Anchor.create(null, 'Clear', self.reset));
            self.frame.cap.invisible();
            self.empty = Html.Tile.create(self.frame, 'Empty').add(Html.Anchor.create(null, 'Select document...', self.select_onclick).noFocus());
          },
          load:function(fs) {
            self.fs = fs;
          },
          reset:function() {
            if (self.viewing) 
              self.viewing.hide();
            self.viewing = null;
            self.empty.show();
          },
          //
          select_onclick:function() {
            DocHistoryPop.pop(self.fs, null, function(rec) {
              DocHistoryPop.close();
              self.empty.hide();
              var view = DocView.from(rec);
              if (self.viewing)
                self.viewing.hide();
              self.viewing = view.create(self.frame, rec, true).show();
            })
          }
        }
      })
    }
  }
}
