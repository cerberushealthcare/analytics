/**
 * Profile Page
 * @author Warren Hornsby
 */
ProfilePage = page = {
  //
  start:function(query) {
    ProfileTile.create(_$('tile'));
    Page.setEvents();
  }
}
//
ProfileTile = {
  create:function(container) {
    container.clean();
    var My = this;
    return ProfileTile = Html.Tile.create(container).extend(function(self) {
      return {
        init:function() {
          self.Personal = My.Section.create_asPersonal(self).bubble('onsave', self.load);
          self.Practice = me.Role.Profile.practice && 
            My.Section.create_asPractice(self).bubble('onsave', self.load);
          self.Billing = me.Role.Profile.billing && 
            My.Section.create_asBilling(self).bubble('onsave', self.load);
          self.Accounts = me.Role.Account && me.Role.Account.manage && 
            My.Section.create_asAccounts(self).bubble('onsave', self.load);
          self.Portal = (me.Role.Account && me.Role.Account.portal || me.Role.Profile.practice) && false &&  
            My.Section.create_asPortal(self).bubble('onsave', self.load);
          Profile.ajax().get(function(rec) {
            self.load(rec);
          })
        },
        load:function(rec) {
          self.Personal.load(rec);
          self.Practice && self.Practice.load(rec);
          self.Billing && self.Billing.load(rec);
          self.Accounts && self.Accounts.load(rec);
          // self.Portal && self.Portal.load(rec);
        }
      }
    })
  },
  Section:{
    create:function(container, header) {
      var self = Html.Table2Col.create(container, Html.H2.create().html(header)).right;
      return self.aug({
        onsave:function() {},
        //
        createForm:function(builder) {
          var form = Html.UlEntry.create_asClickable(self, function(ef) {
            ef.line('fr').id('edit').append(Html.AnchorAction.create('edit2', 'Edit'));
            builder(ef);
            ef.edit.hide();
          })
          return form.aug({
            onmouseover:function() {
              form.ef.edit.show();
            },
            onmouseout:function() {
              form.ef.edit.hide();
            },
            onformclick:function(fid) {
              self.showPop(fid);
            },
            lock:form.lock.extend(function(_lock) {
              form.onmouseover = null;
              _lock();
            })
          })
        },
        //
        showPop:function(fid) {
          if (self._Pop == null)
            self._Pop = self.createPop().bubble_keep('onsave', self);
          self._Pop.pop.call(self._Pop, self.rec, fid);
        },
        createPop:function(fid) {
          return Html.DirtyPop.create('Edit ' + header).withFrame().extend(function(self) {
            return {
              onsave:function(rec) {},
              //
              init:function() {
                Html.CmdBar.asSaveCancel(self.content, self);
              },
              onshow:function(rec, fid) {
                self.Form.load(rec).focus(fid);
              },
              //
              save_onclick:function() {
                self.save();
              },
              cancel_onclick:function() {
                self.close();
              },
              isDirty:function() {
                return self.Form.isDirty();
              },
              save:function() {
                self.Form.getRecord().ajax().save(self.close_asSaved);
              },
              close_asSaved:function(rec) {
                self.close(true);
                self.onsave(rec);
              }
            }
          }).extend(self.Pop);
        }
      })
    },
    create_asPersonal:function(container) {
      return this.create(container, 'Personal Data').extend(function(self) {
        return {
          init:function() {
            self.Form = me.Role.Profile.name && self.createForm(function(ef) {
              ef.line()
                .l('Name').ro('name').l('Email').ro('email');
              me.Role.Profile.license && ef.line()
                .l('License').ro('licenseState').ro('license').l('DEA').ro('dea').l('NPI').ro('npi');
            })
            Html.UlEntry.create(self, function(ef) {
              ef.line('mt10').l().append(Html.AnchorAction.create('edit2', 'Change my password', self.pass_onclick));
            })
          },
          load:function(profile) {
            self.rec = profile.User;
            self.Form && self.Form.load(self.rec);
          },
          Pop:function(self) {
            return {
              init:function() {
                self.Form = Html.UlEntry.create(self.frame, function(ef) {
                  ef.line()
                    .l('Name').textbox('name', 25).lr()
                    .l('Email').textbox('email', 35).lr();  
                  me.Role.Profile.license && ef.line()
                    .l('License').select('licenseState', C_Address.STATES, '').textbox('license', 10)
                    .l('DEA').textbox('dea', 12)
                    .l('NPI').textbox('npi', 12);
                })
              }
            }
          },
          pass_onclick:function() {
            ChangePasswordPop.pop(me.userId);
          }
        }
      })
    },
    create_asPractice:function(container) {
      return this.create(container, 'Practice Info').extend(function(self) {
        return {
          init:function() {
            self.FormGroup = self.createForm(function(ef) {
              ef.line().l('Name').ro('name');
            })
            self.FormAddress = self.createForm(function(ef) {
              ef.line().l('Address').ro('addr1').ro('csz').l('Phone').ro('phone1').l('Fax').ro('phone2');
              ef.line().l('Timezone').ro('_estTzAdj');
            })
          },
          load:function(profile) {
            self.rec = profile.Group;
            self.FormGroup.load(self.rec);
            self.FormAddress.load(self.rec.Address);
          },
          Pop:function(self) {
            return {
              init:function() {
                self.FormGroup = Html.UlEntry.create(self.frame, function(ef) {
                  ef.line().l('Name').textbox('name', 35).lr();  
                })
                self.FormAddress = Html.UlEntry.create(self.frame, function(ef) {
                  ef.line('mt10')
                    .l('Address').textbox('addr1', 40).lr();
                  ef.line()
                    .l().textbox('addr2', 40);
                  ef.line()
                    .l('City').textbox('city', 35).lr();
                  ef.line()
                    .l('State').select('state', C_Address.STATES, '', function() {
                      var tz = C_UserGroup.TIMEZONES_BY_STATE[ef.getValue('state')];
                      if (tz !== null)
                        ef.setValue('estTzAdj', tz);
                    }).lr()
                    .l('Zip').textbox('zip', 5).lr();
                  ef.line()
                    .l('Phone').textbox('phone1', 14).lr()
                    .l('Fax').textbox('phone2', 14).lr();
                  ef.line('mt10')
                    .l('Timezone').select('estTzAdj', C_UserGroup.TIMEZONES, '');
                })
                self.Form = {
                  load:function(rec) {
                    self.FormGroup.load(rec);
                    self.FormAddress.load(rec.Address);
                    return this;
                  },
                  focus:function(fid) {
                    if (fid && fid != 'name') {
                      if (fid == 'csz')
                        fid = 'city';
                      else if (fid == '_estTzAdj')
                        fid = 'estTzAdj';
                      self.FormAddress.focus(fid);
                    } else {
                      self.FormGroup.focus(fid);
                    }
                  },
                  isDirty:function() {
                    return self.FormGroup.isDirty() || self.FormAddress.isDirty();
                  },
                  getRecord:function() {
                    self.FormAddress.applyTo();
                    return self.FormGroup.applyTo();
                  }
                }
              }
            }
          }
        }
      })
    },    
    create_asAccounts:function(container) {
      return this.create(container, 'Login Accounts').extend(function(self) {
        return {
          init:function() {
            self.Form = me.Role.Profile.practice && self.createForm(function(ef) {
              ef.line().l('Timeout').ro('sessionTimeout').ln('minutes');
            })
            me.Role.Account.manage && Html.UlEntry.create(self, function(ef) {
              ef.line('mt10').l().append(Html.AnchorAction.create('acct', 'Manage logins and permissions', self.manage_onclick));
            })
          },
          load:function(profile) {
            self.rec = profile.Group;
            self.Form && self.Form.load(self.rec);
          },
          manage_onclick:function() {
            UserManagerPop.pop();
          },
          Pop:function(self) {
            return {
              TIMEOUTS:{'60':'60 minutes','30':'30 minutes','10':'10 minutes'},
              init:function() {
                self.setCaption('Edit Timeout');
                self.Form = Html.UlEntry.create(self.frame, function(ef) {
                  ef.line().select('sessionTimeout', self.TIMEOUTS);
                })  
              },
              save:function() {
                self.Form.getRecord().ajax().saveTimeout(self.close_asSaved);
              }
            }
          }
        }
      })
    }, 
    create_asPortal:function(container) {
      return this.create(container, 'Patient Portal').extend(function(self) {
        return {
          init:function() {
            self.Form = me.Role.Profile.practice && self.createForm(function(ef) {
              ef.line().l('Status').lro('Active').l('Allow Premium').lro('Yes');
              ef.line().l('Logo').lro('(Not Set)');
              ef.line().l('Welcome Messages').ro('freetext', 'ft');
            })
            me.Role.Account.portal && Html.UlEntry.create(self, function(ef) {
              ef.line('mt10').l().append(Html.AnchorAction.create('keyg3', 'Manage portal logins', Page.go.curry('accounts.php')));
            })
          }
        }
      })
    },
    create_asBilling:function(container) {
      return this.create(container, 'Billing').extend(function(self) {
        return {
          init:function() {
            self.Form = self.createForm(function(ef) {
              ef.line().l('Source').ro('_label');
            })
          },
          load:function(profile) {
            self.rec = (profile.Bill && profile.Bill.BillSource) || {};
            if (self.Form) {
              self.Form.load(self.rec);
              if (self.rec._locked)
                self.Form.lock();
            }
          },
          showPop:self.showPop.extend(function(_showPop, fid) {
            fid = 'cardType';
            VerifyPasswordPop.pop(function() {
              _showPop(fid);
            })
          }),
          Pop:function(self) {
            return {
              init:function() {
                self.Form = Html.UlEntry.create(self.frame, function(ef) {
                  ef.line()
                      .l('Card').select('cardType', C_BillSource.CARD_TYPES, '').lr()
                      .l('Number').textbox('cardNumber', 16).lr()
                      .l('Expires').select('expMonth', C_BillSource.EXP_MONTHS, '').select('expYear', ProfileBillSource.getExpYears(), '').lr()
                    .line()
                      .l('Name').textbox('name', 40).lr()
                    .line('mt10')
                      .l('Address').textbox('addr1', 35).lr()
                    .line()
                      .l().textbox('addr2', 35)
                    .line()
                      .l('City').textbox('city', 25).lr()
                      .l('State').select('state', C_Address.STATES, '').lr()
                      .l('Zip').textbox('zip', 7).lr()
                    .line()
                      .l('Phone').textbox('phone', 22).lr()
                    .line()
                      .l('Email').textbox('email', 30).lr();
                })
              }
            }
          }
        }
      })
    }    
  }
}
