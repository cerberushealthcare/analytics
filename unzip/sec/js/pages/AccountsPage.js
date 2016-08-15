/**
 * Accounts Page
 * @author Warren Hornsby
 */
AccountsPage = page = {
  //
  load:function(query) {
   AccountsPageTile.create(_$('tile')).show();
    Page.setEvents();
  },
  onresize:function() {
    var i = Html.Window.getViewportDim().height - 200;
    if (i != self.maxHeight) {
      self.maxHeight = i;
      AccountsTile.setMaxHeight(i);
    }
  }
}
/**
 * Tile AccountsPageTile
 */
AccountsPageTile = {
  create:function(container) {
    container.clean();
    return AccountsTile = Html.Tile.create(container).extend(function(self) {
      return {
        init:function() {
          self.table = PortalUsersTable.create(self);
          self.table.load();
        },
        setMaxHeight:function(i) {
          self.table.setMaxHeight(i);
        }
      }
    })
  }
}
/**
 * Tile PortalUsersTable
 */
PortalUsersTable = {
  create:function(container) {
    var My = this;
    return Html.Tile.create(container).extend(function(self) {
      return {
        onselect:function(rec) {
          self.edit(rec.Client);
        },
        //
        init:function() {
          self.table = My.Table.create(self)
            .bubble('onselect', self);
          self.cb = Html.CmdBar.create(self)
            .add('New Account...', self.add_onclick)
            .ok(PortalMsgTypeCustomPop.pop);
          self._pad = self.cb.height();
        },
        load:function() {
          self.table.load();
        },
        setMaxHeight:function(i) {
          self.table.setMaxHeight(i - self._pad);
        },
        //
        edit:function(client) {
          self.working(true);
          Ajax.PortalAccounts.editPortalUserFor(client.clientId, null, function(puser) {
            self.working(false);
            if (puser)
              PortalUserManager.pop(puser).bubble('onsave', self.table.update);
            else
              PortalUserEntry.pop_asNew(client).bubble('onsave', self.table.update);
          })
        },
        add_onclick:function() {
          async(function() {
            PatientSelector.pop(function(client) {
              Ajax.Facesheet.Patients.get(client.clientId, self, self.edit);
            })
          })
        }
      }
    })
  },
  Table:{
    create:function(container) {
      return Html.TableLoader.create(container, 'fsy grid').extend(function(self) {
        return {
          onselect:function(report) {},
          //
          init:function() {
            self.thead().trFixed()
              .th('Login ID').w('20%')
              .th('Patient').w('20%')
              .th('Subscription').w('5%')
              .th('Status').w('30%')
              .th('Last Login').w('25%');
          },
          //
          rowKey:function(rec) {
            return rec.portalUserId; 
          },
          rowBreaks:function(rec) {
            return [rec.portalUserId];
          },
          fetch:function(callback_recs) {
            Ajax.PortalAccounts.getPortalUsers(callback_recs);
          },
          add:function(rec, tr) {
            tr.select(rec.asSelector())
              .td(AnchorClient_Facesheet.create(rec.Client))
              .td(rec.uiSubscription())
              .td(rec._status)
              .td(rec._lastLogin);
          },
          setMaxHeight:function(i) {
            self.setHeight(i);
          }
        }
      })
    }
  }
}
PortalMsgTypeCustomPop = {
  pop:function() {
    return Html.Pop.singleton_pop.apply(PortalMsgTypeCustomPop, arguments);
  },
  create:function() {
    var My = this;
    return Html.DirtyPop.create('Portal Message Types').extend(function(self) {
      return {
        init:function() {
          var filterbox = Html.Div.create();
          self.formbox = Html.Div.create('CustomForm');
          self.filter = My.Filter.create(filterbox)
            .bubble('onselect', self.filter_onselect);
          self.form = My.Form.create(self.formbox)
            .bubble('onresize', self.reposition);
          self.cb = Html.CmdBar.create(self.formbox)
            .save(self.save)
            .exit(self.close);
          Html.Table2Col.create(self.content, filterbox.setWidth(150), self.formbox.setWidth(500));
        },
        reset:function() {
          self.userId = null;
        },
        onpop:function() {
          self.form.fetch();
        },
        isDirty:function() {
          return self.form.isDirty();
        },
        save:function() {
          var recs = self.form.getDirtyRecs();
          recs.ajax(self).save(self.userId, self.form.load);
        },
        reposition:self.reposition.prepend(function() {
          if (self.formbox) {
            self.formbox.setHeight();
            self.form.visible();
            self.cb.visible();
          }
        }),
        filter_onselect:function(userId) {
          userId = String.nullify(userId);
          if (userId != self.userId) {
            self.formbox.setHeight(self.formbox.getHeight());
            self.form.invisible();
            self.cb.invisible();
            self.userId = userId;
            self.form.fetch(userId);
          }
        }
      }
    })
  },
  Filter:{
    create:function(container) {
      return Html.UlFilter.create().into(container).extend(function(self) {
        return {
          init:function() {
            var items = Object.clone(C_Docs);
            items[''] = '(Group Defaults)';
            self.load(items);
          }
        }
      })
    }
  },
  Form:{
    create:function(container) {
      var My = this;
      return Html.ScrollTable.create(container, 'fsgr single', 'fstab noscroll').extend(function(self) {
        return {
          onresize:function() {},
          MAX_ROWS:15,
          //
          init:function() {
            self.thead().trFixed()
              .th('').w(60)
              .th('Name')
              .th('Send To')
              .th('').w(40);
            self.another = Html.AnchorAction.asNew('Add Another...', self.another_onclick);
          },
          load:function(recs) {
            self.reset();
            self.recs = recs;
            self.draw();
            self.onresize();
          },
          getDirtyRecs:function() {
            var recs = [], rec;
            self.trows.each(function(trow) {
              rec = trow.applyTo(self.userId);
              if (rec.isDirty())
                recs.push(rec);
            })
            return PortalMsgTypes.revive(recs);
          },
          isDirty:function() {
            return self.getDirtyRecs().length > 0;
          },
          //
          reset:function() {
            self.recs = null;
            self.trows = null;
            self.anotherRow = null;
            self.draw();
          },
          fetch:function(userId) {
            self.reset();
            self.userId = userId;
            PortalMsgTypes.ajax(container).fetch(userId, self.load);
          },
          draw:function() {
            self.tbody().clean();
            self.anotherRow = self.addAnother().visibleIf(self.userId == null);
            self.trows = [];
            if (self.recs)  
              self.recs.each(self.addRow);
          },
          addRow:function(rec) {
            var trow = My.Row.create(self.tbody().tr(), rec).before(self.anotherRow)
            self.trows.push(trow);
            if (self.trows.length > self.MAX_ROWS)
              self.another.hide();
            return trow;
          },
          addAnother:function() {
            self.tbody().tr('another').td(self.another).colspan(4);
            return self.tbody()._tr;
          },
          another_onclick:function() {
            var rec = PortalMsgType.asNew();
            self.recs.push(rec);
            var trow = self.addRow(rec).setFocus();
            trow.bubble('ondelete', function() {
              self.recs.remove(self.recs.find(trow.rec));
              trow.remove();
              self.trows.remove(self.trows.find(trow));
              self.onresize();
            })
            self.onresize();
          }
        }
      })
    },
    Row:{
      create:function(tr, rec) {
        var self = tr._tr();
        return self.aug({
          ondelete:function() {},
          //
          init:function() {
            tr.td(self.active = Html.LabelCheck.create('Active').bubble('ondraw', self.active_ondraw))
              .td(self.name = Html.InputText.create('w100'))
              .td(self.sendTo = Html.Select.create(C_Users, '(Default)'))
              .td(self.trash = Html.Anchor.create('trash', null, self.trash_onclick.confirm('clear this line')));
            self.load(rec);
          },
          load:function(rec) {
            self.rec = rec;
            self.name.setValue(rec.name);
            self.sendTo.setValue(rec.sendTo);
            self.active.setChecked(rec.active);
            self.trash.hideIf(! rec.isNew());
            self.active.hideIf(rec.isNew());
          },
          applyTo:function(userId) {
            return self.rec.setui(
              userId,
              self.active.isChecked(), 
              self.name.getValue(),
              self.sendTo.getValue());
          },
          setFocus:function() {
            self.name.setFocus();
            return self;
          },
          //
          active_ondraw:function(value) {
            self.addClassIf('off', ! value);
            self.name.disabled = ! value;
            self.sendTo.disabled = ! value;
            if (! value && String.isBlank(self.name.getValue()))
              self.name.setValue('(None)');
          },
          trash_onclick:function() {
            self.ondelete();
          }
        })
      }
    }
  }
}
