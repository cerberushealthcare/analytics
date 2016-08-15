InfoButton = {
  create:function(cs, cv, cid) {
    var url = InfoButton.getUrl(cs, cv);
    return Html.Anchor.create('infobutton', null, function() {
      Ajax.Procedures.record(cid, 602784);
      Page.popInfoButton(url);
    }).set('target', '_blank');
  },
  forDiag:function(icd, cid) {
    return this.create('2.16.840.1.113883.6.103', icd, cid);
  },
  forMed:function(rxcui, cid) {
    return this.create('2.16.840.1.113883.6.88', rxcui, cid);
  },
  forLab:function(loinc, cid) {
    return this.create('2.16.840.1.113883.6.1', loinc, cid);
  },
  //
  getUrl:function(cs, cv) {
    return 'http://apps2.nlm.nih.gov/medlineplus/services/mpconnect.cfm?mainSearchCriteria.v.cs=' + cs + '&mainSearchCriteria.v.c=' + cv + '&';
  }
}

InfoButtonPop = {
  
}

/**
 * InfoButtonResponse
 */
InfoButtonResponse = Object.Rec.extend({
  /*
   xsi
   base
   lang
   title
   subtitle
   author
   updated
   category[]
   entry[]
   */
  onload:function() {
    this.entry = InfoButtonEntry.reviveAll(this.entry);
  },
  //
  ajax:function() {
    var self = this;
    return {
      fetchDiag:function(icd, callback) {
        Ajax.InfoButton.diag(icd, callback);
      }
    }
  }
})
InfoButtonEntry = Object.Rec.extend({
  /*
   lang
   title
   link[]
   id
   updated
   summary
   */
})
