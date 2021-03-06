/**
 * ProfileLoader
 */
function ProfileLoader(labelId, spanId) {
  this.lbl = _$(labelId);
  this.span = _$(spanId);
  this.clear();
}
ProfileLoader.prototype = {
  clear:function() {
    this.lbl.innerHTML = "";
    this.span.innerHTML = "";
  },
  /*
   * - spanText: may be array
   * - doBreak: optional, ProfileLoader.BREAK_X 
   */
  add:function(labelText, spanText, doBreak) {
    var la = [labelText];
    var sa = (Array.is(spanText)) ? spanText : [spanText];
    for (var i = 0, j = sa.length; i < j; i++) {
      if (i < la.length) {
        la[i] = (la[i] || '') + ProfileLoader._BR;
        sa[i] = '<span style="white-space:nowrap">' + (sa[i] || '') + '</span>' + ProfileLoader._BR;
      } else {
        if (! String.isBlank(sa[i])) {
          la.push(ProfileLoader._BR);
          sa[i] += ProfileLoader._BR;
        }
      }
    }
    if (doBreak == ProfileLoader.BREAK_BEFORE) {
      la.unshift(ProfileLoader._BRK);
      sa.unshift(ProfileLoader._BRK);
    } else if (doBreak == ProfileLoader.BREAK_AFTER) {
      la.push(ProfileLoader._BRK);
      sa.push(ProfileLoader._BRK);
    } 
    this.lbl.innerHTML += la.join("");
    this.span.innerHTML += sa.join("");
  }
}
ProfileLoader.BREAK_BEFORE = -1;
ProfileLoader.BREAK_AFTER = 1;
ProfileLoader._BRK = "<div class='brk'></div>";
ProfileLoader._BR = "<br/>"; 
