PqriPage = page = {
  //
  load:function(query) {
    PageTile.create(_$('tile'));
  }
}
PageTile = {
  create:function(container) {
    var My = this;
    return PageTile = Html.Tile.create(container, 'PageTile').extend(function(self) {
      return {
        init:function() {
          self.setHeight(500);
          self.Range = My.Range.create(self);
          var t = Html.Table.create(self).tbody();
          for (var id in Cqs) {
            var desc = Cqs[id];
            t.tr().td(Button.create(id).bubble('onclick', self.button_onclick.curry(id))).td(desc);
          }
        },
        button_onclick:function(id) {
          var rec = self.Range.getRecord();
          rec.id = id;
          CqmPop.pop(rec);
        }
      }
    }) 
  },
  Range:{
    create:function(container) {
      return Html.UlEntry.create(container).addClass('mb10').extend(function(self) {
        return {
          init:function() {
            self.build()
              .line().ln('From').date('from').l('To').date('to').l('For').start('docspan').end()
            self.Doctor = DoctorAnchorTab.create().into(self.$('docspan'));
            self.load({'from':'2015-01-01','to':'2016-01-01'});
            self.Doctor.set_byId(me.userId);
          },
          getRecord:self.getRecord.extend(function(_getRecord) {
            var rec = _getRecord();
            rec.userId = self.Doctor.getValue();
            return rec;
          })
        }
      })
    }
  }
}
Button = {
  create:function(text, onclick) {
    return Html.Anchor.create('cmd rep', text, onclick).into(Html.Div.create('cmd-fixed'));
  }
}
Cqs = {
  'CMS002':'Preventive Care and Screening: Screening for Clinical Depression and Follow-Up Plan',
  'CMS050':'Closing the Referral Loop: Receipt of Specialist Report',
  'CMS068':'Documentation of Current Medications in the Medical Record',
  'CMS069':'Preventive Care and Screening: Body Mass Index (BMI) Screening and Follow-Up Plan',
  'CMS090':'Functional Status Assessment for Complex Chronic Conditions',
  'CMS138':'Preventive Care and Screening: Tobacco Use: Screening and Cessation Intervention',
  'CMS156':'Use of High-Risk Medications in the Elderly',
  'CMS165':'Controlling High Blood Pressure',
  'CMS166':'Use of Imaging Studies for Low Back Pain',
  '(CatIII)':'QRDA Category III - Calculated Report'};
//
CqmPop = Html.SingletonPop.aug({
  create:function() {
    return Html.Pop.create('CQM Downloads').withFrame().extend(function(self) {
      return {
        //
        init:function() {
          var height = self.fullscreen(800, 600);
          self.Div = Html.Tile.create(self.frame, 'scroll').setHeight(height);
          Html.CmdBar.create(self.content).exit(self.close)
        },
        onshow:function(rec) {
          self.Div.clean();
          self.working(true);
          AjaxCqm.report(rec, self, function(filename) {
            self.working(false);
            Html.AnchorAction.asXml(filename, function() {
              window.location.href = 'cqm-pdf.php?id=' + filename;
            }).into(Html.Tile.create(self.Div, 'p5'));
          })
        }
      }
    })
  }
})
AjaxCqm = {
  _SVR:'Cqm',
  report:function(rec, worker, callback) {
    Ajax.postr(this._SVR, 'report', rec, null, worker, callback);
  }
}
