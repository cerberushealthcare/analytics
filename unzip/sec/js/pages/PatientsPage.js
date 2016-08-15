/**
 * Patients Page
 * @author Warren Hornsby
 */
PatientsPage = page = {
  //
  start:function(query) {
    PatientsTile.create();
    Page.setEvents();
    async(function() {
      PatientsTile.load();
    })
  },
  onresize:function() {
    var vp = Html.Window.getViewportDim();
    var i = vp.height - 200;
    if (i != self.maxHeight) {
      self.maxHeight = i;
      PatientsTile.setMaxHeight(i);
    }
    PatientsTile.onresize();
  }
}
PatientsTile = {
  create:function() {
    var My = this;
    return PatientsTile = _$('tile').clean().extend(function(self) {
      return {
        init:function() {
          self.Filter = My.Filter.create(_$('filterbox'))
            .bubble('onset', self.Filter_onset);
          self.Table = My.Table.create(self)
            .bubble('onbatchstatus', self.setBatchStatus);
          self.Cmd = Html.SplitCmdBar.create(self)
            .add('New Patient...', self.add_onclick)
            .button('Upload...', self.upload_onclick, 'upload')
            .split()
            .lbl('', null, 'bs');
            //.button('Generate CCDA Batch', self.generate_onclick, 'download2')
            //.button('Clear Last Batch', self.clear_onclick);
        },
        setMaxHeight:function(i) {
          var pad = self.Cmd.getHeight();
          self.Table.setHeight(i - pad);
        },
        onresize:function() {
          self.Table.onresize();
        },
        setBatchStatus:function(s) {
          _$('bs').setText(s || '');
        },
        load:function(rec) {
          if (rec == null)
            rec = self.Filter.get();
          self.Table.load(rec);
          self.Filter.load(rec);
          self.setBatchStatus(rec.Batch && rec.Batch._status);
        },
        add_onclick:function() {
          PatientCreator.pop();
        },
        upload_onclick:function() {
          ClinicalUpload.pop(function(o) {
            self.load();
          })
        },
        generate_onclick:function() {
          Pop.Confirm.showYesNo('This will generate a CCDA batch for all patients. Continue?', function() {
            self.setBatchStatus('Generating...');
            Ajax.Ccd.batchSync(function() {
              Page.Nav.goPatients();
            })
          })
        },
        clear_onclick:function() {
          Pop.Confirm.showYesNo('This will clear last CCDA batch. Continue?', function() {
            self.setBatchStatus('Clearing...');
            Ajax.Ccd.clearBatch(function() {
              Page.Nav.goPatients();
            })
          })
        },
        Filter_onset:function(rec) {
          self.Table.load(rec);
        }
      }
    })
  },
  Filter:{
    create:function(container) {
      return Html.Tile.create(container, 'filterbox').extend(function(self) {
        return {
          onset:function(rec) {}, 
          //
          init:function() {
            Html.Tile.create(self, 'filterhead').setText('Showing');
            self.Show = Html.UlFilter.create().into(self).bubble('onselect', self.onselect);
            self.Show.load({'0':'Active patients only','1':'All patients'}, '0');
            Html.Tile.create(self, 'filterhead mt10').setText('Sorted By').hide();;
            self.Sort = Html.UlFilter.create().into(self).bubble('onselect', self.onselect).hide();
            self.Sort.load({'0':'Most recent activity','1':'Name'}, '0');
          },
          //
          load:function(rec) {
            self.rec = rec;
            self.Show.select(rec.show);
            self.Sort.select(rec.sort);
            self._loaded = true;
          },
          get:function() {
            return {
              show:self.Show.selected.key,
              sort:self.Sort.selected.key
            }
          },
          onselect:function() {
            if (self._loaded)
              self.onset(self.get());
          }
        }
      })
    }
  },
  Table:{
    create:function(container) {
      return Html.ScrollTable.create(container, 'fsb').withMore().extend(function(self) {
        return {
          onbatchstatus:function(s) {},
          //
          init:function() {
            self.addHeader()
              .th('Name').w('20%')
              .th('ID').w('15%')
              .th('Birth / Age').w('15%')
              .th('Most Recent Activity').w('35%')
              .th('CCDA').w('15%');
          },
          addRow:function(rec) {
            var batch;
            if (rec.Batch && rec.Batch._filename) {
              batch = Html.Anchor.create('download3', rec.Batch._filename, function() {
                Page.Nav.goDownloadBatchCcda(rec.Batch._filename);
              })
            }
            self.tbody().trOff()
              .td(AnchorClient_Facesheet.create(rec)).w('20%')
              .td(rec.uid).w('15%')
              .td(rec.birth).w('15%')
              .td(rec.uiMru()).w('35%')
              .td(batch).w('15%');
          },
          load:function(showsort) {
            self.showsort = showsort;
            self.tbody().clean();
            self.pg = 0;
            self.getNextPage();
          },
          getNextPage:function() {
            self.pg++;
            PatientPage.ajax(self.More || self).fetch(self.pg, self.showsort, function(page) {
              if (page.Batch) 
                self.onbatchstatus(page.Batch._status);
              page.recs.each(self.addRow);
              self.setMore(page.more);
              self.syncHeaderWidth();
            })
          },
          onmore:function() {
            self.getNextPage();
          }
        }
      })
    }
  }
}
//
PatientPage = Object.Rec.extend({
  /*
   page
   recs
   more
   */
  onload:function() {
    this.setr('recs', PStub_P);
  },
  //
  ajax:function(worker) {
    return {
      fetch:function(page, showsort, callback) {
        Ajax.getr('Patients', 'getPage', {'page':page,'sort':showsort.sort,'show':showsort.show}, PatientPage, worker, function(page) {
          if (page && page.recs)
            callback(page);
        });
      }
    }
  }
})
PStub_P = Object.Rec.extend({
  //
  uiMru:function() {
    if (this.Mru)
      return this.Mru.date;
  }
})
//
ClinicalUpload = {
  pop:function(callback) {
    return Html.Pop.singleton_pop.apply(ClinicalUpload, arguments);
  },
  create:function() {
    return Html.UploadPop.create('Clinical Data Upload', 'uploadClinical').extend(function(self) {
      return {
        init:function() {
          Html.H3.create('Select XML file').before(self.form);
        },
        onpop:function(callback) {
          self.callback = callback;
        },
        oncomplete:function(ci) {
          var html = 'Patient <a class="bold" href="face.php?id=' + ci.Client.clientId + '">' + ci.Client.name + '</a> imported.';
          //var cls = ci.Client.sex == 'F' ? 'female' : 'male';
          self.callback && self.callback(ci);
          Pop.Confirm.show(html, 'Import Another...', 'upload', '&nbsp; Exit &nbsp;', null, false, null, function(confirmed) {
            ClinicalUpload.pop(self.callback);
          })
        },
        form_oncomplete_error:function(e) {
          if (e.type == 'DupeImportPatient')
            self.warnDupe(e);
          else
            Page.showAjaxError(e);
        },
        working:function(b) {
          if (b)
            Pop.Working.show('Importing...');
          else
            Pop.Working.close();
        },
        warnDupe:function(e) {
          self.close();
          var ci = e.data;
          Pop.Confirm.show(e.message, 'Yes, Import to This Patient', 'save', 'No, Cancel Import', null, false, null, function(confirmed) {
            self.working(true);
            Ajax.post('Upload', 'updateClinical', {'filename':ci.filename,'cid':ci.Client.clientId}, function(ci) {
              self.working(false);
              self.oncomplete(ci);
            })
          })
        }
      }
    })
  }
}
