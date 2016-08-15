/**
 * RecordEntryPop ProcEntry 
 */
ProcEntry = {
  /*
   * @arg Proc rec
   * @arg bool asLabPanel (optional)
   * @arg bool hideRecon (optional, to hide order reconciliation)
   */
  pop:function(rec, asLabPanel, hideRecon) {
    return Html.Pop.singleton_pop.apply(ProcEntry, arguments);
  },
  pop_asHistory:function(rec) {
    return ProcEntry.pop(rec, null, true);
  },
  create:function() {
    var My = this;
    return Html.DirtyEntryPop.create('Procedure Entry', 700).withFrame().extend(function(self) {
      return {
        onsave:function(rec) {},
        onresultsave:function() {},
        ondelete:function(id) {},
        //
        init:function() {
          //self.form = Html.UlEntry.create(self.frame).extend(My.Form);
          //Html.CmdBars.create(self.frame, [
          //  self.cb = Html.CmdBar.asSaveDelCancel(self.frame, self),
          //  self.cbPanel = Html.CmdBar.asSaveCancel(self.frame, self, 'Save and Enter Results...')]);
          self.table = My.ResultTable.create(self.content).hide()
            .bubble('onupdate', self.table_onupdate);
          self.ErrorBox = Html.Pop.ErrorBox.create(self);
          Ipcs.ajax().fetchAll();
        },
        initForm:function(callback) {
          self.form = Html.UlEntry.create(self.frame).extend(My.Form);
          Html.CmdBars.create(self.frame, [
            self.cb = Html.CmdBar.asSaveDelCancel(self.frame, self),
            self.cbPanel = Html.CmdBar.asSaveCancel(self.frame, self, 'Save and Enter Results...')]);
          self.form.focus();
          callback();
        },
        reset:function() {
          self.rec = null;
          self.asLabPanel = null;
          self.hideRecon = null;
          self.resultsave = null;
          self.table.reset();
        },
        load:function(rec, asLabPanel, hideRecon) {
          if (self.form == null) {
            async(function() {
              self.initForm(self.load.curry(rec, asLabPanel, hideRecon));
            })
            return;
          }
          self.rec = rec;
          self.asLabPanel = asLabPanel;
          self.hideRecon = hideRecon;
          self.table.proc = rec;
          self.draw();
        },
        draw:function() {
          if (self.asLabPanel) 
            self.cbPanel.select();
          else 
            self.cb.select().showDelIf(self.rec.procId);
          self.form.load(self.rec, self.asLabPanel, self.hideRecon);
          self.table.showIf(! self.rec.isNew());
          if (! self.rec.isNew())
            self.table.load(self.rec);
          self.reposition();
        },
        onerror:function(e) {
          self.ErrorBox.show(e.message);
        },
        onshow:function() {
          //self.form.focus();
          self.ErrorBox.hide();
          if (self.asLabPanel && ! self.rec.isNew())
            self.table.edit_first();
        },
        onclose:function() {
          if (self.resultsave) 
            self.onresultsave();
        },
        close_asSaved:self.close_asSaved.append(function(rec) {
          if (self.rec.isNew() && self.asLabPanel)
            ProcEntry.pop(rec, true);
        }),
        table_onupdate:function() {
          self.resultsave = true;
        }
      }
    })
  },
  Form:function(self) {
    return {
      init:function() {
        self.build()
          .line().id('top')
            .l('Date').datetime('date').datetime('dateTo')
            .start('ef-recon')
              .l('Reconcile To').ui('Order', OrderPicker, self.order_onset)
            .end()
          .line()
            .l('Name').pick('ipc', 'Ipc', IpcPicker)
          .line()
            .l('Type/Loc').textbox('location', 40)
          .line()
            .l('With').pick('providerId', 'Provider', ProviderPicker, self.provider_onset)
            .ln('at').pick('addrFacility', 'Facility', FacilityPicker)
          .line().id('qr0')
            .l('Comments').textarea('comments')
          .line('mt5').id('qr1')
            .lh3('Quick Result (Optional)')
          .line().id('qr2')
            .l('Value').textbox('value', 5)
            .l('Units').textbox('valueUnit', 5)
            .l('Interpretation').select('interpretCode', C_ProcResult.INTERPRET_CODES, '')
          .line().id('qr4')
            .l('Comments').textarea('rcomments');
      },
      onbeforeload:function(rec, asLabPanel, hideOrder) {
        self.asLabPanel = asLabPanel;
        self.$('ef-recon').hide();
        self.dti2 = self.$('top').children[2].dateTimeInput; 
        self.dti2.hideDateInput();
        if (! hideOrder) {
          self.$('Order').setClientId(rec.clientId, function(recs) {
            self.$('ef-recon').show();
          })
        }
      },
      draw:function() {
        var showQuickResults = self.rec.isNew() && ! self.asLabPanel;
        self.$('qr0').hideIf(showQuickResults);
        self.$('qr1').showIf(showQuickResults);
        self.$('qr2').showIf(showQuickResults);
        self.$('qr4').showIf(showQuickResults);
      },
      provider_onset:function(rec) {
        if (rec && rec.Address_addrFacility) 
          self.setValue('addrFacility', rec.Address_addrFacility);
      },
      order_onset:function(rec) {
        self.setValue('ipc', rec.Ipc);
        self.setValue('providerId', rec.Provider_schedWith);
        self.setValue('addrFacility', rec.Address_schedLoc);
      },
      getRecord:self.getRecord.extend(function(_getRecord) {
        var rec = _getRecord();
        var date = rec.date && rec.date.split(' ')[0];
        var time = self.dti2.getTimeText();
        if (date && time) {
          rec.dateTo = date + ' ' + time;
        } else {
          rec.dateTo = null;
        }
        return rec;
      })
    }
  },
  ResultTable:{
    create:function(container) {
      var tile = Html.Tile.create(container, 'mt10');
      return Html.TableLoader.create(tile, 'fsgr').noWorking().extend(this, function(self, parent) {
        return {
          onupdate:function() {},
          //
          init:function() {
            self.setHeight(200);
            self.thead().trFixed().th('Result(s)').w('20%').th('Value').w('15%').th('Range').w('15%').th('Interpret').w('15%').th('Comments').w('35%');
          },
          load:function(proc) {
            if (proc) {
              self.proc = proc;
              parent(Html.TableLoader).load(proc.ProcResults);
            } else {
              self.recs = null;
              parent(Html.TableLoader).load();
            }
          },
          fetch:function(callback_recs) {
            if (self.recs) 
              callback_recs(self.recs)
            else if (self.proc)
              Ajax.Procedures.get(self.proc.procId, function(proc) {
                self.proc = proc;
                callback_recs(self.proc.ProcResults);
              })
          },
          hide:function() {
            tile.hide();
            return self;
          },
          showIf:function(b) {
            tile.showIf(b);
            return self;
          },
          edit:function(rec, asLabPanel) {
            if (rec == null) 
              rec = ProcResult.asNew(self.proc, asLabPanel);
            ProcResultEntry.pop(rec, self.proc, asLabPanel)
              .bubble('onsave', self.pop_onupdate)
              .bubble('ondelete', self.pop_onupdate);
          },
          edit_first:function() {
            if (Array.isEmpty(self.proc.ProcResults))
              self.edit(null, true);
            else
              self.edit(self.proc.ProcResults.first(), true);
          },
          rowKey:function(rec) {
            return rec.procResultId;
          },
          add:function(rec, tr) {
            tr.select(AnchorProc).td(String.denull(rec.value) + ' ' + String.denull(rec.valueUnit)).td(rec.range).td(C_ProcResult.INTERPRET_CODES[rec.interpretCode]).td(rec.comments);
          },
          ondraw:function() {
            self.tbody().tr().td(Html.AnchorAction.asNew('Add Result...').bubble('onclick', self.onselect.curry(null)), 'cj').colspan(5);
          },
          onselect:function(rec) {
            self.edit(rec);
          },
          pop_onupdate:function() {
            self.onupdate();
            self.load();
          }
        }
      })
    }
  }
}
/**
 * RecordEntryPop ProcResultEntry
 */
ProcResultEntry = {
  pop:function(rec, proc, asLabPanel) {
    return this.create().pop(rec, proc, asLabPanel);
  },
  create:function() {
    return ProcResultEntry = Html.RecordEntryDeletePop.create('Results Entry', 600).extend(function(self) {
      return {
        onsave:function() {},
        ondelete:function() {},
        //
        buildForm:function(ef) {
          ef.li('Name').picker(IpcPicker, 'ipc', 'Ipc');
          ef.li('Value', 'mt10').textbox('value', 5).lbl('Units').textbox('valueUnit', 5).lbl('Range').textbox('range', 20);
          ef.li('Interpretation').select('interpretCode', C_ProcResult.INTERPRET_CODES, '');
          ef.li('Comments').textarea('comments', 6);
        }, 
        buildCmd:function(cb) {
          if (self.next || self.asLabPanel)
            cb.save(self.saveplus_onclick, 'Save and Next...').save(self.save_onclick, 'Save and Exit').del(self.del_onclick).exit(self.cancel_onclick);
          else
            cb.save(self.save_onclick, 'Save and Exit').del(self.del_onclick).exit(self.cancel_onclick);
        },
        isDeletable:function(rec) {
          return ! rec.isNew();
        },
        onpop:function(rec, proc, asLabPanel) {
          self.proc = proc;
          self.asLabPanel = asLabPanel;
          self.cmd.reset();
          self.saveAndNext = null;
          self.next = proc.ProcResults && proc.ProcResults.after(rec);
        },
        onshow:function(rec) {
          self.form.focus(rec.ipc ? 'value' : 'ipc');
        },
        save:function(rec, onsuccess, onerror) {
          rec.ajax().save(onsuccess, onerror);
        },
        saveplus_onclick:function() {
          self.saveAndNext = true;
          self.save_onclick();
        },
        close_asSaved:self.close_asSaved.append(function(rec) {
          if (self.saveAndNext) {
            var proc = Proc.revive(rec.Proc);
            if (! self.next)
              self.next = ProcResult.asNew(proc);
            ProcResultEntry.pop(self.next, proc, self.asLabPanel);
          }
        }),
        remove:function(rec, onsuccess) {
          Ajax.Procedures.deleteResult(rec.procResultId, onsuccess); 
        }
      }
    })
  }
}
//
/*
ProcPanelEntry.pop(self.fs, '3208')
.bubble('onsave', self.pop_onsave);
*/
ProcPanelSelector = {
  //
  pop:function(fs, onsave) {
    if (this.q)
      this._popQ(fs, onsave);
    else
      Ajax.Templates.getIols(Html.Window, function(iols) {
        ProcPanelSelector.q = Question.asDummy_fromMap('Lab Panel Selector', iols);
        ProcPanelSelector._popQ(fs,onsave);
      })  
  },
  _popQ:function(fs, onsave) {
    ProcPanelSelector.q.pop(function(pid) {
      ProcPanelEntry.pop(fs, pid).bubble('onsave', onsave);
    })
  }
}
//
ProcPanelEntry = {
  pop:function(fs, pid) {
    return Html.Pop.singleton_pop.apply(ProcPanelEntry, arguments);
  },
  create:function() {
    var My = this;
    return Html.DirtyPop.create('Panel Entry').withFrame('New Panel').extend(function(self) {
      return {
        onsave:function() {},
        //
        init:function() {
          Html.Pop.CmdBar.create(self).saveCancel('Save Results');
        },
        onshow:function(fs, pid) {
          self.reset();
          IolEntry.ajax(self.frame).fetch(pid, function(iol) {
            self.frame.setCaption('New Panel: ' + iol.Ipc.name);
            self.Tui = My.Tui.create(self.frame.clean(), fs, iol);
          })
        },
        save_onclick:function() {
          self.save();
        },
        save:function() {
          var proc = self.Tui.getProc();
          proc.ajax(self.frame).save(self.close_asSaved);
        },
        reset:function() {
          Html.Tile.create(self.frame.clean()).setDim(200, 500);
        }
      }
    })
  },
  Tui:{
    create:function(container, fs, iol) {
      return Html.TemplateUi.create(container, fs, iol.getTuiFormat()).setWidth(500).extend(function(self) {
        return {
          init:function() {
            if (iol.isTypePar())
              self.setHeight(200);
            self.load(iol.Par);
          },
          getProc:function() {
            return iol.asProc(fs.clientId, self);
          },
          onchange:function(q) {
            if (iol.isTypeForm() && q._next)
              self.pop(q._next);
          }
        }
      })
    }
  }
}
IolEntry = Object.Rec.extend({
  /*
   type
   Ipc
   Par
   dateQid
   //
   Question_Date
   */
  _cache:{},
  //
  getProto:function(json) {
    switch (json.type) {
      case C_IolEntry.TYPE_FORM:
        return IolEntry_Form;
      case C_IolEntry.TYPE_PAR:
        return IolEntry_Par;
    }
  },
  onload:function() {
    this.setr('Par', IolPar);
    this.Question_Date = this.Par.getQuestion(this.dateQid);
  },
  isTypePar:function() {
    return this.type == C_IolEntry.TYPE_PAR;
  },
  isTypeForm:function() {
    return this.type == C_IolEntry.TYPE_FORM;
  },
  //
  ajax:function(worker) {
    var self = this;
    return {
      fetch:function(pid, callback) {
        if (IolEntry._cache[pid]) {
          callback(IolEntry._cache[pid]);
        } else {
          Ajax.Templates.getIolEntry(pid, worker, function(entry) {
            callback(IolEntry._cache[pid] = entry);
          })
        }
      }
    }
  }
})
IolEntry_Par = IolEntry.extend({
  //
  getTuiFormat:function() {
    return TemplateUi.FORMAT_PARAGRAPH;
  },
  asProc:function(cid, tui) {
    var proc = IolProc.asNew(cid, this.Ipc, this.Question_Date.getValue());
    proc.comments = tui.getHtml();
    return proc;
  }
})
IolEntry_Form = IolEntry.extend({
  //
  getTuiFormat:function() {
    return TemplateUi.FORMAT_ENTRY_FORM_WIDE;
  },
  asProc:function(cid) {
    var proc = IolProc.asNew(cid, this.Ipc, this.Question_Date.getValue());
    this.Par.Questions.each(function(q) {
      if (q.ipc)
        proc.addResult(q.ipc, q.getValue());
    })
    return proc;
  }
})
IolPar = Par.extend({
  //
  onload:function(json) {
    Par.onload.call(this, json, IolQuestions);
  },
  getQuestion:function(id) {
    if (this.Questions) 
      return this.Questions.get(id);
  }
})
IolQuestions = Questions.extend({
  //
  onload:function() {
    Array.navify(this);
    this._map = Map.from(this, 'id');
  },
  get:function(id) {
    return this._map[id];
  },
  getItemProto:function() {
    return IolQuestion;
  }
})
IolQuestion = Question.extend({
  //
})
IolProc = Proc.extend({
  //
  addResult:function(ipc, value) {
    if (this.PanelResults == null)
      this.PanelResults = [];
    var result = IolResult.asNew(this, ipc, value);
    this.PanelResults.push(result);
  },
  //
  asNew:function(cid, Ipc, date) {
    var me = Proc.asNew.call(this, cid, Ipc);
    me.date = date;
    return me;
  },
  ajax:function(worker) {
    var self = this;
    return {
      save:function(onsuccess, onerror) {
        Ajax.Procedures.savePanel(self, worker, onsuccess, onerror);
      }
    }
  }
})
IolResult = {
  //
  asNew:function(proc, ipc, value, seq) {
    return {
      'clientId':proc.clientId,
      'date':proc.date,
      'ipc':ipc,
      'value':value};
  }
}