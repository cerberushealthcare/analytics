TrackItemEntry = {
  //
  pop:function(rec) {
    return TrackItemEntry = this.create().pop(rec);
  },
  create:function() {
    var self = Html.RecordEntryPop.create('Tracking Item Entry', 740);
    return self.aug({
      onsave:function(rec) {},
      //
      buildForm:function(ef) {
        ef.aug({
          sched_onset:function(text) {
            ef.showIf('li-sched1', text);
            ef.showIf('li-sched2', text);
          },
          closed_onset:function(text) {
            if (text && self.rec.status < C_TrackItem.STATUS_CLOSED) {
              ef.setValue('closedFor', C_TrackItem.CLOSED_FOR_RECEIVED);
            }
            ef.showIf('li-close1', text);
            ef.showIf('li-close2', text);
          },
          type_onset:function() {
            var type = ef.getValue('rp_type');
            ef.showIf('li-repeats', type > C_TrackEvent.TYPE_NONE);
            ef.showIf('li-repeats2', type > C_TrackEvent.TYPE_NONE);
            ef.setValue('rp_onlbl', C_TrackEvent.LTYPES[type]);
            ef.showIf('rp-by-span', type == C_TrackEvent.TYPE_MONTH);
            ef.showIf('rp-on-span', type == C_TrackEvent.TYPE_WEEK);
          },
          doctor_onset:function(rec) {
            if (rec && rec.Address_addrFacility) 
              ef.setValue('schedLoc', rec.Address_addrFacility);
          },
          getDoctor:function() {
            return ef.fields['schedWith'].rec;
          },
          getFacility:function() {
            return ef.fields['schedLoc'].rec;
          },
          getSchedDate:function() {
            var date = ef.fields['schedDate'].dateTimeInput.di.getText();
            if (date) {
              var dv = new DateValue(date);
              return dv.getDowName() + ', ' + dv.toString_verbose();
            }
          },
          getSchedTime:function() {
            return ef.fields['schedDate'].dateTimeInput.getTimeText()
          }
        })
        self.rspan = Html.Span.create('ml5');
        ef.li('Item').textbox('trackDesc', 40).lbl('Priority').select('priority', C_TrackItem.PRIORITIES);
        ef.li('Ordered').ro('_ordered');
        ef.li('Diagnosis').picker(DiagIcdPicker, 'diagicd');
        // ef.li('Frequency').textbox('freq', 20).lbl('Duration').textbox('duration', 20);
        ef.li('Due').date('dueDate').lbl('Repeats').select('rp_type', C_TrackEvent.TYPES, null, ef.type_onset, true);
        ef.li().id('li-repeats')
          .lbl('Every').select('rp_every', C_TrackEvent.EVERYS, null, null, true).lblf('rp_onlbl')
          .startSpan('rp-on-span').ln('on').check('rp_on0', 'Sun').check('rp_on1', 'Mon').check('rp_on2', 'Tue').check('rp_on3', 'Wed').check('rp_on4', 'Thu').check('rp_on5', 'Fri').check('rp_on6', 'Sat').endSpan()
          .startSpan('rp-by-span').ln('by').select('rp_by', C_TrackEvent.BYS, null, null, true).endSpan();
        ef.li().id('li-repeats2')
          .lbl('Until').date('rp_until');
        ef.li('Notes').textarea('orderNotes', 2);
        ef.li('Scheduled / To Be Obtained', 'mt15').datetime('schedDate', ef.sched_onset).lblf('_sched', 'nopad').ln('or').check('selfRefer', 'Self-referral');
        ef.li('With').id('li-sched1').picker(ProviderPicker, 'schedWith', 'Provider_schedWith', ef.doctor_onset).lbl('Location').picker(FacilityPicker, 'schedLoc', 'Address_schedLoc').endSpan();
        ef.li('Notes').textarea('schedNotes');
        ef.li(' ','sched').id('li-sched2')
          .check('summsent', 'Patient Summary Record Sent')
          .append(Html.AnchorAction.asPrint('Print Appointment Card...', self.apptcard_onclick));
        ef.li('Collected / Performed', 'mt15').datetime('drawnDate');
        ef.li('Closed', 'mt15').date('closedDate', ef.closed_onset).lblf('_closed', 'nopad');
        ef.li('Reason', 'mt5').id('li-close1').select('closedFor', C_TrackItem.CLOSED_FORS).append(self.rspan);
        ef.li('Notes').id('li-close2').textarea('closedNotes');
      }, 
      onload:function() {
        self.rec.diagicd = DiagIcd.from(self.rec.icd, self.rec.diagnosis, self.rec.icd10);
        self.rspan.clean();
        if (self.rec.DocProc) 
          AnchorDocStub_Preview.create(self.rec.DocProc).into(self.rspan);
        else if (self.rec.DocScan)
          AnchorDocStub_Preview.create(self.rec.DocScan).into(self.rspan);
        self.form.setRecord(self.rec);
      },
      save:function(rec, onsuccess, onerror) {
        self.rec.diagnosis = self.rec.diagicd && self.rec.diagicd.diag;
        self.rec.icd = self.rec.diagicd && self.rec.diagicd.icd;
        self.rec.icd10 = self.rec.diagicd && self.rec.diagicd.icd10;
        if (! String.isBlank(rec.schedDate) || ! String.isBlank(rec.closedDate)) {
          rec.rp_type = null;  // don't save update to repeats
        }
        Ajax.Tracking.update(rec, onsuccess);
      },
      buildCmd:function(cb) {
        //cb.save(self.save_onclick).copy('Create Duplicate(s)...', self.copy_onclick).cancel(self.cancel_onclick);
        cb.save(self.save_onclick).cancel(self.cancel_onclick);
      },
      apptcard_onclick:function() {
        self.working(function() {
          self.save(self.getRecord(), function() {
            self.working(false);
            ApptCardPop.pop(self.rec.clientId, self.form.getDoctor(), self.form.getFacility(), self.form.getSchedDate(), self.form.getSchedTime());
          })
        })
      },
      copy_onclick:function() {
        // TODO
      }
    });
  }
}
//
AddByIpPop = {
  pop:function(fs, onsave) {
    return Html.Pop.singleton_pop.apply(AddByIpPop, arguments);
  },
  create:function() {
    return Html.Pop.create('Add Order').withFrame().extend(function(self) {
      return {
        init:function() {
          self.Form = Html.UlEntry.create(self.frame, function(ef) {
            ef.line().l('Item').pick('ipc', null, IpcPicker, self.ipc_onset).lr().hidden('trackDesc').hidden('cptCode')
              .line().l('Category').select('trackCat', C_TrackItem.TCATS)
              .line().l('Priority').select('priority', C_TrackItem.PRIORITIES, '')
              .line().l('Notes').textbox('orderNotes', 40);
          })
          Html.CmdBar.create(self.content).save(self.save_onclick, 'Save to Tracking Sheet').cancel(self.close);
        },
        onshow:function(fs, onsave) {
          self.fs = fs;
          self.onsave = onsave;
          self.Form.reset().focus('ipc');
        },
        ipc_onset:function(ipc) {
          self.ipc = ipc;
          var cat = ipc && ipc.cat ? ipc.cat : C_TrackItem.TCAT_OTHER;
          self.Form.setValue('trackCat', cat);
          self.Form.setValue('trackDesc', ipc && ipc.name);
          self.Form.setValue('cptCode', ipc && ipc.codeSystem == 'C4' && ipc.code);
        },
        save_onclick:function() {
          if (self.ipc == null) {
            return Pop.Msg.showCritical('An item must be selected.', function() {
              self.Form.focus('ipc');
            })
          }
          var rec = self.Form.getRecord();
          rec.clientId = self.fs.clientId;
          self.working(function() {
            Ajax.Tracking.saveOrderByIp(rec, function() {
              self.working(false);
              self.close();
              self.onsave();
            })
          })
        }
      }
    })
  }
}
AddOrdersPop = {
  //
  pop:function(/*Facesheet*/fs) {
    return AddOrdersPop = this.create(fs).pop();  // OK to pass fs to create() as long as only one facesheet can be supplied per page load
  },
  create:function(fs) {
    var My = this;
    var self = Html.Pop.create('Add Orders', 750);
    return self.aug({
      onsave:function() {},
      //
      init:function() {
        self.form = Html.TemplateUi.create(self.content, fs).setHeight(200).addClass('scroll p10');
        self.cmd = Html.Pop.CmdBar.create(self)
          .button('Generate Order(s)...', self.order_onclick, 'gen-order', 'order')
          .cancel(self.close);
        var table = Html.Table2Col.create(self.content,
          self.diag = My.DiagTile.create(),
          self.cmd.wrapper);
        table.left.style.verticalAlign = 'bottom';
      },
      onshow:function() {
        self.form.reset().load(MyPar.ajax(self.form).fetchOe);
      },
      //
      order_onclick:function() {
        var diagicd = self.diag.getDiagIcd();
        var items = [];
        var rec = self.form.tui.getSelected();
        for (var qref in rec) {
          var q = self.form.tui.qs[qref];
          if (q.Opts.hasTracking()) {
            var opts = rec[qref];
            for (var i = 0; i < opts.length; i++) {
              var opt = opts[i];
              items.push(OrderItem.fromFs(fs, q, opt, diagicd));
            }
          }
        }
        if (items)  
          OrderSheet.pop(items, true).aug({
            onsave:function() {
              self.close();
              self.onsave();
            }
          }); 
      }
    });
  },
  DiagTile:{
    create:function() {
      return Html.Div.create().extend(function(self) {
        return {
          //
          init:function() {
            self.form = Html.UlEntry.create(self, function(ef) {
              ef.line().lbl('Diagnosis', 'nopad').picker(DiagIcdPicker, 'diagicd');
            })
          },
          getDiagIcd:function() {
            var rec = self.form.getRecord();
            return rec.diagicd;
          }
        }
      })
    }
  }
}
MultiAreaAtab = {
  create:function() {
    return Html.AnchorTab.create().checks(C_Areas, 3).okCancel();
  }
}
//
OrderSheet = {
  //
  pop:function(/*OrderItem[]*/recs, /*bool*/checkall) {
    return OrderSheet = this.create().pop(recs, checkall);
  },
  create:function() {
    var self = Html.Pop.create('Order Sheet', 800);
    return self.aug({
      onsave:function() {},
      //
      init:function() {
        self.table = OrderSheet.Table.create(self.content).bubble('onanychecked', self.table_onanychecked);
        self.cb = Html.Pop.CmdBar.create(self).saveCancel('Save to Tracking Sheet', 'save');
      }, 
      pop:function(recs, checkall) {
        self.table.reset();
        self.show();
        self.table.load(recs, checkall);
        return self;
      },
      save_onclick:function() {
        var recs = self.table.getCheckedRecs();
        self.working(function() {
          Ajax.Tracking.saveOrder(recs, function() {
            self.working(false);
            self.close();
            self.onsave();
          })
        })
      },
      //
      table_onanychecked:function(b) {
        self.cb.disable('save', ! b);
      }
    });
  },
  Table:{
    create:function(container) {
      return Html.TableLoader.create(container, 'fsgr single grid').extend(function(self) {
        return {
          onanychecked:function(b) {},  // b=true on first checked, false on last unchecked 
          //
          init:function() {
            self.setHeight(200);
            self.addHeader().th(null, 'check').th('Category').w('20%').th('Item').w('30%').th('Priority').w('10%').th('Notes').w('40%');
          },
          load:function(orderItems, checkall) {
            self.checkall = checkall;
            self.working(function() {
              Ajax.Tracking.order(orderItems, function(tracksheet) {
                self._load(tracksheet.items);
                if (! self.anychecked)
                  self.onanychecked(false);
                self.working(false);
              })
            })
          },
          reset:self.reset.prepend(function() {
            self.anychecked = false;
          }),
          add:function(rec, tr) {
            var checked = self.checkall || rec.trackItemId || rec.trackCat != C_TrackItem.TCAT_OTHER;
            rec._selectCat = Html.Select.create(C_TrackItem.TCATS).setValue(rec.trackCat);
            rec._selectPrior = Html.Select.create(C_TrackItem.PRIORITIES).setValue(rec.priority);
            rec._inputNotes = Html.InputText.create().setSize(40).setValue(rec.orderNotes);
            tr.check(rec, checked, self.check_onclick).td(rec._selectCat).td(rec.trackDesc).td(rec._selectPrior).td(rec._inputNotes);
          },
          getCheckedRecs:function() {
            var recs = self.getCheckRecs();
            Array.forEach(recs, function(rec) {
              rec.trackCat = rec._selectCat.getValue();
              rec.priority = rec._selectPrior.getValue();
              rec.orderNotes = rec._inputNotes.getValue();
            });
            return recs;
          },
          //
          check_onclick:function(b) {
            if (b && self.anychecked)
              return;
            else if (! b && self.getCheckRecs().length)
              return;
            self.onanychecked(self.anychecked = b);
          },
          rowOffset:function(rec) {
            return 1;
          }
        }
      })
    } 
  } 
}
//
ApptCardPop = {
  //
  pop:function(/*Doctor*/doctor, /*Facility*/facility) {
    return Html.Pop.singleton_pop.apply(ApptCardPop, arguments);
  },
  create:function() {
    return Html.Pop.create('Appointment Card', 600).extend(function(self) {
      return {
        init:function() {
          self.Tui = Html.TemplateUi.create_asParagraph(self.content).scrollable(350);
          Html.CmdBar.create(self.content)
            .print(self.print_onclick)
            .button('Send to Portal', self.send_onclick, 'message')
            .exit(self.close);
        },
        //
        onshow:function(cid, doctor, facility, date, time) {
          self.Tui.reset();
          self.cid = cid;
          MyPar.ajax(self.Tui).fetchAppt(function(par) {
            par.get('$doctor').setDefaultText(doctor && doctor.getName());
            par.get('$addr').setDefaultText(facility && facility.getNameAddress());
            par.get('$date').setDefaultText(date);
            par.get('$time').setDefaultText(time);
            self.Tui.load(par);
          })
        },
        print_onclick:function() {
          self.create();
          self.close();
        },
        send_onclick:function() {
          self.create(function(rec) {
            rec.ajax().sendToPortal();
          })
        },
        create:function(callback) {
          var h = self.Tui.getHtml();
          ClientDoc.ajax(self).createReferralCard(self.cid, self.Tui.getHtml(), callback);
        }
      }
    })
  }
}
//
DiagIcdPicker = {
  create:function() {
    var My = this;
    return Html.RecPicker.create(60, My.Pop, Html.InputText).extend(function(self) {
      return {
        init:function() {
          self.anchor.setClass('action view').setText('ICD9');
          self.anchor10 = Html.Anchor.create('action view', 'ICD10', self.anchor10_onclick).into(self);
        },
        getValueFrom:function(diagicd) {
          return diagicd;
        },
        getTextFrom:function(diagicd) {
          return diagicd.getText();
        },
        anchor10_onclick:function() {
          My.Pop10.pop(self.value, self.getText())
            .bubble('onselect', self.pop_onselect)
            .bubble('onclose', self.pop_onclose);          
        }
      }
    })
  },
  Pop:Object.create({
    onselect:function(diagicd) {},
    pop:function(diagicd, text) {
      var self = this;
      diagicd = diagicd || DiagIcd.fromText(text);
      var icd10 = diagicd && diagicd.icd10;
      Header.icdLook(diagicd && diagicd.icd, diagicd && diagicd.diag, function(icd, desc) {
        self.onselect(DiagIcd.from(icd, desc, icd10));
      })
      return self;
    }
  }),
  Pop10:Object.create({
    onselect:function(diagicd) {},
    pop:function(diagicd, text) {
      var self = this;
      diagicd = diagicd || DiagIcd.fromText(text);
      var icd9 = diagicd && diagicd.icd;
      Header.icdLook10(diagicd && diagicd.icd10, diagicd && diagicd.diag, function(icd, desc) {
        self.onselect(DiagIcd.from(icd9, desc, icd));
      })
      return self;
    }
  })
}
//
/** Data */
OrderItem = Class.define(
  function(cid, sid, key, tcat, tdesc, cpt, diag, icd) {
    this.cid = cid;
    this.sid = sid;
    this.key = key;
    this.tcat = tcat;
    this.tdesc = tdesc;
    this.cpt = cpt;
    this.diag = diag;
    this.icd = icd;
  },{  
  },{  
    buildKey:function(qidi, oix) {  // returns 'qidi#oix' e.g. '21600@2131#19'
      return qidi + '#' + oix;
    },
    fromFs:function(fs, q, opt, diagicd) {
      return new OrderItem(
        fs.client.clientId, 
        '0', 
        OrderItem.buildKey(q.id, opt.index),
        opt.tcat || '99',
        opt.text || opt.uid,
        opt.cpt,
        diagicd && diagicd.diag,
        diagicd && diagicd.icd);
    }
  }
)
DiagIcd = Object.Rec.extend({
  diag:null,
  icd:null,
  icd10:null,
  //
  getText:function() {
    var a = [], icds = [];
    if (this.icd) 
      icds.push(this.icd);
    if (this.icd10)
      icds.push(this.icd10);
    if (this.diag)
      a.push(this.diag);
    if (icds.length)
      a.push('(' + icds.join(', ') + ')');
    return a.join(' ');
  },
  fromText:function(text) {
    var a = text.split('(');
    if (a.length == 1) {
      return this.from(null, text, null);
    }
    var icds, icd9, icd10, icds, icd, i, d;
    icds = (a[1].split(')')[0]).split(',');
    if (icds.length == 2) {
      icd9 = icds[0].trim();
      icd10 = icds[1].trim();
    } else {
      icd = icds[0].trim();
      if (icd.length >= 3) {
        d = icd.indexOf('.');
        i = icd.substr(0, 1);
        if (isNaN(i)) {
          icd10 = icd;
        } else {
          icd9 = icd;
        }
      }
    }
    return this.from(icd9, text, icd10);
  },
  from:function(icd, diag, icd10) {
    return this.revive({icd:icd, diag:diag, icd10:icd10});
  }
})
