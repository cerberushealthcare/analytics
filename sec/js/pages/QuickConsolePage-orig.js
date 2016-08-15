/**
 * Quick Console Page
 * @author Warren Hornsby
 */
QuickConsolePage = page = {
  //
  start:function(query) {
    QuickConsoleTile.create();
    Page.setEvents();
  },
  onresize:function() {
    var vp = Html.Window.getViewportDim();
    var i = vp.height - 200;
    if (i != self.maxHeight) {
      self.maxHeight = i;
      QuickConsoleTile.setMaxHeight(i);
    }
  }
}
QuickConsoleTile = {
  create:function() {
    var My = this;
    return QuickConsoleTile = _$('tile').clean().extend(function(self) {
      return {
        init:function() {
          self.Selector = My.Selector.create(self)
            .bubble('onchoosepar', self.Selector_onchoosepar)
            .bubble('oncleardoc', self.clear)
            .bubble('onsetclient', self.Selector_onsetclient);
          self.Menu = My.Menu.create(self)
            .bubble('onclear', self.clear)
            .bubble('onclipboard', self.copy);
          self.Tui = My.Tui.create(self);
          me.isErx = function() {return false}
        },
        setMaxHeight:function(i) {
          self.Selector.setHeight(i);
          self.Tui.setHeight(i - self.Menu.getHeight() - 20);
        },
        Selector_onchoosepar:function(pid) {
          self.Tui.getPars(pid);
        },
        Selector_onsetclient:function(client) {
          self.Tui.setClient(client);
        },
        clear:function() {
          self.Tui.reset();
          self.Selector.resetPar();
        },
        copy:function() {
          CopyPop.pop(self.Tui.getHtml());
        }
      }
    })
  },
  Tui:{
    create:function(container) {
      return Html.TemplateUi.create_asParagraph(Html.Tile.create(container, 'tuic')).extend(function(self) {
        return {
          getPars:function(pid) {
            self.pid = pid;
            Pars.ajax(self).fetch(pid, function(pars) {
              self.tui.addPars(pars);
            })
          },
          setClient:function(client) {
            var fs = {'client':client};
            self.tui.facesheet = fs;
            self.tui.refresh();
          }
        }
      })
    }
  },
  Selector:{
    create:function(container) {
      var My = this;
      return Html.Tile.create(container, 'selector').extend(function(self) {
        return {
          onchoosepar:function(pid) {},
          oncleardoc:function() {},
          onsetclient:function(client) {},
          //
          init:function() {
            self.add(Html.H2.create('Patient'))
              .add(self.Patient = My.Anchor.asPatient().bubble('onload', self.Patient_onload))
              .add(Html.H2.create('Document', 'mt10'))
              .add(self.Doc = My.Anchor.asDocument().bubble('onload', self.Doc_onload))
              .add(Html.H2.create('Section', 'mt10'))
              .add(self.Sec = My.Anchor.asSection().bubble('onload', self.Sec_onload))
              .add(self.Pars = Html.Div.create())
              .add(self.Par = My.Anchor.asParagraph().bubble('onload', self.Par_onload));
          },
          resetPar:function() {
            self.Pars.clean();
            self.Par.reset();
          },
          Patient_onload:function(rec) {
            self.patient = rec;
            self.onsetclient(rec);
          },
          Doc_onload:function(rec) {
            if (self.templateId != rec.templateId) {
              self.Sec.setTemplate(rec);
              self.oncleardoc();
            }
            self.templateId = rec.templateId;
          },
          Sec_onload:function(rec) {
            if (self.sectionId != rec.sectionId) 
              self.Par.setSection(rec);
            self.sectionId = rec.sectionId;
          },
          Par_onload:function(rec) {
            self.onchoosepar(rec.parId);
            Html.AnchorAction.create('paragraph', rec.toString()).into(self.Pars)
          }
        }
      })
    },
    Anchor:{
      create:function(cls, name, popf) {
        return Html.AnchorAction.create(cls).extend(function(self) {
          return {
            onload:function(rec) {},
            //
            init:function() {
              self.reset();
              self.Pop = self.createPop();
            },
            isBlank:function() {
              return self.rec == null;
            },
            onclick:function() {
              self.pop(Pop.POS_CURSOR);
            },
            pop:function(pos) {
              self.Pop.POP_POS = pos || Pop.POS_LAST;
              self.Pop.pop.call(self.Pop, self.rec, self.load);
            },
            //
            reset:function() {
              self.setRecord(null);
              self.draw();
            },
            load:function(rec) {
              self.setRecord(rec);
              self.draw();
              self.onload(rec);
            },
            setRecord:function(rec) {
              self.rec = rec;
            },
            draw:function() {
              if (self.rec) {
                self.setText(self.rec.toString());
                self.removeClass('red');
              } else {
                self.addClass('red');
                self.setText('(Select)');
              }
            },
            createPop:function() {
              return Html.DirtyPop.create(name).extend(function(self) {
                return {
                  POP_POS:Pop.POS_CURSOR,
                  onshow:function(rec, onsave) {
                    self.onsave = onsave;
                    self.setRecord(rec);
                  },
                  setRecord:function(rec) {
                    self.rec = rec;
                    if (rec == null) 
                      self.load();
                    else
                      self.draw();
                  },
                  load:function() {},
                  draw:function() {},
                  choose:function(rec) {
                    self.setRecord(rec);
                    self.close_asSaved(rec);
                  }
                }
              }).extend(popf);
            }
          }
        })
      },
      asPatient:function() {
        return this.create('umale', 'Patient', function(self) {
          return {
            init:function() {
              self.Genders = Html.LabelRadios.create({'M':'Male','F':'Female'}, 1).into(Html.Pop.Frame.create(self.content, 'Gender'));
              self.Ages = Html.LabelRadios.create({'0':'Birth-1','2':'1-2','4':'3-5','7':'6-10','12':'11-14','16':'15-20','21':'21-39','41':'40-49','51':'50-59','61':'60-69','71':'70+'}, 1).into(Html.Pop.Frame.create(self.content, 'Age Range').addClass('mt5'));
              self.Cmd = Html.CmdBar.create(self.content).ok(self.ok_onclick).cancel(self.close);
            },
            draw:function() {
              var rec = self.rec;
              self.Genders.setValue(rec && rec.sex);
              self.Ages.setValue(rec && rec.age);
            },
            getRecord:function() {
              return {
                sex:self.Genders.getValue(),
                age:self.Ages.getValue(),
                _genders:self.Genders.map,
                _ages:self.Ages.map,
                toString:function() {
                  return this._genders[this.sex] + ' (' + this._ages[this.age] + ')';
                }}
            },
            ok_onclick:function() {
              var rec = self.getRecord();
              self.close_asSaved(rec);
            }
          }
        })
      },
      asDocument:function() {
        return this.create('page', 'Document Type', function(self) {
          return {
            init:function() {
              self.Window = Html.Tile.create(self.content, 'docwindow').withWorkingSmall();
            },
            load:function() {
              if (self.recs == null) {
                Templates.ajax(self.Window).fetch(function(recs) {
                  self.recs = recs;
                  recs.each(function(rec) {
                    rec.Anchor = Html.Anchor.create('template', rec.name).into(self.Window).set('onclick', function() {
                      self.choose(rec);
                    })
                  })
                  self.Window.setHeight();
                })
              }
            },
            draw:function() {
              self.recs.each(function(rec) {
                rec.Anchor.addClassIf('qcsel', self.rec.templateId == rec.templateId);
              })
            }
          }
        })
      },
      asSection:function() {
        return this.create('section', 'Section', function(self) {
          return {
            POP_POS:Pop.POS_LAST,
            init:function() {
              self.Window = Html.Tile.create(self.content, 'secwindow').withWorkingSmall();
            },
            setTemplate:function(rec) {
              self.template = rec;
              self.recs = null;
              self.Window.clean();
            },
            load:function() {
              if (self.template && self.recs == null) {
                TemplateMap.ajax(self.Window).fetch(self.template.templateId, function(map) {
                  self.recs = map.Sections;
                  self.recs.each(function(rec) {
                    rec.Anchor = Html.AnchorAction.create('sectionl', rec.name).into(self.Window).set('onclick', function() {
                      self.choose(rec);
                    })
                  })
                })
              }
            },
            draw:function() {
              self.recs.each(function(rec) {
                rec.Anchor.addClassIf('qcsel', self.rec.sectionId == rec.sectionId);
              })
            }
          }
        }).extend(function(self) {
          return {
            setTemplate:function(rec) {
              self.reset();
              self.template = rec;
              self.Pop.setTemplate(rec);
              self.pop();
            }
          }
        })
      },
      asParagraph:function() {
        return this.create('paragraph', 'Paragraph', function(self) {
          return {
            POP_POS:Pop.POS_LAST,
            init:function() {
              self.Window = Html.Tile.create(self.content, 'secwindow').withWorkingSmall();
              self.Cmd = Html.CmdBar.create(self.content).exit(self.close);
            },
            setSection:function(rec) {
              self.section = rec;
              self.recs = null;
              self.Window.clean();
            },
            load:function() {
              if (self.section && self.recs == null) {
                self.recs = self.section.Pars;
                self.Window.working(function() {
                  Html.H3.create('Main').into(self.Window);
                  self.Main = Html.Tile.create(self.Window);
                  Html.H3.create('All', 'mt10').into(self.Window);
                  self.All = Html.Tile.create(self.Window);
                  self.recs.each(function(rec) {
                    self.createAnchor(rec).into(self.All);
                    if (rec.major)
                      self.createAnchor(rec).into(self.Main);
                  })
                  self.Window.working(false);
                })
              }
            },
            createAnchor:function(rec) {
              return Html.AnchorAction.create('parl', rec.desc, function() {
                self.choose(rec);
              })
            },
            choose:function(rec) {
              self.setRecord(rec);
              self.onsave(rec);
            }
          }
        }).extend(function(self) {
          return {
            init:function() {
              self.draw();
            },
            setSection:function(rec) {
              self.reset();
              self.section = rec;
              self.Pop.setSection(rec);
              self.pop();
            },
            draw:function() {
              self.addClass('red');
              self.setText('(Add)');
            }
          }
        })
      }
    }
  },
  Menu:{
    create:function(container) {
      return Html.Tile.create(container, 'menu').extend(function(self) {
        return {
          onclear:function() {},
          onclipboard:function() {},
          //
          init:function() {
            Html.AnchorAction.create('copy', 'Clipboard').bubble('onclick', self, 'onclipboard').into(self);
            Html.Span.create('tdiv').into(self);
            Html.AnchorAction.create('clear', 'Clear').bubble('onclick', self, 'onclear').into(self);
          }
        }
      })
    }
  }
}
//
Template = Object.Rec.extend({
  toString:function() {
    return this.name;
  }
})
Templates = Object.RecArray.of(Template, {
  ajax:function(worker) {
    return {
      fetch:function(callback) {
        Ajax.getr('Templates', 'getTemplates', null, Templates, worker, callback);
      }
    }
  }
})
QParInfos = Object.RecArray.extend({
  revive:function(sid, jsons) {
    var recs = [];
    if (jsons) {
      jsons.each(function(json) {
        if (sid == null || json.sid == sid)
          recs.push(QPar.revive(json));
      })
    }
    return recs;
  },
  //
  ajax:function(worker) {
    return {
      fetch:function(tid, sid, pid, callback) {
        Ajax.postr('Session', 'getParInfos', {'tid':tid,'pids':[pid],'nd':'','cid':''}, QParInfos.revive.bind(QParInfos, sid), worker, callback);
      }
    }
  }
})
CopyPop = {
  pop:function() {
    return Html.Pop.singleton_pop.apply(CopyPop, arguments);
  },
  create:function() {
    return Html.Pop.create('Copy to Clipboard', 400).extend(function(self) {
      return {
        init:function() {
          self.Frame = Html.Pop.Frame.create(self.content, 'Press CTRL+C to copy (or right-click selection)');
          self.Doc = Html.Tile.create(self.Frame, 'DocCopy');
          Html.CmdBar.create(self.content).exit(self.close);
        },
        onshow:function(html) {
          self.Doc.html(html);
          //self.Doc.title = 'R';
          document.onkeyup = function() {
            if (event.keyCode == 67 && event.ctrlKey)
              self.close();
          }
          window.getSelection().selectAllChildren(self.Doc);
        },
        onclose:function() {
          document.onkeyup = null;
        }   
      }
    })
  }
}
