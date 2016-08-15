/**
 * Document UI Library
 */
var DocUi = {
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
      age = '' + Math.floor((now.getTime() - born.getTime()) / (365.25 * 24 * 60 * 60 * 1000));
    }
    return age;
  },
  formatSex:function(sex) {
    return (sex) ? 'male' : 'female';
  },
  formatSessionLabel:function(s) {
    var label = s.title;
    if (s.closed) 
      label += ' (Signed)';
    return label;
  },
  createPreviewAnchor:function(cid, s, asZoom) {
    if (s) {
      var href = buildHrefFn('Includer.getDocOpener_preview', [cid, s.sessionId, asZoom]);
      var cls = 'action note-preview';
      return createAnchor(null, href, cls, s.title);
    } else {
      return createSpan('Facesheet');
    }
  }
}
