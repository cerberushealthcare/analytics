OrderPicker = {
  create:function(defaultText) {
    var My = this;
    return Html.Span.create().extend(function(self) {
      return {
        onset:function(rec) {},
        //
        init:function() {
          self.anchor = Html.AnchorAction.asOrder().into(self).bubble('onclick', self.anchor_onclick);
          self.reset();
        },
        /*
         * @arg int cid
         * @arg fn onload(TrackItem[]) if client has open items 
         */
        setClientId:function(cid, onload) {
          self.cid = cid;
          if (cid) {
            TrackItems.ajax(null).fetch_open(cid, function(recs) {
              self.loadOpenOrders(cid, recs);
              if (! Array.isEmpty(recs))
                onload(recs);
            })
          }
        },
        hasClientId:function() {
          return self.cid != null;
        },
        setValue:function(rec) {
          self.load(rec);
          return self;
        },
        resetValue:function(rec) {
          self.setValue(rec);
          self._dirty = false;
          return self;
        },
        isDirty:function() {
          return self._dirty;
        },
        getValue:function() {
          return self.rec;
        },
        loadOpenOrders:function(cid, recs) {
          self.cid = cid;
          self.recs = recs;
          return self;
        },
        //
        load:function(rec) {
          self.reset();
          self._dirty = self._dirty || self.rec != rec;
          self.rec = rec;
          if (rec) 
            self.anchor.setText(rec.trackDesc);
        },
        reset:function() {
          self.rec = null;
          self.anchor.setText(defaultText || '(Select Order)');
        },
        anchor_onclick:function() {
          if (self.hasClientId())
            My.Pop.pop(self.cid, self.rec, self.recs, function(rec) {
              self.setValue(rec);
              self.onset(rec);
            })
        }
      }
    })
  },
  Pop:{
    pop:function(cid, rec, recs, onselect) {
      return Html.Pop.singleton_pop.apply(this, arguments);
    },
    create:function() {
      var My = this;
      return Html.Pop.create('Open Orders', 700).extend(function(self) {
        return {
          init:function() {
            self.table = Html.TableLoader.create_asBlue(self.content).extend(My.Table).bubble('onselect', self.table_onselect);
            self.cb = Html.CmdBar.create(self.content).del(self.del_onclick, 'Select None').exit(self.close);
          },
          onshow:function(cid, rec, recs, onselect) {
            self.recs = recs;
            self.onselect = onselect;
            self.cb.showDelIf(rec);
            self.table.load(recs);
          },
          reset:function() {
            self.table.reset();
          },
          table_onselect:function(rec) {
            self.close();
            self.onselect(rec);
          },
          del_onclick:function() {
            self.table_onselect(null);
          }
        }
      })
    },
    Table:function(self) {
      return {
        onselect:function(rec) {},
        //
        init:function() {
          self.thead().trFixed().th('Category').w('15%').th('Item').w('40%').th('Ordered').w('15%').th('Sched/Obtained').w('30%');
          self.setTopFilter();
          self.setHeight(300);
        },
        rowBreaks:function(rec) {
          return [rec._cat];
        },
        rowKey:function(rec) {
          return rec.trackItemId;
        },
        filter:function(rec) {
          return {'Category':rec._cat,'Ordered By':rec._orderedBy};
        },
        add:function(rec, tr) {
          tr.td(rec._cat, 'histbreak').select(AnchorTrackItem, null, null, 'wrap');
          if (rec.orderNotes) 
            Html.Div.create('notes').setText(rec.orderNotes).into(tr._cell);
          tr.td(rec._orderDate, 'nw').td(rec._schedDate, 'nw');
        }
      }
    }
  }
}
