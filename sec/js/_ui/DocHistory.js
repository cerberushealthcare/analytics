/**
 * Pop DocHistoryPop
 */
DocHistoryPop = {
  /*
   * @arg Facesheet fs
   * @arg DocStub rec (optional, for instant preview)
   * @arg fn(DocStub) onselect (optional, to override preview behavior)
   * @arg bool addproc to pop add test/proc on show 
   */
  pop:function(fs, rec, onselect, addproc) {
    return Html.Pop.singleton_pop.apply(DocHistoryPop, arguments);
  },
  pop_asNewTestProc:function(fs) {
    DocHistoryPop.pop(fs, null, null, true);
  },
  close:function() {
    if (DocHistoryPop._singleton)
      DocHistoryPop._singleton.close();
  },
  create:function() {
    return FacePop.create('Clinical Data and Documentation').extend(function(self) {
      return {
        onupdate:function() {},
        //
        init:function() {
          var height = self.fullscreen(1000, 600);
          self.table = DocHistoryTable.create(self.content, height)
            .bubble('ondraw', self.table_ondraw)
            .bubble('onfilterset', self.table_onfilterset)
            .bubble('onupdate', self.setUpdated)
            .bubble('onexit', self.close);
          self.cb = Html.SplitCmdBar.create(self.content)
            .split().exit(self.close);
          self.cbProc = Html.CmdBar.create(self.cb.table.left)
            .add('Add Test/Proc...', self.addproc_onclick)
            .add('Add Lab Panel...', self.addpanel_onclick)
            .hide();
          self.TYPES = Map.invert(C_DocStub.TYPES);
        },
        onshow:function(fs, rec, onselect, addproc) {
          self.fs = fs;
          self.cid = fs.client.clientId;
          self.recs = fs.docstubs;
          self.rec = rec;
          self.table.load(self.recs, self.cid, onselect);
          if (addproc)
            self.addproc_onclick();
        },
        //
        table_ondraw:function() {
          if (self.rec) { 
            self.table.preview(self.rec);
            self.rec = null;
          }
        },
        table_onfilterset:function(value) {
          var type = self.TYPES[value];
          self.cbProc.showIf(type == C_DocStub.TYPE_RESULT);
        },
        addproc_onclick:function() {
          proc = Proc.asNew(self.cid);
          ProcEntry.pop(proc).bubble('onsave', self.pop_onsave);
        },
        addpanel_onclick:function() {
          ProcPanelSelector.pop(self.fs, self.pop_onsave);
        },
        pop_onsave:function() {
          self.table.load();
          self.setUpdated();
        }
      }
    })
  }
}
/**
 * TableLoader DocHistoryTable
 */
DocHistoryTable = {
  create:function(container, height) {
    return Html.TableLoader.create(container, 'fsgr').extend(function(self) {
      return {
        onupdate:function() {},
        onexit:function() {},
        //
        init:function() {
          self.setHeight(height);
          self.thead().trFixed()
            .th('Date').w('10%')
            .th('Type').w('13%')
            .th('Item').w('27%')
            .th('Details').w('50%')
            .th().w('16px');;
          self.addTopFilter();
        },
        /*
         * @arg DocStub[] recs (optional)
         * @arg int cid (req'd on first call)
         */
        load:self.load.extend(function(_load, recs, cid, onselect) {
          if (cid)
            self.cid = cid;
          if (onselect)
            self.onselect_override = onselect;
          _load(recs);
        }),
        //
        fetch:function(callback_recs) {
          DocStubs.ajax().fetch(self.cid, callback_recs);
        },
        filterold:function(rec) {
          return {'Type':rec._type || self.filterBar.currentValue};
        },
        filter:function(rec) {
          return {
            'Type':rec._type, 
            'Proc/Result':rec.type == C_DocStub.TYPE_RESULT ? rec.name : null, 
            'Provider':rec.provider,
            'Facility':rec.facility};
        },
        rowBreaks:function(rec) {
          return [rec.date];
        },
        add:function(rec, tr) {
          var a;
          if (rec._loinc) {
            a = InfoButton.forLab(rec._loinc, self.cid);
          }
          tr.td(rec.date, 'bold nw')
            .td(rec._type)
            .select(AnchorDocStub)
            .td(rec.desc)
            .td(a);
        },
        preview:function(rec) {
          //var filtered = Array.filterOn(self.recs, '_type', self.getFilterValue());
          var filtered = self.getVisibleRecs();
          DocStubPreviewPop.pop(rec, filtered).bubble('onupdate', function() {
            self.load();
            self.onupdate();
          })
        },
        //
        onselect:function(rec) {
          if (self.onselect_override) {
            self.onselect_override(rec);
            //self.onexit();
          } else {
            self.preview(rec);
          }
        }
      }
    })
  }
}
