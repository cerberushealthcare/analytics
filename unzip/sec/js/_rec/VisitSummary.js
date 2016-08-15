VisitSummary = Object.Rec.extend({
  /*
   clientId
   finalId   
   dos
   sessionId
   finalHead
   finalBody
   finalizedBy
   diagnoses
   iols
   instructs
   vitals
   meds
   */
  //
  ajax:function(worker) {
    var self = this;
    var SVR = 'VisitSummary';
    worker = worker || Html.Window;
    return {
      getPending:function(cid, callback) {
        Ajax.getr(SVR, 'getPending', cid, VisitSummary, worker, callback);
      },
      finalize:function() {
        Html.ServerForm.submit(SVR, 'finalize', self);
      },
      //
      reprint:function(rec) {
        Html.ServerForm.submit(SVR, 'reprint', {'cid':rec.clientId, 'fid':rec.finalId});
      }
    }
  }
})