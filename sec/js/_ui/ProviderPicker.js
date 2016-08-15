/**
 * RecordPicker ProviderPicker
 */
ProviderPicker = {
  create:function() {
    var self = Html.RecordPicker.create('Provider Selector');
    return self.aug({
      init:function() {
        self.thead().tr('fixed head').th('Provider').w('35%').th('Area').w('20%').th('Location').w('45%').th();
        self.input.aug({
          fetch:function(value, callback) {
            Providers.ajax().fetchMatches(value, callback);
          },
          oncustom:function(text) {
            self.new_onclick({'last':text});
          },
          onclear:function() {
            self.set(null);
          },
          Anchor:AnchorProvider
        })
      },
      buttons:function(cmd) {
        cmd.add('Add New Provider...', self.new_onclick).cancel(self.pop.close);
      },
      fetch:function(callback_recs) {
        Ajax.Providers.getAll(callback_recs);
      },
      applies:function(rec, search) {
        if (search)
          return rec.name.match(search);
        return true;
      },
      add:function(rec, tr) {
        var area = C_Areas[rec.area];
        tr.select(rec, AnchorProvider.create(rec))
          .td(area)
          .td(rec.address)
          .td(Html.AnchorAction.asEdit('Edit', self.edit_onclick.curry(rec)));
      },
      getValueFrom:function(rec) {
        return rec.providerId;
      },
      getTextFrom:function(rec) {
        return rec.name;
      },
      //
      new_onclick:function(rec) {
        ProviderEntry.pop(rec, self.entry_onsave.curry(true)); 
      },
      edit_onclick:function(rec) {
        ProviderEntry.pop(rec, self.entry_onsave.curry(false));
      },
      entry_onsave:function(asNew, rec) {
        self.input.reset();
        if (asNew) {
          self.pop.clean();
          self.pop.select(rec);
          self.input.setFocus();
        } else {
          self.pop.reload();
        }
      }
    });
  }
}
AnchorProvider = {
  create:function(rec) {
    if (rec.active)
      return Html.AnchorAction.asSelect(rec.name);
    else
      return Html.AnchorAction.asSelectGray(rec.name);
  }
}
/**
 * RecordPicker ProviderPicker
 */
FacilityPicker = {
  create:function() {
    var self = Html.RecordPicker.create('Facility Selector');
    return self.aug({
      init:function() {
        self.thead().tr('fixed head').th('Facility').w('60%').th('Location').w('40%').th();
        self.input.aug({
          fetch:function(value, callback) {
            Facilities.ajax().fetchMatches(value, callback);
          },
          oncustom:function() {
            Facilities.resetCache();
            self.new_onclick();
          },
          onclear:function() {
            self.set(null);
          }
        })
      },
      buttons:function(cmd) {
        cmd.add('Add New Facility...', self.new_onclick).cancel(self.pop.close);
      },
      fetch:function(callback) {
        Ajax.Providers.Facilities.getAll(callback);
      },
      applies:function(rec, search) {
        if (search && rec.name)
          return rec.name.match(search);
        return true;
      },
      add:function(rec, tr) {
        tr.select(rec, rec.getName())
          .td(rec.csz)
          .td(Html.AnchorAction.asEdit('Edit', self.edit_onclick.curry(rec)));
      },
      getValueFrom:function(rec) {
        return rec.addressId;
      },
      getTextFrom:function(rec) {
        return Facility.revive(rec).getName();
      },
      //
      new_onclick:function() {
        FacilityEntry.pop(null, self.entry_onsave.curry(true));
      },
      edit_onclick:function(rec) {
        FacilityEntry.pop(rec, self.entry_onsave.curry(false));
      },
      entry_onsave:function(asNew, rec) {
        if (asNew) {
          self.pop.clean();
          self.pop.select(rec);
          self.input.setFocus();
        } else {
          self.pop.reload();
        }
      }
    });
  }
}
/**
 * RecordEntryDeletePop ProviderEntry
 */
ProviderEntry = {
  pop:function(rec, callback_onsave) {
    ProviderEntry = this.create().pop(rec, callback_onsave);
  },
  create:function() {
    var self = Html.RecordEntryPop.create('Provider Entry');
    return self.aug({
      onshow:function(rec, callback_onsave) {
        if (callback_onsave)
          self.onsave = callback_onsave;
        _$('li1').showIf(rec != null);
        self.form.focus('last');
      },
      buildForm:function(ef) {
        ef.li(null, 'mb10', null, 'li1').check('active', 'Active?');
        ef.li('Last Name').textbox('last', 15).lbl('First').textbox('first', 12).lbl('Middle').textbox('middle', 6);
        ef.li('Area').select('area', C_Areas, '');
        ef.li('Facility', 'mt10').picker(FacilityPicker, 'addrFacility', 'Address_addrFacility');
      },
      save:function(rec, callback) {
        Ajax.Providers.save(rec, callback);
        Providers.resetCache();
      },
      remove:function(rec, callback) {
        Ajax.Providers.remove(rec.providerId, callback);
        Providers.resetCache();
      }
    })
  }
}
/**
 * RecordEntryDeletePop FacilityEntry
 */
FacilityEntry = {
  pop:function(rec, callback_onsave) {
    FacilityEntry = this.create().pop(rec, callback_onsave);
  },
  create:function() {
    var self = Html.RecordEntryDeletePop.create('Facility Entry');
    return self.aug({
      onshow:function(rec, callback_onsave) {
        if (callback_onsave)
          self.onsave = callback_onsave;
        self.form.focus();
      },
      buildForm:function(ef) {
        ef.li('Name').textbox('name', 30);
        ef.li('Address', 'mt10').textbox('addr1', 40);
        ef.li(' ').textbox('addr2', 40);
        ef.li('City').textbox('city', 35);
        ef.li('State').select('state', C_Address.STATES, '').lbl('Zip').textbox('zip', 5);
        ef.li('Phone', 'mt10').textbox('phone1', 25);
      },
      save:function(rec, callback) {
        Ajax.Providers.Facilities.save(rec, callback);
        Facilities.resetCache();
      },
      remove:function(rec, callback) {
        Ajax.Providers.Facilities.remove(rec.providerId, callback);
        Facilities.resetCache();
      }
    });
  }
}
/** 
 * Data
 */
Provider = Object.Rec.extend({
  //
  onload:function() {
    this.setr('Address_addrFacility', Facility);
  },
  getName:function() {
    var s = String.denull(this.first)
    if (s != '') 
      s += ' ' + String.denull(this.middle);
    s += ' ' + String.denull(this.last);
    return String.trim(s);
  }
})
Providers = Object.RecArray.of(Provider, {
  isMatch:function(rec, search) {
    return rec.last.match(search) || rec.first.match(search);
  },
  lev:function(rec, search) {
    return Math.min(search.lev(rec.last), search.lev(rec.first));
  },
  //
  ajax:function() {
    var self = this;
    return {
      fetchAll:function(callback) {
        self.ajax_fetchAll(Ajax.Providers.getAllActive, callback);
      },
      fetchMatches:function(text, callback) {
        self.ajax_fetchMatches(this.fetchAll, text, callback);
      }
    }
  }
})
Facility = Object.Rec.extend({
  //
  getName:function() {
    if (this.name)
      return this.name;
    var csz = this.getCsz();
    if (this.csz != '')
      return '(' + this.csz + ')';
    return '(Unnamed)';
  },
  getNameAddress:function() {
    var s = String.denull(this.name);
    var addr = this.getAddress();
    if (s != '' && addr != '')
      s += ', ';
    s += addr;
    return s;
  },
  getAddress:function() {
    var s = String.denull(this.addr1);
    if (! String.isBlank(this.addr2)) 
      s += " " + this.addr2;
    var csz = this.getCsz();
    if (s != "" && csz != '')
      s += ", ";
    s += csz;
    return s;
  },
  getCsz:function() {
    var s = String.denull(this.city);
    if (s != "") 
      s += ", ";
    s += String.denull(this.state) + " " + String.denull(this.zip);
    return String.trim(s);
  }
})
Facilities = Object.RecArray.of(Facility, {
  isMatch:function(rec, search) {
    return rec.name && rec.name.match(search);
  },
  //
  ajax:function() {
    var self = this;
    return {
      fetchAll:function(callback) {
        self.ajax_fetchAll(Ajax.Providers.Facilities.getAll, callback);
      },
      fetchMatches:function(text, callback) {
        self.ajax_fetchMatches(this.fetchAll, text, callback);
      }
    }
  }
})
