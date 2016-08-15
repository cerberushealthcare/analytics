TestGraphPage = page = {
  //
  start:function(query) {
    this.Tile = _$('tile').extend(this._Tile).load();    
  },
  _Tile:function(self) {
    return {
      init:function() {
        self.clean();
        self.Chart = Html.Canvas.create(self, 300, 600).extend(Chart);
      },
      load:function() {
        var result = {'ipc':600061, 'clientId':3742};
        ResultHists.ajax().fetch(result, function(recs) {
          var trend1 = Trend_ResultHists.from(recs);
          result = {'ipc':600060, 'clientId':3742};
          ResultHists.ajax().fetch(result, function(recs) {
            var trend2 = Trend_ResultHists.from(recs, 'red');
            self.Chart.load([trend1, trend2]);
            //self.Chart.load([trend1]);
          })
        })
      }
    }
  }
}
Chart = function(self) {
  //
}
//
ResultHist = Object.Rec.extend({
  /*
   clientId
   date
   ipc
   procId
   procResultId
   seq
   value
   valueUnit
   range
   interpretCode
   _ipc
   */
  //
  asPt:function() {
    return Trend.Pt.fromDateValue(this.date, this.value);
  }
})
ResultHists = Object.RecArray.of(ResultHist, {
  //
  getLabel:function() {
    return this[0]._ipc;
  },
  asPts:function() {
    return Trend.Pts.from_asDateValue(this);
  },
  //
  ajax:function(worker) {
    return {
      //
      fetch:function(result, callback) {
        Ajax.Procedures.getResultHistory(result, callback);
      }
    }
  }
})
Trend_ResultHists = {
  //
  from:function(recs, color) {
    return Trend.create(recs.getLabel(), color).load(recs.asPts());
  }
}