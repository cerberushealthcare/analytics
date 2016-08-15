/**
 * IncludedSourcePop PatientSelector
 */
OldPatientSelector = {
  /*
   * @arg fn(Client) callback (optional, default behavior is navigate to selected facesheet)
   * @arg bool showUnavail (optional, to show 'unavail time' create, default false)
   * @arg bool centered (optional, default mouse position)
   * @arg string searchName (optional, to default search)
   * @arg string searchId (optional, to default search)
   */
  pop:function(callback, showUnavail, centered, searchName, searchId, onclose) {
    var self = PatientSelector;
    if (self._singleton == null) {
      var args = Array.prototype.slice.call(arguments);
      self.create(function(singleton) {
        self._singleton = singleton;
        self.pop.apply(self, args);  // call this method again now that's singleton is created
      })
    } else {
      self._singleton.pop.apply(this._singleton, arguments);
    }
  },
  pop_centered:function(callback) {
    PatientSelector.pop(callback, false, true);
  },
  pop_searchId:function(id, callback, onclose) {
    PatientSelector.pop(callback, false, true, null, id, onclose);
  },
  pop_searchName:function(name, callback, onclose) {
    PatientSelector.pop(callback, false, true, name, null, onclose);
  },
  create:function(callback) {
    Html.IncludedSourcePop.create('PatientSelector', 'pop-ps', function(self) {
      callback(self.aug({
        clients:null,
        _scb:null,
        reset:function() {
          _$('working-msg-ps').setText('');
          Html.InputText.$('search-pid').setValue('');
          Html.InputText.$('search-last').setValue('');
          Html.InputText.$('search-first').setValue('');
          Html.InputText.$('search-address').setValue('');
          Html.InputText.$('search-phone').setValue('');
          Html.InputText.$('search-email').setValue('');
          Html.InputText.$('search-custom').setValue('');
          _$('pop-ps-cap-text').setText('Patient Selector');
          _$('pop-ps-h1').setText('Search for a patient');
        },
        pop:function(callback, showUnavail, centered, searchName, searchId, onclose) {
          this.reset();
          callback = callback || function(client){Page.Nav.goFacesheet(client.clientId)};
          self.callback = callback;
          if (onclose)
            self.onclose = onclose;
          self.searchName = searchName;
          self.searchId = searchId;
          Html.Window.working(true);
          if (showUnavail) 
            _$('pop-ps-unavail').style.display = 'block';
          else 
            _$('pop-ps-unavail').style.display = 'none';
          self._scb = Ajax.buildScopedCallback(callback, 'patientSelector');
          Lookup.getClientSearchCustom(
            function(luSearch) {
              Html.Window.working(false);
              var foc = self._applySearchCustom(luSearch);
              if (centered)
                self.show(foc);
              else
                self.showPosCursor(foc);
            });
        },
        onshow:function(foc) {
          focus(foc);
          Html.InputText.$('search-pid').setValue(self.searchId);
          Html.InputText.$('search-last').setValue(self.searchName);
          if (self.clients == null) 
            self.search(foc);
        },
        psSearch:function(foc) {
          Page.workingCmd(true);
          self.search(foc);
        },
        psAdd:function() {
          PatientAdd.pop(function(rec) {
            self.close();
            self.callback(rec);
          })
        },
        search:function(foc) {
          var s_pid = Html.InputText.$('search-pid').getValue();
          var s_ln = Html.InputText.$('search-last').getValue();
          var s_fn = Html.InputText.$('search-first').getValue();
          _$('ss').show().className = 'spreadsheet workingbar';
          _$('ss-searching').show();
          _$('ss-table').hide();
          var req = {'uid':s_pid,'ln':s_ln,'fn':s_fn,'ad':Html.InputText.$('search-address').getValue(),'ph':Html.InputText.$('search-phone').getValue(),'em':Html.InputText.$('search-email').getValue(),'cu':Html.InputText.$('search-custom').getValue()};
          Ajax.get(Ajax.SVR_SCHED, 'search', req, function(clients) {
            Page.workingCmd(false);
            self.clients = clients.clients;
            self._buildResultsTable();
            _$('ss').show().className = 'spreadsheet';
            _$('ss-searching').hide();
            _$('ss-table').show(); 
            if (foc == null) {
              if (s_ln != '') {
                focus('search-last');
              } else if (s_fn != '') {
                focus('search-first');
              } else {
                focus('search-pid');
              }
            }
          });
        },
        psSelect:function(i) {
          Pop.close();
          var client = self.clients[i];
          Ajax.callScopedCallback(self._scb, client);
        },
        psCustomize:function() {
          Pop.show('pop-ccs');
        },
        ccsSave:function() {
          var rec = self._createClientSearchCustom();
          Lookup.saveClientSearchCustom(rec);
          self.resetClientSearchCustomCallback(rec);
        },
        ccsReset:function() {
          Lookup.resetClientSearchCustom(self);
        },
        searchCallback:function(clients) {
          Page.workingCmd(false);
          self.clients = clients.clients;
          self._buildResultsTable();
          _$('ss').show().className = 'spreadsheet';
          _$('ss-searching').hide();
          _$('ss-table').show();
        },
        resetClientSearchCustomCallback:function(rec) {
          Pop.close();
          _$('ss').hide();
          self._applySearchCustom(rec);
          self.search();
        },
        _createClientSearchCustom:function() {
          return {
            'first':(Html.InputCheck.$('ccs-by-id').isChecked() ? 0 : 1),
            'incAddress':Html.InputCheck.$('ccs-inc-address').isChecked(),
            'incPhone':Html.InputCheck.$('ccs-inc-phone').isChecked(),
            'incEmail':Html.InputCheck.$('ccs-inc-email').isChecked(),
            'incCustom':Html.InputCheck.$('ccs-inc-custom').isChecked()
            };
        },
        _applySearchCustom:function(luSearch) {
          var foc;
          if (luSearch.first && luSearch.first == 1) {
            _$('search-ul').insertBefore(_$('search-li-name'), _$('search-li-pid'));
            _$('ccs-by-name').checked = true;
            foc = 'search-last';
          } else {
            _$('search-ul').insertBefore(_$('search-li-pid'), _$('search-li-name'));
            _$('ccs-by-id').checked = true;
            foc = 'search-pid';
          }
          _$('search-li-more').hide();
          if (luSearch.incAddress) {
            _$('search-li-address').show();
            _$('search-li-more').show();
            Html.InputCheck.$('ccs-inc-address').setCheck(true);
          } else {
            _$('search-li-address').hide();
            Html.InputCheck.$('ccs-inc-address').setCheck(false);
          }
          if (luSearch.incPhone) {
            _$('search-li-phone').show();
            _$('search-li-more').show();
            Html.InputCheck.$('ccs-inc-phone').setCheck(true);
          } else {
            _$('search-li-phone').hide();
            Html.InputCheck.$('ccs-inc-phone').setCheck(false);
          }
          if (luSearch.incEmail) {
            _$('search-li-email').show();
            _$('search-li-more').show();
            Html.InputCheck.$('ccs-inc-email').setCheck(true);
          } else {
            _$('search-li-email').hide();
            Html.InputCheck.$('ccs-inc-email').setCheck(false);
          }
          if (luSearch.incCustom) {
            _$('search-li-custom').show();
            _$('search-li-more').show();
            Html.InputCheck.$('ccs-inc-custom').setCheck(true);
          } else {
            _$('search-li-custom').hide();
            Html.InputCheck.$('ccs-inc-custom').setCheck(false);
          }  
          return foc;
        },
        _buildResultsTable:function() {
          var clients = self.clients;
          var tbody = _$('ss-tbody');
          tbody.clean();
          var offset = false;
          if (clients != null) {
            for (var i = 0; i < clients.length; i++) {
              var c = clients[i];
              if (c.clientId > 0) {
                var onclick = self.psSelect.curry(i);
                // var href = 'javascript:PatientSelector.psSelect(' + i + ')';
                var tr = createTr(offset ? 'offset' : '');
                var id = (i == 0) ? 'ss-first-a' : null;
                var cls = (c.sex == 'M') ? 'action umale' : 'action ufemale';
                tr.appendChild(Html.Td.create().add(Html.Anchor.create('action noimg', c.uid, onclick)));
                tr.appendChild(Html.Td.create().add(Html.Anchor.create(cls, c.name, onclick)));
                tr.appendChild(createTd(c.birth));
                tbody.appendChild(tr);
                offset = ! offset;
              }
            }
          } 
          if (tbody.children.length == 0) {
            var tr = createTr('');
            var td = createTd('No patients match your criteria.');
            td.colSpan = 3;
            tr.appendChild(td);
            tbody.appendChild(tr);
            return;
          }
          _$('ss').scrollTop = 0;
        }
      }));
    });
  }
};
/**
 * RecordEntryPop PatientAdd
 */
PatientAdd = {
  /*
   * @arg fn(client) callback (optional, nav to facesheet by default)
   */
  pop:function(callback) {
    Html.Window.working(true);
    Ajax.Facesheet.Patients.getNextUid(function(uid) {
      var rec = {'uid':uid};
      Html.Window.working(false);
      Html.Pop.singleton_pop.call(PatientAdd, rec, callback);  
    })
  },
  create:function() {
    return Html.RecordEntryPop.create('Create a Patient', 740).extend(function(self) {
      return {
        onerror:self.onerror.extend(function(onerror, e) {
          self.working(false);
          if (e.type == 'PossibleDupePatientException')
            self.warnDupe(e);
          else
            onerror(e);
        }),
        buildForm:function(ef) {
          ef.li('Patient ID').textbox('uid', 15);
          ef.li('Last Name').textbox('lastName', 25).lbl('First').textbox('firstName', 15).lbl('Middle').textbox('middleName', 15);
          ef.li('Gender').select('sex', C_Client.SEXES).lbl('Birth').date('birth').start('ssnspan').lbl('SSN').textbox('cdata1', 12).end();
          if (me.userGroupId != 2645)
            ef['ssnspan'].hide();
        },
        buildCmd:function(cb) {
          cb.save(self.save_onclick, 'Create and Continue >').cancel(self.cancel_onclick);
        },
        onload:function(rec, callback) {
          self.callback = callback || function(client){Page.Nav.goFacesheet(client.clientId)};
        },
        save:function(rec, onsuccess, onerror) {
          self.rec = rec;
          Ajax.Facesheet.Patients.save(rec, onsuccess, onerror);
        },
        onsave:function(rec) {
          self.callback(rec);
        },
        warnDupe:function(e) {
          var dupe = e.data;
          Pop.Confirm.show(e.message, 'Yes, Create New Record', 'save', 'No, Open Existing Record', null, true, null, function(confirmed) {
            if (confirmed) {
              Ajax.Facesheet.Patients.saveDupe(self.rec, function(rec) {
                self.close_asSaved(rec);
              }, self.onerror);
            } else {
              self.close_asSaved(dupe);
            }
          })
        }
      }
    })
  }
}