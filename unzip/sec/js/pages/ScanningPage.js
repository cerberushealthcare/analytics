/**
 * Scanning Page
 * @author Warren Hornsby
 */
ScanningPage = {
  //
  start:function() {
    page.tile = ScanIndexing.create(_$('scan-indexing'), function() {
      page.onresize();
      Page.setEvents();
    });
  },
  reload:function() {
    page.tile.reload();
  },
  onresize:function() {
    var i = Html.Window.getViewportDim().height - 200;
    if (i != self.maxHeight) {
      self.maxHeight = i;
      page.tile.setMaxHeight(i);
    }
  }
};
/** 
 * ScanIndexing 
 *   Tile files
 *   Tile folders
 *     ScanEntryFolder entryFolder
 *     Tile savedFolders
 */
ScanIndexing = {
  create:function(parent, callback) {
    parent.clean();
    var My = this;
    var self = Html.Tile.create(parent);
    return self.aug({
      init:function() {
        async(function() {
          var tmain = Html.Table2Col.create(self, 
            self.files = My.Files.create(self).aug({
              oncheck_file:function(scanFile, checked) {
                self.scanFile_oncheck(scanFile, checked); 
              },
              ondelete_file:function(scanFile, checked) {
                self.scanFile_ondelete(scanFile, checked); 
              }
            }),
            self.folders = My.Folders.create(self).aug({
              onchanged:function() {
                self.files.load();
              },
              oncheck_file:function(scanFile, checked) {
                self.scanFile_oncheck(scanFile, checked); 
              },
              ondetach_file:function(scanFile, checked) {
                self.scanFile_ondetach(scanFile, checked); 
              }
            }))
          tmain.left.setWidth('35%').addClass('tmain');
          tmain.right.setWidth('65%');
          var tbl = Html.Table2Col.create().before(self.files);
          self.h2 = Html.H2.create('Unindexed Files').into(tbl.left);
          Html.Label.create('ml5 mr5', 'for').into(tbl.right);
          self.user = UserSelector.create().load(C_Recips, me.userId).into(tbl.right).bubble('onupdate', self.user_onset);
          var tile = Html.Div.create().after(self.files);
          Html.H2.create('Indexed Folders').setClass('ml10').before(self.folders);
          self.cb = Html.CmdBar.create(tile).button('Upload Files...', self.upload_onclick, 'uploadimg').button('Upload Scanned PDF Batch...', self.batch_onclick, 'uploadbatch');  //button('Upload XML...', self.uploadxml_onclick, 'uploadxml');
          //self.folders.load();
          self.files.load(me.userId);
          callback();
        })
      },
      setMaxHeight:function(i) {
        var pad = self.h2.getHeight();
        self.folders.setMaxHeight(i - pad);
        self.files.setMaxHeight(i - pad - self.cb.getHeight());
      },
      reload:function() {
        self.files.load();
      },
      //
      user_onset:function(user) {
        self.files.load(user.userId);
      },
      scanFile_oncheck:function(scanFile, checked) {
        scanFile.resetZoom();
        if (checked)
          self.folders.entryFolder.add(scanFile);
        //else
        //  self.files.add(scanFile);
      },
      scanFile_ondelete:function(scanFile, checked) {
        if (checked)
          return;
        Pop.Confirm.showYesNo('Are you sure you want to delete file ' + scanFile.rec.origFilename + '?', function() {
          self.working(true);
          scanFile.rec.ajax().remove(function(id) {
            self.working(false);
            self.files.load();
          })
        })
      },
      scanFile_ondetach:function(scanFile) {
        self.files.add(scanFile);
      },
      upload_onclick:function() {
        if (me.trial && me.userId != 3143)  // carlos espinoza
          alert('This feature is only available for registered users.');
        else
          ScanUploadPop.pop();
      },
      uploadxml_onclick:function() {
        Pop.show('pop-upload-xml');
      },
      batch_onclick:function() {
        if (me.trial && me.userId != 3143)  // carlos espinoza
          alert('This feature is only available for registered users.');
        else
          BatchUploadPop.pop();
      }
    })
  },
  Folders:{
    create:function(parent) {
      var My = this;
      var self = Html.Tile.create(parent, 'Folders');
      return self.aug({
        onchanged:function() {},
        oncheck_file:function(scanFile, checked) {},
        ondetach_file:function(scanFile, checked) {}, 
        //
        init:function() {
          async(function() {
            self.entryFolder = ScanEntryFolder.create(self)
              .bubble('onupdate', self.entry_onupdate)
              .bubble('onopen', self.entry_onopen)
              .bubble('onclose', self.entry_onclose)
              .bubble('oncancel', self, 'onchanged')
              .bubble('oncheck_file', self)
              .bubble('ondetach_file', self);
            self.savedFolders = My.SavedFolders.create(self)
              .bubble('onclickrec', self.saved_onclick);
            self.load();
            self.setMaxHeight();
          })
        },
        load:function() {
          self.savedFolders.load();
        },
        setMaxHeight:function(i) {
          if (i)
            self._mh = i;
          else
            i = self._mh;
          if (self.entryFolder)
            self.entryFolder.setMaxHeight(i);
        },
        //
        entry_onopen:function() {
          self.savedFolders.hide();
        },
        entry_onclose:function() {
          self.savedFolders.show();
        },
        entry_onupdate:function() {
          self.savedFolders.load();
          self.onchanged();
        },
        saved_onclick:function(rec) {
          self.entryFolder.fetch(rec.scanIndexId);
        }
      })
    },
    SavedFolders:{
      create:function(parent) {
        var My = this;
        var self = Html.Tile.create(parent, 'SavedFolders');
        return self.aug({
          onload:function() {},
          onclickrec:function(rec) {},
          //
          load:function() {
            self.working(true);
            self.clean();
            ScanFiles.ajax().fetchIndexedToday(function(recs) {
              if (recs && recs.length) {
                Html.H3.create("Created Today").into(self);                  
                Array.forEach(recs, function(rec) {
                  My.Folder.create(self, rec).bubble('onclickrec', self);
                });
              }
              self.working(false);
              self.onload();
            });
          }
        });
      },
      Folder:{
        create:function(parent, rec) {
          var self = Html.Div.create('Folder').into(parent);
          return self.aug({
            onclickrec:function(rec) {},
            //
            init:function() {
              self.rec = rec;
              self.anchor = Html.Anchor.create().into(self);
              self.anchor.innerHTML = '<b>' + rec.Client.name + '</b> &bull; ' + C_ScanIndex.TYPES[rec.scanType];
              self.anchor.onclick = function(){self.onclickrec(rec)};
            }
          });
        }
      }
    }
  },
  Files:{
    create:function(parent) {
      var self = Html.Tile.create(parent, 'ScanIndex').aug(Events.onscrollbottom);
      return self.aug({
        oncheck_file:function(scanFile, checked) {},
        ondelete_file:function(scanFile, checked) {},
        //
        load:function(userId) {
          if (userId)
            self.userId = userId;
          self.reset();
          ScanFiles.ajax().fetchUnindexed(self.userId, function(recs) {
            self.recs = recs;
            self.draw();
          })
        },
        //
        reset:function() {
          self.clean();
          self.recs = null;
          self.lasti = -1;
          self.BATCH = 10;
          self.More = null;
        },
        draw:function() {
          var rec, recs = self.recs;
          var lasti = self.getNextLastIndex();
          var worker = self.More || self;
          //var st = self.scrollTop;
          if (self.More)
            self.lockScroll(true);
          if (self.lasti < lasti) {
            worker.work(function() {
              for (var i = self.lasti + 1; i <= lasti; i++) {
                rec = recs[i];
                ScanFileCheck.create(self, rec, false).aug({
                  oncheck:function(scanFile, checked) {
                    self.oncheck_file(scanFile, checked);
                  },
                  ondelete:function(scanFile, checked) {
                    self.ondelete_file(scanFile, checked);
                  }
                })
              }
              self.lasti = lasti;
              //self.scrollTop = st;
              if (self.More) {
                self.More.remove();
                self.lockScroll(false);
//                pause(0.5, function() {
//                  self.lockScroll(false);
//                })
              }
              if (self.lasti < self.recs.length - 1)
                self.More = Html.Tile.create(self, 'More');
              else
                self.More = null;
            })
          }
        },
        setMaxHeight:function(i) {
          self.setHeight(i);
        },
        onscrollbottom:function() {
          self.draw();
        },
        getNextLastIndex:function() {
          var i = self.lasti + self.BATCH;
          var len = self.recs ? self.recs.length : 0;
          if (i >= len)
            i = len - 1;
          return i;
        },
        add:function(scanFile) {
          self.append(scanFile); 
        }
      })
    }
  }
}
/**
 * Assign global instance
 */
var page = ScanningPage;  