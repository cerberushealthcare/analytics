HmDueNowPop = Html.SingletonPop.aug({
  create:function() {
    return Html.Pop.create('Reportable Procedures', 400).extend(function(self) {
      return {
        //
        init:function() {
          self.Table = Html.TableLoader.create(self.content).extend(HmDueNowPop._Table)
            .bubble('onselect', self.Table_onselect);
          Html.CmdBar.create(self.content).exit(self.close);
        },
        onshow:function() {
          self.Table.load();
        },
        Table_onselect:function(rec) {
          HmDueNowReportPop.pop(rec);
        }
      }
    })
  },
  _Table:function(self) {
    return {
      add:function(rec, tr) {
        tr.select(rec.name);
      },
      fetch:function(callback_recs) {
        Ajax.Ipc.allHmIpc(self, callback_recs);
      }
    }
  }
})  
HmDueNowReportPop = Html.SingletonPop.aug({
  create:function() {
    return Html.Pop.create('Due Now Report').extend(function(self) {
      return {
        //
        init:function() {
          var height = self.fullscreen(800, 600);
          self.Tile = DueNowTile.create(self.content, height)
            .bubble('onclose', self.close);
        },
        onshow:function(ipc) {
          self.Tile.load(ipc);
        }
      }
    })
  }
})
DueNowTile = {
  create:function(container, height) {
    return Html.Tile.create(container).extend(function(self) {
      return {
        onclose:function() {},
        //
        init:function() {
          self.Head = Html.H2.create().into(self);
          self.Table = Html.TableLoader.create(self).setHeight(height).extend(DueNowTile._Table);
          Html.SplitCmdBar.create(self)
            .append(Html.Anchor.create('download').setText('Download').bubble('onclick', self.download_onclick))
            .spacer()
            .append(Html.Anchor.create('reminders').setText('MU Preventive Care Reminder Sent').bubble('onclick', self.save_onclick))
            .split()
            .exit(Function.defer(self, 'onclose'))
            .end();
        },
        load:function(ipc) {
          self.reset();
          self.ipc = ipc;
          self.Head.setText(ipc.name);
          Ajax.Ipc.allDueNow(self, ipc.ipc, function(recs) {
            self.recs = recs;
            self.Table.load(self.recs);
          })
        },
        //
        reset:function() {
          self.recs = null;
          self.Table.reset();
        },
        download_onclick:function() {
          Ajax.Ipc.downloadDueNow(self, self.ipc.ipc);
        },
        save_onclick:function() {
          var cids = self.Table.getCheckedIds();
          if (Array.isEmpty(cids)) {
            Pop.Msg.showCritical('No patients are checked.');
          } else {
            Pop.Confirm.showYesNo('This will record that a reminder was sent for each of the patient(s) checked above. Proceed?', function() {
              Pop.Working.show('Recording for selected patient(s)...');
              Ajax.Ipc.recordReminders(cids, self.ipc.name, function() {
                Pop.close();
              })
            })
          }
        }
      }
    })
  },
  _Table:function(self) {
    return {
      init:function() {
        self.All = Html.InputCheck.create().bubble('onclick', self.All_onclick);
        self.thead().trFixed().th(self.All, 'check').th('Patient Due Now');
      },
      add:function(rec, tr) {
        var c = Html.InputCheck.create().set('cid', rec.clientId);
        self.Checks.push(c);
        tr.td(c).select(AnchorClient_FacesheetPop);
      },
      getCheckedIds:/*int[]*/function() {
        return Array.from(self.Checks.filterOn('checked'), 'cid');
      },
      reset:self.reset.extend(function(_reset) {
        self.Checks = [];
        _reset();
      }),
      All_onclick:function() {
        self.Checks.each(function(c) {
          c.checked = self.All.checked;
        })
      }
    }
  }
}
