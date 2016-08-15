/**
 * TabAnchor UserSelector
 */
UserSelector = {
  create:function() {
    var My = this;
    return Html.AnchorTabSelector.create().extend(function(self) {
      return {
        onset:function() {},
        //
        load:function() {
          self.radios(C_Users);
        },
        /*
         * @arg User rec (optional) 
         */
        set:function(rec) {
          self.setText((rec) ? rec.name : 'Select a user');
        },
        /*
         * @arg int userId
         * @arg string name
         */
        setValueText:function(userId) {
          self.setValue(userId);
        },
        //
        onchange:function(value) {
          self.onset(null);
        }
      }
    })
  }
}
/**
 * Rec UserGroup
 */
UserGroup = Object.Rec.extend({
  onload:function() {
    Users.revive(this.Users);
  }
})
/**
 * RecArray Users
 */
Users = Object.RecArray.extend({
  getItemProto:function() {
    return User;
  }
})
/**
 * Rec User
 */
User = Object.Rec.extend({
  //
})