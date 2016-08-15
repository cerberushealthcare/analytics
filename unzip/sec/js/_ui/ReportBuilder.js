/**
 * ReportStubView
 *   TableLoader table
 */
var ReportStubView = {
  create:function(container) {
    var My = this;
    return Html.Tile.create(container, 'ReportStubView').extend(function(self) {
      return {
        onselect:function(stub) {},
        onload:function() {},
        oncreate:function(report) {},
        //
        init:function() {
          self.table = My.Table.create(self)
            .bubble('onselect', self)
            .bubble('onload', self);
          self.cb = Html.CmdBar.create(self)
            .add('New Report...', self.add_onclick);
          self._pad = self.cb.height();
          if (! me.Role.Report.builder)
            self.cb.hide();
          self.load();
        },
        load:function() {
          self.table.load();
        },
        setMaxHeight:function(i) {
          self.table.setMaxHeight(i - self._pad);
        },
        newReport:function() {
          NewReportTypePop.pop(function(type) {
            ReportCriteria.ajax(self).asNew(type, self.oncreate);
          })
        },
        //
        add_onclick:function() {
          self.newReport();
        }
      }
    })
  },
  Table:{
    create:function(container) {
      return Html.TableLoader.create(container, 'fsy vtop').extend(function(self) {
        return {
          onselect:function(report) {},
          onload:function() {},
          //
          init:function() {
            self.thead().trFixed().th('Name').w('35%').th('').th('Description').w('65%');
            self.Filter = self.addSideFilter();
            self.Info = Html.Div.create('Info').after(self.Filter).extend(self._Info);
            if (container.filtering == null)
              container.filtering = ReportCriteria.TYPES[ReportCriteria.TYPE_PATIENT];
          },
          //
          rowKey:function(rec) {
            return rec.reportId; 
          },
          filter:function(rec) {
            //return {'Type':rec._type || container.filtering, 'Source':rec._level};
            return {'Type':rec._type};
          },
          onfilterset:function(value) {
            container.filtering = value;
            self.Info.set(value);
          },
          fetch:function(callback_recs) {
            ReportStubs.ajax().getAll(callback_recs);
          },
          add:function(report, tr) {
            var cls = report.isAppLevel() ? 'AppReport' : 'Report';
            tr.select(Html.AnchorAction.create(cls, report.name));
            if (report.Hist)
              tr.td(PctGraph.create().setValue(report.Hist.value));
            else
              tr.td();
            tr.td(null, 'comment').html(report.uiComment());
          },
          setMaxHeight:function(i) {
            self.setHeight(i);
          },
          _Info:function(self) {
            return {
              init:function() {
                self.Title = Html.Tile.create(self, 'filterhead');
                self.Tiles = Html.Tiles.create(self, [self._Audit(), self._Cds(), self._Cqm(), self._Mu(), self._Mu2(), self._Patient()]).aug({
                  selectByText:function(text) {
                    var i = ['Audit Logs','Clinical Decision Support','Clinical Quality Measures','Meaningful Use (Stage 1)','Meaningful Use (Stage 2)','Patient Reports'].find(text);
                    this.selectByIndex(i);
                  }
                }).hide();
              },
              set:function(text) {
                if (String.isBlank(text))
                  self.hide();
                else
                  self.load(text).show();
              },
              load:function(text) {
                self.Title.setText(text);
                self.Tiles.show().selectByText(text);
                return self;
              },
              //
              _Audit:function() {
                return Html.Div.create()
                  .html('For reporting audit records of user modifications to patient data.');
              },
              _Cqm:function() {
                return Html.Div.create()
                  .html('Meaningful use clinical quality measures.');
              },
              _Cds:function() {
                return Html.Div.create()
                  .add(Html.Div.create('mb10').html('Used to build clinical decision rules for determining when patients are due for certain test/procedures.'))
                  .add(Html.Anchor.create('action block graph', 'Show all clinical decision support items due now', HmDueNowPop.pop.bind(HmDueNowPop)))
                  .add(Html.Anchor.create('action block track mt10', 'Show all non-reconciled orders', Page.Nav.goTracking_sched).hideIf(me.super));
              },
              _Mu:function() {
                return Html.Div.create()
                  .add(Html.Div.create('mb10').html('Reports to apply for meaningful use incentives (Stage 1).'))
                  .add(Html.Anchor.create('action tpdf', 'About Medicare and Medicaid EHR Incentive Program').set('href', 'Medicare_Medicaid_EHR_Incentive_Program.pdf').set('target', '_blank').hideIf(me.super));
              },
              _Mu2:function() {
                return Html.Div.create()
                  .add(Html.Div.create('mb10').html('Reports to apply for meaningful use incentives (Stage 2).'))
                  .add(Html.Anchor.create('action tpdf', 'About Medicare and Medicaid EHR Incentive Program').set('href', 'Medicare_Medicaid_EHR_Incentive_Program.pdf').set('target', '_blank').hideIf(me.super));
              },
              _Patient:function() {
                return Html.Div.create()
                  .html('General ad-hoc reporting on a wide range of criteria.');
              }
            }
          }
        }
      })
    }
  }
}
/**
 * ReportView
 *   Header header
 *   ReportTablePanels Panels
 */
var ReportView = {
  create:function(container) {
    var My = this;
    return Html.Tile.create(container, 'ReportView').extend(function(self) {
      return {
        onedit:function(report) {},
        onexit:function() {},
        //
        init:function() {
          self.htile = Html.Tile.create(self);
          self.info = Html.Tile.create(self.htile, 'cj');
          self.info.style.paddingBottom = '10px';
          self.header = My.Header.create(self.htile)
            .bubble('onedit', self)
            .bubble('onrerun', self.refresh)
            .bubble('onexit', self);
          self.panels = ReportTablePanels.create(self);   
        },
        loadFromStub:function(stub) {
          if (stub.type == 1) {
            var h = me.User.name + ' - ' + me.User.UserGroup.name;
            var a = me.User.UserGroup.Address;
            if (a.addr1 && a.csz)
              h += ' - ' + a.addr1 + ' ' + a.csz;
            self.info.show().html('<h3>' + h + '</h3>');
          } else {
            self.info.hide();
          }
          self.reset();
          ReportCriteria.ajax(self).get(stub.reportId, self.load);
        },
        refresh:function() {
          ReportCriteria.ajax(self).get(self.report.reportId, self.load);
        },
        setMaxHeight:function(i) {
          i = (i) ? self._padContainer = i : self._padContainer;
          self._pad = self.htile.getHeight();
          self.panels.setMaxHeight(i - self._pad);
        },
        reset:function() {
          self.panels.reset();
          self.header.reset();
        },
        refreshSummary:function() {
          if (self.report)
            self.header.drawSummary();
        },
        /*
         * @arg ReportCriteria report
         */
        load:function(report) {
          self.reset();
          self.report = report;
          self.panels.reset();
          self.header.load(report);
          self.panels.load(report);
          self.setMaxHeight();
        }
      }
    })
  },
  Header:{
    create:function(container) {
      return Html.Tile.create(container, 'ReportViewHeader').extend(function(self) {
        return {
          onedit:function(report) {},
          onrerun:function(report) {},
          onexit:function() {},
          //
          init:function() {
            var t = Html.Table2ColHead.create(self);
            self.h2 = Html.H2.create('Report').nbsp().into(t.left);
            self.rerun = Html.AnchorAction.create('refresh2', 'Run this report again').into(t.right).bubble('onclick', self.rerun_onclick);
            self.custom = Html.AnchorAction.asCustom('Configure this report').into(t.right).bubble('onclick', self.custom_onclick);
            self.exit = Html.AnchorAction.create('back', 'Return to list').into(t.right).bubble('onclick', self, 'onexit');
            self.comment = Html.Div.create('Comment').setText('Description').into(self);
            self.summary = Html.SplitTile.create(self);
            if (! me.Role.Report.builder)
              self.custom.invisible();
          },
          reset:function() {
            self.invisible();
          },
          drawSummary:function() {
            self.summary.left.html(self.report.summary('<br>&bull; '));
            if (self.report.isFractioned()) {
              if (self.report.hasIpcHm())
                self.summary.right.html('Patients Due Now');
              else
                self.summary.right.html(self.report.summary('<br>&bull; ', self.report.RecDenom));
            }
          },
          load:function(report) {
            self.report = report;
            self.h2.setText(report.name);
            self.comment.html(report.uiComment());
            if (report.isFractioned())  
              self.summary.showBoth();
            else 
              self.summary.showLeft();
            self.drawSummary();
            self.custom.showIf(report.isEditable()); 
            self.visible();
          },
          //
          custom_onclick:function() {
            self.onedit(self.report);
          },
          rerun_onclick:function() {
            self.onrerun();
          }
        }
      })
    }
  }
}
/**
 * ReportCriteriaView
 *   ReportAnchor reportAnchor
 *   SplitAnchor splitAnchor 
 *   TreeSplitTile tree
 *     ReportTree tree (left)
 *     Tiles tiles (right)
 *       ReportTree treeDenom
 *       IpcHmEntry entryIpcHm
 *   ReportTablePanels panels
 */
var ReportCriteriaView = {
  create:function(container) {
    var My = this;
    return Html.Tile.create(container, 'ReportCriteriaView').extend(function(self) {
      return {
        onexit:function() {},
        //
        init:function() {
          self.split = Html.SplitTile.create(self);
          self.reportAnchor = My.ReportAnchor.create().into(self.split.left)
            .bubble('onsave', self.report_onsave)
            .bubble('ondelete', self.close.curry(true));
          self.splitAnchor = My.SplitAnchor.create().into(self.split.right)
            .bubble('onupdate', self.split_onupdate);
          self.tree = My.TreeSplitTile.create(Html.Tile.create(self, 'TreeTile'))
            .bubble('onresize', self.tree_resize)
            .bubble('onupdate', self.tree_onupdate)
            .bubble('ondelete', self.close.curry(true))
            .bubble('onsave', self.onsave);
          self.cb = Html.CmdBar.create(self)
            .button('Generate Report', self.generate_onclick, 'report')
            .spacer()
            .save(self.save_onclick)
            .del(self.del_onclick, 'Delete Report')
            .exit(self.close);
          self.tile = Html.Tile.create(self, 'mt10');
          self.panels = ReportTablePanels.create(self.tile).hide();         
        },
        /*
         * @arg ReportCriteria report
         */
        load:function(report) {
          self.setDirty(false);
          self.panels.hide();
          if (report == null)
            ReportCriteria.ajax(self).asNew(self.setReport);
          else
            self.setReport(report);
        },
        setReport:function(report) {
          self.report = report;
          self.reportAnchor.load(report);
          self.splitAnchor.load(report);
          self.cb.showDelIf(! report.isNew());
          self.tree.load(report);
        },
        //
        save:function(callback) {
          self.report.ajax(self).save(function(report) {
            self.load(report);
            callback();
          })
        },
        close:function(saved) {
          if (saved || ! self.dirty)
            self.onexit();
          else
            Pop.Confirm.showDirtyExit(self.saveAndExit, self.onexit);
        },
        generate_onclick:function() {
          self.panels.show().load(self.report);
        },
        save_onclick:function() {
          self.working(function() {
            self.save(self.onsave);
          })
        },
        onsave:function() {
          Html.Animator.fade(self.tree);
        },
        del_onclick:function() {
          Pop.Confirm.showDelete('report', function() {
            ReportCriteria.ajax(self).remove(self.report.reportId, self.close.curry(true));
          })
        },
        setDirty:function(value) {
          self.dirty = value;
          self.reportAnchor.setDirty(value);
        },
        tree_onupdate:function() {
          self.setDirty(true);
        },
        split_onupdate:function() {
          self.tree.load(self.report);
          self.setDirty(true);
        },
        saveAndExit:function() {
          self.save(self.close);
        },
        exit_onclick:function() {
          self.onexit();
        },
        resize:function(pad) {
        },
        tree_resize:function(expanded) {
          Html.Window.flickerFixedRows();
        },
        report_onsave:function(report) {
          self.setReport(report);
          self.onsave();
        }
      }
    })
  },
  SplitAnchor:{
    create:function() {
      var My = this;
      return Html.Span.create().extend(function(self) {
        return {
          onupdate:function() {},
          //
          init:function() {
            Html.Tiles.create(self, [
              self.empty = Html.Tile.create(self),
              self.denomAnchor = My.DenomAnchor.create(self).into(self).bubble('onupdate', self),
              self.cdsAnchor = My.CdsAnchor.create(self).into(self).bubble('onupdate', self)]);
          },
          load:function(report) {
            if (report.isFractionable())
              self.denomAnchor.select().load(report);
            else if (report.isTypeCds())
              self.cdsAnchor.select().load(report);
            else
              self.empty.select();
          }
        }
      })
    },
    CdsAnchor:{
      create:function() {
        return Html.Span.create().extend(function(self) {
          return {
            onupdate:function() {},
            //
            init:function() {
              self.add = Html.AnchorAction.create('AddIpc', 'Select Procedure...').into(self)
                .bubble('onclick', self.add_onclick);
              self.remove = Html.AnchorAction.create('Ipc', 'Ipc').into(self)
                .bubble('onclick', self.remove_onclick);
            },
            load:function(report) {
              self.report = report;
              self.draw();
            },
            draw:function() {
              if (self.report.hasIpcHm()) {
                self.add.hide();
                self.remove.setText(self.report.IpcHm.Ipc.name).show();
              } else {
                self.add.show();
                self.remove.hide();
              }
            },
            //
            add_onclick:function() {
              IpcPickerPop.pop().bubble('onchoose', function(rec) {
                self.report.addIpcHm(rec);
                self.draw();
                self.onupdate();
              })
            },
            remove_onclick:function() {
              Pop.Confirm.showYesNo('Are you sure you want to detach this test/procedure?', function() {
                self.report.removeIpcHm();
                self.draw();
                self.onupdate();
              })
            }
          }
        })
      }
    },
    DenomAnchor:{
      create:function() {
        return Html.Span.create().extend(function(self) {
          return {
            onupdate:function() {},
            //
            init:function() {
              self.add = Html.AnchorAction.create('AddDenom', 'Add Denominator...').into(self)
                .bubble('onclick', self.add_onclick);
              self.remove = Html.AnchorAction.create('Denom', 'Denominator').into(self)
                .bubble('onclick', self.remove_onclick);
            },
            load:function(report) {
              self.report = report;
              self.draw();
            },
            draw:function() {
              if (self.report.isFractioned()) {
                self.add.hide();
                self.remove.show();
              } else {
                self.add.show();
                self.remove.hide();
              }
            },
            //
            add_onclick:function() {
              self.report.addDenom();
              self.draw();
              self.onupdate();
            },
            remove_onclick:function() {
              Pop.Confirm.showDelete('denominator', function() {
                self.report.removeDenom();
                self.draw();
                self.onupdate();
              })
            }
          }
        })
      }
    }
  },
  ReportAnchor:{
    /*
     * @arg ReportCriteria report
     */
    create:function() {
      return Html.Span.create().extend(function(self) {
        return {
          onpop:function() {},
          onsave:function(report) {},
          ondelete:function() {},
          //
          init:function() {
            self.reportAnchor = Html.AnchorAction.create().into(self).bubble('onclick', self.pop);
          },
          load:function(report) {
            self.report = report;
            self.reportAnchor.setText(report.name).setClass(report.isAppLevel() ? 'AppReport' : 'Report');
          },
          pop:function() {
            self.onpop();
            return ReportEntryPop.pop(self.report).bubble('onsave', self).bubble('ondelete', self);
          },
          setDirty:function(value) {
            self.reportAnchor.addClassIf('dirty', value);
            self.reportAnchor.tooltip(value ? 'Report has unsaved changes' : null);
          }
        }
      })
    }
  },
  TreeSplitTile:{
    create:function(container) {
      return Html.SplitTile.create(container).extend(function(self) {
        return {
          onresize:function() {},
          onupdate:function() {},
          onsave:function() {},
          //
          init:function() {
            self.tree = ReportTree.create(self.left).bubble('onresize', self).bubble('onupdate', self);
            self.tiles = Html.Tiles.create(self.right, [
              self.treeDenom = ReportTree.create(self.right).bubble('onresize', self).bubble('onupdate', self).bubble('onsave', self),
              self.entryIpcHm = IpcHmEntry.create(self.right).bubble('onupdate', self)]);
         },
          load:function(report) {
            self.tree.load(report, report.Rec);
            if (report.hasIpcHm()) {
              self.entryIpcHm.select().load(report);
              self.showBoth();
            } else if (report.isFractioned()) {
              self.treeDenom.select().load(report, report.RecDenom);
              self.showBoth();
            } else { 
              self.showLeft();
            }
          }
        }
      })
    }
  }
}
/**
 * UlEntry IpcHmEntry
 */
var IpcHmEntry = {
  create:function(container) {
    return Html.UlEntry.create(container).addClass('ml20 mt5').extend(function(self) {
      var ef = self.ef;
      return {
        onupdate:function() {},
        //
        init:function() {
          ef.line().check('auto', 'Interval', self.auto_onclick).startSpan('freq').lbl('every', 'nopad').textbox('every', 2).lbl('', 'spacer').select('interval', C_IpcHm.INTERVALS).endSpan();
        },
        onbeforeload:function(report) {
          self.report = report;
          var rec = report.IpcHm;
          rec.auto = rec.every > 0;
          rec.every = rec.every || 1;
          rec.interval = rec.interval || C_IpcHm.INT_YEAR;
          return rec;
        },
        onchange:function() {
          self.applyTo();
          self.onupdate();
          self.ef.resetRecordChanged();
        },
        onapply:function() {
          self.rec.every = (self.rec.auto) ? self.rec.every : 0;
        },
        draw:function() {
          ef.freq.visibleIf(self.rec.auto);
        },
        auto_onclick:function() {
          self.rec.auto = ef.getValue('auto');
          self.draw();
        }
      }
    })
  }
}
/**
 * Ul ReportTree
 *   Rec
 *   JoinAnchor[]
 *     Rec[]
 *     AnotherJoinRecAnchor
 *   NewJoinAnchor   
 */
var ReportTree = {
  create:function(container) {
    var My = this;
    return Html.Ul.create('ReportTree').into(container).extend(function(self) {
      return {
        onresize:function() {},
        onupdate:function() {},
        //
        /*
         * @arg ReportCriteria report
         * @arg Rec rec (e.g. report.Rec or report.RecDenom)
         */
        load:function(report, rec) {
          self.report = report;
          self.rec = rec || report.Rec;
          self.draw();
        },
        draw:function() {
          self.clean();
          self.items = [];
          self.add(1, My.Rec.create(self.rec).bubble('onupdate', self.update));
          Array.each(self.rec.Joins, function(join) {
            self.add(1, My.JoinAnchor.create(self.report, self.rec, join).bubble('onupdate', self.update));
            var showCase;
            Array.each(join.Recs, function(rec, i) {
              if (join.isCaseType()) {
                if (rec.case_) {
                  if (i > 0)
                    self.add(2, My.AnotherJoinRecAnchor.create(self.report, self.rec, join, i).bubble('onupdate', self.update));
                  self.add(1, My.Case.create(rec));
                }
                showCase = rec.case_ || i > 0;
              }
              self.add(2, rec._span = My.Rec.create(rec, showCase).bubble('onupdate', self.rec_onupdate.curry(rec, join)).bubble('ondelete', self.rec_ondelete.curry(rec, join)));
            })
            self.add(2, My.AnotherJoinRecAnchor.create(self.report, self.rec, join).bubble('onupdate', self.another_onupdate));
            if (join.isCaseType())
              self.add(1, My.AnotherCase.create(self.report, self.rec, join).bubble('onupdate', self.update));
          })
          if (! Array.isEmpty(self.rec.JOINS_TO)) 
            self.add(1, My.NewJoinAnchor.create(self.report, self.rec).bubble('onupdate', self.update));
          self.onresize();
        },
        //
        add:function(level, e) {
          var li = self.li('l' + level).add(e);
          if (level > 0)
            self.items.push(li);
        },
        update:function() {
          self.draw();
          self.onupdate();
        },
        rec_onupdate:function(rec, join) {
          join.refreshCaseFlags();
          self.update();
        },
        rec_ondelete:function(rec, join) {
          join.drop(rec);
          self.update();
        },
        another_onupdate:function(rec) {
          self.update();
          rec && rec._span.pop();
        }
      }
    })
  },
  Case:{
    create:function(rec) {
      return Html.Span.create().extend(function(self) { 
        return {
          init:function() {
            Html.AnchorAction.create('case', 'case', function() {
              rec._span.pop();
            }).into(self);
          }
        }
      })
    }
  },
  AnotherCase:{
    create:function(report, rec, join) {
      return Html.Span.create().extend(function(self) { 
        return {
          onupdate:function() {},
          //
          init:function() {
            Html.AnchorAction.create('red', 'case...', function() {
              JoinTablePop.pop(report, rec, function(name, id) {
                join.ajax(Html.Window).add_asNewCase(id, self.onupdate);
              })
            }).into(self);
          }
        }
      })
    }
  },
  AnotherJoinRecAnchor:{
    create:function(/*ReportCriteria*/report, /*RepCritRec*/rec, /*RepCritJoin*/join, /*int*/i/*=end*/) {
      return Html.Span.create().extend(function(self) { 
        return {
          onupdate:function(rec) {},
          //
          init:function() {
            Html.AnchorAction.create('red', 'add...', function() {
              JoinTablePop.pop(report, rec, function(name, id) {
                join.ajax(Html.Window).add(id, self.onupdate, i);
              })
            }).into(self);
          }
        }
      })
    }
  },
  NewJoinAnchor:{
    create:function(/*ReportCriteria*/report, /*RepCritRec*/rec) {
      return Html.AnchorAction.create('Join red', 'having...').extend(function(self) {
        return {
          onupdate:function() {},
          //
          onclick:function() {
            JoinTablePop.pop_asNewJoin(report, rec, self.onupdate);
          }
        }
      })
    }
  },
  JoinAnchor:{
    create:function(/*ReportCriteria*/report, /*RepCritRec*/rec, /*RepCritJoin*/join/*=null*/) {
      var My = this;
      return Html.Span.create().extend(function(self) {
        return {
          onupdate:function() {},
          //
          init:function() {
            self.jointype = My.JoinTypeAnchor.create(report, rec, join).bubble('onupdate', self).into(self);
            self.count = My.CountAnchor.create(report, rec, join).bubble('onupdate', self).into(self);
            self.count.showIf(join.isCountType());
          }
        }
      })
    },
    JoinTypeAnchor:{
      create:function(report, rec, join) {
        return Html.AnchorAction.create('Join', join.getJoinTypeLabel()).extend(function(self) {
          return {
            onupdate:function() {},
            //
            onclick:function() {
              JoinTypePop.pop(self, join, self.onupdate);
            }
          }
        })
      }
    },
    CountAnchor:{
      create:function(report, rec, join) {
        return Html.AnchorAction.create('Ct', join.getCountLabel()).extend(function(self) {
          return {
            onupdate:function() {},
            //
            onclick:function() {
              JoinCountPop.pop(join, self.onupdate);
            }
          }
        })
      }      
    }
  },
  Rec:{
    create:function(/*RepCritRec*/rec, showCase) {
      return Html.Span.create().extend(function(self) {
        return {
          onupdate:function() {},
          ondelete:function(rec) {},
          //
          init:function() {
            Html.Span.create('Record', rec._name).into(self);
            Html.Anchor.create('Summary', rec.summary(), self.anchor_onclick).into(self);
          },
          anchor_onclick:function() {
            rec.ajax().loadParInfo(self.pop);
          },
          //
          pop:function() {
            CritRecEntryPop.pop_asEntry(rec, showCase)
              .bubble('onsave', self.pop_onsave)
              .bubble('ondelete', self.pop_ondelete);
          },
          pop_onsave:function(update) {
            rec = update;
            self.onupdate();
          },
          pop_ondelete:function() {
            self.ondelete(rec);
          }
        }
      })
    }
  }
}
/**
 * Panels ReportTablePanels
 *   ClientReportSplitTile Client
 *     ClientReportTable table (left)
 *     DenomReportTile denom (right)
 *       DenomReportTable tableDenom 
 *     Tile percentbar
 *   AuditReportTile Audit
 *     AuditReportTable table
 */
var ReportTablePanels = {
  create:function(container) {
    var panels = {
      Client:ClientReportSplitTile, 
      Audit:AuditReportTile};
    return Html.Panels.create(Html.Tile.create(container, 'ReportTablePanels'), panels).extend(function(self) {
      return {
        load:function(report) {
          if (report.isTable_Audit()) 
            self.Panels.Audit.select();
          else 
            self.Panels.Client.select();
          self.selected.load(report);
        }
      }
    })
  }
}
/**
 * SplitTile CientReportSplitTile
 *   ClientReportTable table
 *   DenomReportTile denom
 *     DenomReportTable tableDenom
 *   Tile percentBar
 */
var ClientReportSplitTile = {
  create:function(container) {
    return Html.SplitTile.create(container).extend(function(self) {
      return {
        onreqfacesheet:function(cid) {},
        //
        init:function() {
          self.table = ClientReportTable.create(self.left)
            .bubble('onfetch', self.table_onfetch)
            .bubble('onstats', self.table_onstats)
            .bubble('onreqfacesheet', self);
          self.denom = DenomReportTile.create(self.right)
            .bubble('onstats', self.tableDenom_onstats)
            .bubble('onreqfacesheet', self);
          self.percentBar = Html.Tile.create(container, 'PercentBar');
        },
        load:function(report) {
          self.reset();
          if (report.isFractioned()) {
            self.showBoth();
            self.table.set('_ct', null).load(report);
            self.denom.set('_ct', null);
            self.percentBar.nbsp().showIf(! report.hasIpcHm());
          } else {
            self.showLeft();
            self.table.load(report);
            self.percentBar.hide();
          }
        },
        reset:function() {
          self.table.reset();
          self.denom.reset();
        },
        setMaxHeight:function(i) {
          self.table.setMaxHeight(i);
          self.denom.setMaxHeight(i);
        },
        table_onfetch:function(report) {
          self.table.report = report;
          self.denom.load(report);
        },
        table_onstats:function(length) {
          self.table._ct = length;
          self.setPct(length);
        },
        tableDenom_onstats:function(length) {
          self.denom.tableDenom._ct = length;
          self.setPct(length);
        },
        setPct:function() {
          if (self.table._ct !== null && self.denom.tableDenom._ct) {
            var pct = Math.roundTo(self.table._ct / self.denom.tableDenom._ct * 100, 2);
            self.percentBar.setText('Total: ' + pct + '%');
          }
        }
      }
    })
  }
}
/**
 * Tile AuditReportTile
 */
var AuditReportTile = {
  create:function(container) {
    return Html.Tile.create(container).extend(function(self) {
      return {
        init:function() {
          self.table = AuditReportTable.create(self);
        },
        load:function(report) {
          self.table.load(report);
        },
        reset:function() {
          self.table.reset();
        },
        setMaxHeight:function(i) {
          self.table.setMaxHeight(i);
        }
      }
    })
  }
}
/**
 * TableLoader ReportTable
 *   Tile statusbar
 */
var ReportTable = {
  create:function(container) {
    var My = this;
    var wrapper = Html.Tile.create(container, 'ReportTable');
    return Html.TableLoader.create(wrapper, 'fsb').extend(My, function(self, parent) {
      return {
        onfetch:function(report) {},
        onstats:function(length) {},
        onreqdownload:function(/*bool*/noncomps) {},
        //
        init:function() {
          self.setHeight(350);
          self.statusbar = My.StatusBar.create(wrapper, 'Stats')
            .bubble('onreqdownload', self.sb_onreqdownload)
            .bubble('onreqdownload2', self.sb_onreqdownload2);
          self._pad = self.statusbar.getHeight();
        },
        load:function(report) {
          self.report = report;
          parent(Html.TableLoader).load();
        },
        hide:function() {
          self.statusbar.hide();
          parent(Html.ScrollTable).hide();
          return self;
        },
        show:function() {
          self.statusbar.show();
          parent(Html.ScrollTable).show();
          return self;
        },
        setMaxHeight:function(i) {
          self.setHeight(i - self._pad, 100);
        },
        //
        sb_onreqdownload:function() {
          self.onreqdownload();
        },
        sb_onreqdownload2:function() {
          self.onreqdownload(true);
        },
        fetch:function(callback_recs) {
          self.report.ajax(self).generate(function(report) {
            self.onfetch(report);
            callback_recs(self.getRecsFromReport(report));
          })
        },
        getRecsFromReport:function(report) {
          return report.recs;
        },
        reset:function() {
          self.statusbar.reset();
          self.thead().clean();
          parent(Html.TableLoader).reset();
          return self;
        },
        into:function(e) {
          wrapper.into(e);
          return self;
        },
        setStats:function(recs) {
          var firstJoinOnly = false;
          if (self.report.name == 'MU2:  Medication Reconciliation')
            firstJoinOnly = true;
          if (self.isNumerator && self.isNumerator()) {
            self.statusbar.setText(self.getNumLabel() + ': ' + recs.count(self.report.countBy, firstJoinOnly));
          } else {
            self.statusbar.setText(self.getDenomLabel() + ': ' + recs.count(self.report.countBy, firstJoinOnly));
            self.statusbar.showDownload2(! self.report.isTypeCds());
          }
          self.onstats(recs.count(self.report.countBy, firstJoinOnly));
        },
        getNumLabel:function() {
          if (! self.report.isFractioned())
            return 'Record(s)';
          return (self.report.isTypeCds()) ? 'Inclusive population' : 'Numerator record(s)';
        },
        getDenomLabel:function() {
          return (self.report.isTypeCds()) ? 'Due now' : 'Denominator record(s)';
        }
      }
    })
  },
  StatusBar:{
    create:function(container) {
      return Html.Tile.create(container, 'Stats').extend(function(self) {
        return {
          onreqdownload:function() {},
          onreqdownload2:function() {},
          //
          init:function() {
            self.label = Html.Label.create().into(self);
            self.download = Html.Anchor.create('download').setText('Download').into(self).hide()
            .bubble('onclick', self, 'onreqdownload');
            self.download2 = Html.Anchor.create('download red').setText('Noncompliants').into(self).hide()
            .bubble('onclick', self, 'onreqdownload2');
            //self.cb = Html.CmdBar.create(self).append(self.label);
          },
          setText:function(text) {
            self.label.setText(text);
            self.download.show();
          },
          showDownload2:function(value) {
            self.download2.showIf(value);
          },
          reset:function() {
            self.label.setText();
            self.download.hide();
            self.download2.hide();
          }
        }
      })
    }
  }
}
/**
 * ReportTable ClientReportTable
 */
var ClientReportTable = {
  create:function(container) {
    return ReportTable.create(container, 'fsb').extend(function(self) {
      return {
        onreqfacesheet:function(cid) {},  
        //
        onreqdownload:function(noncomps) {
          var duenow = self.report.hasIpcHm() && ! self.isNumerator(); 
          self.report.ajax().download(self.isNumerator(), noncomps, duenow);
        },
        isNumerator:function() {
          return true;
        },
        onload:function(recs) {
          var tr = self.thead().trFixed();
          if (me.super)
            tr.th('Practice').w('15%');
          tr.th(self.report.Rec._name).w('15%');
          var i = me.super ? 60 : 85;
          self.cw = String.percent(i / recs.joinCt);
          recs.joinTables.each(function(table) {
            tr.th(table).w(self.cw);
          })
          self.setStats(recs);
        },
        add:function(rec, tr) {
          if (rec.UserGroup)
            tr.td().w('10%').html(rec.UserGroup.name);
          var a = AnchorClient_FacesheetPop.create(rec);  
          if (rec._clientLevel) {
            a.style.color = 'green';
            a.title = 'Customized via facesheet';
          }
          if (self.numIds && ! self.numIds[rec.clientId])
            a.style.color = 'red';
          tr.td(a).w('20%');
          rec.getJoinDatas().each(function(j) {
            tr.td().w(self.cw).html(j.labels.join('<br>'));
          })
        }
      }
    })
  }
}
/**
 * ClientReportTable DenomReportTable
 */
var DenomReportTable = {
  create:function(container) {
    var My = this;
    return ClientReportTable.create(container).extend(My, function(self, parent) {
      return {
        load:function(report) {
          self.report = report;
          self.numIds = Map.from(report.recs, 'clientId');
          parent(Html.TableLoader).load(report.recsDenom);
        },
        getRecsFromReport:function(report) {
          return report.recsDenom;
        },
        isNumerator:function() {
          return false;
        }
      }
    })
  }
}
var DenomReportTile = {
  create:function(container) {
    var My = this;
    return Html.Tile.create(container).extend(function(self) {
      return {
        onstats:function(length) {},
        //
        init:function() {
          Html.Tiles.create(self, [
            self.showtile = My.ShowTile.create(self).bubble('onclickshow', self.show_onclick),
            self.tableDenom = DenomReportTable.create(self)]); //.hide()]);
        },
        setMaxHeight:function(i) {
          self.tableDenom.setMaxHeight(i);
          self.showtile.setMaxHeight(i);
        },
        set:function(fid, value) {
          self.tableDenom.set(fid, value);
          return self;
        },
        reset:function() {
          self.showtile.select().reset();
          self.tableDenom.select().reset().hide();
        },
        load:function(report) {
          var count = report.recsDenom.count(report.countBy);
          self.report = report;
          self.onstats(count);
          if (report.hasIpcHm())
            self.showtile.setAsDueNow();
          //self.showtile.showAnchor(count);
          self.tableDenom.show().working(function() {
            self.show_onclick();
          })
        },
        //
        show_onclick:function() {
          self.tableDenom.show().select();
          self.tableDenom.load(self.report);
        }
      }
    })
  },
  ShowTile:{
    create:function(container) {
      return Html.Tile.create(container, 'ShowTile').extend(function(self) {
        return {
          onclickshow:function() {},
          //
          init:function() {
            self.anchor = Html.Anchor.create(null, 'Show Denominator Report', self.anchor_onclick).into(self);
            self.reset();
          },
          reset:function() {
            self.anchor.hide();
            self.text = 'Show Denominator';
            self.tcls = null;
          },
          setMaxHeight:function(i) {
            self.setHeight(i);
          },
          setAsDueNow:function() {
            self.text = 'Show Due Now';
            self.tcls = 'red';
          },
          showAnchor:function(i) {
            self.anchor.setText(self.text + ' (' + 'record'.plural(i) + ')');
            if (i && self.tcls)
              self.anchor.addClass(self.tcls);
            self.anchor.show();
          },
          //
          anchor_onclick:function() {
            self.onclickshow();
          },
          setHeight:function(i) {
            Html._proto.setHeight.call(self, i);
            self.style.lineHeight = self.style.height;
          }
        }
      })
    }
  }
}
/**
 * ReportTable AuditReportTable
 */
var AuditReportTable = {
  create:function(container) {
    var My = this;
    return ReportTable.create(container, 'fsb').extend(function(self) {
      return {
        onload:function(recs) {
          var tr = self.thead().trFixed().th('Audit Record').th('Date').th('User').th('Patient');
          var w = String.percent(60 / recs.joinCt);
          recs.joinTables.each(function(table) {
            tr.th(table).w(w);
          })
          self.setStats(recs);
        },
        onreqdownload:function() {
          self.report.ajax().download(true);
        },
        add:function(rec, tr) {
          tr.select(My.AnchorAudit.create(rec)).td(rec.date).td(rec.User.name).td(AnchorClient_Facesheet.create(rec.Client));
          rec.getJoinDatas().each(function(j) {
            tr.td().html(j.labels.join('<br>'));
          })
        },
        onselect:function(rec) {
          AuditViewer.pop(self.recs, rec);
        }
      }
    })
  },
  AnchorAudit:{
    create:function(rec, text) {
      text = text || rec._label;
      var a;
      switch (rec.action) {
        case AuditRec.ACTION_REVIEW:
          a = Html.AnchorAction.asView(text);
        case AuditRec.ACTION_DELETE:
          a = Html.AnchorAction.asDelete(text);
        case AuditRec.ACTION_PRINT:
          a = Html.AnchorAction.asPrint(text);
        case AuditRec.ACTION_BREAKGLASS:
          a = Html.AnchorAction.asWarning(text);
        case AuditRec.ACTION_SIGN:
          a = Html.AnchorAction.create('sqcheck-g', text);
        case AuditRec.ACTION_UNSIGN:
          a = Html.AnchorAction.create('sqcheck-r', text);
        default:
          a = Html.AnchorAction.asUpdate(text);
        if (rec._altered) {
          a.addClass('red');
        }
        return a;
      }
    }
  }
}
/**
 * Pop AuditViewer
 */
var AuditViewer = {
  pop:function(recs, rec) {
    return this.create().pop(recs, rec);
  },
  create:function() {
    var My = this;
    return AuditViewer = Html.Pop.create('Audit Viewer', 800).extend(function(self) {
      return {
        init:function() {
          self.navbar = My.NavBar.create(self.content).bubble('onselect', self.navbar_onselect);
          self.viewer = My.Viewer.create(self.content);
        },
        onshow:function(recs, rec) {
          self.navbar.load(recs, rec, My.AnchorAudit, true);
        },
        navbar_onselect:function(rec) {
          self.viewer.load(rec);
        }
      }
    })
  },
  NavBar:{
    create:function(container) {
      var My = this;
      return Html.NavBar.create(container).extend(function(self) {
        return {
          init:function() {
            self.onbox.content.hide();
            self.recbox = My.RecBox.create(self.onbox);
          },
          ondraw_load:function(rec, header, content) {
            header.html(rec.Client.name + '<br>' + rec._label);
            self.recbox.load(rec); 
          },
          getContent:function(rec) {
            var a = [];
            a.push(rec.recName + ' ID #' + rec.recId);
            a.push(rec.ACTIONS[rec.action] + ' ' + rec.date + ' by ' + rec.User.name);
            return a.join('<br>');
          }
        }
      })
    },
    RecBox:{
      create:function(container) {
        return Html.Tile.create(container, 'RecBox').extend(function(self) {
          return {
            init:function() {
              var ef = self.form = Html.EntryForm.create(self);
              ef.li('Record ID', null, 'nopad').ro('recId').lbl('User').ro('_by').lbl('Date').ro('date');
            },
            load:function(rec) {
              self.form.setRecord(rec);
            }
          }
        })
      }
    }
  },
  AnchorAudit:{
    create:function(rec, onclick) {
      return Html.AnchorRec.from(AuditReportTable.AnchorAudit.create(rec, rec.recName), rec, onclick);
    }
  },
  Viewer:{
    create:function(container) {
      var My = this;
      return Html.Tile.create(container, 'AuditViewer').extend(function(self) {
        return {
          init:function() {
            var tr = Html.Table.create(self).tbody().tr();
            self.before = My.Snapshot.create(Html.Pop.Frame.create(tr.td()._cell, 'Before')),
            self.after = My.Snapshot.create(Html.Pop.Frame.create(tr.td(null, 'pl5')._cell, 'After'))
          },
          load:function(rec) {
            self.before.load(rec.before);
            self.after.load(rec.after, rec.hrec, rec._hash, rec._altered);
          }
        }
      })
    },
    Snapshot:{
      create:function(container) {
        return Html.Tile.create(container, 'Snapshot').extend(function(self) {
          return {
            init:function() {
              self.table = Html.Table.create().into(self).tbody();
            },
            load:function(rec, hrec, hash, altered) {
              self.table.clean();
              var snap = rec.getSnapshot();
              if (snap) {
                for (var fid in snap) 
                  self.table.tr().th(fid + ':').td(snap[fid]);
              }
              if (hrec) {
                self.table.tr().th('').td('------------------');
                self.table.tr().th('HASH Original:').td(hrec);
                self.table.tr().th('HASH Current:').td(hash);
                if (altered) {
                  self.table.tr().th().td('Warning! Alteration of record detected.', 'red');
                }
              }
            }
          }
        })
      }
    }
  }
}
/**
 * JoinTypePop (dummy question pop)
 */
var JoinTypePop = {
  /*
   * @arg <a> anchor of join
   * @arg RepCritJoin join
   * @arg fn() onupdate
   */
  pop:function(anchor, join, onupdate) {
    var jts = Map.invert(Map.extract(join.JTS, join.allowable()));
    var q = Question.asDummy('Criteria Group Type', Map.keys(jts), join.JTS[join.jt]);
    QPopJoin.pop(q, 
      function(key) {  // onupdate
        join.updateJoinType(jts[key]);
        onupdate();
      }, 
      function() {  // ondelete
        anchor.style.color = 'red';
        Pop.Confirm.showDeleteAlways('criteria group', function(confirmed) {
          anchor.style.color = '';
          if (confirmed) {
            join.remove();
            onupdate();
          }
        })
      })
  }
}
/**
 * JoinCountPop (dummy question pop)
 */
var JoinCountPop = {
  /*
   * @arg ReportCriteria report
   * @arg RepCritRec rec
   * @arg fn() onupdate
   */
  pop:function(join, onupdate) {
    var cts = [];
    for (var i = 1; i < 50; i++) 
      cts.push(String.from(i));
    Question.asDummy('Count', cts).pop(function(ct) {
      join.updateCount(ct);
      onupdate();
    })
  }
}
/**
 * JoinTablePop 
 */
var JoinTablePop = {
  pop:function(/*ReportCriteria*/report, /*RepCritRec*/rec, /*fn(table, id)*/callback) {
    var tables = Map.invert(Map.extract(RepCritRec.TABLES, report.Rec.JOINS_TO));
    Question.asDummy('Add Criteria', Map.keys(tables)).pop(function(key) {
      callback(key, tables[key]);
    })
  },
  pop_asNewJoin:function(report, rec, onupdate) {
    this.pop(report, rec, function(table, id) {
      report.ajax().addJoin(rec, id, onupdate);
    })
  }
}
/**
 * RecordEntryPop ReportEntryPop
 */
var ReportEntryPop = {
  /*
   * @arg RepCritRec rec 
   */
  pop:function(rec) {
    return Html.Pop.singleton_pop.apply(ReportEntryPop, arguments);
  },
  create:function() {
    return Html.RecordEntryDeletePop.create('Report Entry', 650).extend(function(self) {
      return {
        onsave:function(report) {},
        ondelete:function(id) {},
        //
        onshow:function() {
          self.form.focus('name');
        },
        isDeletable:function(rec) {
          return rec.reportId != null;
        },
        buildForm:function(ef, rec) {
          if (me.admin)
            ef.li('', 'fr').check('app', 'Application level?');
          ef.li('Name').textbox('name');
          ef.li('Description').textarea('comment', 10);
          ef.li('Refs').textarea('refs', 10);
          ef.li('Count By').select('countBy', ReportCriteria.COUNT_BYS);
        },
        save:function(rec, onsuccess, onerror) {
          rec = self.rec.aug(rec);
          rec.ajax().save(onsuccess);
        },
        remove:function(rec, onsuccess) {
          ReportCriteria.ajax(self).remove(rec.reportId, onsuccess);
        },
        getDeleteNoun:function() {
          return 'report';
        }
      }
    })
  }
} 
/**
 * Pop GenRemindersPop
 */
var GenRemindersPop = {
  /*
   * @arg Client_Rep recs 
   */
  pop:function(recs) {
    return this.create().pop(recs);
  },
  create:function() {
    return GenRemindersPop = Html.Pop.create('Generate Reminders').extend(function(self) {
      return {
        init:function() {
          self.input = Html.TextArea.create().setWidth(500).setRows(10).into(self.content);
          self.cb = Html.CmdBar.create(self.content).button('Send Now', self.send_onclick, 'send').cancel(self.close);
        },
        onshow:function(recs) {
        }
      }
    })
  }
}
/**
 * Pop NewReportTypePop
 *   Button[]
 */
var NewReportTypePop = {
  /*
   * @callback(type)
   */
  pop:function(callback) {
    return this.create().pop(callback);
  },
  create:function() {
    var My = this;
    return NewReportTypePop = Html.Pop.create('New Report').extend(function(self) {
      return {
        init:function() {
          var frame = Html.Pop.Frame.create(self.content);
          for (var type in ReportCriteria.TYPES) 
            if (type != ReportCriteria.TYPE_MU && type != ReportCriteria.TYPE_MU2)
              My.Button.create(frame, ReportCriteria.TYPES[type], self.button_onclick.curry(type));
          Html.CmdBar.create(self.content).cancel(self.close);
        },
        onshow:function(callback) {
          self.callback = callback;
        },
        button_onclick:function(type) {
          self.close();
          self.callback(type);
        }
      }
    })
  },
  Button:{
    create:function(container, text, onclick) {
      Html.Anchor.create('cmd rep', text, onclick).into(Html.Tile.create(container, 'cmd-fixed'));
    }
  }
}
/**
 * Pop CritRecEntryPop 
 *    CritRecForm form
 */ 
var CritRecEntryPop = {
  pop:function(/*RepCritRec*/rec, /*bool*/asRunPrompt, /*bool*/showCase) {
    return Html.Pop.singleton_pop.apply(this, arguments);
  },
  pop_asEntry:function(rec, showCase) {
    return this.pop(rec, false, showCase);
  },
  pop_asRunPrompt:function(rec) {
    return this.pop(rec, true);
  },
  create:function() {
    return Html.Pop.create('Entry').extend(function(self) {
      return {
        onsave:function(rec) {},
        ondelete:function() {},
        //
        init:function() {
          self.form = CritRecForm.create(self.content)
            .bubble('onchange', self.form_onchange);
          self.cb = Html.CmdBar.create(self.content)
            .ok(self.save_onclick).del(self.del_onclick).cancel(self.close);
        },
        pop:function(rec, asRunPrompt, showCase) {
          self.setCaption(rec._name + ' Criteria');
          self.dirty = false;
          self.form.load(rec, asRunPrompt, showCase);
          self.showPosCursor();
          self.cb.showDelIf(rec._name != 'Patients');
          return self;
        },
        //
        isDirty:function() {
          return self.dirty;
        },
        form_onchange:function() {
          self.dirty = true;
        },
        close:function(saved) {
          if (saved) 
            Pop.close(true);
          else
            Pop.Confirm.closeCheckDirty(self, self.save_onclick);
        },
        save_onclick:function() {
          self.close(true);
          self.onsave(self.form.getRec());
        },
        del_onclick:function() {
          Pop.Confirm.showDelete('criteria', function() {
            self.ondelete();
            self.close(true);
          })
        }
      }
    })
  }
} 
/**
 * Tile CritRecForm
 *   Table table
 *     CritValueEntry table.tr()
 *       Span label
 *       Select op
 *       Spans spans
 *         SpanSingle spanSingle
 *         SpanBetween spanBetween
 *         SpanBetweenDates spanBetweenDates
 *         SpanAge spanAge
 *         SpanSelect spanSelect
 *         SpanPicker spanPicker
 *         SpanRecPicker spanRecPicker
 */
var CritRecForm = {
  create:function(container) {
    var My = this;
    return Html.Tile.create(container, 'CritRecEntry').extend(function(self) {
      return {
        onchange:function() {},
        //
        init:function() {
          self.tile = Html.Tile.create(self, 'CritTile');
          self.table = Html.Table.create().into(self).tbody();
        },
        load:function(/*RepCritRec*/rec, asRunPrompt, showCase) {
          self.rec = rec;
          self.tile.clean();
          self.table.clean();
          self.entries = [];
          if (showCase)
            self.cas = My.CaseEntry.create(self.tile, rec).bubble('onchange', self);
          var i = 0;
          rec.eachValue(function(cv) {
            cv._parent = rec;
            self.entries.push(My.CritValueEntry.create(self.table.tr(), cv, asRunPrompt)
              .bubble('onchange', self));
          })
          if (me.admin && ! asRunPrompt)
            self.prompt = My.PromptedEntry.create(self.table.tr(), rec).bubble('onchange', self);
        },
        getRec:/*RepCritRec*/function() {
          self.entries.each(function(entry) {
            self.rec.update(entry.getValue());
          }) 
          self.prompt && self.prompt.apply();
          self.cas && self.cas.apply();
          return self.rec;
        }
      }
    })
  },
  CaseEntry:{
    create:function(container, rec) {
      return Html.Span.create('CaseEntry').extend(function(self) {
        return {
          onchange:function() {},
          //
          init:function() {
            self.check = Html.LabelCheck.create('New Case').into(container).bubble('onclick_check', self, 'onchange');
            self.check.setChecked(rec.case_);
          },
          apply:function() {
            rec.case_ = self.check.isChecked();
          }
        }
      })
    }
  },
  PromptedEntry:{
    create:function(tr, rec) {
      return Html.Span.create('CritValueEntry').extend(function(self) {
        return {
          onchange:function() {},
          //
          init:function() {
            self.tr = tr._tr();
            tr.td().td().td(self.span = Html.Span.create(), 'Prompt');
            Html.Label.create('mr5', 'User Prompted').into(self.span);
            self.input = Html.InputText.create().setSize(5).into(self.span).bubble('onchange', self);
            self.input.setValue(rec.prompted_);
          },
          apply:function() {
            rec.prompted_ = self.input.getValue();
          }
        }
      })
    }
  },
  CritValueEntry:{
    /*
     * @arg TrAppender tr
     * @arg RepCritValue cv 
     */
    create:function(tr, cv, asRunPrompt) {
      var My = this;
      return Html.Span.create('CritValueEntry').extend(function(self) {
        return {
          onchange:function() {},
          //
          init:function() {
            self.tr = tr._tr(); 
            tr.td(self.label = Html.Span.create('FidLabel'), 'Label');
            self.tdOp = tr.td(self.op = Html.Select.create().bubble('onset', self.toggle).bubble('onchange', self.toggle), 'Op')._cell;
            self.tdSpans = tr.td(self.spans = My.Spans.create().bubble('onchange', self), 'Spans')._cell;
            self.load(cv);
            if (asRunPrompt) {
              if (cv._label != 'Date' && cv._fid != 'userId' && cv._fid != 'userGroupId')
                self.tr.hide();
            } else {
              if (cv._label == 'Internal Provider')
                self.tr.hide();
            }
          },
          load:function(cv) {
            var label = cv._label;
            if (asRunPrompt && label == 'Internal Provider')
              label = 'Provider';
            self.cv = cv;
            self.op.load(Map.extract(RepCritValue.OPS, cv.getFixedOps()), '');
            self.spans.load(cv);
            self.label.setText(label);
            self.op.setValue(cv.op);
            self.spans.setValue(cv.value);
            return self;
          },
          getValue:function() {
            return self.cv.update(self.op.getValue(), self.spans.getValue());
          },
          //
          toggle:function() {
            self.tr.addClassIf('sel', self.op.getValue());
            self.spans.toggle(self.op.getValue());
            self.onchange();
          }
        }
      })
    },
    Spans:{
      create:function() {
        var My = this;
        return Html.Span.create().extend(function(self) {
          return {
            onchange:function() {},
            //
            init:function() {
              self.spans = [
                self.spanSingle = My.SpanSingle.create(self).bubble('onchange', self),
                self.spanBetween = My.SpanBetween.create(self).bubble('onchange', self),
                self.spanBetweenDates = My.SpanBetweenDates.create(self).bubble('onchange', self),
                self.spanAge = My.SpanAge.create(self).bubble('onchange', self),
                self.spanSelect = My.SpanSelect.create(self).bubble('onchange', self),
                self.spanPicker = My.SpanPicker.create(self).bubble('onchange', self),
                self.spanRecPicker = My.SpanRecPicker.create(self).bubble('onchange', self),
                self.spanInGroup = My.InGroup.create(self).bubble('onchange', self)];
              self.hide();
            },
            /*
             * @arg RepCritValue cv
             */
            load:function(cv) {
              self.cv = cv;
              if (cv.getFixedValues) {
                self.spanSelect.load(cv);
                self.showDefault = self.showSelect;
                self.spanInGroup.setValue(cv.value); //, My.SpanSelect);
              } else if (cv.getRecPicker) {
                self.spanRecPicker.load(cv);
                self.showDefault = self.showRecPicker;
                self.spanInGroup.setValue(cv.value); //, My.SpanRecPicker);
              } else if (cv.createPicker) {
                self.spanPicker.load(cv);
                self.showDefault = self.showPicker;
                self.spanInGroup.setValue(cv.value); //, My.SpanPicker);
              } else { 
                self.showDefault = self.showSingle;
                self.spanInGroup.setValue(cv.value); //, My.SpanSingle);
              }
            },
            toggle:function(op) {
              switch (op) {
                case '':
                  self.hide();
                  break;
                case RepCritValue.OP_BETWEEN:
                case RepCritValue.OP_AGERANGE:
                case RepCritValue.OP_SPLITAGERANGE:
                  self.showBetween();
                  break;
                case RepCritValue.OP_BETWEEN_DATES:
                  self.showBetweenDates();
                  break;
                case RepCritValue.OP_WITHIN:
                case RepCritValue.OP_OVER:
                  self.showAge();
                  break;
                case RepCritValue.OP_IN:
                  self.showInGroup();
                  break;
                case RepCritValue.OP_NULL:
                case RepCritValue.OP_NOT_NULL:
                case RepCritValue.OP_NUMERIC:
                  self.hide();
                  break;
                default:
                  self.showDefault();
              }
            },
            hide:function() {
              self.spans.each(function(span) {
                span.hide();
              })
              self.span = null;
            },
            showSingle:function() {
              self.hide();
              self.span = self.spanSingle.show(self.cv);
            },
            showBetween:function() {
              self.hide();
              self.span = self.spanBetween.show(self.cv);
            },
            showBetweenDates:function() {
              self.hide();
              self.span = self.spanBetweenDates.show(self.cv);
            },
            showAge:function() {
              self.hide();
              self.span = self.spanAge.show(self.cv);
            },
            showSelect:function() {
              self.hide();
              self.span = self.spanSelect.show(self.cv);
            },
            showPicker:function() {
              self.hide();
              self.span = self.spanPicker.show(self.cv);
            },
            showRecPicker:function() {
              self.hide();
              self.span = self.spanRecPicker.show(self.cv);
            },
            showInGroup:function() {
              self.hide();
              self.span = self.spanInGroup.show(self.cv);
            },
            setValue:function(value) {
              if (self.span)
                self.span.setValue(value);
            },
            getValue:function() {
              if (self.span)
                return self.span.getValue();
            }
          }
        })  
      },
      SpanSingle:{
        create:function(container) {
          return Html.Span.create().into(container).extend(function(self) {
            return {
              onchange:function() {},
              //
              init:function() {
                self.input = Html.InputText.create().into(self).bubble('onchange', self);
              },
              show:function(cv) {
                Html._proto.show.call(self);
                self.cv = cv;
                self.input.setFocus();
                return self;
              },
              setValue:function(value) {
                self.input.setValue(value);
              },
              getValue:function() {
                self.cv.text_ = null;
                return self.input.getValue();
              }
            }
          })
        }
      },
      SpanBetween:{
        create:function(container) {
          return CritRecForm.CritValueEntry.Spans.SpanSingle.create(container).extend(function(self) {
            return {
              onchange:function() {},
              //
              init:function() {
                self.input.setSize(5);
                self.label = Html.Label.create('p5', '').into(self);
                self.input2 = Html.InputText.create(null).setSize(5).into(self).bubble('onchange', self);
              },
              setValue:function(value) {
                var a = value.split(',');
                self.input.setValue(a[0]);
                self.input2.setValue((a.length > 0) ? a[1] : null);
              },
              getValue:function(value) {
                self.cv.text_ = null;
                return [self.input.getValue(), self.input2.getValue()].join(',');
              }
            }
          })
        }
      },
      SpanBetweenDates:{
        create:function(container) {
          return Html.Span.create().into(container).extend(function(self) {
            return {
              onchange:function() {},
              //
              init:function() {
                self.input = Html.InputDateTime.create().into(self).bubble('onset', self.di1_onset);
                self.label = Html.Label.create('p5', '').into(self);
                self.input2 = Html.InputDateTime.create().into(self).bubble('onset', self.di2_onset);
              },
              show:function(cv) {
                Html._proto.show.call(self);
                self.cv = cv;
                self.input.setFocus();
                return self;
              },
              setValue:function(value) {
                var a = value.split(',');
                self.input.setValue(a[0]);
                self.input2.setValue((a.length > 0) ? a[1] : null);
              },
              getValue:function(value) {
                self.cv.text_ = null;
                return [self.input.getValue(), self.input2.getValue()].join(',');
              },
              //
              di1_onset:function() {
                var v = self.input.getValue();
                if (v) {
                  var t1 = new Date(v).getTime();
                  var t2 = new Date(self.input2.getValue()).getTime();
                  if (t1 > t2)
                    self.input2.setValue(v);
                }
                self.onchange();
              },
              di2_onset:function() {
                self.onchange();
              }
            }
          })
        }
      },
      SpanAge:{
        create:function(container) {
          var My = this;
          return CritRecForm.CritValueEntry.Spans.SpanSingle.create(container).extend(function(self) {
            return {
              onchange:function() {},
              //
              init:function() {
                self.input.setSize(5);
                self.input2 = Html.Select.create(My.Opts).setClass('ml5').into(self).bubble('onchange', self);
              },
              setValue:function(value) {
                var a = value.split(',');
                self.input.setValue(a[0]);
                self.input2.setValue((a.length > 0) ? a[1] : null);
              },
              getValue:function(value) {
                self.cv.text_ = self.input.getValue() + ' ' + self.input2.getText();
                return [self.input.getValue(), self.input2.getValue()].join(',');
              }
            }
          })
        },
        Opts:{'y':'year(s)','m':'month(s)','w':'week(s)','d':'day(s)'}
      },
      SpanSelect:{
        create:function(container) {
          return Html.Span.create().into(container).extend(function(self) {
            return {
              onchange:function() {},
              //
              load:function(cv) {
                self.cv = cv;
                self.select = Html.Select.create(cv.getFixedValues()).into(self).bubble('onchange', self.select_onchange);
                self._saveText();
                return self;
              },
              show:function(cv) {
                Html._proto.show.call(self);
                self.cv = cv;
                self.select.setFocus();
                return self;
              },
              setValue:function(value) {
                self.select.setValue(value);
                self._saveText();
              },
              getValue:function() {
                return self.select.getValue();
              },
              //
              _saveText:function() {
                self.cv.text_ = self.select.getText();
              },
              select_onchange:function() {
                self._saveText();
                self.onchange();
              }
            }
          })
        }
      },
      SpanPicker:{
        create:function(container) {
          return Html.Span.create().into(container).extend(function(self) {
            return {
              onchange:function() {},
              //
              load:function(cv) {
                self.cv = cv;
                self.picker = cv.createPicker(cv).into(self);
                return self;
              },
              show:function() {
                Html._proto.show.call(self);
                self.picker.setFocus && self.picker.setFocus();
                return self;
              },
              setValue:function(value) {
                self.picker.setValue(value);
              },
              getValue:function() {
                self.cv.text_ = null;
                return self.picker.getValue();
              }
            }
          })
        }
      },
      SpanRecPicker:{
        create:function(container) {
          return CritRecForm.CritValueEntry.Spans.SpanPicker.create(container).extend(function(self) {
            return {
              onchange:function() {},
              //
              load:function(cv) {
                self.cv = cv;
                self.picker = cv.getRecPicker().create().into(self).bubble('onset', self).aug({
                  onchange:function(e) {
                    Html.Window.cancelBubble(e);
                  }
                })
                if (self.picker.load) 
                  self.picker.load();
                return self;
              },
              setValue:function(value) {
                if (self.cv._rec)
                  self.picker.set(self.cv._rec);
                else
                  self.picker.setValueText(self.cv.value, self.cv.text_);
              },
              getValue:function() {
                return self.picker.getValue();
              },
              onset:function(rec) {
                self.cv.text_ = self.picker.getText();
                self.cv.value = self.picker.getValue();
                self.cv._rec = rec;
                self.onchange();
              }
            }
          })
        }
      },
      InGroup:{
        create:function(container) {
          return Html.Span.create().into(container).extend(function(self) {
            return {
              onchange:function() {},
              //
              init:function() {
                self.input = Html.InputText.create().into(self).bubble('onchange', self);
              },
              show:function(cv) {
                Html._proto.show.call(self);
                self.cv = cv;
                self.input.setFocus();
                return self;
              },
              setValue:function(value) {
                self.input.setValue(value);
              },
              getValue:function() {
                self.cv.text_ = null;
                return self.input.getValue();
              }
            }
          })
        }
      },
      InGroup2:{
        create:function(container) {
          return Html.Tile.create(container).extend(function(self) {
            return {
              onchange:function() {},
              //
              show:function() {
                Html._proto.show.call(self);
                // TODO setfocus
                return self;
              },
              load:function(cv, MySpan) {
                self.cv = cv;
                self.values = cv.value ? cv.value.split(',') : []; 
                self.texts = cv.text_ ? cv.text_.split(',') : [];
                self.Span = MySpan;
                return self;
              },
              setValue:function() {
                self.Spans = [];
                for (var i = 0, j = self.values.length; i < j; i++) {
                  self.cv.value = self.values[i];
                  self.cv.text_ = self.texts[i];
                  self.addSpan(self.cv.value);
                }
                self.addSpan();
              },
              addSpan:function(value) {
                var span = self.Span.create(self).load(self.cv).set('index', self.Spans.length);
                span.bubble('onchange', self.span_onchange.curry(span));
                if (value)
                  span.setValue(value);
                self.Spans.push(span);
              },
              span_onchange:function(span) {
                if (span.getValue() && span.index == self.Spans.length - 1) {
                  self.addSpan();
                }
              },
              getValue:function() {
                var values = [], texts = [];
                self.Spans.each(function(span) {
                  if (span.getValue()) {
                    values.push(span.picker.getValue());
                    texts.push(span.picker.getText());
                  }
                })
                self.cv.text_ = texts.join(',');
                self.cv.value = values.join(',');
                return self.cv.value;
              }
            }
          })
        }
      }
    }
  }
}
/**
 * QuestionPopDeletable 
 */
var QPopJoin = {
  pop:function(q, onupdatetext, ondelete) {
    return Html.Pop.singleton_pop.apply(this, arguments);
  },
  create:function() {
    return QPopStandard.create().extend(QPopJoin, function(self, parent) {
      var _close = self.close;
      return {
        onupdatetext:function(seltext, selix) {},
        ondelete:function() {},
        //
        init:function() {
          Html.CmdBar.create(self.content).del(self.del_onclick);
        },
        pop:function(q, onupdatetext, ondelete) {
          parent(QPopStandard).pop(q, self.pop_onupdate);
          if (ondelete)
            self.ondelete = ondelete;
          if (onupdatetext)
            self.onupdatetext = onupdatetext;
        },
        loadSingleTile:function() {
          self.singleTile.load(self.sopts, self.design.scols, null).show();
        },
        del_onclick:function() {
          self.close();
          self.ondelete();
        },
        pop_onupdate:function(q) {
          self.onupdatetext(q.opts.getSelText(), q.opts.getSelIx());
        }
      }
    })
  } 
}
PctGraph = {
  create:function(width) {
    if (width == null)
      width = 100;
    return Html.Div.create('PctGraph').extend(function(self) {
      return {
        init:function() {
          self.ValueBar = Html.Tile.create(self, 'ValueBar').setWidth(width);
          self.Value = Html.Tile.create(self.ValueBar, 'Value');
          self.Pct = Html.Tile.create(self, 'Pct').setWidth(width);
        },
        reset:function() {
          self.Pct.setText();
          self.Value.setWidth(0);
          return self;
        },
        setValue:function(value) {
          self.Pct.setText(value + '%');
          self.Value.setWidth(width * value / 100);
          if (value >= 80)
            self.Value.style.backgroundColor = 'green';
          else if (value >= 50)
            self.Value.style.backgroundColor = '#FEE093';
          else
            self.Value.style.backgroundColor = 'red';
          return self;
        }
      }
    })
  }
}