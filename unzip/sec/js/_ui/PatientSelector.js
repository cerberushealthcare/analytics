/**
 * Pop PatientSelector
 */
PatientSelector = {
  /*
   * @arg fn onchoose (opt, default navigate to facesheet)
   * @arg bool centered (opt, default mouse pos)
   * @arg name (opt, to start default search)
   * @arg uid (opt, to start default search)
   * @arg fn onclose
   */
  pop:function(onchoose/*PatientStub*/, centered, name, uid, onclose) {
    return Html.Pop.singleton_pop.apply(PatientSelector, arguments);
  },
  pop_centered:function(onchoose, name, uid, onclose) {
    return PatientSelector.pop(onchoose, true, name, uid, onclose);
  },
  pop_searchId:function(uid, onchoose, onclose) {
    return PatientSelector.pop_centered(onchoose, null, uid, onclose);
  },
  pop_searchName:function(name, onchoose, onclose) {
    return PatientSelector.pop_centered(onchoose, name, null, onclose);
  },
  //
  create:function() {
    var pos = PatientSelector._pos;
    return Html.Pop.create('Patient Selector', 550).extend(function(self) {
      return {
        init:function() {
          self.Frame = Html.Pop.Frame.create(self.content, 'Search by name, birthdate, or ID').addClass('mb10');
          self.Table = Html.TableLoader.create_asBlue(self.content);
          self.Entry = Html.UlEntry.create(self.Frame, function(ef) {
            ef.line().l('Last Name').textbox('last', 25).l('First').textbox('first', 15)
              .line().l('Birthdate').date('dob').l('Patient ID').textbox('uid', 15)
              .line().l().check('active', 'Active Only')
          }).withOnCr(self.search);
          if (me.Role.Patient.create) {
            Html.CmdBar.create(self.Frame)
              .button('Search', self.search_onclick, 'search')
              .add('Create New Patient...', self.add_onclick)
              .cancel(self.cancel_onclick);
          } else {
            Html.CmdBar.create(self.Frame)
              .button('Search', self.search_onclick, 'search')
              .cancel(self.cancel_onclick);
          }
        },
        onpop:function(onchoose, centered, name, uid, onclose) {
          self.onchoose = onchoose;
          if (onclose)
            self.onclose = onclose;
          self.POP_POS = centered ? Pop.POS_CENTER : Pop.POS_CURSOR;
          self.reset();
        },
        onpopactivate:function() {
          self.Entry.focus();
        },
        onshow:function(onchoose, centered, name, uid) {
          if (name) 
            self.Entry.setValue('last', name);
          else if (uid)
            self.Entry.setValue('uid', uid);
          self.Entry.setValue('active', true);
          self.search();
          self.Entry.focus(uid ? 'uid' : 'last');
        },
        reset:function() {
          self.Entry.reset();
          self.Table.reset();
        },
        search:function() {
          self.Table.reset();
          var rec = PatientSearch.revive(self.Entry.getRecord());
          PatientStubs.ajax(self.Table).search(rec, function(recs) {
            self.load(recs, ! rec.isEmpty());
          })
        },
        choose:function(rec) {
          self.close();
          if (self.onchoose)
            self.onchoose(rec);
          else
            Page.Nav.goFacesheet(rec.clientId);
        },
        load:function(recs, focus) {
          self.Table._first = null;
          self.Table.load(recs, function(rec, tr) {
            var a = self.onchoose ? AnchorClient.create(rec, self.choose) : AnchorClient_Facesheet.create(rec);
            tr.td(a).td(rec.birth).td(rec.uid);
            if (focus) {
              a.setFocus();
              focus = null;
            }
          })
        },
        search_onclick:function() {
          self.search();
        },
        add_onclick:function() {
          PatientCreator.pop();
        },
        cancel_onclick:function() {
          self.close();
        }
      }
    })
  }
}
/**
 * Pop PatientCreator
 */
PatientCreator = {
  pop:function() {
    PatientAdd.ajax(Html.Window).createNew(function(rec) {
      Html.Pop.singleton_pop.call(PatientCreator, rec);
    })
  },
  create:function() {
    return Html.DirtyEntryPop.create('Create a Patient', 780).extend(function(self) {
      return {
        init:function() {
          self.Frame = Html.Pop.Frame.create(self.content);
          self.Entry = self.form = Html.UlEntry.create(self.Frame, function(ef) {
            ef.line().l('Patient ID').textbox('uid', 12).lr()
              .line().l('Last Name').textbox('lastName', 15).lr().l('First').textbox('firstName', 10).lr().l('Middle').textbox('middleName', 10).l('Nickname').textbox('nickName', 10)
              .line().l('Gender').select('sex', C_Client.SEXES).l('Birth').date('birth').lr().start('ssnspan').l('SSN').textbox('cdata1', 12).end();
          }).withOnCr(self.save);
          if (me.userGroupId != 2645)
            self.Entry.$('ssnspan').hide();
          Html.CmdBar.create(self.content)
            .save(self.save_onclick, 'Create and Continue >')
            .cancel(self.cancel_onclick);
          self.ErrorBox = Html.Pop.ErrorBox.create(self);
        },
        onpop:function(/*PatientAdd*/rec) {
          self.ErrorBox.hide();
          self.load(rec);
        },
        onerror:function(e) {
          if (e.type == 'DuplicateNameBirth')
            self.warnDupe(e);
          else
            self.ErrorBox.show(e.message);
        },
        onsave:function(rec) {
          Page.Nav.goFacesheet(rec.clientId);
        },
        save:function() {
          self.form.getRecord().ajax(self).save(self.close_asSaved, self.onerror);
        },
        saveDupe:function() {
          self.form.getRecord().ajax(self).saveDupe(self.close_asSaved, self.onerror);
        },
        warnDupe:function(e) {
          var dupe = e.data;
          Pop.Confirm.show(e.message, 'Yes, Create New Record', 'save', 'No, Open Existing Record', null, true, null, function(confirmed) {
            if (confirmed)
              self.saveDupe();
            else
              self.close_asSaved(dupe);
          })
        }
      }
    })
  }
} 
/**
 * Recs
 */
PatientAdd = Object.Rec.extend({
  /*
   uid
   lastName
   firstName
   middleName
   sex
   birth
   cdata1
   primaryPhys
   */
  //
  ajax:function(worker) {
    var self = this;
    return {
      createNew:function(callback/*PatientAdd*/) {
        Ajax.Patients.getNextUid(worker, function(uid) {
          var rec = PatientAdd.revive({'uid':uid});
          callback(rec);
        })
      },
      save:function(onsuccess, onerror) {
        Ajax.Patients.saveNew(self, worker, onsuccess, onerror);
      },
      saveDupe:function(onsuccess, onerror) {
        Ajax.Patients.saveDupe(self, worker, onsuccess, onerror);
      }
    }
  }
})
PatientSearch = Object.Rec.extend({
  /*
   last
   first
   uid
   dob
   */
  isEmpty:function() {
    return String.isBlank(this.last) && String.isBlank(this.uid) && String.isBlank(this.dob);
  }
})
PatientStub = Object.Rec.extend({
  /*
   clientId
   uid
   lastName
   firstName
   middleName
   sex
   birth
   primaryPhys
   deceased
   active
   inactiveCode
   age
   ageYears
   _score
   */
})
PatientStubs = Object.RecArray.of(PatientStub, {
  //
  getPerfectMatch:function() {
    if (this.length == 1 && this[0]._score == 100)
      return this[0];
  },
  ajax:function(worker) {
    return {
      search:function(/*PatientSearch*/rec, callback/*PatientStubs[]*/) {
        if (rec.isEmpty())
          Ajax.Patients.getMru(rec, worker, callback);
        else
          Ajax.Patients.search(rec, worker, callback);
      }
    }
  }
})
