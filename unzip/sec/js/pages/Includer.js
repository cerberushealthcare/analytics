/**
 * Includer 
 * Global static  
 * Requires: Ajax.js
 * @author Warren Hornsby 
 */
var Includer = {
  // Pops
  CALENDAR:'js/pops/Calendar.js',
  DOC_OPENER:'js/pops/DocOpener.js',
  FACE_DIAGNOSES:'js/pops/FaceDiagnoses.js',
  FACE_HM:'js/pops/FaceHm.js',
  FACE_DOC_HX:'js/pops/FaceDocHx.js',
  FACE_TRACK:'js/pops/FaceTrack.js',
  FACE_VITALS:'js/pops/FaceVitals.js',
  MSG_PREVIEWER:'js/pops/MsgPreviewer.js',
  ORDER_SHEET:'js/pops/OrderSheet.js',
  PATIENT_EDITOR:'js/pops/PatientEditor.js',
  PATIENT_SELECTOR:'js/pops/PatientSelector.js',
  TRACKING_ENTRY:'js/pops/TrackingEntry.js',
  // HTML layouts
  HTML_CALENDAR:'js/pops/inc/Calendar.php',
  HTML_DOC_DOWNLOAD_FORM:'js/pops/inc/DocDownloadForm.html',
  HTML_DOC_OPENER:'js/pops/inc/DocOpener.php',
  HTML_FACE_ALLERGIES:'js/pops/inc/FaceAllergies.php',
  HTML_FACE_DIAGNOSES:'js/pops/inc/FaceDiagnoses.php',
  HTML_FACE_HM:'js/pops/inc/FaceHm.php',
  HTML_FACE_HX:'js/pops/inc/FaceHx.php',
  HTML_FACE_DOC_HX:'js/pops/inc/FaceDocHx.php',
  HTML_FACE_IMMUN:'js/pops/inc/FaceImmun.php',
  HTML_FACE_MEDS:'js/pops/inc/FaceMeds.php',
  HTML_FACE_TRACK:'js/pops/inc/FaceTrack.php',
  HTML_FACE_VITALS:'js/pops/inc/FaceVitals.php',
  HTML_MSG_PREVIEWER:'js/pops/inc/MsgPreviewer.php',
  HTML_ORDER_SHEET:'js/pops/inc/OrderSheet.php',
  HTML_PATIENT_SELECTOR:'js/pops/inc/PatientSelector.php',
  HTML_PATIENT_EDITOR:'js/pops/inc/PatientEditor.php',
  HTML_TRACKING_ENTRY:'js/pops/inc/TrackingEntry.php',
  // Tiles
  TILE_TRACK_TABLE:'js/tiles/TrackingTable.js',
  // Legacy pops
  AP_ICD_POP:'inc/ajax-pops/icd-pop.php',
  //AP_TEMPLATE_POPS:'inc/ajax-pops/template-pops.php',
  AP_CALENDAR:'inc/ajax-pops/calendar.php',                // uses pop.js
  //
  NO_CALLBACK:false,
  WORKING_ON:true,
  //
  _includes:{},  // [url:{loaded:b,scb:scb,urls:[url,..]}
  //
  ERR_GET:'Includer.get',
  /*
   * Get an include
   * - url: single 'url' or ['url',..]
   * - callback: optional callback[url], see Ajax.buildScopedCallback for format
   * - workingOn: optional, to overlay working while getting
   */
  get:function(url, callback, workingOn) {
    if (url == null) {
      throw Page.error(Includer.ERR_GET, 'Includer.get: null url');
    }
    var urls = this._makeUnloadedArray(url);
    var scb = Ajax.buildScopedCallback(callback, 'includer');
    if (urls == null) {
      async(function() {
        Ajax.callScopedCallback(scb, url);  
      });
      return;
    }
    if (workingOn) {
      Html.Window.working(true);
    }
    var i, div;
    for (i = 0; i < urls.length; i++) {
      url = urls[i];
      this._includes[url] = this._buildInc(scb, urls, workingOn);
    }
    for (i = 0; i < urls.length; i++) {
      url = urls[i];
      div = this._appendDiv(url);
      Ajax.include(urls[i], div, [this._getIncludeCallback, this]);
    }
  },
  getWorking:function(url, callback) {
    this.get(url, callback, Includer.WORKING_ON);
  },
  /*
   * Get include and pop
   */
  getDocOpener_preview:function(cid, sid, zoom) {
    this.get(Includer.DOC_OPENER, function(){DocOpener.popPreview(cid, sid, zoom)});
  },
  getDocOpener_open:function(sid) {
    if (window.DocOpener)
      DocOpener.openConsole(sid);
    else
      this.get(Includer.DOC_OPENER, function(){DocOpener.openConsole(sid)});
  },
  getDocOpener_new:function(cid, cname) {
    if (window.DocOpener)
      DocOpener.popNew(cid, cname);
    else
      this.get(Includer.DOC_OPENER, function(){DocOpener.popNew(cid, cname)});
  },
  getDocOpener_replicate:function(session, callback) {
    this.get(Includer.DOC_OPENER, function(){DocOpener.popReplicate(session, callback)})
  },
  getMsgPreviewer_pop:function(cid, mtid, zoom) {
    this.get(Includer.MSG_PREVIEWER, function(){MsgPreviewer.pop(cid, mtid, zoom)});
  },
  getPatientSelector_pop:function(callback) {
    this.get(Includer.PATIENT_SELECTOR, function(){PatientSelector.pop(callback)});
  },
  getPatientEditor_pop:function(client, popEdit, callback) {
    if (window.PatientEditor)
      PatientEditor.pop(client, popEdit, callback);
    else
      this.get(Includer.PATIENT_EDITOR, function(){PatientEditor.pop(client, popEdit, callback)});
  },
  getFaceDiagnoses_pop:function(fs, zoom, callback) {
    this.get(Includer.FACE_DIAGNOSES, function(){FaceDiagnoses.pop(fs, zoom, callback)});
  },
  getFaceDocHx_pop:function(fs, tab, zoom, callback) {
    this.get(Includer.FACE_DOC_HX, function(){FaceDocHx.pop(fs, tab, zoom, callback)});
  },
  getFaceHm_pop:function(fs, pcid, zoom, callback) {
    this.get(Includer.FACE_HM, function(){FaceHm.pop(fs, pcid, zoom, callback)});
  },
  getFaceVitals_pop:function(fs, id, zoom, callback) {
    this.get(Includer.FACE_VITALS, function(){FaceVitals.pop(fs, id, zoom, callback)});
  },
  getFaceTrack_pop:function(fs, zoom, callback) {
    this.get(Includer.HTML_FACE_TRACK, function(){FaceTrack.pop(fs, zoom, callback)});
  },
  getTrackingEntry_pop:function(trackItem, callback) {
    this.getWorking([Includer.TRACKING_ENTRY, Includer.HTML_TRACKING_ENTRY], function() {
      TrackingEntry.pop(trackItem, callback);  
    });
  },
  getCalendar_pop:function(value, callback) {
    this.getWorking([Includer.CALENDAR, Includer.HTML_CALENDAR], function() {
      Calendar.pop(value, callback);
    });
  },
  getClock_pop:function(value, callback) {
    this.getWorking([Includer.CALENDAR, Includer.HTML_CALENDAR], function() {
      Clock.pop(value, callback);
    });
  },
  //
  _getIncludeCallback:function(url) {
    var inc = this._includes[url];
    inc.loaded = true;
    if (inc.scb && this._allLoaded(inc)) {
      if (inc.working) {
        Html.Window.working(false);
      }
      Ajax.callScopedCallback(inc.scb, inc.urls);
    }
  },
  _buildInc:function(scb, urls, workingOn) {
    return {
      'loaded':false,
      'scb':scb,
      'urls':urls,
      'working':workingOn};
  },
  _makeUnloadedArray:function(url) {  // returns null if all loaded
    var urls = (Array.is(url)) ? url : [url];
    var unloaded = [];
    var waiting = false;
    for (var i = 0; i < urls.length; i++) {
      url = urls[i];
      var inc = this._includes[url];
      if (inc == null) {
        unloaded.push(url);
      } else if (! inc.loaded) {
        waiting = true;
      }
    }
    return (unloaded.length == 0 && ! waiting) ? null : unloaded;
  },
  _allLoaded:function(inc) {
    if (inc.urls.length == 1) {
      return true;
    }
    for (var i = 0; i < inc.urls.length; i++) {
      var url = inc.urls[i];
      if (! this._includes[url].loaded) {
        return false;
      }
    }
    return true;
  },
  _appendDiv:function(url) {  
    var div = null;
    if (! this._isJavascript(url)) {
      var pi = _$('page-includes');
      div = Html.Div.create();
      pi.append(div);
    }
    return div;
  },
  _isJavascript:function(url) {
    return (url.split('.').pop()) == 'js';
  }
}
