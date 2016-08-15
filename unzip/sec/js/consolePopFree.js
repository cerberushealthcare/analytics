var freeTrigger;

// Show free textpopup
function showFreePop(id) {
  event.returnValue = false;
  if (session.closed) return;
  freeTrigger = $(id + 'ft');
  //var cap = "Insert Free Text";
  var html;
  
  if (freeTrigger.className != "ftd") { 
    
    // Popup triggered by existing free text (update)
    html = freeTrigger.innerHTML;
    //showFreetext(freeDoOk, freeTrigger.innerText, cap, true);
    
  } else {
  
    // Popup triggered by "free" link (add)
    html = null;
    //showFreetext(freeDoOk, "", cap);
  }
  FreeTextPop.pop(session.id, html, function(html) {
    setFreeText(freeTrigger.id, html);
  })
}
function freePopText(span) {
  var a = span.childNodes[0];
  if (a.className == "ftd") {
    return "";
  }
  return a.innerText + "  ";
}
function setFreeText(id, text, noAutosave) {
  pushAction("setFreeText('" + id + "','" + esc(text) + "')", "Set Free Text", noAutosave);
  var a = $(id);
  if (text != "") {
    //a.innerHTML = text;  // can't do this, forced to do below, because IE is stupid
    var div = Html.Div.create().html(text);
    if (div.children.length) {
      a.innerHTML = '';
      while (div.children.length) {      
        a.appendChild(div.children[0]);
      }
    } else {
      a.innerText = div.innerText;
    }
    a.className = "";
    a.parentElement.style.display = "inline";
  } else {
    a.innerHTML = "";
    a.className = "ftd";
  }
}
function freeDoOk(text) {
  if (text) {
    setFreeText(freeTrigger.id, text);
  } else {
    freeDoDelete();
  }
}
function freeDoDelete() {
  setFreeText(freeTrigger.id, "");
}
//
FreeTextPop = {
  //
  pop:function(sid, html, /*fn(html)*/onupdate) {
    return Html.Pop.singleton_pop.apply(FreeTextPop, arguments);
  },
  create:function() {
    return Html.DirtyPop.create('Free Text').extend(function(self) {
      return {
        init:function() {
          self.Rtb = Rtb_Upload.create(self.content);
          self.Cmd = Html.CmdBar.create(self.content)
            .ok(self.save)
            .del(self.close_asSaved.curry('').confirm('clear this free text'))
            .cancel(self.close);
        },
        onshow:function(sid, html, onupdate) {
          self.sid = sid;
          self.html = String.trim(html);
          self.onupdate = onupdate;
          self.Cmd.showDelIf(self.html != '');
          self.Rtb.load(sid, html).setHeight(400).setFocus();
        },
        //
        isDirty:function() {
          return self.Rtb.getHtml() != self.html;
        },
        save:function() {
          self.close_asSaved(self.Rtb.getHtml());
        },
        onsave:function(html) {
          self.onupdate(html);
        }
      }
    })
  }
}
Rtb_Upload = {
  create:function(container) {
    return Html.TinyMce.create(container, null, null, Rtb_Upload.Opts).extend(function(self) {
      return {
        init:function() {
          self.initMce(Rtb_Upload.Opts);
        },
        mce_onsetup:function(mce) {
          self.addButton('upload', 'Upload Image', 'img/icons/uploadsm.png', function() {
            ImgUploadPop.pop(self.sid, function(upload) {
              mce.selection.setContent(upload.asHtml());
              mce.focus();
            })
          }) 
        },
        mce_onshow:function(mce) {
          self.setTooltip('image', 'Edit Image');
        },
        load:function(sid, html) {
          self.sid = sid;
          self.setHtml(html);
          return self;
        },
        getHtml:self.getHtml.extend(function(_getHtml) {
          var html = _getHtml();
          var pars = html.split('<p>');
          if (pars.length == 2 && html.beginsWith('<p>') && ! html.contains('<img'))
            return pars[1].split('</p>')[0];
          else
            return html;
        })
      }
    })
  },
  Opts:function(id) {
    var opts = Html.TinyMce.DefaultOpts(id);
    opts.theme_advanced_buttons1 = "bold,italic,underline,strikethrough,|,bullist,numlist,|,outdent,indent,blockquote,|,forecolor,backcolor,|,cut,copy,paste,|,search,replace,|,undo,redo,|,fullscreen",
    opts.theme_advanced_buttons2 = "tablecontrols,|,upload,image";
    return opts;
  }
}
//
ImgUploadPop = {
  //
  pop:function(sid, /*fn(UploadImg)*/callback) {
    return Html.Pop.singleton_pop.apply(this, arguments);
  },
  create:function() {
    var vars = {
      'action':'uploadSessionImage',
      'sid':null};
    return Html.UploadPop.create('Upload Image', vars).extend(function(self) {
      return {
        onpop:function(sid, callback) {
          self.callback = callback;
          self.form.setValue('sid', sid);
        },
        oncomplete:function(upload) {
          self.callback && self.callback(UploadImg.revive(upload));
        }
      }
    })
  }
}
//
UploadImg = Object.Rec.extend({
  /*
  width
  height
  src
  ratio
  */
  MAX:200,
  onload:function() {
    this._width = this.MAX;
    this._height = this._width * this.ratio;
  },
  asHtml:function() {
    return '<p><img src="' + this.src + '" alt="' + this.name + '" height=' + this._height + ' width=' + this._width + '></p>'; 
  }
})
