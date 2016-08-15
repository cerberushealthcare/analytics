/**
 * Facesheet Allergies
 * Global static 
 * Requires: TableLoader.js, facesheet.css
 */
var FaceAllergies = {
  fs:null,
  changed:null,
  _scb:null,
  _POP:'fsp-all',
  /*
   * callback(facesheet) if anything changed (calls page.allergiesChangedCallback by default)
   */
  pop:function(fs, zoom, callback) {
    Html.Window.working(true);
    this.fs = fs;
    this.changed = false;
    this._scb = Ajax.buildScopedCallback(callback || 'allergiesChangedCallback');
    var self = this;
    Includer.get(Includer.HTML_FACE_ALLERGIES, function() {
      new TabBar('fsp-all', ['Current Allergies', 'Documented History'], ['Current', 'Documented']);
      if (fs.client) {
        Pop.setCaption('fsp-all-cap-text', fs.client.name + ' - Allergies');
      }
      self._load();
      if (me.isErx() || me.trial) {
        _$('all-cmd-erx').show();
        _$('all-cmd').hide();
      }
      if (! me.Role.erx && ! me.trial) {
        _$('all-recon').invisible();
        _$('all-dleg').invisible();
        _$('all-update').invisible();
      }
      Html.Window.working(false);
      Pop.show(FaceAllergies._POP);
    });
  },
  update:function(fs) {
    this.fs.cuTimestamp = fs.cuTimestamp;
    this.fs.allergies = fs.allergies;
    this.fs.allergiesHistory = fs.allergiesHistory;
    this.fs.activeAllers = fs.activeAllers;
    if (me.Role.erx) {
      this.fs.meds = fs.meds;
      this.fs.activeMeds = fs.activeMeds;
      this.fs.medsHistByMed = fs.medsHistByMed;
      this.fs.medsHistByDate = fs.medsHistByDate;
      this.fs.medsLastReview = fs.medsLastReview;
    }
    this.changed = true;
    this._load();    
  },
  fpClose:function() {
    Pop.close();
    if (this.changed) {
      Ajax.callScopedCallback(this._scb, this.fs);
    }    
  },
  fpNewCrop:function() {
    if (me.trial) {
      Pop.Msg.showCritical('This function is not available for trial users.');
      return;
    }
    page.pNewCrop_compose(this.update.bind(this));
  },
  fpEdit:function(id) {
    Html.Window.working(true);
    var fs = this.fs;
    fs.allergy = id ? fs.activeAllergiesById[id] : null;
    if (fs.aq) {
      this._fpEdit();
    } else {
      var self = this;
      Ajax.Facesheet.Allergies.getQuestion(
        function(q) {
          q.clone = true;
          q.cboOnly = true;
          fs.aq = q;
          self._fpEdit();
        });
    }
  },
  fpReconcile:function() {
    var self = this;
    DocHistoryPop.pop(self.fs, null, function(rec) {
      if (rec && rec._type == 'Electronic') {
        Ajax.Ccd.getAllergies(rec.id, function(allers) {
          DocHistoryPop.close();
          AllergyReconcile.pop(self.fs, allers, rec.date, function(fs) {
            self.update(fs);
          })          
        })
      } else {
        DocHistoryPop.close();
      }
    })    
  },
  _fpEdit:function() {
    var fs = this.fs;
    var q = fs.aq;
    if (fs.allergy) {
      q.cix = 1;
      qSetByValueCombo(q, fs.allergy.agent, fs.allergy.reactions);
    } else {
      q.cix = null;
      q.sel = [];
    }
    var self = this;
    var saveCallback = function(q){self._saveCallback(q)};
    var delCallback = function(q){self._deleteCallback(q)};
    Html.Window.working(false);
    showQuestion(q, null, null, null, saveCallback, delCallback);
  },
  _saveCallback:function(q) {
    var self = this;
    Html.Window.working(true);
    Ajax.Facesheet.Allergies.save(this._buildRec(q), 
      function(fs) {
        self.update(fs);
        Html.Window.working(false);
      });
  },
  _deleteCallback:function(q) {
    var fs = this.fs;
    Html.Window.working(true);
    var self = this;
    Ajax.Facesheet.Allergies.deactivate(fs.allergy.dataAllergyId, 
      function(fs) {
        self.update(fs);
        Html.Window.working(false);
      });
  },  
  _buildRec:function(q) {
    var fs = this.fs;
    var a = {};
    a.id = (fs.allergy) ? fs.allergy.dataAllergyId : null;
    a.clientId = fs.client.clientId;
    a.index = q.sel[0];
    a.agent = qOptText(q.opts[q.sel[0]]);
    a.reactions = toJSONString(qOptTextArray(q.opts, q.sel, 1));
    return a;
  },
  fpDeleteLegacy:function() {
    var fs = this.fs;
    var self = this;
    Pop.Confirm.showYesNo('Remove legacy allergies from active list?', function() {
      Html.Window.working(true);
      Ajax.Facesheet.Allergies.deleteLegacy(fs.client.clientId, 
        function(fs) {
          hide('all-dleg');
          self.update(fs);
        }); 
    });
  },
  fpDeleteChecked:function() {
    var checks = getCheckedValues('sel-all', 'fsp-all-tbody'); 
    var self = this;
    if (checks.length > 0) {
      Pop.Confirm.showYesNo('Mark checked selection(s) as inactive?', function(confirm) {
        if (confirm) {
          Html.Window.working(true);
          Ajax.Facesheet.Allergies.deactivateMany(checks, 
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
  //
  _load:function() {
    var fs = this.fs;
    fs.activeAllergiesById = {};
    var tp = new TableLoader("fsp-all-tbody", "off", "fsp-all-div");
    tp.filterAllLabel = 'Complete (Active and Inactive)';
    tp.defineFilterFn(
      function(allergy) {
        return {'Show':allergy && allergy._active}
      });
    var allergy;
    var i, cls, check;
    if (fs.allergies) {
      //tp.createTr(null, null, {'_status':'Active'});
      for (i = 0; i < fs.allergies.length; i++) {
        allergy = fs.allergies[i];
        fs.activeAllergiesById[allergy.dataAllergyId] = allergy;
        tp.createTr(null, null, allergy);
        if (me.isErx()) {
          if (allergy.source != SOURCE_NEWCROP) {
            // cls = (allergy.active == '1') ? 'fs legacy' : 'fsi';
            if (allergy.active) {
              cls = 'fs legacy';
              var html = allergy.agent;
              if (allergy.reactions)
                html += "<br><span class='lpad2'>Reactions: " + allergy.reactions + "</span>";
              html += "<br><span class='lpad2'>Date: " + allergy._date + "</span>";
              html += "<br><span class='lpad2'>Date Reconciled: " + allergy._dateRecon + "</span>";
              tp.createTd(cls, null, html).colSpan = 3;
              show('all-dleg');
            }
          } else {
            var html = allergy.agent;
            cls = (allergy.active == '1') ? 'fs' : 'fsi';
            /*
            if (allergy.reactions)
              html += " <span class='lpad2'>" + allergy.reactions + "</span>";
            */
            tp.createTd(cls, null, html);
            tp.createTd(null, null, allergy.reactions);
            tp.createTd(null, allergy._status);
          }
        } else {
          var href = "javascript:FaceAllergies.fpEdit(" + allergy.dataAllergyId + ")";
          var html = bulletJoin(allergy.reactions);
          tp.createTd("check");
          if (allergy.active) {
            check = createCheckbox("sel-all", allergy.dataAllergyId);
            tp.append(check);
          }
          tp.createTd();
          cls = (allergy.active) ? 'fs' : 'fsi';
          var a = createAnchor(null, href, cls, allergy.agent)
          tp.append(a, createSpan("lpad", null, null, html));
          tp.createTd(null, allergy._status);
        }
        tp.loadFilterTopbar('all-filter-ul');
      }
    } else {
      tp.createTrTd(null, null, "&nbsp;");
    }
    tp.setFilterTopbar({'Show':'Active Only'});
    _$('all-cmd-left').showIf(check);
    t = new TableLoader("fsp-allh-tbody", "off", "fsp-allh-div");
    if (fs.allergiesHistory) {
      for (i = 0; i < fs.allergiesHistory.length; i++) {
        allergy = fs.allergiesHistory[i];
        t.createTr(allergy.date, [allergy.date, allergy.sessionId]);
        t.createTd("histbreak nowrap", allergy.date);
        t.createTdAppend(null, FaceUi.createSessionAnchor(fs, allergy.sessionId));
        t.createTd(null, null, this._getAllergyText(allergy));
      }
    }
    Html.Window.working(false);
  },
  _getAllergyText:function(a) {
    var text = "<b>" + a.agent + "</b>";
    if (! Array.isEmpty(a.reactions)) {
      text += ": " + a.reactions.join(", ");
    }
    return text;
  },
  _flicker:function() {
    flicker("allh-head");
  }
};
AllergyReconcile = {
    pop:function(fs, ccdAllers, date, callback/*(fs)*/) {
      return Html.Pop.singleton_pop.apply(AllergyReconcile, arguments);
    },
    create:function() {
      var My = this;
      return Html.Pop.create('Allergy Check').extend(function(self) {
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
          onpop:function(fs, ccdAllers, date, callback) {
            self.fs = fs;
            self.allers = fs.allergies;
            self.callers = ccdAllers;
            self.callback = callback;
            self.facetile.load(self.allers);
            self.ccdtile.load(self.callers);
            self.ccdtile.setCaption('Clinical Document: ' + date);
            self.recontile.reset();
          },
          //
          record_onclick:function() {
            Pop.Confirm.showImportant('This will set facesheet active allergies with contents of the reconciled list. Proceed?', null, null, null, null, null, function() {
              var cid = self.fs.clientId;
              var allers = self.recontile.getRecs();
              Ajax.Facesheet.Allergies.reconcile(cid, allers, function(fs) {
                self.callback && self.callback(fs);
                self.close();
              })            
            })
          },
          facetile_oncheck:function(aller, on) {
            self.recontile.set(aller, on);
          },
          ccdtile_oncheck:function(aller, on) {
            self.recontile.set(aller, on);
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
              self.allers = {};
              self.draw();
            },
            set:function(aller, on) {
              if (on) {
                self.allers[aller.agent] = aller;
              } else {
                if (self.allers[aller.agent])
                  delete self.allers[aller.agent];
              }
              self.draw();
            },
            draw:function() {
              self.list.clean();
              for (var text in self.allers) {
                Html.Tile.create(self.list).setText(text);
              }
            },
            getRecs:function() {
              return Map.values(self.allers);
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
              self.frame = Html.Pop.Frame.create(self, caption); //, Html.Anchor.create('icheck', 'Check All', self.checkall_onclick));
              self.list = My.CheckList.create(self.frame).bubble('oncheck', self);
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
            getCheckedAllers:function() {
              return self.list.getChecked();
            },
            setChecked:function(names) {
              self.list.setChecked(names);
              self.oncheck(self.isAllChecked());
            },
            setCaption:function(text) {
              self.frame.setCaption(text);
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
                    rec.divcheck = Html.DivCheck.create(rec.agent).setText('Reaction: ' + (rec.reactions || '')).setText2('Date: ' + (rec.date || rec._date)).into(self).bubble('oncheck', self.dc_oncheck.curry(rec));
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
                  rec.divcheck.setChecked(names.has(rec.agent));
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