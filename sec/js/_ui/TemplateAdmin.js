/**
 * Tile CinfoPop
 */
CinfoTile = {
  create:function(container) {
    var My = this;
    return CinfoTile = Html.Tile.create(container, 'CinfoTile').extend(function(self) {
      return {
        init:function() {
          self.table = My.Table.create(self);
          Html.CmdBar.create(self).add('New Record', self.add_onclick).exit(self.exit_onclick);
        },
        load:function(pid) {
          self.pid = pid;
          self.table.load(pid);
        },
        add_onclick:function() {
          CinfoEditor.pop({'parId':self.pid}).bubble('onupdate', function() {
            self.table.load()
          })
        },
        exit_onclick:function() {
          window.close();
        }
      }
    })
  },
  Table:{
    create:function(container) {
      return Html.TableLoader.create(container).extend(function(self) {
        return {
          onupdate:function() {},
          //
          init:function() {
            self.setHeight(400);
            self.thead().trFixed().th('Name').w('20%').th('Text').w('80%');
          },
          rowOffset:function(rec) {
            return rec.cinfoId;
          },
          rowKey:function(rec) {
            return rec.cinfoId;
          },
          add:function(rec, tr) {
            tr.edit(rec.name).td(rec.text && rec.text.ellips(100));
          },
          load:function(pid) {
            if (pid)
              self.pid = pid;
            self.fetch(self._load);
          },
          fetch:function(callback_recs) {
            Ajax.get('AdminTemplates', 'getCinfos', self.pid, callback_recs);
          },
          onselect:function(rec) {
            self.edit(rec);
          },
          edit:function(rec) {
            CinfoEditor.pop(rec).bubble('onupdate', self.editor_onupdate);
          },
          //
          editor_onupdate:function(rec) {
            self.update(rec);
            self.onupdate();
          }
        }
      })
    }
  }
}
/**
 * RecordEntryDeletePop CinfoEditor
 */
CinfoEditor = {
  pop:function(rec) {
    return CinfoEditor = this.create().pop(rec);
  },
  create:function(container) {
    var My = this;
    return Html.RecordEntryDeletePop.create('Clinical Info Editor', 730).extend(function(self) {
      return {
        onupdate:function(rec) {},
        //
        buildForm:function(ef) {
          ef.li('Name').textbox('name', 20);
          ef.li().rtb('text', 15);
        },
        onsave:function(rec) {
          self.onupdate(rec);
        },
        ondelete:function(id) {
          self.onupdate(id);
        },
        save:function(rec, callback_rec) {
          Ajax.post('AdminTemplates', 'saveCinfo', rec, callback_rec);
        },
        remove:function(rec, callback_id) {
          Ajax.get('AdminTemplates', 'deleteCinfo', rec.cinfoId, callback_id);
        }
      }
    })
  }

}