/**
 * TableLoader TrackingTable 
 */
TrackingTable = {
  /*
   * @arg int cid (optional, to restrict table to one client)
   * @arg int panelIndex (optional)
   */
  create:function(parent, cid, panelIndex) {
    var titles = ['Open Items By Category', 'Unscheduled/Uncollected Items By Date', 'Unreconciled Items By Date', 'Closed Items'];
    var tabs = ['Open', 'Unscheduled', 'Unreconciled', 'Closed'];
    var self = Html.TabPanels.create(parent, titles, tabs);
    return self.aug({
      onchange:function() {},
      //
      TAB_OPEN:0,
      TAB_UNSCHED:1,
      TAB_SCHED:2,
      TAB_CLOSED:3,
      init:function() {
        TrackingTable.OpenTable.create(self.panels.get(self.TAB_OPEN), cid).bubble('onselect', self.table_onselect);
        TrackingTable.UnschedTable.create(self.panels.get(self.TAB_UNSCHED), cid).bubble('onselect', self.table_onselect);
        TrackingTable.SchedTable.create(self.panels.get(self.TAB_SCHED), cid).bubble('onselect', self.table_onselect);
        TrackingTable.ClosedTable.create(self.panels.get(self.TAB_CLOSED), cid).bubble('onselect', self.table_onselect);
        self.select(panelIndex || 0);
      },
      setHeight:function(i) {
        self.panels.forEach(function(panel) {
          panel.table.setHeight(i);
        });
      },
      reset:function() {
        self.panels.forEach(function(panel) {
          panel.table.reset();
        });
        self.select(0);
      },
      //
      panel_onselect:function(panel) {
        if (! panel.table.isLoaded())  
          panel.table.load();
        else
          panel.table.syncHeaderWidth();
      },
      table_onselect:function(rec) {
        Html.Window.working(true);
        rec.ajax().fetch(function(rec) {
          Html.Window.working(false);
          TrackItemEntry.pop(rec).aug({
            onsave:function(rec) {
              self.onchange();
              self.panels.forEach(function(panel) {
                panel.table.load();
              })
            }
          })
        })
      }
    });
  },
  TableLoader:{
    create:function(panel, cid) {
      var self = Html.TableLoader.create(panel, 'fsb');
      panel.table = self;
      return self.aug({
        init:function() {
          self.setTopFilter();
        },
        rowKey:function(rec) {
          return rec.trackItemId;
        },
        formatStatus:function(status) {
          switch (status) {
            case C_TrackItem.STATUS_ORDERED:
              return 'Ordered Only'
            case C_TrackItem.STATUS_SCHED:
              return 'Scheduled/Obtained';
            case C_TrackItem.STATUS_CLOSED:
              return 'Closed';
            default:
              return null;
          }
        },
        stub_onclick:function(rec) {
          DocStubPreviewPop.pop(rec);
        },
        fetch:function(callback_recs) {
          var requestType = panel.index;
          var criteria = (cid) ? {'cid':cid} : null;
          Ajax.Tracking.getTrackItems(requestType, criteria, null, callback_recs);
        }
      });
    }
  },
  OpenTable:{
    create:function(panel, cid) {
      var self = TrackingTable.TableLoader.create(panel, cid);
      return self.aug({
        init:function() {
          if (! cid)
            self.addHeader().th('Category').w('15%').th('Item').w('30%').th('Patient').w('15%').th('Source').w('15%').th('Due').w('10%').th('Sched').w('15%');
          else
            self.addHeader().th('Category').w('15%').th('Item').w('30%').th('Source').w('15%').th('Due').w('10%').th('Sched').w('30%');
        },
        filter:function(rec) {
          if (! cid)
            return {'Category':rec._cat,'Ordered By':rec._orderedBy,'Status':self.formatStatus(rec.status),'Patient':rec._client};  
          else
            return {'Category':rec._cat,'Ordered By':rec._orderedBy,'Status':self.formatStatus(rec.status)};  
        },
        rowBreaks:function(rec) {
          return [rec._cat];
        },
        add:function(rec, tr) {
          tr.td(rec._cat, 'histbreak').select(AnchorTrackItem, null, null, 'wrap');
          if (rec.orderNotes) 
            Html.Div.create('notes').setText(rec.orderNotes).into(tr._cell);
          if (! cid)
            tr.td(AnchorClient_Facesheet.create(rec.ClientStub), 'nw');
          tr.td(AnchorDocStub_Preview.create_orFacesheet(rec.DocSession)).td(rec._dueDate, 'nw').td(rec._schedDate, 'nw');
        }
      });
    }
  },
  UnschedTable:{
    create:function(panel, cid) {
      var self = TrackingTable.TableLoader.create(panel, cid);
      return self.aug({
        init:function() {
          if (! cid)
            self.addHeader().th('Due').w('10%').th('Item').w('30%').th('Category').w('15%').th('Patient').w('15%').th('Source').w('30%');
          else
            self.addHeader().th('Due').w('10%').th('Item').w('30%').th('Category').w('15%').th('Source').w('45%');
        },
        filter:function(rec) {
          if (! cid) 
            return {'Category':rec._cat,'Ordered By':rec._orderedBy,'Patient':rec._client};
          else
            return {'Category':rec._cat,'Ordered By':rec._orderedBy};
        },
        rowBreaks:function(rec) {
          return [rec.dueDate];
        },
        add:function(rec, tr) {
          tr.td(rec._dueDate, 'histbreak').select(AnchorTrackItem, null, null, 'wrap').td(rec._cat);
          if (! cid)
            tr.td(AnchorClient_Facesheet.create(rec.ClientStub), 'nw');
          tr.td(AnchorDocStub_Preview.create_orFacesheet(rec.DocSession), 'nw');
        }
      });
    }
  },
  SchedTable:{
    create:function(panel, cid) {
      var self = TrackingTable.TableLoader.create(panel, cid);
      return self.aug({
        init:function() {
          if (! cid)
            self.addHeader().th('Scheduled').w('10%').th('Days Ago').w('5%').th('Item').w('30%').th('Category').w('15%').th('Patient').w('15%').th('Source').w('25%');
          else
            self.addHeader().th('Scheduled').w('10%').th('Days Ago').w('5%').th('Item').w('30%').th('Category').w('15%').th('Source').w('40');
        },
        filter:function(rec) {
          if (! cid) 
            return {'Category':rec._cat,'Ordered By':rec._orderedBy,'Patient':rec._client};
          else
            return {'Category':rec._cat,'Ordered By':rec._orderedBy};
        },
        rowBreaks:function(rec) {
        },
        add:function(rec, tr) {
          tr.td(rec.schedDate, 'histbreak').td(String.denull(rec._overdue) + '', rec._overdue > 14 ? 'red' : '').select(AnchorTrackItem, null, null, 'wrap').td(rec._cat);
          if (! cid)
            tr.td(AnchorClient_Facesheet.create(rec.ClientStub), 'nw');
          tr.td(AnchorDocStub_Preview.create_orFacesheet(rec.DocSession), 'nw');
        }
      });
    }
  },
  ClosedTable:{
    create:function(panel, cid) {
      var self = TrackingTable.TableLoader.create(panel, cid);
      return self.aug({
        init:function() {
          if (! cid)
            self.addHeader().th('Closed').w('10%').th().w('10%').th('Item').w('15%').th('Category').w('10%').th('Patient').w('15%').th('Source').w('15%').th('Due').w('10%').th('Sched').w('15%');
          else
            self.addHeader().th('Closed').w('10%').th().w('10%').th('Item').w('15%').th('Category').w('10%').w('15%').th('Source').w('15%').th('Due').w('10%').th('Sched').w('30%');
        },
        filter:function(rec) {
          if (! cid) 
            return {'Category':rec._cat,'Ordered By':rec._orderedBy,'Patient':rec._client};
          else
            return {'Category':rec._cat,'Ordered By':rec._orderedBy}; 
        },
        rowBreaks:function(rec) {
          return [rec.closedDate];
        },
        add:function(rec, tr) {
          var cls = (rec.closedFor > C_TrackItem.CLOSED_FOR_RECEIVED) ? 'red' : ''; 
          tr.td(rec.closedDate, 'histbreak').td(C_TrackItem.CLOSED_FORS[rec.closedFor], cls).select(AnchorTrackItem, null, null, 'wrap').td(rec._cat);
          if (! cid)
            tr.td(AnchorClient_Facesheet.create(rec.ClientStub), 'nw');
          tr.td(AnchorDocStub_Preview.create_orFacesheet(rec.DocSession), 'nw').td(rec._dueDate, 'nw').td(rec._schedDate, 'nw');
        }
      });
    }
  }
}
