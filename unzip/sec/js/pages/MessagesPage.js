/**
 * Messages Page
 */
MessagesPage = page = {
  //
  start:function(query) {
    page.tile = MessagesTile.create();
    var id = (query.mine) ? me.userId : null;
    if (query.get == 'sent')
      page.tile.showSent();
    else
      page.tile.showInbox(id);
    Page.setEvents();
  },
  onresize:function() {
    var i = Html.Window.getViewportDim().height - 200;
    if (i != self.maxHeight) {
      self.maxHeight = i;
      page.tile.setMaxHeight(i);
    }
  }
}
MessagesTile = {
  create:function() {
    var My = this;
    return _$('tile').extend(function(self) {
      return {
        init:function() {
          self.clean();
          self.header = Html.Table2ColHead.create(self).extend(My.Header).bubble('onupdate', self.header_onupdate);
          self.tiles = Html.Tiles.create(self, [
            self.inbox = My.Table.create_asInbox(self).bubble('onloaddash', self.table_onload),
            self.sent = My.Table.create_asSent(self).bubble('onloaddash', self.table_onload)]);
          self.cb = Html.CmdBar.create(self)
            .add('Office Message...', self.office_onclick)
            .add('Patient Portal Message...', self.patient_onclick);
        },
        showInbox:function(id) {
          self.header.showInbox();
          self.inbox.load(id);
          self.tiles.select(self.inbox);
          return self;
        },
        showSent:function() {
          self.header.showSent();
          self.sent.load();
          self.tiles.select(self.sent);
          return self;
        },
        setMaxHeight:function(i) {
          i -= (self.cb.getHeight() + self.header.getHeight());
          self.inbox.setMaxHeight(i);
          self.sent.setMaxHeight(i);
        },
        //
        office_onclick:function() {
          Page.Nav.goMessageNew();
        },
        patient_onclick:function() {
          PatientSelector.pop(function(client) {
            Page.Nav.goMessageNewPortal(client.clientId);
          })
        },
        table_onload:function(dash) {
          self.header.load(dash);
        },
        header_onupdate:function(user) {
          self.inbox.load(user.userId);
        }
      }
    })
  },
  Header:function(self) {
    return {
      onupdate:function(user) {},
      //
      init:function() {
        Html.Tiles.create(self.left, [
          self.headInbox = Html.Table2Col.create().extend(self._HeadInbox).bubble('onupdate', self),
          self.headSent = Html.H2.create('My Sent Messages')]);
        Html.Tiles.create(self.right, [
          self.navInbox = Html.Anchor.create('icon big go', 'View my sent messages', Page.Nav.goMessagesSent),
          self.navSent = Html.Anchor.create('icon big go', 'View my inbox', Page.Nav.goMessages)]);
      },
      showInbox:function() {
        self.headInbox.select();
        self.navInbox.select();
      },
      showSent:function() {
        self.headSent.select();
        self.navSent.select();
      },
      load:function(dash) {
        self.headInbox.load(dash);
      },
      //
      _HeadInbox:function(self) {
        return {
          onupdate:function(user) {},
          //
          init:function() {
            Html.H2.create('Inbox for').into(self.left);
            self.User = UserSelector.create().into(self.right).addClass('ml5')
              .bubble('onupdate', self);
          },
          load:function(dash) {
            var recip = dash.keys.getRecipientUser();
            self.User.load(dash.keys.users).setValue(recip);
          }
        }
      }
    }
  },
  Table:{
    create:function(container, offCls) {
      return Html.TableLoader.create(container, 'fsgr', null, offCls).extend(function(self) {
        return {
          onloaddash:function(dash) {},
          //
          init:function() {
            self.thead().trFixed()
              .th('Message').w('30%')
              .th('Patient').w('15%')
              .th('Last Post').w('20%')
              .th('From').w('15%')
              .th('To').w('15%');
            self.setSideFilter().bubble('onload', function() {
              this.showIf(this.getTopFilterCount());
            });
          },
          filter:function(rec) {
            return {
              'Type':rec._type,
              'Patient':rec._patient};
          },
          setMaxHeight:function(i) {
            self.setHeight(i);
          },
          add:function(rec, tr) {
            var type = (rec.isPortal()) ? Html.Span.create('patient', rec._type) : rec._type;
            tr.select(rec.asSelector())
              .td(rec._patient)
              .td(rec.uiPostDate())
              .td(rec.uiPostFrom())
              .td(rec.uiPostTo());
            self.onadd(rec, _$(self._tr));
          },
          onselect:function(rec) {
            Page.Nav.goMessage(rec.threadId);
          }
        }
      })
    },
    create_asInbox:function(container) {
      return this.create(container, '').extend(function(self) {
        return {
          fetch:function(callback) {  // if self.recipId == null, will use cached dashboard value
            Dashboard.ajax().fetchMessages(self.recipId, function(dash) {
              self.recipId = dash.keys.msgRecipient; 
              self.dash = dash;
              self.onloaddash(dash);
              callback(dash.messages);
            })
          },
          load:self.load.extend(function(_load, recipId) {
            self.recipId = recipId;
            _load();
          }),
          onadd:function(rec, tr) {
            tr.addClassIf('off', rec.isUnread());
          },
          onselect:function(rec) {
            var id = (self.recipId == me.userId) ? null : self.recipId;
            Page.Nav.goMessage(rec.threadId, id);
          }
        }
      })
    },
    create_asSent:function(container) {
      return this.create(container, 'off2').extend(function(self) {
        return {
          fetch:function(callback) {
            MsgThreads.ajax().fetchSent(callback);
          },
          onadd:function(rec, tr) {}
        }
      })
    }
  }
}
