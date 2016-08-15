/**
 * Rec PortalUser
 */
PortalUser = Object.Rec.extend({
  //
  onload:function() {
    this._status = this.uiStatus();
    this._scq1 = C_PortalUser.CUSTOM;
    this._scq2 = C_PortalUser.CUSTOM;
    this._scq3 = C_PortalUser.CUSTOM;
    this._lastLogin = 'Last Login: ' + this.uiLastLogin();
  },
  setCqs:function() {
    for (var i = 1; i <= 3; i++)  
      this.setCq(i);
  },
  setCq:function(i) {
    var sel = this['_scq' + i];
    var qkey = 'cq' + i;
    if (sel != C_PortalUser.CUSTOM)
      this[qkey] = C_PortalUser.QUESTIONS[sel];
    else if (this[qkey] == C_PortalUser.CUSTOM_Q)
      this[qkey] = null;
  },
  isActivated:function() {
    return this.active && this.status == C_PortalUser.STATUS_PW_SET;
  },
  isSuspended:function() {
    return ! this.active;
  },
  isPremium:function() {
    return this.subscription > 0;
  },
  uiStatus:function() {
    if (! this.active) 
      return 'Suspended';
    switch (this.status) {
      case C_PortalUser.STATUS_RESET:
      case C_PortalUser.STATUS_CHALLENGED:
        return 'Not yet activated';
      case C_PortalUser.STATUS_PW_SET:
        return 'Activated: ' + this.pwSet;
    }
  },
  uiSubscription:function() {
    if (this.isActivated())
      return C_PortalUser.SUBSCRIPTIONS[this.subscription];
  },
  uiLastLogin:function() {
    return (this.LastLogin) ? this.LastLogin.logDate : 'Never';
  },
  //
  asSelector:function() {
    return AnchorPortalUser.create(this);
  },
  from:function(fs) {
    if (fs.portalUser)
      return this.revive(fs.portalUser);
  },
  //
  ajax:function(worker) {
    var self = this;
    return {
      save:function(callback) {
        Ajax.PortalAccounts.savePortalUser(self, worker, callback);
      },
      reset:function(callback) {
        Ajax.PortalAccounts.resetPortalUser(self.Client.clientId, worker, callback);
      },
      suspend:function(callback) {
        Ajax.PortalAccounts.suspendPortalUser(self.Client.clientId, worker, callback);
      },
      edit:function(callback) {
        Ajax.PortalAccounts.editPortalUserFor(self.Client.clientId, worker, callback);
      }
    }
  }
})
//
PortalUsers = Object.RecArray.of(PortalUser, {
  //
  ajax:function(worker) {
    return {
      fetch:function(callback) {
        Ajax.PortalAccounts.getPortalUsers(callback);
      }
    }
  }
})
//
/**
 * Rec NewPortalUser
 */
NewPortalUser = Object.Rec.extend({
  onload:function(json) {
    this.setCqs();
  },
  setCqs:function() {
    for (var i = 1; i <= 3; i++)  
      this.setCq(i);
  },
  setCq:function(i) {
    var sel = this['_scq' + i];
    var qkey = 'cq' + i;
    if (sel != C_PortalUser.CUSTOM)
      this[qkey] = C_PortalUser.QUESTIONS[sel];
    else if (this[qkey] == C_PortalUser.CUSTOM_Q)
      this[qkey] = null;
  },
  from:function(client) {
    var id = IdGenerator.makeId(client.clientId);
    var pw = IdGenerator.makePw(client.clientId);
    return this.revive({
      'clientId':client.clientId,
      'uid':id,
      'pwpt':pw,
      'lastName':client.lastName.toUpperCase(),
      'zipCode':client.Address_Home && client.Address_Home.zip,
      'email':client.Address_Home && client.Address_Home.email1,
      '_scq1':'0',
      '_scq2':'1',
      '_scq3':'2'
    });
  },
  //
  ajax:function(worker) {
    var self = this;
    return {
      save:function(callback) {
        Ajax.PortalAccounts.createPortalUser(self, worker, callback);
      }
    }
  }
})
//
PortalMsgType = Object.LevelRec.extend({
  /*
   * msgTypeId
   * userGroupId
   * userId
   * name
   * active
   * sendTo
   */
  setui:function(userId, active, name, sendTo) {
    this.set('active', active);
    if (active) {
      this.set('name', name);
      this.set('sendTo', sendTo);
    }
    if (this.isDirty()) {
      this.userGroupId = me.userGroupId;
      this.userId = userId;
    }
    return this;
  },
  isNew:function() {
    return this._new;
  },
  //
  asNew:function() {
    return this.revive({
      'name':null,
      'active':true,
      'sendTo':null,
      '_new':1,
      '_dirty':1
    });
  }
})
PortalMsgTypes = Object.RecArray.of(PortalMsgType, {
  //
  ajax:function(worker) {
    var self = this;
    return {
      fetch:function(userId, callback) {
        Ajax.PortalAccounts.getPortalMsgTypes(userId, worker, callback);
      },
      save:function(userId, callback) {
        Ajax.PortalAccounts.savePortalMsgTypes(self, userId, worker, callback);
      }
    }
  }
})
//
IdGenerator = {
  LETTERS:'CHJLMNRTWX',
  DIGITS:'23479',
  //
  makeId:function(cid) {
    return this.encode(String.toInt(cid) + 100000, this.LETTERS) + this.encode(String.toInt(String.rnd(2)), this.DIGITS, 2);
  },
  makePw:function(cid) {
    return this.encode(String.toInt(String.rnd(5)) + 100000, this.LETTERS);
  },
  encode:function(n, cipher, len) {
    var code = '';
    var s = n.toString(cipher.length);
    if (len)
      s = String.zpad(s, len);
    for (var i = s.length - 1; i >= 0; i--) 
      code += cipher.charAt(String.toInt(s.charAt(i)));
    return code;
  }
} 