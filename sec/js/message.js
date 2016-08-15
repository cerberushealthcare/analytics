/**
 * Page (singleton)
 * - mtid: thread ID
 * - dao: MsgDao lists (priorities, recips, sections) 
 */
var page;
function MessagePage(mtid, dao) {
  page = this;
  page.working(true);
  page.newPostPanel = new NewPostPanel(dao.recips, dao.sections);
  page.postsPanel = new PostsPanel();
  page.clientPanel = new ClientPanel();
  page.sending = hide('send-post');
  page.getThread(mtid);
}
MessagePage.prototype = {
  thread:null,
  isWorking:null,
  reset:function() {
    page.thread = null;
    page.newPostPanel.reset();
    page.setTitle();
  },
  getThread:function(mtid) {
    page.reset();
    page.working(true);
    sendRequest(7, 'action=getThread&id=' + mtid);
  },
  getThreadCallback:function(thread) {
    if (thread == null) {
      window.location.href = 'messages.php';
      return;
    }
    page.thread = thread;
    page.setTitle(thread.subject);
    page.clientPanel.load(thread);
    page.newPostPanel.defaultRecips(thread);
    page.postsPanel.load(thread);
    page.showNewPostPanel(thread.posts.length == 0);
    page.working(false);
  },
  newPost:function() {
    page.showNewPostPanel(true);
  },
  sendPost:function() {
    try {
      var post = page.newPostPanel.getRecord();
      page.showNewPostPanel(false);
      hide('post-reply');
      show_(page.sending).innerHTML = post.html;
      overlayWorking(true, page.sending);
      post.id = page.thread.mtid;
      postRequest(7, 'action=reply&obj=' + jsonUrl(post));
    } catch (e) {
      page.showError(e);
    }
  },
  postCallback:function(thread) {
    overlayWorking(false);
    hide_(page.sending).innerHTML = '';
    page.working(true);
    page.getThreadCallback(thread);
  },
  cancelPost:function() {
    page.showNewPostPanel(false);
  },
  showError:function(e) {
    var msg = e.message;
    if (msg == NewPostPanel.ERR_NO_RECIPS) {
      msg = 'No recipient(s) are selected.';
    } else if (msg == NewPostPanel.ERR_NO_BODY) {
      msg = 'Message cannot be blank.';
    }
    showErrorMsg(msg, null, true);
  },
  showNewPostPanel:function(on) {
    if (on) {
      page.newPostPanel.show(true);
      hide('post-reply');
    } else {
      page.newPostPanel.show(false);
      show('post-reply');
    }
  },
  setTitle:function(text) {
    if (text) {
      setText('h2', text);
    } else {
      setHtml('h2', '&nbsp');
    }
  },
  working:function(on) {
    if (on != page.isWorking) {
      if (on) {
        hide('thread');
        overlayWorking(true, show('message-working'));
      } else {
        overlayWorking(false);
        hide('message-working');
        show('thread');
      }
    }
  }
}
/**
 * Posts Panel
 */
function PostsPanel() {
  this.div = $('posts');
}
PostsPanel.prototype = {
  authors:null,
  load:function(thread) {
    this.authors = [];
    clearChildren(this.div);
    for (var i = 0; i < thread.posts.length; i++) {
      this.div.appendChild(this._createPost(thread.posts[i]));
    }
  },
  _createPost:function(post) {
    var table = createTable();
    var tr = appendTr(table);
    var th = createTh(null, 'r' + this._getAuthorIx(post));
    th.appendChild(createDiv(null, 'time', post.date));
    th.appendChild(createDiv(null, null, post.author));
    th.appendChild(createSpan(null, post.sendTo.split(';').join(', ')));
    tr.appendChild(th);
    tr = appendTr(table);
    tr.appendChild(createTdHtml(post.body));
    var div = createDivAppend((post.isUnread) ? 'post unread' : 'post', null, table);
    return div;
  },
  _getAuthorIx:function(post) {
    for (var i = 0; i < this.authors.length; i++) {
      if (this.authors[i] == post.author) {
        return i;
      }
    }
    this.authors.push(post.author);
    return i;
  }
}
/**
 * New Post Panel
 */
function NewPostPanel(recips, sections) {
  this.entryForm = loadEntryForm(recips);
  this.templateWindows = new TemplateWindows(sections);
  this.free = $('post-free');
  function loadEntryForm(recips) {
    var at = new AnchorTab('Select Recipient(s)', 'recips');
    at.loadChecks(recips, 'id', 'name');
    at.appendCmd();
    var ef = new EntryForm($('new-post-ul'));
    ef.addLi();
    ef.appendAnchorTab('to', at, 'Send To:', 'new-post', 450);
    return ef;
  }
}
NewPostPanel.prototype = {
  reset:function() {
    this.entryForm.reset();
    this.templateWindows.reset();
    this.free.value = '';
    this.recipsDefaulted = false;
  },
  defaultRecips:function(thread) {
    if (thread.posts.length > 0) {
      var id = thread.posts[0].authorId;
      if (id != me.id) {
        this.entryForm.setValue('to', [id]);
        this.recipsDefaulted = true;
      }      
    } else {
      this.recipsDefaulted = false;
    }
  },
  /*
   * Returns {
   *   'to':[id,..],
   *   'data':"[{'pid':pid,'syncs':syncs},..]",  // serialized; see TemplateUi.getSyncValues
   *   'html':html  
   *   }
   */
  getRecord:function() {
    var rec = this.entryForm.getRecord();
    var dataOut = this.templateWindows.getDataOut();
    var freeText = value_(this.free);
    if (freeText != '') {
      dataOut.out.push(freeText);
    } 
    if (rec.to.length == 0) {
      throw new Error(NewPostPanel.ERR_NO_RECIPS);
    }
    if (dataOut.out.length == 0) {
      throw new Error(NewPostPanel.ERR_NO_BODY);
    }
    var html = '<p>' + dataOut.out.join('</p><p>') + '</p>';
    return {
      'to':rec.to,
      'data':dataOut.data,
      'html':html};
  },
  show:function(on) {
    showIf(on, 'new-post');
    if (on) {
      focus('post-free');
    }
//    if (on && this.recipsDefaulted) {
//      this.entryForm.getField('to').pop();
//      this.recipsDefaulted = false;
//    }
  }
}
NewPostPanel.ERR_NO_BODY = 'NewPostPanel.ERR_NO_BODY';
NewPostPanel.ERR_NO_RECIPS = 'NewPostPanel.ERR_NO_RECIPS';
/**
 * Template Windows
 */
function TemplateWindows(sections) {
  this.sections = sections;
  this.container = $('templates');
  this.atabs = $('tchooser-atabs');
  this.div = $('tuis');
  this.pars = {};  // {pid:{'desc':pdesc,'pi':JParInfo,'tuis':[tuis,..]},..}
  this.loadTemplateChooser();
  this.reset();
}
TemplateWindows.prototype = {
  reset:function() {
    clearChildren(this.div);
  },
  /*
   * Returns {
   *   'data':"[{'pid':pid,'syncs':syncs},..]",  // serialized; see TemplateUi.getSyncValues
   *   'out':[html,..]  // see TemplateUi.out  
   *   }
   */
  getDataOut:function() {
    var dataOut = {
      'data':null,
      'out':[]};
    var windows = this.div.children;
    if (windows.length > 0) {
      var data = [];
      var out = [];
      for (var i = 0; i < windows.length; i++) {
        var w = windows[i];
        data.push({
          'pid':w.pid,
          'syncs':w.tui.getSyncValues(true)});
        out.push(w.tui.out());
      }
      dataOut.data = toJSONString(data);
      dataOut.out = out;
    }
    return dataOut;
  },
  loadTemplateChooser:function() {
    var self = this;
    for (var sid in this.sections) {
      s = this.sections[sid];
      var at = new AnchorTab(s.name, 'templates');
      at.loadChecks(s.pars, null, null, AnchorTab.SEL_TEXT_AS_NONE);
      at.appendCmd(null, function(atab){self.templateOk(atab)}, 'Insert'); 
      this.atabs.appendChild(at.anchor);
      for (var pid in s.pars) {
        this.pars[pid] = {'desc':s.pars[pid]};
      }
    }
  },
  templateOk:function(atab) {
    var pids = atab.getValue();
    atab.resetChecks();
    this.add(pids);
  },
  add:function(pids) {
    for (var i = 0; i < pids.length; i++) {
      var pid = pids[i];
      this.div.appendChild(this.createTuiWindow(pid));
    }
  },
  createTuiWindow:function(pid) {
    var par = this.pars[pid];
    var window = createDiv(null, 'post-entry');
    window.appendChild(this.createTuiCap(par));
    var tui = new TemplateUi(null, null, pid);
    window.appendChild(tui.doc);
    window.tui = tui;
    window.pid = pid;
    return window;
  },
  createTuiCap:function(par) {
    var self = this;
    var t = createTable();
    var tr = appendTr(t);
    tr.appendChild(createTh(par.desc));
    var a = createAnchor(null, null, null, 'X', null, function(){self.closeTui(this)});  
    tr.appendChild(createTdAppend(null, a));
    return createDivAppend('pcap', null, t);
  },
  closeTui:function(a) {
    var div = findAncestorWith(a, 'className', 'post-entry');
    showConfirmDelete(this.deleteTui, null, 'template section', div);
  },
  deleteTui:function(confirmed, div) {
    if (confirmed) {
      TemplateUi.clearInstance(div.tui);
      deflate(div);
    }
  }
}
/**
 * ClientPanel
 */
function ClientPanel() {
}
ClientPanel.prototype = {
  client:null,  
  load:function(t) {
    this.t = t;
    var c;
    if (t.cid) {
      c = t.facesheet.client;
      show('td-client');
      setText('h2-client', c.name).className = c.sex;
      setText('client-uid', c.uid);
      setText('client-dob', c.birth + ' (' + c.age + ')');
      setHtml('allergies', this._joinData(t.facesheet.allergies, 'agent', ' &bull; '));
      setHtml('meds', this._joinData(t.facesheet.activeMeds, 'name', '<br>'));
      this._formatVitals(t.facesheet.vitals);
      clearChildren($('client-links')).appendChild(createAnchor(null, 'face.php?id=' + c.id, 'gogo', 'Patient Facesheet'));
      loadMedHistory(t.facesheet.meds);
    } else {
      hide('td-client');
    }
    this.client = c;
  },
  _formatVitals:function(vitals) {
    var v;
    for (var d in vitals) {
      v = vitals[d];
      break; 
    }
    if (v && v.all) {
      setText('h3-vitals', 'Vitals (' + v.dateText.substr(0, 6) + ')');
      setHtml('vitals', bulletJoin(v.all));
    } else {
      setText('h3-vitals', 'Vitals');
      setHtml('vitals', '');
    }
  },
  _joinData:function(a, field, glue) {
    var v = [];
    for (var i = 0; i < a.length; i++) {
      v.push(a[i][field]);
    }
    return v.join(glue);
  }
}


function loadThread(t) {
  thread = t;
  thread.authors = [];  // for coloring posts
  return;
  var dt = setHtml('div-posts', '');
  for (var i = 0; i < thread.posts.length; i++) {
    dt.appendChild(buildPost(thread.posts[i]));
  }
}

function send() {
  var dataOut = templateWindows.getDataOut();
  
}
function outTuiWndows() {
  var out = {};
  var windows = $('tuis').children;
  for (var i = 0; i < windows.length; i++) {
    var tui = windows[i].tui;
    tui.doc.innerHTML = tui.getSyncValues();
  }
}
function newPost() {
  hide('post-reply');
  loadPostEntryForm();
  if (templateWindows == null) {
    templateWindows = new TemplateWindows(sections);
  }
}
function buildPost(post) {
  var t = [];
  t.push("<table><tr><th class='r");
  t.push(getAuthorIx(post));
  t.push("><div class='time'>");
  t.push(post.date);
  t.push('</div><div>');
  t.push(post.author);
  t.push('<span>');
  t.push(post.sendTo.split(';').join(', '));
  t.push('</span></div></th></tr><tr><td>');
  t.push(post.body);
  t.push('</td></tr></table>');
  var div = createDiv(null, ((post.isUnread) ? 'post unread' : 'post'), null, t.join(''));
  return div;
}
function getAuthorIx(post) {
  for (var i = 0; i < thread.authors.length; i++) {
    if (thread.authors[i] == post.author) {
      return i;
    }
  }
  thread.authors.push(post.author);
  return i;
}
function reply() {
  var text = value('ta');
  if (text == '') {
    showErrorMsg('There is no reply to send.', null, true);
    return;
  } else {
    text = esc(crlfToBr(text));
  }
  var reply = {
    id:thread.mtid,
    read:thread.readCt,
    text:text};
  overlayWorking(true, $('box'));
  postRequest(7, 'action=reply&obj=' + jsonUrl(reply));
}
function replyCallback(t) {
  loadThread(t);
  overlayWorking(false);
}
function send2() {
  var text = value('new');
  var subj = value('subject', '(No Subject)');
  var recips = getCheckedValues('recips', 'recips-span');
  var priority = getCheckedValues('priority', 'priority-span')[0];
  if (recips.length == 0) {
    showErrorMsg('No recipient(s) were selected.', null, true);
    return;
  }
  if (text == '') {
    showErrorMsg('Message cannot be blank.', null, true);
    return;
  }
  var message = {
    subj:subj,
    recips:recips,
    priority:priority,
    text:text};
  overlayWorking(true, $('box'));
  postRequest(7, 'action=send&obj=' + jsonUrl(message));
}
function sendCallback(mtid) {
  overlayWorking(false);
  getThread(mtid);
}