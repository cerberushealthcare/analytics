/**
 * Calendar Pop Controller
 * Requires: DateUi.js
 */
var Calendar = {
  callback:null,
  //
  date:null,
  now:null,
  //
  todayVal:null,
  year:null, 
  month:null,
  //
  cboMonth:null,
  cboYear:null,
  tbody:null,
  title:null,
  today:null,
  //
  ONE_DAY:86400000,
  START_YEAR:1900,
  /*
   * Show calendar pop
   * @arg '23-Nov-2011' text (optional)
   * @callback(string) '23-Nov-2011' on click, '' on clear, null on cancel
   */
  pop:function(text, callback) {
    this.load(text, callback);
    Pop.showPosCursor('pop-calen');
  },
  pClose:function() {
    Pop.close();
    this.callback(null);
  },
  pClear:function() {
    Pop.close();
    this.callback('');
  },
  load:function(text, callback) {
    this.callback = callback;
    this.tbody = _$('cal-tbody');
    this.title = _$('cal-title');
    this.today = _$('cal-today');
    this.set(text);
  },
  set:function(text) {
    this.now = new Date();
    this.todayVal = (new Date(this.now.getFullYear(), this.now.getMonth(), this.now.getDate())).valueOf();
    var dv = new DateValue(text);
    this.date = dv.getDate();
    var d = (this.date) ? this.date : this.now;
    this.year = d.getFullYear();
    this.month = d.getMonth();
    this._loadCombos();
    this._drawCalendar();
  },
  setByCombos:function() {
    this.month = this.cboMonth.selectedIndex;
    this.year = String.toInt(this.cboYear.value);
    this._drawCalendar();
  },
  setToday:function() {
    this.month = this.now.getMonth();
    this.year = this.now.getFullYear();
    this._drawCalendar();
  },
  click:function() {
    var dv = null;
    if (window.event && window.event.srcElement) {
      var day = window.event.srcElement.day;
      if (day) 
        dv = new DateValue([this.year, this.month, day]);
    }
    if (dv) { 
      Pop.close();
      this.callback(dv.toString());
    }
  },
  nextMonth:function() {
    if (this.month == 11) {
      this.month = 0;
      this.year++;
    } else {
      this.month++;
    }
    this._drawCalendar();
  },
  prevMonth:function() {
    if (this.month == 0) {
      this.month = 11;
      this.year--;
    } else {
      this.month--;
    }
    this._drawCalendar();
  },
  //
  _loadCombos:function() {
    if (this.cboMonth == null) {
      this.cboMonth = _$('cal-month');
      this.cboYear = _$('cal-year');
      for (var m = 0; m < 12; m++)
        addOpt2(this.cboMonth, m, DateUi.getMonthName(m));
      for (var y = Calendar.START_YEAR, ly = this.now.getFullYear() + 10; y <= ly; y++) 
        addOpt2(this.cboYear, y, y);
    }
  },
  _setCombos:function() {
    this.cboMonth.selectedIndex = this.month;
    this.cboYear.selectedIndex = this.year - Calendar.START_YEAR;
  },
  _drawCalendar:function() {
    this.title.innerText = DateUi.getMonthName(this.month) + ' ' + this.year;
    this._setCombos();
    var dFirstOfMonth = new Date(this.year, this.month, 1);  // first Sunday to display
    var dCounter = new Date(dFirstOfMonth.valueOf() - dFirstOfMonth.getDay() * Calendar.ONE_DAY);
    var todayShown = false;
    var tr, td, isToday;
    for (var row = 0; row < 6; row++) {
      tr = this.tbody.rows[row];
      for (var cell = 0; cell < 7; cell++) {
        td = tr.cells[cell];
        if (dCounter.getMonth() == this.month) {
          td.day = dCounter.getDate();
          td.innerText = td.day;
          isToday = dCounter.valueOf() == this.todayVal;
          if (this._equalsSetDate(dCounter)) { 
            td.className = 'setting';
          } else { 
            if (isToday) { 
              td.className = 'today';
            } else {
              td.className = (cell == 0 || cell == 6) ? 'weekend' : 'day';
            }
          }
          if (isToday)
            todayShown = true; 
        } else {
          td.innerHTML = '&nbsp;';
          td.className = 'offDay';
        }
        dCounter.setDate(dCounter.getDate() + 1);
      }
    }
    this.today.style.visibility = (todayShown) ? 'hidden' : '';
  },
  _equalsSetDate:function(d) {
    return this.date && d.getMonth() == this.date.getMonth() && d.getFullYear() == this.date.getFullYear() && d.getDate() == this.date.getDate();
  }
};
/**
 * Clock Pop Controller
 */
var Clock = {
  callback:null,    
  cboHour:null,
  cboMin:null,
  hourOnly:null,
  /*
   * Show clock pop
   * @arg '04:30PM' text (optional)
   * @arg bool hourOnly (optional)
   * @callback(string) '11:50AM' on click, '' on clear, null on cancel
   */
  pop:function(text, hourOnly, callback) {
    this.callback = callback;
    this.hourOnly = hourOnly;
    this.cboHour = _$('clkHour');
    this.cboMin = _$('clkMin');
    this.cboMin.style.display = (hourOnly) ? 'none' : '';
    this.set(text);
    Pop.show('pop-clock', Pop.POS_MOUSE);
  },
  set:function(text) {
    var tv = new TimeValue(text);
    var h = tv.toString(TimeValue.FMT_HHAMPM);
    var m = (this.hourOnly) ? '00' : tv.toString(TimeValue.FMT_MM);
    setValue_(this.cboHour, h);
    setValue_(this.cboMin, m);
  },
  pClose:function() {
    Pop.close();
    this.callback(null);
  },
  pClear:function() {
    Pop.close();
    this.callback('');
  },
  pOk:function() {
    var h = String.toInt(this.cboHour.value);
    var m = this.cboMin.value;
    var ampm = this.cboHour.value.substr(2);
    var tv = new TimeValue(h + ':' + m + ampm);
    Pop.close();
    this.callback(tv.toString());
  }
}
/* ui.js remnant */
function addOpt2(sel, value, text, selected) {
  var opt = document.createElement("option");
  sel.options.add(opt);
  opt.value = value || '';
  opt.text = text || '';
  opt.selected = (selected == true);
  return opt;
}
