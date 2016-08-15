/**
 * Document Formatter
 * Global static
 */
var DocFormatter = {
  //
  htmlHeader:null,
  htmlBody:null,
  //
  _ix:0,
  /*
   * Build simple <html> string from console doc
   * Returns '<DIV id=head>..</DIV><HR/><DIV id=body>..</DIV>'
   * These may be retrieved separately via DocFormatter.htmlHeader and DocFormatter.htmlBody  
   */
  consoleToHtml:function() {
    var htmlTitle = this.toHtml($('title'));
    var htmlDoc = this.toHtml($('dSections'));
    if (htmlDoc == null)
      return '';
    this._splitHeaderBody(htmlDoc, htmlTitle);
    return this.htmlHeader + '<HR/>' + this.htmlBody;
  },
  toHtml:function(e) {
    this._ix++;
    var html = this._crawl(e);
    if (html)
      html = html.replace(/\u2022/g, "&#149;");
    return html;
  },
  /*
   * Create PDF from console doc
   */
  consoleToPdf:function(session) {
    var pdf;
    if (session.closed) {
      pdf = Pdf_Session.fromClosed(session);
    } else {
      this.consoleToHtml();
      pdf = Pdf_Session.from(session, this.htmlBody, this.htmlHeader);
    }
    pdf.download();
  },
  //
  _splitHeaderBody:function(htmlDoc, htmlTitle) {
    var h = htmlDoc.split('<HR/>');
    this.htmlHeader = (h.length == 2) ? h[0] : '';
    this.htmlBody = (h.length == 2) ? h[1] : h[0];
    htmlTitle = (htmlTitle) ? '<DIV id=title>' + htmlTitle + '</DIV>' : '';
    this.htmlHeader = '<DIV id=head>' + this.htmlHeader + '</DIV>';
    this.htmlBody = '<DIV id=body>' + htmlTitle + this.htmlBody + '</DIV>';
  },
  _crawl:function(e) {
    if (! e.tagName) 
      return this._getText(e);
    if (this._hidden(e)) 
      return null;
    if (e.tagName == 'BR')
      return '&nbsp;<BR/>';
    if (e.className == 'cloneat')
      return '<BR/>';
    if (e.children.length == 0) { 
      if (e.textContent)
        return this._tag(e, e.textContent);
      else if (e.innerText) 
        return this._tag(e, e.innerText);
      else if (e.tagName == 'IMG')
        return e.outerHTML;
      else
        return null;
    }
    var hs = [];
    for (var i = 0; i < e.childNodes.length; i++) {
      var h = this._crawl(e.childNodes[i]);
      if (h)
        hs.push(h);
    }
    if (hs.length == 0) 
      return null;
    return this._tag(e, hs.join(''));
  },
  _tag:function(e, text) {
    text = this._fixText(text);
    if (text.indexOf('\chpgn') > 0) 
      return null;
    var tag = this._getTag(e);
    if (tag) {
      if (e.className == 'pTitle') 
        text = '<B><U>' + text + '</U></B>';
      else if (e.tagName == 'TH')
        text = '<B>' + text + '</B>';
      else if (e.className.contains('bu'))
        text = '<B><U>' + text + '</U></B>';
      else
        text = this._fixLfs(text);
      var h = '<' + tag + '>' + text + '</' + e.tagName + '>'; 
      if (e.tagName == 'TABLE') 
        h += '&nbsp;';
      return h;
    } else {
      if (e.className == 'dunsel')
        text += ' ';
      else if (e.className == 'listAnchor' || e.className == 'listAnchor2')
        text = '<BR/>' + text;
      else if (e.className.contains('bu'))
        text = '<B><U>' + text + '</U></B>';
      else if (e.suid == '@header') 
        text = text + '<HR/>';
      else if (e.className == 'del' || e.className == 'notext')
        return null;
      return text;
    }
  },
  _fixText:function(d) {
    d = d.replace(/\xa0/g, " ");  // change hex-160 to space
    d = d.replace(/\s+\./g, ".");  // elim spaces before period
    d = d.replace(/\.\s+/g, ". ");  // one space after period
//    return trim(d);
    return d;
  },
  _getText:function(e) {
    if (e.data) 
      return (e.data.substr(0, 1) == ' ') ? e.data.substr(1) : e.data;
    var text = nullify(e.toString());
    if (text == '[object]')
      text = null;
    return text;
  },
  _getTag:function(e) {
    var tag = null;
    switch (e.tagName) {
      case 'P':
      case 'THEAD':
      case 'TBODY':
      case 'TR':
        tag = e.tagName;
        break;
      case 'TABLE':
        tag = 'TABLE nobr="true" border=1';
        break;
      case 'TD':
      case 'TH':
        tag = e.tagName + ' align=center';
        if (e.colSpan > 1) 
          tag += ' colSpan=' + e.colSpan;
        if (e.rowSpan > 1)
          tag += ' rowSpan=' + e.rowSpan;
        if (e.tagName == 'TH') 
          tag += ' bgcolor=#A0A0A0';
        return tag;
    }
    return tag;
  },
  _fixLfs:function(text) {
    return removeNullsFromArray(text.split('<BR/>')).join('<BR/>');
  },
  _hidden:function(e) {
    if (e.suid == '@header')
      return false;
    if (e.className == 'h' || e.className == 'h2' || e.className == 'clone' || e.className == 'noprt' || e.className == 'icd' || e.className == 'cmd erx') {
      if (e.children.length) {
        for (var i = 0; i < e.children.length; i++) 
          e.children[i].crawlix = this._ix;
      }
      return true;
    }
    if (e.getAttribute('name') == 'clonePop') 
      if (e.getElementsByTagName('DIV').length == 0)
        return true;
    if (e.crawlix == this._ix)
      return true;  // already processed
    e.crawlix = this._ix;
    return false;
  }
}