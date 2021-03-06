/**
 * Message Previewer
 * Global static 
 */
MsgPreviewer = {
  cid:null,
  mtid:null,
  threads:null, 
  thread:null,   
  nav:null,
  _zoom:null,
  /*
   * - cid: client ID
   * - mtid: optional; if null, first msg will be shown
   */
  pop:function(cid, mtid, zoom) {
    overlayWorking(true);
    this.threadId = mtid;
    this._zoom = zoom;
    if (this.cid = cid && this.threads) {
      this._preview(this.threads, mtid);      
    } else {
      this.cid = cid;
      var self = this;
      Includer.get([Includer.HTML_MSG_PREVIEWER], function() {
        Ajax.get(Ajax.SVR_MSG, 'getClientThreads', self.cid, [self._preview, self]);
      });
    }
  },
  _preview:function(threads) {
    this._setThreads(threads);
    this._show(this.threadId);
    overlayWorking(false);
    if (this._zoom) {
      Pop.zoom('pop-mpv');
    } else {
      Pop.show('pop-mpv');
    }
    this._previewing = true;
  },
  mpvClose:function() {
    Pop.close();
  },
  mpvPrev:function() {
    this._show(this.nav.prev);
  },
  mpvNext:function() {
    this._show(this.nav.next);
  },
  mpvEdit:function() {
    Pop.close();
    Page.Nav.goMessage(this.threadId);
  },
  _setThreads:function(threads) {
    this.threads = threads;
    if (threads) {
      var last;
      for (var mtid in threads) {
        if (this.threadId == null) {
          this.threadId = mtid;
        }
        var t = threads[mtid];
        if (last) {
          last.prev = t.threadId;
          t.next = last.threadId;
        } else {
          t.next = null;
        }
        last = t;
      }
      last.prev = null;
    }
  },
  _show:function(mtid) {
    this.threadId = mtid;
    this.thread = this.threads[mtid];
    this._buildNav(mtid);
    setHtml('pop-mpv-body', '').className = 'working-circle';
    Ajax.get(Ajax.SVR_MSG, 'previewThread', mtid, function(html) {
      setHtml('pop-mpv-body', html).className = '';
    });
  },
  _buildNav:function(mtid) {
    var thread = this.threads[mtid];
    this.nav = {
      'on':mtid, 
      'prev':thread.prev, 
      'next':thread.next};
    setHtml('mpv-nav-on-div', this._buildNavHtml(mtid));
    if (this.nav.prev) {
      $('mpv-nav-prev').style.visibility = '';
      setHtml('mpv-nav-prev-div', this._buildNavHtml(this.nav.prev));
    } else {
      $('mpv-nav-prev').style.visibility = 'hidden';
    }  
    if (this.nav.next) {
      $('mpv-nav-next').style.visibility = '';
      setHtml('mpv-nav-next-div', this._buildNavHtml(this.nav.next));
    } else {
      $('mpv-nav-next').style.visibility = 'hidden';
    }
  },
  _buildNavHtml:function(mtid) {
    var thread = this.threads[mtid];
    return thread.subject + '<br/><span>(' + thread.dateCreated + ')</span>'; 
  }
};
