/**
 * RecordPicker ProviderPicker
 */
ProviderPicker = {
  create:function() {
    var self = Html.RecordPicker.create('Provider Selector');
    return self.aug({
      init:function() {
        self.thead().tr('fixed head').th('Provider').w('35%').th('Area').w('20%').th('Location').w('45%');
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
        var area = C_Lookups.AREAS[rec.area];
        tr.select(rec, rec.name).td(area).td(rec.address);
      },
      getValueFrom:function(rec) {
        return rec.providerId;
      },
      getTextFrom:function(rec) {
        return rec.name;
      },
      //
      new_onclick:function() {
        ProviderEntry.pop(null, self.pop_onsave); 
      },
      pop_onsave:function(rec) {
        self.pop.clean();
        self.pop.select(rec);
      }
    });
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
        self.thead().tr('fixed head').th('Facility').w('33%').th('Location').w('67%');
      },
      buttons:function(cmd) {
        cmd.add('Add New Facility...', self.new_onclick).cancel(self.pop.close);
      },
      fetch:function(callback) {
        Ajax.Providers.Facilities.getAll(callback);
      },
      applies:function(rec, search) {
        if (search)
          return rec.name.match(search);
        return true;
      },
      add:function(rec, tr) {
        tr.select(rec, rec.name).td(rec.csz);
      },
      getValueFrom:function(rec) {
        return rec.addressId;
      },
      getTextFrom:function(rec) {
        return rec.name;
      },
      //
      new_onclick:function() {
        FacilityEntry.pop(null, self.pop_onsave);
      },
      pop_onsave:function(rec) {
        self.pop.clean();
        self.pop.select(rec);
      }
    });
  }
}
/**
 * RecordEntryDeletePop ProviderEntry
 */
ProviderEntry = {
  pop:function(rec, callback_onsave) {
    ProviderEntry = this.create(callback_onsave).pop(rec);
  },
  create:function(callback_onsave) {
    var self = Html.RecordEntryDeletePop.create('Provider Entry');
    return self.aug({
      onsave:callback_onsave,
      buildForm:function(ef) {
        ef.li('Last Name').textbox('last', 15).lbl('First').textbox('first', 12).lbl('Middle').textbox('middle', 6);
        ef.li('Area').select('area', C_Lookups.AREAS, '');
        ef.li('Facility', 'mt10').picker(FacilityPicker, 'addrFacility');
      },
      save:function(rec, callback) {
        Ajax.Providers.save(rec, callback);
      },
      remove:function(rec, callback) {
        Ajax.Providers.remove(rec.providerId, callback);
      }
    });
  }
}
/**
 * RecordEntryDeletePop FacilityEntry
 */
FacilityEntry = {
  pop:function(rec, callback_onsave) {
    FacilityEntry = this.create(callback_onsave).pop(rec);
  },
  create:function() {
    var self = Html.RecordEntryDeletePop.create('Facility Entry');
    return self.aug({
      onsave:callback_onsave,
      buildForm:function(ef) {
        ef.li('Name').textbox('name', 30);
        ef.li('Address', 'mt10').textbox('addr1', 40);
        ef.li(' ').textbox('addr2', 40);
        ef.li('City').textbox('city', 35);
        ef.li('State').select('state', C_Address.STATES, '').lbl('Zip').textbox('zip', 5);
      },
      save:function(rec, callback) {
        Ajax.Providers.Facilities.save(rec, callback);
      },
      remove:function(rec, callback) {
        Ajax.Providers.Facilities.remove(rec.providerId, callback);
      }
    });
  }
}