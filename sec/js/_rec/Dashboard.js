Dashboard = Object.Rec.extend({
  /*
   DashKeys keys
   Client[] patients
   MsgThreads messages
   Appt[] appts
   DocStubs unreviewed
   Session[] unsigned
   */
  onload:function() {
    this.setr('keys', DashKeys);
    this.setr('messages', MsgThreads);
  },
  set_fromRefetch:function(dash) {
    this.keys = dash.keys;
    if (dash.patients)
      this.patients = dash.patients;
    if (dash.messages)
      this.messages = dash.messages;
    if (dash.appts)
      this.appts = dash.appts;
    if (dash.unreviewed)
      this.unreviewed = dash.unreviewed;
    if (dash.unsigned)
      this.unsigned = dash.unsigned;
  },
  //
  ajax:function(worker) {
    var self = this;
    return {
      fetch:function(callback) {
        Ajax.Dashboard.get(worker, callback);
      },
      fetchMessages:function(id, callback) {
        Ajax.Dashboard.getMessages(id, worker, callback);
      },
      refetchAppts:function(date, doctor, callback) {
        Ajax.Dashboard.getAppts(date, doctor, worker, function(dash) {
          self.appts = null;
          self.set_fromRefetch(dash);
          callback(self);
        })
      },
      refetchMessages:function(id, callback) {
        Ajax.Dashboard.getMessages(id, worker, function(dash) {
          self.messages = null;
          self.set_fromRefetch(dash);
          callback(self);
        })
      }
    }
  }
})
DashKeys = Object.Rec.extend({
  /*
   ugid
   userId
   DashUsers users
   apptDate
   apptDoctor
   msgRecipient
   */
  onload:function() {
    this.setr('users', DashUsers);
    if (this.apptDoctor == null)
      this.apptDoctor = me.docId;
  },
  getRecipientUser:function() {
    return this.users.get(this.msgRecipient);
  }
})
DashUser = Object.Rec.extend({
  //
  isSelf:function() {
    return this.userId == me.userId;
  }
})
DashUsers = Object.RecArray.of(DashUser, {
  get:function(userId) {
    return DashUsers._getMap(this)[userId];
  },
  //
  _getMap:function(array) {
    if (DashUsers._map == null) 
      DashUsers._map = Map.from(array, 'userId');
    return DashUsers._map;
  }
})