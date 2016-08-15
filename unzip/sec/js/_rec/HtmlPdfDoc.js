HtmlPdfDoc = Object.Rec.extend({
  //
  body:null,
  head:null,
  filename:null,
  author:null,
  title:null,
  style:null,
  dos:null,
  //
  download:function() {
    Html.RecForm.create('serverPdfM.php', this).submit();
  }
})
Pdf_Session = HtmlPdfDoc.extend({
  //
  fromClosed:function(session) {
    var body, head, h = session.html.split('<HR/>');
    if (h.length > 1) {
      head = h.shift(); 
      body = h.join('');
    } else {
      head = '';
      body = session.html;
      h = session.html.split('@header');
      if (h.length) {
        h = h[1].split('<p>');
        if (h.length) {
          h = h[1].split('</p>');
          head = h[0];
        }
      }
    }
    return this.from(session, body, head);
  },
  from:function(session, body, head) {
    var dos;
    if (session.templateId == '40') {
      dos = session.dateService;
    }
    var rec = {
      body:body,
      head:head,
      //filename:session.cname + "_S" + session.id + '.pdf',
      filename:"S" + session.id + '.pdf',
      author:session.assignedTo || session.createdBy,
      title:session.title,
      dos:dos};
    if (session.cname == null)
      rec.filename = 'Note_' + session.sessionId + '.pdf';
    return this.revive(rec);
  }
})
Pdf_Immun = HtmlPdfDoc.extend({
  //
  from:function(fs, div) {
    var rec = {
      head:this.buildHead(fs),
      body:this.buildBody(div),
      filename:'Immunization.pdf',
      style:'TD {border:1px solid black; text-align:center;} TD.immcat {font-weight:bold; text-align:left; background-color:#efefef} TD.immname {text-align:right} A {color:black; text-decoration:none;} DIV#head {text-align:center}'};
    return this.revive(rec);
  },
  buildHead:function(fs) {
    var ug = me.User.UserGroup;
    var uga = ug.Address;
    var a = ['<big><b>IMMUNIZATION CERTIFICATE</b></big>'];
    a.push(ug.name);
    a.pushIfNotNull(this.buildAddr(uga));
    a.push('Date Printed: ' + DateUi.getToday());
    a.push('<br><big><b>' + fs.client.name + ' &bull; Date of Birth: ' + fs.client.birth + '</b></big>');
    return a.join('<BR>'); 
  },
  buildAddr:function(uga) {
    var a = [];
    a.pushIfNotNull(uga.addr1);
    a.pushIfNotNull(uga.addr2);
    a.pushIfNotNull(uga.csz);
    a.pushIfNotNull(uga.phone1);
    if (a.length)
      return a.join(' &bull; ');
  },
  buildBody:function(div) {
    var a = [div.innerHTML];
    a.push('');
    a.push('');
    a.push('_____________________________________________________');
    a.push(me.User.UserGroup.name);
    return a.join('<BR>');
  }
})
Pdf_Vitals = HtmlPdfDoc.extend({
  //
  from:function(fs, div) {
    var rec = {
      head:this.buildHead(fs),
      body:this.buildBody(div),
      filename:'Vitals.pdf',
      style:'TD {border:1px solid black;text-align:left;} A {color:black; text-decoration:none;}'};
    return this.revive(rec);
  },
  buildHead:function(fs) {
    var a = [];
    var ug = me.User.UserGroup;
    var uga = ug.Address;
    a.push(fs.client.name);
    a.push('Date of Birth: ' + fs.client.birth);
    a.push(ug.name);
    a.push('Date Printed: ' + DateUi.getToday());
    return a.join('<BR>'); 
  },
  buildBody:function(div) {
    return div.innerHTML;
  }
})