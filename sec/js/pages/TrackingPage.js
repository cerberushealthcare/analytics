/**
 * Tracking Page
 * Static page controller (instance assigned to global variable 'page')
 * @author Warren Hornsby
 */
var TrackingPage = {
  load:function(query) {
    page.table = TrackingTable.create(_$('tracking-table-tile'), query.id, query.pix);
    Page.setEvents();
  },
  onresize:function() {
    page.table.setHeight(Html.Window.getViewportDim().height - 275);
  }
}
/**
 * Assign global instance
 */
var page = TrackingPage;  
