var rxLa;
function showPopRx() {
  event.returnValue = false;
  if (session.closed) return;
  var rxTrigger = event.srcElement;
  rxLa = document.getElementById(rxTrigger.la);
  rxDate.value = today;
  rxDoc.value = session.uname;
  rxTmar.value = lu_rx.tmar;
  rxLmar.value = lu_rx.lmar;
  rxPractice1.value = denull(session.ucompany);
  rxPractice2.value = denull(session.uaddress);
  rxPractice3.value = denull(session.uphone);
  rxPractice4.value = "Lic: " + denull(session.license);
  if (session.licenseState != null) {
    rxPractice4.value += " (" + session.licenseState + ")";
  }
  if (session.dea != null && session.dea != "") {
    rxPractice4.value += " DEA: " + session.dea;
  }
  if (session.npi != null && session.npi != "") {
    rxPractice4.value += " NPI: " + session.npi;
  }
  rxPatient.value = session.cname;
  rxDob.value = denull(session.cbirth);
  rxMed.value = rxLa.medName;
  if (rxLa.medDisp != null && rxLa.medDisp != "") {
    rxDisp.value = rxLa.medDisp;
  } else {
    rxDisp.value = calcRxDisp();
  }
  rxSig.value = calcRxSig();
  popWin = showPop("popRx");
  positionPopWin();
}
function calcRxDisp() { 
  var a = rxLa.medAmt;
  var i = 0.;
  var u = "";
  var cf = 5;
  if (a == "1/4") {
    i = 1/4;
  } else if (a == "1/3") {
    i = 1/3;
  } else if (a == "1/2") {
    i = 1/2;
  } else if (a == "1") {
    i = 1;
  } else if (a == "1 1/2") {
    i = 1.5;
  } else if (a == "2") {
    i = 2;
  } else if (a == "3") {
    i = 3;
  } else if (a == "4") {
    i = 4;
  } else if (a == "5") {
    i = 5;
  } else {
    u = " ml";
    if (a == "1/4 tsp") {
      i = 1/4 * cf;
    } else if (a == "1/3 tsp") {
      i = 1/3 * cf;
    } else if (a == "1/2 tsp") {
      i = 1/2 * cf;
    } else if (a == "3/4 tsp") {
      i = 3/4 * cf;
    } else if (a == "1 tsp") {
      i = cf;
    } else if (a == "1 1/4 tsp") {
      i = 5/4 * cf;
    } else if (a == "1 1/3 tsp") {
      i = 5/3 * cf;
    } else if (a == "1 1/2 tsp") {
      i = 3/2 * cf;
    } else if (a == "1 3/4 tsp") {
      i = 7/4 * cf;
    } else if (a == "2 tsp") {
      i = 2 * cf;
    } else if (a == "3 tsp") {
      i = 3 * cf;
    } else if (a == "0.4 ml") {
      i = 0.4;
    } else if (a == "0.8 ml") {
      i = 0.8;
    } else if (a == "1 ml") {
      i = 1;
    } else if (a == "1.2 ml") {
      i = 1.2;
    } else if (a == "1 1/2 ml") {
      i = 1.5;
    } else if (a == "1.6 ml") {
      i = 1.6;
    } else if (a == "2 ml") {
      i = 2;
    } else if (a == "2 1/2 ml") {
      i = 2.5;
    } else if (a == "3 ml") {
      i = 3;
    } else if (a == "3 1/2 ml") {
      i = 3.5;
    } else if (a == "4 ml") {
      i = 4;
    } else if (a == "4 1/2 ml") {
      i = 4.5;
    } else if (a == "5 ml") {
      i = 5;
    } else {
      return "QS";
    }
  }
  // Calculate 1-day amt
  var f = rxLa.medFreq;
  var m = 0.;
  if (f == "every hour") {
    m = i * 24;
  } else if (f == "every 2 hours") {
    m = i * 12; 
  } else if (f == "every 3 hours") {
    m = i * 8; 
  } else if (f == "every 4 hours") {
    m = i * 6; 
  } else if (f == "every 6 hours") {
    m = i * 4; 
  } else if (f == "every 8 hours") {
    m = i * 3; 
  } else if (f == "every 12 hours") {
    m = i * 2; 
  } else if (f == "daily") {
    m = i; 
  } else if (f == "BID") {
    m = i * 2; 
  } else if (f == "TID") {
    m = i * 3; 
  } else if (f == "QID") {
    m = i * 4; 
  } else if (f == "five times daily") {
    m = i * 5; 
  } else if (f == "QAM") {
    m = i; 
  } else if (f == "QHS") {
    m = i; 
  } else if (f == "every 2 days") {
    m = i * 1/2; 
  } else if (f == "every 3 days") {
    m = i * 1/3; 
  } else if (f == "MWF") {
    m = i * 12/30; 
  } else if (f == "once weekly") {
    m = i * 4/30; 
  } else if (f == "once monthly") {
    m = i * 1/30; 
  } else if (f == "every 2 weeks") {
    m = i * 2/30; 
  } else if (f == "every 10 days") {
    m = i * 3/30; 
  } else {
    return "QS";
  }
  // Multiply by period
  var l = rxLa.medLength;
  if (l == "1 day") {
  } else if (l == "2 days") {
    m = m * 2;
  } else if (l == "3 days") {
    m = m * 3;
  } else if (l == "4 days") {
    m = m * 4;
  } else if (l == "5 days") {
    m = m * 5;
  } else if (l == "6 days") {
    m = m * 6;
  } else if (l == "7 days") {
    m = m * 7;
  } else if (l == "10 days") {
    m = m * 10;
  } else if (l == "12 days") {
    m = m * 12;
  } else if (l == "14 days") {
    m = m * 14;
  } else if (l == "21 days") {
    m = m * 21;
  } else if (l == "28 days") {
    m = m * 28;
  } else if (l == "30 days") {
    m = m * 30;
  } else if (l == "60 days") {
    m = m * 60;
  } else if (l == "90 days") {
    m = m * 90;
  } else {
    m = m * 30;
  }
  m = Math.round(m * 100) / 100;
  return m + u;
}
function calcRxSig() {
  var mt = parseMedText("", rxLa.medAmt, rxLa.medFreq, rxLa.medRoute, rxLa.medLength, rxLa.medAsNeed, rxLa.medMeals);
  mt = mt.replace(/for long-term/, "");
  return "Take" + mt;
}
function rxDoCancel() {
  closePop();
}
function rxDoOk() {
  saveLuRx();
  closePop();
  setFreeText("med" + rxLa.id + "ft", " (RX " + today + " Disp: " + rxDisp.value + ", Refills: " + rxRefills.value + ")");
  var dns = "";
  if (rxDns.checked) {
    dns = "Do Not Substitute";
  }
  if (rxDaw.checked) {
    if (dns != "") {
      dns += " / ";
    }
    dns += "Dispense As Written";
  }
  
  var q = "date=" + rxDate.value
      + "&doc=" + rxDoc.value
      + "&practice1=" + rxPractice1.value
      + "&practice2=" + rxPractice2.value
      + "&practice3=" + rxPractice3.value
      + "&practice4=" + rxPractice4.value
      + "&patient=" + rxPatient.value
      + "&dob=" + rxDob.value
      + "&med=" + rxMed.value
      + "&disp=" + rxDisp.value
      + "&sig=" + rxSig.value
      + "&refills=" + rxRefills.value
      + "&dns=" + dns 
      + "&tm=" + rxTmar.value 
      + "&lm=" + rxLmar.value;
  window.open("rx.php?" + q + "&" + Math.random());
}
function refill(text) {
  document.getElementById("rxRefills").value = text;
}
function saveLuRx() {
  var tmar = floatValOrZero(rxTmar.value);
  var lmar = floatValOrZero(rxLmar.value);
  if (tmar != lu_rx.tmar || lmar != lu_rx.lmar) {
    lu_rx.tmar = tmar;
    lu_rx.lmar = lmar;
    postLookupSave("saveConsoleRx", lu_rx);
  }
}