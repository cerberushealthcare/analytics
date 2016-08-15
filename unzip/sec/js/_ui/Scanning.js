/**
 * Pop EntryFolderPop
 */
EntryFolderPop = {
  /*
   * @arg ScanIndex rec @callback() onupdate
   */
  pop:function(rec, onupdate) {
    return Html.Pop.singleton_pop.apply(this, arguments);
  },
  create:function() {
    return Html.DirtyPop.create('Scan Folder', 600).extend(function(self) {
      return {
        onupdate:function() {
        },
        //
        init:function() {
          self.folder = ScanEntryFolder.create(self.content, true)
            .bubble('onopen', self.folder_onopen)
            .bubble('onupdate', self)
            .bubble('onclose', self.close_asSaved);
          self.content.onmousedown = Zoom.closeAll;
        },
        onpop:function() {
          self.invisible();
        },
        onshow:function(rec, onupdate) {
          self.folder.fetch(rec.scanIndexId);
          self.onupdate = onupdate;
        },
        onclose:function() {
          Zoom.closeAll();
        },
        reset:function() {
          self.folder.reset();
        },
        isDirty:function() {
          return self.folder.isDirty();
        },
        save:function() {
          folder.save_onclick();
        },
        //
        folder_onopen:function() {
          self.reposition();
          self.visible();
        },
        folder_onupdate:function() {
          if (self.onupdate)
            self.onupdate();
        }
      }
    })
  }
}
/**
 * ScanEntryFolder EntryForm form FileList list
 */
ScanEntryFolder = {
  create:function(container, noTbar) {
    var My = this;
    return Html.Tile.create(container, 'EntryFolder').extend(function(self) {
      return {
        onopen:function() {
        },
        onclose:function() {
        },
        onupdate:function() {
        },
        oncancel:function() {
        },
        oncheck_file:function(scanFile, checked) {
        },
        ondetach_file:function(scanFile) {
          scanFile.remove(); // @abstract
        },
        //
        init:function() {
          self.tbar = self.tile(My.Title, 'new-index').bubble('onclick_create', self.tbar_oncreate).hideIf(noTbar);
          self.content = Html.Div.create().into(self).hide();
          self.form = Html.UlEntry.create(self.content).extend(My.EntryForm);
          self.form.onresize = self.resize;
          self.list = My.FileList.create(self.content, self).bubble('oncheck_file', self).bubble('ondetach_file', self);
          self.cmd = My.CmdBar.create(self.content, self);
          self.reset();
        },
        reset:function() {
          self.rec = null;
          self.removeClass('update');
          self.tbar.showIf(!noTbar);
          self.content.hide();
          self.closed = self;
          self.form.reset();
          return self;
        },
        fetch:function(sfid) {
          ScanIndex.ajax().fetch(sfid, self.open);
        },
        open:function(rec) {
          self.rec = rec;
          self.addClassIf('update', rec);
          self.tbar.hide();
          self.cmd.showDelIf(rec);
          self.content.show();
          self.form.load(rec);
          self.list.load(rec);
          self.closed = null;
          self.resize();
          self.onopen();
        },
        close:function(updated) {
          self.reset();
          self.onclose();
          if (updated)
            self.onupdate();
          return self;
        },
        add:function(scanFile) {
          if (self.closed)
            self.open();
          self.list.add(scanFile);
        },
        isDirty:function() {
          return self.form.isDirty() || self.list.isDirty();
        },
        setMaxHeight:function(i) {
          self._maxh = i - 20;
          self.resize();
        },
        resize:function() {
          if (!self.closed && self._maxh) {
            var pad = self.form.getHeight() + self.cmd.getHeight();
            self.list.setHeight(self._maxh - pad);
          }
        },
        //
        tbar_oncreate:function() {
          self.open();
        },
        save_onclick:function() {
          self.working(true);
          var rec = self.form.getRecord();
          Ajax.Scanning.saveIndex(self.form.getRecord(), self.list.getScanFileIds(), function(rec) {
            self.working(false);
            self.close(true);
          })
        },
        del_onclick:function() {
          Pop.Confirm.showYesNo('Are you sure you want to remove this index?<br><br>Note: Only the index will be deleted. Any scanned files in this index will be returned to Unindexed Files.', function() {
            self.working(true);
            Ajax.Scanning.removeIndex(self.rec.scanIndexId, function(id) {
              self.working(false);
              self.close(true);
            });
          });
        },
        cancel_onclick:function() {
          if (self.isDirty()) {
            Pop.Confirm.show('This index record has unsaved changes. Save now?', 'Save Now', 'save', "Don't Save", null, true, null, function(confirmed) {
              if (confirmed) {
                self.save_onclick();
              } else {
                if (self.list.isDirty())
                  self.oncancel();
                self.close();
              }
            });
          } else {
            self.close();
          }
        }
      }
    })
  },
  Title:function(self) {
    return {
      onclick_create:function() {
      },
      //
      init:function() {
        Html.Anchor.create(null, 'Create New...').into(self).bubble('onclick', self, 'onclick_create');
      }
    }
  },
  EntryForm:function(self) {
    return {
      onresize:function() {
      },
      //
      init:function() {
        self.build('first70')
          .line('fr')
            .l('To').select('recipient', C_Docs).check('reviewed', 'Reviewed')
          .line()
            .client('clientId', 'Client', '(Select Patient)', self.client_onset)
          .line()
            .l('Date').date('datePerformed')
            .start('ef-recon')
              .l('Reconcile To').ui('Order', OrderPicker, self.order_onset)
            .end()
          .line('mb5 mt10')
            .l('Type').atab('scanType', ScanTypeAtab, self.type_onchange)
            .start('ef-ipc')
              .ln('for').pick('ipc', 'Ipc', IpcPicker)
              .check('withImage', 'With Image')
              .append(Html.Anchor.create('ml5', 'Quick Result...', self.quick_onclick))
            .end()
          .line().id('qr2')
            .l('Value').textbox('value', 5)
            .l('Units').textbox('valueUnit', 5)
            .l('Interpretation').select('interpretCode', C_ProcResult.INTERPRET_CODES, '')
          .line().id('qr4')
            .l('Comments').textarea('rcomments')
          .line()
            .l('Provider').pick('providerId', 'Provider', ProviderPicker, self.provider_onset)
            .ln('at').pick('addrFacility', 'Address_addrFacility', FacilityPicker)
          .line()
            .l('Area').atabf('areas', MultiAreaAtab, 400, Function.defer(self, 'onresize'));
      },
      onbeforeload:function(rec) {
        self.setValue('recipient', me.docId);
        self.setOrderClient(rec && rec.clientId);
        if (rec == null)
          rec = {'recipient':me.docId};
        else if (rec.recipient == null)
          rec.recipient = me.docId;
        return rec;
      },
      draw:function() {
        self.showRecon();
        self.showScanType();
      },
      reset:self.reset.append(function() {
        self._showingResults = null;
        self._orders = null;
      }),
      //
      client_onset:function(c) {
        self.setOrderClient(c.clientId);
      },
      setOrderClient:function(cid) {
        self.$('Order').setClientId(cid, function(recs) {
          self._orders = recs;
          self.draw();
        });
      },
      type_onchange:function() {
        async(function() {
          self.showScanType(true);
        })
      },
      quick_onclick:function() {
        self.showResults(true);
      },
      provider_onset:function(rec) {
        if (rec) {
          if (rec.Address_addrFacility)
            self.setValue('addrFacility', rec.Address_addrFacility);
          if (rec.area)
            if (Array.isEmpty(self.getValue('areas')))
              self.setValue('areas', [ rec.area ]);
        }
      },
      order_onset:function(rec) {
        var type = self.trackCatToType(rec.trackCat);
        if (type) {
          self.setValue('scanType', type);
          if (type == C_ScanIndex.TYPE_RESULT)
            self.setValue('ipc', rec.Ipc);
          self.setValue('providerId', rec.Provider_schedWith);
          self.setValue('addrFacility', rec.Address_schedLoc);
        }
      },
      showRecon:function() {
        self.$('ef-recon').showIf(self._orders);
      },
      showScanType:function(focus) {
        if (self.getValue('scanType') == C_ScanIndex.TYPE_RESULT) {
          self.$('ef-ipc').show();
          if (focus)
            self.focus('ef-ipc');
        } else {
          self.$('ef-ipc').hide();
          self.showResults(false);
        }
        self.onresize();
      },
      showResults:function(b) {
        if (b !== self._showingResults) {
          self._showingResults = b;
          self.$('qr2').showIf(b);
          self.$('qr4').showIf(b);
          self.onresize();
        }
      },
      trackCatToType:function(tcat) {
        switch (tcat) {
        case C_TrackItem.TCAT_LAB:
        case C_TrackItem.TCAT_NUCLEAR:
        case C_TrackItem.TCAT_RADIO:
        case C_TrackItem.TCAT_TEST:
        case C_TrackItem.TCAT_PROC:
          return C_ScanIndex.TYPE_RESULT;
        case C_TrackItem.TCAT_REFER:
          return C_ScanIndex.TYPE_LETTER;
        }
      }
    }
  },
  FileList:{
    create:function(container) {
      var self = Html.Div.create('EntryFolderList').into(container);
      return self.aug({
        oncheck_file:function(scanFile, checked) {
        },
        ondetach_file:function(scanFile, checked) {
        },
        //
        load:function(rec) {
          this.clean();
          var file;
          if (rec)
            Array.forEach(rec.ScanFiles, function(rec, i) {
              file = ScanFileCheck.create(self, rec, true);
              self.add(file);
            })
          this._recOrig = Json.encode(self.getScanFileIds());
        },
        add:function(scanFile) {
          self.append(scanFile
            .bubble('oncheck', self, 'oncheck_file')
            .bubble('ondetach', self, 'ondetach_file')
            .bubble('onsortup', self.file_onsortup)
            .bubble('onsortdown', self.file_onsortdown));
        },
        isDirty:function() {
          var _recNow = Json.encode(self.getScanFileIds());
          return (_recNow != self._recOrig);
        },
        getScanFileIds:function() {
          var recs = [];
          if (self.children.length)
            Array.forEach(self.children, function(scanFile) {
              recs.push(scanFile.rec.scanFileId);
            })
          return recs;
        },
        file_onsortup:function(scanFile) {
          if (scanFile.previousSibling) {
            scanFile.before(scanFile.previousSibling);
            Html.Animator.fade(scanFile);
            Html.Animator.scrollTo(self, scanFile);
          }
          Html.Window.cancelBubble();
        },
        file_onsortdown:function(scanFile) {
          if (scanFile.nextSibling) {
            scanFile.after(scanFile.nextSibling);
            Html.Animator.fade(scanFile);
            Html.Animator.scrollTo(self, scanFile);
          }
          Html.Window.cancelBubble();
        }
      });
    }
  },
  CmdBar:{
    create:function(container, context) {
      return Html.CmdBar.create(container).save(context.save_onclick).del(context.del_onclick).cancel(context.cancel_onclick);
    }
  }
}
//
/**
 * Div ScanFileCheck ScanFile rec ImageScanThumb thumb InputCheck check Label
 * label Anchor aremove
 */
ScanFileCheck = {
  create:function(container, rec, checked) {
    var self = Html.Div.create('ScanFile').into(container);
    return self.aug({
      oncheck:function(scanFile, checked) {
      },
      ondelete:function(scanFile, checked) {
      },
      onsortup:function(scanFile) {
      },
      onsortdown:function(scanFile) {
      },
      ondetach:function(scanFile) {
      },
      //
      init:function() {
        self.load(rec, checked);
      },
      load:function(rec, checked) {
        self.clean();
        self.rec = rec;
        self.check = Html.InputCheck.create().bubble('onclick', self.check_onclick).into(self).hide();
        self.wrapper = Html.Span.create('wrapper').into(self).bubble('onclick', self.wrapper_onclick);
        self.thumb = ImageScanThumb.create(self, rec).bubble('onrotate', self.thumb_onrotate);
        self.label = Html.Label.create(null, rec.origFilename);
        self.aremove = Html.Anchor.create('remove').tooltip('Delete this file').bubble('onclick', self.aremove_onclick);
        self.up = Html.Anchor.create('up').aug(Events.onhoverclass).tooltip('Sort file up').bubble('onclick', self.up_onclick);
        self.down = Html.Anchor.create('down').aug(Events.onhoverclass).tooltip('Sort file down').bubble('onclick', self.down_onclick);
        self.detach = Html.Anchor.create('detach').tooltip('Detach this file').bubble('onclick', self.detach_onclick);
        self.ixcmds = Html.Table1Row.create(null, [ self.up, self.down, self.detach ]);
        Html.Table.create(self.wrapper, 'w100').tbody().tr().td(self.thumb).w('10%').td(self.label, 'p5').w('90%').td(self.aremove, 'rj').td(self.ixcmds, 'rj');
        self.check.checked = checked;
        self.draw();
      },
      draw:function() {
        self.addClassIf('check', self.check.checked);
        if (self.isChecked()) {
          self.addClass('check');
          self.thumb.shrink(true);
          self.aremove.hide();
          self.ixcmds.show();
        } else {
          self.removeClass('check');
          self.thumb.shrink(false);
          self.aremove.show();
          self.ixcmds.hide();
        }
      },
      reload:function(rec) {
        self.load(rec, self.isChecked());
      },
      isChecked:function() {
        return self.check.checked;
      },
      setChecked:function(value) {
        self.check.checked = value;
        self.draw();
        self.oncheck(self, value);
      },
      resetZoom:function() {
        self.thumb.resetZoom();
      },
      //
      onmouseover:function() {
        if (!self.isChecked())
          self.style.backgroundColor = '#C3FDB8';
      },
      onmouseout:function() {
        if (!self.isChecked())
          self.style.backgroundColor = '';
      },
      thumb_onrotate:function() {
        self.rec.ajax().rotate(function(rec) {
          self.reload(rec);
          self.thumb.preview(true, Zoom.NO_DELAY, Zoom.USE_LAST_POS);
        })
      },
      aremove_onclick:function() {
        self.ondelete(self, self.check.checked);
        Html.Window.cancelBubble();
      },
      detach_onclick:function() {
        Html.Window.cancelBubble();
        Pop.Confirm.showYesNo('Are you sure you want to detach file ' + self.rec.origFilename + '?', function() {
          self.check.checked = false;
          self.draw();
          self.ondetach(self);
        })
      },
      up_onclick:function() {
        self.onsortup(self);
        self.up.onmouseout();
        Html.Window.cancelBubble();
      },
      down_onclick:function() {
        self.onsortdown(self);
        self.down.onmouseout();
        Html.Window.cancelBubble();
      },
      check_onclick:function() {
        self.draw();
        self.oncheck(self, self.check.checked);
      },
      wrapper_onclick:function() {
        if (self.isChecked()) {
          self.thumb.preview(!self.thumb.isPreviewing(), Zoom.NO_DELAY);
        } else {
          self.setChecked(true);
        }
      }
    })
  }
}
/**
 * Image ImageScanThumb Image zoom
 */
ImageScanThumb = {
  create:function(parent, rec) {
    var My = this;
    var h = 60;
    var w = 50;
    var MyZoom = (rec._pdf) ? PdfZoom : ImageZoom;
    var max = Html.Window.getViewportDim();
    var self = rec.asImage(h, w).into(parent);
    return self.aug({
      onrotate:function() {
      },
      //
      init:function() {
        self.parent = parent;
        self.h = h;
        self.w = w;
        self.rec = rec;
      },
      onmouseover:function() {
        self.preview(true);
      },
      onmouseout:function(e) {
        var to = Html.Window.getEventTo(e);
        if (!(to && to.isChildOf && to.isChildOf(self.zoom)))
          self.preview(false);
      },
      isPreviewing:function() {
        return self.zoom && self.zoom.isPreviewing(self);
      },
      preview:function(b, noDelay, useLastPos) {
        if (b) {
          self.zoom = MyZoom.open(self, noDelay, useLastPos);
          if (self.zoom == null) {
            self._trying = true;
            pause(1, function() {
              if (self.zoom == null && self._trying)
                self.zoom = MyZoom.open(self, noDelay, useLastPos);
            })
          }
        } else {
          self._trying = null;
          MyZoom.close(self, noDelay);
        }
      },
      shrink:function(on) {
        if (on)
          return self.setHeight(25).setWidth(25);
        else
          return self.setHeight(self.h).setWidth(self.w);
      },
      getInitPos:function() {
        if (parent.thumbInitPos == null)
          parent.thumbInitPos = self.getPosDim();
        return Object.clone(parent.thumbInitPos);
      },
      resetZoom:function() {
        self.preview(false);
        parent.thumbInitPos = null;
      }
    });
  },
  resize:function(h, maxh, w, maxw) {
    var r = h / w;
    if (h > maxh || w > maxw) {
      if (r > 1 && h > maxh) {
        r = maxh / h;
        h = maxh;
        w = w * r;
      } else {
        r = maxw / w;
        w = maxw;
        h = h * r;
      }
    }
    return {
      'h':h,
      'w':w,
      'maxh':maxh,
      'maxw':maxw
    };
  }
}
/**
 * Div Zoom
 */
Zoom = {
  NO_DELAY:true,
  USE_LAST_POS:true,
  //
  closeAll:function() {
    ImageZoom.close();
    PdfZoom.close();
  },
  pauseAll:function() {
    ImageZoom.pause(true);
    PdfZoom.pause(true);
  },
  resumeAll:function() {
    ImageZoom.pause(false);
    PdfZoom.pause(false);
  },
  create:function() {
    var My = this;
    return Html.Div.create('Zoom').into().hide().extend(function(self) {
      return {
        //
        init:function() {
          self.setPosition('absolute').invisible();
          self.cap = My.Cap.create(self).bubble('onclose', self.cap_onclose);
          self.img = Html.Image.create().into(self);
          self.max = Html.Window.getViewportDim();
        },
        open:function(thumb, noDelay, useLastPos) {
          self.load(thumb);
          self.preview(true, thumb, noDelay, useLastPos);
          return self;
        },
        close:function(thumb, noDelay) {
          if (thumb == null || self.thumb == thumb)
            self.preview(false, thumb, noDelay);
        },
        pause:function(b) {
          self.paused = b;
          if (self.paused)
            self.close();
        },
        //
        load:function(thumb) {
          self.hide();
          self.thumb = thumb;
          self.cap.load(thumb);
          var rec = thumb.rec;
          self.img.src = rec.src;
          self.img.setSizeWithin(rec.height, rec.width, self.max.height - 60, 600);
          self.previewing = false;
        },
        cap_onclose:function() {
          self.preview(false);
        },
        onmousedown:Html.Window.cancelBubble,
        onmouseover:function() {
          self.preview(true, self.thumb);
        },
        onmouseout:function(e) {
          var to = Html.Window.getEventTo(e);
          if (to && (to == self.thumb || (to.isChildOf && to.isChildOf(self))))
            return;
          if (self.previewing)
            self.preview(false, self.thumb);
        },
        isPreviewing:function(thumb) {
          return (self.thumb == thumb && self.previewing);
        },
        close_reset:function() {
          self.previewing = false;
          self._changingto = null;
          self.thumb = null;
          self.invisible();
        },
        preview:function(b, thumb, noDelay, useLastPos) {
          if (thumb == null && !b)
            return self.close_reset();
          var delay = (noDelay) ? 0 : 1;
          if (self.thumb == thumb) {
            if (self.previewing == b) {
              self._changingto = null;
            } else {
              self._changingto = b;
              if (b) {
                pause(0.3 * delay, function() {
                  if (self.thumb == thumb && self._changingto) {
                    self.previewing = true;
                    self._changingto = null;
                    if (!useLastPos) {
                      var pos = thumb.getInitPos();
                      pos.left = pos.left + pos.width;
                      pos.top = pos.top - Html.Window.getScrollTop(); // absolute
                                                                      // positioned
                                                                      // zoom is
                                                                      // out of
                                                                      // flow
                      self.invisible().show().repos(pos);
                    }
                    self.visible().show();
                  }
                })
              } else {
                pause(0.7 * delay, function() {
                  if (self.thumb == thumb && self._changingto === false)
                    self.close_reset();
                })
              }
            }
          }
        }
      }
    })
  },
  Cap:{
    create:function(container) {
      return Html.Tile.create(container, 'cap').extend(function(self) {
        return {
          onclose:function() {
          },
          //
          init:function() {
            // self.table = Html.Table2Col.create(self, null, self.label =
            // Html.Label.create());
            self.table = Html.Table2Col.create(self, null, self.closer = Html.AnchorNoFocus.create(null, 'Close').bubble('onclick', self, 'onclose'));
          },
          load:function(thumb) {
            // self.label.setText(thumb.rec.origFilename);
          }
        }
      })
    }
  }
}
ImageZoom = {
  open:function(thumb) {
    return Html.Pop.singleton.call(this).open.apply(this._singleton, arguments);
  },
  close:function(thumb) {
    return Html.Pop.singleton.call(this).close.apply(this._singleton, arguments);
  },
  pause:function(value) {
    Html.Pop.singleton.call(this).pause.apply(this._singleton, arguments);
  },
  create:function() {
    var My = this;
    return Zoom.create().extend(function(self) {
      return {
        init:function() {
          self.cap.extend(My.Cap).bubble('onrotate', function() {
            if (self.thumb.onrotate)
              self.thumb.onrotate();
          })
        }
      }
    })
  },
  Cap:function(self) {
    return {
      onrotate:function() {
      },
      //
      init:function() {
        Html.AnchorNoFocus.create('rotate', 'Rotate').bubble('onclick', self, 'onrotate').into(self.table.left);
      }
    }
  }
}
PdfZoom = {
  open:function(thumb) {
    return Html.Pop.singleton.call(this).open.apply(this._singleton, arguments);
  },
  close:function(thumb) {
    return Html.Pop.singleton.call(this).close.apply(this._singleton, arguments);
  },
  pause:function(value) {
    Html.Pop.singleton.call(this).pause.apply(this._singleton, arguments);
  },
  create:function(thumb) {
    return Zoom.create().extend(function(self) {
      return {
        //
        init:function() {
          self.img.hide();
          self.iframe = Html.IFrame.create(null, null, self.max.height - 100, 500).into(self);
        },
        load:self.load.append(function(thumb) {
          self.iframe.set('src', thumb.rec.pdfsrc);
        }),
        invisible:function() {
          self.style.visibility = 'hidden';
          self.iframe.style.display = 'none';
          return self;
        },
        show:function() {
          self.style.display = 'block';
          self.iframe.style.display = '';
          return self;
        },
        visible:function() {
          self.style.visibility = '';
          self.iframe.style.display = '';
          return self;
        }
      }
    })
  }
}
/**
 * AnchorTab MultiAreaAtab
 */
MultiAreaAtab = {
  create:function() {
    return Html.AnchorTab.create().checks(C_Areas, 3, false).okCancel();
  }
}
/**
 * AnchorTab ScanTypeAtab
 */
ScanTypeAtab = {
  create:function() {
    return Html.AnchorTab.create().radios(C_ScanIndex.TYPES, 3, true).okCancel();
  }
}
/**
 * UploadPop ScanUploadPop
 */
ScanUploadPop = {
  pop:function() {
    return Html.Pop.singleton_pop.apply(this, arguments);
  },
  create:function() {
    return Html.UploadPop.create('Upload Scanned Files', 'uploadScans', 5).extend(function(self) {
      return {
        init:function() {
          self.donebox = Html.Div.create('information mb10 pt10').before(self.form).html('Your file(s) were uploaded successfully.<br>You may now select more files to upload, or click Cancel to begin indexing.');
        },
        oncomplete:function() {
          page.reload();
        },
        reset:self.reset.append(function() {
          self.donebox.hide();
        }),
        form_oncomplete_ok:function() {
          self.form.reset();
          self.donebox.show();
        }
      }
    })
  }
}
/**
 * UploadPop BatchUploadPop
 */
BatchUploadPop = {
  pop:function() {
    return Html.Pop.singleton_pop.apply(this, arguments);
  },
  create:function() {
    return Html.UploadPop.create('Upload Batch PDF', 'uploadBatch').extend(function(self) {
      return {
        oncomplete:function(filename) {
          Pop.Working.show('Upload successful.<br>Splitting individual pages from batch PDF...');
          Ajax.Scanning.splitBatch(filename, function() {
            Pop.Working.close();
            page.reload();
          })
        }
      }
    })
  }
}
/**
 * Rec ScanFile
 */
ScanFile = Object.Rec.extend({
  /*
   * scanFileId userGroupId filename fileseq origFilename height width rotation
   * mime scanIndexId seq dateCreated createdBy
   */
  isPdf:function() {
    return this._pdf;
  },
  getSizedSrc:function(h, w) {
    if (this.isPdf())
      return this.src;
    return this.src + '&h=' + h + '&w=' + w;
  },
  asImage:function(h, w) {
    return Html.Image.create(null, this.getSizedSrc(h, w));
  },
  //
  ajax:function() {
    var worker = Html.Window;
    var self = this;
    return {
      remove:function(callback) {
        Ajax.Scanning.deleteFile(self.scanFileId, callback);
      },
      rotate:function(callback) {
        Ajax.Scanning.rotate(self.scanFileId, worker, callback);
      }
    }
  }
})
ScanFiles = Object.RecArray.of(ScanFile, {
  ajax:function(worker) {
    var worker = worker || Html.Window;
    return {
      fetchUnindexed:function(userId, callback) {
        Ajax.Scanning.getUnindexed(userId, worker, callback);
      },
      fetchIndexedToday:function(callback) {
        Ajax.Scanning.getIndexedToday(worker, callback);
      }
    }
  }
})
ScanIndex = Object.Rec.extend({
  onload:function() {
    this.ScanFiles = ScanFiles.revive(this.ScanFiles);
  },
  ajax:function(worker) {
    var self = this;
    worker = worker || Html.Window;
    return {
      fetch:function(id, callback) {
        Ajax.Scanning.getIndex(id, worker, callback);
      }
    }
  }
})