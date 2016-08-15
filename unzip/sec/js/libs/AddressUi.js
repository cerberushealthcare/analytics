/**
 * Address UI Library
 */
var AddressUi = {
  formatPhone:function(phone, type) {
    if (phone) {
      var s = phone;
      var t = this.formatPhoneType(type);
      if (t)
        s += ' (' + t + ')';
      return s;
    }
  },
  formatPhoneType:function(type) {
    if (! Object.isUndefined(type))
      return C_Address.PHONE_TYPES[type];
  }
}
