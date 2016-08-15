/**
 * Page Header 
 * Requires: Ajax.js, ui.js, icd-pop.js
 */
Header = {
  _pageName:null,
  //
  load:function(pageName, inboxCt, unsignedCt, labCt) {
    this._pageName = pageName;
    this._elimImageFlicker();
    Header.Mail.load(inboxCt);
    Header.Review.load(unsignedCt);
    Header.Labs.load(labCt);
    if (me.Role.erx) {
      this._er = true;
      Header.Erx.load();
    }
  },
  logout:function() {
    if (! me.trial && this._er && Header.Erx.loaded) {
      Pop.Working.show('Logging out...', true);
      Ajax.Polling.getStatusCount(function(s) {
        Pop.Working.close();
        if (s && s.statusCount || s.pharmComCount) 
          Header.showLogoutWarning(s);
        else
          Page.Nav.goLogout();
      })
    } else {
      Page.Nav.goLogout();
    }
  },
  hideNavMenu:function() {
    _$('page-nav').invisible();
  },
  showLogoutWarning:function(s) {
    var a = [];
    if (s.pharmComCount)
      a.push(plural(s.pharmComCount, "pharmacy request"));
    if (s.statusCount) 
      a.push(plural(s.statusCount, "unfinished prescription"));
    var msg = "You have " + a.join(' and ') + ". Do you still want to logout?";
    Pop.Confirm.showImportant(msg, 'Yes, Logout', null, 'No, Review ERX', null, true, function(confirmed) {
      if (confirmed)
        Page.Nav.goLogout();
      else
        if (s.pharmComCount)
          Page.go(Page.PAGE_ERX_PHARM);
        else
          Page.go(Page.PAGE_ERX_STATUS);
    });
  },
  /*
   * @arg string code (optional)
   * @arg string desc (optional)
   * @arg fn(code, desc) callback (optional)
   */
  icdLook:function(code, desc, callback, as10) {
    if (callback == null)
      callback = false;
    Includer.getWorking([Includer.AP_ICD_POP], function() {
      showIcd(code, desc, callback, as10);
    });
  },
  icdLook10:function(code, desc, callback) {
    Header.icdLook(code, desc, callback, true);
  },
  closeSticky:function(id, remember) {
    hide(id);
    if (remember)
      Ajax.get(Ajax.SVR_POP, 'hideSticky', id, Ajax.NO_CALLBACK);
  },
  _elimImageFlicker:function() {
    try {
      document.execCommand('BackgroundImageCache', false, true);
    } catch(e) {}
  }
}
/*
 * Mail
 */
Header.Mail = {
  _newMsgCallback:null,
  //
  load:function(unreadCt) {
    this.setUnread(unreadCt);
    var self = this;
    Polling.Inbox.start(unreadCt, 
      function(ct) {
        self.setUnread(ct);
        if (self._newMsgCallback) 
          self._newMsgCallback.call(page);
        Html.Animator.pulse(_$('img-mail'));
      })
  },
  /*
   * Assign a callback when new message received
   */
  setNewMsgCallback:function(callback) {
    this._newMsgCallback = callback
  },
  /*
   * Set unread count
   */
  setUnread:function(ct) {
    if (ct == 0) 
      _$('a-mail').setText('Msg').setClass('mail');
    else 
      _$('a-mail').setText(ct + ' unread').setClass('mail newmail');
  }
}
/*
 * Review
 */
Header.Review = {
  _callback:null,
  //
  load:function(unsignedCt) {
    this.setunsigned(unsignedCt);
    var self = this;
    Polling.Review.start(unsignedCt, 
      function(ct, silent) {
        self.setunsigned(ct);
        if (self._callback) 
          self._callback.call(page);
        if (! silent)
          Html.Animator.pulse(_$('img-review'));
      })
  },
  setCallback:function(callback) {  // Assign a callback when review msg received
    this._callback = callback
  },
  setunsigned:function(ct) {
    if (ct == 0) 
      _$('a-review').setText('Review').setClass('todo');
    else 
      _$('a-review').setText('Review ' + ct).setClass('todo red');
  }
}
Header.Labs = {
  _callback:null,
  //
  load:function(labCt) {
    this.setCount(labCt);
    var self = this;
    Polling.Labs.start(labCt, 
      function(ct) {
        self.setCount(ct);
        if (self._callback) 
          self._callback.call(page);
      })
  },
  setCallback:function(callback) {
    this._callback = callback
  },
  setCount:function(ct) {
    if (ct == 0) 
      _$('a-labs').setText('Labs').setClass('labs').show();
    else 
      _$('a-labs').setText('Labs ' + ct).setClass('labs red').show();
  }
}
/*
 * ERX
 */
Header.Erx = {
  //
  load:function() {
    var status = CookieErx.get();
    if (status) 
      this.setAnchors(status);
    else
      Polling.ErxStatus.start(this.refresh.bind(this));
  },
  refresh:function() {
    if (this.getting)
      return;
    CookieErx.expire();
    var self = this;
    self.getting = true;
    Ajax.Polling.getStatusCount(function(status) {
      if (status) {
        self.loaded = true;
        self.getting = false;
        CookieErx.set(status);
        self.setAnchors(status);
      }
    });
  },
  status_onclick:function() {
    Page.go(Page.PAGE_ERX_STATUS);
  },
  pharm_onclick:function() {
    Page.go(Page.PAGE_ERX_PHARM);
  },
  setAnchors:function(status) {
    this.setAnchorPharm(status);
    this.setAnchorStatus(status);
  },
  setAnchorPharm:function(status) {
    this._setAnchor(_$('nc-pharm'), _$('a-ncp'), status._pharmText, status._pharmColor);
  },
  setAnchorStatus:function(status) {
    this._setAnchor(_$('nc-status'), _$('a-ncs'), status._statusText, status._statusColor);
  },
  _setAnchor:function(div, a, text, color) {
    if (text) {
      a.setText(text).style.color = (color) ? color : ''
      div.show();
    } else {
      div.hide();
    }
  }
}
//
CookieErx = {
  name:'NC_STATUS',
  duration:10,  // minutes
  //
  set:function(status) {
    Cookies.set(this.name, status, this.duration);
  },
  get:function() {
    return Cookies.get(this.name); 
  },
  expire:function() {
    Cookies.expire(this.name);
  }
}