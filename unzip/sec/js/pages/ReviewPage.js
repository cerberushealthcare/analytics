/**
 * Review Page
 * @author Warren Hornsby
 */
ReviewPage = page = {
  //
  start:function(query) {
    ReviewTile.create(_$('tile')).load(DocStubKey.from(query));
    Page.setEvents();
  },
  onresize:function() {
    var i = Html.Window.getViewportDim().height - 200;
    if (i != self.maxHeight) {
      self.maxHeight = i;
      ReviewTile.setMaxHeight(i);
    }
  }
}
/**
 * Tile ReviewTile
 */
ReviewTile = {
  create:function(container) {
    container.clean();
    var My = this;
    return ReviewTile = Html.Tile.create(container).extend(function(self) {
      return {
        init:function() {
          self.table = My.Table.create(self)
            .bubble('onload', self.table_onload);
        },
        /*
         * @arg DocStubKey key to preview first (optional)
         */
        load:function(key) {
          async(function() {
            self.key = key;
            self.table.load();
          })
        },
        setMaxHeight:function(i) {
          self.table.setHeight(i);
        },
        table_onload:function() {
          if (self.key) 
            self.table.pop(self.key);
        }
      }
    })
  },
  Table:{
    create:function(container) {
      return Html.TableLoader.create(container).extend(function(self) {
        return {
          onupdate:function() {},
          onload:function() {},
          //
          init:function() {
            self.thead().trFixed()
              .th('Type').w('10%')
              .th('Item').w('30%')
              .th('Patient').w('20%')
              .th('Date').w('10%')
              .th('Details').w('30%');
          },
          fetch:function(callback_recs) {
            DocStubs.ajax().fetchUnreviewed(callback_recs);
          },
          add:function(rec, tr) {
            tr.td(rec._type)
              .select(AnchorDocStub)
              .td(AnchorClient_Facesheet.create(rec.Unreviewed.Client))
              .td(rec.date)
              .td(rec.desc);
          },
          rowOffset:function(rec) {
            return rec.cid;
          },
          pop:function(key) {
            var rec = self.recs.get(key);
            if (rec)
              self.onselect(rec);
          },
          onselect:function(rec) {
            DocStubPreviewPop.pop(rec, self.recs)
              .bubble('onupdate', function() {
                self.load();
                self.onupdate();
              }).bubble_keep('onreview', function() {
                var next = DocStubPreviewPop._singleton.getNext();
                if (next) {
                  DocStubPreviewPop._singleton.nav(next);
                } else {
                  DocStubPreviewPop._singleton.close();
                  self.onupdate();
                }
              })
          }
        }
      })
    }
  }
}
