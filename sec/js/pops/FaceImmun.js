/**
 * Facesheet Immunizations
 * Global static 
 * Requires: TableLoader.js, facesheet.css
 */
FaceImmun = {
  fs:null,
  changed:null,
  _scb:null,
  _POP:'fsp-imm',
  //
  pop:function(fs, zoom, callback, tab) {
    var self = this;
    Page.work(function() {
      self.fs = fs;
      self.changed = false;
      self._scb = Ajax.buildScopedCallback(callback || 'immunChangedCallback');
      Includer.get([Includer.HTML_FACE_IMMUN], function() {
        if (self.chart == null)
          self.chart = SchedImmTile.create(_$('fsp-imms-div'))
            .bubble('onupdate', self.update.bind(self))
            .bubble('onprint', self.fpPrint)
            .bubble('onexit', self.fpClose);
        self.tabbar = new TabBar('fsp-imm', ['Immunizations by Date', 'Immunizations by Vaccine', 'Scheduled Chart'], ['By Date', 'By Vaccine', 'Schedule']);
        Pop.setCaption('fsp-imm-cap-text', fs.client.name + ' - Immunizations');
        Pop.setCaption('pop-imme-cap-text', fs.client.name + ' - Immunization Entry');
        Pop.setCaption('pop-immc-cap-text', fs.client.name + ' - Immunization Chart');
        if (! me.Role.Patient.immuns) {
          _$('imm-act').invisible();
        }
        self._load();
        Page.work();
        if (zoom) {
          Pop.zoom(FaceImmun._POP);  
        } else {
          Pop.show(FaceImmun._POP);
        }
        if (tab)
          self.tabbar.select(tab);
      });
    });
  },
  update:function(fs) {
    this.fs.cuTimestamp = fs.cuTimestamp;
    this.fs.immuns = fs.immuns;
    this.fs.immunCd = fs.immunCd;
    this.changed = true;
    this._load();    
  },
  fpEdit:function(id) {
    if (me.Role.Patient.immuns)
      FaceImmunEntry.pop(this.fs, id, FaceImmun.update.bind(FaceImmun));
  },
  fpChart:function() {
    //FaceImmunChart.pop();
    if (! me.Role.Patient.immuns)
      return;
    this.tabbar.select(1);
    this.fpPrint();
  },
  fpPrint:function() {
    if (! me.Role.Patient.immuns)
      return;
    PrintImmPop.pop(this.fs, this.fpClose);
  },
  fpClose:function() {
    Pop.close();
    if (this.changed) 
      Ajax.callScopedCallback(this._scb, this.fs);
  },
  fpDownload:function() {
    if (! me.Role.Patient.immuns)
      return;
    VxuDownloader.pop(this.fs.client.clientId);
  },
  fpTest:function() {
    //SchedImmPop.pop(this.fs);
  },
  //
  _load:function() {
    var fs = this.fs;
    var tp = new TableLoader('fsp-immh-tbody', 'off', 'fsp-immh-div');
    if (fs.immuns) {
      for (var i = 0; i < fs.immuns.length; i++) {
        var immun = fs.immuns[i];
        var href = 'javascript:FaceImmun.fpEdit(' + immun.dataImmunId + ')';
        tp.createTrTd('main');
        tp.append(createAnchor(null, href, immun.uiDateCls('fs vital'), immun.uiDate()));
        tp.createTd(null, immun.name);
        tp.createTd(null, immun.tradeName);
        tp.createTd(null, null, bulletJoin([immun.manufac, immun.lot], true));
        tp.createTd(null, null, bulletJoin([immun.dose, immun.route], true));
      }
    }
    var map = fs.immunCd && fs.immunCd.getImmsByVcat();
    if (map) {
      var tp = new TableLoader('fsp-immh2-tbody', 'off', 'fsp-immh2-div');
      for (var vcat in map) {
        //tp.createTr(1);
        //tp.createTd('fscap2', vcat).colSpan = 5;
        var imms = map[vcat];
        if (imms.length) {
          for (var i = 0; i < imms.length; i++) {
            var immun = fs.immuns.get(imms[i].dataImmunId);
            var href = 'javascript:FaceImmun.fpEdit(' + immun.dataImmunId + ')';
            tp.createTr(vcat, [vcat]);
            tp.createTd('fscap2', vcat);
            tp.createTd('main');
            tp.append(createAnchor(null, href, immun.uiDateCls('fs vital'), immun.uiDate()));
            tp.createTd(null, immun.name);
            tp.createTd(null, immun.tradeName);
            tp.createTd(null, null, bulletJoin([immun.manufac, immun.lot], true));
            tp.createTd(null, null, bulletJoin([immun.dose, immun.route], true));
          }
        } else {
          tp.createTr(vcat, [vcat]);
          tp.createTd('fscap2', vcat);
          tp.createTd('main', '(None)');
          tp.createTd();
          tp.createTd();
          tp.createTd();
          tp.createTd();
        }
      }
    }
    this.chart.load(fs);
  }
}
ImmunTui = {
  create:function(container, fs) {
    return Html.TemplateUi.create_asEntry(container, fs, 'imm.').extend(function(self) {
      return {
        onupdate:function() {},
        //
        load:self.load.extend(function(_load, /*TemplateEntry*/ rec) {
          self.rec = rec;
          _load(rec.par);
        }),
        reset:self.reset.append(function() {
          if (self.rec)
            self.load(self.rec);
        }),
        getRecord_forSave:function() {
          var rec = self.getRecord();
          self.rec.saveLot(rec);
          return rec;
        },
        setRecord:self.setRecord.extend(function(_setRecord, rec) {
          self.lastKey = self.rec.getLotKey(rec);
          _setRecord(rec);
        }),
        //
        setLot:function() {
          var rec = self.getRecord();
          var key = self.rec.getLotKey(rec);
          if (key != self.lastKey) {
            self.lastKey = key;
            var lrec = self.rec.getLot(key);
            if (lrec) {
              if (lrec.lot != rec.lot) {
                self.setField('lot', lrec.lot);
                self.setField('dateExp', lrec.dateExp);
              }
            } else {
              self.setField('lot', null);
              self.setField('dateExp', null);
            }
          }
        },
        onchange:function(q) {
          self.setLot();
          self.onupdate();
        }
      }
    })
  }
}
/**
 * Immunization Entry
 */
FaceImmunEntry = {
  parent:null,
  fs:null,
  tui:null,
  id:null,
  dateform:null,
  //
  pop:function(fs, id/*null for add*/, onsave/*fs*/) {
    //var fs = FaceUi.setParentage(parent || FaceImmun, this);
    this.fs = fs;
    this.onsave = onsave;
    var self = this;
    Page.work(function() {
      if (self.tui == null) {
        self.tui = ImmunTui.create(_$('imme-tui'), fs)
          .bubble('onupdate', self._tui_onupdate);
        Ajax.Templates.getImmunEntry(fs.immunPid, function(rec) {
          self.tui.load(rec);
          self._pop(id);
        })
      } else {
        self.tui.reset();
        self._pop(id);
      }
      //self.tui.fetch(fs.immunPid, function() {
       // self._pop(id);
      //})
    })
  },
  _tui_onupdate:function() {
    var status = this.tui.getRecord().status; 
    if (status != this._status) {
      this._status = status;
      Pop.reposition();
    }
  },
  _pop:function(id) {
    this._addVisLink('');
    this._addVisLink('2');
    this._addVisLink('3');
    this._addUnits();
    this.id = id;
    _$('imme-audit').hide();
    if (this.dateform == null) {
      this.dateform = new EntryForm(_$('ul-imme-date'), '');
      this.dateform.li('Date Given', null, 'first120').datetime('date');
    }
    if (id) {
      var imm = this.fs.immuns.get(id);
      this.tui.setRecord(imm);
      this.dateform.setValue('date', imm.dateGiven);
      _$('imme-delete-span').show();
      _$('imme-print').show();
      if (imm._updated)
        _$('imme-audit').show();
      this._status = imm.status;
    } else {
      this.tui.setRecord({});
      this.dateform.setValue('date', DateValue.nowValue());
      _$('imme-delete-span').hide();
      _$('imme-print').hide();
      this._status = null;
    }
    Page.work();
    Pop.show(_$('pop-imme'));
    //Pop._pop.setTop(40);
    if (id == null)
      TemplateUi._pop(this.tui.getQuestion('immCert.+immunRecord.immunizationsAdmin').a, true);
  },
  _addUnits:function() {
    var quid = 'immCert.+immunRecord.dose';
    var li = this.tui.getQuestion(quid).tuiq;
    Html.Span.create(null, 'mL').into(li);
  },
  _addVisLink:function(suffix) {
    var quid = 'immCert.+immunRecord.$visForm' + suffix;
    var li = this.tui.getQuestion(quid).tuiq;
    var a = createAnchor(null, 'javascript:FaceImmunEntry.fpVis("' + suffix + '")', null, 'Select CDC VIS');
    Html.Span.create().add(a).into(li);
  },
  fpClose:function(saved) {
    var self = FaceImmunEntry;
    if (saved)
      Pop.close(true);
    else
      Pop.Confirm.closeCheckDirty(self, self.fpSave);
  },
  fpAudit:function() {
    var self = FaceImmunEntry;
    Ajax.Reporting.fetchImmunAudits(self.fs.clientId, self.id, function(recs) {
      if (recs && recs.length) {
        AuditViewer.pop(recs, recs[0]);
      } else {
        Pop.Msg.showCritical('No audits found.');
      }
    })
  },
  fpPrint:function() {
    //Html.ServerForm.submit('Immun', 'downloadSinglePdf', {'id':FaceImmunEntry.id});
    window.location.href = 'serverImmun.php?action=downloadSinglePdf&id=' + FaceImmunEntry.id;
  },
  isDirty:function() {
    return this.tui.isDirty();
  },
  fpVis:function(suffix) {
    this.visSuffix = suffix;
    Pop.show('pop-vis');
  },
  fpVisSel:function(a) {
    var form = a.innerText;
    var date = a.parentElement.parentElement.children[1].innerText;
    this.tui.setField('formVis' + this.visSuffix, form).setField('dateVis' + this.visSuffix, date);
    Ajax.Procedures.record(this.fs.client.clientId, 600179);  // ImmunInfoProvided
    Pop.close();
  },
  fpSave:function() {
    Page.workingCmd(true);
    var rec = this.tui.getRecord_forSave();
    rec.clientId = this.fs.client.clientId;
    rec.dateGiven = this.dateform.getRecord().date;
    var self = this;
    Ajax.Facesheet.Immuns.save(rec, 
      function(fs) {
        Page.workingCmd(false);
        //self.parent.update(fs);
        self.onsave(fs);
        Pop.close();
      });
  },
  fpDelete:function() {
    if (this.id) {
      var self = this;
      Pop.Confirm.showDeleteRecord(function() {
        Html.Window.working(true);
        Ajax.Facesheet.Immuns.remove(self.id,
          function(fs) {
            Html.Window.working(false);
            //self.parent.update(fs);
            self.onsave(fs);
            Pop.close();
          });
      });
    }
  },
  _formatDate:function(text) {
    return (text) ? 
      calFormatShortDate(calParse(text, CAL_FMT_SENTENCE)) : 
      null;
  }
};
/**
 * Immunization Chart
 */
FaceImmunChart = {
  fs:null,
  parent:null,
  _chart:null,
  _DATE_COLS:6,
  pop:function(fs) {
    var fs = FaceUi.setParentage(FaceImmun, this);
    var self = this;
    Page.work(function() {
      Lookup.getVacChart(
        function(chart) {
          self._chart = chart;
          self._load(fs);
          Page.work();
          Pop.show('pop-immc');
        });
    });
  },
  fpEdit:function(id) {
    FaceImmunEntry.pop(this.fs, id, FaceImmunChart.update.bind(FaceImmunChart));
  },
  fpPrint:function() {
    PrintImmPop.pop(this.fs, this.close);
    //Html.ServerForm.submit('Immun', 'downloadPdf', {'cid':this.fs.client.clientId});
    //Pdf_Immun.from(this.fs, _$('immc-div')).download();
    /*
    var ug = me.User.UserGroup;
    var uga = ug.Address;
    Page.pop(Page.PAGE_PRINT_POP, {
      'pop':'FaceImmun',
      'obj':'FaceImmunChart',
      'arg':this.fs.client.clientId,
      'titlel':'IMMUNIZATION CERTIFICATE<br>' + this.fs.client.name + '<br>Birth: ' + this.fs.client.birth + '<br>Printed: ' + DateUi.getToday(),
      'titler':ug.name + '<br>' + (uga.addr1 || '')  + '<br>Phone: ' + uga.phone1 + '<br>Fax: ' + uga.phone2});
    */
  },
  close:function() {
    Pop.close();
  },
  print:function(id) {
    var self = this;
    Lookup.getVacChart(
      function(chart) {
        self._chart = chart;
        Ajax.Facesheet.get(id, null, 
          function(fs) {  
            self._load(fs);
            printout('immc-tbl');
          });
      });
  },
  update:function(fs) {
    this._load(fs);
    this.parent.update(fs);
  },
  _load:function(fs) {
    this.fs = fs;
    if (fs.immuns) {
      var chart = this._buildChart();
      var t = new TableLoader('immc-tbody', 'off', 'immc-div');
      for (var cat in chart) {
        if (chart[cat].active) {
          t.createTr(0);
          t.createTd('immcat', cat);
          t.td.colSpan = this._DATE_COLS + 1; 
          var vacs = chart[cat].vacs;
          for (var vac in vacs) {
            t.createTr(1);
            t.createTd('immname', vac);
            t.td.style.width = '25%';
            var immuns = vacs[vac];
            for (var i = 0; i < this._DATE_COLS; i++) {
              if (i < immuns.length) {
                var a = createAnchor(null, 'javascript:FaceImmunChart.fpEdit(' + immuns[i].dataImmunId + ')', null, immuns[i]._dateOnly);
                t.createTdAppend('nowrap', a);
              } else {
                t.createTd(null, null, '&nbsp;');
              }
              t.td.style.width = '66px';
            }
          }
        }
      }
    }
  },
  /*
   * Returns {
   *   category:{
   *     'active':!,     
   *     'vacs':{
   *       vac:[immun,..],..
   *       },..
   *     },..
   *   }
   */
  _buildChart:function() {
    var fs = this.fs;
    var chart = {};
    var catByVac = {};
    for (var cat in this._chart) {
      chart[cat] = {
        'active':0,
        'vacs':{}
        };
      var vacs = this._chart[cat];
      for (var i = 0; i < vacs.length; i++) {
        catByVac[vacs[i]] = chart[cat];
      }
    }
    var other = {
      'active':0,
      'vacs':{}
      };
    chart['Other'] = other;
    for (var i = 0; i < fs.immuns.length; i++) {
      var immun = fs.immuns[i];
      var vacs = this._getVacs(immun.name);
      for (var j = 0; j < vacs.length; j++) {
        var vac = vacs[j];
        var cat = catByVac[vac] || other;
        cat.active = true;
        Map.unshiftInto(cat.vacs, vac, immun);
      }
    }
    return chart;
  },
  _xrefChartVacs:function(chart) {
  },
  _getVacs:function(name) {
    var vacs;
    var a = name.split('- ');
    if (a.length == 1) {
      vacs = [name];
    } else {
      vacs = a[1].split('/');
    }
    return vacs;
  }
};
PrintImmPop = {
  pop:function(fs, onprint) {
    return Html.Pop.singleton_pop.apply(PrintImmPop, arguments);
  },
  create:function() {
    return Html.Pop.create('Print Certificate', 400).extend(function(self) {
      return {
        init:function() {
          self.forms = {'':'(Standard Form)','KY':'Kentucky Certificate'};
          self.Entry = Html.UlEntry.create(self.content, function(ef) {
            ef.line().lbl('Form').select('cf', self.forms);
            ef.line().lbl('Expires').date('expires').ln('(Leave blank to auto-calculate)');
          })
          Html.CmdBar.create(self.content).print(self.print).cancel(self.close);
        },
        onshow:function(fs, onprint) {
          self.fs = fs;
          self.onprint = onprint;
        },
        print:function() {
          var form = self.Entry.getValue('cf');
          var until = self.Entry.getValue('expires');
/*          if (form == '') 
            Pdf_Immun.from(self.fs, _$('immc-div')).download();
          else */
            Html.ServerForm.submit('Immun', 'downloadPdf', {'cid':self.fs.client.clientId,'form':form,'until':until});
          self.close();
          self.onprint && self.onprint();
        }
      }
    })
  }
}
/**
 * Scheduled Immunizations Tile 
 */
SchedImmTile = {
  create:function(container) {
    var My = this;
    return Html.Tile.create(container).extend(function(self) {
      return {
        onclose:function() {},
        onprint:function() {},
        onupdate:function(fs) {},
        //
        init:function() {
          Html.Tile.create(self, 'CdcLinks')
            .add(Html.Anchor.create('qicon cdc', 'Immunization Schedules', self.cdc_onclick))
            .add(Html.Anchor.create('qicon cdc', 'Information Statements', self.vis_onclick));
          self.ImmTile = My.ImmTile.create(self)
            .bubble('onupdate', self);
          Html.SplitCmdBar.create(self)
            .print(Function.defer(self, 'onprint'), 'Print Certificate...')
            .split()
            .exit(Function.defer(self, 'onexit'))
            .end();
        },
        load:function(fs) {
          self.fs = fs;
          self.ImmTile.load(self.fs);
        },
        newCdcAnchor:function(url, text) {
          var a = Html.Anchor.create('qicon cdc', text);
          a.href = 'http://www.cdc.gov/vaccines/' + url;
          a.target = '_blank';
          return a;
        },
        cdc_onclick:function() {
          CdcPop.pop();
        },
        vis_onclick:function() {
          VisPop.pop();
        }
      }
    })
  },
  ImmTile:{
    create:function(container) {
      return Html.Tile.create(container, 'ImmTile').setId('ImmTile').extend(function(self) {
        return {
          onupdate:function(fs) {},
          //
          load:function(fs) {
            self.clean();
            self.fs = fs;
            fs.immunCd.ics && fs.immunCd.ics.each(self.draw);
          },
          draw:function(sched) {
            var tr = Html.Table.create(self).tbody().tr();
            tr.th(sched.vcat);
            sched.each(function(imm) {
              self.td(tr, Html.Anchor.create(null, imm.uiCell(), self.sched_onclick.curry(imm)));
            })
            if (sched.na) {
              tr.td(sched.na, 'na');
            } else {
              if (sched.dose)
                self.td(tr, Html.Anchor.create(null, sched.uiCell(), self.sched_onclick), sched.due ? 'due' : 'next');
              for (var i = 0; i < sched.left; i++)
                self.td(tr, null, 'left');
            }
          },
          td:function(tr, e, cls) {
            var div = Html.Div.create(cls);
            if (e)
              div.add(e);
            else
              div.html('&nbsp');
            tr.td(div);
          },
          sched_onclick:function(imm) {
            FaceImmunEntry.pop(self.fs, imm && imm.dataImmunId, function(fs) {
              self.onupdate(fs);
              self.load(fs);
            })
          }
        }
      })
    }
  }
}
/*
TestPop = {
  pop:function(cid) {
    return Html.Pop.singleton_pop.apply(TestPop, arguments);
  },
  create:function() {
    return Html.Pop.create('Test').withFrame().extend(function(self) {
      return {
        init:function() {
          var height = self.fullscreen(1000, 600);          
          self.Tile = Html.Tile.create(self.frame, 'fstab').withWorkingSmall().setWidth('100%').setHeight(height);
          Html.CmdBar.create(self.content).exit(self.close);
        },
        onshow:function(cid) {
          self.Tile.clean();
          self.Tile.working(function() {
            Ajax.get('Immun', 'test', cid, function(results) {
              self.Tile.working(false);
              self.load(results);
            })
          })
        },
        load:function(results) {
          Html.Tile.create(self.Tile, 'bw p10').html(results._html);
        }
      }
    })
  }
}
*/
CdcPop = {
  pop:function() {
    return Html.Pop.singleton_pop.apply(CdcPop, arguments);
  },
  create:function(src) {
    var My = this;
    return Html.Pop.create('CDC Website').extend(function(self) {
      return {
        init:function() {
          self.content.addClass('p0');
          My.TabPanel.create(self.content);
        }
      }
    })
  },
  TabPanel:{
    create:function(container) {
      var titles = ['Birth through 18 years', 'Catch-up Schedule', 'Adult Schedule', 'Medical and Other Indications', 'Adult Contraindications and Precautions'];
      var tabs = ['0-18', 'Catch-up', 'Adult', 'Adult Medical', 'Contraindications'];
      return Html.TabPanels.create(container, titles, tabs).extend(function(self) {
        var srcs = ['http://www.cdc.gov/vaccines/schedules/hcp/imz/child-adolescent-shell.html','http://www.cdc.gov/vaccines/schedules/hcp/imz/catchup-shell.html','http://www.cdc.gov/vaccines/schedules/hcp/imz/adult-shell.html','http://www.cdc.gov/vaccines/schedules/hcp/imz/adult-conditions-shell.html','http://www.cdc.gov/vaccines/schedules/hcp/imz/adult-contraindications-shell.html'];
        return {
          init:function() {
            self.addIFrames();
            self.select(0);
          },
          addIFrames:function() {
            for (var i = 0; i < 5; i++) 
              self.addIFrame(i);
          },
          addIFrame:function(index) {
            var w = Html.Window.getViewportDim();
            var panel = self.panels.get(index);
            panel.IFrame = Html.IFrame.create().fullsize(112).into(panel);
          },
          panel_onselect:function(panel) {
            if (! panel._loaded) {
              panel.IFrame.nav(srcs[panel.index]);
              panel._loaded = true;
            }
          }
        }
      })
    }
  }
}
VisPop = {
  pop:function() {
    return Html.Pop.singleton_pop.apply(VisPop, arguments);
  },
  create:function() {
    return Html.BrowserPop.create('CDC Website', 'http://www.cdc.gov/vaccines/hcp/vis/index.html');
  }
}


