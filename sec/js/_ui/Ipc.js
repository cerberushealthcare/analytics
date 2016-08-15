/**
 * RecordPicker IpcPicker
 */
IpcPicker = {
  create:function() {
    var self = Html.RecordPicker.create('Test/Procedure Selector', 26);
    return self.aug({
      init:function() {
        self.thead().tr('fixed head').th('Test/Procedure').w('35%').th('Description').w('45%').th('Category').w('20%');
      },
      fetch:function(callback_recs) {
        Ajax.Ipc.getAll(callback_recs);
      },
      applies:function(rec, search) {
        if (search)
          return rec.name.match(search);
        return true;
      },
      add:function(rec, tr) {
        var cat = C_Ipc.CATS[rec.cat];
        tr.select(rec, rec.name).td(rec.desc).td(cat);
      },
      getValueFrom:function(rec) {
        return rec.ipc;
      },
      getTextFrom:function(rec) {
        return rec.name;
      }
    });
  }
}
