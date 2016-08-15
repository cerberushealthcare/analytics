/*Pop*/DocStubPreviewPop = {
  //
  pop:function(/*DocStub*/rec/*=preview*/, /*DocStub[]*/recs/*=navbar*/, width/*=1000*/) {
    if (! Array.is(recs))
      recs = null;
    if (width) 
      return DocStubPreviewPop.create(width, true).pop(rec, recs);
    else
      return Html.Pop.singleton_pop.apply(DocStubPreviewPop, arguments);
  },
  pop_asOverlay:function(rec) {
    DocStubPreviewPop.pop(rec, null, 700);
  },
  setOnUpdate:function(onupdate) {
    DocStubPreviewPop.onupdate = onupdate;
  },
  create:function(width, noCmdBar) {
    noCmdBar = null;
    var My = this;
    return Html.Pop.create('Documentation Preview', 800).extend(function(self) {
      return {
        onupdate:function() {}, /*fired on close*/
        onreview:function() {}, /*fired immediately*/
        //
        init:function() {
          var height = self.fullscreen(width || 1000, 600) - 20;
          self.content.addClass('dsp');
          self.navbar = My.NavBar.create(self.content)
            .bubble('onselect', self.navbar_onselect)
            .bubble('onfacepop', self.navbar_onfacepop);
          self.viewer = My.Viewer.create(self.content, height, noCmdBar)
            .bubble('onexit', self.viewer_onclose)
            .bubble('onupdate', self.viewer_onupdate)
            .bubble('onpreview', self.viewer_onpreview)
            .bubble('onreview', self);
        },
        reset:function() {
          self.updated = false;
          self.viewer.reset();
          self.navbar.reset();
        },
        pop:function(rec, recs) {
          self.reset();
          self.show();
          async(function() {
            self.navbar.load(recs, rec);
          })
          if (DocStubPreviewPop.onupdate)
            self.onupdate = DocStubPreviewPop.onupdate;
          return self;
        },
        nav:function(rec) {
          self.navbar.draw(rec);
          self.viewer.load(rec);
        },
        onclose:function() {
          if (self.updated) 
            self.onupdate();
        },
        getNext:function() {
          return self.navbar.nextbox.rec;
        },
        //
        navbar_onselect:function(rec) {
          self.viewer.load(rec);
        },
        navbar_onfacepop:function() {
          self.viewer.reload();
        },
        viewer_onupdate:function(stub) {
          self.updated = true;
          if (stub)
            self.navbar.refresh(stub);
          else
            self.close();
        },
        viewer_onpreview:function(stub) {
          self.navbar.refresh(stub);
        },
        viewer_onclose:function() {
          self.close();
        }
      }
    })
  },
  Viewer:{
    create:function(container, height, noCmdBar) {
      var self = Html.Tile.create(container, 'Viewer');
      return self.aug({
        onexit:function() {},
        onpreview:function(rec) {},
        onupdate:function(rec) {},
        //
        init:function() {
          self.spacer = DocView.asSpacer(self, height);
        },
        load:function(rec) {
          self.reset();
          self.rec = rec;
          self.draw();
        },
        //
        reset:function() {
          if (self.viewing) {
            self.viewing.hide();
          }
          self.viewing = null;
          self.spacer.show();
        },
        draw:function() {
          var view = DocView.from(self.rec);
          self.spacer.hide();
          self.viewing = view.create(self, self.rec, noCmdBar, height)
            .bubble('onexit', self)
            .bubble('onupdate', self)
            .bubble('onpreview', self)
            .bubble('onreview', self);
        },
        reload:function() {
          if (self.viewing && self.viewing.reload)
            self.viewing.reload();
        }
      })
    }
  },
  NavBar:{
    create:function(container) {
      var My = this;
      var self = Html.Tile.create(container, 'NavBar');
      return self.aug({
        onselect:function(rec) {},
        onfacepop:function() {},
        //
        init:function() {
          var Prev = Html.Div.create('nplabel').html('P<br>R<br>E<br>V');
          var Next = Html.Div.create('nplabel').html('N<br>E<br>X<br>T');
          self.prevbox = My.LinkBox.asPrev(Prev).set('onnav', self.prev_onnav);
          self.onbox = My.OnBox.create(self).bubble('onfacepop', self);
          self.nextbox = My.LinkBox.asNext(Next).set('onnav', self.next_onnav);
          Html.Table.create(self, 'w100').tbody()
            .tr()
              .td(Prev).w(15)
              .td(self.prevbox).w(100)
              .td(self.onbox).w('100%')
              .td(self.nextbox).w(100)
              .td(Next).w(15);
        },
        load:function(recs, rec) {
          self.recs = recs;
          self._navify(recs);
          self.draw(rec);
          self.onselect(rec);
        },
        reset:function() {
          self.recs = null;
          self.rec = null;
          self.prevbox.reset();
          self.nextbox.reset();
          self.onbox.reset();
        },
        refresh:function(rec) {
          rec.Client = self.rec.Client || rec.Client;
          if (self.rec._next) {
            rec._next = self.rec._next;
            self.rec._next._prev = rec;
          }
          if (self.rec._prev) {
            rec._prev = self.rec._prev;
            self.rec._prev._next = rec;
          }
          self.draw(rec);
        },
        //
        draw:function(rec) {
          self.rec = rec;
          if (self.recs) {
            self.prevbox.load(rec._next);
            self.nextbox.load(rec._prev);
          }
          self.onbox.load(rec);
        },
        next_onnav:function(rec) {
          self.draw(rec);
          self.onselect(rec);
        },
        prev_onnav:function(rec) {
          self.draw(rec);
          self.onselect(rec);
        },
        _navify:function(recs) {
          var last;
          Array.forEach(recs, function(rec) {
            if (last) {
              last._prev = rec;
              rec._next = last;
            } else {
              rec._next = null;
            }
            last = rec;
          });
        }
      });
    },
    OnBox:{
      create:function(container) {
        var self = Html.Div.create('onbox').into(container);
        return self.aug({
          onfacepop:function() {},
          //
          load:function(rec) {
            self.reset();
            Html.H2.create(self.getHeader(rec)).into(self);
            var title = rec.date && rec._type ? rec.date + ' - ' + rec._type : '';
            Html.Tile.create(self, 'bold2').setText(title);
            Html.Tile.create(self).setText(rec.desc); 
            if (rec.Client) {  
              Html.Tile.create(self).setText(rec.Client.name + ' - DOB: ' + rec.Client._birth); 
              //Html.Tile.create(self).add(AnchorClient_FacesheetPop.create(rec.Client, function() {
              //  self.onfacepop();
              //}))
            }
          },
          reset:function() {
            self.clean();
          },
          getHeader:function(rec) {
            return rec.name; 
          }
        });
      }
    },
    LinkBox:{
      create:function(cls, label) {
        var self = Html.Div.create(cls);
        return self.aug({
          onnav:function(rec) {},
          //
          load:function(rec) {
            self.reset();
            self.rec = rec;
            self.Anchor = self.createAnchor();
            self.addClassIf('empty', self.rec == null);
            self.visibleIf(self.rec);
            label.hide();
            //label.showIf(self.rec);
            return self;
          },
          reset:function() {
            self.clean();
            self.rec = null;
            self.invisible();
            label.hide();
          },
          //
          onclick:function() {
            if (self.rec) 
              self.onnav(self.rec);
          },
          ondblclick:function() {
            self.onclick();
          },
          onmouseover:function() {
            if (self.Anchor) 
              self.addClass('hover');
          },
          onmouseout:function() {
            self.removeClass('hover');
          },
          createAnchor:function() {
            if (self.rec) { 
              var a = AnchorDocStub.create(self.rec).addClass('linkbox').noFocus().into(self);
              a.html(a.getText() + '<br>' + self.rec.date);
              return a;
            }
          }
        });
      },
      asPrev:function(label) {
        return this.create('linkbox prevbox', label);
      },
      asNext:function(label) {
        return this.create('linkbox nextbox', label);
      }
    }
  }
}
/*Tile*/DocView = {
  create:function(container, stub, noCmdBar, height) {
    return Html.Tile.create(container).extend(function(self) {
      return {
        onexit:function() {},
        onupdate:function(stub) {},  // returns null if deleted
        onpreview:function(stub) {},  
        draw:function() {},
        reset:function() {},
        //
        init:function() {
          self.view = Html.Tile.create(self, 'View');
          if (! noCmdBar) {
            self.table = Html.Table2Col.create(self);
            Html.CmdBar.create(self.table.right).exit(self.exit);
            self.table.left.setWidth('100%');
          }
          // self.hide();
          // extenders call self.load(stub)
        },
        //
        load:function(stub, asUpdate) {
          self.stub = stub;
          self.view.clean();
          self.reset();
          if (height) 
            self.setHeight(height);
          if (stub.Preview) {
            self.rec = stub.Preview;
            self.draw();
          } else {
            stub.ajax().preview(function(stub) {
              if (stub) {
                self.stub = stub;
                self.rec = stub.Preview;
                self.draw();
                if (asUpdate)
                  self.onupdate(stub);
                else
                  self.onpreview(stub);
              } else {
                self.onupdate();
                self.exit();
              }
            })
          }
        },
        reload:function() {
          self.stub.resetPreview();
          self.load(self.stub, true);
        },
        noPreview:function() {
          self.view.add(Html.Div.create().add(Html.Label.create(null, 'Preview is not available for this item.')));
        },
        exit:function() {
          self.onexit();
        },
        setHeight:function(i) {
          self.view.setHeight(i);
          return self;
        }
      }
    })
  },
  from:function(stub) {
    switch (stub.type) {
      case C_DocStub.TYPE_SESSION:
        return DocViewSession;
      case C_DocStub.TYPE_MSG:
        return DocViewMsg;
      case C_DocStub.TYPE_APPT:
        return DocViewNa;  //DocViewAppt;
      case C_DocStub.TYPE_ORDER:
        return DocViewNa;  //DocViewOrder;
      case C_DocStub.TYPE_SCAN:
        return DocViewScan;
      case C_DocStub.TYPE_SCAN_XML:
        return DocViewScanXml;
      case C_DocStub.TYPE_RESULT:
        return DocViewResult;
      case C_DocStub.TYPE_VISITSUM:
        return DocViewVisit;
      case C_DocStub.TYPE_CLIENTDOC:
        return DocViewClientDoc;
      case C_DocStub.TYPE_SUPERBILL:
        return DocViewSuperbill;
    }
  },
  asSpacer:function(container, height) {
    return this.create(container).setHeight(height).show();
  }
}
/*DocView*/DocViewNa = {
  create:function(container, stub, noCmdBar, height) {
    return DocView.create(container, stub, noCmdBar, height).extend(function(self) {
      return {
        init:function() {
          self.setHeight(height);
        }
      }
    })
  }
}
/*AnchorAction*/EditHead = Object.extend(Html.AnchorAction, {
  create:function(cls, text, onclick) {
    return Html.Table2ColHead.create(null,
      Html.AnchorAction.create(cls, text, onclick).addClass('EditHead'),
      Html.AnchorAction.asEdit('Edit', onclick));
  }
})
/*DocView*/DocViewSession = {
  create:function(container, stub, noCmdBar, height) {
    return DocView.create(container, stub, noCmdBar, height).extend(function(self) {
      return {
        init:function() {
          self.view.addClass('ViewSession');
          if (self.table) {
            self.cb = Html.CmdBar.create(self.table.left)
              //.button('Open in Console', self.open_onclick, 'note')
              .button('Replicate...', self.replicate_onclick, 'copy-note')
              .print(self.print_onclick)
              .button('Send to Office', self.sendoffice_onclick, 'message')
              .button('Send to Portal', self.send_onclick, 'message')
              .button('Unlock', self.unlock_onclick.confirm('unsign and unlock'));
            self.send = self.cb.get('Send to Portal').hide();
            self.sendoffice = self.cb.get('Send to Office').hide();
            self.print = self.cb.get('Print').hide();
            self.unlock = self.cb.get('Unlock').hide();
          }
          self.load(stub);
        },
        draw:function() {
          EditHead.asNote(self.rec.label, self.open_onclick).into(self.view);
          var body; 
          if (self.rec._html) {
            body = Html.Div.create();
            self.view.add(body.html(self.rec._html));
          } else {
            self.noPreview();
          }
          if (self.print)
            self.print.showIf(self.rec.closedBy);
          if (self.send)
            self.send.showIf(self.rec.closedBy);
          if (self.sendoffice)
            self.sendoffice.showIf(self.rec.closedBy);
          if (self.unlock && me.Role.Artifact && me.Role.Artifact.noteSign)
            self.unlock.showIf(self.rec.closedBy);
          if (body && self.rec.stubId) {
            var stub = DocStub.revive({
              id:self.rec.stubId, type:String.toInt(self.rec.stubType), name:self.rec.stubName
            })
            Html.Tile.create(body).add(AnchorDocStub_Preview.create(stub));
          }
        },
        open_onclick:function() {
          Page.popConsole(self.rec.sessionId, self.reload);
        },
        unlock_onclick:function() {
          self.working(true);
          Ajax.get('Session', 'unsign', self.rec.sessionId, function() {
            self.working(false);
            self.reload();
            self.open_onclick();
          })
        },
        sendoffice_onclick:function() {
          Page.Nav.goMessageNew(self.rec.clientId, stub);
        },
        send_onclick:function() {
          Page.Nav.goMessageNewPortal(self.rec.clientId, stub);
        },
        print_onclick:function() {
          self.rec.html = self.rec._html;
          Pdf_Session.fromClosed(self.rec).download();
        },
        replicate_onclick:function() {
          if (! me.Role.Artifact || ! me.Role.Artifact.noteCreate)
            return;
          self.rec.cid = self.rec.clientId;
          self.rec.tid = self.rec.templateId;
          self.rec.id = self.rec.sessionId;
          Includer.getDocOpener_replicate(self.rec, self.reload);
        }
      }
    })
  }
}
/*DocView*/DocViewMsg = {
  create:function(container, stub, noCmdBar, height) {
    var My = this;
    return DocView.create(container, stub, noCmdBar, height).extend(function(self) {
      return {
        init:function() {
          self.view.addClass('ViewMsg');
          if (self.table)
            self.cb = Html.CmdBar.create(self.table.left).print(self.print_onclick);
          self.load(stub);
        },
        draw:function() {
          var maxWidth = container.getWidth() - 40;
          EditHead.asMsg(self.stub.name, self.open_onclick).into(self.view);
          if (self.rec.MsgPosts) {
            self.rec.MsgPosts.each(function(post) {
              My.Post.create(post, maxWidth).into(self.view);
            })
          } else {
            self.noPreview();
          }
          /*
          if (self.rec._html)
            self.view.add(Html.Div.create().html(self.rec._html));
          else
            self.noPreview();
          */
        },
        open_onclick:function() {
          Page.Nav.goMessage(self.rec.threadId);
        },
        print_onclick:function() {
          Html.ServerForm.submit(Ajax.SVR_MSG, 'download', {'id':self.rec.threadId});          
        }
      }
    })
  },
  Post:{
    create:function(post, maxWidth) {
      var self = Html.Div.create();
      var tile = Html.Tile.create(self, 'posthead');
      if (post.action == C_MsgPost.ACTION_CLOSE) {
        tile.add(Html.Label.create(null, 'Closed By:'))
          .add(Html.Span.create(null, post.author));
      } else {
        tile.add(Html.Label.create(null, 'From:'))
        .add(Html.Span.create(null, post.author))
        .add(Html.Label.create('ml10', 'To:'))
        .add(Html.Span.create(null, post.sendTo))
      }
      tile.add(Html.Br.create())
        .add(Html.Label.create(null, 'Date:'))
        .add(Html.Span.create(null, post.dateCreated));
      if (post.body)
        Html.Tile.create(self)
          .html(post.body);
      if (post.Stub)
        Html.Tile.create(self)
          .add(AnchorDocStub_Preview.create(DocStub.revive(post.Stub)));
      if (post.portalFile)
        Html.Tile.create(self)
          .add(Html.Image.create(null, 'portal-image.php?id=' + post.portalFile + '&w=' + maxWidth))
      return self;
    }
  }
}
/*DocView*/DocViewVisit = {
  create:function(container, stub, noCmdBar, height) {
    var My = this;
    return DocView.create(container, stub, noCmdBar, height).extend(function(self) {
      return {
        init:function() {
          self.load(stub);
          self.cb = Html.CmdBar.create(self.table.left)
            .print(self.print_onclick);
        },
        draw:function() {
          var maxWidth = container.getWidth() - 40;
          // EditHead.asNote('Visit Summary').into(self.view);
          Html.Tile.create(self.view, 'VisitHead').html(self.rec.finalHead);
          Html.Tile.create(self.view, 'VisitBody').html(self.rec.finalBody);
        },
        print_onclick:function() {
          VisitSummary.ajax().reprint(self.rec);
        }
      }
    })
  }
}
/*DocView*/DocViewClientDoc = {
  create:function(container, stub, noCmdBar, height) {
    var My = this;
    return DocView.create(container, stub, noCmdBar, height).extend(function(self) {
      return {
        init:function() {
          self.load(stub);
          self.cb = Html.CmdBar.create(self.table.left)
            .print(self.print_onclick)
            .button('Send to Portal', self.send_onclick, 'message');
        },
        draw:function() {
          var maxWidth = container.getWidth() - 40;
          Html.Tile.create(self.view).html(self.rec.html);
        },
        print_onclick:function() {
          ClientDoc.revive(self.rec).ajax().download();
        },
        send_onclick:function() {
          ClientDoc.revive(self.rec).ajax().sendToPortal();
        }
      }
    })
  }
}
/*DocView*/DocViewSuperbill = {
  create:function(container, stub, noCmdBar, height) {
    var My = this;
    return DocView.create(container, stub, noCmdBar, height).extend(function(self) {
      return {
        init:function() {
          self.load(stub);
          self.cb = Html.CmdBar.create(self.table.left).ok(self.ok_onclick);
        },
        draw:function() {
          var maxWidth = container.getWidth() - 40;
          var maxHeight = container.getHeight() - 100; 
          //Html.CenteredDiv.create().setText('Howdy').within(self.view);
          //Html.Tile.create(self.view).html(self.rec.html);
        },
        ok_onclick:function() {
          var sid = self.rec.internalId;
          CerberusPop.pop_asSuperbill(sid);
          //Page.pop('cerberus.php', {'sid':sid}, Page.HIDE_MENU);
        }
      }
    })
  }
}
/*DocView*/DocViewScan = {
  create:function(container, stub, noCmdBar, height) {
    var My = this;
    return DocView.create(container, stub, noCmdBar, height).extend(function(self) {
      return {
        onreview:function() {},
        //
        init:function() {
          self.view.addClass('ViewScan');
          if (self.table) { 
            self.cb = Html.CmdBar.create(self.table.left)
              .print(self.print_onclick)
              .button('Send to Office', self.sendoffice_onclick, 'message')
              .button('Send to Portal', self.send_onclick, 'message')
              .button('Record as Reviewed', self.record_onclick, 'approve', 'approve')
              .button('Forward to...', self.forward_onclick, 'approve', 'forward');
            self.label = self.cb.outer.label(null, 'pl5');
          }
          self.load(stub);
        },
        reset:function() {
          if (self.table) {
            self.cb.disable('approve');
            self.label.setText('');
          }
        },
        draw:function() {
          var maxWidth = container.getWidth() - 40;
          var maxHeight = container.getHeight() - 100; 
          EditHead.asImage('Scan(s)', self.open_onclick).into(self.view);
          var img;
          if (self.rec.ScanFiles) {
            self.rec.ScanFiles.forEach(function(file) {
              if (file._pdf)
                My.Pdf.create(file, maxWidth, maxHeight).into(self.view);
              else
                My.Image.create(file, maxWidth).into(self.view);
            })
          }
          if (self.table) {
            self.cb.disable('approve', ! self.stub.needsReview());
            self.label.setText(self.stub.getReviewedLabel());
          }
        },
        open_onclick:function() {
          self.view.invisible();
          EntryFolderPop.pop(self.rec)
            .bubble('onupdate', self.pop_onupdate)
            .bubble('onclose', self.pop_onclose);
        },
        pop_onupdate:function() {
          self.onupdate();
        },
        pop_onclose:function() {
          self.view.visible();
        },
        forward_onclick:function() {
          ForwardPop.pop(function(id) {
            self.stub.ajax().forward(id, function() {
              self.reload();
              Polling.Review.refresh();
            })
          })
        },
        record_onclick:function() {
          self.stub.ajax().reviewed(function() {
            self.reload();
            Polling.Review.refresh();
            self.onreview();
          })
        },
        sendoffice_onclick:function() {
          Page.Nav.goMessageNew(self.rec.clientId, stub);
        },
        send_onclick:function() {
          Page.Nav.goMessageNewPortal(self.rec.clientId, stub);
        },
        print_onclick:function() {
          Ajax.Scanning.download(self.rec.scanIndexId);
          //Pdf_Scan.from(self.rec).download();
        }
      }
    })
  },
  Image:{
    create:function(file, maxWidth) {
      var self = Html.Div.create('ScanImage');
      file.asImage(file.height, maxWidth).into(self);
      //Html.Label.create(null, file._uploaded).into(self);
      return self;
    }
  },
  Image2:{
    create:function(file, maxWidth) {
      var self = Html.Div.create('ScanImage');
      var div = Html.Div.create('pancontainer').into(self);
      var img = file.asImage(file.height, maxWidth).into(div);
      var $self = jQuery(div);
      $self.css({position:'relative', overflow:'hidden', cursor:'move'});
      var $img = jQuery(img);
      $img.bind('load', function() {
        var h = $img.height();
        var w = $img.width();
        div.setHeight(h).setWidth(w);
        var options = {$pancontainer:$self, pos:$self.attr('data-orient'), curzoom:1, canzoom:'yes', wrappersize:[w, h]};
        $img.imgmover(options);
      })
      Html.Label.create(null, file._uploaded).into(self);
      return self;
    }
  },
  Pdf:{
    create:function(file, maxWidth, maxHeight) {
      var iframe = Html.IFrame.create(null, file.pdfsrc).setHeight(maxHeight).setWidth(maxWidth);
      return iframe;
    }
  }
}
/*DocView*/DocViewScanXml = {
  create:function(container, stub, noCmdBar, height) {
    return DocView.create(container, stub, noCmdBar, height).extend(function(self) {
      return {
        init:function() {
          //if (self.table)
            //Html.CmdBar.create(self.table.left).print(self.print_onclick);
          self.load(stub);
        },
        draw:function() {
          EditHead.asImage('Clinical Document', self.open_onclick).into(self.view);
          self.contents = Html.Div.create().into(self.view);
          self.contents.addClass('ViewXml');
          if (self.rec._html)
            self.contents.html(self.rec._html);
        },
        open_onclick:function() {
          self.view.invisible();
          EntryFolderPop.pop(self.rec)
            .bubble('onupdate', self.pop_onupdate)
            .bubble('onclose', self.pop_onclose);
        },
        pop_onupdate:function() {
          self.onupdate();
        },
        pop_onclose:function() {
          self.view.visible();
        },
        print_onclick:function() {
        }
      }
    })
  }
}
/*DocView*/DocViewResult = {
  create:function(container, stub, noCmdBar, height) {
    return DocView.create(container, stub, noCmdBar, height).extend(function(self) {
      return {
        onreview:function() {},
        //
        init:function() {
          self.view.addClass('ViewResult');
          if (self.table) {
            self.cb = Html.CmdBar.create(self.table.left)
              .print(self.print_onclick)
              .button('Send to Office', self.sendoffice_onclick, 'message')
              .button('Send to Portal', self.send_onclick, 'message')
              .button('Record as Reviewed', self.record_onclick, 'approve', 'approve')
              .button('Forward to...', self.forward_onclick, 'approve', 'forward');
            self.label = self.cb.outer.label(null, 'pl5');
          }
          self.load(stub);
        },
        draw:function() {
          var name = self.rec.Ipc.name + " (" + self.rec.date + ")";
          EditHead.asGraph(name, self.open_onclick).into(self.view);
          if (self.rec.LabInbox)
            self.drawInbox();
          if (self.rec.comments == null) {
            self.drawChart();
          } else {
            self.drawComments();
            if (self.rec.ProcResults)
              self.drawChart();
          }
          if (self.rec.ScanIndex)
            self.drawScans();
          if (self.table) {
            self.cb.disable('approve', ! self.stub.needsReview());
            self.label.setText(self.stub.getReviewedLabel());
          }
        },
        drawInbox:function() {
          var tile = Html.Tile.create(self.view, 'mt-10');
          var ib = self.rec.LabInbox;
           Html.UlEntry.create(tile, function(ef) {
             var e = ef.line()
               .l('Source').lro(ib.source)
               .l('Report Received').lro(ib.dateReceived)
               .append(Html.Anchor.create('find ml10', 'View Source Details...', LabMessagePop.pop_forInbox.curry(ib.hl7InboxId)));
             if (ib.pdf) {
               e.append(Html.Anchor.create('ml10 action tpdf', 'View PDF...', Page.popLabPdf.bind(Page, ib.hl7InboxId)));
             }
           })
        },
        drawComments:function() {
          var ul = Html.Ul.create('eform').into(self.view);
          var li = ul.li();
          Html.Label.create('first', 'Comments').into(li);
          Html.Span.create('rof').html('<pre>' + self.rec.comments + '</pre>').into(li);
          /*
          var t = Html.Table.create(self.view, 'ResultTable');
          t.thead().tr().th('Comments').w('100%');
          var tag = self.rec.LabInbox ? 'pre' : 'span';
          t.tbody().trToggle().td(Html.create('tag').html(self.rec.comments));
          t.addClass('mb10');
          */
        },
        drawChart:function() {
          var t = Html.Table.create(self.view, 'ResultTable mt10');
          t.thead().tr()
            .th('Result Item').w('25%')
            .th('Value').w('10%')
            .th('Range').w('10%')
            .th('Interpret').w('5%')
            .th('Comments').w('50%');
          if (self.rec.ProcResults)
            self.rec.ProcResults.forEach(function(result) {
              var edit = Html.Anchor.create(null, result.Ipc.name, self.select_onclick.curry(result)); 
              //var tag = self.rec.LabInbox ? 'pre' : 'span';
              var tag = 'span';
              t.tbody().trToggle()
                .th(edit)
                .td(result._value, result.interpretCode)
                .td(result.range)
                .td(result._interpretCode)
                .td(Html.create(tag).html(result.comments));
            })
          else
            t.tbody().trToggle().th().td('(No Results)').colspan(4);
        },
        drawScans:function() {
          var maxWidth = container.getWidth() - 40;
          var maxHeight = container.getHeight() - 100; 
          EditHead.asImage('Image(s)', self.scan_onclick).addClass('mt20').into(self.view);
          self.rec.ScanIndex.ScanFiles.forEach(function(file) {
            if (file._pdf)
              DocViewScan.Pdf.create(file, maxWidth, maxHeight).into(self.view);
            else
              DocViewScan.Image.create(file, maxWidth).into(self.view);
          })
        },
        open_onclick:function() {
          self.view.invisible();
          self.working(true);
          Proc.fetch(self.rec.procId, function(proc) {
            self.working(false);
            ProcEntry.pop(proc)
              .bubble('onsave', self.pop_onupdate)
              .bubble('onresultsave', self.pop_onupdate)
              .bubble('ondelete', function() {
                self.onupdate(null);
              })
              .bubble('onclose', self.pop_onclose);
          })
        },
        scan_onclick:function() {
          self.view.invisible();
          EntryFolderPop.pop(self.rec.ScanIndex)
            .bubble('onupdate', self.pop_onupdate)
            .bubble('onclose', self.pop_onclose);
        },
        pop_onupdate:function() {
          self.reload();
        },
        pop_onclose:function() {
          self.view.visible();
        },
        forward_onclick:function() {
          ForwardPop.pop(function(id) {
            self.stub.ajax().forward(id, function() {
              self.reload();
              Polling.Review.refresh();
            })
          })
        },
        record_onclick:function() {
          self.stub.ajax().reviewed(function() {
            self.reload();
            Polling.Review.refresh();
            self.onreview();
          })
        },
        sendoffice_onclick:function() {
          Page.Nav.goMessageNew(self.rec.clientId, stub);
        },
        send_onclick:function() {
          Page.Nav.goMessageNewPortal(self.rec.clientId, stub);
        },
        print_onclick:function() {
          Ajax.Procedures.download(self.rec.procId);
        },
        select_onclick:function(result) {
          ResultHistoryPop.pop(result, self.rec);
        }
      }
    })
  }
}
ForwardPop = Html.SingletonPop.aug({
  create:function() {
    return Html.Pop.create('Forward for Review').extend(function(self) {
      return {
        //
        init:function() {
          self.form = Html.UlEntry.create(self.content, function(ef) {
            ef.line().l('Forward To').select('userId', C_Docs, '');
          })
          Html.CmdBar.create(self.content).ok(self.ok_onclick).cancel(self.close);
        },
        onpop:function(callback) {
          self.callback = callback;
        },
        ok_onclick:function() {
          var id = self.form.getValue('userId');
          self.close();
          if (id)
            self.callback(id);
        }
      }
    })
  }
})
/*AnchorDocStub*/AnchorDocStub_Preview = {
  create:function(rec, nulltext/*opt*/) {
    return (rec) ? AnchorDocStub.create(rec, DocStubPreviewPop.pop_asOverlay) : nulltext;
  },
  create_orFacesheet:function(rec) {
    return AnchorDocStub_Preview.create(rec, '(Facesheet)');
  }
}
/** Attachments */
AnchorStubAttach = {
  create:function(rec) {
    return AnchorDocStub.create(rec).extend(function(self) {
      return {
        ondetach:function() {
        },
        //
        onclick:function() {
          AttachPreviewPop.pop_forDetach(rec).bubble('ondetach', self);
        }
      }
    })
  }
}
AttachPreviewPop = {
  /*
   * @arg DocStub rec
   */
  pop:function(rec, isAttached, onattach) {
    return Html.Pop.singleton_pop.apply(AttachPreviewPop, arguments);
  },
  pop_forAttach:function(rec, onattach) {
    return AttachPreviewPop.pop(rec, false, onattach);
  },
  pop_forDetach:function(rec) {
    return AttachPreviewPop.pop(rec, true);
  },
  create:function() {
    var My = this;
    return Html.Pop.create('Attachment', 800).extend(function(self) {
      return {
        ondetach:function() {
        },
        onattach:function() {
        },
        //
        init:function() {
          var height = self.fullscreen(1000, 600) - 20;
          self.content.addClass('dsp');
          self.viewer = My.Viewer.create(self.content, height).bubble('onexit', self.close);
          self.cb = Html.CmdBar.create(self.content).button('Attach', self.attach_onclick, 'ok').button('Detach', self.detach_onclick, 'delete').exit(self.close)
        },
        reset:function() {
          self.viewer.reset();
        },
        onshow:function(rec, isAttached, onattach) {
          self.rec = rec;
          self.onattach = onattach;
          self.cb.showButtonIf('Attach', ! isAttached).showButtonIf('Detach', isAttached);
          self.viewer.load(rec);
        },
        attach_onclick:function() {
          self.close();
          self.onattach();
        },
        detach_onclick:function() {
          Pop.Confirm.showYesNo('Are you sure you want to remove this attachment?', function() {
            self.close();
            self.ondetach();
          })
        }
      }
    })
  },
  Viewer:{
    create:function(container, height) {
      var self = Html.Tile.create(container, 'Viewer');
      return self.aug({
        onexit:function() {
        },
        //
        init:function() {
          self.spacer = DocView.asSpacer(self, height);
        },
        load:function(rec) {
          self.reset();
          self.rec = rec;
          self.draw();
        },
        //
        reset:function() {
          if (self.viewing)
            self.viewing.hide();
          self.viewing = null;
          self.spacer.show();
        },
        draw:function() {
          var view = DocView.from(self.rec);
          self.spacer.hide();
          self.viewing = view.create(self, self.rec, true, height);
        }
      })
    }
  }
}