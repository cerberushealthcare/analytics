/**
 * Pop ResultHistoryPop
 */
ResultHistoryPop = {
  /*
   * @arg ProcResult result
   */
  pop:function(result, proc) {
    return this.create().pop(result, proc);
  },
  create:function() {
    var My = this;
    return ResultHistoryPop = Html.Pop.create('Result History', 700).extend(function(self) {
      return {
        //
        init:function() {
          self.head = Html.H3.create().nbsp().into(self.content);
          self.table = My.Table.create(self.content);
          self.chart = My.Chart.create(self.content);
          self.cb = Html.CmdBar.create(self.content).exit(self.close);
        },
        onpop:function() {
          self.table.clean();
        },
        onshow:function(result, proc) {
          self.working(true);
          ResultHists.fetch(result, function(recs) {
            self.working(false);
            self.load(result, recs, proc);
          })
        },
        load:function(result, recs, proc) {
          self.proc = proc;
          self.result = result;
          self.recs = recs;
          self.head.setText(result.Ipc.name);
          self.table.load(result, recs, proc);
        }
      }
    })
  },
  Table:{
    create:function(container) {
      return Html.Tile.create(container, 'ResultHistory ViewResult').extend(function(self) {
        return {
          //
          load:function(result, recs, proc) {
            var t = Html.Table.create(self);
            t.thead().tr().th().w('25%').th('Value').w('10%').th('Range').w('10%').th('Interpret').w('5%').th('Comments').w('50%');
            recs.forEach(function(rec) {
              t.tbody().trToggle().th(proc.date).td(rec._value, rec.interpretCode).td(rec.range).td(rec._interpretCode).td(rec.comments);
            })
          }
        }
      })
    }
  },
  Chart:{
    create:function(container) {
      return Html.Tile.create(container, 'HistoryChart').extend(function(self) {
        return {
        }
      })
    }
  }
}
/**
 * Recs
 */
ResultHists = Object.RecArray.extend({
  //
  getItemProto:function() {
    return ResultHist;
  },
  /*
   * @arg ProcResult result
   * @arg fn(ResultHists) callback
   */
  fetch:function(result, callback) {
    Ajax.Procedures.getResultHistory(result, callback);
  }
})
//
ResultHist = Object.Rec.extend({
  //
})