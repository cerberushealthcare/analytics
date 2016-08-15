var calcValue = "0";
var calcWasPreset = false;
var calcQ;  // Clicked question
var calcO;  // Option of clicked question, where calc value goes

//Show calc popup
function showPopCalc(qid) {
  event.returnValue = false;
  if (session.closed) return;
	calcQ = questions[qid];
  calcO = calcQ.opts[0];
  calcPreset(calcO.text);
  popCalcT.innerText = calcQ.desc;
  popWin = showPop("popCalc");
  popWin.style.width = "200px";
  positionPopWin();
}
function calcPreset(v) {
  if (v == "N/A") {
    calcValue = "";
  } else {
    calcValue = v.replace(/[^\d.-]/g,"");
    if (calcValue.length == 0) {
      calcValue = "0";
    }
  }
  calcDisplay();
  calcWasPreset = true;
}
function calcPush(v) {
  if (v == "c") {
    calcValue = "0";
  } else if (v == "n") {
    calcValue = "";
  } else if (v == "-") {
    if (calcValue.substring(0, 1) == "-") {
      calcValue = calcValue.substring(1);
    } else {
      calcValue = "-" + calcValue;
    }
  } else if (v == "b") {
    calcValue = calcValue.substring(0, calcValue.length - 1);
    if (calcValue.length == 0) {
      calcValue = "0";
    }
  } else {
    if (calcValue.length < 10) {
      if (calcWasPreset) {
        calcValue = "0";
      }
      if (v == ".") {
        if (calcValue.indexOf(".") == -1) {
          calcValue += v;
        }
      } else {
        if (calcValue == "0") {
          calcValue = v;
        } else if (calcValue == "-0") {
          calcValue = "-" + v;
        } else {
          calcValue += v;
        }
      }
    }
  }
  calcWasPreset = false;
  calcDisplay();
}
function calcDisplay() {
  if (calcValue.length == 0) {
    $("calcDisplay").innerText = "N/A";
  } else {
    $("calcDisplay").innerText = addCommas(calcValue);
  }
}
function addCommas(n) {
  x = n.split(".");
  x1 = x[0];
  x2 = x.length > 1 ? "." + x[1] : "";
  var rgx = /(\d+)(\d{3})/;
  while (rgx.test(x1)) {
    x1 = x1.replace(rgx, "$1" + "," + "$2");
  }
  return x1 + x2;
}
function setCalcValue(qid, value) {
  var q = questions[qid];
  setFormattedOption(qid, value);
}

// Action buttons
function calcDoOk() {
  var qid = calcQ.id;
  var value = $("calcDisplay").innerText;
  setCalcValue(qid, value);
  pushAction("setCalcValue(" + qid + ",'" + value + "')", qUndoText(calcQ));
  closePop();
}
function calcDoCancel() {
  closePop();
}
