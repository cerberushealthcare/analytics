Profile = Object.Rec.extend({
  /*
   User
   Group
   Bill
   */
  onload:function() {
    this.setr('User', ProfileUser);
    this.setr('Group', ProfileGroup);
    this.setr('Bill', ProfileBill);
  },
  //
  ajax:function() {
    return {
      get:function(callback) {
        Ajax.Profile.get(callback);
      }
    }
  }
})
//
ProfileUser = Object.Rec.extend({
  /*
   userId
   uid
   name
   subscription
   active
   userGroupId
   userType
   licenseState
   license
   dea
   npi
   email
   expiration
   expireReason
   roleType
   mixins
   NcUser
   */
  onload:function() {
    this.setr('NcUser', NcUser);
  },
  //
  ajax:function() {
    var self = this;
    return {
      save:function(callback) {
        Ajax.Profile.saveUser(self, callback);
      }
    }
  }
})
//
NcUser = Object.Rec.extend({
  /*
   userId
   userType
   roleType
   partnerId
   nameLast
   nameFirst
   nameMiddle
   namePrefix
   nameSuffix
   freeformCred
   */
})
ProfileGroup = Object.Rec.extend({
  /*
   userGroupId
   name
   estTzAdj
   sessionTimeout
   Address
   */
  onload:function() {
    this.Address.estTzAdj = this.estTzAdj;
  },
  //
  ajax:function() {
    var self = this;
    return {
      save:function(callback) {
        Ajax.Profile.saveGroup(self, callback);
      },
      saveTimeout:function(callback) {
        Ajax.Profile.saveTimeout(self.sessionTimeout, callback);
      } 
    }
  }
})
ProfileBill = Object.Rec.extend({
  /*
   userId
   planId
   sourceId
   active
   inactiveCode
   BillPlan
   BillSource
   */
  //
  onload:function() {
    if (this.BillSource) {
      this.setr('BillSource', ProfileBillSource);
      if (this.userId != this.sourceId)
        this.BillSource._locked = true;
    }
  }
})
ProfileBillSource = Object.Rec.extend({
  /*
  billSourceId
  userId
  billerUserId
  name
  addr1
  addr2
  city
  state
  zip
  country
  phone
  type
  number
  expMonth
  expYear
  _label 'MC ...1234'
  */
 //
 getExpYears:function() {
   var y = new Date().getFullYear();
   var start = y - 1;
   var end = y + 10;
   var s = {};
   for (var i = start; i < end; i++) {
     y = String.from(i);
     s[y] = y;
   }
   return s;
 },
 ajax:function() {
   var self = this;
   return {
     save:function(callback) {
       Ajax.Profile.saveBilling(self, callback);
     }
   }
 }
})