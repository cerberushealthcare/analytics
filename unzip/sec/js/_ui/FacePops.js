/**
 * Pop FacePop 
 */
FacePop = {
  create:function(caption) {
    return Html.Pop.create(caption, 0).extend(function(self) {
      return {
        onupdate:function() {},
        //
        onshow:function(fs, rec) {
          self.fs = fs;
          self.table.load(fs);
          if (rec && self.table && self.table.edit)
            self.table.edit(rec);
          return self;
        },
        onclose:function() {
          if (self.updated)
            self.onupdate();
        },
        reset:function() {
          self.updated = false;
          self.table.reset();
        },
        setUpdated:function() {
          self.updated = true;
        }
      }
    })
  }
}
/**
 * TableLoader FacePopTable 
 */
FacePopTable = {
  create:function(container, cls, height) {
    return Html.TableLoader.create(container, cls).extend(this, function(self, parent) {
      return {
        onupdate:function() {},
        //
        edit:function(rec) {},
        getRecsFromFs:function(fs) {},
        fetch:function(callback_recs) {},
        drawRow:function(rec) {},
        //
        load:function(fs) {
          if (fs) {
            self.fs = fs;
            self.cid = fs.clientId;
            parent(Html.TableLoader).load(self.getRecsFromFs(fs));
          } else {
            parent(Html.TableLoader).load();
          }
        }
      }
    })
  }
}
/**
 * Pop ProcsPop
 *   TableLoader Table
 */
ProcsPop = {
  pop:function(fs) {
    return Html.Pop.singleton_pop.apply(this, arguments);
  },
  create:function() {
    var My = this;
    return FacePop.create('Procedures').extend(function(self) {
      return {
        onupdate:function() {},
        //
        init:function() {
          var height = self.fullscreen(1000, 600);
          self.table = My.Table.create(self.content, height)
            .bubble('onupdate', self.setUpdated);
          self.cb = Html.CmdBar.create(self.content)
            .add('Add...', self.new_onclick)
            .add('Add Lab Panel...', self.newPanel_onclick)
            .exit(self.close);
        },
        //
        new_onclick:function() {
          if (! me.Role.Patient.vitals)
            return;
          self.table.edit();
        },
        newPanel_onclick:function() {
          if (! me.Role.Patient.vitals)
            return;
          //self.table.edit(null, true);
          self.table.popPanelEntry();
        }
      }
    })
  },
  Table:{
    create:function(container, height) {
      return FacePopTable.create(container, 'fsg', height).extend(function(self) {
        return {
          onupdate:function() {},
          //
          init:function() {
            self.setHeight(height);
            self.thead().trFixed()
              .th('Date').w('10%')
              .th('Category').w('10%')
              .th('Name').w('20%')
              .th('Snomed').w('5%')
              .th('Results').w('55%');
            self.setTopFilter();
          },
          edit:function(proc, asLabPanel) {
            if (! me.Role.Patient.vitals)
              return;
            if (proc == null) {
              proc = Proc.asNew(self.cid);
            } else if (self.fs.docstubs && proc.getKey) {  // is this being used?
              var stub = self.fs.docstubs.get(proc.getKey());
              if (stub) {
                DocStubPreviewPop.pop(stub)
                  .bubble('onupdate', self.pop_onsave);
                return;
              }
            }
            ProcEntry.pop(proc, asLabPanel)
              .bubble('onsave', self.pop_onsave)
              .bubble('onresultsave', self.pop_onsave)
              .bubble('ondelete', function() {
                self.onupdate();
                self.load();
              })
          },
          popPanelEntry:function() {
            if (! me.Role.Patient.vitals)
              return;
            ProcPanelSelector.pop(self.fs, self.pop_onsave);
          },
          //
          getRecsFromFs:function(fs) {
            return fs.procedures; 
          },
          fetch:function(callback_recs) {
            self.fs.ajax().refetchProcs(callback_recs);
          },
          filter:function(rec) {
            var ipc = (rec.Ipc) ? rec.Ipc : {};
            return {'Category':C_Ipc.CATS[ipc.cat],'Name':ipc.name};
          },
          rowKey:function(rec) {
            return rec.procId;
          },
          drawRow:function(rec) {
            rec._cat = rec.Ipc && C_Ipc.CATS[rec.Ipc.cat];
            if (rec._results)
              rec.__results = rec._results.join('; ');
            self.tbody().tr(rec)
              .td(rec.date, 'bold nw')
              .td(rec._cat)
              .select(AnchorProc)
              .td(rec.Ipc.codeSnomed)
              .td(rec.__results);
          },
          onselect:function(rec) {
            self.edit(rec);
          },
          pop_onsave:function() {
            self.update();
            self.onupdate();
          }
        }
      })
    }
  }
}
/**
 * Pop CdsPop
 *   TableLoader Table
 */
CdsPop = {
  /*
   * @arg Facesheet fs
   * @arg IpcHm rec
   */
  pop:function(fs, rec) {
    return Html.Pop.singleton_pop.apply(this, arguments);
  },
  create:function() {
    var My = this;
    return FacePop.create('Clinical Decision Support').extend(function(self) {
      return {
        onupdate:function() {},
        //
        init:function() {
          var height = self.fullscreen(1000, 600);
          self.table = My.Table.create(self.content, height)
            .bubble('onupdate', self.setUpdated);
          self.cb = Html.CmdBar.create(self.content)
            .add('Add For This Patient...', self.new_onclick)
            .exit(self.close);
        },
        //
        new_onclick2:function() {
          CdsCustomEntryPop.pop().bubble('onupdate', function(rec) {
            self.table.load();
            self.setUpdated();
          })
        },
        new_onclick:function() {
          IpcPickerPop.pop().bubble('onchoose', function(rec) {
            rec = IpcHm.asNewClientLevel(self.fs.cid, rec);
            CdsCustomEntryPop.pop(rec).bubble('onupdate', function(rec) {
              self.table.load();
              self.setUpdated();
            })
          })
        }
      }
    })
  },
  Table:{
    create:function(container, height) {
      var My = this;
      return FacePopTable.create(container, 'fsp', height).extend(function(self) {
        return {
          onupdate:function() {},
          //
          init:function() {
            self.setHeight(height);
            self.thead().trFixed()
              .th('Item').w('20%')
              .th('Description').w('30%')
              .th('Frequency').w('15%')
              .th('Last Recorded').w('10%')
              .th().w('25%');
          },
          edit:function(rec) {
            CdsEntryPop.pop(self.fs, rec)
              .bubble('onupdate', self.pop_onupdate);
          },
          //
          getRecsFromFs:function(fs) {
            return fs.hms; 
          },
          fetch:function(callback_recs) {
            self.fs.ajax().refetchHms(callback_recs);
          },
          drawRow:function(rec) {
            var result = rec.Proc_last && rec.Proc_last.uiResults();
            self.tbody().tr(rec)
              .select(CdsAnchor.create(rec))
              .td(rec._comment).td(rec.summaryEvery())
              .td(My.ProcAnchor.create(rec, self.proc_onclick))
              .td(result);
          },
          onselect:function(rec) {
            self.edit(rec);
          },
          pop_onupdate:function() {
            self.onupdate();
            self.load();
          },
          proc_onclick:function(rec) {
            self.edit(rec);
          }
        }
      })
    },
    ProcAnchor:{
      create:function(rec, onclick) {
        if (rec.hasInterval()) {
          if (rec.Proc_last) 
            return Html.Anchor.create('action graph', rec.Proc_last.date, onclick.curry(rec));
          else
            return Html.Anchor.create(null, '(None)', onclick.curry(rec));            
        }
      }
    }
  }
}
CdsAnchor = {
  create:function(rec) {
    return Html.AnchorAction.create(this._getCls(rec), this._getName(rec));
  },
  _getCls:function(rec) {
    if (! rec.active)
      return 'sqcheck-gray fs';
    else if (! rec.hasInterval()) 
      return (rec.Proc_last) ? 'sqcheck-g fs' : 'sqcheck-y fs'; 
    else if (rec._overdue)
      return 'sqcheck-r fs';
    else
      return 'sqcheck-g fs';
  },
  _getName:function(rec) {
    return (rec.active) ? rec._name : 'Inactive: ' + rec._name;
  }
}
/**
 * Pop CdsEntryPop
 */
CdsEntryPop = {
  /*
   * @arg Facesheet fs
   * @arg IcpHm rec
   */
  pop:function(fs, rec) {
    return Html.Pop.singleton_pop.apply(this, arguments);
  },
  create:function() {
    var My = this;
    return Html.Pop.create('Clinical Decision Entry', 720).extend(function(self) {
      return {
        onupdate:function() {},
        //
        init:function() {
          self.h2 = Html.H2.create().nbsp().into(self.content);
          self.comment = Html.Div.create().into(self.content);
          self.custombox = My.CustomBox.create(self.content)
            .bubble('onupdate', self.custom_onupdate);
          self.frame2 = Html.Pop.Frame.create(self.content).addClass('mt20');
          self.histbox = My.HistBox.create(self.frame2)
            .bubble('onupdate', self)
            .bubble('onexit', self.close);
        },
        onshow:function(fs, rec) {
          self.fs = fs;
          self.rec = rec;
          self.custombox.load(self.fs.cid, self.rec);
          self.histbox.load(self.fs, self.rec);
          self.draw();
        },
        draw:function() {
          self.h2.setText(self.rec._name);
          self.comment.html(self.rec._comment);
          Html.Window.flickerFixedRows();
        },
        custom_onupdate:function(rec) {
          if (rec) {
            self.rec = rec;
            self.draw();
          } else {
            self.close();
          }
          self.onupdate();
        }
      }
    })
  },
  HistBox:{
    create:function(container) {
      var My = this;
      return Html.Tile.create(container).extend(function(self) {
        return {
          onupdate:function() {},
          onexit:function() {},
          //
          init:function() {
            self.table = My.Table.create(self, 200).bubble('onupdate', self);
            self.cb = Html.CmdBar.create(self).add('Add', self.new_onclick).exit(self.exit_onclick);
          },
          load:function(fs, rec) {
            self.cb.get('Add').setText(rec.Ipc.name + ' Result...');
            self.table.load(fs, rec); 
          },
          new_onclick:function() {
            self.table.edit();
          },
          exit_onclick:function() {
            self.onexit();
          }
        }
      })
    },
    Table:{
      create:function(container, height) {
        return FacePopTable.create(container, 'fsg', height).extend(function(self) {
          return {
            onupdate:function() {},
            //
            init:function() {
              self.setHeight(height);
              self.thead().trFixed().th('Date').w('10%').th('Name').w('20%').th('Results').w('60%');
            },
            load:self.load.prepend(function(fs, rec) {
              if (rec)
                self.rec = rec;
            }),
            edit:function(proc) {
              proc = proc || Proc.asNew(self.cid, self.rec.Ipc);
              ProcEntry.pop(proc).bubble('onsave', self.pop_onsave).bubble('onresultsave', self.pop_onsave).bubble('ondelete', function() {
                self.onupdate();
                self.load();
              })
            },
            //
            getRecsFromFs:function(fs) {
              return fs.procedures;
            },
            fetch:function(callback_recs) {
              self.fs.ajax().refetchProcs(callback_recs);
            },
            rowKey:function(rec) {
              return rec.procId;
            },
            drawRow:function(rec) {
              if (rec.ipc == self.rec.ipc) {
                if (rec._results)
                  rec.__results = rec._results.join('; ');
                self.tbody().tr(rec).td(rec.date, 'bold nw').select(AnchorProc).td(rec.__results);
              }
            },
            onselect:function(rec) {
              self.edit(rec);
            },
            pop_onsave:function() {
              self.update();
              self.onupdate();
            }
          }
        })
      }
    }
  },
  CustomBox:{
    create:function(container) {
      return Html.Tile.create(container).setClass('mt10').extend(function(self) {
        return {
          onupdate:function(rec) {},  // may be null if custom removed and no group/app level exists
          //
          init:function() {
            Html.Label.create(null, 'Applies for this patient:').setClass('mr5').into(self);
            self.custom = Html.AnchorAction.asEdit('', self.custom_onclick).into(self);
          },
          load:function(cid, rec) {  // IpcHm
            self.cid = cid;
            self.rec = rec;
            self.custom.setText(rec.uiApplies());
          },
          //
          custom_onclick:function() {
            var rec = (self.rec.isClientLevel()) ? self.rec : IpcHm.cloneAsClientLevel(self.cid, self.rec);
            CdsCustomEntryPop.pop(rec).bubble('onupdate', function(rec) {
              if (rec)
                self.load(self.cid, rec);
              self.onupdate(rec);
            })
          }
        }
      })
    }
  }
}
/**
 * DirtyPop CdsCustomEntryPop
 */
CdsCustomEntryPop = {
  /*
   * @arg IcpHm rec
   */
  pop:function(rec) {
    return Html.Pop.singleton_pop.apply(this, arguments);
  },
  create:function() {
    var My = this;
    return Html.DirtyPop.create('Custom Clinical Decision Entry', 600).extend(function(self) {
      return {
        onupdate:function(rec) {},
        //
        init:function() {
          self.h2 = Html.H2.create().nbsp().into(self.content);
          self.comment = Html.Div.create().into(self.content);
          self.frame = Html.Pop.Frame.create(self.content).addClass('mt10');
          self.applybox = My.ApplyBox.create(self.frame);
          Html.Tiles.create(self.content, [ 
            self.cmdNew = Html.CmdBar.create(self.content).save(self.save_onclick, 'Save Customization').cancel(self.close),
            self.cmdUsed = Html.CmdBar.create(self.content).save(self.save_onclick).delc(self.delc_onclick, 'Delete Customization', null, 'customization').cancel(self.close)]);
        },
        onshow:function(rec) {
          self.h2.setText(rec._name);
          self.load(rec);
        },
        load:function(rec) {  // IpcHm
          self.rec = rec;
          self.applybox.load(rec);
          self.draw();
        },
        draw:function() {
          if (self.rec.isNew()) 
            self.cmdNew.select();
          else
            self.cmdUsed.select();
          self.comment.html(self.rec._comment);
        },
        //
        save_onclick:function() {
          var rec = self.applybox.getRecord(); 
          rec.ajax(self).save(self.close_updated);
        },
        delc_onclick:function() {
          var rec = self.applybox.getRecord(); 
          rec.ajax(self).remove(self.close_updated);
        },
        close_updated:function(rec) {
          self.onupdate(rec);
          self.close(true);
        }
      }
    })
  },
  ApplyBox:{
    create:function(container) {
      var My = this;
      return Html.Tile.create(container, 'Tree').extend(function(self) {
        return {
          //
          init:function() {
            self.entry = My.Entry.create(self);
          },
          load:function(rec) {
            self.rec = rec;
            self.entry.load(rec);
          },
          getRecord:function() {
            return self.entry.applyTo();
          }
        }
      })
    },
    Entry:{
      create:function(container) {
        return Html.UlEntry.create(container).extend(function(self) {
          var ef = self.ef;
          return {
            init:function() {
              // ef.line().lbl('Test/Procedure').pick('ipc', 'Ipc', IpcPicker);
              ef.line().check('active', 'Active for this patient', self.oncheck);
              ef.line().id('liRec').check('auto', 'Interval', self.oncheck).startSpan('spanFreq').lbl('every', 'nopad').textbox('every', 2).lbl('', 'spacer').select('interval', C_IpcHm.INTERVALS).endSpan();
            },
            onbeforeload:function(rec) {
              rec.auto = rec.every > 0;
            },
            draw:function() {
              ef.liRec.visibleIf(self.rec.active);
              ef.spanFreq.visibleIf(self.rec.auto);
            },
            oncheck:function() {
              self.applyTo();
              self.draw();
            }
          }
        })
      }
    }
  }
}
