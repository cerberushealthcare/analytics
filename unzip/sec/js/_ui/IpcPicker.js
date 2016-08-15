/**
 * RecEntryPop IpcCustomEntry
 */
IpcCustomEntry = {
  /*
   * @arg Ipc rec
   * @arg fn(Ipc) callback on save
   */
  pop:function(rec, callback) {
    return Html.Pop.singleton_pop.apply(IpcCustomEntry, arguments);
  },
  create:function() {
    return Html.RecEntryPop.create('Custom Test/Procedure Code Entry').extend(function(self) {
      return {
        onshow:function(rec) {
          if (rec.ipc == null && rec.cat && rec.name && rec.desc)
            self.cmd.focus('save');
          else
            self.form.focus((rec && rec.cat) ? 'name' : 'cat');
        },
        buildForm:function(ef) {
          ef.line().lbl('Category').select('cat', C_Ipc.CATS);
          ef.line('mt10').lbl('Name').textbox('name', 25, 25);
          ef.line().lbl('Desc').textbox('desc', 60);
          ef.line('mt10').lbl('SNOMED').textbox('codeSnomed', 20);
          ef.line().lbl('CPT').textbox('codeCpt', 20);
          ef.line().lbl('LOINC').textbox('codeLoinc', 20);
          //ef.line('mt10').lbl('Alt Code').textbox('code', 15).lbl('System').select('codeSystem', C_Ipc.CODE_SYSTEMS, '');
        },
        isDeletable:function(rec) {
          // return rec && rec.ipc;  shouldn't be able to delete IPC
        },
        save:function(rec, callback_rec) {
          Ajax.post('Ipc', 'saveCustom', rec, callback_rec);
          Ipcs.resetCache();
        }
      }
    })
  }
}
IpcPickerPop = {
  //
  pop:function(value, text, cat) {
    return Html.Pop.singleton_pop.apply(IpcPickerPop, arguments);
  },
  create:function() {
    var My = this;
    return Html.Pop.create('Test/Procedure Selector', 780).extend(function(self) {
      return {
        onchoose:function(/*Ipc*/rec) {},
        //
        init:function() {
          self.Input = Html.SearchTextAnchor.create(null, 40, Html.InputText).into(Html.Tile.create(self.content, 'mb5'))
            .bubble('onkeyup', self.search)
            .withOnCr(self.Input_oncr);
          self.Table = My.Table.create(self.content)
            .bubble('onchoose', self.Table_onchoose)
            .bubble('onedit', self.Table_onedit);
          Html.CmdBar.create(self.content)
            .add('Add Custom...', self.add_onclick)
            .cancel(self.close);
          self.Entry = IpcCustomEntry;
        },
        //
        onshow:function(value, text, cat) {
          self.Input.setText(text).setFocus();
          self.cat = cat;
        },
        getText:function() {
          return self.Input.getText();
        },
        search:function() {
          self.Table.search(self.getText());
        },
        Input_oncr:function() {
          self.Table.chooseFirst();
        },
        Table_onchoose:function(rec) {
          self.choose(rec);
        },
        Table_onedit:function(rec) {
          self.Entry.pop(rec, function() {
            Ipcs.resetCache();
            self.Table.refetch();
          })
        },
        add_onclick:function() {
          var rec = Ipc_Custom.asNew(self.getText(), self.cat);
          self.Entry.pop(rec, function(rec) {
            Ipcs.resetCache();
            self.choose(rec);
          })
        },
        choose:function(rec) {
          self.close();
          self.onchoose(rec);
        }
      }
    })  
  },
  Table:{
    create:function(container) {
      return Html.ScrollTable.create(container, 'fsb').withMore().extend(function(self) {
        return {
          LIMIT:20,
          onchoose:function(rec) {},
          onedit:function(rec) {},
          //
          init:function() {
            self.addHeader()
              .th('Test/Procedure').w('25%')
              .th('Description').w('50%')
              .th('Category').w('20%')
              .th().w('5%');
          },
          search:function(text) {
            if (text != self.text && ! String.isBlank(text)) 
              self.fetch(text);
          },
          fetch:function(text) {
            self.reset();
            self.text = text;
            self.working(function() {
              Ipcs.ajax().fetchMatches(self.text, self.load);
            })
          },
          refetch:function() {
            self.fetch(self.text);
          },
          chooseFirst:function() {
            if (self.recs && self.recs.length)
              self.onchoose(self.recs[0]);
          },
          //
          reset:function() {
            self.text = null;
            self.recs = null;
            self.first = null;
            self.tbody().clean();
            self.setMore(false);
          },
          load:function(recs, limit) {
            self.recs = recs;
            self.working(false);
            if (recs && recs.length) {
              for (var i = 0, rec; i < recs.length && i < self.LIMIT; i++) {
                self.add(recs[i]);
              }
              self.setMore(recs.length > self.LIMIT);
              self.syncHeaderWidth();
            }
          },
          add:function(rec) {
            var edit = rec.custom ? Html.AnchorAction.asEdit('Edit', self.onedit.curry(rec)) : '';
            self.tbody().trOff()
              .td(IpcAnchor.create(rec, self.onchoose))
              .td(rec.desc)
              .td(rec._cat)
              .td(edit);
          },
          onmore:function() {
            self.More.working(function() {
              for (var i = self.LIMIT; i < self.recs.length; i++) {
                self.add(self.recs[i]);
              }
              self.syncHeaderWidth();
              self.More.working(false);
              self.setMore(false);
            })
          }
        }
      })
    }
  }
}
/**
 * PickerPop IpcPickerPop
 */
IpcPickerPop_old = {
  /*
   * @arg string value
   * @arg string text
   * @arg int cat Ipc.CAT (optional)
   */
  pop:function(value, text, cat) {
    return Html.Pop.singleton_pop.apply(IpcPickerPop, arguments);
  },
  Entry:IpcCustomEntry,
  create:function() {
    var My = this;
    return Html.PickerPop.create('Test/Procedure Selector').extend(My, function(self, parent) {
      return {
        POP_POS:Pop.POS_CENTER,
        onselect:function(rec) {},
        //
        init:function() {
          self.table.thead().tr('fixed head').th('Test/Procedure').w('35%').th('Description').w('45%').th('Category').w('20%');
        },
        cmdbar_buttons:function(cb) {
          cb.add('Add Custom...', self.add_onclick).cancel(self.close);
        },
        onbeforeshow:function(value, text, cat) {
          self.cat = cat;
        },
        table_fetch:function(callback_recs) {
          Ajax.Ipc.getAll_forCat(self.cat, callback_recs);
        },
        table_applies:function(rec, search) {
          if (search)
            return rec.name.match(search) || (rec.code && rec.code.match(search))
          else
            return true;
        },
        table_add:function(rec, tr) {
          tr.select(rec, IpcAnchor.create(rec)).td(rec.desc).td(rec._cat);
        },
        add_onclick:function() {
          var rec = {
            'cat':self.cat,
            'name':self.getSearchText(),
            'desc':self.getSearchText()};
          My.Entry.pop(rec, self.entry_onupdate);
        },
        entry_onupdate:function(rec) {
          self.close();
          self.onselect(rec);
        }
      }
    })
  }
}
IpcAnchor = {
  create:function(rec, onclick) {
    var name = rec.name.substr(0, 25);
    if (onclick)
      onclick = onclick.curry(rec);
    return (rec.custom) ? Html.AnchorAction.asSelect2(name, onclick).tooltip(rec.desc) : Html.AnchorAction.asSelect(name, onclick).tooltip(rec.desc);
  }
}
/**
 * RecPicker IpcPicker
 */
IpcPicker = {
  create:function() {
    return Html.RecPicker.create(40, IpcPickerPop).extend(function(self) {
      return {
        init:function() {
          self.input.aug({
            fetch:function(value, callback) {
              Ipcs.ajax().fetchMatches(value, callback);
            },
            oncustom:function(text) {
              self.showEntry(text);
            },
            Anchor:IpcAnchor
          })
        },
        showPop:function(value, text) {
          self.pop.pop(value, text)
            .bubble('onchoose', self.pop_onselect)
            .bubble('onclose', self.pop_onclose);
        },
        getValueFrom:function(rec) {
          return rec.ipc;
        },
        getTextFrom:function(rec) {
          return rec.name;
        },
        showFilteredPop:function(cat, value, text) {
          self.pop.pop(value, text, cat).bubble('onselect', self.pop_onselect);
        },
        showEntry:function(text) {
          var rec = {
            'name':text,
            'desc':text};
          IpcPickerPop.Entry.pop(rec, self.pop_onselect).bubble('onclose', self.setFocus);
        }
      }
    })
  }
}
IpcPicker_Lab = {
  create:function(defaultText) {
    return IpcPicker.create().extend(function(self) {
      return {
        init:function() {
          self.input.setCustomText('Add ' + defaultText + '...');
        },
        showPop:function(value, text) {
          text = text || defaultText;
          self.showFilteredPop(C_Ipc.CAT_LAB, value, text);
        },
        showEntry:function() {
          var rec = {
            'cat':C_Ipc.CAT_LAB,
            'name':defaultText,
            'desc':defaultText};
          IpcPickerPop.Entry.pop(rec, self.pop_onselect).bubble('onclose', self.setFocus);
        }
      }
    })
  }
}
IpcPicker_Admin = {
  create:function(defaultText) {
    return IpcPicker.create().extend(function(self) {
      return {
        init:function() {
          self.input.setCustomText('Add ' + defaultText + '...');
        },
        showPop:function(value, text) {
          text = text || defaultText;
          self.showFilteredPop(C_Ipc.CAT_ADMIN, value, text);
        },
        showEntry:function() {
          var rec = {
            'cat':C_Ipc.CAT_ADMIN,
            'name':defaultText,
            'desc':defaultText};
          IpcPickerPop.Entry.pop(rec, self.pop_onselect).bubble('onclose', self.setFocus);
        }
      }
    })
  }
}
