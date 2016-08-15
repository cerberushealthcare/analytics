/**
 * Page Functions
 * @author Warren Hornsby
 */
var Page = {
  //
  PAGE_CONSOLE:'new-console.php',
  PAGE_ERX_PHARM:'erxpharm.php',
  PAGE_ERX_STATUS:'erxstatus.php',
  PAGE_FACESHEET:'face.php',
  PAGE_NEWCROP:'newcrop.php',
  PAGE_TRACKING:'tracking.php',
  PAGE_VCHART:'vchart.php',
  PAGE_PRINT_POP:'print-pop.php',
  //
  HIDE_MENU:true,
  //
  sessid:null,  // PHP session ID for API navigation
  //
  _wc:null,     // active working cmd
  _wfb:null,    // window focus/blur
  _pops:{},   // pop window refs
  //
  browser:{
    isMsie:function() {
      return me && me.ie > 0;
    },
    isMsie6:function() {
      return me && me.ie == 6;
    }
  },
  /*
   * Navigate to url
   * - args: optional query strings {name:value,..} or single string value to supply to 'id'
   */
  go:function(page, args) {
    Html && Html.Window.working(true);
    setTimeout(function(){window.location.href = Page.url(page, args)},1);
  },
  /*
   * Navigate to url as popup
   * - args: optional query strings {name:value,..} or single string value to supply to 'id'
   * - hideMenu: default false
   * - name: optional window name
   * - callback: optional, to call when parent window receives focus after pop closed
   * - callbackStillOpen(p): optional, to call when parent window recieves focus but pop is still open
   *                         can do a p.focus() in callback
   */
  pop:function(page, args, hideMenu, name, callback, callbackStillOpen) {
    if (name == null)
      name = (Math.random() + '').substr(2);
    var height = (hideMenu) ? screen.availHeight - 100 : screen.availHeight - 150;
    var width = screen.availWidth - 100;
    var top = 50;
    var left = 50;
    if (page == 'new-console.php') {
      height = screen.availHeight;
      width = screen.availWidth;
      top = 0;
      left = 0;
    }
    if (name == 'PopBuilder') {
      height = 330;
      width = 600;
    }
    var menu = (hideMenu) ? 'toolbar=0,menubar=0' : 'toolbar=1,menubar=1';
    Page._pops[name] = window.open(Page.url(page, args), name, 'height=' + height + ',width=' + width + ',top=' + top + ',left=' + left + ',resizable=1,location=0,scrollbars=1' + menu);
    if (Page._pops[name] == null) {
      Pop.Msg.showCritical("Your browser's popup blocker prevented showing the requested window.");
    } else {
      if (callback) {
        var onfocus = Html.Window.getOnFocus();
        Html.Window.setOnFocus(function() {
          if (onfocus)
            onfocus();
          Html.Window.setOnFocus(onfocus);
          pause(0.1, function() {
            var p = Page._pops[name];
            if (p == null || p.closed) {
              delete Page._pops[name];
              callback();
            }
          })
        })
        /*
        if (! Html.Window.isTablet()) {
          var onfocus;
          Page.attachEvent('focus', onfocus = function() {
            pause(0.5, function() {
              var p = Page._pops[name];
              if (p == null || p.closed) {
                delete Page._pops[name];
                callback();
                Page.detachEvent('focus', arguments.callee);
              } else {
                if (callbackStillOpen)
                  callbackStillOpen(p);
              }
            })
          })
        } else if (window.page) {
          var fid = window.page.onFocus ? 'onFocus' : 'onfocus';
          var onfocus = window.page[fid];
          window.page[fid] = function() {
            if (onfocus)
              onfocus();
            pause(0.5, function() {
              var p = Page._pops[name];
              if (p == null || p.closed) {
                delete Page._pops[name];
                callback();
                window.page[fid] = onfocus;
              }
            })
          }
        }
        */
      }
    }
  },
  popPortalPrint:function(cid) {
    this.pop('print-portal-login.php', cid, true, 'ppp');
  },
  popInfoButton:function(url) {
    this.pop(url, null, true, 'info');
  },
  popCcdPrint:function(file, cid, demoOnly, visit) {
    //this.pop('print-ccd.php', {'id':file.filename,'cid':cid}, true, 'pcp');
    Ajax.Ccd.print(file.filename, cid, demoOnly, visit);
  },
  popConsole:function(sid, callback) {
    this.pop(Page.PAGE_CONSOLE, {'sid':sid}, true, 'X' + sid, callback);
  },
  popLabPdf:function(id) {
    this.pop('hl7inbox-pdf.php', {id:id}, true);
  },
  popFace:function(cid, callback) {
    this.pop(Page.PAGE_FACESHEET, {'id':cid,'pop':1}, true, 'facepop', callback);
  },
  focus:function(e) {
    if (e.setFocus)
      e.setFocus();
    else
      try {
        e.focus();
        e.select();
      } catch(ex) {
      }
  },
  /*
   * - fn: If provided, start overlay working and make async call
   *       If null, overlay working off
   */
  work:function(fn) {
    if (fn) {
      Html.Window.working(true);
      setTimeout(fn, 1);
    } else {
      Html.Window.working(false);
    }
  },
  /*
   * Returns page?name=value&..
   * - args: optional query strings {name:value,..} or single string value to supply to 'id'
   */
  url:function(page, args) {
    var u = page;
    if (page.indexOf('?') == -1)
      u += "?";
    var a = [];
    if (args)
      if (Object.is(args))
        for (var name in args) {
          if (args[name])
            a.push(name + '=' + encodeURIComponent(args[name]));
          else
            a.push(name + '=');
        }
      else
        a.push('id=' + args);
    if (Page.sessid)
      a.push('sess=' + Page.sessid);
    if (page != Page.PAGE_FACESHEET)
      a.push(Math.random());
    return u + a.join('&');
  },
  /*
   * Builds error with Clicktate number plus optional originating error (e)
   */
  error:function(number, description, e) {
    var err = new Error(number, description);
    err.e = e;
    return err;
  },
  show:function(e, on) {
    e.style.display = (on) ? 'block' : 'none';
  },
  visible:function(e, on) {
    e.style.visibility = (on) ? 'visible' : 'hidden';
  },
  showError:function(e) {
    Pop.Msg.showCritical(e.message);
  },
  showErrorDetails:function(text, errorMsg) {
    Pop.Msg.showCritical(text + '<br><br><b>Details</b><br>' + errorMsg);
  },
  showAjaxError:function(e) {
    // Page.lastAjaxError = e;  todo?
    Html.Window.working(false);
    Page.workingCmd(false);
    Html.Window.clearWorking();
    if (e.type == 'RestrictedChartException')
      BreakGlassPop.pop(e.message);
    else
      Pop.Msg.showCritical(e.message);
  },
  workingCmd:function(on) {
    var a;
    if (on) {
      if (Page._wc == null && event && event.srcElement) {
        a = event.srcElement;
        a.style.borderColor = '#c0c0c0';
        a.style.color = '#f8f8f8';
        a.style.backgroundColor = '#f8f8f8';
        a.style.backgroundImage = 'url(img/icons/working6.gif)';
        a.style.backgroundPositionX = 'center';
        a.style.backgroundPositionY = 'center';
        a.style.backgroundRepeat = 'no-repeat';
        Page._wc = a;
      }
    } else {
      if (Page._wc) {
        a = Page._wc;
        a.style.borderColor = '';
        a.style.color = '';
        a.style.backgroundColor = '';
        a.style.backgroundImage = '';
        a.style.backgroundPositionX = '';
        a.style.backgroundPositionY = '';
        a.style.backgroundRepeat = '';
        Page._wc = null;
      }
    }
  },
  sessionTimeout:function() {
    window.location.href = 'index.php?timeout=1';
  },
  setTitle:function(title) {
    document.title = title + ' \u2022 Clicktate';
  },
  /*
   * Set page handlers to:
   * Disallow select (except for input/textarea and those with 'selectable=1' prop)
   * Close pops on ESC keypress
   * Delegate window focus/blur to page.onFocus() and page.onBlur() if they exist
   */
  setEvents:function() {
    document.onselectstart = function() {
      var e = event.srcElement;
      return (e && (e.tagName == "INPUT" || e.tagName == "TEXTAREA" || e.selectable == "1"));
    };
    if (window.Pop) {
      //document.onmousedown = Pop._closeByControlBox;
      //document.ontouchend = Pop._closeByControlBox;
      //document.onkeydown = Pop._closeByEventKey;
      Html.Window.attachEvent('resize', Pop._sizeCurtain);
    } else {
      document.onmousedown = closePopByControlBox;
      document.onkeydown = function() {
        if (event && event.keyCode == 27) {
          Pop.close();
        }
      };
    }
    if (window.page) {
      if (! Html.Window.isTablet()) {
        var onfocus = page.onFocus || page.onfocus;
        var onblur = page.onBlur || page.onblur;
        if (onfocus || onblur) {
          var active = document.activeElement;
          var blurred = false;
          Html.Window.setOnFocus(function() {
            if (blurred) {  
              blurred = false;
              onfocus && onfocus.call(page);
            }
          })
          Html.Window.setOnBlur(function() {
            if (active != document.activeElement) {
              active = document.activeElement;
            } else {
              blurred = true;
              onblur && onblur.call(page);
            }
          })
        }
      } else {  // temp? logic while iPads do not support window.onfocus/onblur
        var fid = page.onFocus ? 'onFocus' : 'onfocus';
        Html.Window.setOnFocus(function() {
          page[fid] && page[fid]();
        })
      }
    }
    if (window.page && page.onresize) {
      window.onresize = page.onresize;
      window.onresize();
    }
  },
  /*
   * Flicker fixed header <tr> on window resize (IE render bug)
   * @arg <e> tr
   */
  addFlickerEvent:function(tr) {
    Page.attachEvent('resize', function() {
      tr.style.display = 'none';
      tr.style.display = '';
    });
  },
  /*
   * Attach an event handler to window
   * @arg event 'focus' (not 'onfocus')
   */
  attachEvent:function(event, fn) {
    Html.Window.attachEvent(event, fn);
  },
  /*
   * Detach an event handler from window
   */
  detachEvent:function(event, fn) {
    Html.Window.detachEvent(event, fn);
  },
  /*
   * Attach a run-once event handler to window
   */
  attachRunOnceEvent:function(event, fn) {
    Html.Window.attachEvent(event, function() {
      fn();
      Html.Window.detachEvent(event, arguments.callee);
    });
  }
};
/**
 * Invocations
 */
Page.Nav = {
  _FACESHEET:'face.php',
  _MESSAGE:'message.php',
  _MESSAGES:'messages.php',
  _PATIENTS:'patients.php',
  _SCHEDULE:'schedule.php',
  _CONSOLE:'new-console.php',
  //
  goPatients:function() {
    Page.go(Page.Nav._PATIENTS);
  },
  goConsole:function(sid) {
    Page.go(Page.Nav._CONSOLE, {'sid':sid});
  },
  goMessage:function(mtid, userId) {
    Page.go(Page.Nav._MESSAGE, {'id':mtid, 'ob':userId});
  },
  goReview:function() {
    Page.go('review.php');
  },
  goTracking:function(pix) {
    Page.go('tracking.php', {'pix':pix || '0'});
  },
  goTracking_sched:function() {
    Page.Nav.goTracking(2);
  },
  goMessageNew:function(cid, stub, portal) {
    var args = {'cid':cid};
    if (portal)
      args.portal = 1;
    if (stub) {
      args.aid = stub.id;
      args.atype = stub.type;
      args.aname = stub.name;
    }
    Page.go(Page.Nav._MESSAGE, args);
  },
  goMessageNewPortal:function(cid, stub) {
    Page.Nav.goMessageNew(cid, stub, 1);
  },
  goMessages:function() {
    Page.go(Page.Nav._MESSAGES);
  },
  goMessagesSent:function() {
    Page.go(Page.Nav._MESSAGES, {'get':'sent'});
  },
  goSched:function() {
    Page.go(Page.Nav._SCHEDULE);
  },
  goSchedDate:function(date) {
    Page.go(Page.Nav._SCHEDULE, {'v':0,'d':date});
  },
  goSchedPop:function(skid) {
    Page.go(Page.Nav._SCHEDULE, {'pop':skid});
  },
  goSchedEdit:function(skid) {
    Page.go(Page.Nav._SCHEDULE, {'pe':1,'pop':skid});
  },
  goSchedNew:function(cid) {
    Page.go(Page.Nav._SCHEDULE, {'v':1,'sid':cid});
  },
  goFacesheet:function(cid) {
    Page.go(Page.Nav._FACESHEET, cid);
  },
  goFaceEditDemo:function(cid) {
    Page.go(Page.Nav._FACESHEET, {'id':cid,'pe':1});
  },
  goDownloadVisit:function(cid, fid) {
    window.location.href = Page.url('serverVisitSummary.php', {'action':'download','cid':cid,'fid':fid});
  },
  goDownloadCcd:function(file, cid, visit) {
    window.location.href = Page.url('serverCcd.php', {'action':'download','id':file.filename,'cid':cid,'visit':visit});
  },
  goDownloadBatchCcda:function(filename) {
    window.location.href = Page.url('serverCcd.php', {'action':'batchDownload','id':filename});
  },
  goDownloadPqri:function(file, from, to, userId) {
    window.location.href = Page.url('serverPqri.php', {'action':'download','id':file.filename,'from':from,'to':to,'userId':userId});
  },
  goDownloadVxu:function(file) {
    window.location.href = Page.url('serverVxu.php', {'action':'download','id':file.filename});
  },
  goDownloadAdt:function(file) {
    window.location.href = Page.url('serverAdt.php', {'action':'download','id':file.filename});
  },
  goWelcome:function() {
    Page.go('welcome.php');
  },
  goLogout:function() {
    Page.go('index.php?logout=Y');
  }
}
/**
 * Tile
 */
function Tile(id) {
  if (id) {
    this.div = _$(id);
  }
}
Tile.prototype = {
  load:function(e) {
    this.reset();
    this.append(e);
  },
  reset:function() {
    Tile.clear(this.div);
  },
  append:function(e) {
    this.div.appendChild(e);
  },
  setText:function(text) {
    this.div.innerText = text;
  },
  setHtml:function(html) {
    this.div.innerHTML = html;
  },
  show:function(on) {
    Page.show(this.div, on);
  },
  working:function(on) {
    for (var i = 0; i < this.div.children.length; i++) {
      Page.visible(this.div.children[i], ! on);
    }
    if (on) {
      this.div.addClass('working-circle');
    } else {
      this.div.removeClass('working-circle');
    }
  }
}
Tile.clear = function(e) {
  _$(e).clean();
}
/**
 * BreakGlassPop
 */
BreakGlassPop = {
  pop:function(cid) {
    var html = '<big><big><big><b class=red>RESTRICTED ACCESS</b></big></big><br><br>The chart you are attempting to access is restricted except in the case of emergency. All access will be logged and reported.<br><br>Do you wish to proceed?<big>';
    Pop.Confirm.showImportant(html, 'Yes', null, 'No', null, false, function() {
      Ajax.Facesheet.Patients.breakGlass(cid, function() {
        Page.Nav.goFacesheet(cid);
      });
    });
  }
}
