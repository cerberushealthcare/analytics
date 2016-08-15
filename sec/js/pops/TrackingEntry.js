/**
 * Tracking Item Entry
 * Static pop controller
 * Requires: C_TrackItem
 */
var TrackingEntry = {
  callback:null,
  form:null,
  //
  isDirty:function() {
    return (this.form && this.form.isRecordChanged());
  },
  /*
   * Edit tracking item
   * @arg TrackItem item  
   * @callback(trackItemId) on record save 
   */
  pop:function(item, callback) {
    this.callback = callback;
    this.load(item);
    Pop.show('pop-tie');
  },
  load:function(item) {
    var orderBy = item.orderDate + ' by ' + item.UserStub_orderBy.name;
    var schedBy = (item.UserStub_schedBy) ? 'by ' + item.UserStub_schedBy.name : '';
    var closedBy = (item.UserStub_closedBy) ? 'by ' + item.UserStub_closedBy.name : '';
    var ef = new EntryForm($('tie-form'), 'first2');
    ef.li('Item').textbox('trackDesc', 40).lbl('Priority').select('priority', C_TrackItem.PRIORITIES);
    ef.li('Ordered').readonly(orderBy);
    ef.li('Notes').span(item.orderNotes, 'rof rota');
    ef.li('Scheduled', 'mt15').datetime('schedDate').lbl(schedBy, 'nopad');
    ef.li('With', 'mt5').textbox('schedWith', 18).lbl('Location').textbox('schedLoc', 35);
    ef.li('Notes').textarea('schedNotes');
    ef.li('Closed', 'mt15').date('closedDate').lbl(closedBy, 'nopad');
    ef.li('Reason', 'mt5').select('closedFor', C_TrackItem.CLOSED_FORS);
    ef.li('Notes').textarea('closedNotes');
    ef.setRecord(item);
    this.form = ef;
  },
  pClose:function() {
    Pop.Confirm.closeCheckDirty(this, this.pSave);
  },
  pSave:function() {
    overlayWorking(true);
    var rec = this.form.getRecord();
    var self = this;
    Ajax.Tracking.update(rec,
      function() {
        overlayWorking(false);
        Pop.close();
        if (self.callback)
          self.callback(rec.trackItemId);
      });
  }
};
/**
 * Tracking Add Orders
 * Static pop controller
 */
var TrackingAdd = {
  callback:null,
  fs:null,
  tui:null,
  pid:null,
  /*
   * Add tracking item(s) from ordering template
   * @arg Facesheet fs
   * @callback() on record save
   */
  pop:function(fs, callback) {
    this.callback = callback;
    this.fs = fs;
    var self = this;
    Page.work(function() {
      if (self.tui == null) {
        Ajax.Tracking.getPid(
          function(pid) {
            self.pid = pid;
            self.tui = new TemplateUi($('ta-tui'), fs, null, pid, TemplateUi.FORMAT_ENTRY_FORM_WIDE, function() {
              self._pop();
            });
          });
      } else {
        self.tui.reset();
        self.tui.getParInfo(self.pid, function() {
          self._pop();
        });  
      }
    });
  },
  _pop:function() {
    Page.work();
    Pop.show('pop-ta');
  },
  pClose:function() {
    Pop.close();
  },
  pOrder:function() {
    var items = [];
    var rec = this.tui.getSelected();
    for (var qref in rec) {
      var q = this.tui.qs[qref];
      if (q.track) {
        var opts = rec[qref];
        for (var i = 0; i < opts.length; i++) {
          var opt = opts[i];
          items.push(OrderItem.fromFs(this.fs, q, opt));
        }
      }
    }
    var self = this;
    if (items) 
      OrderSheet.pop(items, function() {
        Pop.close();
        if (self.callback) 
          self.callback();
      });
  }
}