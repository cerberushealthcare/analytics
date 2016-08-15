/**
 * Practices Page
 * @author Warren Hornsby
 */
PracticesPage = page = {
  //
  start:function(query) {
    PracticesTile.create();
    Page.setEvents();
    async(function() {
      PracticesTile.load();
    })
  },
  onresize:function() {
    var vp = Html.Window.getViewportDim();
    var i = vp.height - 200;
    if (i != self.maxHeight) {
      self.maxHeight = i;
      PracticesTile.setMaxHeight(i);
    }
    PracticesTile.onresize();
  }
}
PracticesTile = {
  create:function() {
    var My = this;
    return PracticesTile = _$('tile').clean().extend(function(self) {
      return {
        init:function() {
          self.Table = My.Table.create(self);
        },
        setMaxHeight:function(i) {
          self.Table.setHeight(i);
        },
        onresize:function() {
          self.Table.onresize();
        },
        load:function() {
          self.Table.load();
        }
      }
    })
  },
  Table:{
    create:function(container) {
      return Html.ScrollTable.create(container, 'fsb').withMore().extend(function(self) {
        return {
          init:function() {
            self.addHeader()
              .th('Name').w('40%')
              .th('Location').w('30%')
              .th('Providers', 'rj').w('8%')
              .th('Patients', 'rj').w('8%')
              .th('Last Update').w('14%');
          },
          addRow:function(rec) {
            self.tbody().trOff()
              .td(Html.AnchorAction.asPractice(rec.name, PracticePop.pop.curry(rec))).w('40%')
              .td(rec.Address.csz).w('30%')
              .td(rec._providers, 'rj').w('8%')
              .td(rec._patients, 'rj').w('8%')
              .td(rec._lastUpdate).w('14%');
          },
          load:function() {
            self.tbody().clean();
            Ajax.get('Practices', 'get', null, function(recs) {
              recs.each(self.addRow);
              self.syncHeaderWidth();
            })
          }
        }
      })
    }
  }
}
//
PracticePop = {
  pop:function(/*UserGroup*/rec) {
    return Html.Pop.singleton_pop.apply(PracticePop, arguments);
  },
  create:function() {
    return Html.Pop.create('Practice', 500).extend(function(self) {
      return {
        init:function() {
          self.H2 = Html.H2.create().into(self.content);
          self.Form = Html.UlEntry.create(self.content, function(ef) {
            ef.line().lblf('addr1').lblf('addr2')
              .line().lblf('csz').l('Phone:').lblf('phone1');
          })
          self.H3 = Html.H3.create('Providers').into(Html.Tile.create(self.content, 'mt15'));
          self.Table = Html.ScrollTable.create(self.content, 'fsgr');
          Html.CmdBar.create(self.content).exit(self.close);
        },
        onshow:function(rec) {
          self.H2.setText(rec.name);
          self.Form.load(rec.Address);
          self.Table.tbody().clean();
          rec.Users.each(function(rec) {
            self.Table.tbody().tr().td(rec.name);
          })
        }
      }
    })
  }
}
