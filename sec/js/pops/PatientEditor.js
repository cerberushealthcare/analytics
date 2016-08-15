/**
 * IncludedSourcePop PatientEditor
 */
PatientEditor = {
  /*
   * @arg JClient client (null for new)
   * @arg EDIT_ popEdit (optional automatic edit pop) 
   * @callback(JClient) if any info changed (optional, default calls patientEditorCallback)
   */
  pop:function(client, popEdit, callback) {
    this.create(function(self) {
      PatientEditor = self;
      PatientEditor.pop(client, null, callback);
    })
  },
  create:function(callback) {
    Html.IncludedSourcePop.create('PatientEditor', 'pop-po', function(self) {
      callback(self.aug({
        init:function() {
          self.demoform = Html.EntryForm.create(_$('demoform'));
          self.demoform.li('Patient ID').ro('uid').lbl('Gender').ro('_sex').lbl('Birth').ro('birth').lbl('Age').ro('age').startSpan('dod').lbl('Died').ro('deceased').end();
          self.demoform.li('Race').ro('_race');
          self.demoform.li('Ethnicity').ro('_ethnicity').lbl('Language').ro('_language');
          self.demoform.li('Primary Phys').ro('_primaryPhys').start('ssnspan2').lbl('SSN').ro('cdata1').end();
          if (me.userGroupId != 2645)
            self.demoform['ssnspan2'].hide();
          if (me.pap) {
            Html.Window.getTagsByClass('pencil', 'A', self).forEach(function(e) {
              e.invisible();
            })
          } 
        },
        //
        EDIT_NONE:0,
        EDIT_DEMO:1,
        EDIT_HOME_ADDR:2,
        EDIT_ICARD:3,
        EDIT_ICARD2:4,
        EDIT_CUSTOM:5,
        EDIT_EMER_ADDR:6,
        EDIT_RX:7,
        EDIT_FATHER:8,
        EDIT_MOTHER:9,
        EDIT_SPOUSE:10,
        EDIT_RELEASE:11,
        EDIT_LIVING_WILL:12,
        EDIT_POA:13,
        EDIT_RESTRICTS:14,
        EDIT_DNR:15,
        EDIT_IMM_REG:16,
        //
        SECTION_CONTACT:1,
        SECTION_INSURANCE:2,
        SECTION_LEGAL:3,
        SECTION_FAMILY:4,
        SECTION_CUSTOM:5,
        //
        client:null,
        address:null,
        icard:null,
        changed:null,
        editOnly:null,  // popEdit
        _scb:null,
        //
        pop:function(client, popEdit, callback) {
          client.races = RacesAtab.asArray(client.race);
          client._race = RacesAtab.asDesc(client.races);
          client._language = client.Language && client.Language.engName;
          self.client = client;
          self.editOnly = (popEdit != null);
          self.changed = false;
          self._scb = Ajax.buildScopedCallback(callback, 'patientEditor');
          self._renderPo();
          if (self.client == null) {
            self.pe1Edit();      
          } else {
            if (popEdit) 
              self.popEdit(popEdit);
            else 
              self.show();
          }
        },
        popEdit:function(ix) {
          if (! me.Role.Patient.demo)
            return;
          switch (ix) {
            case PatientEditor.EDIT_DEMO:
              self.pe1Edit();
              break;
            case PatientEditor.EDIT_HOME_ADDR:
            case PatientEditor.EDIT_EMER_ADDR:
            case PatientEditor.EDIT_RX:
              self.peScroll(PatientEditor.SECTION_CONTACT);
              self.pe2Edit(ix);
              break;
            case PatientEditor.EDIT_ICARD:
            case PatientEditor.EDIT_ICARD2:
              self.peScroll(PatientEditor.SECTION_INSURANCE);
              self.pe2Edit(ix);
              break;
            case PatientEditor.EDIT_RELEASE:
            case PatientEditor.EDIT_LIVING_WILL:
            case PatientEditor.EDIT_POA:
            case PatientEditor.EDIT_RESTRICTS:
            case PatientEditor.EDIT_DNR:
            case PatientEditor.EDIT_IMM_REG:
              self.peScroll(PatientEditor.SECTION_LEGAL);
              self.pe2Edit(ix);
              break;
            case PatientEditor.EDIT_FATHER:
            case PatientEditor.EDIT_MOTHER:
            case PatientEditor.EDIT_SPOUSE:
              self.peScroll(PatientEditor.SECTION_FAMILY);
              self.pe2Edit(ix);
              break;
            case PatientEditor.EDIT_CUSTOM:
              self.peScroll(PatientEditor.SECTION_CUSTOM);
              self.pe2Edit(ix);
              break;
          }
        },
        poClose:function() {
          Pop.close();
          if (self.changed) {
            Ajax.callScopedCallback(self._scb, self.client);
          }
        },
        pe1Edit:function(focus) {
          if (! me.Role.Patient.demo)
            return;
          PeDemo.pop(self.client, function(rec) {
            self.rec = rec;
            self.changed = true;
            self.afterSave(rec);
          });
        },
        pe1Save:function() {
          self.client.uid = _$('pe1-pid').getValue();
          self.client.lastName = _$('pe1-lastName').getValue();
          self.client.middleName = _$('pe1-middleName').getValue();
          self.client.firstName = _$('pe1-firstName').getValue();
          self.client.sex = _$('pe1-sex').getValue();
          self.client.birth = _$('pe1-birth').getValue();
          if (self._showValidateErrors('pop-error-pe1', self._validateClient(self.client))) {
            return;
          } 
          if (self.client.clientId) {
            Pop.close();
          }
          self.changed = true;
          Pop.Working.show('Saving')
          Ajax.Facesheet.Patients.save(self.client, self, self);
        },
        peScroll:function(section) {
          switch (section) {
            case PatientEditor.SECTION_CONTACT:
              Html.Animator.scrollTo('csf','csf-contact');
              break;
            case PatientEditor.SECTION_INSURANCE:
              Html.Animator.scrollTo('csf','csf-insurance');
              break;
            case PatientEditor.SECTION_LEGAL:
              Html.Animator.scrollTo('csf','csf-legal');
              break;
            case PatientEditor.SECTION_FAMILY:
              Html.Animator.scrollTo('csf','csf-family');
              break;
            case PatientEditor.SECTION_CUSTOM:
              Html.Animator.scrollTo('csf','csf-custom');
              break;
          }
        },
        pe2Edit:function(i) {
          if (! me.Role.Patient.demo)
            return;
          switch (i) {
            case PatientEditor.EDIT_HOME_ADDR:
              self.pe2EditContact();
              break;
            case PatientEditor.EDIT_ICARD:
              if (me.cerberus) {
                CerberusPop.pop_asInsurance(self.client.clientId).bubble('onclose', function() {
                  Html.Window.working(true);
                  Ajax.get('Cerberus', 'refreshICards', self.client.clientId, function(rec) {
                    Html.Window.working(false);
                    self.changed = true;
                    self.afterSave(rec);
                  })
                })
              } else {
                self.pe2EditInsurance(self.client.icard, 1);
              }
              break;
            case PatientEditor.EDIT_ICARD2:
              self.pe2EditInsurance(self.client.icard2, 2);
              break;
            case PatientEditor.EDIT_CUSTOM:
              self.pe2EditCustom();
              break;
            case PatientEditor.EDIT_EMER_ADDR:
              self.pe2EditEmer();
              break;
            case PatientEditor.EDIT_RX:
              self.pe2EditPharm();
              break;
            case PatientEditor.EDIT_FATHER:
              self.pe2EditFather();
              break;
            case PatientEditor.EDIT_MOTHER:
              self.pe2EditMother();
              break;
            case PatientEditor.EDIT_SPOUSE:
              self.pe2EditSpouse();
              break;
            case PatientEditor.EDIT_RELEASE:
              PeRelease.pop(self.client, function(rec) {
                self.changed = true;
                self.afterSave(rec);
              });
              break;
            case PatientEditor.EDIT_LIVING_WILL:
              PofLivingWill.pop(self.client, function(rec) {
                self.changed = true;
                self.afterSave(rec);
              });
              break;
            case PatientEditor.EDIT_POA:
              PofPowerAttorney.pop(self.client, function(rec) {
                self.changed = true;
                self.afterSave(rec);
              });
              break;
            case PatientEditor.EDIT_RESTRICTS:
              PeRestricts.pop(self.client, function(rec) {
                self.changed = true;
                self.afterSave(rec);
              });
              break;
            case PatientEditor.EDIT_DNR:
              PeDnr.pop(self.client, function(rec) {
                self.changed = true;
                self.afterSave(rec);
              });
              break;
            case PatientEditor.EDIT_IMM_REG:
              PeImmReg.pop(self.client, function(rec) {
                self.changed = true;
                self.afterSave(rec);
              });
              break;
          }
        },
        pe2EditContact:function() {
          self.showAddress(self.client.Address_Home, false, self.client.name + " - Home Address");
        },
        pe2EditEmer:function() {
          self.showAddress(self.client.Address_Emergency, true, self.client.name + " - Emergency Contact");
        },
        pe2EditPharm:function() {
          self.showAddress(self.client.Address_Rx, true, self.client.name + " - Preferred Pharmacy");
        },
        pe2EditFather:function() {
          self.showAddress(self.client.Address_Father, true, self.client.name + " - Father");
        },
        pe2EditMother:function() {
          self.showAddress(self.client.Address_Mother, true, self.client.name + " - Mother");
        },
        pe2EditSpouse:function() {
          self.showAddress(self.client.Address_Spouse, true, self.client.name + " - Spouse");
        },
        pe2EditOnFile:function() {
          Html.InputCheck.$("pof-living-will").setCheck(self.client.livingWill);
          Html.InputCheck.$("pof-poa").setCheck(self.client.poa);
          self._rec = Json.encode(self.buildPof());
          Pop.show("pop-onfile");
        },
        pe2EditInsurance:function(icard, seq) {
          if (icard == null) {
            icard = {'clientId':self.client.clientId,'seq':seq};
            self.icard = icard;
          } else {
            self.icard = clone(icard);
          }
          Pop.setCaption("pop-icard-cap-text", self.client.name + " - Insurance Info");
          Html.InputText.$("pic-ic-plan").setValue(icard.planName);
          Html.InputText.$("pic-ic-group").setValue(icard.groupNo);
          Html.InputText.$("pic-ic-policy").setValue(icard.subscriberNo);
          Html.InputText.$("pic-ic-subscriber").setValue(icard.subscriberName);
          Html.InputText.$("pic-ic-name").setValue(icard.nameOnCard);
          Html.InputText.$("pic-ic-effective").setValue(icard.dateEffective);
          self._rec = Json.encode(self.buildICard());
          Pop.show("pop-icard", "pic-ic-plan");
        },
        pe2EditCustom:function() {
          Pop.setCaption("pop-custom-cap-text", self.client.name + " - Custom Fields");
          Html.InputText.$("pcu-custom1").setValue(self.client.cdata1);
          Html.InputText.$("pcu-custom2").setValue(self.client.cdata2);
          Html.InputText.$("pcu-custom3").setValue(self.client.cdata3);
          self._rec = Json.encode(self.buildCustoms());
          Pop.show("pop-custom", "pcu-custom1");  
        },
        pe2SaveAddress:function(a) {
          var data = {'address':a,'id':self.client.clientId};
          self._postSave('saveAddress', data);
        },
        pcuSave:function() {
          self.client.cdata1 = value("pcu-custom1");
          self.client.cdata2 = value("pcu-custom2");
          self.client.cdata3 = value("pcu-custom3");
          self._postSave('save', self.client);
        },
        pcuClose:function() {
          var rec = Json.encode(self.buildCustoms());
          if (rec != self._rec) {
            Pop.Confirm.showDirtyExit(function() {
              self.pcuSave();
            });
          } else {
            Pop.close();
          }
        },
        buildCustoms:function() {
          var c = {};
          c.cdata1 = value("pcu-custom1");
          c.cdata2 = value("pcu-custom2");
          c.cdata3 = value("pcu-custom3");
          return c;
        },
        pofSave:function() {
          self.client.livingWill = _$("pof-living-will").checked;
          self.client.poa = _$("pof-poa").checked;
          self._postSave('save', self.client);
        },
        pofClose:function() {
          var rec = Json.encode(self.buildPof());
          if (rec != self._rec) {
            Pop.Confirm.showDirtyExit(function() {
              self.pofSave();
            });
          } else {
            Pop.close();
          }
        },
        buildPof:function() {
          var c = {};
          c.livingWill = _$("pof-living-will").checked;
          c.poa = _$("pof-poa").checked;
          return c;
        },
        picSave:function() {
          var c = self.buildICard();
          var data = {'icard':c,'id':self.client.clientId};
          self._postSave('saveICard', data);
        },
        buildICard:function() {
          var c = self.icard;
          c.planName = value("pic-ic-plan");
          c.groupNo = value("pic-ic-group");
          c.subscriberNo = value("pic-ic-policy");
          c.subscriberName = value("pic-ic-subscriber");
          c.nameOnCard = value("pic-ic-name");
          c.dateEffective = nullify(value("pic-ic-effective"));
          return c;
        },
        picClose:function() { 
          var rec = Json.encode(self.buildICard());
          if (rec != self._rec) {
            Pop.Confirm.showDirtyExit(function() {
              self.picSave();
            });
          } else {
            Pop.close();
          }
        },
        _postSave:function(action, data) {
          self.changed = true;
          Pop.close();
          Pop.Working.show('Saving');
          Ajax.Facesheet.Patients[action](data, [self.savePatientCallback, self], [self.savePatientError, self]);
        },
        showAddress:function(a, includeName, cap) {
          self.Address_ = clone(a);
          Pop.setCaption("pop-addr-cap-text", cap ? cap : "Address");
          Html.InputText.$("pa-name").setValue(a.name);
          Html.InputText.$("pa-addr1").setValue(a.addr1);  
          Html.InputText.$("pa-addr2").setValue(a.addr2);  
          Html.InputText.$("pa-addr3").setValue(a.addr3);  
          Html.InputText.$("pa-city").setValue(a.city);
          Html.Select.$("pa-state").setValue(a.state);
          Html.InputText.$("pa-zip").setValue(a.zip);
          Html.InputText.$("pa-county").setValue(a.county);
          Html.InputText.$("pa-phone1").setValue(a.phone1); 
          Html.Select.$("pa-phone1Type").setValue(a.phone1Type); 
          Html.InputText.$("pa-phone2").setValue(a.phone2);
          Html.Select.$("pa-phone2Type").setValue(a.phone2Type); 
          Html.InputText.$("pa-phone3").setValue(a.phone3);
          Html.Select.$("pa-phone3Type").setValue(a.phone3Type);  
          Html.InputText.$("pa-email1").setValue(a.email1);
          _$("pa-li-name").showIf(includeName); 
          self._rec = Json.encode(self.buildAddress());
          Pop.show("pop-addr", includeName ? "pa-name" : "pa-addr1");
        },
        paSave:function() {
          self.pe2SaveAddress(self.buildAddress());
        },
        buildAddress:function() {
          var a = self.Address_;
          a.name = _$("pa-name").getValue();
          a.addr1 = _$("pa-addr1").getValue();
          a.addr2 = _$("pa-addr2").getValue();
          a.addr3 = _$("pa-addr3").getValue();
          a.city = _$("pa-city").getValue();
          a.state = _$("pa-state").getValue();
          a.zip = _$("pa-zip").getValue();
          a.county = _$('pa-county').getValue();
          a.phone1 = _$("pa-phone1").getValue();
          a.phone1Type = _$("pa-phone1Type").getValue();
          a.phone2 = _$("pa-phone2").getValue();
          a.phone2Type = _$("pa-phone2Type").getValue();
          a.phone3 = _$("pa-phone3").getValue();
          a.phone3Type = _$("pa-phone3Type").getValue();
          a.email1 = _$("pa-email1").getValue();
          return a;
        },
        paClose:function() {
          var rec = Json.encode(self.buildAddress());
          if (rec != self._rec) {
            Pop.Confirm.showDirtyExit(function() {
              self.paSave();
            });
          } else {
            Pop.close();
          }
        },
        savePatientCallback:function(client) {
          if (! self.editOnly)
            Pop.close();
            self.afterSave(client);
        },
        afterSave:function(client) {
          if (self.editOnly) {
            self.poClose();
            return;
          }
          if (self.client.clientId) {
            self.client = client;
            self._renderPo();
          } else {
            Page.go(Page.PAGE_FACESHEET, {'id':self.client.clientId,'pe':1});
          }
        },
        savePatientError:function(e) {
          if (e.type == 'ClientUidExistsException') {
            Pop.Working.close();
            var dupe = e.message;
            var html = '<b>A patient with ID ' + dupe.uid + ' already exists:</b>';
            html += '<br/>' + dupe.name + ' (DOB: ' + (dupe.birth || '') + ')';
            showError('pop-error-pe1', html);
          } else {
            Page.showAjaxError(e);
          }
        },
        _renderPo:function() {
          self.demoform.setRecord(self.client);
          self.demoform.showIf('dod', self.client.deceased);
          var pl;
          _$('po-name').setText(self.client.name);
          pl = new ProfileLoader('po-lbl-address', 'po-address');
          self._renderAddress('Home', pl, self.client.Address_Home);
          pl = new ProfileLoader('po-lbl-emer', 'po-emer');
          self._renderAddress('Emergency', pl, self.client.Address_Emergency);
          pl = new ProfileLoader('po-lbl-pharm', 'po-pharm');
          self._renderAddress('Pharmacy', pl, self.client.Address_Rx);
          pl = new ProfileLoader('po-lbl-ins', 'po-ins');
          if (me.cerberus) {
            _$('po-ins-ul-2').hide();
            self._poLoadExtInsurances(pl, self.client.ICards);
            //  
          } else {
            self._setIcards(self.client.ICards);
            self._poLoadInsurance(pl, self.client.icard);
            pl = new ProfileLoader('po-lbl-ins2', 'po-ins2');
            self._poLoadInsurance(pl, self.client.icard2);
          }
          pl = new ProfileLoader('po-lbl-famrel', 'po-famrel');
          pl.add('Family Release', self.client.familyRelease);
          pl.add('Preference', self.client._releasePref);
          pl = new ProfileLoader('po-lbl-ichecks1', 'po-ichecks1');
          pl.add('Living Will?', String.yesNo(self.client.livingWill));  
          pl = new ProfileLoader('po-lbl-ichecks2', 'po-ichecks2');
          pl.add('POA?', String.yesNo(self.client.poa));  
          pl = new ProfileLoader('po-lbl-restricts', 'po-restricts');
          pl.add('Chart Restrictions', self.client._userRestricts);
          pl = new ProfileLoader('po-lbl-dnr', 'po-dnr');
          pl.add('DNR Orders', self.client._dnr);
          pl = new ProfileLoader('po-lbl-immreg', 'po-immreg');
          pl.add('Immun Registry', self.client._immReg);
          pl = new ProfileLoader('po-lbl-father', 'po-father');
          self._renderAddress('Father', pl, self.client.Address_Father);  
          pl = new ProfileLoader('po-lbl-mother', 'po-mother');
          self._renderAddress('Mother', pl, self.client.Address_Mother);  
          pl = new ProfileLoader('po-lbl-spouse', 'po-spouse');
          self._renderAddress('Spouse', pl, self.client.Address_Spouse);  
          pl = new ProfileLoader('po-lbl-custom', 'po-custom');
          var clabel1 = (me.userGroupId == 3 || me.userGroupId == 3013 || me.userGroupId == 3017 || me.userGroupId == 3018) ? 'SSN' : 'Custom 1';
          _$('pcu-clabel1').setText(clabel1);
          pl.add(clabel1, self.client.cdata1);  
          pl.add('Custom 2', self.client.cdata2, -1);  
          pl.add('Custom 3', self.client.cdata3, -1);    
        },
        _setIcards:function(icards) {
          self.client.icard = null;
          self.client.icard2 = null;
          if (icards == null)
            return;
          for (var i = 0; i < icards.length; i++) {
            icard = icards[i];
            if (icard.seq == 2)
              self.client.icard2 = icard;
            else
              self.client.icard = icard;
          } 
        },
        _renderAddress:function(lbl, pl, a) {
          var addr = (a) ? [a.addr1, a.addr2, a.csz] : '';
          if (a && a.name) {
            addr.unshift(a.name);
          }
          pl.add(lbl, addr);
          if (a && a.phone1) {
            pl.add('', [AddressUi.formatPhone(a.phone1, a.phone1Type), AddressUi.formatPhone(a.phone2, a.phone2Type), AddressUi.formatPhone(a.phone3, a.phone3Type)], -1);
          }
          if (a && a.email1) {
            pl.add('', [a.email1, a.email2], -1);
          }  
        },
        _poLoadInsurance:function(pl, icard) {
          pl.add('Plan', icard ? icard.planName : '');
          pl.add('Group/Policy #', icard ? (icard.groupNo || '') + ' ' + (icard.subscriberNo || '') : '');
          pl.add('Effective', icard ? icard.dateEffective : '');
          if (icard && icard.subscriber) {
            pl.add('Subscriber', icard.subscriber.name, -1);
          }
          if (icard && icard.nameOnCard) {
            pl.add('Name on Card', icard.nameOnCard);
          }
        }, 
        _poLoadExtInsurances:function(pl, icards) {
          var ext;
          icards.each(function(ic) {
            if (ext && ic.external)
              pl.add('','');
            if (ic.external) {
              ext = ic.external;
              pl.add('Seq', ext.BILLING_SEQ);
              pl.add('Carrier/Policy', ext.PAYER + ' / ' + ext.POLICY);
              pl.add('Effective', ext.EFF_DATE + (ext.END_DATE ? '-' + ext.END_DATE : ''));
              pl.add('Eligibility', ext.ELIGIBILITY);
              var copay = ext.COPAY ? ext.COPAY : '';
              var coins = ext.COINSURANCE ? ext.COINSURANCE : '';
              if (ext.COPAY || ext.COINSURANCE)
                pl.add('CoPay/CoIns', copay + ' / ' + coins);
            }
          })
          if (ext == null)
            pl.add('','');
        },
        _poFormatSex:function(sex) {
          return (sex == 'F') ? 'Female' : 'Male';
        },
        _validateClient:function(client) {
          var errs = [];
          if (client.uid == '') {
            errs.push(errMsg('client.uid', msgReq('Patient ID')));
          }
          if (client.lastName == '') {
            errs.push(errMsg('client.lastName', msgReq('Last name')));
          }
          if (client.firstName == '') {
            errs.push(errMsg('client.firstName', msgReq('First name')));
          }
          if (client.sex == '') {
            errs.push(errMsg('client.sex', msgReq('Gender')));
          }
          if (client.birth) {
            client.birth = DateUi.validate(client.birth); 
            if (! client.birth) {
              errs.push(errMsg('client.birth', 'Birth date is not valid. Please use MM/DD/YYYY.'));
            }
          }
          return errs;
        },
        _showValidateErrors:function(id, errMsgs) {
          _$(id).hide();
          if (errMsgs.length > 0) {
            showErrors(id, errMsgs);
            focusError(errMsgs[0].id);
            return true;
          }
        },
        _focusError:function(id) {
          if (id == 'client.uid') {
            focus('pid');
          } else if (id == 'client.firstName') {
            focus('firstName');
          } else if (id == 'client.lastName') {
            focus('lastName');
          } else if (id == 'client.sex') {
            focus('sex');
          }
        }
      }));
    });
  }
}
/**
 * RecordEntryPop PatientEntry
 */
PatientEntry = {
  /*
   * @callback(Rec) updated
   */
  create:function(callback, caption, width, frameCaption) {
    var self = Html.RecordEntryPop.create(caption || 'Patient Entry', width || 700, frameCaption);
    return self.aug({
      onbeforesave:function(rec) {},
      onsave:callback,
      save:function(rec, onsuccess, onerror) {
        self.onbeforesave(rec);
        rec.race = RacesAtab.asInt(rec.races);
        Ajax.Facesheet.Patients.save(rec, function(rec) {
          rec.races = RacesAtab.asArray(rec.race);
          rec._race = RacesAtab.asDesc(rec.races);
          rec._language = rec.Language && rec.Language.engName;
          onsuccess(rec);
        }, onerror); 
      }
    });
  }    
}
PeDemo = {
  pop:function(rec, callback) {
    PeDemo = this.create(callback).pop(rec);
  },
  create:function(callback) {
    var self = PatientEntry.create(callback, 'Patient Fields', 780);
    return self.aug({
      buildForm:function(ef) {
        ef.aug({
          onload:function() {
            ef.draw();
          },
          active_oncheck:function() {
            ef.draw();
          },
          draw:function() {
            ef.showIf('inact', ! ef.getValue('active'));
          }
        })
        ef.li('Patient ID').textbox('uid', 15).lbl('').check('active', 'Active', ef.active_oncheck).startSpan('inact').lbl('Inactive Reason').select('inactiveCode', C_Client.INACTIVE_CODES, '').end();;
        ef.li('Last Name', 'mt10').textbox('lastName', 20).lbl('First').textbox('firstName', 10).lbl('Middle').textbox('middleName', 10).lbl('Nickname').textbox('nickName', 10);
        ef.li('Gender').select('sex', C_Client.SEXES).lbl('Birth').date('birth').lbl('Gest Age').select('gestAge', this.getWeeks()).lbl('Death').date('deceased');
        ef.li('Race', 'mt10').atab('races', RacesAtab);
        ef.li('Ethnicity').select('ethnicity', C_Client.ETHNICITIES, '', null, true).lbl('Language').pick('language', 'Language', LangPicker);
        ef.li('Primary Phys', 'mt10').select('primaryPhys', C_Docs, '').start('ssnspan').lbl('SSN').textbox('cdata1', 12).end();
        if (me.userGroupId != 2645)
          ef['ssnspan'].hide();
      },
      getWeeks:function() {
        var o = {'':'', '0':'40 weeks (full-term)'};
        for (var i = 1; i < 21; i++) 
          o[i] = (40 - i) + ' weeks';
        return o;
      }
    });
  }
}
Lang = Object.Rec.extend({
  //
})
Langs = Object.RecArray.of(Lang, {
  isMatch:function(rec, search) {
    return rec.engName.match(search);
  },
  lev:function(rec, search) {
    return search.lev(rec.engName);
  },
  //
  ajax:function() {
    var self = this;
    return {
      fetchAll:function(callback) {
        self.ajax_fetchAll(Ajax.Patients.getLangs, callback);
      },
      fetchMatches:function(text, callback) {
        self.ajax_fetchMatches(this.fetchAll, text, callback);
      }
    }
  }
})
LangPickerPop = {
  //
  pop:function(value, text) {
    return Html.Pop.singleton_pop.apply(LangPickerPop, arguments);
  },
  create:function() {
    var My = this;
    return Html.PickerPop.create('Language Selector').extend(My, function(self, parent) {
      return {
        POP_POS:Pop.POS_CENTER,
        onselect:function(rec) {},
        //
        init:function() {
          self.table.thead().tr('fixed head').th('Name').w('80%').th('Code').w('20%');
        },
        cmdbar_buttons:function(cb) {
          cb.cancel(self.close);
        },
        table_fetch:function(callback_recs) {
          Ajax.Patients.getLangs(callback_recs);
        },
        table_applies:function(rec, search) {
          if (search)
            return rec.engName.match(search);
          else
            return true;
        },
        table_add:function(rec, tr) {
          tr.select(rec, LangAnchor.create(rec)).td(rec.alpha3Code);
        }
      }
    })
  }
}
LangPicker = {
  create:function() {
    return Html.RecPicker.create(26, LangPickerPop).extend(function(self) {
      return {
        init:function() {
          self.input.aug({
            fetch:function(value, callback) {
              Langs.ajax().fetchMatches(value, callback);
            },
            Anchor:LangAnchor
          })
        },
        getValueFrom:function(rec) {
          return rec.isolangId;
        },
        getTextFrom:function(rec) {
          return rec.engName;
        }
      }
    })
  }
}
LangAnchor = {
  create:function(rec) {
    return Html.AnchorAction.asSelect(rec.engName);
  }  
}
RacesAtab = {
  create:function() {
    return Html.AnchorTab.create().checks(C_Client.RACES, 1).okCancel();
  },
  asDesc:function(races) {
    var descs = [];
    for (var i = 0; i < races.length; i++) {
      descs.push(C_Client.RACES[races[i]]);
    }
    return descs.join(', ');
  },
  asArray:function(race) {
    if (Array.is(race))
      return race;
    race = String.toInt(race);
    if (race == -1)
      return [-1];
    var a = [];
    for (var i in C_Client.RACES) {
      i = String.toInt(i);
      if (i > -1) {
        if (race & i)
          a.push(i);
      }
    }
    return a;
  },
  asInt:function(races) {
    if (! races.length)
      return races;
    var v = 0, j;
    for (var i = 0; i < races.length; i++) {
      j = String.toInt(races[i]);
      if (j == -1) {
        if (races.length == 1)
          return j;
      } else {
        v += j;
      }
    }
    return v;
  }
}
PeRelease = {
  pop:function(rec, callback) {
    PeRelease = this.create(callback).pop(rec);
  },
  create:function(callback) {
    var self = PatientEntry.create(callback, null, null, 'Confidential Information Release');
    return self.aug({
      buildForm:function(ef) {
        ef.aug({
          releasePref_onchange:function() {
            if (ef.getValue('releasePref')) {
              ef.showField('release');
              ef.focus('release');
            } else {
              ef.hideField('release');
            }
          }
        });
        ef.li('Family Members Approved').textarea('familyRelease', 3);
        ef.li('Prefer By', 'mt10').select('releasePref', C_Client.RELEASE_PREFS, '(No Preference)', ef.releasePref_onchange);
        ef.li(' ').textarea('release', 3);
      }
    });
  }
}
PeRestricts = {
  pop:function(rec, callback) {
    PeRestricts = this.create(callback).pop(rec);
  },
  create:function(callback) {
    var self = PatientEntry.create(callback, null, null, 'Chart Access Restrictions');
    return self.aug({
      buildForm:function(ef) {
        ef.li('Deny To').atab('userRestricts', UserAtab);
      },
      onerror:self.onerror.extend(function(_onerror, e) {
        if (e.type == 'RestrictedChartException') {  // just finished restricting yourself
          Page.Nav.goFacesheet(self.rec.clientId);
        } else {
          _onerror(e);
        }
      })
    });
  }
}
PeDnr = {
  pop:function(rec, callback) {
    PeDnr = this.create(callback).pop(rec);
  },
  create:function(callback) {
    var self = PatientEntry.create(callback, null, null, 'DNR Orders');
    return self.aug({
      buildForm:function(ef) {
        ef.aug({
          dnrc_onchange:function() {
            var show = ef.getValue('_dnrc') == 'S'; 
            ef['dnr1'].showIf(show);
            ef['dnr2'].showIf(show);
            ef['dnr3'].showIf(show);
            ef['dnr4'].showIf(show);
            ef['dnr5'].showIf(show);
          }
        })
        ef.li('Code').select('_dnrc', C_Client.DNR_CODES, '', ef.dnrc_onchange);
        ef.li(' ').start('dnr1').check('_dnr1', C_Client.DNR_INTS['1']).end();
        ef.li(' ').start('dnr2').check('_dnr2', C_Client.DNR_INTS['2']).end();
        ef.li(' ').start('dnr3').check('_dnr3', C_Client.DNR_INTS['3']).end();
        ef.li(' ').start('dnr4').check('_dnr4', C_Client.DNR_INTS['4']).end();
        ef.li(' ').start('dnr5').check('_dnr5', C_Client.DNR_INTS['5']).end();
      },
      onbeforesave:function(rec) {
        switch (rec._dnrc) {
        case 'D':
          rec.dnr = 'DNR';
          break;
        case 'F':
          rec.dnr = 'FC';
          break;
        default:
          rec.dnr = '';
          for (var i = 1; i <= 5; i++) {
            if (rec['_dnr' + i]) {
              rec.dnr += i;
            }
          }
        }
      }
    });
  }
}
PeImmReg = {
  pop:function(rec, callback) {
    PeImmReg = this.create(callback).pop(rec);
  },
  create:function(callback) {
    var self = PatientEntry.create(callback, null, null, 'Immunization Registry');
    return self.aug({
      buildForm:function(ef) {
        ef.li('Reminders').select('immRegReminders', C_Client.IR_REMINDERS, '');
        ef.li('Refuse registry').select('immRegRefuse', C_Client.IR_REFUSES, '');
      }
    });
  }
}
UserAtab = {
  create:function() {
    return Html.AnchorTab.create().checks(C_Users, 3).okCancel();
  }
}
PofLivingWill = {
  pop:function(rec, callback) {
    PofLivingWill = this.create(callback).pop(rec);
  },
  create:function(callback) {
    var self = PatientEntry.create(callback, null, 450);
    return self.aug({
      init:function() {
        self.list = PofLivingWill.ScanList.create(self).hide(); 
      },
      getFid:function() {
        return 'livingWill';
      },
      getLabel:function() {
        return 'Living Will on File?';
      },
      buildForm:function(ef) {
        ef.li().check(self.getFid(), self.getLabel(), self.check_onclick);
      },
      check_onclick:function(lc) {
        //self.list.showIf(lc.isChecked());
      }
    });
  },
  ScanList:{
    create:function(pop) {
      var self = Html.Div.create('EntryFolderList').into(pop.frame);
      return self;
    }
  }
}
PofPowerAttorney = {
  pop:function(rec, callback) {
    PofPowerAttorney = this.create(callback).pop(rec);
  },
  create:function(callback) {
    var self = PatientEntry.create(callback, null, 450);
    return self.aug({
      getFid:function() {
        return 'poa';
      },
      getLabel:function() {
        return 'Power of Attorney?';
      },
      buildForm:function(ef) {
        ef.li().check(self.getFid(), self.getLabel(), self.check_onclick);
      },
      check_onclick:function(lc) {
        //self.list.showIf(lc.isChecked());
      }
    });
  }
}
