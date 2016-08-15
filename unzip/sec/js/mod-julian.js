/**
 * Modified Julian routines
 * Day # for dates, to coincide with ChartIndex toJulian()
 */
var GREGORIAN_EPOCH = -719162;
var MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
/*
 * Returns 'dd-mmm-yyyy'
 */
function dateFromJd(jd) {
  var a = jd_to_gregorian(jd);
  return lpad(a[2]) + '-' + MONTHS[a[1] - 1] + '-' + a[0];
}
function jdFromDate(s) {  // 'dd-mmm-yyyy'
  var a = s.split('-');
  a[0] = String.toInt(a[0]);
  a[1] = MONTHS.find(a[1]);
  a[2] = String.toInt(a[2]);
  return gregorian_to_jd(a[2], a[1], a[0]);
}
//
function gregorian_to_jd(year, month, day) {
  return (GREGORIAN_EPOCH - 1) +
    (365 * (year - 1)) +
    Math.floor((year - 1) / 4) +
    (-Math.floor((year - 1) / 100)) +
    Math.floor((year - 1) / 400) +
    Math.floor((((367 * month) - 362) / 12) +
    ((month <= 2) ? 0 : (leap_gregorian(year) ? -1 : -2)) +
    day);
}
function jd_to_gregorian(jd) {
  var wjd, depoch, quadricent, dqc, cent, dcent, quad, dquad, yindex, dyindex, year, yearday, leapadj;
  wjd = jd;
  depoch = wjd - GREGORIAN_EPOCH;
  quadricent = Math.floor(depoch / 146097);
  dqc = mod(depoch, 146097);
  cent = Math.floor(dqc / 36524);
  dcent = mod(dqc, 36524);
  quad = Math.floor(dcent / 1461);
  dquad = mod(dcent, 1461);
  yindex = Math.floor(dquad / 365);
  year = (quadricent * 400) + (cent * 100) + (quad * 4) + yindex;
  if (!((cent == 4) || (yindex == 4))) {
    year++;
  }
  yearday = wjd - gregorian_to_jd(year, 1, 1);
  leapadj = ((wjd < gregorian_to_jd(year, 3, 1)) ? 0 : (leap_gregorian(year) ? 1 : 2));
  month = Math.floor((((yearday + leapadj) * 12) + 373) / 367);
  day = (wjd - gregorian_to_jd(year, month, 1)) + 1;
  return new Array(year, month, day);
}
function mod(a, b) {
  return a - (b * Math.floor(a / b));
}
function leap_gregorian(year) {
  return ((year % 4) == 0) && (!(((year % 100) == 0) && ((year % 400) != 0)));
}
