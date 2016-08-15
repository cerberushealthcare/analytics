/**
 * Face Tracking 
 * Static pop controller
 */
var FaceTrack = {
  fs:null,
  changed:null,
  recs:null,
  table:null,
  _scb:null,
  _POP:'fsp-trk',
  //
  pop:function(fs, zoom, callback) {
    var self = this;
    Page.work(function() {
      self.fs = fs;
      self.changed = false;
      self._scb = Ajax.buildScopedCallback(callback || 'trkChangedCallback');
      Pop.setCaption('fsp-trk-cap-text', fs.client.name + ' - Tracking Sheet');
      self.table = TrackingTable.create(_$('tracking-table-tile'), fs.client.clientId);
      self.table.aug({
        init:function() {
          self.table.setHeight(350);
          Page.work();
          if (zoom) 
            Pop.zoom(FaceTrack._POP);  
          else 
            Pop.show(FaceTrack._POP);
        },
        onchange:function() {
          self.changed = true;
        }
      });
    });
  },
  fpAdd:function() {
    var self = this;
    AddOrdersPop.pop(self.fs).aug({
      onsave:function() {
        self.table.reset();
        self.changed = true;
      }
    });
  },
  fpAddByLookup:function() {
    var self = this;
    AddByIpPop.pop(self.fs, function() {
      self.table.reset();
      self.changed = true;
    })
  },
  fpClose:function() {
    if (this.changed) {
      this.changed = null;
      var self = this;
      Html.Window.working(true);
      Ajax.get(Ajax.SVR_POP, 'getTracking', self.fs.client.clientId, 
        function(fs) {
          self.fs.cuTimestamp = fs.cuTimestamp;
          self.fs.tracking = fs.tracking;
          Html.Window.working(false);
          Pop.close();
          Ajax.callScopedCallback(self._scb, self.fs);
        });
    } else {
      Pop.close();
    }
  }
};
