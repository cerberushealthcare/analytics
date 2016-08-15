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