SnomedPop = {
  pop:function(cid, onselect) {
    return Html.Pop.singleton_pop.apply(SnomedPop, arguments);
  },
  create:function() {
    var My = this;
    return Html.Pop.create('Snomed Selector', 600).extend(function(self) {
      return {
        onchoose:function(rec) {},
        //
        init:function() {
          self.Input = Html.SearchTextAnchor.create(null, 40, Html.InputText).into(Html.Tile.create(self.content, 'mb5'))
            .withOnCr()
            .bubble('onclick_anchor', self.search)
          self.Table = My.Table.create(self.content)
            .bubble('onchoose', self.Table_onchoose);
          Html.CmdBar.create(self.content)
            .cancel(self.close);
        },
        onshow:function(cid, callback) {
          self.Input.setText(cid);
          self.Input.setFocus();
          if (cid)
            self.search();
          self.callback = callback;
        },
        search:function() {
          self.Table.search(self.Input.getText());
        },
        Table_onchoose:function(rec) {
          self.close();
          if (self.callback)
            self.callback(rec);
        }
      }
    })
  },
  Table:{
    create:function(container) {
      return Html.ScrollTable.create(container, 'fsgr').withMore().extend(function(self) {
        return {
          LIMIT:20,
          onchoose:function(rec) {},
          //
          init:function() {
            self.addHeader()
              .th('Description').w('50%')
              .th('SNOMED').w('50%');
          },
          search:function(text) {
            if (text != self.text && ! String.isBlank(text)) 
              self.fetch(text);
          },
          load:function(recs) {
            self.recs = recs;
            self.working(false);
            recs.each(self.add);
            self.syncHeaderWidth();
          },
          add:function(rec) {
            var selector = Html.AnchorRec.asSelect(rec.snomedFsn, rec, self.onchoose);
            self.tbody().trOff()
              .td(selector)
              .td(rec.snomedCid);
          },
          fetch:function(text) {
            self.reset();
            self.text = text;
            self.working(function() {
              Ajax.Snomed.search(self.text, self.load);
            })
          },
          reset:function() {
            self.text = null;
            self.recs = null;
            self.first = null;
            if (self.tbody)
              self.tbody().clean();
          }          
        }
      })
    }
  }
}