var m_today;
var m_now;  
var m_setting;  // Date preset
var m_month;  // Selected month
var m_year;  // Selected year
var m_fmt;  // Format (0=none, 1=full with "in/on" prefix)
var calQ;  // Clicked question
var calO;  // Option of clicked question, where cal values goes

var M_START_YEAR = 1910;
var ONE_DAY =  86400000;

function showPopCal0(qid) {
  m_fmt = 0;
  showPopCal(qid);
}
function showPopCal1(qid) {
  m_fmt = 1;
  showPopCal(qid);
}

function showPopCal(qid) {
  event.returnValue = false;
  if (session.closed) return;
	calQ = questions[qid];
  calO = calQ.opts[0];
  mcalPreset(calO.text);
  popCalT.innerText = calQ.desc;
  popWin = showPop("popCalendar");
  popWin.style.width = "250px";
  positionPopWin();
}

function mcalPreset(v) {
  m_now = new Date();
  m_today = new Date(m_now.getFullYear(), m_now.getMonth(), m_now.getDate());
  m_setting = null;
  if (v != null && v.length > 0) {
    if (m_fmt == 1) {
      v = v.substring(3);  // extract the leading in/on
    }
    var a = v.split("/");
    if (a.length > 2) {
      m_year = parseInt(a[2], 10);
      m_month = parseInt(a[0], 10) - 1;
      day = parseInt(a[1], 10);
      m_setting = new Date(m_year, m_month, day);
      if (m_setting.toString() == "NaN") {
        m_setting = null;
      }
    } else {
      a = v.split(",");
      if (a.length > 1) {
        var b = a[0].split(" ");
        m_month = mmonthIndex(b[0]);
        day = parseInt(b[1], 10);
        m_year = parseInt(a[1], 10);
        m_setting = new Date(m_year, m_month, day);
        if (m_setting.toString() == "NaN") {
          m_setting = null;
        }
      } else {
        a = v.split(" of ");
        if (a.length > 1) {
          m_month = mmonthIndex(a[0]);
          day = 1;
          m_year = parseInt(a[1], 10);
          m_setting = new Date(m_year, m_month, day);
          if (m_setting.toString() == "NaN") {
            m_setting = null;
          }
        } else {
          m_year = parseInt(v, 10);
          day = 1;
          m_month = 0;
          m_setting = new Date(m_year, m_month, day);
          if (m_setting.toString() == "NaN") {
            m_setting = null;
          }
        }
      }
    }
  }
  if (m_setting == null) {
    m_year = m_now.getFullYear();
    m_month = m_now.getMonth();
  }
  mcalLoadCombos();
  mformatCalendar();
}

function calUnk() {
  var qid = calQ.id;
  var value = "on an unknown date";
  setCalValue(qid, value);
  closePop();
}

function calMonthOnly() {
  var qid = calQ.id;
  var value = aMonth.innerText + " of " + aYear.innerText;
  if (m_fmt == 1) value = "in " + value;
  setCalValue(qid, value);
  closePop();
}

function calYearOnly() {
  var qid = calQ.id;
  var value = aYear.innerText;
  if (m_fmt == 1) value = "in " + value;
  setCalValue(qid, value);
  closePop();
}

function calOnClick() {
  td = window.event.srcElement;
  if (td.tagName == "TD") {
    day = parseInt(td.innerText, 10);
    if (day > 0) {
      if (m_fmt == 1) {
        calFormatFullDate(day);
      } else {
        month = m_month + 1;
        returnMonth = (month < 10) ? "0" + month : String(month);
        returnDay = (day < 10) ? "0" + day : String(day);
        var value = returnMonth + "/" + returnDay + "/" + m_year;
        var qid = calQ.id;
        setCalValue(qid, value);
      }
      closePop();
    }
  }
}

function calFormatFullDate(day) {
  var qid = calQ.id;
  var value = "on " + aMonth.innerText + " " + day + ", " + aYear.innerText;
  setCalValue(qid, value);
}

function setCalValue(qid, value) {
  var q = setFormattedOption(qid, value);
  pushAction("setCalValue(" + qid + ",'" + value + "')", qUndoText(q));
}

function monMouseOut() {

  td = window.event.srcElement;
  if (td.tagName == "TD") {
    if (td.className == "styleSelected") {
      td.className = td.oldClassName;
    }
  }
}

function monMouseOver() {

  td = window.event.srcElement;
  if (td.tagName == "TD") {
    if (parseInt(td.innerText, 10) > 0) {
      td.oldClassName = td.className;
      td.className = "styleSelected";
    }
  }
}

function msetDateByCombos() {

  m_month = popcboMonth.selectedIndex;
  m_year = parseInt(popcboYear.value, 10);
  mformatCalendar();
}

function mcalLoadCombos() {

  popcboMonth.innerHTML = "";
  popcboYear.innerHTML = "";

  for (month = 0; month <= 11; month++) {
    popcboMonth.add(mcreateOption(mmonthName(month), month));
  }
  for (year = M_START_YEAR; year <= m_now.getFullYear() + 10; year++) {
    popcboYear.add(mcreateOption(year, year));
  }
}

function mcreateOption(text, value) {

  opt = document.createElement("OPTION");
  opt.text = text;
  opt.value = value;
  return opt;
}

function mformatCalendar() {

  // Set title
  aMonth.innerText = mmonthName(m_month);
  aYear.innerText = m_year;

  // Change combo
  popcboMonth.selectedIndex = m_month;
  popcboYear.selectedIndex = m_year - M_START_YEAR;

  // Init date counter with first Sunday to display
  dFirstOfMonth = new Date(m_year, m_month, 1);
  dCounter = new Date(dFirstOfMonth.valueOf() - dFirstOfMonth.getDay() * ONE_DAY);

  // Assign days to calendar
  for (row = 3; row <= 8; row++) {
    for (cell = 0; cell <= 6; cell++) {
      if (dCounter.getMonth() == m_month) {
        tblCal.rows(row).cells(cell).innerText = dCounter.getDate();
        if ((m_setting != null) && (dCounter.getMonth() == m_setting.getMonth()) && (dCounter.getFullYear() == m_setting.getFullYear()) && (dCounter.getDate() == m_setting.getDate())) {
          tblCal.rows(row).cells(cell).className = "styleSetting";
        } else {
          if (dCounter.valueOf() == m_today.valueOf()) {
            tblCal.rows(row).cells(cell).className = "styleToday";
          } else {
            if ((cell == 0) | (cell == 6)) {
              tblCal.rows(row).cells(cell).className = "styleWeekend";
            } else {
              tblCal.rows(row).cells(cell).className = "styleDay";
            }
          }
        }
      } else {
        tblCal.rows(row).cells(cell).innerHTML = "";
        tblCal.rows(row).cells(cell).className = "styleOffDay";
      }

      // Increment counter
      dCounter.setDate(dCounter.getDate() + 1);
    }
    if (row == 7) {
      if (dCounter.getMonth() != m_month) {
        mhideExtraRow();
        return;
      }
    }
  }
}

function mprevMonth() {

  if (m_month == 0) {
    m_month = 11;
    m_year = m_year - 1;
  } else {
    m_month = m_month - 1;
  }
  mformatCalendar();
}

function mnextMonth() {

  if (m_month == 11) {
    m_month = 0;
    m_year = m_year + 1;
  } else {
    m_month = m_month + 1;
  }
  mformatCalendar();
}

function mhideExtraRow() {

  for (cell = 0; cell <=6; cell++) {
    mtrExtraRow.cells[cell].className = "styleHide";
    mtrExtraRow.cells[cell].innerHTML = "&nbsp;";
  }
}

function mmonthName(month) {

  switch (month) {
    case 0:
      return "January";
    case 1:
      return "February";
    case 2:
      return "March";
    case 3:
      return "April";
    case 4:
      return "May";
    case 5:
      return "June";
    case 6:
      return "July";
    case 7:
      return "August";
    case 8:
      return "September";
    case 9:
      return "October";
    case 10:
      return "November";
    case 11:
      return "December";
  }
}

function mmonthIndex(month) {

  if (month == "January") return 0;
  if (month == "February") return 1;
  if (month == "March") return 2;
  if (month == "April") return 3;
  if (month == "May") return 4;
  if (month == "June") return 5;
  if (month == "July") return 6;
  if (month == "August") return 7;
  if (month == "September") return 8;
  if (month == "October") return 9;
  if (month == "November") return 10;
  if (month == "December") return 11;
}