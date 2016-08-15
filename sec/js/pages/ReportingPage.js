/**
 * Reporting Page
 * @author Warren Hornsby
 */
var ReportingPage = page = {
  //
  start:function(query) {
    ReportingTile.create(_$('tile')).showList();
    Page.setEvents();
    if (query.npr) 
      ReportingTile.newReport();
  },
  onresize:function() {
    var i = Html.Window.getViewportDim().height - 220;
    if (i != self.maxHeight) {
      self.maxHeight = i;
      ReportingTile.setMaxHeight(i);
    }
  }
}
/**
 * Tile ReportingTile
 *   ReportStubView listview
 *   ReportView reportview
 *   ReportCriteriaView criteriaview
 */
var ReportingTile = {
  create:function(container) {
    container.clean();
    return ReportingTile = Html.Tile.create(container).extend(function(self) {
      return {
        init:function() {
          self.listview = ReportStubView.create(self).hide()
            .bubble('onselect', self.listview_onselect)
            .bubble('onload', self.listview_onload)
            .bubble('oncreate', self.listview_oncreate);
          self.reportview = ReportView.create(self).hide()
            .bubble('onedit', self.reportview_onedit)
            .bubble('onexit', self.reportview_onexit);
          self.criteriaview = ReportCriteriaView.create(self).hide()
            .bubble('onexit', self.criteriaview_onexit);
        },
        showList:function() {
          self.reportview.hide();
          self.criteriaview.hide();
          return self.view = self.listview.show();
        },
        showReport:function(stub) {
          self.listview.hide();
          self.criteriaview.hide();
          return self.view = self.reportview.show().loadFromStub(stub);
        },
        showCriteria:function(report) {
          self.listview.hide();
          self.reportview.hide();
          return self.view = self.criteriaview.show().load(report);
        },
        setMaxHeight:function(i) {
          self.listview.setMaxHeight(i);
          self.reportview.setMaxHeight(i);
        },
        newReport:function() {
          if (self._loaded)
            self.listview.newReport();
          else
            self._newReport = true;
        },
        //
        listview_onselect:function(stub) {
          //UrlHash_Reporting.set_asReport(stub);
          self.showReport(stub);
        },
        listview_oncreate:function(report) {
          self.showCriteria(report);
        },
        listview_onload:function() {
          if (! self._loaded) {
            self._loaded = true;
            if (self._newReport)
              self.newReport();
          }
        },
        reportview_onedit:function(report) {
          self.showCriteria(report);
        },
        reportview_onexit:function() {
          //UrlHash_Reporting.set_asList();
          self.showList();
        },
        criteriaview_onexit:function() {
          self.showList().load();
        }
      }
    })
  }
}
var UrlHash_Reporting = UrlHash.extend({
  view:null,
  id:null,
  //
  set_asList:function() {
    this.set(this.asList());
  },
  set_asReport:function(stub) {
    this.set(this.asReport(stub));
  },
  //
  asList:function(stub) {
    return {
      'view':'list'};
  },
  asReport:function(stub) {
    return {
      'view':'report',
      'id':stub.reportId};
  }
})