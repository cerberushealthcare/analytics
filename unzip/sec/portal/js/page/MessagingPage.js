/**
 * PortalPage MessagingPage
 */
MessagingPage = PortalPage.extend(function(self) {
  return {
    onbodyload:function() {
      PageTile.create();
    }
  }
})
PageTile = {
  create:function() {
    return Html.Tile.create(window.content).extend(function(self) {
      return {
        STATES:{LISTING:0,EDITING:1},
        //
        init:function() {
          self.tiles = Html.Tiles.create(self, [
            self.list = ListTile.create(self)
              .bubble('onselect', self.list_onselect)
              .bubble('oncompose', self.list_oncompose),
            self.edit = EditTile.create(self)
              .bubble('onpost', self.draw_asListing.curry('Your message was sent.'))
              .bubble('onreturn', self.draw_asListing)]);
          if (_get.a == 'refill' && _get.m)
            self.draw_asRefill(_get.m);
          else
            self.draw_asListing();
        },
        draw:function(state) {
          if (state == self.STATES.LISTING) 
            self.list.select().load();
          else
            self.edit.select().load(self.thread);
        },
        draw_asListing:function(msg) {
          self.draw(self.STATES.LISTING);
          if (String.is(msg))
            PopMsg.create().show(msg);
        },
        draw_asEditing:function() {
          self.draw(self.STATES_EDITING);
        },
        draw_asRefill:function(med) {
          self.draw_asEditing();
          self.edit.setRefill(med);
        },
        list_onselect:function(thread) {
          self.thread = thread;
          self.draw_asEditing();
        },
        list_oncompose:function() {
          self.thread = null;
          self.draw_asEditing();
        }
      }
    })
  }
}
EditTile = {
  create:function(container) {
    var My = this;
    return Html.Tile.create(container, 'Message').extend(function(self) {
      return {
        onpost:function() {},
        onreturn:function() {},
        //
        init:function() {
          self.head = Html.H1.create().into(self);
          self.posts = Html.Tile.create(self, 'Posts');
          self.reply = My.ReplyTile.create(self)
            .bubble('onpost', self)
            .bubble('onreturn', self);
          self.back = ReturnAnchor.create(self, 'Back to Messages');
        },
        load:function(thread) {
          if (thread) {
            self.back.hide();
            self.posts.clean().setHeight(100).working(true);
            Ajax.Messaging.openThread(thread.threadId, function(thread) {
              self.thread = thread;
              self.reply.load(thread);
              self.posts.setHeight().working(false);
              self.draw();
              self.back.show();
            })
          } else {
            self.thread = null;
            self.reply.load();
            self.back.hide();
            self.draw();
          }
        },
        setRefill:function(med) {
          self.reply.setRefill(med);
        },
        draw:function() {
          self.head.setText((self.thread && self.thread.subject) || 'New Message');
          self.posts.clean();
          if (self.thread) {
            self.thread.MsgPosts.each(function(post) {
              My.Post.create(post, self.getWidth()).into(self.posts);
            })
          }
        }
      }
    })
  },
  ReplyTile:{
    create:function(container) {
      var My = this;
      return Html.Tile.create(container).extend(function(self) {
        return {
          onpost:function() {},
          onreturn:function() {},
          //
          init:function() {
            self.tiles = Html.Tiles.create(self, [
              self.reply = Html.AnchorAction.asNote('Post a reply').addClass('mt10 bold').into(self)
                .bubble('onclick', self.reply_onclick),
              self.editor = My.Editor.create(self)
                .bubble('onpost', self)
                .bubble('oncancel', self.editor_oncancel)]);
          },
          load:function(thread) {
            self.thread = thread;
            self.editor.load(self.thread);
            if (self.thread)
              self.reply.select();
            else
              self.editor.select();
          },
          setRefill:function(med) {
            self.editor.setRefill(med);
          },
          //
          reply_onclick:function() {
            self.editor.select();
          },
          editor_oncancel:function() {
            if (self.thread)
              self.reply.select();
            else
              self.onreturn();
          }
        }
      })
    },
    Editor:{
      create:function(container) {
        var My = this;
        return Html.Tile.create(container, 'Editor').extend(function(self) {
          return {
            onpost:function() {},
            oncancel:function() {},
            //
            init:function() {
              self.htitle = Html.Table2Col.create(Html.Div.create('Author').into(self));
              self.htitle.left.setText('New Post');
              var div = Html.Tile.create(self, 'EditorC');
              self.typebox = Html.Tile.create(div);
              Html.Label.create(null, 'Type:').into(self.typebox);
              self.type = Html.Select.create(Map.from(C_MsgTypes, null, 'name')).into(self.typebox);
              self.input = Html.DirtyTextArea.create().setRows(6).into(div);
              self.attachments = My.Attachments.create(div, self.getWidth()); 
              self.cmd = Html.CmdBar.create(self).button('Send Now', self.send_onclick, 'send').cancel(self.cancel_onclick);
            },
            load:function(thread) {
              self.reset();
              self.thread = thread;
              self.draw();
            },
            setRefill:function(med) {
              self.type.setValue(1/*Rx Refill*/);
              self.input.setValue('Please refill the following medication: ' + med).set('_dirty', true);
            },
            reset:function() {
              self.thread = null;
              self.input.clean();
              self.type.setValue();
              self.draw();
            },            
            //
            draw:function() {
              self.typebox.showIf(self.thread == null);
              self.htitle.showIf(self.thread);
            },
            ontileselect:function() {
              self.input.setFocus();
            },
            getType:function() {
              return C_MsgTypes[self.type.getValue()];
            },
            getTypeSendTos:function() {
              var type = self.getType();
              return Array.from(Json.decode(type.sendTo));
            },
            getTypeName:function() {
              var type = self.getType();
              return type.name;
            },
            getBody:function() {
              return '<p>' + self.input.getValue() + '</p>';
            },
            getFile:function() {
              return self.attachments.getFilename();
            },
            send_onclick:function() {
              if (! self.input.isDirty()) {
                PopMsg.create().show('Please type a message before sending.');
                return;
              }
              self.working(function() {
                if (self.thread) {
                  var sendTos = [self.thread.getReplyId()];
                  Ajax.Messaging.postReply(self.thread.threadId, sendTos, self.getBody(), self.getFile(), function() {
                    self.working(false);
                    self.onpost();
                  })
                } else {
                  Ajax.Messaging.newThread(self.getTypeName(), self.getTypeSendTos(), self.getBody(), self.getFile(), function() {
                    self.working(false);
                    self.onpost();
                  })
                }
              })
            },
            cancel_onclick:function() {
              self.oncancel();
            }
          }
        })
      },
      Attachments:{
        create:function(container, maxWidth) {
          return Html.Tile.create(container, 'Attachments').extend(function(self) {
            return {
              init:function() {
                self.attacher = Html.Anchor.create('action attach', 'Attach a file', self.attach_onclick).into(self);
                self.preview = Html.Tile.create(self);
                self.deleter = Html.Anchor.create('action detach', 'Remove this attachment', self.detach_onclick).into(self);
                self.reset();
              },
              getFilename:function() {
                return self.filename;
              },
              //
              reset:function() {
                self.filename = null;
                self.preview.clean();
                self.draw();
              },
              load:function(filename) {
                self.filename = filename;
                self.preview.add(PortalFileImage.create(filename, maxWidth));
                self.draw();
              },
              draw:function() {
                self.attacher.showIf(self.filename == null);
                self.deleter.showIf(self.filename);
                self.preview.showIf(self.filename);
              },
              //
              attach_onclick:function() {
                UploadPop.pop(function(filename) {
                  self.load(filename);
                })
              },
              detach_onclick:function() {
                Pop.Confirm.showYesNo('Are you sure you want to remove this attachment?', function() {
                  self.reset();
                })
              }
            }
          })
        }
      }
    }
  },
  Post:{
    create:function(post, maxWidth) {
      var My = this;      
      return Html.Div.create('Post').extend(function(self) {
        return {
          //
          init:function() {
            self.authorc = Html.Div.create('Author').into(self);
            self.author = Html.Table2Col.create(self.authorc);
            self.body = Html.Div.create('Body').into(self);
            self.draw();
          },
          draw:function() {
            self.authorc.addClassIf('self', post.authorId == me.portalUserId);
            self.author.left.setText(post.author);
            self.author.right.setText(post.dateCreated);
            self.body.html(post.body);
            if (post.Stub) 
              My.Preview.create(post.Stub).into(self);
            if (post.portalFile)
              PortalFileImage.create(post.portalFile, maxWidth).into(self);
          }
        }
      })
    },
    Preview:{
      create:function(stub) {
        return Html.Div.create('Preview').extend(function(self) {
          return {
            //
            init:function() {
              switch (stub.type) {
                case C_DocStub.TYPE_SCAN:
                  self.addScans(stub.Preview.ScanFiles);
                  break;
                case C_DocStub.TYPE_RESULT:
                  self.addProc(stub.Preview);
                  break;
                default:
                  self.html(stub.Preview._html);
                  break;
              }
            },
            addProc:function(proc) {
              if (proc.comments == null) {
                self.addChart(proc);
              } else {
                self.addComments(proc);
                if (proc.ProcResults)
                  self.addChart(proc);
              }
              if (proc.ScanIndex && proc.ScanIndex.ScanFiles)
                self.addScans(proc.ScanIndex.ScanFiles);
            },
            addChart:function(proc) {
              var t = Html.Table.create(self, 'ResultTable');
              t.thead().tr()
                .th('').w('25%')
                .th('Value').w('10%')
                .th('Range').w('10%')
                .th('Interpret').w('5%')
                .th('Comments').w('50%');
              if (proc.ProcResults)
                proc.ProcResults.forEach(function(result) {
                  var tag = proc.LabInbox ? 'pre' : 'span';
                  t.tbody().trToggle()
                    .th(result.Ipc.name)
                    .td(result._value, result.interpretCode)
                    .td(result.range)
                    .td(result._interpretCode)
                    .td(Html.create(tag).html(result.comments));
                })
              else
                t.tbody().trToggle().th().td('(No Results)').colspan(4);
            },
            addComments:function(proc) {
              
            },
            addScans:function(files) {
              var vd = Html.Window.getViewportDim();
              var maxw = vd.width - 80;
              if (maxw > 800) 
                maxw = 800;
              maxw = maxw - 40;
              var maxh = vd.height * .8;
              files.each(function(file) {
                if (file._pdf)
                  self.addPdf(file, maxh, maxw);
                else
                  self.addImage(file, maxw);
              })
            },
            addImage:function(file, maxw) {
              var img = Html.Image.create('ScanImage', file.src);
              img.setSizeWithin(file.height, file.width, null, maxw);
              Html.Tile.create(self, 'ScanImage').add(img);
            },
            addPdf:function(file, maxh, maxw) {
              var iframe = Html.IFrame.create(null, file.pdfsrc).setHeight(maxh).setWidth(maxw);
              Html.Tile.create(self, 'ScanImage').add(iframe);
            }
          }
        })
      }
    }
  }
}
ListTile = {
  create:function(container) {
    var My = this;
    return Html.Tile.create(container, 'ListTile').extend(function(self) {
      return {
        onselect:function(thread) {},
        oncompose:function() {},
        //
        init:function() {
          self.head = Html.H1.create('Messages').into(self);
          self.stubs = Html.Tile.create(self, 'Stubs');
          var actions = Html.Tile.create(self, 'Actions');
          self.compose = Html.AnchorAction.asNote('Compose a new message').into(actions).bubble('onclick', self, 'oncompose'),
          self.back = ReturnAnchor.create(self);
        },
        load:function() {
          self.back.hide();
          self.stubs.clean().setHeight(100).working(true);
          Ajax.Messaging.getMyInboxThreads(function(threads) {
            self.threads = threads;
            self.stubs.setHeight().working(false);
            self.draw();
            self.back.show();
          })          
        },
        draw:function() {
          self.stubs.clean();
          if (self.threads.isEmpty()) {
            self.stubs.add(Html.Span.create('none', 'You have no messages.'));
          } else { 
            self.threads.each(function(thread) {
              My.Stub.create(thread).into(self.stubs).bubble('onselect', self);
            })
          }
        }
      }
    })
  },
  Stub:{
    create:function(thread) {
      var My = this;
      return Html.HoverableDiv.create('Stub').extend(function(self) {
        return {
          onselect:function(thread) {},
          //
          init:function() {
            self.topic = Html.Div.create('Topic').into(self);
            self.post = Html.Div.create('Post').into(self);
            self.draw();
          },
          draw:function() {
            self.addClassIf('read', thread.isRead());
            self.topic.setText(thread.subject);
            self.post.add(Html.Span.create('bold').setText('Last post:'));
            self.post.add(Html.Span.create('ml5').setText(self.getPostText(thread.Inbox.MsgPost)));
          },
          getPostText:function(p) {
            return p.dateCreated + ' by ' + p.author;
          },
          onclick:function() {
            self.onselect(thread);
          }
        }
      })
    }
  }
}
UploadPop = {
  pop:function(callback) {
    return Html.Pop.singleton_pop.apply(this, arguments);
  },
  create:function() {
    return Html.UploadPop.create('Upload File', 'upload', 1).extend(function(self) {
      return {
        onpop:function(callback) {
          self.callback = callback;
        },
        oncomplete:function(filename) {
          self.callback(filename);
        }
      }
    })
  }
}
PortalFileImage = {
  create:function(filename, maxWidth) {
    return Html.Image.create(null, 'portal-image.php?id=' + filename + '&w=' + maxWidth);
  }
}