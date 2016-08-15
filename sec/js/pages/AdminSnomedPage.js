/**
 * Admin Snomed Page
 * @author Warren Hornsby
 */
AdminSnomedPage = {
  //
  load:function(query) {
    Page.setEvents();
    SnomedAdmin.create(_$('snomed-list'));
  }
};
/** 
 * Tile SnomedAdmin
 *   TableLoader table
 *   CmdBar cmd
 */
SnomedAdmin = {
  create:function(parent, callback) {
    parent.clean();
    var self = Html.Tile.create(parent);
    return self.aug({
      init:function() {
        self.table = SnomedAdmin.Table.create(self).bubble('onselect', self.table_onselect);
        self.cmd = Html.CmdBar.create(self)
          .del(self.del_onclick, 'Delete Checked...')
          .add('New Snomed Code...', self.add_onclick);
      },
      add_onclick:function() {
        SnomedEntryPop.pop(null, self.refresh);
      },
      del_onclick:function() {
        var checks = self.table.getCheckValues();
        if (checks.length) {
          Pop.Confirm.showDeleteChecked("delete", function(confirm) {
            if (confirm) {
              Ajax.post('AdminSnomed', 'deleteMany', checks, function() {
                self.refresh(checks);
              });
            }
          });  
        } else {
          Pop.Msg.showCritical('Nothing was selected.');
        }
      },
      table_onselect:function(rec) {
        SnomedEntryPop.pop(rec, self.refresh);
      },
      refresh:function(update) {
        var next = self.table.load(update);
        if (next && next.selector) 
          next.selector.click();
      }
    });
  },
  Table:{
    create:function(parent, augs) {
      var self = Html.TableLoader.create(parent);
      return self.aug({
        init:function() {
          self.setHeight(540);
          self.thead().tr('fixed head').th(null, 'check').th('Desc').w('50%').th('Snomed').w('50%');
          self.load();
        },
        rowKey:function(rec) {
          return rec.snomedCid; 
        },
        load:function(update) {
          if (Array.is(update)) {
            self.loader().removeTrs(update);
          } else if (update && self.loader().getRowByKey(update.snomedCId)) {
            self.add(update);
            var tr = self.loader().getRowByKey(update.snomedCid)
            Html.Animator.fade(tr);
            return tr.nextSibling; 
          } else {
            self.reset();
            var scrollTo = null;
            self.working(true);
            Ajax.get('AdminSnomed', 'getAll', null, function(recs) {
              Array.forEach(recs, function(rec) {
                self.add(rec);
                if (update && update.snomedCid == rec.snomedCid) 
                  scrollTo = self.loader().tr;
              });
              self.setTopFilter();
              if (scrollTo) 
                Html.Animator.fade(self.loader().scrollToTr(scrollTo));
              self.working(false);
            }); 
          }
        },
        add:function(rec) {
          self.tbody().tr(rec).check().edit(rec.snomedFsn).td(rec.snomedCid);
        }
      }).aug(augs);
    }
  }
}
/**
 * RecordEntryDeletePop SnomedEntry
 */
SnomedEntryPop = {
  pop:function(rec, callback) {
    SnomedEntryPop = this.create(callback).pop(rec);
  },
  create:function(callback) {
    var self = Html.RecordEntryDeletePop.create('Snomed Entry');
    return self.aug({
      onshow:function(rec) {
        self.form.focus('snomedCid');
      },
      onsave:function(rec) {
        callback(rec);
      },
      ondelete:function(id) {
        callback([id]);
      },
      buildForm:function(ef) {
        ef.line().lbl('Snomed').textbox('snomedCid', 5);
        ef.line().lbl('Desc').textbox('snomedFsn', 60);
      },
      save:function(rec, callback_rec) {
        Ajax.post('AdminSnomed', 'save', rec, callback_rec);
      },
      remove:function(rec, callback_id) {
        Ajax.get('AdminSnomed', 'delete', rec.snomedCid, callback_id);
      }
    })
  }
}  
/**
 * Assign global instance
 */
var page = AdminSnomedPage;  
