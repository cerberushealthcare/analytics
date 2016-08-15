/**
 * Date UI Library
 */
DateUi = {
  //
  MONTH_NAMES:['January','February','March','April','May','June','July','August','September','October','November','December'],
  MONTH_ABBR_IX:{'Jan':0,'Feb':1,'Mar':2,'Apr':3,'May':4,'Jun':5,'Jul':6,'Aug':7,'Sep':8,'Oct':9,'Nov':10,'Dec':11},
  DAY_NAMES:['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
  //
  getMonthName:function(i) {
    return DateUi.MONTH_NAMES[i];
  },
  getMonthAbbr:function(i) {
    return DateUi.MONTH_NAMES[i].substr(0, 3);
  },
  getMonthIndex:function(abbr) {
    return DateUi.MONTH_ABBR_IX[abbr];
  },
  getDayName:function(i) {
    return DateUi.DAY_NAMES[i];
  },
  /*
   * @arg int fmt (optional, 1='dd/mm/yyyy', default 'dd-mmm-yyyy')
   * @return string
   */
  getToday:function(fmt) {  
    var now = DateValue.now();
    if (fmt) 
      return now.getMM() + '/' + now.getDD() + '/' + now.getYear(); 
    else
      return now.toString();
  },
  /*
   * @arg string text
   * @return false if invalid, 'dd-mmm-yyyy' if valid
   */
  validate:function(text) {  
    if (! String.is(text))
      return false;
    var dv = new DateValue(text);
    if (dv.isNull())
      return false;
    else
      return dv.toString();
  },
  /*
   * @arg string date 'on January 4, 2012'
   * @return 'January 4, 2012'
   */
  extractDate:function(date) { 
    if (date) {
      var inon = date.substr(0, 3);
      if (inon == "in " || inon == "on ") {
        return date.substring(3);
      }
    }
    return date;
  }
}
/**
 * Date Value object
 * @arg mixed setting:
 *        string, any formatted string, e.g. 'dd-mmm-yyyy', 'yyyy-mm-dd', 'mm-dd-yy', 'mm-dd-yyyy'
 *        array, e.g. [y, m, d]
 *        Date object
 */
function DateValue(setting) {
  if (setting)
    if (String.is(setting)) 
      this.date = stringToDate(setting);
    else if (Array.is(setting))
      this.date = arrayToDate(setting);
    else if (Object.is(setting))
      this.date = setting;
  //
  function stringToDate(text) {
    if (text == '')
      return null;
    var date;
    if (text.indexOf('-') >= 0) 
      a = text.split('-');
    else
      a = text.split('/');
    if (a.length == 3) 
      date = _parseDashDelimited(a);
    else 
      date = _parseSpaceDelimited(text);    
    if (date && date.toString() == 'NaN') 
      date = null;
    return date;
  }
  function arrayToDate(a) {
    var date;
    switch (a.length) {
      case 3:  // y,m,d
        date = new Date(a[0], a[1], a[2]);
        break;
      case 2:  // y,m
        date = new Date(a[0], a[1], 1);
        date.approx = DateValue.APPROX_MONTH_YEAR;
        break;
      case 1:  // y
        date = new Date(a[0], 0, 1);
        date.approx = DateValue.APPROX_YEAR;
        break;
   }
    return date;
  }
  function _parseDashDelimited(a) {
    var y, m, d;
    if (a[1].length == 3) {  // dd-mmm-yyyy
      y = String.toInt(a[2]);
      m = DateUi.getMonthIndex(a[1]);
      d = String.toInt(a[0]);
    } else if (a[0].length == 4) {  // yyyy-mm-dd
      y = String.toInt(a[0]);
      m = String.toInt(a[1]) - 1;
      d = String.toInt(a[2]);
    } else {  // mm-dd-yy or mm-dd-yyyy
      y = String.toInt(a[2]);
      m = String.toInt(a[0]) - 1;
      d = String.toInt(a[1]);
      if (y < 100) 
        if (y > 20) 
          y += 1900;
        else 
          y += 2000;
    }
    return new Date(y, m, d);
  }
  function _parseSpaceDelimited(text) {
    var y, m, d, approx, date;
    if (text.indexOf('unknown') > -1) {
      date = new Date();
      date.approx = DateValue.APPROX_UNKNOWN; 
    } else {
      var a = text.split(' ');
      if (a[0] == 'on') {  // 'December 12, 2010'
        a.shift();
        y = String.toInt(a[2]);
        m = DateUi.getMonthIndex(a[0].substr(0, 3));
        d = String.toInt(a[1]);
      } else {
        d = 1;  // Approximates
        if (a[0] == 'in') 
          a.shift();
        if (a.length == 1) {  // '1966'
          y = String.toInt(a[0]);
          m = 0;
          approx = DateValue.APPROX_YEAR;
        } else if (a.length == 2) {  // 'Feb 2009'
          y = String.toInt(a[1]);
          m = DateUi.getMonthIndex(a[0]);
          approx = DateValue.APPROX_MONTH_YEAR;
        } else if (a.length == 3) {  // 'December of 2010'
          y = String.toInt(a[2]);
          m = DateUi.getMonthIndex(a[0].substr(0, 3));
          approx = DateValue.APPROX_MONTH_YEAR;
        }
      }
      if (y == 0)
        return null;
      date = new Date(y, m, d);
      if (approx)
        date.approx = approx; 
    }
    return date;
  }
}
DateValue.prototype = {
  date:null,
  //
  getDate:function() {
    return this.date;
  },
  isApprox:function () {
    return (this.date.approx != null);
  },
  isExact:function() {
    return (this.date.approx == null);
  },
  isNull:function() {
    return (this.date == null);
  },
  getApprox:function() {
    return this.date.approx;  // DateValue.APPROX_ (or null if exact) 
  },
  getYear:function() {
    return (this.date) ? this.date.getFullYear() : null;
  },
  getMonth:function() {
    return (this.date) ? this.date.getMonth() : null;
  },
  getMM:function() {
    return (this.date) ? String.zpad(this.date.getMonth()) : null;
  },
  getMonthName:function() {
    return (this.date) ? DateUi.getMonthName(this.date.getMonth()) : null;
  },
  getMonthAbbr:function() {
    return (this.date) ? DateUi.getMonthAbbr(this.date.getMonth()) : null;
  },
  getDay:function() {
    return (this.date) ? this.date.getDate() : null;
  },
  getDD:function() {
    return (this.date) ? String.zpad(this.date.getDate()) : null;
  },
  getDow:function() {
    return (this.date) ? this.date.getDay() : null;
  },
  getDowName:function() {
    return (this.date) ? DateUi.getDayName(this.date.getDay()) : null;
  },
  getTimeValue:function() {
    return (this.date) ? new TimeValue(this.date.getHours() + ':' + String.zpad(this.date.getMinutes())) : null;
  },
  /*
   * @arg int fmt (optional, default FMT_DEFAULT) 
   * @arg bool requireExact (optional) 
   */
  toString:function(fmt, requireExact) {
    if (this.date) {
      if (requireExact && this.getApprox()) {
      } else {
        fmt = fmt || DateValue.FMT_DEFAULT;
        switch (fmt) {
          case DateValue.FMT_DEFAULT:
            switch (this.getApprox()) {
              case DateValue.APPROX_MONTH_YEAR:
                return this.getMonthAbbr() + ' ' + this.getYear();
              case DateValue.APPROX_YEAR:
                return this.getYear();
              case DateValue.APPROX_UNKNOWN:
                return null;
              default:
                return this.getDD() + '-' + this.getMonthAbbr() + '-' + this.getYear();
            }
          case DateValue.FMT_VERBOSE:
            switch (this.getApprox()) {
              case DateValue.APPROX_MONTH_YEAR:
                return this.getMonthName() + ' of ' + this.getYear();
              case DateValue.APPROX_YEAR:
                return this.getYear();
              case DateValue.APPROX_UNKNOWN:
                return 'date unknown';
              default:
                return this.getMonthName() + ' ' + this.getDay() + ', ' + this.getYear();
            }
          case DateValue.FMT_SENTENCE:
            switch (this.getApprox()) {
              case DateValue.APPROX_MONTH_YEAR:
                return 'in ' + this.getMonthName() + ' of ' + this.getYear();
              case DateValue.APPROX_YEAR:
                return 'in ' + this.getYear();
              case DateValue.APPROX_UNKNOWN:
                return 'on an unknown date';
              default:
                return 'on ' + this.getMonthName() + ' ' + this.getDay() + ', ' + this.getYear();
            }
          case DateValue.FMT_DATETIME:
            return this.getDay() + '-' + this.getMonthAbbr() + '-' + this.getYear() + ' ' + this.getTimeValue().toString();
        }
      }
    }
    return '';
  },
  toString_verbose:function() {
    return this.toString(DateValue.FMT_VERBOSE, true);
  }
},
//
DateValue.APPROX_MONTH_YEAR = 1;
DateValue.APPROX_YEAR = 2;
DateValue.APPROX_UNKNOWN = 3;
//
DateValue.FMT_SENTENCE = 1, // 'on December 14, 2010'
DateValue.FMT_DEFAULT = 2,  // '14-Dec-2010'
DateValue.FMT_DATETIME = 3, // '14-Dec-2010 04:30PM'
DateValue.FMT_VERBOSE = 4;  // 'December 14, 2010'
//
/*
 * @return DateValue
 */
DateValue.now = function() {
  return new DateValue(new Date());
}
/*
 * @arg int time Date time value
 * @return DateValue
 */
DateValue.fromTime = function(time) {
  var date = new Date();
  date.setTime(time);
  return new DateValue(date);
}
/*
 * @return DateValue
 */
DateValue.asUnknown = function() {
  var v = DateValue.now();
  v.date.approx = DateValue.APPROX_UNKNOWN;
  return v;
}
/*
 * @return '14-Dec-2010 04:30PM'
 */
DateValue.nowValue = function() {
  return DateValue.now().toString(DateValue.FMT_DATETIME);
}
/**
 * Time Value object
 * @arg mixed setting:
 *        string, e.g. '04:30PM', '4:30 PM', '16:30'
 *        array, e.g. [4, 30]
 */
function TimeValue(setting) {
  if (setting)
    if (String.is(setting)) 
      this.value = stringToMil(setting);
    else if (Array.is(setting)) 
      this.value = arrayToMil(setting);
  //
  function stringToMil(text) {
    var mil;
    text = String.trim(text);
    if (text && text != '') {
      var h, m;
      var ampm = text.substr(text.length - 2).toUpperCase();
      if (ampm == 'AM' || ampm == 'PM') 
        text = String.trim(text.substr(0, text.length - 2));
      else
        ampm = null;
      var a = text.split(':');
      h = String.toInt(a[0]);
      if (ampm) {
        if (h == 12) 
          h = 0;
        if (ampm == 'PM')
          h = h + 12;
      }
      if (a.length == 2) { 
        m = String.toInt(a[1]);
        mil = h * 100 + m;  
      } else {
        mil = h;
      }    
    }
    return mil;
  }
  function arrayToMil(a) {
    var mil;
    switch (a.length) {
      case 2:  // 4,30
        mil = a[0] * 100 + a[1];
        break;
      case 1:  // 430
        mil = a[0];
        break;
    }
    return mil;
  }
}
TimeValue.prototype = {
  value:null,  // int hhmm military
  //
  /*
   * @return int hour (0-23) military
   */
  getHour:function() {
    if (this.value == null) 
      return null;
    var h = this._getHM()[0];
    return h;
  },
  /*
   * @return int min (0-59)
   */
  getMin:function() {
    if (this.value == null) 
      return null;
    var m = this._getHM()[1];
    return m;
  },
  getAMPM:function() {
    if (this.value == null) 
      return null;
    return (this.value >= 1200) ? 'PM' : 'AM';
  },
  /*
   * @arg int fmt TimeValue.FMT (optional)
   * @return string e.g. '04:30PM' 
   */
  toString:function(fmt) {
    var text = '';
    if (this.value) { 
      fmt = fmt || TimeValue.FMT_STANDARD;
      var hm = this._getHM();
      var h = hm[0];
      var m = hm[1];
      switch (fmt) {
        case TimeValue.FMT_STANDARD:
        case TimeValue.FMT_HHAMPM:
          if (h > 12)
            h -= 12;
          else if (h == 0) 
            h = 12;
          if (fmt == TimeValue.FMT_STANDARD)
            text = String.zpad(h) + ':' + String.zpad(m) + this.getAMPM();
          else 
            text = String.zpad(h) + this.getAMPM();
          break;
        case TimeValue.FMT_MILITARY:
          text = String.zpad(h) + ':' + String.zpad(m);
          break;
        case TimeValue.FMT_MM:
          text = String.zpad(m);
          break;
      }
    }
    return text;
  },
  //
  _getHM:function() {
    var m = this.value % 100;
    var h = (this.value - m) / 100;
    return [h, m];
  }
}
TimeValue.FMT_STANDARD = 0;  // '04:05PM'
TimeValue.FMT_MILITARY = 1;  // '16:05'
TimeValue.FMT_HHAMPM = 2;    // '04PM'
TimeValue.FMT_MM = 3;        // '05'