/**
 * Session document function library
 * Global static
 */
var DocLib = {
  calculateAge:function(dob) {
    var age = '';
    if (dob) {
      var now = new Date();
      var born;
      try {
        born = new Date(dob);
      } catch (e) {
        born = new Date(1960, 1, 1);
      }
      age = Math.floor((now.getTime() - born.getTime()) / (365.25 * 24 * 60 * 60 * 1000));
    }
    return age;
  },
  formatSex:function(sex) {
    return (sex) ? 'male' : 'female';
  }
}
