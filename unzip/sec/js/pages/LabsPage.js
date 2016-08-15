/**
 * Reporting Page
 * @author Warren Hornsby
 */
LabsPage = page = {
  //
  start:function(query) {
    LabsTile.create(_$('tile')).showInbox();
    Page.setEvents();
  },
  onresize:function() {
    var i = Html.Window.getViewportDim().height - 200;
    if (i != self.maxHeight) {
      self.maxHeight = i;
      LabsTile.setMaxHeight(i);
    }
  },
  popPdf:function() {
    LabsTile.popPdf();
  },
  resetErrorUi:function() {
    page._errorUis = [];
  },
  setErrorUi:function(e, msg) {
    if (msg)  
      page._errorUis.push(e);
  },
  getErrorUi:function() {
    if (page._errorUis.length)
      return page._errorUis[0];
  },
  getErrorCt:function() {
    return page._errorUis.length;
  },
  scrollError:function() {
    // Html.Animator.scrollTo(LabsTile.Recon.Tile, page.getErrorUi(), 10);
    page.getErrorUi().setFocus();
  }
}
/**
 * Tile LabsTile
 *   Tiles Views
 */
LabsTile = {
  create:function(container) {
    container.clean();
    return LabsTile = Html.Tile.create(container).extend(function(self) {
      return {
        init:function() {
          self.H1 = _$('h1');
          self.Views = Html.Tiles.create(self, [
            self.Inbox = LabInboxTile.create(self)
              .bubble('onselect', self.Inbox_onselect),
            self.Recon = LabReconTile.create(self)
              .bubble('oncancel', self.showInbox)
              .bubble('ondelete', self.Recon_ondelete)
              .bubble('onsave', self.Recon_onsave)]);
        },
        showInbox:function() {
          self.inboxId = null;
          self.H1.setText('Labs: Inbox');
          self.Views.select(self.Inbox);
        },
        showRecon:function(inbox) {
          LabRecon.ajax().fetch(inbox, function(rec) {
            self.inboxId = rec.Inbox.hl7InboxId;
            self.H1.setText('Labs: ' + rec.Inbox.Lab.name);
            self.Views.select(self.Recon.load(rec));
          })
        },
        popPdf:function() {
          if (self.inboxId)
            Page.popLabPdf(self.inboxId);
        },
        setMaxHeight:function(i) {
          self.Views.setMaxHeight(i);
        },
        Inbox_onselect:function(rec) {
          self.showRecon(rec);
        },
        Recon_onsave:function() {
          self.showInbox();
          self.Inbox.load();
        },
        Recon_ondelete:function() {
          self.showInbox();
          self.Inbox.load();
        }
      }
    })
  }
}
/**
 * LabInboxTile
 */
LabInboxTile = {
  create:function(container) {
    var My = this;
    return Html.Tile.create(container).extend(function(self) {
      return {
        onselect:function(rec) {},
        //
        init:function() {
          self.table = My.Table.create(self)
            .bubble('onselect', self)
            .bubble('onload', self.table_onload);
          self.cmd = Html.CmdBar.create(self)
            .button('Upload...', self.upload_onclick, 'upload');
          self.load();
        },
        load:function() {
          self.table.load();
        },
        setMaxHeight:function(i) {
          self.table.setMaxHeight(i - 50);
        },
        upload_onclick:function() {
          LabUploadPop.pop(function(o) {
            self.load();
          })
        },
        table_onload:function(recs) {
          if (recs.length == 0) {
            var info = _$('info');
            info.centerWithin(self).show();
          } else {
            _$('info').hide();
          }
        }
      }
    })
  },
  Table:{
    create:function(container) {
      return Html.TableLoader.create(container, 'fsb grid').extend(function(self) {
        return {
          onselect:function(rec) {},
          //
          init:function() {
            self.thead().trFixed().th('Lab Message').w('30%').th('Received').w('20%').th('Source').w('50%');
          },
          //
          fetch:function(callback_recs) {
            Hl7Inboxes.ajax().fetch(callback_recs);
          },
          add:function(rec, tr) {
            tr.select(Html.AnchorAction.create('msg', rec.patientName)).td(rec.dateReceived).td(rec.uiSource());
          },
          setMaxHeight:function(i) {
            self.setHeight(i);
          }
        }
      })
    }
  }
}
LabUploadPop = {
  pop:function(callback) {
    return Html.Pop.singleton_pop.apply(LabUploadPop, arguments);
  },
  create:function() {
    return Html.UploadPop.create('HL7 Message Upload', 'uploadLab').extend(function(self) {
      return {
        init:function() {
          Html.H3.create('Select HL7 file').before(self.form);
        },
        onpop:function(callback) {
          self.callback = callback;
        },
        oncomplete:function() {
          self.callback && self.callback();
        },
        working:function(b) {
          if (b)
            Pop.Working.show('Importing...');
          else
            Pop.Working.close();
        }        
      }
    })
  }
}
/**
 * LabReconTile
 */
LabReconTile = {
  create:function(container) {
    var My = this;
    return LabReconTile = Html.Tile.create(container).extend(function(self) {
      return {
        oncancel:function() {},
        onsave:function() {},
        ondelete:function() {},
        //
        init:function() {
          self.Msg = Html.Tile.create(self, 'Msg');
          self.Tile = My.Tile.create(self)
            .bubble('onupdate', self.load);
          self.Cmd = Html.CmdBar.create(self)
            .save(self.save_onclick, 'Save Into Chart')
            .del(self.del_onclick.confirm('delete this message'), 'Delete Message')
            .cancel(self.cancel_onclick);
        },
        load:function(recon) {
          Ipcs.ajax().fetchAll();
          page.resetErrorUi();
          self.recon = recon;
          self.Tile.load(recon);
          self.Cmd.get('save').showIf(recon.getClient());
          self.drawMsg();
          return self;
        },
        setMaxHeight:function(i) {
          i = i - self.Cmd.height() - self.Msg.getHeight() - 20;
          self.Tile.setMaxHeight(i);
        },
        isDirty:function() {
          return self.Tile.isDirty();
        },
        //
        ontileselect:function() {
          self.Tile.scrollToTop();
        },
        drawMsg:function() {
          if (page.getErrorCt())
            self.Msg.html('Please correct highlighted errors below. <a href="javascript:page.scrollError()">Jump to first error</a>');
          else
            self.Msg.html("Please review information and save into selected patient's chart.");
        },
        save_onclick:function() {
          var msg = self.Tile.getRecord();
          self.recon.ajax().save(self.Tile.getRecord(), function(recon) {
            if (recon)
              self.load(recon);
            else
              self.onsave();
          })
        },
        del_onclick:function() {
          self.recon.Inbox.ajax().remove(self.ondelete);
        },
        cancel_onclick:function() {
          if (self.isDirty()) 
            Pop.Confirm.showDirtyExit(self.save_onclick, self.oncancel)
          else
            self.oncancel();
        }
      }
    })
  },
  Tile:{
    create:function(container) {
      var My = this;
      return Html.Tile.create(container, 'ScrollTile').extend(function(self) {
        return {
          onupdate:function(recon) {},
          //
          init:function() {
            self.Header = My.Header.create(self);
            self.Pid = My.Pid.create(self)
              .bubble('onupdate', self);
            self.Obrs = My.Obrs.create(self);
          },
          reset:function() {
            self.Header.reset();
            self.Pid.reset();
            self.Obrs.reset();
          },
          load:function(recon) {
            self.reset();
            self.msg = recon.Msg;
            
            self.inbox = recon.Inbox;
            self.Header.load(self.inbox, self.msg);
            self.Pid.load(self.inbox, self.msg);
            self.Obrs.load(self.msg);
          },
          getRecord:function() {
            var msg = self.Obrs.getRecord();
            msg.PatientId.Client_.reviewer_ = self.Header.getReviewer();
            return msg;
          },
          isDirty:function() {
            return self.Pid.isDirty() || self.Obrs.isDirty(); 
          },
          //
          setMaxHeight:function(i) {
            self.setHeight(i);
          }
        }
      })
    },
    Header:{
      create:function(container) {
        return SegTile.create_noBox(container).extend(function(self) {
          return {
            load:function(inbox, msg) {
              self.addClass('hs');
              self.msg = msg;
              var orc = msg.getCommonOrder();
              self.form(function(ef) {
                ef.line()
                    .l('Received').lro(inbox.dateReceived)
                  .line()
                    .l('Source').lro(inbox.uiSource())
                  .line()
                    .l('Order').lro(orc.placerOrder && orc.placerOrder.id)
                    .l('Priority').lro(orc.qtyTiming && orc.qtyTiming.priority)
                    .append(Html.Anchor.create('find ml10', 'View Source Details...', LabMessagePop.pop.curry(msg)));
              })
              self.SendTo = self.recon('Send to', Html.Select.create(C_Docs, ''));
              if (msg.PatientId.Client_)
                self.SendTo.setValue(msg.PatientId.Client_.reviewer_);
            },
            getReviewer:function() {
              return self.SendTo.getValue();
            }
          }
        })
      }
    },
    Pid:{
      create:function(container) {
        return SegTile.create(container).extend(function(self) {
          return {
            onupdate:function(recon) {},
            //
            load:function(inbox, msg) {
              self.inbox = inbox;
              self.msg = msg;
              var pid = msg.PatientId;
              self.cid = pid.Client_ && pid.Client_.clientId;
              self.form(function(ef) {
                ef.line()
                  .lh2(inbox.patientName);
                ef.line()
                  .l('ID').lro(pid.uiPatientId())
                  .l('Birth').lro(pid.birthDate && pid.birthDate._date)
                  .l('Gender').lro(pid.uiGender())
                  .l('Race').lro(pid.uiRace());
                ef.line()
                  .l('Address').lro(pid.uiAddress());
                if (pid.phoneHome) {
                  ef.line()
                    .l('Home').lro(pid.phoneHome.phone);
                }
                if (pid.phoneWork) {
                  ef.line()
                    .l('Work').lro(pid.phoneWork.phone);
                }
                if (pid.account) {
                  ef.line()
                    .l('Account').lro(pid.account.id);
                }
                self.Client = self.recon('Matched to Patient',
                  ClientSelector.create_asRequired(pid.error_).set(pid.Client_).bubble('onset', self.client_onset));
                page.setErrorUi(self.Client, pid.error_);
              })
            },
            client_onset:function(rec) {
              if (rec.clientId != self.cid) { 
                self.inbox.ajax(self).setClient(rec.clientId, self.onupdate);
                self._dirty = true;
              }
            },
            isDirty:function() {
              return self._dirty;
            }
          } 
        })
      }
    },
    Obrs:{
      create:function(container) {
        var My = this;
        return Html.Tile.create(container).extend(function(self) {
          return {
            reset:function() {
              self.clean();
            },
            load:function(msg) {
              self.msg = msg;
              var obrs = msg.getObsRequests();
              var client = msg.getClient();
              self.Obrs = [];
              obrs.each(function(obr) {
                self.Obrs.push(My.Obr.create(self, obr, client));
              })
            },
            getRecord:function() {
              self.Obrs.each(function(Obr) {
                Obr.applyTo();
              })
              return self.msg;
            },
            isDirty:function() {
              for (var i = 0; i < self.Obrs.length; i++) {
                if (self.Obrs[i].isDirty())
                  return true;
              }
            }
          }
        })
      },
      Obr:{
        create:function(container, obr, client) {
          var My = this;
          return SegTile.create(container).extend(function(self) {
            return {
              init:function() {
                self.form(function(ef) {
                  ef.line().lh2(obr.seq + ': ' + obr.uiServiceId());
                  ef.line().l('Date').lro(obr.obsDateTime._date);
                })
                if (obr.Proc_)
                  self.Ipc = self.recon('Matched to Test/Procedure',
                    IpcPicker_Lab.create(obr.uiServiceId()).reset(obr.Proc_.Ipc),
                    obr.error_);
                if (client == null) {
                  self.Recon.hide();
                } else if (client.TrackItems) {
                  self.Order = self.recon('Matched to Order',
                    OrderPicker.create('(None)').loadOpenOrders(client.clientId, client.TrackItems));
                  if (obr.TrackItem_)
                    self.Order.resetValue(obr.TrackItem_);
                }
                self.Obxs = [];
                var Tile = Html.Tile.create(self.Box, 'obxs');
                Array.each(obr.getObservations(), function(obx) {
                  self.Obxs.push(My.Obx.create(Tile, obr, obx, client));
                })
                if (obr.Comment)
                  Html.UlEntry.create(self.Box, function(ef) {
                    ef.line().lbl('Notes').lro(obr.uiComments());
                  })
              },
              applyTo:function() {
                if (obr.Proc_)
                  obr.Proc_.ipc = self.Ipc.getValue();
                if (self.Order)
                  obr.TrackItem_ = self.Order.getValue();
                self.Obxs.each(function(Obx) {
                  Obx.applyTo();
                })
              },
              isDirty:function() {
                return self.isIpcDirty() || self.isOrderDirty() || self.isObxsDirty();
              },
              //
              isIpcDirty:function() {
                return self.Ipc && self.Ipc.isDirty();
              },
              isOrderDirty:function() {
                return self.Order && self.Order.isDirty(); 
              },
              isObxsDirty:function() {
                for (var i = 0; i < self.Obxs.length; i++) {
                  if (self.Obxs[i].isDirty()) 
                    return true;
                }
              }
            }
          })
        },
        Obx:{
          create:function(container, obr, obx, client) {
            return SegTile.create_noBox(container).extend(function(self) {
              return {
                init:function() {
                  self.head();
                  if (obx.pdf_) {
                    self.form(function(ef) {
                      ef.line().append(Html.Anchor.create('action tpdf', 'View PDF...', page.popPdf));
                    })
                  } else {
                    self.form(function(ef) {
                      ef.line().lh2(obr.seq + '.' + obx.seq + ': ' + obx.obsId.text);
                      ef.line().l('Value').lro(obx.uiValue()).lbl(obx.uiAbnormal(), 'abnormal');
                      if (obx.range)
                        ef.line().lbl('Range').lro(obx.range);
                    })
                    if (! obx.pdf_) {
                      var ipc = obx.ProcResult_ && obx.ProcResult_.Ipc;
                      self.Ipc = self.recon('Matched to',
                          IpcPicker_Lab.create(obx.obsId.text).reset(ipc),
                          obx.error_);
                    }
                    if (client == null)
                      self.Recon.hide();
                    if (obx.Comment)
                      Html.UlEntry.create(self.Box, function(ef) {
                        ef.line().lbl('Notes').lro(obx.uiComments());
                      })
                  }
                },
                applyTo:function() {
                  if (obx.ProcResult_)
                    obx.ProcResult_.ipc = self.Ipc.getValue();
                },
                isDirty:function() {
                  return self.Ipc && self.Ipc.isDirty();
                }
              }
            })
          }
        }
      }
    }
  }
}
SegTile = {
  create:function(container, noBox) {
    return Html.Tile.create(container, 'seg').extend(function(self) {
      return {
        init:function() {
          self.Head = Html.H2.create().into(self);
          self.Box = (noBox) ? Html.Tile.create(self, 'nobox') : Html.Box.create_asWideThin(self);
          self.Table = Html.Table2Col.create(self.Box);
          self.Recon = Html.Tile.create(self.Table.right);
          self.reset();
        },
        reset:function() {
          self.Head.hide();
          self.Table.left.clean();
          self.Form = Html.UlEntry.create(self.Table.left);
          self.Recon.clean();
        },
        head:function(text) {
          self.Head.setText(text).show();
        },
        form:function(builder) {
          builder(self.Form);
        },
        recon:function(label, Selector, error) {
          self._Selector = Selector;
          var tile = Html.Tile.create(self.Recon, 'recon');
          Html.UlEntry.create(tile, function(ef) {
            ef.line().l(label).append(Selector);
            if (error) {  
              ef.line().l().append(Html.Span.create('warning2', error));
              page.setErrorUi(Selector, error);
            }
          })
          return Selector;
        }
      }
    })
  },
  create_noBox:function(container) {
    return SegTile.create(container, true);
  }
}
