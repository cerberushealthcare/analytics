/*
 * Show ICD popup from IMPR paragraph's ICD <a>
 */
function showPopIcd(pid, qid, as10) {
  if (session.closed)
    return;
  var desc = getParText(pid, true);
  var fn = as10? showIcd10 : showIcd;
  fn(null, desc,
    function(code, desc) {
      setIcd(qid, code, desc, as10);
    } 
  );  
}
function showPopIcd10(pid, qid) {
  showPopIcd(pid, qid, 1);
}
/*
 * Saved session action
 * Assign code and desc to ICD <a>
 * - code: pass null to clear ICD 
 */
function setIcd(qid, code, desc, as10) {
  var a = $icd(qid, as10);
  if (a) {
    if (code) {
      var arg = as10 ? ', 1' : '';
      pushAction("setIcd('" + qid + "','" + esc(code) + "','" + esc(desc) + "'" + arg + ")", "Set ICD");
      a.icd = code;
      a.title = desc;
      a.innerText = "(" + code + ")";
      a.className = "icdset";
      a.parentElement.style.display = "inline";
    } else {
      pushAction("setIcd('" + qid + "',null,null)", "Clear ICD");
      a.icd = null;
      a.title = '';
      a.innerText = "ICD";
      a.className = "icd";
    }
  }
  questions[qid].blank = false;
}
/*
 * Return <a> of paragraph ICD code
 */
function $icd(qid, as10) {
  return $(qidify(qid) + (as10 ? 'icd10' : 'icd'));
}
/*
 * Return ICD code assigned to question
 */
function getIcd(qid) { // doesn't seem to be used
  var a = $icd(qid);
  return (a && a.icd) ? a.icd : null;
}
