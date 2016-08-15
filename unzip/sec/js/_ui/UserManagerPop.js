UserManagerPop = Html.SingletonPop.aug({
  create:function() {
    return Html.Pop.create('Login Management').extend(function(self) {
      return {
        //
        init:function() {
          var height = self.fullscreen(1000, 600);
          self.Tile = UserManagerTile.create(self.content, height).bubble('oncancel', self.close);
        },
        onshow:function() {
          self.Tile.load();
        }
      }
    })
  }
})
UserManagerTile = {
  create:function(container, height) {
    return Html.Tile.create(container, 'UserManager').extend(function(self) {
      return {
        oncancel:function() {},
        //
        init:function() {
          self.Table = Html.TableLoader.create(self).setHeight(height).extend(UserManagerTile._Table)
            .bubble('onsave', self.load);
          Html.CmdBar.create(self)
            .add('New Support...', self.add_onclick)
            .exit(Function.defer(self, 'oncancel'));
        },
        load:function() {
          self.reset();
          Users.ajax(self).fetch(function(recs) {
            self.recs = recs;
            self.Table.load(self.recs);
          })
        },
        //
        reset:function() {
          self.recs = null;
          self.Table.reset();
        },
        add_onclick:function() {
          UserEditor_NewSupport.pop(User.asNew()).bubble('onsave', function(rec) {
            self.load();
            Pop.Confirm.showYesNo('Do you want to set up electronic prescribing for ' + rec.name + '?', function() {
              UserEditor.pop_forErx(rec).bubble('onsave', self.load);
            })
          })
        }
      }
    })
  },
  _Table:function(self) {
    return {
      onsave:function() {},
      //
      init:function() {
        self.thead().trFixed()
          .th('Login').th('Name').th('Type').th('Additional Permissions').th('ERx');
      },
      add:function(rec, tr) {
        tr.select(AnchorLogin)
          .a(rec.uiName())
          .a(rec.uiRoleType())
          .a(rec.uiMixins())
          .a(rec.uiErx());
      },
      onselect:function(rec, label) {
        if (! rec.isActive())
          self.confirm_activate(rec);
        else
          UserEditor.pop(rec, label).bubble('onsave', self);
      },
      confirm_activate:function(rec) {
        Pop.Confirm.showYesNo('This account is currently inactive. Do you want to re-activate it?', function() {
          rec.ajax(self).activate(function(rec) {
            self.onsave();
            self.onselect(rec);
          })
        })
      }
    }
  }
}
UserEditor = {
  pop:function(rec, label) {
    var Pop = rec.isProvider() ? UserEditor_Doctor : UserEditor_Support;
    return Pop.pop(rec, label);
  },
  pop_forErx:function(rec) {
    return UserEditor.pop(rec, 'ERx');
  },
  create:function() {
    return Html.DirtyEntryPop.create('User Entry', 550).extend(function(self) {
      return {
        onsave:function(rec) {},
        //
        init:function() {
          self.top = Html.Tile.create(self.content);
          self.form = Html.UlEntries([
            Html.UlEntry.create(Html.Pop.Frame.create(self.content, 'Additional Permissions').addClass('mt10'), function(ef) {
              ef.line('ml10').check('mix_ad', 'Account Administrator');
              ef.line('ml10').check('mix_pa', 'Credit Card Management');
              ef.line('ml10').check('mix_au', 'Audit Reports');
              ef.line('ml10').check('mix_rb', 'Report Builder');
              me.cerberus && ef.line('ml10').check('mix_bi', 'Patient Billing');
            }),
            Html.UlEntry.create(Html.Pop.Frame.create(self.erxTile = Html.Tile.create(self.content), 'Electronic Prescribing').addClass('mt10'), function(ef) {
              ef.line('ml10').append(self.erx = Html.AnchorAction.asEdit('Edit', Function.defer(self, 'erx_onclick')));
            }).bubble('onload', self.erx_onload)])
          self.cb = Html.Pop.CmdBar.create(self).addClass('mt20')
            .save(self.save_onclick)
            .del(self.del_onclick, 'Deactivate')
            .exit(self.close);
          //self.cb = Html.Pop.CmdBar.create(self).addClass('mt20').save(self.save_onclick).del(self.del_onclick, 'Deactivate').button('Reset Password', self.reset_onclick, null, 'reset').cancel(self.close);
        },
        onshow:function(rec, label) {
          var fid = self.getFid(label);
          if (fid == 'ERx')
            self.erx_onclick();
          else
            self.form.focus(fid);
        },
        erx_onload:function() {
          self.erx.setText(self.rec.uiErx());
          self.cb.showDelIf(! self.rec.isPrimary() && ! self.rec.isNew());
        },
        del_onclick:function() {
          Pop.Confirm.showYesNo('Are you sure you want to deactivate this account?', self.remove);
        },
        save:function() {
          self.form.getRecord().ajax(self).save(self.close_asSaved);
        },
        remove:function() {
          self.rec.ajax(self).deactivate(self.close_asSaved);
        },
        getFid:function(label) {
          switch (label) {
            case 'Type':
              return 'roleType';
            case 'ERx':
              return 'ERx';
            case 'Additional Permissions':
              return 'mix_ad';
            default:
              return 'name';
          }
        },
        erx_onclick:function() {
          var Pop = self.getErxPop();
          Pop.pop(self.rec)
            .bubble('onsave', self.erx_onsave)
            .bubble('ondelete', self.erx_ondelete);
        },
        erx_onsave:function(ncuser) {
          self.rec.NcUser = ncuser;
          self.load(self.rec);
          self.onsave(self.rec);
        },
        erx_ondelete:function(id) {
          self.erx_onsave(null);
        },
        reset_onclick:function() {
          
        }
      }
    })
  }
}
UserEditor_Doctor = Html.SingletonPop.aug({
  create:function() {
    return UserEditor.create().extend(function(self) {
      return {
        //
        init:function() {
          self.form.unshift(
            Html.UlEntry.create(Html.Pop.Frame.create(self.top, 'Provider Account'), function(ef) {
              ef.line().l('Login').ro('uid').l().check('primary', 'Primary Provider');
              ef.line().l('Name').textbox('name', 40).lr();
              ef.line().l('Email').textbox('email', 50).lr();
            }))
        },
        getErxPop:function() {
          return NcUserEditor_Doctor;
        }
      }
    })
  }
}) 
UserEditor_Support = Html.SingletonPop.aug({
  create:function() {
    return UserEditor.create().extend(function(self) {
      return {
        //
        init:function() {
          self.form.unshift(
            Html.UlEntry.create(Html.Pop.Frame.create(self.top, 'Support Account'), function(ef) {
              ef.line().l('Login').ro('uid').l('Type').select('roleType', C_UserRole.SUPPORT_TYPES);
              ef.line().l('Name').textbox('name', 40).lr();
              ef.line().l('Email').textbox('email', 50).lr();
            }))
        },
        getErxPop:function() {
          return NcUserEditor_Staff;
        }
      }
    })
  }
})
UserEditor_NewSupport = Html.SingletonPop.aug({
  create:function() {
    return UserEditor.create().extend(function(self) {
      return {
        //
        init:function() {
          self.erxTile.hide();
          self.form.unshift(
            Html.UlEntry.create(Html.Pop.Frame.create(self.top, 'New Support Account'), function(ef) {
              ef.line().l('Login').textbox('uid', 12).lr().l('Type').select('roleType', C_UserRole.SUPPORT_TYPES);
              ef.line().l('Password').textbox('pw', 20).lr();
              ef.line().l('Name').textbox('name', 40).lr();
              ef.line().l('Email').textbox('email', 50).lr();
            }))
        },
        onshow:function(rec, label) {
          //self.cb.showButtonIf('reset', false);
          self.form.focus('uid');
        }
      }
    })
  }
})
NcUserEditor = {
  create:function() {
    return Html.DirtyEntryPop.create('Electronic Rx', 450).extend(function(self) {
      return {
        onsave:function(rec) {},
        ondelete:function(id) {},
        //
        init:function() {
          self.form = Html.UlEntries([
            Html.UlEntry.create(Html.Pop.Frame.create(self.content, 'Name'), function(ef) {
              ef.line().l('Prefix').select('namePrefix', C_NcUser.PREFIXES, '');
              ef.line().l('First').textbox('nameFirst', 20).lr();
              ef.line().l('Middle').textbox('nameMiddle', 18);
              ef.line().l('Last').textbox('nameLast', 24).lr();
              ef.line().l('Suffix').select('nameSuffix', C_NcUser.SUFFIXES, '').textbox('freeformCred', 10);
            })])
          self.frame = Html.Pop.Frame.create(self.content, 'Access').addClass('mt10');
          self.cb = Html.Pop.CmdBar.create(self).addClass('mt20').saveDelCancel(null, null, 'Remove Access');
        },
        onbeforeload:function(user) {
          return user.NcUser || NcUser.asNew(user.userId);
        },
        onshow:function() {
          self.cb.showDelIf(! self.rec.isNew());
          self.form.focus('nameFirst');
        },
        del_onclick:function() {
          Pop.Confirm.showYesNo('Are you sure you want to remove ERx access for this user?', self.remove);
        }
      }
    })
  }
}
NcUserEditor_Doctor = Html.SingletonPop.aug({
  create:function() {
    return NcUserEditor.create().extend(function(self) {
      return {
        //
        init:function() {
          self.form.push(
            Html.UlEntry.create(self.frame, function(ef) {
              ef.line().l('Type').select('userType', C_NcUser.PROVIDER_USER_TYPES);
              ef.line().l('Partner').select('partnerId', C_Users, '');
            }))
        }
      }
    })
  }
})
NcUserEditor_Staff = Html.SingletonPop.aug({
  create:function() {
    return NcUserEditor.create().extend(function(self) {
      return {
        //
        init:function() {
          self.form.push(
            Html.UlEntry.create(self.frame, function(ef) {
              ef.line().l('Type').select('roleType', C_NcUser.STAFF_ROLE_TYPES);
            }))
        }
      }
    })
  }
})
/*
 * Recs
 */
User = Object.Rec.extend({
  /*
   userId
   uid
   name
   active
   userGroupId
   email
   roleType
   Mixins mixins
   NcUser NcUser
   */
  onload:function() {
    this.setr('NcUser', NcUser);
    this.primary = this.isPrimary();
    if (this.primary) 
      this.mixins = Mixins.asPrimary();
    else 
      this.setr('mixins', Mixins);
    this.setMixinFlags(this.mixins);
  },
  onsave:function() {
    if (this.isProvider())  
      this.roleType = (this.primary) ? C_UserRole.TYPE_PROVIDER_PRIMARY : C_UserRole.TYPE_PROVIDER;
  },
  isActive:function() {
    return this.active;
  },
  isProvider:function() {
    return this.roleType == C_UserRole.TYPE_PROVIDER_PRIMARY || this.roleType == C_UserRole.TYPE_PROVIDER;
  },
  isPrimary:function() {
    return this.roleType == C_UserRole.TYPE_PROVIDER_PRIMARY;
  },
  isSupport:function() {
    return ! this.isProvider();
  },
  isNew:function() {
    return this._new;
  },
  uiName:function() {
    return this.isActive() ? this.name : this.name + ' (Inactive)';
  },
  uiRoleType:function() {
    if (this.isActive()) 
      return this._roleType;
  },
  uiMixins:function() {
    if (this.isActive()) {
      if (this.mixins) 
        return this.mixins.ui().join(' &bull; ');
    }
  },
  uiErx:function() {
    if (this.isActive()) {
      if (this.NcUser)
        return this.NcUser.ui();
      else
        return 'No';
    }
  },
  setMixinFlags:function(mixins) {
    this.mix_ad = mixins && mixins.admin;
    this.mix_pa = mixins && mixins.payer;
    this.mix_au = mixins && mixins.auditor;
    this.mix_rb = mixins && mixins.builder;
    this.mix_bi = mixins && mixins.billing;
  },
  //
  asNew:function() {
    return User.revive({
      active:1,
      _new:1
    })
  },
  ajax:function(worker) {
    var self = this;
    return {
      save:function(callback) { 
        self.onsave();
        Ajax.Users.save(worker, self, callback);
      },
      activate:function(callback) { 
        Ajax.Users.activate(worker, self.userId, callback);
      },
      deactivate:function(callback) { 
        Ajax.Users.deactivate(worker, self.userId, callback);
      }
    }
  }
})
Users = Object.RecArray.of(User, {
  //
  ajax:function(worker) {
    return {
      fetch:function(callback) { 
        Ajax.Users.get(null, callback);
      }
    }
  }
})
Mixins = Object.Rec.extend({
  //
  onload:function(s) {
    s = '|' + s + '|';
    this.admin = s.contains('|AD|');
    this.payer = s.contains('|PA|');
    this.auditor = s.contains('|AU|');
    this.builder = s.contains('|RB|');
    this.billing = s.contains('|BI|');
  },
  ui:function() {
    var s = [];
    this.admin && s.push('Admin');
    this.payer && s.push('Credit');
    this.auditor && s.push('Audit');
    this.builder && s.push('Report');
    me.cerberus && this.billing && s.push('Billing');
    return s;
  },
  //
  asPrimary:function() {
    return Mixins.revive('AD|PA|AU|RB|BI');
  }
})
NcUser = Object.Rec.extend({
  //
  isLp:function() {
    return this.userType == 'LicensedPrescriber';
  },
  isNew:function() {
    return this._new;
  },
  ui:function() {
    if (this.isLp())
      return this.uiUserType();
    else
      return this.uiUserType() + ': ' + this.uiRoleType();
  },
  uiUserType:function() {
    switch (this.userType) {
      case 'LicensedPrescriber':
        return 'Licensed Prescriber';
      default:
        return this.userType;
    }
  },
  uiRoleType:function() {
    switch (this.roleType) {
      case 'nurseNoRx':
        return 'nurse (no RX)';
      default:
        return this.roleType;
    }
  },
  //
  asNew:function(userId) {
    return NcUser.revive({
      'userId':userId,
      '_new':1
    })
  },
  ajax:function(worker) {
    var self = this;
    return {
      save:function(callback) { 
        Ajax.Users.saveErx(worker, self, callback);
      },
      remove:function(callback) { 
        Ajax.Users.removeErx(worker, self.userId, callback);
      }
    }
  }
})
AnchorLogin = {
  create:function(rec, onclick) {
    var cls = AnchorLogin.getClass(rec);
    return Html.AnchorRec.create(cls, rec.uid, rec, onclick);
  },
  getClass:function(rec) {
    if (! rec.isActive())
      return 'acctg';
    switch (rec.roleType) {
      case C_UserRole.TYPE_TRIAL:
      case C_UserRole.TYPE_PROVIDER_PRIMARY:
      case C_UserRole.TYPE_PROVIDER:
        return 'acct1';
      case C_UserRole.TYPE_CLINICAL:
        return 'acct2';
      default:
        return 'acct3';
    }
  }
}
