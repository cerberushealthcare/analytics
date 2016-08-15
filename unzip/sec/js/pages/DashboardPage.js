/**
 * Dashboard Page
 * @author Warren Hornsby
 */
DashboardPage = page = {
  //
  start:function(query) {
    DashPanels.create();
    Page.setEvents();
  },
  onresize:function() {
    var vp = Html.Window.getViewportDim();
    var i = vp.height - 200;
    if (i != self.maxHeight) {
      self.maxHeight = i;
      DashPanels.setMaxHeight(i);
    }
    DashPanels.widthCheck(vp.width);
  },
  onfocus:function() {
    //if (! DashPanels.isWorking())
    //  DashPanels.fetch();
  }
}
DashPanels = {
  create:function() {
    return DashPanels = _$('boxes').extend(function(self) {
      return {
        init:function() {
          self.Panels = [
            self.Sched = Dash_Sched.create(),
            self.Patient = Dash_Patient.create()
              .bubble('onload', self.Patient_onload),
            self.Message = Dash_Message.create(),
            self.Review = Dash_Review.create()
            ].delegate('working', 'load', 'setMaxHeight', 'showIcon');
          self.fetch();
          self._showingIcons = true;
        },
        fetch:function() {
          self._working = true;
          Dashboard.ajax(self.Panels).fetch(self.load);
        },
        load:function(dash) {
          self.dash = dash;
          self.Panels.load(dash);
          self.loadLogin(dash.login);
          self._working = null;
        },
        isWorking:function() {
          return self._working;
        },
        setMaxHeight:function(i) {
          i = i / 2 - 10;
          self.Panels.setMaxHeight(i);
        },
        widthCheck:function(i) {
          var showingIcons = i > 900;
          if (showingIcons != self._showingIcons) {
            self._showingIcons = showingIcons; 
            self.showIcons();
          }
        },
        showIcons:function() {
          self.Panels.showIcon(self._showingIcons);
        },
        Patient_onload:function() {
          self.Patient.setFocus();
        },
        loadLogin:function(login) {
          if (login && login.recentBad) {
            _$('failed').setText('failed login'.plural(login.recentBad) + ' last 5 days');
            _$('failed-span').style.display = '';
          } else {
            _$('failed-span').style.display = 'none';
          }
          _$('last-login-span').style.display = '';
        }
      }
    })
  }
}
DashPanel = {
  create:function(id, tcls) {
    var My = this;
    return _$(id).extend(function(self) {
      return {
        onload:function() {},
        //
        init:function() {
          self.Head = My.Head.create(_$(id + '-head').setHeight(22)).invisible();
          self.Table = My.Table.create($(id + '-table'), tcls).bubble('onload', self.Table_onload);
          self.Foot = _$(id + '-foot').setHeight(26).invisible();
        },
        load:function() {},
        //
        reset:function() {
          self.Table.reset();
        },
        showIcon:function(b) {
          _$('td' + id).showIf(b);
        },
        working:function(b) {
          self._working = b;
          if (b)
            self.Table.reset();
          self.Table.working(b);
          self.Table.wrapper.addClassIf('tuiwork', b);
          Function.is(b) && async(b);
        },
        isWorking:function() {
          return self._working;
        },
        setMaxHeight:function(i) {
          self.Table.setHeight(i - self.Head.getHeight() - self.Foot.getHeight());
        },
        Table_onload:function() {
          self.Head.visible();
          self.Foot.visible();
          self.onload();
        }
      }
    })
  },
  Head:{
    create:function(container) {
      return Html.Table2Col.create(container);
    }
  },
  Table:{
    create:function(container, tcls) {
      return Html.TableLoader.create(container, tcls).noWorking();
    }
  }
}
Dash_Sched = {
  create:function() {
    return DashPanel.create('sched', 'fsg').extend(function(self) {
      return {
        init:function() {
          self.Head.extend(Dash_Sched._Head)
            .bubble('onchange', self.Head_onchange);
          Html.AnchorAction.asNew('New appointment', Page.Nav.goSched).addClass('ml10').into(self.Foot);
          self.Table.aug({
            rowOffset:function(rec) {
              return rec._past || '0';
            },
            onselect:function(rec) {
              if (rec.CrAppt == null)
                Page.Nav.goSchedEdit(rec.schedId);
            },
            ondrawrow:function(rec) {
              if (rec.date != self._lastDate) {
                this.tbody().tr(rec).td(Html.Anchor.create(null, rec._date, Page.Nav.goSchedDate.curry(rec.date.substr(0, 10))), 'brk').colspan(2);
                self._lastDate = rec.date;
              }
              if (rec._past)
                self._lastPast = self.Table._tr;
            },
            ondraw:function() {
              self.Table.scrollTo(self._lastPast, 25);
            }
          })
        },
        load:function(dash) {
          self.dash = dash;
          self.Head.load(dash.keys);
          self.Table.load(dash.appts, function(rec, tr) {
            var e;
            if (rec.Client)
              e = AnchorClient_Facesheet.create(rec.Client);
            else
              e = Html.Span.create(null, rec.comment);
            tr.select(AnchorAppt.create(rec))
              .td(e);
          })
        },
        reset:function() {
          self.Table.reset();
          self._lastDate = null;
          self._lastPast = null;
        },
        Head_onchange:function(keys) {
          self.reset();
          self.dash.ajax(self).refetchAppts(keys.apptDate, keys.apptDoctor, self.load);
        }
      }
    })
  },
  _Head:function(self) {
    return {
      onchange:function(keys) {},
      //
      init:function() {
        Html.Anchor.create('Head', 'Schedule', Page.Nav.goSched).into(self.left);
        self.Date = Html.AnchorDate.create().into(self.right)
          .bubble('onset', self.Date_onset);
        Html.Label.create('ml5 mr5', 'for').into(self.right);
        self.Doctor = DoctorAnchorTab.create().into(self.right)
          .bubble('onset', self.Doctor_onset);
      },
      load:function(keys) {
        self.keys = keys;
        self.Date.setText(self.keys.apptDate);
        self.Doctor.set_byId(self.keys.apptDoctor);
        self.setKeys();
      },
      setKeys:function() {
        self.keys.apptDate = self.Date.getText();
        self.keys.apptDoctor = self.Doctor.getValue();
        return self.keys;
      },
      //
      Date_onset:function() {
        if (self.Date.getText() != self.keys.apptDate)
          self.onchange(self.setKeys());
      },
      Doctor_onset:function() {
        if (self.Doctor.getValue() != self.keys.apptDoctor) {
          self.onchange(self.setKeys());
        }
      }
    }
  }
}
Dash_Patient = {
  create:function() {
    return DashPanel.create('patient', 'fsb').extend(function(self) {
      return {
        init:function() {
          self.Head.extend(Dash_Patient._Head)
            .bubble('onchange', self.Head_onchange);
          if (me.Role.Patient.create)
            Html.AnchorAction.asNew('New patient', self.new_onclick).into(self.Foot);
          self.Input = Html.InputTextBlank.create('Search for name / ID').into(Html.Span.create('search').into(self.Foot))
            .bubble('onkeypresscr', self.Input_oncr);
        },
        load:function(dash) {
          self.patients = dash.patients;
          self.Table.load(self.patients, function(rec, tr) {
            tr.td(AnchorClient_Facesheet.create(rec)).td(rec.birth);
          })
        },
        setFocus:function() {
          self.Input.setFocus();
        },
        new_onclick:function() {
          PatientCreator.pop();
        },
        lookup_onclick:function() {
          PatientSelector.pop_centered();
        },
        Head_onchange:function() {
          // TODO
        },
        Input_oncr:function() {
          var value = self.Input.getValue();
          var pop = (String.hasNumber(value)) ? PatientSelector.pop_searchId : PatientSelector.pop_searchName;
          pop(value, null, function() {
            self.Input.setFocus();
          });
        }
      }
    })
  },
  _Head:function(self) {
    return {
      onchange:function(keys) {},
      //
      init:function() {
        Html.Anchor.create('Head', 'Patients', Page.Nav.goPatients).into(self.left);
      }
    }
  }
}
Dash_Review = {
  create:function() {
    return DashPanel.create('review').extend(function(self) {
      return {
        init:function() {
          Html.Anchor.create('Head', 'Items to Review', Page.Nav.goReview).into(self.Head.left);
          self.Table.aug({
            rowOffset:function(rec) {
              return rec.cid;
            },
            onselect:function(rec) {
              Page.go('review.php', {'type':rec.type,'id':rec.id});
            }
          })
        },
        load:function(dash) {
          self.stubs = dash.unreviewed;
          self.Table.load(self.stubs, function(rec, tr) {
            tr.select(AnchorDocStub)
              .td(rec.Unreviewed.Client.name);
          })
        }
      }
    })
  }
}
Dash_Message = {
  create:function() {
    return DashPanel.create('message', 'fsgr').extend(function(self) {
      return {
        init:function() {
          self.Head.extend(Dash_Message._Head)
            .bubble('onchange', self.Head_onchange);
          Html.AnchorAction.asMsg('Compose', self.compose_onclick).into(self.Foot);
          self.Table.aug({
            rowOffset:function(rec) {
              return null;
            },
            onadd:function(rec, tr) {
              tr.addClassIf('off', rec.isUnread());
            },
            onselect:function(rec) {
              var id = self.dash.keys.msgRecipient;
              if (id == me.userId)
                id = null;
              Page.Nav.goMessage(rec.threadId, id);
            }
          })
        },
        load:function(dash) {
          self.dash = dash;
          self.Head.load(dash.keys);
          self.Table.load(dash.messages, function(rec, tr) {
            var type = (rec.isPortal()) ? Html.Span.create('patient', rec._type) : rec._type;
            var patient = rec._patient != '(None)' && rec._patient != rec.uiPostFrom() ? rec._patient : '';
            tr.select(rec.asSelector())
              .td(rec.uiPostFrom())
              .td(patient);
          })
        },
        compose_onclick:function() {
          Page.go('message.php');
        },
        Head_onchange:function(keys) {
          self.reset();
          self.dash.ajax(self).refetchMessages(keys.msgRecipient, self.load);
        }
      }
    })
  },
  _Head:function(self) {
    return {
      onchange:function(keys) {},
      //
      init:function() {
        Html.Anchor.create('Head', 'Inbox', Page.Nav.goMessages).into(self.left);
        Html.Label.create('ml5 mr5', 'for').into(self.right);
        //self.User = UserAnchorTab.create().load().into(self.right).bubble('onset', self.User_onset);
        self.User = UserSelector.create().into(self.right)
          .bubble('onupdate', self.User_onset);
      },
      load:function(keys) {
        self.keys = keys;
        self.User.load(self.keys.users).setValue(self.keys.getRecipientUser());
        self.setKeys();
      },
      setKeys:function() {
        self.keys.msgRecipient = self.User.getValue();
        return self.keys;
      },
      //
      User_onset:function() {
        if (self.User.getValue() != self.keys.msgRecipient) {
          self.onchange(self.setKeys());
        }
      }
    }
  }
}
LoginHistPop = Html.SingletonPop.aug({
  create:function() {
    return Html.Pop.create('Login History', 500).withFrameExit('Recent Logins for "' + me.uid + '"').extend(function(self) {
      return {
        init:function() {
          self.Table = Html.TableLoader.create_asBlue(self.frame).extend(self._Table);
        },
        onshow:function() {
          self.Table.reset();
          Ajax.Dashboard.getLoginHist(self.Table, function(recs) {
            self.Table.load(recs);
          })
        },
        //
        _Table:function(self) {
          return {
            init:function() {
              self.thead().trFixed().th('Date/Time').w('50%').th('IP Address').w('25%').th('Status').w('25%');
            },
            add:function(rec, tr) {
              var status = (rec.result == 1) ? 'OK' : 'Failed';
              var cls = (status == 'OK') ? '' : 'red';
              tr.td(rec.time).td(rec.ipAddress).td(status, cls);
            }
          }
        }
      }
    })
  }
})